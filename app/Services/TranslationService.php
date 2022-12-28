<?php

namespace App\Services;

use App\Helpers\FileSystemHelper;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Support\Collection;
use App\Traits\FilterConstantsTrait;

class TranslationService
{
    use FilterConstantsTrait;

    public const LOCALE_RO = 'ro';
    public const LOCALE_RU = 'ru';
    public const LOCALE_EN = 'en';

    /**
     * @var array
     */
    private array $locales;

    /**
     * TranslationService constructor.
     */
    public function __construct()
    {
        $this->locales = self::filterConstants('LOCALE');
    }

    /**
     * @param string $folder
     * @return Collection
     */
    public function getFilesWithContent(string $folder) : Collection
    {
        return collect(FileSystemHelper::getFolderFiles($folder))
            ->map(function (SplFileInfo $file) {
                return (object) [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'content' => include($file->getPathname())
                ];
            });
    }

    /**
     * @return Collection
     */
    public function getLocales() : Collection
    {
        return collect(FileSystemHelper::getLangDirectories())
            ->filter(function (string $path) {
                return in_array($this->filterLocale($path), $this->locales);
            })
            ->map(function ($path) {
                return $this->fillLang($path);
            });
    }

    /**
     * @param Model $model
     * @param string $field
     * @param string $val
     */
    public function setLocaleTranslates(Model &$model, string $field, string $val) : void
    {
        $translations = collect($this->locales)
            ->map(function (string $locale) use ($val) {
                return [$locale => $val];
            })
            ->collapse()
            ->toArray();

        $model->setTranslations($field, $translations);
    }

    /**
     * @param string $path
     * @return \stdClass
     */
    private function fillLang(string $path) : \stdClass
    {
        $lang = new \stdClass();
        $lang->locale = $this->filterLocale($path);
        $lang->path = $path;

        return $lang;
    }

    /**
     * @param string $path
     * @return string
     */
    private function filterLocale(string $path) : string
    {
        return collect(explode("/", $path))->last();
    }
}
