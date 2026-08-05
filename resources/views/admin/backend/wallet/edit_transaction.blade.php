@extends('admin.master_admin')
@section('admin')

{{-- ========================
     EDIT WALLET TRANSACTION
======================== --}}

<style>
:root {
    --wal-cyan:       #06B6D4;
    --wal-cyan-light: #38BDF8;
    --wal-navy:       #0F172A;
    --wal-border:     rgba(255,255,255,0.08);
}

.wal-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--wal-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}
.wal-card-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.wal-card-header .icon-wrap {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--wal-cyan), var(--wal-cyan-light));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(6,182,212,0.35);
    flex-shrink: 0;
}
.wal-card-body { padding: 32px; }

.wal-card .form-control,
.wal-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    transition: all 0.25s !important;
    font-size: 0.92rem !important;
}
.wal-card .form-control:focus,
.wal-card .form-select:focus {
    background: rgba(6,182,212,0.08) !important;
    border-color: var(--wal-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
    color: #F8FAFC !important;
}
.wal-card .form-select option { background: #1E293B; color: #F8FAFC; }
.wal-card .form-label { color: #CBD5E1 !important; font-weight: 700 !important; font-size: 0.88rem !important; margin-bottom: 8px; }

.wal-section-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--wal-cyan-light);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wal-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

.btn-save-wal {
    background: linear-gradient(135deg, var(--wal-cyan), var(--wal-cyan-light));
    border: none;
    color: #0F172A;
    font-weight: 800;
    font-size: 1.05rem;
    padding: 14px 44px;
    border-radius: 14px;
    transition: all 0.25s;
    box-shadow: 0 6px 25px rgba(6,182,212,0.35);
}
.btn-save-wal:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(6,182,212,0.5);
    color: #0F172A;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR EDIT WALLET TRANSACTION PAGE
═════════════════════════════════════════════════════════════ */
html.light-theme .wal-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
}
html.light-theme .wal-card-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .wal-card-header h4 {
    color: #0F172A !important;
}
html.light-theme .wal-card-header small,
html.light-theme .wal-card-header p {
    color: #64748B !important;
}
html.light-theme .wal-card .form-control,
html.light-theme .wal-card .form-select {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .wal-card .form-control:focus,
html.light-theme .wal-card .form-select:focus {
    background: #FFFFFF !important;
    border-color: #0284C7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.15) !important;
    color: #0F172A !important;
}
html.light-theme .wal-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .wal-card .form-label {
    color: #334155 !important;
}
html.light-theme .wal-section-title {
    color: #0284C7 !important;
}
html.light-theme .wal-section-title::after {
    background: linear-gradient(90deg, rgba(2,132,199,0.3), transparent) !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-edit text-info me-2"></i>Edit Wallet Transaction #{{ $transaction->id }}
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Update transaction type, user, amount, or linked shipment / scheduled trip.
        </p>
    </div>
    <div>
        <a href="{{ route('all.wallets') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-left-arrow-alt fs-5"></i>
            <span>Back to All User Wallets</span>
        </a>
    </div>
</div>

{{-- Main Form Card --}}
<div class="wal-card">
    <div class="wal-card-header">
        <div class="icon-wrap"><i class="bx bx-edit-alt"></i></div>
        <div>
            <h4 class="fw-bold text-white mb-0">Update Transaction Details</h4>
            <small class="text-muted">Modify wallet transaction parameters below</small>
        </div>
    </div>
    <div class="wal-card-body">
        <form method="POST" action="{{ route('update.wallet.transaction') }}" id="walForm" autocomplete="off">
            @csrf
            <input type="hidden" name="id" value="{{ $transaction->id }}">

            {{-- ════ 1. USER & OPERATION TYPE ════ --}}
            <div class="wal-section-title"><i class="bx bx-user me-1"></i>User & Transaction Type</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Select User Account <span class="text-danger">*</span></label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">-- Select User --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $transaction->user_id == $u->id ? 'selected' : '' }}>
                                {{ $u->fname }} {{ $u->lname }} ({{ $u->email }}) [{{ ucfirst(str_replace('_', ' ', $u->role)) }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select">
                        <option value="deposit" {{ $transaction->type === 'deposit' ? 'selected' : '' }}>Deposit (+) / إيداع</option>
                        <option value="withdrawal" {{ $transaction->type === 'withdrawal' ? 'selected' : '' }}>Withdrawal (-) / سحب</option>
                        <option value="trip_earnings" {{ $transaction->type === 'trip_earnings' ? 'selected' : '' }}>Trip Earnings (+) / أرباح رحلة</option>
                        <option value="commission_deduction" {{ $transaction->type === 'commission_deduction' ? 'selected' : '' }}>Commission Deduction (-) / خصم عمولة</option>
                        <option value="refund" {{ $transaction->type === 'refund' ? 'selected' : '' }}>Refund (+) / استرجاع</option>
                    </select>
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 2. AMOUNT & DESCRIPTION ════ --}}
            <div class="wal-section-title"><i class="bx bx-dollar-circle me-1"></i>Amount & Description</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Transaction Amount (KWD) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" placeholder="0.00" value="{{ old('amount', $transaction->amount) }}">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-warning fw-bold">KWD</span>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Transaction Description</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. Deposit for shipment #104" value="{{ old('description', $transaction->description) }}">
                </div>
            </div>

            <div class="my-4" style="height:1px;background:rgba(255,255,255,0.06);"></div>

            {{-- ════ 3. LINKED RESOURCE (SHIPMENT OR SCHEDULED TRIP) ════ --}}
            <div class="wal-section-title"><i class="bx bx-link me-1"></i>Linked Order Reference (Optional)</div>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Linked Shipment Order (Optional)</label>
                    <select name="shipment_id" id="shipment_id" class="form-select">
                        <option value="">-- None (Not Linked to Shipment) --</option>
                        @foreach($shipments as $s)
                            <option value="{{ $s->id }}" {{ $transaction->shipment_id == $s->id ? 'selected' : '' }}>
                                Shipment Order #{{ $s->id }} ({{ $s->shipment_name ?: 'Shipment' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Linked Scheduled Trip (Optional)</label>
                    <select name="scheduled_trip_id" id="scheduled_trip_id" class="form-select">
                        <option value="">-- None (Not Linked to Scheduled Trip) --</option>
                        @foreach($trips as $tr)
                            <option value="{{ $tr->id }}" {{ $transaction->scheduled_trip_id == $tr->id ? 'selected' : '' }}>
                                Scheduled Trip #{{ $tr->id }} ({{ $tr->route->originCity->name_en ?? 'Origin' }} ➔ {{ $tr->route->destinationCity->name_en ?? 'Destination' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="text-end pt-3">
                <button type="submit" class="btn btn-save-wal d-inline-flex align-items-center gap-2" id="submitBtn">
                    <i class="bx bx-save fs-5"></i>
                    <span>Update Transaction</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {

    $('#walForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Updating Transaction...');
    });

});
</script>

@endsection
