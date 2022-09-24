<?php

namespace App\Http\Controllers\Api\facilities;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\facilities;
use App\Models\favorites;
use App\Models\photos_fac;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:0"]);
    }
    public function index()
    {
        $favorite=\auth()->user()->favorite_facilities;
        return response([
            "Data"=> FacilityResource::collection($favorite)
        ]);
    }
    public function store(Request $request )
    {
        $validator = Validator::make($request->all(),[
            'id_facility'=>['required',Rule::exists("facilities","id")],
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()]);
        }
        $favorite=favorites::where([
            'id_facility'=> $request->id_facility,
            'id_user'=>\Auth::id()
        ])->first();

        if(!is_null($favorite)){
            $favorite->delete();
            return response()->json([
                "message"=>"delete  favorite"
            ]);
        }
        else
        {
            favorites::create([
                'id_user'=>\Auth::id(),
                'id_facility'=>$request->id_facility
            ]);

            return response()->json([
                "message"=>"add to favorite"
            ]);

        }
    }
    public function show($id)
    {
        $favorite=Auth::user()->favorite_facilities()->get();
        if ( is_null($favorite) ) {
            return response()->json(['message'=> 'favorit not found']);
        }
        return  response()->json([
            'message'=> 'favorite  exist',
            'Date'=>$favorite
        ]);
    }
}
