<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Conversation;
use App\Models\User;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message_type',
        'content',
        'metadata',
        'reply_to_id',
        'is_read',
        'external_message_id',
        'delivery_status',
    ];

    protected $casts = [
        'metadata' => 'array', // لتحويل JSON تلقائياً إلى مصفوفة
        'is_read' => 'boolean',
    ];

    /**
     * علاقة الرسالة بالمحادثة
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * علاقة الرسالة بالمرسل
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * علاقة الرسالة بالرسالة الأصلية (في حال كانت رداً)
     */
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * الردود المرتبطة بهذه الرسالة
     */
    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }
}