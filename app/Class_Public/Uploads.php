<?php

namespace App\Class_Public;

class Uploads
{
    public function save($file)
    {
        if($file!==null){
            if($file->isValid()){
                return $file->store("/",[
                    "disk" => "photo_user"
                ]);
            }
        }
        return null;
    }
}
