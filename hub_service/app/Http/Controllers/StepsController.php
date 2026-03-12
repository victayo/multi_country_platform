<?php

namespace App\Http\Controllers;

use App\DTO\ApiResponse;
use App\Domain\UI\Factories\CountryUiFactory;
use App\Http\Requests\CountryRequest;
use App\Http\Resources\ApiResponseResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StepsController extends Controller
{
    public function __construct(
        private CountryUiFactory $factory
    ) {}

    public function __invoke(CountryRequest $request): JsonResponse
    {
        $country = $request->query('country');

        $provider = $this->factory->stepsProvider($country);

        return $this->apiResponse(
            ApiResponse::success('Steps retrieved successfully', $provider->steps(), Response::HTTP_OK)
        );
    }

    private function apiResponse(ApiResponse $apiResponse): JsonResponse
    {
        return (new ApiResponseResource($apiResponse))
            ->response()
            ->setStatusCode($apiResponse->statusCode ?? Response::HTTP_OK);
    }
}
