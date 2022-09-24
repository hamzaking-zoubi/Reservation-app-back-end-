<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\OwnerBookingController;

Route::controller(OwnerBookingController::class)->prefix("owner")->group(function (){
    Route::get("bookings/show","OwnerShowBookingAll");
    Route::get("bookings/facility","ShowBookingFacility");
    Route::post("booking","BookingByTheOwner");
});
