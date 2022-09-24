<?php

namespace Database\Seeders;

use App\Models\bookings;
use App\Models\chat;
use App\Models\facilities;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Nette\Utils\Random;

class Create_Facilities extends Seeder
{
    private function rrr()
    {
        $arr = ["hostel","chalet","farmer"];
        return $arr[rand(0,2)];
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 80;$i<=100;$i++){
            Profile::create([
                "id_user" => $i,
                "path_photo"=>"uploads/Users/defult_profile.png"
            ]);
//        chat::create([
//            "id_send"=>$i,
//            "id_recipient"=>44,
//            "message"=>"sakmsakmskmas"
//        ]);
        }
//        bookings::create([
//            "id_user" => 4,
//            "id_facility" => 1,
//            "cost" => (float)rand(100,440),
//            //YYYY-MM-DD HH:MM:SS
//            "start_date" => "2022-06-01",
//            "end_date" => "2022-06-06"
//        ]);
//        for ($i=1;$i<=50;$i++){
//            $name = \Str::random(10);
//            User::create([
//                "name"=>$name,
//                "email"=>$name."@".$name,
//                "password"=>"12345678",
//                "rule" => (string) rand(0,2),
//                "amount" => (float)rand(500,1000),
//            ]);
//        }
//        for($i=1;$i<2000;$i++)
//        {
//            $name = \Str::random(10);
//            facilities::create([
//                "id_user" =>rand(25,30),
//                "name"=> $name,
//                "location"=> $name."location",
//                "description"=> $name."description",
//                "type"=> $this->rrr(),
//                "cost"=> (float) rand(100,1000),
//                "rate"=> (integer) rand(1,5),
//                "num_guest"=> (integer) rand(1,5),
//                "num_room"=> (integer) rand(1,5),
//                "wifi"=>(bool) rand(true,false),
//            ]);
//        }
    }
}
