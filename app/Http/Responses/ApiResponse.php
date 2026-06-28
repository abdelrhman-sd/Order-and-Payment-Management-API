<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    public static function build(
        mixed   $data   = null,
        int     $status = Response::HTTP_OK,
        array   $additional = []
    ): JsonResponse {

        return response()->json(
            array_merge(['success' => true, 'data' => $data], $additional),
            $status
        );
    }
}
