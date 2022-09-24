<?php

namespace App\Http\Controllers\Api\facilities;

use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\bookings;
use App\Models\facilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\isEmpty;

class ProposalsController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:0"])->only("Proposals");
    }

    private function WithPhotos($final_data){
        foreach ($final_data as $item){
            $item->photos = DB::table("photos_facility")
                ->select(["photos_facility.*"])
                ->where("photos_facility.id_facility",$item->id)
                ->get();
        }
    }
    public function Top5Rate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(),[
                "type" => ["nullable","array",Rule::in(["hostel","chalet","farmer"])],
            ]);
            if($validate->fails())
            {
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            if(is_null($request->type)){
                $Facilities = DB::table("facilities")
                    ->orderByDesc("rate")
                    ->take(5)
                    ->get();
            }
            else{
                $Facilities = DB::table("facilities")
                    ->where("type",$request->type)
                    ->orderByDesc("rate")
                    ->take(5)
                    ->get();
            }

            $this->WithPhotos($Facilities);
            return response()->json($Facilities);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }
    public function MostBooked(): \Illuminate\Http\JsonResponse
    {
        try {
        $Facilities = DB::table("bookings")
            ->selectRaw('COUNT(bookings.id_facility) as NumBooking,facilities.*')
            ->leftJoin('facilities','facilities.id','=','bookings.id_facility')
            ->groupBy(["bookings.id_facility"])
            ->orderByDesc("NumBooking")
            ->take(10)
            ->get();
        $this->WithPhotos($Facilities);
        return response()->json($Facilities);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }
    public function Proposals(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = [];
            foreach ($this->MostBooked()->getData() as $item){
                $ids[]=$item->id;
            }
            $FacilitiesAlike = facilities::whereIn("id",$this->GetIdsFacilitiesAlike())
                 ->orWhereIn("id",$ids)
                 ->orderBy("rate","desc")
                 ->paginate($this->NumberOfValues($request));
            $FinalAllData = $this->Paginate("facilities",$FacilitiesAlike);
            $this->WithPhotos($FinalAllData["facilities"]);
            return response()->json($FinalAllData);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ], 401);
        }
    }

    private function GetIdsFacilitiesAlike(){
        $user = auth()->user();
        $ids_facilities = [];
        $ids_facilities_temp = $user->bookings()
            ->select("id_facility")
            ->distinct()->get()->toArray();//id_facility : values
        foreach ($ids_facilities_temp as $item){
            $ids_facilities []= $item["id_facility"];
        }
        $ids_users = [];
        $ids_users_temp = bookings::select("id_user")
            ->whereIn("id_facility",$ids_facilities)->where("id_user","!=",$user->id)
            ->distinct()->get()->toArray();//id_facility : values
        foreach ($ids_users_temp as $item){
            $ids_users []= $item["id_user"];
        }
        return bookings::select("id_facility")
            ->whereIn("id_user",$ids_users)->whereNotIn("id_facility",$ids_facilities)
            ->distinct()->get()->toArray();//id_facility : values
    }
}
