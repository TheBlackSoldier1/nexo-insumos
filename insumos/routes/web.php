<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{InventoryController,ReportController};
Route::get('/',[InventoryController::class,'index'])->name('home');
Route::post('/products',[InventoryController::class,'product']);
Route::put('/products/{product}',[InventoryController::class,'product']);
Route::delete('/products/{product}',[InventoryController::class,'delete']);
Route::post('/movements',[InventoryController::class,'move']);
Route::post('/locations',[InventoryController::class,'location']);
Route::put('/locations/{location}',[InventoryController::class,'location']);
Route::delete('/locations/{location}',[InventoryController::class,'deleteLocation']);
Route::get('/export/{format}',[ReportController::class,'export'])->whereIn('format',['pdf','xlsx','csv','json']);
Route::get('/{page}',[InventoryController::class,'index'])->name('page');
