<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bookings extends Model
{
    use HasFactory;
    protected $table = "bookings";
    protected $fillable =[
        "id_facility","id_user","cost",
        "start_date","end_date"
    ];
    protected $casts = [
        "start_date"=>"date",
        "end_date"=>"date"
    ];
    protected $hidden=["updated_at","state"];

    //datetime
    //YYYY-MM-DD HH:MM:SS
    public function facilities_bookings(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(facilities::class,"id_facility","id")->withDefault();
    }
    public function users_bookings(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_user","id")->withDefault();
    }
}
