<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    /**
     * Submit a support ticket from mobile app.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'message' => 'required|string',
        ]);

        $user = $request->user();
        $ticketCount = SupportTicket::count() + 1;
        $ticketNumber = 'TKT-' . date('Y') . '-' . str_pad($ticketCount, 5, '0', STR_PAD_LEFT);

        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? ($user->name ?? $user->fullName ?? 'Salasil Customer') : ($request->input('name', 'Anonymous User')),
            'user_email' => $user ? $user->email : $request->input('email', null),
            'user_phone' => $user ? $user->phone : $request->input('phone', null),
            'category' => $request->category,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        try {
            if ($user) {
                $user->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Support Ticket Submitted #' . $ticket->ticket_number,
                    'body'         => 'Your ticket regarding "' . $ticket->subject . '" has been received. Our support team will assist you shortly.',
                    'type'         => 'support',
                    'reference_id' => $ticket->ticket_number,
                    'action_route' => '/my_support_tickets_screen',
                    'metadata'     => [
                        'ticket_id'     => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'subject'       => $ticket->subject,
                    ]
                ]));
            }
        } catch (\Throwable $ignoreTicketNotif) {}

        try {
            \App\Services\AdminNotificationService::newSupportTicket($ticket);
        } catch (\Throwable $ignoreAdminNotif) {}

        return response()->json([
            'status' => true,
            'message' => 'Support ticket submitted successfully',
            'data' => [
                'id' => (string) $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'message' => $ticket->message,
                'status' => $ticket->status,
                'admin_reply' => $ticket->admin_reply,
                'created_at' => $ticket->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * List user's tickets.
     */
    public function myTickets(Request $request)
    {
        $user = $request->user();
        $query = SupportTicket::query();

        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->email) {
                    $q->orWhere('user_email', $user->email);
                }
            });
        }

        $tickets = $query->latest()->get()->map(function ($ticket) {
            return [
                'id' => (string) $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'category' => $ticket->category,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'admin_reply' => $ticket->admin_reply,
                'replied_at' => $ticket->replied_at ? $ticket->replied_at->toIso8601String() : null,
                'created_at' => $ticket->created_at ? $ticket->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Tickets retrieved successfully',
            'data' => $tickets,
        ], 200);
    }

    /**
     * Show single ticket details.
     */
    public function show($id)
    {
        $ticket = SupportTicket::findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Ticket details retrieved successfully',
            'data' => [
                'id' => (string) $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'category' => $ticket->category,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'admin_reply' => $ticket->admin_reply,
                'replied_at' => $ticket->replied_at ? $ticket->replied_at->toIso8601String() : null,
                'created_at' => $ticket->created_at ? $ticket->created_at->toIso8601String() : null,
            ],
        ], 200);
    }
}
