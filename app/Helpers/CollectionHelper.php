<?php

namespace App\Helpers;

use App\Models\Ticket;
use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class CollectionHelper
{
    /**
     * @param Collection $results
     * @param int $pageSize
     *
     * @return LengthAwarePaginator
     */
    public static function paginate(Collection $results, int $pageSize) : LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage('page');

        $total = $results->count();

        return self::paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page'
        ]);

    }

    /**
     * @param Collection $items
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @param array $options
     *
     * @return LengthAwarePaginator
     */
    protected static function paginator(Collection $items, int $total, int $perPage, int $currentPage, array $options) : LengthAwarePaginator
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }

    public  static function unique_code()
    {
        $limit = 6;
        $code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
        $ticket = Ticket::where('barcode',$code)->first();
        if(!empty($ticket)) {
            $code = unique_code();
        }
        return $code;
    }

    public static function user_ip()
    {
//      $ip = getHostByName(getHostName());// "127.0.2.1"
        $ip = $_SERVER["REMOTE_ADDR"];// "192.168.20.1"
        return $ip;

        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

public static function isMobileDevice() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo
|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i"
            , $_SERVER["HTTP_USER_AGENT"]);
    }

}
