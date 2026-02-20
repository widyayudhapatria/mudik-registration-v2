<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Success response.
     */
    protected function responseSuccess(
        string $message = 'Success',
        mixed $data = null,
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Created response.
     */
    protected function responseCreated(
        string $message = 'Created',
        mixed $data = null
    ): JsonResponse {
        return $this->responseSuccess($message, $data, 201);
    }

    /**
     * Error response.
     */
    protected function responseError(
        string $message = 'Error',
        string $errorCode = 'ERROR',
        mixed $data = null,
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Forbidden response.
     */
    protected function responseForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->responseError($message, 'FORBIDDEN', null, 403);
    }

    /**
     * Not found response.
     */
    protected function responseNotFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->responseError($message, 'NOT_FOUND', null, 404);
    }
}