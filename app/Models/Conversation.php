<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shipment;
use App\Models\ConversationParticipant;

use App\Models\Message;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'type',
        'channel',
        'status',
    ];

    /**
     * علاقة المحادثة بالشحنة (إن وجدت)
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }


    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id')
                    ->withPivot('last_read_at', 'joined_at');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany();
    }

    public function unreadCountForUser($userId)
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        $lastReadAt = $participant ? $participant->last_read_at : null;

        $query = $this->messages()->where('sender_id', '!=', $userId);
        if ($lastReadAt) {
            $query->where('created_at', '>', $lastReadAt);
        }
        return $query->count();
    }
}