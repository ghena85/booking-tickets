<?php

namespace App\Helpers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageHelper
{

    /**
     * @param Media|null $media
     *
     * @return string
     */
    public static function thumbImage(? Media $media) : string
    {
        if (! $media) {
            return "";
        }

        $link = MediaHelper::forceChangeUrlToPublic($media->thumb);

        return $media
            ? sprintf('<img src="%s" />', $link)
            : '';
    }

    public static function fullImageAdmin(? Media $media) : string
    {
        if (! $media) {
            return "";
        }
        $link = MediaHelper::forceChangeUrlToPublic($media->url);

        return $media
            ? sprintf('<img src="%s" style="width: 50px" />', $link)
            : '';
    }

}


