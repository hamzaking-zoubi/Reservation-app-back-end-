<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\StatusController;


Route::controller(StatusController::class)->prefix("state")->group(function (){
    Route::post("online","Online");
    Route::post("offline","Offline");
});
