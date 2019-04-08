<?php
/**
 * Author: 赵振 <270281156@qq.com>
 * Date: 2019/2/26 下午12:28
 */

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Response;

trait ApiResponse
{
    /**
     * @var int
     */
    protected $statusCode = FoundationResponse::HTTP_OK;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {

        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param $data
     * @param array $header
     * @return mixed
     */
    public function respond($data, $header = [])
    {

        return Response::json($data, $this->getStatusCode(), $header);
    }

    /**
     * @param $status
     * @param array $data
     * @param null $code
     * @return mixed
     */
    public function status($status, $message = null, $data = null, $code = 0)
    {
        $response = [
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];

        return $this->respond($response);

    }

    /**
     * @param $message
     * @param int $code
     * @param string $status
     * @return mixed
     */
    public function failed($message, $code = -1)
    {
        return $this->status("fail", $message,[],$code);
    }


    /**
     * @param $data
     * @param string $status
     * @return mixed
     */
    public function success($data = null, $message = null)
    {

        return $this->status("success", $message, $data);
    }

}