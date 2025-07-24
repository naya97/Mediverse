<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatParticipant extends Model
{
    protected $table = "chat_participants";
    protected $guarded = ['id'];

    public function user() : BelongsTo{
        return $this->belongsTo(User::class, 'user_id');
    }
}
