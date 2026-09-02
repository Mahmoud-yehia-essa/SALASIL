<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatController extends Controller
{
    /**
     * List all conversations for the authenticated user (or all for admin).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Conversation::with([
            'shipment:id,shipment_name,status',
            'users:id,fname,lname,email,phone,photo,role,client_code',
            'participants.user:id,fname,lname,photo,role',
            'latestMessage.sender:id,fname,lname,photo',
        ])->orderBy('updated_at', 'desc');

        // Scoping: non-admin users only see conversations they participate in
        if ($user->role !== 'admin') {
            $query->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Shipment ID
        if ($request->filled('shipment_id')) {
            $query->where('shipment_id', $request->shipment_id);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('users', function ($u) use ($search) {
                    $u->where('fname', 'like', "%{$search}%")
                      ->orWhere('lname', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('messages', function ($m) use ($search) {
                    $m->where('content', 'like', "%{$search}%");
                })
                ->orWhere('id', $search);
            });
        }

        $conversations = $query->paginate($request->input('per_page', 20));

        // Attach unread count for current user to each conversation
        foreach ($conversations as $conv) {
            $conv->unread_count = $conv->unreadCountForUser($user->id);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Conversations retrieved successfully',
            'data'    => $conversations
        ], 200);
    }

    /**
     * Get overview KPI statistics for chat and conversations.
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        $query = Conversation::query();
        if ($user->role !== 'admin') {
            $query->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $stats = [
            'total_conversations'   => (clone $query)->count(),
            'open_conversations'    => (clone $query)->where('status', 'open')->count(),
            'support_conversations' => (clone $query)->where('type', 'support')->count(),
            'unread_messages'       => Message::whereHas('conversation.participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('sender_id', '!=', $user->id)->where('is_read', 0)->count(),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Chat statistics retrieved successfully',
            'data'    => $stats
        ], 200);
    }

    /**
     * Start a new conversation.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'type'            => 'required|in:direct,support',
            'channel'         => 'required|in:in_app,whatsapp,sms',
            'shipment_id'     => 'nullable|exists:shipments,id',
            'recipient_id'    => 'nullable|exists:users,id',
            'user_ids'        => 'nullable|array',
            'user_ids.*'      => 'exists:users,id',
            'initial_message' => 'nullable|string',
            'message_type'    => 'nullable|in:text,image,audio,location,file,system_alert',
        ], [
            'type.required'    => 'Please select conversation type (direct, support).',
            'channel.required' => 'Please select communication channel (in_app, whatsapp, sms).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Determine participants
        $participants = [$user->id];
        if ($request->filled('recipient_id')) {
            $participants[] = (int) $request->recipient_id;
        }
        if ($request->filled('user_ids') && is_array($request->user_ids)) {
            $participants = array_merge($participants, $request->user_ids);
        }
        $participants = array_unique($participants);

        // Create Conversation
        $conversation = Conversation::create([
            'shipment_id' => $request->shipment_id ?: null,
            'type'        => $request->type,
            'channel'     => $request->channel,
            'status'      => 'open',
        ]);

        // Add Participants
        foreach ($participants as $uId) {
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $uId,
                'joined_at'       => Carbon::now(),
                'last_read_at'    => ($uId == $user->id) ? Carbon::now() : null,
            ]);
        }

        // Send initial message if provided
        if ($request->filled('initial_message')) {
            $msg = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $user->id,
                'message_type'    => $request->message_type ?? 'text',
                'content'         => $request->initial_message,
                'delivery_status' => 'sent',
                'is_read'         => 0,
            ]);

            try {
                broadcast(new MessageSent($msg))->toOthers();
            } catch (\Throwable $e) {
                Log::warning('Initial message broadcast skipped: ' . $e->getMessage());
            }
        }

        $conversation->load([
            'shipment',
            'users:id,fname,lname,email,phone,photo,role',
            'participants.user:id,fname,lname,photo',
            'latestMessage.sender:id,fname,lname,photo'
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Conversation created successfully',
            'data'    => $conversation
        ], 201);
    }

    /**
     * Get full details and messages of a specific conversation.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $conversation = Conversation::with([
            'shipment.pickupCity',
            'shipment.dropoffCity',
            'users:id,fname,lname,email,phone,photo,role,client_code',
            'participants.user:id,fname,lname,photo,role'
        ])->find($id);

        if (!$conversation) {
            return response()->json([
                'status'  => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        // Check participation
        if ($user->role !== 'admin') {
            $isParticipant = $conversation->participants->contains('user_id', $user->id);
            if (!$isParticipant) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized access to this conversation.'
                ], 403);
            }
        }

        // Update current user's last_read_at
        ConversationParticipant::where('conversation_id', $id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => Carbon::now()]);

        // Mark incoming messages as read
        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', 0)
            ->update([
                'is_read'         => 1,
                'delivery_status' => 'read',
            ]);

        $messages = Message::with(['sender:id,fname,lname,photo,role', 'replyTo.sender:id,fname,lname'])
            ->where('conversation_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedMessages = $messages->map(function ($msg) use ($user) {
            return [
                'id'              => $msg->id,
                'conversation_id' => $msg->conversation_id,
                'sender_id'       => $msg->sender_id,
                'is_me'           => ($msg->sender_id == $user->id),
                'sender_name'     => $msg->sender ? trim(($msg->sender->fname ?? '') . ' ' . ($msg->sender->lname ?? '')) : 'System',
                'sender_role'     => $msg->sender->role ?? 'member',
                'sender_photo'    => ($msg->sender && $msg->sender->photo) ? asset('upload/admin_images/' . $msg->sender->photo) : null,
                'message_type'    => $msg->message_type,
                'content'         => $msg->content,
                'metadata'        => $msg->metadata,
                'reply_to'        => $msg->replyTo ? [
                    'id'          => $msg->replyTo->id,
                    'sender_name' => $msg->replyTo->sender ? trim(($msg->replyTo->sender->fname ?? '') . ' ' . ($msg->replyTo->sender->lname ?? '')) : 'User',
                    'content'     => $msg->replyTo->content,
                ] : null,
                'is_read'         => (bool) $msg->is_read,
                'delivery_status' => $msg->delivery_status ?? 'sent',
                'created_at'      => $msg->created_at ? $msg->created_at->toIso8601String() : null,
                'time_ago'        => $msg->created_at ? $msg->created_at->diffForHumans() : '',
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Conversation messages retrieved successfully',
            'data'    => [
                'conversation' => $conversation,
                'messages'     => $formattedMessages,
            ]
        ], 200);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
            'message_type'    => 'required|in:text,image,audio,location,file,system_alert',
            'content'         => 'required_if:message_type,text|nullable|string',
            'metadata'        => 'nullable|array',
            'reply_to_id'     => 'nullable|exists:messages,id',
            'file_attachment' => 'nullable|file|max:15360',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'address'         => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $conversation = Conversation::findOrFail($request->conversation_id);

        $metadata = $request->metadata ?? null;
        $content  = $request->content;

        // Handle File Attachment
        if ($request->hasFile('file_attachment')) {
            $file = $request->file('file_attachment');
            $originalName = $file->getClientOriginalName();
            $fileSize     = $file->getSize();
            $mimeType     = $file->getClientMimeType();
            $extension    = $file->getClientOriginalExtension() ?: 'png';

            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = public_path('upload/messages');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $fileName);
            $fileUrl = asset('upload/messages/' . $fileName);

            $metadata = [
                'file_name'     => $originalName,
                'file_size'     => $fileSize,
                'mime_type'     => $mimeType,
                'file_url'      => $fileUrl,
                'relative_path' => 'upload/messages/' . $fileName,
                'duration'      => $request->input('duration', 0),
            ];

            if (empty($content)) {
                $content = ($request->message_type === 'audio') ? 'Voice Note' : $originalName;
            }
        }

        // Handle Location Metadata
        if ($request->message_type === 'location') {
            $metadata = [
                'latitude'  => (float) $request->input('latitude', 0.0),
                'longitude' => (float) $request->input('longitude', 0.0),
                'address'   => $request->input('address', 'Pinned Location'),
            ];
            if (empty($content)) {
                $content = 'Location Pin: ' . ($metadata['address'] ?? '');
            }
        }

        // Create Message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'message_type'    => $request->message_type,
            'content'         => $content,
            'metadata'        => $metadata,
            'reply_to_id'     => $request->reply_to_id ?: null,
            'is_read'         => 0,
            'delivery_status' => 'sent',
        ]);

        // Broadcast real-time live message via Reverb
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Message broadcast skipped: ' . $e->getMessage());
        }

        // Update conversation timestamp
        $conversation->touch();

        // Update sender's last_read_at
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => Carbon::now()]);

        $message->load(['sender:id,fname,lname,photo,role', 'replyTo.sender:id,fname,lname']);

        return response()->json([
            'status'  => true,
            'message' => 'Message sent successfully',
            'data'    => $message
        ], 201);
    }

    /**
     * Toggle Conversation Status (open <-> closed).
     */
    public function toggleStatus(Request $request, $id)
    {
        $conversation = Conversation::find($id);
        if (!$conversation) {
            return response()->json([
                'status'  => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $conversation->status = $request->status;
        $conversation->save();

        return response()->json([
            'status'  => true,
            'message' => 'Conversation status updated to ' . $conversation->status . ' successfully',
            'data'    => $conversation
        ], 200);
    }

    /**
     * Change Communication Channel (in_app, whatsapp, sms).
     */
    public function changeChannel(Request $request, $id)
    {
        $conversation = Conversation::find($id);
        if (!$conversation) {
            return response()->json([
                'status'  => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'channel' => 'required|in:in_app,whatsapp,sms',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $conversation->channel = $request->channel;
        $conversation->save();

        return response()->json([
            'status'  => true,
            'message' => 'Conversation channel updated to ' . $conversation->channel . ' successfully',
            'data'    => $conversation
        ], 200);
    }

    /**
     * Delete Message (Soft Delete).
     */
    public function deleteMessage(Request $request, $id)
    {
        $message = Message::find($id);
        if (!$message) {
            return response()->json([
                'status'  => false,
                'message' => 'Message not found'
            ], 404);
        }

        $user = $request->user();
        if ($user->role !== 'admin' && $message->sender_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized to delete this message.'
            ], 403);
        }

        $message->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Message deleted successfully'
        ], 200);
    }

    /**
     * Delete Conversation.
     */
    public function destroy($id)
    {
        $conversation = Conversation::find($id);
        if (!$conversation) {
            return response()->json([
                'status'  => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        $conversation->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Conversation deleted successfully'
        ], 200);
    }
}
