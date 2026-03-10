<?php

namespace App\Http\Controllers;

use App\Checklist\Services\ChecklistAggregator;
use App\Checklist\Services\ChecklistService;
use App\Contracts\EmployeeRepositoryInterface;
use App\Http\Requests\ChecklistRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChecklistController extends Controller
{
    public function __construct(
        private EmployeeRepositoryInterface $repository,
        private ChecklistService $checklistService,
        private ChecklistAggregator $aggregator
    ) {}

    public function index(ChecklistRequest $request)
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
