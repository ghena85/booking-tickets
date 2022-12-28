<?php

namespace App\Services\Events;

use App\Helpers\DatatablesHelper;
use App\Helpers\EmailHelper;
use App\Helpers\LabelHelper;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\EventRepository;
use App\Services\UnaService;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\MediaHelper;
use App\Helpers\ImageHelper;
use App\Repositories\CategoryRepository;
use \Illuminate\Support\Collection;

class EventService
{
    protected string $adminEmail = 'ghena2011@gmail.com';
    /**
     * @var EventRepository
     */
    public EventRepository $repository;

    /**
     * DictionaryService constructor.
     *
     * @param EventRepository $repository
     */
    public function __construct(EventRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getDatatablesData()
    {
        $collection = $this->repository->getCollectionToIndex();

        return Datatables::of($collection)
            ->addColumn('placeholder', '&nbsp;')
            ->addColumn('sala', function ($row) {
                return '<a href="' . route('admin.events.places', $row->id) . '">Ordres</a>';
            })
            ->addColumn('schema', function ($row) {
                return '<a href="' . route('admin.events.places', $row->id) . '">Ordres</a>';
            })
            ->editColumn('id', fn ($row) => $row->id)
            ->editColumn('sala', fn ($row) => isset($row->schema->name) ? $row->schema->name : '')
            ->editColumn('name', fn ($row) => isset($row->spectacle->name) ? $row->spectacle->name : '')
            ->editColumn('fest', fn ($row) => LabelHelper::festEventLabel($row))
            ->editColumn('active', fn ($row) => LabelHelper::boolLabel($row->active))
            ->editColumn('is_premiera', fn ($row) => LabelHelper::boolLabel($row->is_premiera))
            ->addColumn('image', fn ($row) => isset($row->spectacle->image_grid) ? ImageHelper::fullImageAdmin($row->spectacle->image_grid) : '')
          	->editColumn('has_notes', fn ($row) => LabelHelper::boolLabel($row->has_notes))
            ->editColumn('start_at', fn ($row) => $row->start_at)
            ->editColumn('created_at', fn ($row) => $row->created_at)
            ->addColumn('actions', fn ($row) => DatatablesHelper::renderActionsRow($row, 'events'))
            ->rawColumns(['actions', 'placeholder', 'active', 'is_premiera', 'image','fest', 'schema'])
            ->make(true);
    }

    /**
     * @param int|null $categoryId
     *
     * @return Collection
     */
    public function getCategoryList(? int $categoryId)
    {
        if ($categoryId) {
            return app(CategoryRepository::class)->getEvents($categoryId);
        }

        return $this->repository->getActiveEvents();
    }


    /**
     * @param StoreEventRequest $request
     *
     * @return Event
     */
    public function createEvent(StoreEventRequest $request) : Event
    {
        $inputData = $request->all();
        $spectacle = $this->repository->saveEvent($inputData);

        return $spectacle;
    }

    public function updateEvent(UpdateEventRequest $request, Event $event) : Event
    {
         return $this->repository->updateData($request->validated(), $event);
    }

    public function cancelRservationByOrderID($order,$una)
    {
        $errorCancel = 0;
        $tickets = Ticket::select('reservation_code')->where(['order_id' => $order->id])->where('reservation_code','!=','')->groupBy('reservation_code')->get();
        foreach ($tickets as $ticket) {
            $response = $una->cancelReservation($ticket->reservation_code);
            if($response['type'] == 'error') {
                EmailHelper::send($this->adminEmail, ['order' =>$order,'info' =>'EventService/cancelRservationByOrderID problema la anulare rezerva, Ticket reservation_code: '.$ticket->reservation_code], null,null, 'Ts/EventService/cancelRservationByOrderID Error R103' ,'warning');
                $errorCancel++;
            }
        }
        if($errorCancel == 0) {
            $order->reservation_code = '';
            Ticket::where(['order_id' => $order->id])->update(['reservation_code' => '','status' => 'open']);
            $order->save();

            $cc = null;
            if($order->ref != '') {
                $user  = User::where('ref',$order->ref)->first();
                $cc    = !empty($user) ? $user->email : '';
            }

            EmailHelper::send($order->email, ['order' =>$order], null,$cc, 'Rezervarea dumneavoastră a fost anulată' ,'payment_notify');
        }
        return ['success' => $errorCancel == 0 ? 1 : 0,'message' => isset($response['message']) ? $response['message'] : ''];
    }

    public function confirmPaymentByOrderID($order,$una)
    {
        $errorCancel = 0;
        $tickets = Ticket::select('reservation_code')->where(['order_id' => $order->id])->where('reservation_code','!=','')->groupBy('reservation_code')->get();
        foreach ($tickets as $ticket) {
            $response = $una->confirmPayment($ticket->reservation_code);
            if($response['type'] == 'error') {
                EmailHelper::send($this->adminEmail, ['info' =>'EventService/confirmPaymentByOrderID problema la confirmare plata, Ticket reservation_code: '.$ticket->reservation_code], null,null, 'Ts/EventService/confirmPaymentByOrderID Error R123' ,'warning');
                $errorCancel++;
            }
        }
        if($errorCancel == 0) {
//            $order->reservation_code = '';
            Ticket::where(['order_id' => $order->id])->update(['status' => 'paid']);
            $order->save();
//            EmailHelper::send($order->email, ['order' =>$order], null,null, 'Rezerva a fost achitata cu succes, #ID:'.$order->id ,'payment_notify');
        }
        return ['success' => $errorCancel == 0 ? 1 : 0,'message' => isset($response['message']) ? $response['message'] : ''];
    }
}
