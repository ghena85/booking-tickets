<?php

namespace App\Traits;

use Illuminate\Support\Facades\View;
use App\Services\TranslationService;

trait Translatable
{
    public function shareLocales() : void
    {
        $service = resolve(TranslationService::class);

        $languages = $service->getLocales();

        $langs = [];
        foreach ($languages as $value) {
            if($value->locale == 'ro') {
                $langs[] = $value;
            }
        }
        foreach ($languages as $value) {
            if($value->locale != 'ro') {
                $langs[] = $value;
            }
        }

        $languages = json_decode(json_encode($langs), FALSE);

        View::share(compact('languages'));
    }

}
