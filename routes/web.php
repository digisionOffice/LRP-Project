<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Geofencing validation route for karyawan panel
Route::post('/karyawan/validate-geofencing', [App\Http\Controllers\GeofencingController::class, 'validateAttendanceLocation'])
    ->middleware(['auth'])
    ->name('karyawan.validate-geofencing');
