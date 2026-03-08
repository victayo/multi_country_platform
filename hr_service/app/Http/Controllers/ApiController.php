<?php

namespace App\Http\Controllers;

use App\DTO\ApiResponse;
use Illuminate\Http\Response;

class ApiController extends Controller
{
   protected function respond(ApiResponse $response)
   {
       return response()->json([
           'success' => $response->success ? 'success' : 'error',
           'message' => $response->message,
           'data' => $response->data,
       ], $response->statusCode ?? ($response->success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST));
   }

   public function respondSuccess(mixed $data = null, ?string $message = null, ?int $statusCode = Response::HTTP_OK)
   {
       return $this->respond(ApiResponse::success($message ?? 'Operation successful', $data, $statusCode));
   }

    public function respondError(string $message, mixed $data = null, ?int $statusCode = Response::HTTP_BAD_REQUEST)
    {
         return $this->respond(ApiResponse::error($message, $data, $statusCode));
    }
}
