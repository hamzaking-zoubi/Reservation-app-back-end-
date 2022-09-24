<?php

namespace App\Http\Controllers\Api\User;

use App\Class_Public\DataInNotifiy;
use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\bookings;
use App\Models\facilities;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OwnerBookingController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:1"]);
    }
    public function BookingByTheOwner(Request $request): \Illuminate\Http\JsonResponse
    {
        $BCheck = new BookingController();
        DB::beginTransaction();
        try {
            $validate = Validator::make($request->all(), [
                "id_facility" => ["required", Rule::exists("facilities", "id"), "numeric"],
                "start_date" => ["required","date"],
                "end_date" => ["required","date"]
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $start_date = $request->start_date  ?? null;
            $end_date =  $request->end_date ?? null;
            if($start_date!==null&&$end_date!==null){
                if(!$this->Check_Date($start_date,$end_date)){
                    Throw new \Exception("The Problem in Date :(");
                }
            }
            $user = auth()->user();
            $facility = facilities::where("id",$request->id_facility)->where("id_user",$user->id)->first();
            if(!is_null($facility)){
                if($facility->available===0){
                    return \response()->json(["Error"=>[
                        "facility" => "The Facility is Not Available Now :("
                    ]]);
                }
                if($BCheck->CheckBooking($facility,$start_date,$end_date)===true){
                    $booking = bookings::create([
                        "id_user"=>$user->id,
                        "id_facility"=>$facility->id,
                        "cost"=> 0,
                        "start_date"=>$start_date,
                        "end_date"=>$end_date
                    ]);
                    DB::commit();
                    return \response()->json(["booking"=>$booking]);
                }else{
                    return \response()->json(["Error"=>[
                        "facility" => "The Facility is Not Available in Between This Date :("
                    ]]);
                }
            }else{
                Throw new \Exception("Unauthenticated.");
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }

    public function ShowBookingFacility(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(), [
                "id_facility" => ["required", Rule::exists("facilities", "id"), "numeric"],
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $facility = auth()->user()
                ->user_facilities()->where("facilities.id",$request->id_facility)->first();
            if(!is_null($facility)){
            $bookings = bookings::where("id_facility",$facility->id)
                ->paginate($this->NumberOfValues($request));
                $final = $this->Paginate("bookings",$bookings);
                foreach ($final["bookings"] as $item){
                    $item->profile = $this->GetProfile($item["id_user"]);
                }
                return response()->json($final);
            }
            else{
                Throw new \Exception("the facility is not included in this owner facilities");
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }

    public function OwnerShowBookingAll(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $id_facilities = auth()->user()->user_facilities()->select("facilities.id as id")->get()->toArray();
            $bookings = bookings::whereIn("id_facility",$id_facilities)->paginate($this->NumberOfValues($request));
            $final = $this->Paginate("bookings",$bookings);
            foreach ($final["bookings"] as $item){
                 $item->profile = $this->GetProfile($item["id_user"]);
            }
            return response()->json($final);
        }catch (\Exception $exception){
            return response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    private function GetProfile($id){
        $user = User::with("profile")->where("id",$id)->first();
        return $user;
    }

}
