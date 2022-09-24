<?php

namespace App\Http\Controllers\Api\User;

use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware(["auth:userapi"]);
    }
    public function CountNotRead(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            return response()->json([
                "count"=> $user->unreadNotifications()->count()
            ]);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function AllRequestWork(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            $validate = \Validator::make($request->all(),[
                "type" => ["required","string",Rule::in(["Read","UnRead","All","delete","update"])]
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $type = $request->type;
            $final = null;
            switch ($type){
                case "Read":
                    $final = $this->GetNotificationRead($request,$user);
                    break;
                case "UnRead":
                    $final = $this->GetNotificationUnRead($request,$user);
                    break;
                case "All":
                    $final = $this->GetNotificationAll($request,$user);
                    break;
                case "update":
                    if($request->isMethod("POST")) return $this->UpdateNotificationToRead($user);
                    else Throw new \Exception("the method Request is not POST");
                case "delete":
                    if($request->isMethod("DELETE")) return $this->DeleteNotifications($user);
                    else Throw new \Exception("the method Request is not DELETE");
            }
            if($request->isMethod("GET")){
                return response()->json($final);
            }else{
                Throw new \Exception("the method Request is not GET");
            }
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function GetNotificationRead($request,$user):array{
        $notifications = $user->readNotifications()->paginate($this->NumberOfValues($request));
        return $this->Paginate("Notifications",$notifications);
    }
    public function GetNotificationUnRead($request,$user):array{
        $notifications = $user->unreadNotifications()->select("data")->paginate($this->NumberOfValues($request));
        return $this->Paginate("Notifications",$notifications);
    }
    public function GetNotificationAll($request,$user):array{
        $notifications = $user->notifications()->paginate($this->NumberOfValues($request));
        return $this->Paginate("Notifications",$notifications);
    }

    public function UpdateNotificationToRead($user): \Illuminate\Http\JsonResponse
    {
        $user->unreadNotifications()->update([
            "read_at"=>Carbon::now()
        ]);
        return response()->json(["message"=>"Success Read Notification"]);
    }
    public function DeleteNotifications($user): \Illuminate\Http\JsonResponse
    {
        $user->notifications()->delete();
        return response()->json(["message"=>"Success Read Notification"]);
    }

}
