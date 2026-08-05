<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ConversationParticipant;
use App\Models\User;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function (User $user, int $conversationId) {
    // التحقق مما إذا كان المستخدم مشاركاً في هذه المحادثة
    return ConversationParticipant::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('shipment-tracking.{shipmentId}', function (User $user, int $shipmentId) {
    // السماح للمستخدم المسجل الدخول بمتابعة بث تتبع الشحنة
    return auth()->check();
});