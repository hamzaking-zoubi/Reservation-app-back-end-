<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $primaryKey = "id_user";
    protected $table = "profiles";
    protected $fillable =[
        "gender","id_user","path_photo","age","phone"
    ];
    public $timestamps = false;
    protected $casts = [
        "age" => "date"
    ];
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_user","id");
    }
}
