<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\facilities\SearchController;


Route::controller(SearchController::class)->prefix("facilities")->group(function (){
    Route::get("search","SearchFullAttr");
});
