<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/admin/hotels', 'admin.hotels')->name('admin.hotels');
Route::view('/admin/hotels/create', 'admin.hotels.create')->name('admin.hotels.create');
Route::view('/admin/hotels/{id}/edit', 'admin.hotels.edit')->name('admin.hotels.edit');
Route::view('/admin/room-types', 'admin.room-types')->name('admin.room-types');
