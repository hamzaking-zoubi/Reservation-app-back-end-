<?php

namespace App\Http\Controllers\Api\RealTime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RealTimeController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:userapi");
    }
    //
}
