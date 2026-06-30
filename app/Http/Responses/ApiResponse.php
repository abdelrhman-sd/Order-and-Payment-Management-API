<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    public static function build(
        ?string     $message    = null,
        mixed       $data       = null,
        int         $status     = Response::HTTP_OK,
        array       $additional = []
    ): JsonResponse {

        $response = ['success' => true, 'status' => $status];

        is_null($message) ?: $response['message'] = $message;
        is_null($data)  ?: $response['data'] = $data;

        return response()->json(array_merge($response, $additional), $status);
    }
}
