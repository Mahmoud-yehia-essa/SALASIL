<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CommunicationController extends Controller
{
    /**
     * Display Communications & Direct Chat Dashboard.
     */
    public function AllConversations(Request $request)
    {
        $adminId = Auth::id() ?? 1;

        $conversationsQuery = Conversation::with([
            'shipment',
            'users',
            'participants.user',
            'latestMessage.sender',
        ])->orderBy('updated_at', 'desc');

        // Filter by Type
        if ($request->filled('type')) {
            $conversationsQuery->where('type', $request->type);
        }

        // Filter by Channel
        if ($request->filled('channel')) {
            $conversationsQuery->where('channel', $request->channel);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $conversationsQuery->where('status', $request->status);
        }

        // Filter by Shipment ID
        if ($request->filled('shipment_id')) {
            $conversationsQuery->where('shipment_id', $request->shipment_id);
        }

        // Search by participant name, phone, email or message content
        if ($request->filled('search')) {
            $search = trim($request->search);
            $conversationsQuery->where(function ($q) use ($search) {
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

        $conversations = $conversationsQuery->get();

        // Attach unread count for current user to each conversation
        foreach ($conversations as $conv) {
            $conv->unread_count = $conv->unreadCountForUser($adminId);
        }

        // Calculate KPI Statistics
        $stats = [
            'total_conversations'   => Conversation::count(),
            'open_conversations'    => Conversation::where('status', 'open')->count(),
            'support_conversations' => Conversation::where('type', 'support')->count(),
            'unread_messages'       => Message::where('sender_id', '!=', $adminId)->where('is_read', 0)->count(),
        ];

        // All active users for starting conversations or adding participants
        $users = User::where('status', '!=', 'banned')
                    ->orderBy('fname', 'asc')
                    ->get(['id', 'fname', 'lname', 'email', 'phone', 'role', 'photo']);

        // All shipments for linking conversations
        $shipments = Shipment::orderBy('id', 'desc')->get(['id', 'shipment_name', 'status']);

        // Default selected conversation (first one or from request query)
        $selectedConvId = $request->query('active_id', $conversations->first()?->id);

        return view('admin.backend.communication.all_conversations', compact(
            'conversations',
            'stats',
            'users',
            'shipments',
            'selectedConvId'
        ));
    }

    /**
     * Store new Conversation.
     */
    public function StoreConversation(Request $request)
    {
        $request->validate([
            'type'            => 'required|in:direct,support',
            'channel'         => 'required|in:in_app,whatsapp,sms',
            'shipment_id'     => 'nullable|exists:shipments,id',
            'user_ids'        => 'required|array|min:1',
            'user_ids.*'      => 'exists:users,id',
            'initial_message' => 'nullable|string',
            'message_type'    => 'nullable|in:text,image,audio,location,file,system_alert',
        ]);

        $adminId = Auth::id();

        // Create Conversation
        $conversation = Conversation::create([
            'shipment_id' => $request->shipment_id ?: null,
            'type'        => $request->type,
            'channel'     => $request->channel,
            'status'      => 'open',
        ]);

        // Gather all participant IDs (including admin)
        $participantIds = array_unique(array_merge([$adminId], $request->user_ids));

        foreach ($participantIds as $uId) {
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $uId,
                'joined_at'       => Carbon::now(),
                'last_read_at'    => ($uId == $adminId) ? Carbon::now() : null,
            ]);
        }

        // Store initial message if provided
        if ($request->filled('initial_message')) {
            $initMsg = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $adminId,
                'message_type'    => $request->message_type ?? 'text',
                'content'         => $request->initial_message,
                'delivery_status' => 'sent',
                'is_read'         => 0,
            ]);

            try {
                broadcast(new \App\Events\MessageSent($initMsg))->toOthers();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Initial message broadcast skipped or failed: ' . $e->getMessage());
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success'         => true,
                'message'         => 'Conversation created successfully.',
                'conversation_id' => $conversation->id,
            ]);
        }

        $notification = [
            'message'    => 'Conversation created successfully.',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.conversations', ['active_id' => $conversation->id])->with($notification);
    }

    /**
     * Get Messages for a specific conversation (AJAX).
     */
    public function GetMessagesAjax($id)
    {
        $adminId = Auth::id();
        $conversation = Conversation::with(['shipment', 'participants.user'])->findOrFail($id);

        // Update current admin last_read_at timestamp
        ConversationParticipant::where('conversation_id', $id)
            ->where('user_id', $adminId)
            ->update(['last_read_at' => Carbon::now()]);

        // Mark incoming messages in this conversation as read
        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $adminId)
            ->where('is_read', 0)
            ->update([
                'is_read'         => 1,
                'delivery_status' => 'read',
            ]);

        $messages = Message::with(['sender', 'replyTo.sender'])
            ->where('conversation_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedMessages = $messages->map(function ($msg) use ($adminId) {
            $sender = $msg->sender;
            $senderName = $sender ? trim(($sender->fname ?? '') . ' ' . ($sender->lname ?? '')) : 'System';
            $senderPhoto = ($sender && $sender->photo) ? asset('upload/admin_images/' . $sender->photo) : asset('upload/no_image.jpg');

            $replyInfo = null;
            if ($msg->replyTo) {
                $rSender = $msg->replyTo->sender;
                $replyInfo = [
                    'id'          => $msg->replyTo->id,
                    'sender_name' => $rSender ? trim(($rSender->fname ?? '') . ' ' . ($rSender->lname ?? '')) : 'User',
                    'content'     => $msg->replyTo->content ?? '[' . $msg->replyTo->message_type . ']',
                ];
            }

            return [
                'id'              => $msg->id,
                'sender_id'       => $msg->sender_id,
                'is_me'           => ($msg->sender_id == $adminId),
                'sender_name'     => $senderName,
                'sender_role'     => $sender->role ?? 'admin',
                'sender_photo'    => $senderPhoto,
                'message_type'    => $msg->message_type,
                'content'         => $msg->content,
                'metadata'        => $msg->metadata,
                'reply_to'        => $replyInfo,
                'is_read'         => (bool) $msg->is_read,
                'delivery_status' => $msg->delivery_status ?? 'sent',
                'created_at'      => $msg->created_at ? $msg->created_at->format('M d, Y H:i') : '',
                'time_ago'        => $msg->created_at ? $msg->created_at->diffForHumans() : '',
            ];
        });

        // Format Participants
        $participants = $conversation->participants->map(function ($p) {
            $u = $p->user;
            return [
                'id'           => $p->id,
                'user_id'      => $p->user_id,
                'name'         => $u ? trim(($u->fname ?? '') . ' ' . ($u->lname ?? '')) : 'User #' . $p->user_id,
                'email'        => $u->email ?? '',
                'phone'        => $u->phone ?? '',
                'role'         => $u->role ?? 'member',
                'photo'        => ($u && $u->photo) ? asset('upload/admin_images/' . $u->photo) : asset('upload/no_image.jpg'),
                'joined_at'    => $p->joined_at ? $p->joined_at->format('M d, Y H:i') : '—',
                'last_read_at' => $p->last_read_at ? $p->last_read_at->format('M d, Y H:i') : 'Unread',
            ];
        });

        return response()->json([
            'success'      => true,
            'conversation' => [
                'id'           => $conversation->id,
                'type'         => $conversation->type,
                'channel'      => $conversation->channel,
                'status'       => $conversation->status,
                'shipment'     => $conversation->shipment ? [
                    'id'              => $conversation->shipment->id,
                    'shipment_number' => '#' . $conversation->shipment->id,
                    'title'           => $conversation->shipment->shipment_name ?? ('Shipment #' . $conversation->shipment->id),
                    'status'          => $conversation->shipment->status ?? '—',
                ] : null,
                'participants' => $participants,
            ],
            'messages'     => $formattedMessages,
        ]);
    }

    /**
     * Send Message (AJAX).
     */
    public function SendMessageAjax(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message_type'    => 'required|in:text,image,audio,location,file,system_alert',
            'content'         => 'nullable|string',
            'reply_to_id'     => 'nullable|exists:messages,id',
            'file_attachment' => 'nullable|file|max:10240',
        ]);

        $adminId = Auth::id();
        $conversation = Conversation::findOrFail($request->conversation_id);

        $metadata = null;
        $content  = $request->content;

        // Handle File Attachment (Image, File, Audio)
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

        // Handle Location Type Metadata
        if ($request->message_type === 'location') {
            $metadata = [
                'latitude'  => $request->input('latitude', 0.0),
                'longitude' => $request->input('longitude', 0.0),
                'address'   => $request->input('address', 'Pinned Location'),
            ];
            if (empty($content)) {
                $content = 'Location Pin: ' . ($metadata['address'] ?? '');
            }
        }

        // Create Message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $adminId,
            'message_type'    => $request->message_type,
            'content'         => $content,
            'metadata'        => $metadata,
            'reply_to_id'     => $request->reply_to_id ?: null,
            'is_read'         => 0,
            'delivery_status' => 'sent',
        ]);

        // Broadcast real-time live message via Reverb immediately (safely wrapped)
        try {
            broadcast(new \App\Events\MessageSent($message));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Communication message broadcast skipped or failed: ' . $e->getMessage());
        }

        // Touch conversation updated_at
        $conversation->touch();

        // Update current admin last_read_at
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $adminId)
            ->update(['last_read_at' => Carbon::now()]);

        $sender = Auth::user();
        $senderName = $sender ? trim(($sender->fname ?? '') . ' ' . ($sender->lname ?? '')) : 'System';
        $senderPhoto = ($sender && $sender->photo) ? asset('upload/admin_images/' . $sender->photo) : asset('upload/no_image.jpg');

        $replyInfo = null;
        if ($message->replyTo) {
            $rSender = $message->replyTo->sender;
            $replyInfo = [
                'id'          => $message->replyTo->id,
                'sender_name' => $rSender ? trim(($rSender->fname ?? '') . ' ' . ($rSender->lname ?? '')) : 'User',
                'content'     => $message->replyTo->content,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id'              => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id'       => $message->sender_id,
                'is_me'           => true,
                'sender_name'     => $senderName,
                'sender_role'     => $sender->role ?? 'admin',
                'sender_photo'    => $senderPhoto,
                'message_type'    => $message->message_type,
                'content'         => $message->content,
                'metadata'        => $message->metadata,
                'reply_to'        => $replyInfo,
                'is_read'         => false,
                'delivery_status' => 'sent',
                'created_at'      => $message->created_at->format('M d, Y H:i'),
                'time_ago'        => $message->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Toggle Conversation Status (Open <-> Closed).
     */
    public function ToggleStatusAjax(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'status'          => 'required|in:open,closed',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        $conversation->status = $request->status;
        $conversation->save();

        return response()->json([
            'success' => true,
            'message' => 'Conversation status updated successfully.',
            'status'  => $conversation->status,
        ]);
    }

    /**
     * Change Conversation Channel (in_app, whatsapp, sms).
     */
    public function ChangeChannelAjax(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'channel'         => 'required|in:in_app,whatsapp,sms',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        $conversation->channel = $request->channel;
        $conversation->save();

        return response()->json([
            'success' => true,
            'message' => 'Communication channel updated successfully.',
            'channel' => $conversation->channel,
        ]);
    }

    /**
     * Delete Message (Soft Delete).
     */
    public function DeleteMessageAjax($id)
    {
        $message = Message::findOrFail($id);
        $message->delete(); // Uses SoftDeletes

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully.',
        ]);
    }

    /**
     * Delete Conversation.
     */
    public function DeleteConversation($id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->delete();

        $notification = [
            'message'    => 'Conversation permanently deleted.',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.conversations')->with($notification);
    }

    /**
     * Add Participant to Conversation.
     */
    public function AddParticipantAjax(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'user_id'         => 'required|exists:users,id',
        ]);

        $existing = ConversationParticipant::where('conversation_id', $request->conversation_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$existing) {
            ConversationParticipant::create([
                'conversation_id' => $request->conversation_id,
                'user_id'         => $request->user_id,
                'joined_at'       => Carbon::now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Participant added successfully.',
        ]);
    }

    /**
     * Remove Participant from Conversation.
     */
    public function RemoveParticipantAjax(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'user_id'         => 'required|exists:users,id',
        ]);

        ConversationParticipant::where('conversation_id', $request->conversation_id)
            ->where('user_id', $request->user_id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Participant removed successfully.',
        ]);
    }
}
