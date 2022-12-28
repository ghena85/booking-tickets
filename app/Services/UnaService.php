<?php

namespace App\Services;

use GuzzleHttp\Client;

class UnaService
{

    private  $client;
    private  $appOptions;
    public function __construct()
    {
        $options = [
            'base_url' => 'http://api.com',
            'debug'  => true,
            'verify'   => true,
        ];
        $this->appOptions =  [
            'p_org_id' => 'p_org_id',
            'password' => 'password',
            'username' => 'username',
        ];
        $this->client = new Client($options);
    }

    public function authHash()
    {
        $query = ['query' => $this->appOptions];
        $request  = $this->client->get('/ticket/api/include/authorization.php',$query);
        $response = json_decode($request->getBody()->getContents());
        return $response->type == 'success' ? $response->result : '';
    }

    /**
     * Get list of Events
     * List of Spectacles
     */
    public function getEvents()
    {
        $query = ['query' => $this->appOptions];
        $request  = $this->client->get('/ticket/api/include/getEvents.php',$query);
        $response = json_decode($request->getBody()->getContents());
        return $response->type == 'success' ? $response->content : '';
    }

    /**
     * Get list of places
     * List of places for Spectacles
     */
    public function getPlaces($code, $data)
    {
        $query = ['query' =>
            [
                'p_org_id' => 'satiricus',
                'code'     => $code, // EVENT_CODE
                'data'     => $data, // EVENT start date
                'hash'     => $this->authHash(), // Auth token hash
            ]
        ];

        $request  = $this->client->get('ticket/api/include/getPlaces.php',$query);
        $response = json_decode($request->getBody()->getContents());
        return $response->type == 'success' ? $response->content : '';
    }

    /**
     * Get Reservation
     */
    public function getReservation($code)
    {
        $dataQuery =  [
            'p_org_id' => 'p_org_id',
            'reservation_code' => $code,
            'hash' => $this->authHash(),
        ];
        $query    = ['query' => $dataQuery ];
        $request  = $this->client->get('/ticket/api/include/get_reservation.php',$query);
        $response = json_decode($request->getBody()->getContents());
        $type     = !empty($response) ? $response->type : 'error';
        return [
            'type'    => $type,
            'error'   => $type == 'success' ? 0 : 1,
            'message' => !empty($response) ? $type == 'error' ? $response->message : '' : '',
            'info_reservation' => !empty($response) ? $type == 'success' ? $response->info_reservation : '' : '',
        ];
    }

    /**
     * Reservation
     */
    public function reserve($params)
    {
        $dataQuery =  [
            'p_org_id' => 'p_org_id',
            'hash' => $this->authHash(),
        ];
        $dataQuery = array_merge($dataQuery,$params);
        $query    = ['query' => $dataQuery ];
        $request  = $this->client->get('/ticket/api/include/reserve.php',$query);
        $response = json_decode($request->getBody()->getContents());
        return [
            'type' => !empty($response) ? $response->type : 'error',
            'reservation_code' => !empty($response) ? ($response->type != 'error' ? $response->reservation_code : '') : '',
            'message' => !empty($response) ? ($response->type == 'error' ? $response->message : '') : '',
        ];
    }

    /**
     * Cancelt Reservation
     */
    public function cancelReservation($reservation_code)
    {
        $dataQuery =  [
            'p_org_id' => 'p_org_id',
            'reservation_code' => $reservation_code,
            'hash' => $this->authHash(),
        ];
        $query    = ['query' => $dataQuery ];
        $request  = $this->client->get('/ticket/api/include/cancelReservation.php',$query);
        $response = json_decode($request->getBody()->getContents());
        $message = '';
        if(!empty($response)) {
            if($response->type == 'error') {
                if(isset($response->result)) {
                    $message = $response->result;
                } else if(isset($response->message)) {
                    $message = $response->message;
                }
            }
        }
        return [
            'type' => !empty($response) ? $response->type : 'error',
            'message' => $message,
        ];
    }


    /**
     * ConfirmPayment Reservation
     */
    public function confirmPayment($reservation_code)
    {
        $dataQuery =  [
            'p_org_id' => 'satiricus',
            'reservation_code' => $reservation_code,
            'payment_result' => 1,
            'hash' => $this->authHash(),
        ];
        $query    = ['query' => $dataQuery ];
        $request  = $this->client->get('/ticket/api/include/confirmPayment.php',$query);
        $response = json_decode($request->getBody()->getContents());
        // message
        $message = '';
        if(!empty($response)) {
            if($response->type == 'error') {
                if(isset($response->result)) {
                    $message = $response->result;
                } else if(isset($response->message)) {
                    $message = $response->message;
                }
            }
        }
        return [
            'type' => !empty($response) ? $response->type : 'error',
            'message' => $message,
        ];
    }
}
