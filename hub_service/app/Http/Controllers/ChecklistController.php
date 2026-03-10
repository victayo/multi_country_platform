<?php

namespace App\Http\Controllers;

use App\Domain\Checklist\Services\ChecklistAggregator;
use App\Domain\Checklist\Services\ChecklistService;
use App\Domain\Employees\Contracts\EmployeeRepositoryInterface;
use App\Http\Requests\CountryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChecklistController extends Controller
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
        private ChecklistService $checklistService,
        private ChecklistAggregator $aggregator
    ) {}

    public function index(CountryRequest $request)
    {
        $country = strtolower($request->query('country'));

        $result = Cache::remember(
            "checklist:$country",
            now()->addMinutes(10),
            function () use ($country) {

                $employees = $this->repository
                    ->findByCountry($country)
                    ->get();

                $evaluations = $this->checklistService
                    ->evaluate($employees->toArray(), $country);

                return $this->aggregator
                    ->aggregate($evaluations);
            }
        );

        return response()->json($result);
    }
}
