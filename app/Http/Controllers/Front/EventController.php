<?php

namespace App\Http\Controllers\Front;

use App\Helpers\EmailHelper;
use App\Helpers\LabelHelper;
use App\Helpers\MediaHelper;
use App\Models\Event;
use App\Repositories\EventRepository;
use App\Services\Events\EventService;
use \Illuminate\Contracts\View\View;
use \Illuminate\Contracts\View\Factory;
use \Illuminate\Contracts\Foundation\Application;
use App\Models\Spectacle;
use App\Repositories\CategoryRepository;
use App\Http\Requests\Front\Spectacles\IndexSpectacleRequest;
use App\Services\Spectacles\SpectacleService;
use App\Helpers\CollectionHelper;
use App\Http\Controllers\FrontController;
use App\Services\SchemaEventService;
use App\Models\Note;

class EventController extends FrontController
{

    /**
     * @param IndexSpectacleRequest $request
     * @param SpectacleService   $service
     * @param CategoryRepository    $categoryRepository
     *
     * @return Application|Factory|View
     */
    public function index(IndexSpectacleRequest $request, EventRepository $eventRepository, EventService $service, CategoryRepository $categoryRepository)
    {
        $s = $request->input('s');
        $categories = $categoryRepository->getCollectionToIndex();
        $events     = Event::select("events.*","spectacles.name")
                             ->where('events.fest_id',0)
                            ->join('spectacles','spectacles.id','=','events.spectacle_id')
                            ->orderBy('start_at', 'asc')
                            ->paginate(20);

        if($s)
        {
            $events = $events->get();
            $results = collect($events)->filter(function ($item) use ($s) {
                return false !== stristr($item->name, $s);
            });
            $events = CollectionHelper::paginate($results, 12);
        }
        else
        {
            $events = $events->orderBy('events.start_at', 'asc')->paginate(12);
        }


        return view('front.events.index', compact('events', 'categories'));
    }

    /**
     * @param Spectacle     $spectacle
     * @param SchemaEventService $schemaEventService
     *
     * @return Application|Factory|View
     */
    public function show(Event $event, SchemaEventService $schemaEventService,$fest_id = '')
    {
        if($fest_id) {
            $event = Event::where('fest_id',$fest_id)->first();

            if(empty($event)) return abort(404);
        }
        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Pragma: no-cache'); // HTTP 1.0.
        header('Expires: 0'); // Proxies.

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        set_time_limit (0);

        $schema = $event->schema;

      	$notes = null;
      	if($event->has_notes){
         	$notes = Note::get();
        }
        $rows = $schemaEventService->generateRowsData($schema, $event);
        $cartTotals = (new CartController())->getGroupTotals();
        $total = \Cart::getTotal();

        //SEO for Share
        $og_meta['image']        = url(MediaHelper::getImageUrl($event->spectacle, 'image_detail'));
        $og_meta['image_width']  = '600';
        $og_meta['image_height'] = '300';
        $og_meta['title']        = $event->spectacle->name;
        $og_meta['description']  = LabelHelper::shortDesc($event->spectacle->description);
        // end SEO for Share
        return view('front.events.show', compact('event','og_meta', 'rows', 'cartTotals', 'total', 'notes'));
    }

}
