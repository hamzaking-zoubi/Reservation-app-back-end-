<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    //users
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'amount',
        'rule',"password","status"
    ];
    protected $hidden = [
        'password',
        "status",
        "created_at",
        "updated_at","email_verified_at"
    ];

    ##### Profile #####
    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Profile::class,"id_user","id");
    }
    ##### Start chat #####
    public function user_send_messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(chat::class,"id_send","id");
    }
    public function user_recipient_messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(chat::class,"id_recipient","id");
    }
    ##### End chat #####

    ##### Start One To Many #####
    public function user_facilities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(facilities::class,"id_user","id");
    }

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(bookings::class,"id_user","id");
    }
    public function favorites(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(favorites::class,"id_user","id");
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(review::class,"id_user","id");
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(reports::class,"id_user","id");
    }
    ##### End One To Many #####


    ##### Start Many To Many #####
    public function review_facilities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            facilities::class,
            "reviews",
            "id_user",
            "id_facility",
            "id",
            "id",
        );
    }
    public function favorite_facilities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            facilities::class,
            "favorites",
            "id_user",
            "id_facility",
            "id",
            "id",
        );
    }

    public function bookings_facilities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            facilities::class,
            "bookings",
            "id_user",
            "id_facility",
            "id",
            "id",
        );
    }
    public function reports_facilities(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            facilities::class,
            "reports",
            "id_user",
            "id_facility",
            "id",
            "id",
        );
    }
    ##### End Many To Many #####

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'User.Notify.'.$this->id;
    }
}
