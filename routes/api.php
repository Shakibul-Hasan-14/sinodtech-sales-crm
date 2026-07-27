<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SaleController;

Route::apiResource('products', ProductController::class);

Route::apiResource('customers', CustomerController::class);
Route::get('customers-inactive', [CustomerController::class, 'inactive']);
Route::patch('customers/{customer}/assign', [CustomerController::class, 'assign']);

Route::apiResource('employees', EmployeeController::class);
Route::post('customers/{customer}/reengage', [CustomerController::class, 'reengage']);

Route::get('sales', [SaleController::class, 'index']);
Route::post('sales', [SaleController::class, 'store']);
Route::get('sales/{sale}', [SaleController::class, 'show']);