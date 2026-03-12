<?php

namespace App\Http\Controllers;

use App\DTO\ApiResponse;
use App\Domain\Checklist\Services\ChecklistService;
use App\Http\Requests\CountryRequest;
use App\Http\Resources\ApiResponseResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ChecklistController extends Controller
{
    public function __construct(
        private ChecklistService $checklistService,
    ) {}

    public function index(CountryRequest $request): JsonResponse
    {
        $country = strtolower($request->query('country'));
        try {
            $result = $this->checklistService->countrySummary($country);

            return $this->apiResponse(
                ApiResponse::success('Checklist summary retrieved successfully', $result, Response::HTTP_OK)
            );
        } catch (\Exception $e) {
            return $this->apiResponse(
                ApiResponse::error('An error occurred while processing the checklist.', null, Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }
    }

    private function apiResponse(ApiResponse $apiResponse): JsonResponse
    {
        return (new ApiResponseResource($apiResponse))
            ->response()
            ->setStatusCode($apiResponse->statusCode ?? Response::HTTP_OK);
    }
}
