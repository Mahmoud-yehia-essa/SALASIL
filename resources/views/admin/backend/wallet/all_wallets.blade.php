@extends('admin.master_admin')
@section('admin')

{{-- ========================
     FINANCIAL & BILLING — USER WALLETS & TRANSACTIONS
======================== --}}

<style>
:root {
    --wal-cyan:       #06B6D4;
    --wal-cyan-light: #38BDF8;
    --wal-navy:       #0F172A;
    --wal-border:     rgba(255,255,255,0.08);
    --wal-success:    #10B981;
    --wal-warning:    #F59E0B;
    --wal-danger:     #F43F5E;
    --wal-purple:     #8B5CF6;
}

/* ─── KPI Cards ─── */
.kpi-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.9) 0%, rgba(15,23,42,0.95) 100%);
    border: 1px solid var(--wal-border);
    border-radius: 16px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.kpi-card:hover {
    transform: translateY(-4px);
    border-color: rgba(6,182,212,0.3);
    box-shadow: 0 15px 35px rgba(6,182,212,0.15);
}
.kpi-icon-wrap {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    flex-shrink: 0;
}
.kpi-icon-wrap.total    { background: linear-gradient(135deg, #06B6D4, #38BDF8); box-shadow: 0 8px 20px rgba(6,182,212,0.3); }
.kpi-icon-wrap.balance  { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
.kpi-icon-wrap.deposits { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 8px 20px rgba(139,92,246,0.3); }
.kpi-icon-wrap.deduct   { background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 8px 20px rgba(245,158,11,0.3); color:#0F172A; }

.kpi-label { font-size: 0.78rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.5rem; font-weight: 800; color: #F8FAFC; margin-top: 2px; }

/* ─── Filter Bar ─── */
.filter-card {
    background: rgba(30,41,59,0.7);
    border: 1px solid var(--wal-border);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 24px;
    backdrop-filter: blur(10px);
}
.filter-card .form-control,
.filter-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 10px !important;
    padding: 9px 14px !important;
    font-size: 0.88rem !important;
}
.filter-card .form-control:focus,
.filter-card .form-select:focus {
    border-color: var(--wal-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
}
.filter-card .form-select option { background: #1E293B; color: #F8FAFC; }

/* ─── Data Table ─── */
.wal-table-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--wal-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
    margin-bottom: 30px;
}
.wal-table {
    width: 100%;
    margin-bottom: 0;
    color: #CBD5E1;
    border-collapse: separate;
    border-spacing: 0;
}
.wal-table th {
    background: rgba(15,23,42,0.9);
    color: #94A3B8;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    white-space: nowrap;
}
.wal-table td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.88rem;
}
.wal-table tbody tr { transition: background 0.2s; }
.wal-table tbody tr:hover { background: rgba(6,182,212,0.05); }

/* Role Badges */
.role-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 700;
}
.role-badge.company    { background: rgba(6,182,212,0.15); color: #38BDF8; border: 1px solid rgba(6,182,212,0.3); }
.role-badge.individual { background: rgba(139,92,246,0.15); color: #A78BFA; border: 1px solid rgba(139,92,246,0.3); }
.role-badge.driver     { background: rgba(245,158,11,0.15); color: #FCD34D; border: 1px solid rgba(245,158,11,0.3); }

/* Type Badges */
.type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 50px; font-size: 0.78rem; font-weight: 700;
}
.type-badge.deposit             { background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.3); }
.type-badge.trip_earnings       { background: rgba(6,182,212,0.15); color: #38BDF8; border: 1px solid rgba(6,182,212,0.3); }
.type-badge.refund              { background: rgba(59,130,246,0.15); color: #60A5FA; border: 1px solid rgba(59,130,246,0.3); }
.type-badge.withdrawal          { background: rgba(244,63,94,0.15); color: #F43F5E; border: 1px solid rgba(244,63,94,0.3); }
.type-badge.commission_deduction{ background: rgba(245,158,11,0.15); color: #F59E0B; border: 1px solid rgba(245,158,11,0.3); }

.btn-action {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--wal-border);
    background: rgba(255,255,255,0.04);
    color: #CBD5E1;
    transition: all 0.2s;
}
.btn-action:hover {
    background: rgba(6,182,212,0.15);
    border-color: var(--wal-cyan);
    color: #38BDF8;
    transform: translateY(-2px);
}
.btn-action.btn-del:hover {
    background: rgba(244,63,94,0.15);
    border-color: var(--wal-danger);
    color: #F43F5E;
}

/* Modal Drawer */
.modal-wal-log .modal-dialog { max-width: 850px; }
.modal-wal-log .modal-content {
    background: #0F172A;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    color: #F8FAFC;
}
.modal-wal-log .modal-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
}
.modal-wal-log .modal-body { padding: 32px; max-height: 80vh; overflow-y: auto; }
.modal-wal-log .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); padding: 20px 32px; background: rgba(0,0,0,0.15); }

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ALL USER WALLETS PAGE
═════════════════════════════════════════════════════════════ */
html.light-theme .kpi-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
}
html.light-theme .kpi-label {
    color: #64748B !important;
}
html.light-theme .kpi-value {
    color: #0F172A !important;
}
html.light-theme .filter-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
}
html.light-theme .filter-card .form-control,
html.light-theme .filter-card .form-select {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .filter-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .wal-table-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.05) !important;
}
html.light-theme .wal-table {
    color: #0F172A !important;
}
html.light-theme .wal-table th {
    background: #F1F5F9 !important;
    color: #334155 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .wal-table td {
    border-bottom-color: #F1F5F9 !important;
    color: #0F172A !important;
}
html.light-theme .wal-table tbody tr:hover {
    background: #F8FAFC !important;
}
html.light-theme .btn-action {
    background: #F1F5F9 !important;
    border-color: #CBD5E1 !important;
    color: #475569 !important;
}
html.light-theme .btn-action:hover {
    background: #E0F2FE !important;
    border-color: #0284C7 !important;
    color: #0284C7 !important;
}
html.light-theme .btn-action.btn-del:hover {
    background: #FFE4E6 !important;
    border-color: #F43F5E !important;
    color: #E11D48 !important;
}
html.light-theme .modal-wal-log .modal-content {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15) !important;
}
html.light-theme .modal-wal-log .modal-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .modal-wal-log .modal-header h5,
html.light-theme .modal-wal-log .modal-header .modal-title {
    color: #0F172A !important;
}
html.light-theme .modal-wal-log .modal-header .text-muted {
    color: #64748B !important;
}
html.light-theme .modal-wal-log .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}
html.light-theme .modal-wal-log .modal-footer {
    background: #F8FAFC !important;
    border-top-color: #E2E8F0 !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-wallet text-info me-2"></i>Financial & Billing System — Wallets & Balances
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Manage user balances, deposit, withdraw, and inspect transaction ledgers (shipments & scheduled trips).
        </p>
    </div>
    <div>
        <a href="{{ route('add.wallet.transaction') }}" class="btn btn-info rounded-3 px-3 py-2 fw-bold d-inline-flex align-items-center gap-2" style="background:linear-gradient(135deg,#06B6D4,#38BDF8);border:none;color:#0F172A;">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Deposit / Withdraw Funds</span>
        </a>
    </div>
</div>

{{-- ─── KPI Stats Bar ─── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap total"><i class="bx bx-user"></i></div>
            <div>
                <div class="kpi-label">User Wallets</div>
                <div class="kpi-value">{{ number_format($stats['total_wallets']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap balance"><i class="bx bx-wallet-alt"></i></div>
            <div>
                <div class="kpi-label">Active Total Balance</div>
                <div class="kpi-value" style="font-size:1.3rem;">KWD {{ number_format($stats['total_balance'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap deposits"><i class="bx bx-plus-circle"></i></div>
            <div>
                <div class="kpi-label">Total Deposits</div>
                <div class="kpi-value" style="font-size:1.3rem;">KWD {{ number_format($stats['total_deposits'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon-wrap deduct"><i class="bx bx-minus-circle"></i></div>
            <div>
                <div class="kpi-label">Total Deductions</div>
                <div class="kpi-value" style="font-size:1.3rem;">KWD {{ number_format($stats['total_deductions'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Filter & Search Bar ─── --}}
<div class="filter-card">
    <form method="GET" action="{{ route('all.wallets') }}" id="filterForm">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-7">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control ps-5" placeholder="Search user name, email, phone..." value="{{ request('search') }}">
                    <span class="position-absolute text-muted" style="left:14px;top:50%;transform:translateY(-50%);"><i class="bx bx-search fs-5"></i></span>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="role" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Account Types (Companies / Individuals / Drivers)</option>
                    <option value="company_customer" {{ request('role') === 'company_customer' ? 'selected' : '' }}>Company Customers</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Individual Customers</option>
                    <option value="driver" {{ request('role') === 'driver' ? 'selected' : '' }}>Drivers</option>
                </select>
            </div>
            <div class="col-12 col-md-1 text-end">
                <a href="{{ route('all.wallets') }}" class="btn btn-outline-secondary w-100 rounded-3" title="Reset Filters"><i class="bx bx-refresh fs-5"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- ─── User Wallets Summary Table ─── --}}
<div class="mb-2 d-flex align-items-center justify-content-between">
    <h5 class="fw-bold text-white mb-0"><i class="bx bx-user-pin me-2 text-info"></i>User Wallets & Balances</h5>
</div>
<div class="wal-table-card">
    <div class="table-responsive">
        <table class="table wal-table">
            <thead>
                <tr>
                    <th>User ID & Name</th>
                    <th>Account Type</th>
                    <th>Email / Phone</th>
                    <th>Current Balance</th>
                    <th>Total Txns</th>
                    <th>Last Activity</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    {{-- User Name --}}
                    <td>
                        <div class="fw-bold text-white">{{ $u->fname }} {{ $u->lname }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">User ID: #{{ $u->id }}</div>
                    </td>

                    {{-- Role --}}
                    <td>
                        @if($u->role === 'company_customer')
                            <span class="role-badge company"><i class="bx bx-building"></i> Company</span>
                        @elseif($u->role === 'driver')
                            <span class="role-badge driver"><i class="bx bx-car"></i> Driver</span>
                        @else
                            <span class="role-badge individual"><i class="bx bx-user"></i> Individual</span>
                        @endif
                    </td>

                    {{-- Email / Phone --}}
                    <td class="text-muted" style="font-size:0.82rem;">
                        <div>{{ $u->email }}</div>
                        <div>{{ $u->phone ?: '—' }}</div>
                    </td>

                    {{-- Current Balance --}}
                    <td>
                        <strong class="text-success fs-6">KWD {{ number_format($u->current_balance, 2) }}</strong>
                    </td>

                    {{-- Txns Count --}}
                    <td>
                        <span class="badge bg-dark border border-secondary border-opacity-25 text-info">{{ $u->txns_count }} Txns</span>
                    </td>

                    {{-- Last Activity --}}
                    <td class="text-muted" style="font-size:0.82rem;">
                        {{ $u->last_activity }}
                    </td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <button type="button" class="btn-action" onclick="openWalletLog({{ $u->id }})" title="View Statement Ledger">
                                <i class="bx bx-list-ol fs-5"></i>
                            </button>
                            <a href="{{ route('add.wallet.transaction', ['user_id' => $u->id]) }}" class="btn-action" title="Deposit / Withdraw">
                                <i class="bx bx-plus-circle fs-5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bx bx-wallet fs-1 text-slate-500 mb-2 d-block"></i>
                        No user wallets found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ─── Recent Transactions Audit Table ─── --}}
<div class="mb-2 mt-4 d-flex align-items-center justify-content-between">
    <h5 class="fw-bold text-white mb-0"><i class="bx bx-history me-2 text-info"></i>Recent Wallet Transactions Ledger</h5>
</div>
<div class="wal-table-card">
    <div class="table-responsive">
        <table class="table wal-table">
            <thead>
                <tr>
                    <th># ID</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance After</th>
                    <th>Linked Resource</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $t)
                <tr>
                    {{-- Txn ID --}}
                    <td class="fw-bold text-white">#{{ $t->id }}</td>

                    {{-- User --}}
                    <td>
                        <div class="fw-bold text-white">{{ $t->user ? ($t->user->fname . ' ' . $t->user->lname) : 'Guest' }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">{{ $t->user ? $t->user->email : '—' }}</div>
                    </td>

                    {{-- Type --}}
                    <td>
                        <span class="type-badge {{ $t->type }}">
                            {{ ucfirst(str_replace('_', ' ', $t->type)) }}
                        </span>
                    </td>

                    {{-- Amount --}}
                    <td>
                        @if(in_array($t->type, ['deposit', 'trip_earnings', 'refund']))
                            <span class="text-success fw-bold">+ KWD {{ number_format($t->amount, 2) }}</span>
                        @else
                            <span class="text-danger fw-bold">- KWD {{ number_format($t->amount, 2) }}</span>
                        @endif
                    </td>

                    {{-- Balance After --}}
                    <td class="fw-bold text-white">
                        KWD {{ number_format($t->balance_after, 2) }}
                    </td>

                    {{-- Linked Resource --}}
                    <td>
                        @if($t->shipment_id)
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25">
                                <i class="bx bx-package me-1"></i>Shipment #{{ $t->shipment_id }}
                            </span>
                        @elseif($t->scheduled_trip_id)
                            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25">
                                <i class="bx bx-calendar me-1"></i>Trip #{{ $t->scheduled_trip_id }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Description --}}
                    <td class="text-slate-300" style="font-size:0.84rem;">
                        {{ $t->description ?: '—' }}
                    </td>

                    {{-- Date --}}
                    <td class="text-muted" style="font-size:0.82rem;">
                        {{ $t->created_at ? $t->created_at->format('Y-m-d H:i') : '—' }}
                    </td>

                    {{-- Actions --}}
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <a href="{{ route('edit.wallet.transaction', $t->id) }}" class="btn-action" title="Edit Transaction">
                                <i class="bx bx-edit fs-5"></i>
                            </a>
                            <a href="{{ route('delete.wallet.transaction', $t->id) }}" class="btn-action btn-del confirm-delete" title="Delete Transaction">
                                <i class="bx bx-trash fs-5"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        No transactions recorded.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // ── Open User Wallet Log Modal via AJAX ──
    function openWalletLog(userId) {
        var modal = new bootstrap.Modal(document.getElementById('walletLogModal'));
        $('#modalLoader').show();
        $('#modalDataWrap').hide().empty();
        modal.show();

        $.ajax({
            url: '{{ route("get.user.wallet.log.ajax", ":id") }}'.replace(':id', userId),
            type: 'GET',
            success: function(res) {
                $('#modalLoader').hide();
                if (res.status === 'success') {
                    renderWalletLedger(res.user, res.transactions);
                    $('#modalDataWrap').show();
                } else {
                    $('#modalDataWrap').html('<div class="alert alert-danger">Failed to load ledger.</div>').show();
                }
            },
            error: function() {
                $('#modalLoader').hide();
                $('#modalDataWrap').html('<div class="alert alert-danger">Error fetching wallet ledger.</div>').show();
            }
        });
    }

    function renderWalletLedger(user, txns) {
        $('#modalUserTitle').text(user.name + ' (' + user.role + ')');
        $('#modalUserBalance').text('KWD ' + user.current_balance);

        var html = '';
        html += '<table class="table wal-table"><thead><tr><th>Txn #</th><th>Type</th><th>Amount</th><th>Balance After</th><th>Linked Ref</th><th>Description</th><th>Date</th></tr></thead><tbody>';

        if (txns && txns.length > 0) {
            txns.forEach(function(t) {
                var amtColor = (['deposit','trip_earnings','refund'].includes(t.type)) ? 'text-success' : 'text-danger';
                var sign = (['deposit','trip_earnings','refund'].includes(t.type)) ? '+' : '-';
                var ref = t.shipment || t.scheduled_trip || '—';

                html += '<tr>';
                html += '<td class="fw-bold text-white">#' + t.id + '</td>';
                html += '<td><span class="badge bg-dark border border-secondary text-info">' + t.type_label + '</span></td>';
                html += '<td class="' + amtColor + ' fw-bold">' + sign + ' KWD ' + t.amount + '</td>';
                html += '<td class="fw-bold text-white">KWD ' + t.balance_after + '</td>';
                html += '<td><small class="text-info">' + ref + '</small></td>';
                html += '<td><small class="text-slate-300">' + t.description + '</small></td>';
                html += '<td><small class="text-muted">' + t.date + '</small></td>';
                html += '</tr>';
            });
        } else {
            html += '<tr><td colspan="7" class="text-center py-4 text-muted">No transaction history found for this user.</td></tr>';
        }

        html += '</tbody></table>';

        $('#modalDataWrap').html(html);
    }

    // ── SweetAlert Delete Confirmation ──
    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        Swal.fire({
            title: 'Delete Transaction?',
            text: "User wallet balance will be recalculated automatically!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F43F5E',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Yes, Delete & Recalculate!',
            background: '#1E293B',
            color: '#F8FAFC'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        });
    });
</script>

{{-- WALLET LEDGER LOG MODAL DRAWER --}}
<div class="modal fade modal-wal-log" id="walletLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 p-2 border border-info border-opacity-25 text-info">
                        <i class="bx bx-wallet fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalUserTitle">User Wallet Statement</h5>
                        <small class="text-muted">Current Balance: <strong class="text-success ms-1" id="modalUserBalance">KWD 0.00</strong></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-5" id="modalLoader">
                    <div class="spinner-border text-info" role="status"></div>
                    <div class="text-info mt-2">Loading wallet ledger statement...</div>
                </div>
                <div id="modalDataWrap" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection
