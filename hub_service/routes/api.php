<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\SchemaController;
use App\Http\Controllers\StepsController;
use Illuminate\Support\Facades\Route;


Route::get('/checklists', [ChecklistController::class, 'index'])->name('checklists.index');
Route::get('/steps', [StepsController::class, '__invoke'])->name('steps');
Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
Route::get('/schema/{step}', [SchemaController::class, 'show'])->name('schema.show');
