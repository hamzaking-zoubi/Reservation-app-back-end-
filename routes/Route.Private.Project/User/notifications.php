<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\NotificationController;


Route::controller(NotificationController::class)->prefix("user")->group(function (){
    Route::match(["get","post","delete"],"notifications","AllRequestWork");
    Route::get("notifications/countRead","CountNotRead");
});
