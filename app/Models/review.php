<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class review extends Model
{
    use HasFactory;
    protected $table = "reviews";
    protected $fillable =[
        "id_facility","id_user","comment","rate"
    ];
    public function facilities_reviews(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(facilities::class,"id_facility","id")->withDefault();
    }
    public function users_reviews(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_user","id")->withDefault();
    }
}
