<?php

namespace App\Http\Controllers;

use App\Domain\UI\Factories\CountryUiFactory;
use App\Http\Requests\CountryRequest;

class StepsController extends Controller
{
    public function __construct(
        private CountryUiFactory $factory
    ) {}

    public function __invoke(CountryRequest $request)
    {
        $country = $request->query('country');

        $provider = $this->factory->stepsProvider($country);

        return response()->json($provider->steps());
    }
}
