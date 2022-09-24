<?php

namespace App\Http\Controllers\Api\User;

use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware("auth:userapi")->except(["register","login"]);
    }

    public function register(Request $request) {

        $validate = Validator::make($request->all(),[
            "name" => ["required","string"],
            "email" => ["required",Rule::unique("users","email"),"email"],
            "password" => ["required","min:8"],
            "password_c"=>["required","same:password"],
            "rule" => ["required","string","regex:/^[0-2]$/"],
        ]);
        if($validate->fails())
        {
            return \response()->json([
                "Error" => $validate->errors()
            ],401);
        }
        if($request->rule==="0")
        {
            $amount = 1000;
        }
        DB::beginTransaction();
        try {
            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => password_hash($request->password,PASSWORD_DEFAULT),
                "amount" => $amount ?? 0,
                "rule" => $request->rule
            ]);
            $token = $user->createToken($request->name,["*"])->plainTextToken;
            $user->profile()->create(["path_photo"=>"uploads/Users/defult_profile.png"]);
            $temp = clone $user;
            DB::commit();
            return \response()->json([
               "user" => $user,
               "path_photo"=>$temp->profile->path_photo??null,
               "token" => $token
            ],201);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(),[
            "email" => ["required",Rule::exists("users","email")],
            "password" => ["required"]
        ]);
        if($validate->fails())
        {
            return \response()->json([
                "Error" => $validate->errors()
            ],401);
        }
        $user = User::where("email",$request->email)->first();
        if(!is_null($user))
        {
            if (password_verify($request->password,$user->password)){
                $token = $user->createToken($user->name,["*"])->plainTextToken;
                $temp = clone $user;
                $this->UpdateAvailableFacilitiesOwner($user,true);
                return response(["user"=>$user,
                    "path_photo"=>$temp->profile->path_photo??null,
                    "token"=>$token
                ],201);
            }
            else{
                return response(["Error"=>["password"=>["password is error!!"]]],401);
            }
        }
        else{
            return response(["Error"=>"the User is not exists",401]);
        }
    }

    public function logout()
    {
        try {
            $user=auth()->user();
            $user->currentAccessToken()->delete();
            $data = $this->GetJsonFile($this->path_file());
            $data["countLogout"] += 1 ;
            $this->UpdateJsonFile($this->path_file(),$data);
            //$this->UpdateAvailableFacilitiesOwner($user,false);
            return response()->json(["Message"=>"Successfully logged out"],201);
        }catch (\Exception $exception){
            return response()->json([
                "Error"=> $exception->getMessage()
            ]);
        }
    }
    private function UpdateAvailableFacilitiesOwner($user,$available){
        if($user->rule==="1"){
            $user->user_facilities()->update([
                "available" => $available
            ]);
        }
    }
}
