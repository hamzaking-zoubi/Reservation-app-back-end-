<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class chat extends Model
{
    use HasFactory;
    protected $table="chats";
    protected $fillable = ["id_send","id_recipient","message","read_at"];
    protected $hidden = ["updated_at"];
    public function user_send(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_send","id")->withDefault();
    }
    public function user_recipient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_recipient","id")->withDefault();
    }
}
