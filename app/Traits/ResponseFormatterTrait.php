<?php

namespace App\Traits;

trait ResponseFormatterTrait
{
    /**
     * Send Success Response
     * 
     * @param string $message
     * @param array/object $data
     * @param integer $code
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($message, $data = null, $code)
    {
        return response()->json([
            'message' => $message,
            'error' => false,
            'code' => $code,
            'results' => $data
        ], $code);
    }

    /**
     * Send Error Response
     * 
     * @param string $message
     * @param integer $code
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($message, $code)
    {
        $statusCode = [
            '200',
            '201',
            '400',
            '401',
            '404',
            '422',
            '419',
            '500'
        ];

        // Make dynamic error response code
        $statsCode = [];
        foreach($statusCode as $status) {
            if($status == (string)$code) {
                $statsCode[] = $status;
            }
            $statsCode['error'] = 500;
        }

        // Replace code value
        $code = count($statsCode) == 2 ? $statsCode[0] : $statsCode['error'];

        return response()->json([
            'message' => $message,
            'error' => true,
            'code' => (int)$code
        ], $code);
    }
}