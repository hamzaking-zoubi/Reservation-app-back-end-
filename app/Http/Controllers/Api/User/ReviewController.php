<?php

namespace App\Http\Controllers\Api\User;

use App\Class_Public\DataInNotifiy;
use App\Class_Public\GeneralTrait;
use App\Events\CommentEvent;
use App\Http\Controllers\Controller;
use App\Models\facilities;
use App\Models\Profile;
use App\Models\review;
use App\Models\User;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    use GeneralTrait;
    public function __construct()
    {
        $this->middleware(["auth:userapi","multi.auth:0"])->except("ShowReviewAll");
        $this->middleware(["auth:userapi","multi.auth:0|2"])->only("DeleteReview");
    }

    /**
     * @throws \Throwable
     */

    public function ShowReviewAll(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(),[
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")],
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $reviews = review::where("reviews.id_facility",$request->id_facility)
                ->orderBy("reviews.id")
                ->paginate($this->NumberOfValues($request));
            $reviews = $this->Paginate("reviews",$reviews);

            foreach ($reviews["reviews"] as $item){
                $temp_user = User::where("users.id",$item->id_user)->first();
                $item->user = [
                    "name"=> $temp_user->name ?? null,
                    "path_photo"=> $temp_user->profile->path_photo ?? null
                    ];
            }
            return \response()->json($reviews);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    public function CreateReviewRating(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $validate = Validator::make($request->all(),[
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")],
                "rate" => ["required","numeric","min:1","max:5"]
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            if($this->CheckCanReview($user,$request->id_facility)===true){
                $review = review::updateOrCreate([
                    "id_facility"=>$request->id_facility,
                    "id_user"=>$user->id
                ],[
                    "id_facility"=>$request->id_facility,
                    "id_user"=>$user->id,
                    "rate"=>$request->rate
                ]);
                $this->UpdateRateFacility($request->id_facility);
                DB::commit();
                return \response()->json([
                    "review" => $review
                ]);
            }
            else{
                Throw new \Exception("It is not possible to make an evaluation due to not making a reservation in advance");
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    /**
     * @throws \Throwable
     */
    public function CreateReviewComment(Request $request): \Illuminate\Http\JsonResponse{
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $validate = Validator::make($request->all(),[
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")],
                "comment" => ["required","string"]
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $facility = facilities::where("id",$request->id_facility)->first();
            $owner = User::where("id",$facility->id_user)->first();
            $review = $user->reviews()->where("id_facility",$request->id_facility)->where("id_user",$user->id)->first();
            if(!is_null($review)){
                $review->update(
                    [
                    "comment"=>$request->comment
                ]);
                $header = "Comment facility ".$facility->name;
                $body = $user->name." has Reviewed the Facility";
                $body_request = ["id_facility"=>$facility->id];
                $Data = new DataInNotifiy("/user/review/show",$body_request,"GET");
                broadcast(new CommentEvent($user->name, $user->profile->path_photo,$review));
                $owner->notify(new UserNotification($header,"Comment facility", $body,Carbon::now(),$Data));
                DB::commit();
                return \response()->json([
                    "review" => $review
                ]);
            }
            else{
                Throw new \Exception("You Dont make Rating");
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function GetReview(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            $validate = Validator::make($request->all(),[
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")],
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $review = $user->reviews()->where("reviews.id_facility",$request->id_facility)->first();
            return \response()->json([
                "review" => $review
            ]);
        }catch (\Exception $exception){
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }
    public function DeleteReview(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $validate = Validator::make($request->all(),[
                "id_review"=>["required","numeric",Rule::exists("reviews","id")],
                "id_facility"=>["required","numeric",Rule::exists("facilities","id")]
            ]);
            if($validate->fails()){
                return \response()->json([
                    "Error" => $validate->errors()
                ],401);
            }
            $user = auth()->user();
            $rev = null;
            if($user->rule==="2"){
               $rev = review::where("id",$request->id_review)
                    ->where("id_facility",$request->id_facility)->first();
               if(is_null($rev)){
                   Throw new \Exception("The Review is Not Found :(");
               }
               $rev->delete();
            }
            else{
                $rev = review::where("id",$request->id_review)
                    ->where("id_facility",$request->id_facility)->where("id_user",$user->id)->first();
                if(is_null($rev)){
                    Throw new \Exception("The Review is Not Found :(");
                }
                $rev->delete();
            }
            $this->UpdateRateFacility($request->id_facility);
            DB::commit();
            return \response()->json([
                "message" => "Success Delete Reviews"
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return \response()->json([
                "Error" => $exception->getMessage()
            ],401);
        }
    }

    private function UpdateRateFacility($id_fac){
        $facility = facilities::where("id",$id_fac)->first();
        $avg = review::where("id_facility",$facility->id)->avg("rate");
        $facility->update([
            "rate" => $avg
        ]);
    }

    private function CheckCanReview($user,$id_fac):bool{
        $temp = $user->bookings()->where("id_facility",$id_fac)->first();
        return !is_null($temp);
    }
}
