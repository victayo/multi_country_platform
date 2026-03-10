<?php

namespace App\Http\Controllers;

use App\UI\Factories\CountryUiFactory;
use Illuminate\Http\Request;

class SchemaController extends Controller
{
    public function __construct(
        private CountryUiFactory $factory
    ) {}

    public function show(Request $request, $step)
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
