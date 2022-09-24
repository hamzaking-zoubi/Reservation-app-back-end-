<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class photos_fac extends Model
{
    use HasFactory;
    protected $table = "photos_facility";
    protected $fillable =[
        "id_facility","path_photo"
    ];
    public $timestamps = false;

    public function facility(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(facilities::class,"id_facility","id")->withDefault();
    }
}
