<?php


Route::controller(\App\Http\Controllers\Api\facilities\FavoriteController::class)->prefix("favorite")->group(function (){
    Route::post("toggle","store");
    Route::get("index","index");
    Route::get("show/{id}","show");
});
