<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\facilities\ProposalsController;


Route::controller(ProposalsController::class)->prefix("user")->group(function (){
    Route::get("proposals","Proposals");
    Route::get("mostbooked","MostBooked");
    Route::get("top5rate","Top5Rate");
});
