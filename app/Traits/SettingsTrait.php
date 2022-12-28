<?php

namespace App\Traits;

use App\Repositories\PageRepository;
use Illuminate\Support\Facades\View;
use App\Repositories\Articles\ArticleRepository;

trait SettingsTrait
{

    public function sharePages() : void
    {
        $pageRepository = app(PageRepository::class);
        $headerPages = $pageRepository->getHeaderPages();
        $footerPages = $pageRepository->getFooterPages();

        $articleRepository = app(ArticleRepository::class);
        $footerArticles = $articleRepository->getFooterArticles();

        View::share(compact('headerPages', 'footerPages', 'footerArticles'));
    }

}
