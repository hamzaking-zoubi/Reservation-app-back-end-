<?php


use App\Http\Controllers\Api\facilities\FacilitiesController;

Route::controller(FacilitiesController::class)->prefix("facility")->group(function (){
    Route::post("store","store");
    Route::get("indexAll","indexAll");
    Route::get("index","index");
    Route::get("show/{id}","show");
    Route::delete("delete/{id}","delete");
    Route::delete("deleteAllImage/{id}","deleteAllImage");
    Route::delete("deleteOneImage/{id}","deleteOneImage");
    Route::post("addListImage","addListImage");
    Route::post("update","update");
    Route::post("toggleAvailable","status");
});
