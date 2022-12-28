<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait APIExceptionTrait
{
    protected $statusCode = 200;
    private $errors = [];

    # Misc
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function addError($message, $code, $parameter = '')
    {
        $this->errors[] = ['message' => $message, 'code' => $code, 'parameter' => $parameter];
    }

    # Respond
    public function respond($data, $headers = [])
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }

    public function respondError($statusCode)
    {

        return $this->setStatusCode($statusCode)->respond([
            'error'       => $this->errors,
            'status_code' => $this->getStatusCode(),
        ]);
    }

    public function respondSuccess($data = [], $statusCode = 200)
    {
        return $this->setStatusCode($statusCode)->respond([
            'data'        => $data,
            'status_code' => $this->getStatusCode(),
        ]);
    }

    public function respondSuccessPaginate($data = [], $paginator = [], $statusCode = HTTP_OK)
    {
        return $this->setStatusCode($statusCode)->respond([
            'data'        => $data,
            'paginator'   => $paginator,
            'status_code' => $this->getStatusCode(),
        ]);
    }
}
