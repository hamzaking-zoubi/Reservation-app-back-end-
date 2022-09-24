<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reports extends Model
{
    use HasFactory;
    protected $table = "reports";
    protected $fillable =[
        "id_facility","id_user","report"
    ];
    public function facilities_reports(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(facilities::class,"id_facility","id")->withDefault();
    }
    public function users_reports(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_user","id")->withDefault();
    }
}
