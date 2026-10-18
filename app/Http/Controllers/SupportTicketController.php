<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    /**
     * Display all support tickets.
     */
    public function AllTickets(Request $request)
    {
        $query = SupportTicket::with('user')->latest();

        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('category') && !empty($request->category) && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $tickets = $query->paginate(15);
        $counts = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
        ];

        return view('admin.backend.support_ticket.all_tickets', compact('tickets', 'counts'));
    }

    /**
     * Show ticket details & response form.
     */
    public function ShowTicket($id)
    {
        $ticket = SupportTicket::with('user')->findOrFail($id);
        return view('admin.backend.support_ticket.show_ticket', compact('ticket'));
    }

    /**
     * Reply to ticket and update status.
     */
    public function ReplyTicket(Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->admin_reply = $request->admin_reply;
        $ticket->status = $request->status;
        $ticket->replied_at = now();
        $ticket->save();
        try {
            if ($ticket->user_id && ($ticketUser = \App\Models\User::find($ticket->user_id))) {
                $ticketUser->notify(new \App\Notifications\CustomerActivityNotification([
                    'title'        => 'Support Ticket Replied #' . $ticket->ticket_number,
                    'body'         => 'Support replied: "' . \Illuminate\Support\Str::limit($request->admin_reply, 90) . '"',
                    'type'         => 'support',
                    'reference_id' => $ticket->ticket_number,
                    'action_route' => '/my_support_tickets_screen',
                    'metadata'     => [
                        'ticket_id'     => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'status'        => $ticket->status,
                    ]
                ]));
            }
        } catch (\Throwable $ignoreReplyNotif) {}

        $notification = [
            'message' => 'Reply sent and ticket status updated successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * Update ticket status via AJAX.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $ticket = SupportTicket::findOrFail($request->id);
        $ticket->status = $request->status;
        $ticket->save();

        return response()->json([
            'status' => true,
            'message' => 'Ticket status updated successfully!'
        ]);
    }

    /**
     * Delete ticket.
     */
    public function DeleteTicket($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->delete();

        $notification = [
            'message' => 'Ticket deleted successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.support.tickets')->with($notification);
    }
}
