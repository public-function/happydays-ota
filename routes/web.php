<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home\Admins\Hotels\Index as HotelsIndex;
use App\Livewire\Home\Admins\Hotels\Create as HotelsCreate;
use App\Livewire\Home\Admins\Hotels\Edit as HotelsEdit;
use App\Livewire\Home\Admins\RoomTypes\Index as RoomTypesIndex;

Route::get('/', function () {
    return redirect()->route('admin.hotels');
});

// Dashboard
Route::view('/admin/dashboard', 'livewire.layouts.app-layout')->name('admin.dashboard');

// Admin Hotels
Route::get('/admin/hotels', HotelsIndex::class)->name('admin.hotels');
Route::get('/admin/hotels/create', HotelsCreate::class)->name('admin.hotels.create');
Route::get('/admin/hotels/{id}/edit', HotelsEdit::class)->name('admin.hotels.edit');

// Admin Room Types
Route::get('/admin/room-types', RoomTypesIndex::class)->name('admin.room-types');

// Placeholder routes for features that need components
Route::view('/admin/inventory', 'livewire.layouts.app-layout')->name('admin.inventory');
Route::view('/admin/bookings', 'livewire.layouts.app-layout')->name('admin.bookings');
Route::view('/admin/bookings/create', 'livewire.layouts.app-layout')->name('admin.bookings.create');
Route::view('/admin/payments', 'livewire.layouts.app-layout')->name('admin.payments');
Route::view('/admin/rate-plans', 'livewire.layouts.app-layout')->name('admin.rate-plans');
