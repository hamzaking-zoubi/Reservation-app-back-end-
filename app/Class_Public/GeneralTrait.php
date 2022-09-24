<?php

namespace App\Class_Public;

use App\Models\bookings;
use App\Models\User;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;


trait GeneralTrait
{
    public function NameImage_DefultPath():string{
        return "uploads/Users/defult_profile.png";
    }

    public function path_file (): string
    {
        return storage_path("app\\public\\TempCountsUsers.json");
    }

    public function Paginate(string $namedata,$paginate): array
    {
        return [
            $namedata=> $paginate->items(),
            "current_page" => $paginate->currentPage(),
            "url_next_page" => $paginate->nextPageUrl(),
            "url_first_page" => $paginate->path()."?page=1",
            "url_last_page" => $paginate->path()."?page=".$paginate->lastPage(),
            "total_pages" => $paginate->lastPage(),
            "total_items" => $paginate->total()
        ];
    }

    public function NumberOfValues(Request $request): int
    {
        try {
            if($request->has("num_values")&&is_numeric($request->num_values)&&$request->num_values>0){
                return $request->num_values;
            }
            throw new \Exception("");
        }catch (\Exception $exception){
            return 10;
        }
    }

    /**
     * @throws \Throwable
     */
    public function RefundToUser($facility){
        DB::beginTransaction();
        try {
            echo "sakmaskmaksamks";
            $owner = User::where("id",$facility->id_user)->first();
            $bookings = bookings::where("id_facility",$facility->id)->where("start_date",">",Carbon::now())->get()->toArray();
            $header = "Delete facility ".$facility->name;
            $body = "A booked facility has been deleted from the system, The cost of booking has been added back to your balance";
            foreach ($bookings as $booking){
                $user = User::where("id",$booking["id_user"])->first();
                $owner->decrement("amount",$booking["cost"]);
                $user->increment("amount",$booking["cost"]);
                $user->notify(new UserNotification($header,"Delete facility", $body,Carbon::now()));
            }
            $body = "Sorry Your facility has been deleted beacuase the number of reports exceeded 3";
            $owner->notify(new UserNotification($header,"Delete facility",$body,Carbon::now()));
            DB::commit();
            return 1;
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function Check_Date($datestr,$dateend): bool
    {
        $num = round(strtotime($dateend) - strtotime($datestr));
        if($num<0){
            return false;
        }
        return true;
    }

    public function GetJsonFile($path){
        $jsonString = file_get_contents($path);
        return json_decode($jsonString, true);
    }

    public function UpdateJsonFile($path,$newData){
        file_put_contents($path, json_encode($newData));
    }
}
