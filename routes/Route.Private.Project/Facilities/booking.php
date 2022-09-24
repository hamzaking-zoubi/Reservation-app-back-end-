<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\BookingController;

Route::controller(BookingController::class)->prefix("bookings")->group(function (){
    Route::get("dates","DatesNotAvailable");
    Route::get("costbooking","CostBooking");
    Route::get("show","Display_Booking");
    Route::get("info","GetInfoBooking");
    Route::post("booking","Booking");
    Route::delete("unbooking","UnBooking");
});
