<?php

namespace App\Http\Controllers;

use App\UI\Factories\CountryUiFactory;
use Illuminate\Http\Request;

class StepsController extends Controller
{
    public function __construct(
        private CountryUiFactory $factory
    ) {}

    public function __invoke(Request $request)
    {
        $country = $request->query('country');

        $provider = $this->factory->stepsProvider($country);

        return response()->json($provider->steps());
    }
}
