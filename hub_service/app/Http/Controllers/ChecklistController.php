<?php

namespace App\Http\Controllers;

use App\Domain\Checklist\Services\ChecklistService;
use App\Http\Requests\CountryRequest;

class ChecklistController extends Controller
{
    public function __construct(
        private ChecklistService $checklistService,
    ) {}

    public function index(CountryRequest $request)
    {
        $country = strtolower($request->query('country'));
        try{

            $result = $this->checklistService->countrySummary($country);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while processing the checklist.'], 500);
        }

    }
}
