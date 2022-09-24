<?php

namespace App\Http\Controllers\Api\facilities;

use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
    use GeneralTrait;
    private $num_values = null;
    private $facilities;

    public function __construct(){
        $this->facilities = DB::table("facilities")
            ->select();
//
    }
    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SearchFullAttr(Request $request): \Illuminate\Http\JsonResponse
    {
        $validate = $this->Validate_IsTrue($request);
        $start_date = $request->start_date  ?? null;
        $end_date =  $request->end_date ?? null;
        if($validate->fails()){
            return response()->json(["Error"=>$validate->errors()]);
        }
        if($start_date!==null&&$end_date!==null){
            if(!$this->Check_Date($start_date,$end_date)){
                return response()->json(["Error"=>"The Problem in Date"]);
            }
        }
        try {
        $this->num_values = $this->NumberOfValues($request);
        $this->facilities = $this->Available();
        $this->facilities = $this->Location($request);
        $this->facilities = $this->Type($request);
        $this->facilities = $this->Cost($request);
        $this->facilities = $this->BetweenCost($request);
        $this->facilities = $this->BestRate($request);
        $this->facilities = $this->Rate($request);
        $this->facilities = $this->Num_Guest($request);
        $this->facilities = $this->Num_Room($request);
        $this->facilities = $this->Wifi($request);
        $this->facilities = $this->TV($request);
        $this->facilities = $this->Fridge($request);
        $this->facilities = $this->AirCondition($request);
        $this->facilities = $this->CoffeeMachine($request);
        $finalSearch = $this->Date($request)
            ->orderBy("facilities.".$this->Order($request),"desc")
            ->paginate($this->num_values);
        $FinalAllData = $this->Paginate("facilities",$finalSearch);
        foreach ($FinalAllData["facilities"] as $item){
            $item->photos = DB::table("photos_facility")
                ->select(["photos_facility.id as id_photo","photos_facility.path_photo"])
                ->where("photos_facility.id_facility",$item->id)
                ->get();
            $user = auth("userapi")->user();
            if($user!=null){
                $fav = DB::table("favorites")->where("id_facility",$item->id)->first();
                if($fav!==null){
                    $item->favorite = true;
                }
            }
        }
        return response()->json($FinalAllData);
        }catch (\Exception $exception){
            return response()->json([
                "Error" => $exception->getMessage()
            ]);
        }
    }

    public function Validate_IsTrue(Request $request){
        return Validator::make($request->all(),[
            "num_values" => ["nullable","numeric"],
            //order : id ,rate, cost,num_guest,num_room
            "order" =>["nullable","string"],
            "location" => ["nullable","string"],
            "type" => ["nullable","array",Rule::in(["hostel","chalet","farmer"])],
            "cost" => ["nullable","numeric"],
            "cost1" => ["nullable","numeric"],
            "cost2" => ["nullable","numeric"],
            "rate" =>["nullable","numeric"],
            "bestrate" => ["nullable","boolean"],
            "num_guest" => ["nullable","numeric"],
            "num_room" => ["nullable","numeric"],
            "wifi"=>["nullable","boolean"],
            "coffee_machine"=>["nullable","boolean"],
            "air_condition"=>["nullable","boolean"],
            "tv"=>["nullable","boolean"],
            "fridge"=>["nullable","boolean"],
            "start_date" => ["nullable","date"],
            "end_date" => ["nullable","date"]
        ]);
    }

    public function Available(): \Illuminate\Database\Query\Builder
    {
        if(!is_null(auth("userapi")->user())){
                echo "maksm";
            if(auth("userapi")->user()->rule!=="2"){
                return $this->facilities->where("available",true);
            }
            else
            {
                return $this->facilities;
            }
        }
        return $this->facilities->where("available",true);
    }

    public function Order(Request $request)
    {
        //order : id ,rate, cost,num_guest,num_room
        $arrOrder = ["id" ,"rate", "cost","num_guest","num_room"];
        if(in_array($request->order,$arrOrder)){
            return $request->order;
        }
        else
            return "rate";
    }

    public function Date(Request $request){
        $start_date = $request->start_date ?? null;
        $end_date = $request->end_date ?? null;
        if ($start_date !== null && $end_date !== null) {
            $GetIdsAvailable = [];
            $IdsNotBooking = [];
            $IdsTemp = [];
            $ids_Bookings = DB::table("bookings")->select(["id_facility",DB::raw("count(bookings.id_facility) as count")])
            ->groupBy(["id_facility"])->get()->toArray();
            foreach ($ids_Bookings as $booking){
                $temp = DB::table("bookings")
                    ->select(["bookings.start_date","bookings.end_date","bookings.id_facility"])
                    ->where("id_facility",$booking->id_facility)
                    ->whereNotBetween("bookings.start_date",[$start_date,$end_date])
                    ->whereNotBetween("bookings.end_date",[$start_date,$end_date])
                    ->WhereRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$start_date])
                    ->WhereRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$end_date])
                    ->whereRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$start_date,$start_date])
                    ->whereRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$end_date,$end_date])
                    ->count();
                if($temp===$booking->count){
                    $GetIdsAvailable[] = $booking->id_facility;
                }
                $IdsTemp[]=$booking->id_facility;
            }
            foreach ( DB::table("facilities")
                          ->select(["facilities.id"])
                          ->whereNotIn("facilities.id",$IdsTemp)
                          ->get() as $item) {
                $IdsNotBooking[] = $item->id;
            }
            foreach ($GetIdsAvailable as $item){
                $IdsNotBooking[] = $item;
            }
            return $this->facilities->whereIn("id",$IdsNotBooking);
        }
        return $this->facilities;
    }

    public function Location(Request $request): \Illuminate\Database\Query\Builder
    {
        $location = $request->location??null;
        $test = clone $this->facilities;
        if($location!==null){
            return $test->where("facilities.location","like","%".$location."%");
        }
        return $this->facilities;
    }

    public function Type(Request $request): \Illuminate\Database\Query\Builder
    {
        $type = $request->type??null;
        $test = clone $this->facilities;
        if($type!==null){
            return $test->whereIn("facilities.type",$type);
        }
        return $this->facilities;
    }

    public function Cost(Request $request): \Illuminate\Database\Query\Builder
    {
        $cost = $request->cost??null;
        $test = clone $this->facilities;
        if($cost!==null){
            $cost= (float)$cost;
            return $test->where("facilities.cost",$cost);
        }
        return $this->facilities;
    }

    public function BetweenCost(Request $request): \Illuminate\Database\Query\Builder
    {
        $c1 = $request->cost1 ?? null;
        $c2 = $request->cost2 ?? null;
        $test = clone $this->facilities;
        if($c1!==null&&$c2!==null) {
            return $test->whereBetween("facilities.cost",[$c1,$c2]);
        }
        return $this->facilities;
    }

    public function Rate(Request $request): \Illuminate\Database\Query\Builder
    {
        $rate = $request->rate??null;
        $test = clone $this->facilities;
        if($rate!==null){
            return $test->where("facilities.rate",$rate);
        }
        return $this->facilities;
    }

    public function BestRate(Request $request): \Illuminate\Database\Query\Builder
    {
        $Brate = $request->bestrate ?? null;
        $test = clone $this->facilities;
        if($Brate===true){
            $max = $this->facilities->max("rate");
            return $test->where("facilities.rate",$max);
        }
        return $this->facilities;
    }

    public function Num_Guest(Request $request): \Illuminate\Database\Query\Builder
    {
        $num_guest = $request->num_guest ?? null;
        $Operators = $request->operator_guest ?? "=";
        $test = clone $this->facilities;
        if($num_guest){
            return $test->where("facilities.num_guest",$Operators,$num_guest);
        }
        return $this->facilities;
    }

    public function Num_Room(Request $request): \Illuminate\Database\Query\Builder
    {
        $num_room = $request->num_room ?? null;
        $Operators = $request->operator_room ?? "=";
        $test = clone $this->facilities;
        if($num_room!==null){
            return $test->where("facilities.num_room",$Operators,$num_room);
        }
        return $this->facilities;
    }

    public function Wifi(Request $request): \Illuminate\Database\Query\Builder
    {
        $wifi = $request->wifi ?? null;
        $test = clone $this->facilities;
        if($wifi!==null){
            return $test->where("facilities.wifi","=",$wifi);
        }
        return $this->facilities;
    }

    public function CoffeeMachine(Request $request): \Illuminate\Database\Query\Builder
    {
        $coffee_machine = $request->coffee_machine ?? null;
        $test = clone $this->facilities;
        if($coffee_machine!==null){
            return $test->where("facilities.coffee_machine","=",$coffee_machine);
        }
        return $this->facilities;
    }

    public function AirCondition(Request $request): \Illuminate\Database\Query\Builder
    {
        $air_condition = $request->air_condition ?? null;
        $test = clone $this->facilities;
        if($air_condition!==null){
            return $test->where("facilities.air_condition","=",$air_condition);
        }
        return $this->facilities;
    }

    public function TV(Request $request): \Illuminate\Database\Query\Builder
    {
        $tv = $request->tv ?? null;
        $test = clone $this->facilities;
        if($tv!==null){
            return $test->where("facilities.tv","=",$tv);
        }
        return $this->facilities;
    }

    public function Fridge(Request $request): \Illuminate\Database\Query\Builder
    {
        $fridge = $request->fridge ?? null;
        $test = clone $this->facilities;
        if($fridge!==null){
            return $test->where("facilities.fridge","=",$fridge);
        }
        return $this->facilities;
    }

}



//    public function SearchByDate(Request $request): \Illuminate\Database\Query\Builder
//    {
//        try {
//            $start_date = $request->start_date ?? null;
//            $end_date = $request->end_date ?? null;
//            $arr_id_fac = [];
//            $arr_id_fac1 = [];
//            $arr_id_fac2 = [];
//            if ($start_date !== null && $end_date !== null) {
//                //availbale booking in date
////                foreach (DB::table("bookings")
////                    ->select(["bookings.start_date","bookings.end_date","bookings.id_facility"])
////                    ->groupBy("bookings.id_facility")
////                    ->havingBetween("bookings.start_date",[$start_date,$end_date],"and",true)
////                    ->havingBetween("bookings.end_date",[$start_date,$end_date],"and",true)
////                    ->havingRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$start_date])
////                    ->havingRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$end_date])
////                    ->havingRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$start_date,$start_date])
////                    ->havingRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$end_date,$end_date])
////                    ->get() as $item)
////                {
////                    $arr_id_fac [] = $item->id_facility;
////                }
////                dd($arr_id_fac);
////
////                $Bookings_ids_group = DB::table("bookings")
////                    ->select(["bookings.id_facility"])
////                    ->get()->count();
//
////                dd($Bookings_ids_group);
//
////                $temp_count_bookings = DB::table("bookings")->count();
////
////                $GetBookings = DB::table("bookings")
////                    ->select(["bookings.start_date","bookings.end_date","bookings.id_facility"])
////                    ->whereNotBetween("bookings.start_date",[$start_date,$end_date])
////                    ->whereNotBetween("bookings.end_date",[$start_date,$end_date])
////                    ->WhereRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$start_date])
////                    ->WhereRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$end_date])
////                    ->whereRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$start_date,$start_date])
////                    ->whereRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$end_date,$end_date])
////                    ->get()->toArray();
//
//
////                ***********************
////                foreach ( DB::table("facilities")
////                              ->select(["bookings.start_date","bookings.end_date","bookings.id_facility"])
////                              ->whereNotBetween("bookings.start_date",[$start_date,$end_date])
////                              ->whereNotBetween("bookings.end_date",[$start_date,$end_date])
////                              ->WhereRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$start_date])
////                              ->WhereRaw("Not( ? between  bookings.start_date and bookings.end_date ) ",[$end_date])
////                              ->whereRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$start_date,$start_date])
////                              ->whereRaw("( (? != bookings.start_date) and (? != bookings.end_date) )",[$end_date,$end_date])
////                              ->get() as $item){
////                    $arr_id_fac [] = $item->id_facility;
////                }
////                dd($arr_id_fac);
//                foreach ( DB::table("bookings")
//                             ->select(["bookings.id_facility"])
//                             ->distinct()
//                             ->get() as $item){
//                    $arr_id_fac1 [] = $item->id_facility;
//                }
//                foreach ( DB::table("facilities")
//                             ->select(["facilities.id"])
//                             ->whereNotIn("facilities.id",$arr_id_fac1)
//                             ->get() as $item) {
//                    $arr_id_fac2[] = $item->id;
//                }
//                foreach ($arr_id_fac as $item){
//                    $arr_id_fac2[] = $item;
//                }
//                return $this->facilities->whereIn("id",$arr_id_fac2);
//            }
//            return $this->facilities;
//        }catch (\Exception $exception){
//            return $this->facilities;
//        }
////            $temp = clone $test
////                ->select("facilities.id")
////                ->join("bookings","facilities.id","=","bookings.id_facility")
////                ->select(["bookings.start_date","bookings.end_date","bookings.id_facility"])
////                ->where("bookings.end_date",">",$start_date)
////                ->Where("bookings.start_date",">",$end_date)
////                ->WhereRaw("( ('bookings.start_date' > ?) and ('bookings.end_date' > ?) ) ",[$start_date,$end_date])
////                ->get();
//    }
