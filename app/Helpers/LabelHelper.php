<?php

namespace App\Helpers;

use Carbon\Carbon;

class LabelHelper
{

    /**
     * @param $val
     * @return string
     */
    public static function boolLabel($val) : string
    {
        $str = $val ? __('global.yes') : __('global.no');

        return '<span class="badge badge-' . ($val ? 'success' : 'danger') . '">' . $str . '</span>';
    }

    public static function unaEventLabel($val) : string
    {
        $str = $val->COD > 0 ? __('global.yes') : __('global.no');

        return '<span class="badge badge-' . ($val->COD > 0 ? 'success' : 'danger') . '">' . $str . '</span>';
    }

    public static function unaSpectacleLabel($val) : string
    {
        $str = $val->EVENT_ID > 0 ? __('global.yes') : __('global.no');
        return '<span class="badge badge-' . ($val->EVENT_ID > 0 ? 'success' : 'danger') . '">' . $str . '</span>';
    }


    public static function statusOrderLabel($val) : string
    {
        return !empty($val->order_status) ? $val->order_status->name_ro : '';
    }

    public static function paymentTypeOrderLabel($val) : string
    {
        return !empty($val->payment_type) ? $val->payment_type->name_ro : '';
    }

    public static function orderClientDetail($val) : string
    {
        return $val->first_name.' '.$val->last_name.'<br />'.$val->email.' '.$val->phone;
    }

    public static function orderOrganizatorDetail($order) : string
    {
        return  $order->organizator ? $order->organizator->name : 'online';
    }

    public static function orderCasirDetail($order) : string
    {
        return  $order->casir ? $order->casir->name : 'online';
    }


    public static function dateCreatedAtFormated($val) : string
    {
        return Carbon::parse($val->created_at)->format('d.m.y H:i:s');
    }

    public static function shortDesc($value, $limit = 100)
    {
        $short = strip_tags(htmlspecialchars_decode($value));
//        return \Illuminate\Support\Str::limit($short, $limit);
        return $short;
    }

    /**
     * @param $color
     * @return string
     */
    public static function colorLabel($color) : string
    {
        return "<span style='background-color: $color; padding-left: 15px; padding-right: 15px;' >&nbsp;</span>";
    }


}
