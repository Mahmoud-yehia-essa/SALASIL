@extends('admin.master_admin')
@section('admin')

<style>
    .badge-open {
        background-color: rgba(234, 88, 12, 0.18) !important;
        color: #F97316 !important;
        border: 1px solid rgba(234, 88, 12, 0.35) !important;
    }
    .badge-in_progress {
        background-color: rgba(6, 182, 212, 0.18) !important;
        color: #06B6D4 !important;
        border: 1px solid rgba(6, 182, 212, 0.35) !important;
    }
    .badge-resolved {
        background-color: rgba(34, 197, 94, 0.18) !important;
        color: #22C55E !important;
        border: 1px solid rgba(34, 197, 94, 0.35) !important;
    }
    .badge-closed {
        background-color: rgba(148, 163, 184, 0.18) !important;
        color: #94A3B8 !important;
        border: 1px solid rgba(148, 163, 184, 0.35) !important;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-support text-info me-2"></i>Support Tickets & Inquiries
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Review incoming tickets from app clients, provide official admin responses, and manage ticket resolution status.
        </p>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 p-3 shadow-sm" style="background-color: var(--card-bg, #1e293b);">
            <span class="text-muted small fw-semibold">Total Tickets</span>
            <h3 class="fw-bold text-white mb-0 mt-1">{{ $counts['total'] }}</h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 p-3 shadow-sm" style="background-color: var(--card-bg, #1e293b);">
            <span class="text-warning small fw-semibold">Open Tickets</span>
            <h3 class="fw-bold text-warning mb-0 mt-1">{{ $counts['open'] }}</h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 p-3 shadow-sm" style="background-color: var(--card-bg, #1e293b);">
            <span class="text-info small fw-semibold">In Progress</span>
            <h3 class="fw-bold text-info mb-0 mt-1">{{ $counts['in_progress'] }}</h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 p-3 shadow-sm" style="background-color: var(--card-bg, #1e293b);">
            <span class="text-success small fw-semibold">Resolved</span>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $counts['resolved'] }}</h3>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background-color: var(--card-bg, #1e293b);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Ticket ID</th>
                        <th>User / Contact</th>
                        <th>Category & Subject</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Replied</th>
                        <th class="text-center">Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td class="ps-4 fw-bold text-info">
                            {{ $ticket->ticket_number }}
                        </td>
                        <td>
                            <div class="fw-semibold text-white">{{ $ticket->user_name ?: ($ticket->user?->fullName ?: 'Client') }}</div>
                            <div class="text-muted small">{{ $ticket->user_email ?: $ticket->user_phone }}</div>
                        </td>
                        <td style="white-space: normal; min-width: 250px; max-width: 400px;">
                            <span class="badge bg-secondary bg-opacity-25 text-slate-300 rounded-pill px-2 py-0.5 small mb-1">
                                {{ $ticket->category }}
                            </span>
                            <div class="fw-bold text-slate-200">{{ $ticket->subject }}</div>
                            <div class="text-slate-400 small text-truncate" style="max-width: 380px;">
                                {{ Str::limit($ticket->message, 80) }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $ticket->status }} rounded-pill px-3 py-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">
                                {{ str_replace('_', ' ', $ticket->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($ticket->admin_reply)
                                <i class="bx bx-check-circle text-success fs-4" title="Admin replied"></i>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-0.5">Pending</span>
                            @endif
                        </td>
                        <td class="text-center text-muted small">
                            {{ $ticket->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('show.support.ticket', $ticket->id) }}" class="btn btn-sm btn-primary rounded-3 d-inline-flex align-items-center gap-1" title="View & Reply">
                                    <i class="bx bx-reply"></i>
                                    <span>Reply</span>
                                </a>
                                <a href="{{ route('delete.support.ticket', $ticket->id) }}" class="btn btn-sm btn-outline-danger rounded-3" id="delete" title="Delete Ticket">
                                    <i class="bx bx-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bx bx-support fs-1 mb-2 d-block"></i>
                            No support tickets received yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer bg-transparent border-0 p-3">
        {{ $tickets->links() }}
    </div>
    @endif
</div>

@endsection
