<?php

namespace App\Http\Controllers;

use App\Http\Requests\CountryRequest;
use App\Domain\UI\Factories\CountryUiFactory;

class SchemaController extends Controller
{
    public function __construct(
        private CountryUiFactory $factory
    ) {}

    public function show(CountryRequest $request, $step)
    {
        $country = $request->query('country');

        if ($step === 'dashboard') {

            $schema = $this->factory->dashboardSchema($country);

            return response()->json([
                "widgets" => $schema->widgets()
            ]);
        }

        return response()->json([]);
    }
}
