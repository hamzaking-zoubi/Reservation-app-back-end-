<?php

namespace App\Http\Controllers\Api\RealTime;

use App\Class_Public\GeneralTrait;
use App\Events\ChatEvent;
use App\Events\ReadMessageEvent;
use App\Http\Controllers\Controller;
use App\Models\chat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpParser\Node\Expr\Cast\Object_;
use function PHPUnit\Framework\throwException;

class ChatController extends Controller
{
    use GeneralTrait;

    public function __construct()
    {
        $this->middleware("auth:userapi")->except("Ids");
        $this->middleware(["multi.auth:1|0"])->except("Ids");
    }

    /**
     * @throws \Throwable
     */
    public function send_message(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $validate = Validator::make($request->all(),[
                "id_recipient" => ["required",Rule::exists("users","id"),"numeric"],
                "message" => ["required"]
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $user_send_message = auth()->user()->rule;
            if($user_send_message==="0"){
                $user_recipient_message =  DB::table("users")
                    ->where("id",$request->id_recipient)
                    ->first()->rule;
            if($user_recipient_message!=="1")
                throw new \Exception("the user is send message to user not Owner");
            }
            else if($user_send_message==="1"){
                $user_recipient_message =  DB::table("users")
                    ->where("id",$request->id_recipient)
                    ->first()->rule;
                if($user_recipient_message!=="0")
                    throw new \Exception("the Owner is send message Owner not user");
            }
            else{
                throw new \Exception("the user is admin");
            }
            $message = chat::create([
               "id_send" => auth()->id(),
               "id_recipient"=>$request->id_recipient,
               "message" => $request->message,
            ]);
            broadcast(new ChatEvent($message));
            DB::commit();
            return response()->json([
                "id_message"=> $message->id,
                "created_at" => $message->created_at
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json(["Error"=>$exception->getMessage()]);
        }
    }
    /**
     * @throws \Throwable
     */
    public function read_message(Request $request): \Illuminate\Http\JsonResponse
    {
        $validate = Validator::make($request->all(),[
            "id_send" => ["required",Rule::exists("users","id"),"numeric"],
        ]);
        if($validate->fails()){
            return response()->json(["Error"=>$validate->errors()]);
        }
        $arr_messages = [];
        DB::beginTransaction();
        try {
            $messages_unRead = chat::where("id_send",$request->id_send)->where("read_at",null)->get()->toArray();
            foreach ($messages_unRead as $item){
                chat::where("id",$item["id"])->update([
                    "read_at" => Carbon::now()
                ]);
                $arr_messages[]=$item["id"];
            }
            //broadcast(new ReadMessageEvent($arr_messages,$request->id_send));
            DB::commit();
            return response()->json(["Success"=>"read messages Done!"]);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json(["Error"=>$exception->getMessage()]);
        }
    }

    public function Ids($user){
        $ids = DB::table("chats")
            ->select("id_recipient")
            ->where("id_send",$user)
            ->distinct();
        return DB::table("chats")
            ->select("id_send as id")
            ->where("id_recipient",$user)
            ->distinct()
            ->union($ids)->get();
    }

    public function GetIdsChats(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user()->id;
            $idsFinal = $this->Ids($user);
            foreach ($idsFinal as $chat){
                $chat->LastMessageDate = DB::table("chats")
                    ->select("created_at")
                    ->orderBy("id","desc")
                    ->WhereRaw("((id_send = ?) && (id_recipient = ?))", [$user,$chat->id])
                    ->orWhereRaw("((id_send = ?) && (id_recipient = ?))", [$chat->id, $user])
                    ->first()->created_at;
                $chat->UnReadMessages = DB::table("chats")
                    ->select("id")
                    ->WhereRaw("((id_send = ?) && (id_recipient = ?))", [$chat->id, $user])
                    ->whereNull("read_at")
                    ->count("id");
            }
            return response()->json(["users"=>$idsFinal]);
        }catch (\Exception $exception){
            return response()->json(["Error"=>$exception->getMessage()]);
        }
    }

    public function Show_all_chats(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user()->id;
            $idsFinal = $this->Ids($user);
            foreach ($idsFinal as $chat){
                ### Profile ###
                $chat->profile_rec = DB::table("users")
                    ->select(["users.name","users.status","profiles.path_photo"])
                    ->where("users.id",$chat->id)
                    ->join("profiles","users.id","=","profiles.id_user")
                    ->first();
                ### last message ###
                $chat->lastMessage = DB::table("chats")
                    ->select(["id as id_message","message","created_at"])
                    ->orderBy("id","desc")
                    ->whereRaw("(id_send = ?) && (id_recipient = ?)",[$user,$chat->id])
                    ->orWhereRaw("(id_send = ?) && (id_recipient = ?)",[$chat->id,$user])
                    ->first();
                $chat->countNotread = DB::table("chats")
                    ->select("id")
                    ->WhereRaw("((id_send = ?) && (id_recipient = ?))", [$chat->id, $user])
                    ->whereNull("read_at")
                    ->count("id");
            }
            return response()->json(["chats"=>$idsFinal]);
        }catch (\Exception $exception){
            return response()->json(["Error"=>$exception->getMessage()]);
        }
    }

    public function Info_User_Chat(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $chat = new class{
            };
            $validate = Validator::make($request->all(),[
                "id_user" => ["required",Rule::exists("users","id"),"numeric"]
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $chat->profile_rec = DB::table("users")
                ->select(["users.id","users.name","users.status","profiles.path_photo"])
                ->where("users.id",$request->id_user)
                ->join("profiles","users.id","=","profiles.id_user")
                ->first();
            $chat->lastMessage = DB::table("chats")
                ->select(["id as id_message","message","created_at"])
                ->orderBy("id","desc")
                ->whereRaw("(id_send = ?) && (id_recipient = ?)",[auth()->id(),$request->id_user])
                ->orWhereRaw("(id_send = ?) && (id_recipient = ?)",[$request->id_user,auth()->id()])
                ->first();
            $chat->countNotread = DB::table("chats")
                ->select("id")
                ->WhereRaw("((id_send = ?) && (id_recipient = ?))", [$request->id_user, auth()->id()])
                ->whereNull("read_at")
                ->count();
        return response()->json(["info_user" => $chat]);
        }catch (\Exception $exception){
            return response()->json(["Error"=>$exception->getMessage()]);
        }
    }

    public function show_messages(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validate = Validator::make($request->all(),[
                "id_recipient" => ["required",Rule::exists("users","id"),"numeric"],
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            $message_send = DB::table("chats")
                ->where("id_send",auth()->id())
                ->where("id_recipient",$request->id_recipient);
            $message_recipient = DB::table("chats")
                ->where("id_send",$request->id_recipient)
                ->where("id_recipient",auth()->id())
                ->union($message_send)
                ->orderBy("id","desc")
                ->paginate($this->NumberOfValues($request));
            return response()->json($this->Paginate("messages",$message_recipient));
        }
        catch (\Exception $exception){
            return response()->json(["Error"=>$exception->getMessage()]);
        }
    }

    /**
     * @throws \Throwable
     */
    public function delete_messages(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $validate = Validator::make($request->all(),[
                "id_message" => ["required","array"],
                "id_recipient" => ["required",Rule::exists("users","id"),"numeric"],
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
             DB::table("chats")
                ->whereIn("id",$request->id_message)
                ->where("id_send",auth()->id())
                ->where("id_recipient",$request->id_recipient)
                 ->delete();
            DB::table("chats")
                ->whereIn("id",$request->id_message)
                ->where("id_send",$request->id_recipient)
                ->where("id_recipient",auth()->id())
                ->delete();
            DB::commit();
            return response()->json(["Success"=>"Deleted Message Done!"]);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json(["Error"=>$exception->getMessage()]);
        }
    }

    /**
     * @throws \Throwable
     */
    public function destroy_chats(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $validate = Validator::make($request->all(),[
                "id_recipient" => ["required",Rule::exists("users","id"),"array"],
            ]);
            if($validate->fails()){
                return response()->json(["Error"=>$validate->errors()]);
            }
            foreach ($request->id_recipient as $item) {
            DB::table("chats")
                ->where("id_recipient",$item)
                ->where("id_send",auth()->id())
                ->delete();
            DB::table("chats")
                ->where("id_recipient",auth()->id())
                ->where("id_send",$item)
                ->delete();
            }
            DB::commit();
            return response()->json(["Success"=>"Destroy Chat Done!"]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(["Error"=>$exception->getMessage()]);
        }
    }
}
