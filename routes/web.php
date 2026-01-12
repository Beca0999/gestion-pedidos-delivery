<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\QuickOrder;
use App\Livewire\OrderTracking;

// Página principal del cliente
Route::get('/', QuickOrder::class);

// Página de rastreo para el cliente
Route::get('/rastreo/{id}', OrderTracking::class)->name('order.tracking');
use App\Livewire\RiderPanel;
Route::get('/rider', RiderPanel::class);
