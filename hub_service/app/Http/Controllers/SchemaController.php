<?php

namespace App\Http\Controllers;

use App\DTO\ApiResponse;
use App\Http\Requests\CountryRequest;
use App\Domain\UI\Factories\CountryUiFactory;
use App\Http\Resources\ApiResponseResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SchemaController extends Controller
{
    public function __construct(
        private CountryUiFactory $factory
    ) {}

    public function show(CountryRequest $request, $step): JsonResponse
    {
        $country = $request->query('country');

        if ($step === 'dashboard') {

            $schema = $this->factory->dashboardSchema($country);

            return $this->apiResponse(
                ApiResponse::success('Schema retrieved successfully', [
                    'widgets' => $schema->widgets(),
                ], Response::HTTP_OK)
            );
        }

        return $this->apiResponse(
            ApiResponse::success('Schema retrieved successfully', [], Response::HTTP_OK)
        );
    }

    private function apiResponse(ApiResponse $apiResponse): JsonResponse
    {
        return (new ApiResponseResource($apiResponse))
            ->response()
            ->setStatusCode($apiResponse->statusCode ?? Response::HTTP_OK);
    }
}
