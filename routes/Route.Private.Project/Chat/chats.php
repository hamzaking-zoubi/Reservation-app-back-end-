<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\RealTime\ChatController;


Route::controller(ChatController::class)->prefix("chat")->group(function (){
    Route::get("show","show_messages");
    Route::get("chatsdata","Show_all_chats");
    Route::get("chats","GetIdsChats");
    Route::get("info","Info_User_Chat");
    Route::post("send","send_message");
    Route::post("read","read_message");
    Route::delete("delete_messages","delete_messages");
    Route::delete("destroy_chats","destroy_chats");//58 5
});
