<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

// Must be registered before the resource route, otherwise /items/search would be treated as /items/{item}.
Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');

Route::resource('items', ItemController::class);

// Stock movements are an audit log, so they can be listed and created but never edited or deleted.
Route::resource('transactions', StockTransactionController::class)->only(['index', 'create', 'store']);