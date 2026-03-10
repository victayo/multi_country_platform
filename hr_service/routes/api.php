<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('employees', \App\Http\Controllers\EmployeeController::class);
