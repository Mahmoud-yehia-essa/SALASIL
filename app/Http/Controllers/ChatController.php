<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message_type'    => 'required|in:text,image,audio,location,file,system_alert',
            'content'         => 'required_if:message_type,text|nullable|string',
            'metadata'        => 'nullable|array',
            'reply_to_id'     => 'nullable|exists:messages,id',
        ]);

        // 1. حفظ الرسالة في قاعدة البيانات
        $message = Message::create([
            'conversation_id' => $validated['conversation_id'],
            'sender_id'       => auth()->id(), // المستخدم الحالي
            'message_type'    => $validated['message_type'],
            'content'         => $validated['content'] ?? null,
            'metadata'        => $validated['metadata'] ?? null,
            'reply_to_id'     => $validated['reply_to_id'] ?? null,
            'delivery_status' => 'sent',
        ]);

        // 2. إطلاق البث المباشر عبر Reverb (في حال عمل السيرفر)
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Chat message broadcast skipped or failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $message->load('sender:id,name'),
        ]);
    }
}