<?php

namespace App\Http\Controllers\Api\facilities;

use App\Class_Public\GeneralTrait;
use App\Http\Controllers\Controller;

use App\Models\facilities;
use App\Models\photos_fac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FacilitiesController extends Controller{
    use GeneralTrait;

    public function __construct()
    {
        //owner=>1
        //user=>0
        //admine=>2
        $this->middleware(["auth:userapi","multi.auth:1|2"])->only(["deleteOneImage","addListImage",
            "delete","store","update","index","deleteAllImage","status"]);
        $this->middleware(["auth:userapi"])->only(["show","indexAll"]);
    }
    public  function  indexAll(){
        $facility=facilities::with('photos')->get();
        return response([
            'Data'=>$facility
        ]);
    }
    public  function  index(){
        try{
            //  $facility=facilities::with('photos')->get();
            // $facility=$facility->where('user_id',Auth::id());
            $facility=Auth::user()->user_facilities()->with('photos')->get();
            return response([
                'Data'=>$facility
            ]);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }

    }
    public  function  show($id){
        try{
            $facility=facilities::where(["id"=>$id])->with("photos")->where("id",$id)->first();
        if($facility!=null)
        {
            return response([
                'Data'=>$facility
            ]);
        }else{

            return response([
                'Data'=>'no id facility'
            ]);
        }
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage(),
                "message" => 'no id'
            ],401);
        }
    }
    public  function  delete($id)
    {
        DB::beginTransaction();
        try{
            $user = auth()->user()->rule;
            if($user==="2"){
                echo "asmsakmsak";
                $facility = facilities::where(["id"=>$id])->first();
                if($facility!=null)
                {
                    $this->RefundToUser($facility);
                    $id_photo= $facility->photos;
                    $facility->delete();
                    foreach ($id_photo as $path)
                    {
                        unlink($path->path_photo);
                    }
                    DB::commit();
                    return response(['message'=>'facility deleted successfully']);
                }else{
                    DB::rollback();
                    return response(['message'=>'facility not found']);
                }

            }else{
            $facility= Auth::user()->user_facilities()->where("id",$id)->first();
            if($facility!=null)
            {
                $this->RefundToUser($facility);
                $id_photo= $facility->photos;
                $facility->delete();
                foreach ($id_photo as $path)
                {
                    unlink($path->path_photo);
                }
                DB::commit();
                return response(['message'=>'facility deleted successfully']);
            }else{
                DB::rollback();
                return response(['message'=>'facility not found']);
            }
            }
        }catch  (\Exception $exception){
            DB::rollback();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        } catch (\Throwable $e) {
        }
    }
    public function   store(Request  $request){

        $validator=Validator::make($request->all(),[
            "name"=>["required","string"],
            "location"=>["required","string"],
            "description"=>["required","string"],
            "photo_list"=>["required","array"],
            "type"=>["required",Rule::in(["hostel","chalet","farmer"])],

            "cost"=>["required","numeric"],
            "num_guest"=>["required","numeric"],
            "num_room"=>["required","numeric"],

            "wifi"=>["required","string"],
            "coffee_machine"=>["required","string"],
            "air_condition"=>["required","string"],
            "tv"=>["required","string"],
            "fridge"=>["required","string"],
        ]);
        if($validator->fails())
        {
            return response()->json([
                "error"=>$validator->errors()
            ] );
        }
        if(!$request->hasFile('photo_list')) {
            return response()->json(['upload_file_not_found'], 400);
        }
        DB::beginTransaction();
        try {
            $facility =new facilities();

            if($request->air_condition=='true' || $request->coffee_machine==1) {
                $facility->air_condition = true;
            }else {
                $facility->air_condition=false;
            }

            if($request->coffee_machine=='true' || $request->coffee_machine==1) {
                $facility->coffee_machine = true;
            }else {
                $facility->coffee_machine=false;
            }

            if($request->wifi=='true' || $request->wifi==1) {
                $facility->wifi=true;
            }else{
                $facility->wifi=false;
            }

            if ($request->fridge=='true' || $request->fridge==1) {
                $facility->fridge=true;
            }else{
                $facility->fridge=false;
            }
            if ($request->tv=='true' || $request->tv==1) {
                $facility->tv=true;
            }else{
                $facility->tv=false;
            }
            $facility ->name=$request->name;
            $facility   ->location=$request->location;
            $facility  ->description=$request->description;
            $facility  ->type=$request->type;
            $facility  ->cost= $request->cost;
            $facility  ->num_guest=$request->num_guest;
            $facility   ->num_room=$request->num_room;
            $facility   ->id_user=Auth::id();
            $facility->rate = 1;
            $facility->save();

//                $facility = \auth()->user()->user_facilities()->create([
//                "name"=>$request->name,
//                "location"=>$request->location,
//                "description"=>$request->description,
//                "type"=>$request->type,
//                "cost"=> $request->cost,
//                "num_guest"=>$request->num_guest,
//                "num_room"=>$request->num_room,
            //    "air_condition"=>$request->air_condition,
            // "coffee_machine"=>$request->coffee_machine=='true'?true:false,
//                "tv"=>$request->tv=='true'?true:false,
//                "wifi"=>$request->wifi=='true'?true:false,
//                "fridge"=>$request->fridge=='true'?true:false
            //  ]);
            $photoList=$request->file('photo_list');
            foreach ($photoList as $photo){
                $newPhoto = time().$photo->getClientOriginalName();
                $facility->photos()->create([
                    "path_photo"=>'uploads/facility/'.$newPhoto,
                ]);
                $photo->move('uploads/facility',$newPhoto);
            }
            DB::commit();
            return response()->json([
                "message" =>"Facility created successfully",
                "data" => "facility"
            ],201);
        }catch (\Exception $exception){
            DB::rollBack();

            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function   update(Request  $request){

        $validator=Validator::make($request->all(),[
            "id"=>["required",Rule::exists("facilities","id")],
            "name"=>["required","string"],
            "location"=>["required","string"],
            "description"=>["required","string"],
            // "photo_list"=>["required","array"],
            "type"=>["required",Rule::in(["hostel","chalet","farmer"])],
            "cost"=>["required","numeric"],
            "num_guest"=>["required","numeric"],
            "num_room"=>["required","numeric"],
            "wifi"=>["required","boolean"],
            "coffee_machine"=>["required","boolean"],
            "air_condition"=>["required","boolean"],
            "tv"=>["required","boolean"],
            "fridge"=>["required","boolean"],
        ]);
        if($validator->fails()){
            return response()->json([
                "error"=>$validator->errors()
            ]);
        }

        DB::beginTransaction();
        try{
            $facility = \auth()->user()->user_facilities()->where("id",$request->id)->first()->update([
                "name"=>$request->name,
                "location"=>$request->location,
                "description"=>$request->description,
                "type"=>$request->type,
                "cost"=>$request->cost,
                "num_guest"=>$request->num_guest,
                "num_room"=>$request->num_room,
                "air_condition"=>$request->air_condition,
                "coffee_machine"=>$request->coffee_machine,
                "tv"=>$request->tv,
                "wifi"=>$request->wifi,
                "fridge"=>$request->fridge
            ]);
            DB::commit();
            return response()->json([
                "status" => true,
                "message" => "facility data has been updated",
                "data"=>$facility
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public  function  deleteOneImage($id){
        DB::beginTransaction();
        try{
            $_photo= photos_fac::find($id);

            if($_photo!=null)
            {
                $teamp=clone $_photo;
                $_photo->delete();
                unlink($teamp->path_photo);
                DB::commit();
                return response(['message'=>'facility deleted successfully']);
            }else{
                return response(['message'=>'facility not found']);
            }
        }catch  (\Exception $exception){
            DB::rollback();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function  addListImage(Request $request )
    {
        $validator=Validator::make($request->all(), [
            "id" => "required",
            "photo_list" => ["required", "array"],
        ]);
        if($validator->fails()){
            return response()->json([
                "error"=>$validator->errors()
            ]);
        }
        if(!$request->hasFile('photo_list')) {
            return response()->json(['upload_file_not_found'], 401);
        }
        try{
            DB::beginTransaction();
            $facility = \auth()->user()->user_facilities()->where("id",$request->id)->first();
            if($facility!=null){
                $photoList = $request->file('photo_list');
                foreach ($photoList as $photo) {
                    $newPhoto = time() . $photo->getClientOriginalName();
                    $facility->photos()->create([
                        "path_photo" => 'uploads/facility/' . $newPhoto,
                    ]);
                    $photo->move('uploads/facility', $newPhoto);
                    DB::commit();
                }
                return response()->json([
                    "status" => true,
                    "message" => "image list add successfully"
                ]);
            }
            else{
                return response()->json([
                    "status" => false,
                    "message" => "facility nt found"
                ]);
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => "created fails"
            ]);

        }
    }
    public function deleteAllImage($id){
        $_photo= photos_fac::where("id_facility",$id);
        if($_photo!==null){
        $pa=clone  $_photo->get();
        foreach ($pa as $path)
        {
            unlink($path->path_photo);
        }
        $_photo->delete();
        }
    }

    public function status(Request  $request){
        $validator = Validator::make($request->all(),[
            'id_facility'=>['required',Rule::exists("facilities","id")],
        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()]);
        }
        $facility = \auth()->user()->user_facilities()->where("id",$request->id_facility)->first();
        if(!is_null($facility) && $facility->available==true ){
            $facility->update([
                "available"=>0
            ]);
        } else {
            if (!is_null($facility) && $facility->available == false) {
                $facility->update([
                    "available"=>1
                ]);
            }
        }
        return  response()->json([
            'message'=> "toggle status success"  ,
            'Date'=>$facility->available
        ]);
    }
}
