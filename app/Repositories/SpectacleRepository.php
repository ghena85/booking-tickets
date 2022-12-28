<?php

namespace App\Repositories;

use App\Models\Spectacle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Helpers\SlugHelper;

class SpectacleRepository extends Model
{

    /**
     * @return Collection
     */
    public function getListForHome() : Collection
    {
        return Spectacle::query()
            ->active()
            ->orderBy('start_at', 'desc')
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection
     */
    public function getListForSelect() : Collection
    {
        $list = Spectacle::query()
            ->where('id','!=',3530)
            ->where('id','!=',3772)
            ->get()
            ->groupBy('id', true)
            ->map(function (Collection $items) {
                return $items->shift()->name;
            });
        foreach ($list as $key => $value) {
            $list[$key] = $value.' - '.$key;
        }
        return $list;
    }

    /**
     * @param Spectacle $spectacle
     *
     * @return array
     */
    public function getRelatedCategoryIds(Spectacle $spectacle) : array
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
        return Spectacle::query()
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * @return Collection
     */
    public function getActiveSpectacles() : Collection
    {
        return Spectacle::query()
            ->active()
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * @param array $data
     *
     * @return Spectacle
     */
    public function saveSpectacle(array $data) : Spectacle
    {
        $spectacle = new Spectacle($data);
        $spectacle->slug = SlugHelper::generate('spectacle');
        $spectacle->save();

        return $spectacle->refresh();
    }

    public function getYoutubeEmbedUrl($url)
    {
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';

        if (preg_match($longUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }

        if (preg_match($shortUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }
        return 'https://www.youtube.com/embed/' . $youtube_id ;
    }
    /**
     * @param array    $data
     * @param Spectacle $spectacle
     *
     * @return Spectacle
     */
    public function updateData(array $data, Spectacle $spectacle) : Spectacle
    {
          if(isset($data['video_youtube_url']['ro']) && $data['video_youtube_url']['ro']) {
              $data['video_youtube_url']['ro'] = $this->getYoutubeEmbedUrl($data['video_youtube_url']['ro']);
          }
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
        Spectacle::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Spectacle $spectacle) {
                $spectacle->delete();
            });
    }

}
