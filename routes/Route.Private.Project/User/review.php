<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\User\ReviewController;


Route::controller(ReviewController::class)->prefix("user")->group(function (){
    Route::get("review/info","GetReview");
    Route::get("review/show","ShowReviewAll");
    Route::post("rate","CreateReviewRating");
    Route::post("comment","CreateReviewComment");
    Route::delete("review/delete","DeleteReview");
});
