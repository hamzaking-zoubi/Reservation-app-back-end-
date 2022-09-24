<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class facilities extends Model
{
    use HasFactory;
    protected $table = "facilities";
    protected $fillable = [
        "id_user","name","location","description","available",
        "type","cost","rate","num_guest","num_room"
        ,"wifi","coffee_machine","air_condition","tv","fridge"
    ];
    protected $hidden = ["pivot"];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_user","id")->withDefault();
    }

    public function photos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(photos_fac::class,"id_facility","id");
    }

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(bookings::class,"id_facility","id");
    }
    public function favorites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(favorites::class,"id_facility","id");
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(review::class,"id_facility","id");
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(reports::class,"id_facility","id");
    }

    //with users
    public function review_users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            "reviews",
            "id_facility",
            "id_user",
            "id",
            "id",
        );
    }

    public function favorite_users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            "favorites",
            "id_facility",
            "id_user",
            "id",
            "id",
        );
    }

    public function bookings_users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            "bookings",
            "id_facility",
            "id_user",
            "id",
            "id",
        );
    }

    public function reports_users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            "reports",
            "id_facility",
            "id_user",
            "id",
            "id",
        );
    }
}
