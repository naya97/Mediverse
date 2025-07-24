<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $table = "chats";
    protected $guarded = ['id'];

    public function participants() : HasMany{
        return $this->hasMany(ChatParticipant::class,'chat_id');
    }
    public function messages(){
        return $this->hasMany(ChatMessage::class,'chat_id');
    }
    public function lastMessage(){
        return $this->hasOne(ChatMessage::class,'chat_id')->latestOfMany('updated_at');
    }
    public function scopeHasParticipant($query, int $userId){
        return $query->whereHas('participants', function($q) use ($userId){
            $q->where('user_id',$userId);
        });
    }
}
