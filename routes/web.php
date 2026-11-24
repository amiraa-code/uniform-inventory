<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/weather', WeatherController::class)->name('weather');
Route::view('/scan', 'scan')->name('scan');

// Must be registered before the resource route, otherwise /items/search would be treated as /items/{item}.
Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
Route::get('/items/check-sku', [ItemController::class, 'checkSku'])->name('items.check-sku');
Route::get('/items/lookup', [ItemController::class, 'lookup'])->name('items.lookup');
Route::resource('items', ItemController::class);

// Stock movements are an audit log, so they can be listed and created but never edited or deleted.
Route::resource('transactions', StockTransactionController::class)->only(['index', 'create', 'store']);