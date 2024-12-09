<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;

Route::get('/brands', [BrandController::class, 'index']); // Fetch all active brands
Route::get('/brands/{id}', [BrandController::class, 'show']); // Fetch a specific brand
Route::get('/categories', [CategoryController::class, 'index']); // Fetch all active categories
Route::get('/categories/{id}', [CategoryController::class, 'show']); // Fetch a specific category

