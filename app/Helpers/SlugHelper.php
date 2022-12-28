<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use \App\Http\Middleware\LocaleMiddleware;

class SlugHelper
{

    /**
     * @param string|null $str
     *
     * @return string
     */
    public static function generate(? string $str = '') : string
    {
        return Str::slug($str . "-" .  Str::random(5), '-', app()->getLocale());
    }

    /**
     * @param string $slug
     * @param string $prefix
     * @return string
     */
    public static function href(string $slug, string $prefix = '') : string
    {
        if (app()->getLocale() !== LocaleMiddleware::$mainLanguage) {
            return url(app()->getLocale() . $prefix . '/' . $slug);
        }

        return url($prefix . '/' . $slug);
    }
}
