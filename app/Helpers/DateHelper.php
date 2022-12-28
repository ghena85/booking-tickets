<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DateHelper
{

    /**
     * @param Model  $model
     * @param string $field
     *
     * @return string
     */
    public static function time(Model $model, string $field = 'date') : string
    {
        return $model->$field ? $model->$field->format('H:i') ?? '' : '';
    }

    /**
     * @param Model  $model
     * @param string $field
     *
     * @return string
     */
    public static function month(Model $model, string $field = 'date') : string
    {
        return $model->$field ? $model->$field->monthName ?? '' : '';
    }

    public static function year(Model $model, string $field = 'date') : string
    {
        return $model->$field ? $model->$field->year ?? '' : '';
    }

    /**
     * @param Model  $model
     * @param string $field
     *
     * @return string
     */
    public static function dayWeek(Model $model, string $field = 'date') : string
    {
        return $model->$field ? $model->$field->dayName ?? '' : '';
    }

    /**
     * @param Model  $model
     * @param string $field
     *
     * @return string
     */
    public static function day(Model $model, string $field = 'date') : string
    {
        return $model->$field ? $model->$field->format('d') ?? '' : '';
    }


    public static function diffHours($start,$end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        return  $start->diffInHours($end);
    }


    public static function diffMinutes($start,$end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        return  $start->diffInMinutes($end);
    }

}
