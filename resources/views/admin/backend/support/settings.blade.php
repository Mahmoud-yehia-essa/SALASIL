@extends('admin.master_admin')
@section('admin')

<style>
    .support-card {
        background-color: var(--card-bg, #1e293b);
        border: 1px solid rgba(148, 163, 184, 0.15);
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .support-card:hover {
        border-color: rgba(6, 182, 212, 0.4);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
    }
    .channel-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .form-check-input:checked {
        background-color: #06B6D4;
        border-color: #06B6D4;
    }
    .form-check-input {
        cursor: pointer;
        width: 2.8em !important;
        height: 1.5em !important;
    }
</style>

<!-- Page Header -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-headphone text-info me-2"></i>Support & Help Center Settings
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Control customer support channels shown in the mobile app (Phone, Email, WhatsApp, Live Chat toggle, badges, and numbers).
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.support.settings.update') }}">
    @csrf

    <div class="row g-4">
        <!-- 1. LIVE CHAT -->
        <div class="col-12 col-md-6">
            <div class="support-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="channel-icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bx bx-chat"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Live Chat</h5>
                            <span class="text-muted small">Real-time instant messaging support</span>
                        </div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="support_livechat_enabled" id="support_livechat_enabled" value="1" {{ $settings['support_livechat_enabled'] ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label text-slate-300 small fw-semibold">Badge Text</label>
                    <input type="text" name="support_livechat_badge" class="form-control rounded-3" value="{{ $settings['support_livechat_badge'] }}" placeholder="e.g. Instant">
                    <div class="form-text text-muted">
                        {{ $settings['support_livechat_enabled'] ? 'Live chat is currently visible in the mobile app.' : 'Live chat is currently hidden in the mobile app.' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CALL US -->
        <div class="col-12 col-md-6">
            <div class="support-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="channel-icon-box bg-info bg-opacity-10 text-info">
                            <i class="bx bx-phone-call"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Call Us (Phone Support)</h5>
                            <span class="text-muted small">Direct customer hotline phone number</span>
                        </div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="support_call_enabled" id="support_call_enabled" value="1" {{ $settings['support_call_enabled'] ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-8">
                        <label class="form-label text-slate-300 small fw-semibold">Phone Number</label>
                        <input type="text" name="support_call_phone" class="form-control rounded-3" value="{{ $settings['support_call_phone'] }}" placeholder="+965 1800 0000">
                    </div>
                    <div class="col-4">
                        <label class="form-label text-slate-300 small fw-semibold">Badge</label>
                        <input type="text" name="support_call_badge" class="form-control rounded-3" value="{{ $settings['support_call_badge'] }}" placeholder="Toll Free">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. EMAIL -->
        <div class="col-12 col-md-6">
            <div class="support-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="channel-icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="bx bx-envelope"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Email Support</h5>
                            <span class="text-muted small">Official support email inbox</span>
                        </div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="support_email_enabled" id="support_email_enabled" value="1" {{ $settings['support_email_enabled'] ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label text-slate-300 small fw-semibold">Support Email Address</label>
                    <input type="email" name="support_email_address" class="form-control rounded-3" value="{{ $settings['support_email_address'] }}" placeholder="support@salasel.com">
                </div>
            </div>
        </div>

        <!-- 4. WHATSAPP -->
        <div class="col-12 col-md-6">
            <div class="support-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="channel-icon-box bg-success bg-opacity-10 text-success">
                            <i class="bx bxl-whatsapp"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">WhatsApp Support</h5>
                            <span class="text-muted small">Direct WhatsApp instant messaging number</span>
                        </div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="support_whatsapp_enabled" id="support_whatsapp_enabled" value="1" {{ $settings['support_whatsapp_enabled'] ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label text-slate-300 small fw-semibold">WhatsApp Number (with country code)</label>
                    <input type="text" name="support_whatsapp_number" class="form-control rounded-3" value="{{ $settings['support_whatsapp_number'] }}" placeholder="+965 9876 5432">
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button Bar -->
    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow">
            <i class="bx bx-check-double fs-5"></i>
            <span>Save Support Settings</span>
        </button>
    </div>
</form>

@endsection
