<?php

namespace App\Http\Controllers\Api\User;

use App\Class_Public\DataInNotifiy;
use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\bookings;
use App\Models\facilities;
use App\Models\reports;
use App\Models\User;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Notification;

class ReportController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:2"])->except(["RefundToUser","AddReport"]);
        $this->middleware(["auth:userapi","multi.auth:0"])->only(["RefundToUser",'AddReport']);
    }

    /**
     * @throws \Throwable
     */
    public function AddReport(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator=Validator::make($request->all(),[
            "id_facility"=>['required',Rule::exists('facilities','id')],
            "report"=>['required','string'],
        ]);

        if($validator->fails()){
            return response()->json([
                'Error'=>$validator->errors()
            ]);
        }
        $user = auth()->user();
        $facility = facilities::where("id",$request->id_facility)->first();
        $owner = User::where("id",$facility->id_user)->first();
        $admins = User::where("rule","2")->get();
        DB::beginTransaction();
        try {
        if(!$this->CheckBooking($user->id,$request->id_facility)){
            $report = reports::create([
                "id_user"=> $user->id,
                "id_facility" => $request->id_facility,
                "report" => $request->report
            ]);
            if($this->CheckIS3Report($facility)){
                $this->RefundToUser($facility);
                $header = "Report Facility ".$facility->name;
                $body = "the facility was deleted because the number of reports was equal to 3";
                Notification::send($admins,new UserNotification($header,"Delete facility",$body,Carbon::now()));
                $owner->notify(new UserNotification($header,"Delete facility", $body,Carbon::now()));
            }else{
                $header = "Report Facility ".$facility->name;
                $body = "The facility has been report by the user : ".$user->name;
                $body_request = ["id_report"=>$report->id];
                $Data = new DataInNotifiy("/report/info",$body_request,"GET");
                Notification::send($admins,new UserNotification($header,"Report",$body,Carbon::now(),$Data));
                $owner->notify(new UserNotification($header,"Report", $body,Carbon::now()),$Data);
            }
            DB::commit();
            return response()->json([
                "report" => $report
            ]);
        }else{
            Throw new \Exception("the user is not Booking facility");
        }
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function infoReport(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator=Validator::make($request->all(),[
                "id_report"=>['required',Rule::exists('reports','id')],
            ]);

            if($validator->fails()){
                return response()->json([
                    'Error'=>$validator->errors()
                ]);
            }
            return response()->json([
                "report" => reports::where("id",$request->id_report)->first()
            ]);
        }catch (\Exception $exception){
            return response()->json([
            "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function ShowReportsFac(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator=Validator::make($request->all(),[
                "id_facility"=>['required',Rule::exists('facilities','id')],
            ]);

            if($validator->fails()){
                return response()->json([
                    'Error'=>$validator->errors()
                ]);
            }
            return response()->json([
                "report" => reports::where("id_facility",$request->id_facility)->get()
            ]);
        }catch (\Exception $exception){
            return response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function ShowReportsAll(Request $request): \Illuminate\Http\JsonResponse
    {
        $reports = reports::paginate($this->NumberOfValues($request));
        return response()->json($this->Paginate("reports",$reports));
    }

    /**
     * @throws \Throwable
     */
    public function ClearReportsFacility(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $validator=Validator::make($request->all(),[
                "id_facility"=>['required',Rule::exists('reports','id_facility'),Rule::exists('facilities','id')],
            ]);

            if($validator->fails()){
                return response()->json([
                    'Error'=>$validator->errors()
                ]);
            }
            if(is_null($request->id_facility)){
                reports::all()->delete();
            }else{
                reports::where("id_facility",$request->id_facility)->delete();
            }
            DB::commit();
            return response()->json([
                "message" => "Done Clear Reports"
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    private function CheckIS3Report($facility): bool
    {
        $count = reports::where("id_facility",$facility->id)->count("id");
        if ($count >= 3){
            $photos = $facility->photos;
            $facility->delete();
            foreach ($photos as $photo)
            {
                unlink($photo->path_photo);
            }
            return true;
        }
        return false;
    }

    private function CheckBooking(int $iduser,int $idfacility): bool
    {
        $booking = bookings::where("id_user",$iduser)->where("id_facility",$idfacility)->first();
        return is_null($booking);
    }

}
