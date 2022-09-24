<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class favorites extends Model
{
    use HasFactory;
    protected $table = "favorites";
    protected $fillable =[
        "id_facility","id_user"
    ];
    public $timestamps = false;
    public function facilities_favorites(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(facilities::class,"id_facility","id")->withDefault();
    }
    public function users_favorites(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_user","id")->withDefault();
    }
}
