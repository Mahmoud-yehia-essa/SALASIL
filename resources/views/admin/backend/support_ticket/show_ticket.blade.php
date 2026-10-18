@extends('admin.master_admin')
@section('admin')

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-receipt text-info me-2"></i>Ticket {{ $ticket->ticket_number }}
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Customer Inquiry submitted on {{ $ticket->created_at->format('d M Y, h:i A') }}
        </p>
    </div>
    <div>
        <a href="{{ route('all.support.tickets') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-arrow-back fs-5"></i>
            <span>Back to All Tickets</span>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Ticket Details Column -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background-color: var(--card-bg, #1e293b);">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-secondary border-opacity-25 pb-3">
                <span class="text-muted small">Status</span>
                <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 rounded-pill px-3 py-1 text-uppercase fw-semibold">
                    {{ str_replace('_', ' ', $ticket->status) }}
                </span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block mb-1">Customer</span>
                <div class="fw-bold text-white fs-6">{{ $ticket->user_name ?: ($ticket->user?->fullName ?: 'Client') }}</div>
                <div class="text-slate-400 small">{{ $ticket->user_email }}</div>
                @if($ticket->user_phone)
                <div class="text-slate-400 small">{{ $ticket->user_phone }}</div>
                @endif
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block mb-1">Category</span>
                <span class="badge bg-secondary bg-opacity-25 text-slate-300 rounded-pill px-3 py-1">
                    {{ $ticket->category }}
                </span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block mb-1">Subject</span>
                <div class="fw-bold text-slate-200">{{ $ticket->subject }}</div>
            </div>

            <div class="border-top border-secondary border-opacity-25 pt-3">
                <span class="text-muted small d-block mb-2">Message from Customer</span>
                <div class="p-3 rounded-3 text-slate-200" style="background-color: rgba(15, 23, 42, 0.6); white-space: pre-wrap; line-height: 1.6;">{{ $ticket->message }}</div>
            </div>
        </div>
    </div>

    <!-- Admin Response Column -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background-color: var(--card-bg, #1e293b);">
            <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                <i class="bx bx-reply-all text-info"></i>
                <span>Admin Response to Customer</span>
            </h5>
            <p class="text-muted small mb-4">The customer will instantly view this response inside the mobile app ticket viewer.</p>

            <form method="POST" action="{{ route('reply.support.ticket', $ticket->id) }}">
                @csrf

                <!-- Status Selector -->
                <div class="mb-3">
                    <label class="form-label text-slate-300 fw-semibold">Update Ticket Status</label>
                    <select name="status" class="form-select rounded-3" required>
                        <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved (Solved)</option>
                        <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <!-- Admin Reply Textarea -->
                <div class="mb-4">
                    <label class="form-label text-slate-300 fw-semibold">Official Reply Message <span class="text-danger">*</span></label>
                    <textarea name="admin_reply" rows="8" class="form-control rounded-3" placeholder="Type your response to the user here..." required>{{ old('admin_reply', $ticket->admin_reply) }}</textarea>
                    @if($ticket->replied_at)
                    <small class="text-success mt-1 d-block">
                        <i class="bx bx-check"></i> Last replied on {{ $ticket->replied_at->format('d M Y, h:i A') }}
                    </small>
                    @endif
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow">
                        <i class="bx bx-send fs-5"></i>
                        <span>Submit Response to Customer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
