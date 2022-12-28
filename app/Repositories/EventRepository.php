<?php

namespace App\Repositories;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Helpers\SlugHelper;

class EventRepository extends Model
{

    /**
     * @return Collection
     */
    public function getListForHome() : Collection
    {
        return Event::query()
            ->active()
            ->where('start_at','>',Carbon::now()->format('Y-m-d H:i:s'))//
            ->where('fest_id',0)// in lista nu trebuie sa apara
            ->orderBy('start_at', 'asc')
            ->get();
    }

    public function getListForSlider() : Collection
    {
        return Event::query()
            ->active()
            ->where('start_at','>=',Carbon::now()->format('Y-m-d H:i:s'))//
            ->where('fest_id',0)// in lista nu trebuie sa apara
            ->orderBy('start_at', 'asc')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection
     */
    public function getListForSelect() : Collection
    {
        return Event::query()
            ->get()
            ->groupBy('id', true)
            ->map(function (Collection $items) {
                return $items->shift()->name;
            });
    }

    /**
     * @param Event $spectacle
     *
     * @return array
     */
    public function getRelatedCategoryIds(Event $spectacle) : array
    {
        return $spectacle
            ->categories
            ->pluck('id')
            ->toArray();
    }

    /**
     * @return Collection
     */
    public function getCollectionToIndex() : Collection
    {
        return Event::query()
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * @return Collection
     */
    public function getActiveEvents() : Collection
    {
        return Event::query()
            ->active()
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * @param array $data
     *
     * @return Event
     */
    public function saveEvent(array $data) : Event
    {
//        dd($data);
        $spectacle = new Event($data);
        $spectacle->schema_id = $data['schema_id'];
        $spectacle->slug = SlugHelper::generate('spectacle');
        $spectacle->save();

        return $spectacle->refresh();
    }

    /**
     * @param array    $data
     * @param Event $spectacle
     *
     * @return Event
     */
    public function updateData(array $data, Event $spectacle) : Event
    {
        $spectacle->update($data);

        return $spectacle->refresh();
    }

    /**
     * @param array $ids
     *
     * @throws \Exception
     */
    public function deleteIds(array $ids) : void
    {
        Event::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Event $spectacle) {
                $spectacle->delete();
            });
    }

}
