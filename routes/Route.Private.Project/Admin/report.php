<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\ReportController;


Route::controller(ReportController::class)->prefix("report")->group(function (){
    Route::get("info","infoReport");
    Route::get("facility/show","ShowReportsFac");
    Route::get("show/all","ShowReportsAll");
    Route::post("add","AddReport");
    Route::delete("clear","ClearReportsFacility");
});
