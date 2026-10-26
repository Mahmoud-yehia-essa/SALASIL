@extends('admin.master_admin')
@section('admin')

{{-- ============================================================
     ADD SCHEDULED TRIP ORDER — 8-STEP WIZARD (LINKED TO TRIP)
============================================================ --}}

<style>
/* ─── Wizard Global Variables ─── */
:root {
    --wz-cyan:       #06B6D4;
    --wz-cyan-light: #38BDF8;
    --wz-navy:       #0F172A;
    --wz-card:       #1E293B;
    --wz-border:     rgba(255,255,255,0.08);
    --wz-success:    #10B981;
    --wz-warning:    #F59E0B;
    --wz-danger:     #F43F5E;
}

/* ─── Trip Selector Hero Card ─── */
.trip-hero-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid rgba(6,182,212,0.3);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 28px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.35);
    position: relative;
    overflow: hidden;
}
.trip-hero-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--wz-cyan), var(--wz-cyan-light), #10B981);
}
.trip-spec-pill {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.trip-spec-pill .icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: rgba(6,182,212,0.15);
    color: var(--wz-cyan-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.trip-spec-pill .content .lbl {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #94A3B8;
    letter-spacing: 0.5px;
}
.trip-spec-pill .content .val {
    font-size: 0.88rem;
    font-weight: 700;
    color: #F8FAFC;
}

/* ─── Stepper Progress Bar ─── */
.wz-progress-wrap {
    background: rgba(255,255,255,0.04);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 28px;
    border: 1px solid var(--wz-border);
    backdrop-filter: blur(10px);
}
.wz-steps-track {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}
.wz-steps-track::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 20px;
    right: 20px;
    height: 2px;
    background: rgba(255,255,255,0.08);
    z-index: 0;
}
.wz-step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    cursor: pointer;
    flex: 1;
}
.wz-step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
    border: 2px solid rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #64748B;
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    position: relative;
}
.wz-step-item.active .wz-step-circle {
    background: var(--wz-cyan);
    border-color: var(--wz-cyan);
    color: #fff;
    box-shadow: 0 0 20px rgba(6,182,212,0.5);
    transform: scale(1.15);
}
.wz-step-item.done .wz-step-circle {
    background: var(--wz-success);
    border-color: var(--wz-success);
    color: #fff;
}
.wz-step-label {
    margin-top: 8px;
    font-size: 11px;
    font-weight: 600;
    color: #64748B;
    text-align: center;
    white-space: nowrap;
    transition: color 0.25s;
    max-width: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.wz-step-item.active .wz-step-label { color: var(--wz-cyan-light); }
.wz-step-item.done   .wz-step-label { color: var(--wz-success); }

/* ─── Responsive Stepper for Mobile ─── */
@media (max-width: 767.98px) {
    .wz-progress-wrap {
        padding: 14px 10px;
        margin-bottom: 20px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(6,182,212,0.4) transparent;
        border-radius: 16px;
    }
    .wz-steps-track {
        justify-content: flex-start;
        gap: 12px;
        min-width: max-content;
        padding: 4px 6px;
    }
    .wz-step-item { flex: 0 0 auto; width: 72px; }
    .wz-step-circle { width: 36px; height: 36px; font-size: 12px; }
    .wz-step-label { font-size: 10px; max-width: 72px; }
}

/* ─── Wizard Card ─── */
.wz-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--wz-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}
.wz-card-header {
    background: linear-gradient(135deg, rgba(6,182,212,0.12) 0%, rgba(56,189,248,0.06) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 22px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.wz-card-header .step-icon {
    width: 48px; height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--wz-cyan), var(--wz-cyan-light));
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #fff;
    box-shadow: 0 6px 18px rgba(6,182,212,0.35);
    flex-shrink: 0;
}
.wz-card-header h4 { font-size: 1.15rem; font-weight: 800; color: #F8FAFC; margin-bottom: 2px; }
.wz-card-header p  { font-size: 0.82rem; color: #94A3B8; margin-bottom: 0; }
.wz-card-header .step-counter {
    background: rgba(6,182,212,0.15);
    border: 1px solid rgba(6,182,212,0.3);
    color: var(--wz-cyan-light);
    font-size: 0.76rem;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 20px;
}

.wz-card-body { padding: 32px; }
.wz-card-footer {
    border-top: 1px solid rgba(255,255,255,0.06);
    padding: 20px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(0,0,0,0.15);
}

.wz-step-panel { display: none; }
.wz-step-panel.active { display: block; animation: wzFadeIn 0.3s ease; }
@keyframes wzFadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ─── Buttons ─── */
.btn-wz-next {
    background: linear-gradient(135deg, var(--wz-cyan), var(--wz-cyan-light));
    border: none;
    color: #0F172A;
    font-weight: 700;
    padding: 12px 30px;
    border-radius: 12px;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 6px 20px rgba(6,182,212,0.3);
    transition: all 0.25s;
}
.btn-wz-next:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(6,182,212,0.45);
    color: #0F172A;
}
.btn-wz-prev {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    color: #94A3B8;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 12px;
    display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.25s;
}
.btn-wz-prev:hover {
    background: rgba(255,255,255,0.1);
    color: #F8FAFC;
}
.btn-wz-submit {
    background: linear-gradient(135deg, #10B981, #059669);
    border: none;
    color: #fff;
    font-weight: 800;
    padding: 12px 36px;
    border-radius: 12px;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 6px 20px rgba(16,185,129,0.35);
    transition: all 0.25s;
}
.btn-wz-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(16,185,129,0.5);
    color: #fff;
}

/* ─── Form Inputs ─── */
.wz-card .form-control,
.wz-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    transition: all 0.25s !important;
    font-size: 0.92rem !important;
}
.wz-card .form-control:focus,
.wz-card .form-select:focus {
    background: rgba(6,182,212,0.08) !important;
    border-color: var(--wz-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
    color: #F8FAFC !important;
}
.wz-card .form-select option { background: #1E293B; color: #F8FAFC; }
.wz-card .form-label { color: #CBD5E1 !important; font-weight: 700 !important; font-size: 0.88rem !important; margin-bottom: 8px; }

.wz-section-title {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--wz-cyan-light);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wz-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(6,182,212,0.4), transparent);
}

/* ─── Pre-filled Info Alert Banner ─── */
.prefill-banner {
    background: rgba(6,182,212,0.1);
    border: 1px solid rgba(6,182,212,0.25);
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #E2E8F0;
    font-size: 0.86rem;
}
.prefill-banner .ic {
    color: var(--wz-cyan);
    font-size: 1.3rem;
    flex-shrink: 0;
}

/* ─── Customer Search & List ─── */
.customer-search { position: relative; margin-bottom: 14px; }
.customer-search .search-ic { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748B; font-size: 18px; pointer-events: none; }
.customer-search input { padding-left: 44px !important; }
.customer-list-container {
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid var(--wz-border);
    border-radius: 12px;
    background: rgba(15,23,42,0.5);
    scrollbar-width: thin;
}
.customer-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    cursor: pointer;
    transition: all 0.2s;
}
.customer-list-item:last-child { border-bottom: none; }
.customer-list-item:hover { background: rgba(6,182,212,0.08); }
.customer-list-item.selected {
    background: rgba(6,182,212,0.18);
    border-left: 3px solid var(--wz-cyan);
}
.customer-list-item .avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(6,182,212,0.15);
    border: 1px solid rgba(6,182,212,0.3);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: var(--wz-cyan-light);
    flex-shrink: 0; overflow: hidden;
}
.customer-list-item .info { flex: 1; min-width: 0; }
.customer-list-item .info .name { font-weight: 700; color: #F8FAFC; font-size: 0.9rem; }
.customer-list-item .info .sub  { font-size: 0.78rem; color: #64748B; }
.role-badge { font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 8px; text-transform: uppercase; }
.role-badge.company    { background: rgba(139,92,246,0.15); color: #A78BFA; border: 1px solid rgba(139,92,246,0.3); }
.role-badge.individual { background: rgba(6,182,212,0.15); color: #38BDF8; border: 1px solid rgba(6,182,212,0.3); }

/* ─── Map Styles ─── */
.map-container {
    height: 320px;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid var(--wz-border);
    position: relative;
    background: #0f172a;
}
.map-container #pickupMap,
.map-container #dropoffMap { width: 100%; height: 100%; }
.map-coords-badge {
    position: absolute; bottom: 12px; left: 12px;
    background: rgba(15,23,42,0.88);
    border: 1px solid rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    border-radius: 10px;
    padding: 6px 14px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #E2E8F0;
    z-index: 5;
    pointer-events: none;
}
.map-search-wrap { position: relative; }
.map-search-inner {
    display: flex; align-items: center;
    background: rgba(15,23,42,0.85);
    border: 1px solid rgba(6,182,212,0.4);
    border-radius: 10px;
    padding: 8px 14px;
    gap: 10px;
}
.map-search-input {
    background: transparent; border: none; outline: none;
    color: #F8FAFC; width: 100%; font-size: 0.88rem;
}
.map-search-input::placeholder { color: #64748B; }

/* ─── Review Cards ─── */
.rv-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
}
.rv-card-title {
    font-size: 0.82rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 0.8px; color: var(--wz-cyan-light); margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}
.rv-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 0.88rem; }
.rv-row:last-child { border-bottom: none; }
.rv-row .lbl { color: #94A3B8; font-weight: 600; }
.rv-row .val { color: #F8FAFC; font-weight: 700; text-align: right; }

/* ─── Light Mode Overrides ─── */
html.light-theme .trip-hero-card,
html.light-theme .wz-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06) !important;
}
html.light-theme .trip-spec-pill,
html.light-theme .rv-card {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .trip-spec-pill .content .val,
html.light-theme .rv-row .val,
html.light-theme .wz-card-header h4 {
    color: #0F172A !important;
}
html.light-theme .wz-card .form-control,
html.light-theme .wz-card .form-select {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .wz-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .wz-card .form-label {
    color: #334155 !important;
}
html.light-theme .customer-list-container {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .customer-list-item .info .name {
    color: #0F172A !important;
}
html.light-theme .wz-progress-wrap {
    background: #F1F5F9 !important;
    border-color: #E2E8F0 !important;
}

/* ─── Step 3 Truck Specs Custom Styles (App-Aligned) ─── */
.truck-select-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.truck-select-card {
    background: rgba(255,255,255,0.04);
    border: 1.5px solid rgba(255,255,255,0.1);
    border-radius: 14px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 104px;
    position: relative;
    user-select: none;
}
.truck-select-card:hover {
    border-color: rgba(6,182,212,0.45);
    background: rgba(6,182,212,0.06);
    transform: translateY(-2px);
}
.truck-select-card.selected {
    background: rgba(6,182,212,0.12);
    border-color: var(--wz-cyan);
    box-shadow: 0 6px 20px rgba(6,182,212,0.22);
}
.truck-select-card .card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.truck-select-card .truck-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(6,182,212,0.15);
    color: var(--wz-cyan-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    transition: all 0.2s;
}
.truck-select-card.selected .truck-icon-wrap {
    background: var(--wz-cyan);
    color: #0F172A;
}
.truck-select-card .radio-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: transparent;
    transition: all 0.2s;
}
.truck-select-card.selected .radio-indicator {
    border-color: var(--wz-cyan);
    background: var(--wz-cyan);
    color: #0F172A;
}
.truck-select-card .truck-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #F8FAFC;
    margin-bottom: 2px;
}
.truck-select-card .truck-cap {
    font-size: 0.78rem;
    color: #94A3B8;
    font-weight: 500;
}

/* Truck Specifications Highlight Banner */
.truck-spec-highlight-card {
    background: linear-gradient(135deg, rgba(6,182,212,0.08) 0%, rgba(56,189,248,0.04) 100%);
    border: 1px solid rgba(6,182,212,0.28);
    border-radius: 14px;
    padding: 16px 20px;
}
.truck-spec-highlight-card .spec-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
    color: var(--wz-cyan-light);
    font-weight: 700;
    font-size: 0.92rem;
}
.truck-spec-highlight-card .spec-desc {
    color: #CBD5E1;
    font-size: 0.86rem;
    line-height: 1.55;
    margin-bottom: 0;
}

/* Number of Trucks Counter Box */
.truck-counter-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 8px 16px;
    min-height: 50px;
}
.truck-counter-text {
    font-size: 1rem;
    font-weight: 700;
    color: #F8FAFC;
}
.truck-counter-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}
.truck-counter-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.2s;
    background: rgba(255,255,255,0.06);
    color: var(--wz-cyan-light);
}
.truck-counter-btn:hover:not(.disabled) {
    background: rgba(6,182,212,0.2);
    color: #F8FAFC;
}
.truck-counter-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    color: #64748B;
}
.truck-counter-btn.inc {
    background: var(--wz-cyan);
    border-color: var(--wz-cyan);
    color: #0F172A;
    font-weight: 700;
}
.truck-counter-btn.inc:hover {
    background: var(--wz-cyan-light);
    transform: scale(1.05);
}

/* Special Equipment Chips */
.equipment-chips-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.equipment-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 100px;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.04);
    color: #CBD5E1;
    font-size: 0.84rem;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}
.equipment-chip:hover {
    border-color: rgba(6,182,212,0.45);
    background: rgba(6,182,212,0.08);
    color: #F8FAFC;
}
.equipment-chip.active {
    background: rgba(6,182,212,0.18);
    border-color: var(--wz-cyan);
    color: var(--wz-cyan-light);
    box-shadow: 0 2px 10px rgba(6,182,212,0.25);
}
.equipment-chip .chip-check {
    font-size: 14px;
    display: none;
}
.equipment-chip.active .chip-check {
    display: inline-flex;
}

/* Light Mode Overrides for Truck Step */
html.light-theme .truck-select-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .truck-select-card:hover {
    border-color: #38BDF8 !important;
    background: #F0F9FF !important;
}
html.light-theme .truck-select-card.selected {
    background: #F0FDF4 !important;
    border-color: #0284C7 !important;
    box-shadow: 0 4px 14px rgba(2,132,199,0.15) !important;
}
html.light-theme .truck-select-card .truck-title {
    color: #0F172A !important;
}
html.light-theme .truck-spec-highlight-card {
    background: #F4F8FD !important;
    border-color: #E8F0FA !important;
}
html.light-theme .truck-spec-highlight-card .spec-header {
    color: #3A77BB !important;
}
html.light-theme .truck-spec-highlight-card .spec-desc {
    color: #475466 !important;
}
html.light-theme .truck-counter-box {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .truck-counter-text {
    color: #0F172A !important;
}
html.light-theme .truck-counter-btn.dec {
    background: #F4F8FD !important;
    border-color: #E2E8F0 !important;
    color: #3A77BB !important;
}
html.light-theme .equipment-chip {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #344053 !important;
}
html.light-theme .equipment-chip.active {
    background: #F4F8FD !important;
    border-color: #3A77BB !important;
    color: #3A77BB !important;
}

/* ─── Schedule & Time Slots (App-Aligned) ─── */
.form-icon-wrap {
    position: relative;
}
.form-icon-wrap .form-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748B;
    font-size: 1.15rem;
    pointer-events: none;
}
.form-icon-wrap.icon-left .form-icon {
    right: auto;
    left: 14px;
}
.form-icon-wrap.icon-left .form-control {
    padding-left: 42px !important;
}
.timeslot-chips-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}
.timeslot-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 100px;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.04);
    color: #CBD5E1;
    font-size: 0.84rem;
    font-weight: 500;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}
.timeslot-chip:hover {
    border-color: rgba(6,182,212,0.45);
    background: rgba(6,182,212,0.08);
    color: #F8FAFC;
}
.timeslot-chip.active {
    background: rgba(6,182,212,0.18);
    border-color: var(--wz-cyan);
    color: var(--wz-cyan-light);
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(6,182,212,0.25);
}
.timeslot-chip .slot-check {
    font-size: 14px;
    display: none;
}
.timeslot-chip.active .slot-check {
    display: inline-flex;
}

html.light-theme .timeslot-chip {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #344053 !important;
}
html.light-theme .timeslot-chip.active {
    background: #F4F8FD !important;
    border-color: #3A77BB !important;
    color: #3A77BB !important;
}
html.light-theme .form-icon-wrap .form-icon {
    color: #667084 !important;
}

/* ─── Step 7 Document Upload Styles (App-Aligned) ─── */
.doc-upload-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.doc-upload-card {
    background: rgba(255,255,255,0.03);
    border: 1.5px solid rgba(255,255,255,0.09);
    border-radius: 16px;
    padding: 16px 18px;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 170px;
}
.doc-upload-card:hover {
    border-color: rgba(6,182,212,0.35);
    background: rgba(6,182,212,0.03);
}
.doc-upload-card.has-file {
    border-color: rgba(16,185,129,0.5);
    background: rgba(16,185,129,0.04);
}
.doc-upload-card .card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}
.doc-upload-card .doc-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.doc-upload-card .doc-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
    background: rgba(6,182,212,0.12);
    color: var(--wz-cyan-light);
}
.doc-upload-card.has-file .doc-icon-badge {
    background: rgba(16,185,129,0.18);
    color: #10B981;
}
.doc-upload-card .doc-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #F8FAFC;
    margin-bottom: 2px;
}
.doc-upload-card .doc-hint {
    font-size: 0.76rem;
    color: #94A3B8;
}
.doc-dropzone-box {
    border: 1.5px dashed rgba(255,255,255,0.18);
    border-radius: 12px;
    padding: 16px 14px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: rgba(255,255,255,0.02);
}
.doc-dropzone-box:hover {
    border-color: var(--wz-cyan);
    background: rgba(6,182,212,0.08);
}
.doc-dropzone-box i {
    font-size: 1.8rem;
    color: var(--wz-cyan-light);
    margin-bottom: 4px;
    display: block;
}
.doc-dropzone-box .dz-text {
    font-size: 0.84rem;
    font-weight: 600;
    color: #CBD5E1;
    margin-bottom: 2px;
}
.doc-dropzone-box .dz-sub {
    font-size: 0.74rem;
    color: #64748B;
}
.doc-preview-box {
    display: none;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 12px 14px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.doc-preview-box .file-info {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow: hidden;
}
.doc-preview-box .file-type-pill {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    background: rgba(217,44,32,0.2);
    color: #EF4444;
}
.doc-preview-box .file-name-text {
    font-size: 0.85rem;
    font-weight: 600;
    color: #F8FAFC;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 170px;
}
.doc-preview-box .file-size-text {
    font-size: 0.74rem;
    color: #94A3B8;
}

html.light-theme .doc-upload-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .doc-upload-card.has-file {
    background: #F0FDF4 !important;
    border-color: #86EFAC !important;
}
html.light-theme .doc-upload-card .doc-name {
    color: #0F172A !important;
}
html.light-theme .doc-dropzone-box {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .doc-dropzone-box:hover {
    border-color: #0284C7 !important;
    background: #F0F9FF !important;
}
html.light-theme .doc-dropzone-box .dz-text {
    color: #334155 !important;
}
html.light-theme .doc-preview-box {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .doc-preview-box .file-name-text {
    color: #0F172A !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-calendar-plus text-info me-2"></i>Add Scheduled Trip Order
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Book and link an order to a fixed Scheduled Trip with complete 8-step cargo, truck & location specifications.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('all.scheduled.trip.orders') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>All Trip Orders</span>
        </a>
        <a href="{{ route('all.scheduled.trips') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-calendar fs-5"></i>
            <span>All Trips</span>
        </a>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════
     HERO: SCHEDULED TRIP SELECTION & LIVE PRE-FILLED SPECS CARD
═════════════════════════════════════════════════════════════ --}}
<div class="trip-hero-card">
    <div class="row g-3 align-items-center mb-3">
        <div class="col-12 col-md-4">
            <label class="form-label text-info fw-bold mb-1" style="font-size:0.88rem;">
                <i class="bx bx-calendar-event me-1"></i>Select Scheduled Trip <span class="text-danger">*</span>
            </label>
            <select id="scheduled_trip_select" class="form-select form-select-lg fw-bold" style="border: 2px solid var(--wz-cyan) !important;">
                <option value="">-- Choose an Active Scheduled Trip --</option>
                @foreach($trips as $t)
                    <option value="{{ $t->id }}" {{ (isset($selectedTripId) && $selectedTripId == $t->id) ? 'selected' : '' }}>
                        Trip #{{ $t->id }}: {{ $t->route->originCity->name_en ?? 'Origin' }} ➔ {{ $t->route->destinationCity->name_en ?? 'Dest' }} ({{ $t->trip_date ?: 'Flexible Schedule' }}) — KWD {{ number_format($t->price, 2) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-8">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-2">
                    <i class="bx bx-link me-1"></i>Linked to Scheduled Trip Process
                </span>
                <span id="tripSyncStatus" class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-3 py-2" style="display:none;">
                    <i class="bx bx-check-double me-1"></i>Trip Details Pre-filled
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Selecting a trip will automatically inherit its route origin, destination coordinates, truck type and base price into the 8-step wizard below.
            </p>
        </div>
    </div>

    {{-- Live Specs Grid (Populated dynamically) --}}
    <div id="tripSpecsGrid" style="display:none;">
        <div class="row g-2 g-md-3">
            <div class="col-6 col-md-3">
                <div class="trip-spec-pill">
                    <div class="icon"><i class="bx bx-git-repo-forked"></i></div>
                    <div class="content">
                        <div class="lbl">Route</div>
                        <div class="val text-truncate" id="pillRoute">—</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trip-spec-pill">
                    <div class="icon"><i class="bx bx-calendar"></i></div>
                    <div class="content">
                        <div class="lbl">Date & Time</div>
                        <div class="val" id="pillDateTime">—</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trip-spec-pill">
                    <div class="icon"><i class="bx bx-truck"></i></div>
                    <div class="content">
                        <div class="lbl">Truck Type</div>
                        <div class="val text-truncate" id="pillTruck">—</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trip-spec-pill">
                    <div class="icon"><i class="bx bx-purchase-tag-alt"></i></div>
                    <div class="content">
                        <div class="lbl">Trip Unit Price</div>
                        <div class="val text-warning" id="pillPrice">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════
     MAIN 9-STEP WIZARD FORM
═════════════════════════════════════════════════════════════ --}}
<form method="POST" action="{{ route('store.scheduled.trip.order') }}" id="wizardOrderForm" enctype="multipart/form-data" autocomplete="off">
    @csrf

    {{-- Hidden Trip ID (Linked from selection above) --}}
    <input type="hidden" name="scheduled_trip_id" id="hidden_trip_id" value="{{ $selectedTripId ?? '' }}">

    {{-- ─── Stepper Progress Bar ─── --}}
    <div class="wz-progress-wrap">
        <div class="wz-steps-track" id="stepsTrack">
            @php
            $steps = [
                ['icon'=>'bx bx-user', 'label'=>'Customer'],
                ['icon'=>'bx bx-package', 'label'=>'Cargo'],
                ['icon'=>'bx bx-truck', 'label'=>'Truck'],
                ['icon'=>'bx bx-map-pin', 'label'=>'Pickup'],
                ['icon'=>'bx bx-navigation', 'label'=>'Delivery'],
                ['icon'=>'bx bx-phone', 'label'=>'Contacts'],
                ['icon'=>'bx bx-file', 'label'=>'Documents'],
                ['icon'=>'bx bx-dollar-circle', 'label'=>'Pricing'],
                ['icon'=>'bx bx-check-shield', 'label'=>'Review'],
            ];
            @endphp
            @foreach($steps as $idx => $step)
            <div class="wz-step-item {{ $idx === 0 ? 'active' : '' }}" data-step="{{ $idx + 1 }}">
                <div class="wz-step-circle">
                    @if($idx === 0)
                        <i class="{{ $step['icon'] }}"></i>
                    @else
                        {{ $idx + 1 }}
                    @endif
                </div>
                <div class="wz-step-label">{{ $step['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ─── Wizard Card Container ─── --}}
    <div class="wz-card">

        {{-- ══════════════════════════════════════════════════════
             STEP 1: CUSTOMER INFORMATION
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel active" id="step1">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-user-circle"></i></div>
                <div>
                    <h4>Step 1: Customer Information</h4>
                    <p>Select the customer booking this scheduled trip order, or add a new customer.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 1 of 9</span></div>
            </div>
            <div class="wz-card-body">
                <input type="hidden" name="customer_id" id="selectedCustomerId" value="">

                <div class="wz-section-title"><i class="bx bx-search-alt me-1"></i>Select Customer</div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="customer-search">
                            <i class="bx bx-search-alt search-ic"></i>
                            <input type="text" id="customerSearch" class="form-control" placeholder="Search customer by name, email or phone...">
                        </div>
                        <div class="customer-list-container" id="customerList">
                            @foreach($customers as $customer)
                            <div class="customer-list-item"
                                 data-id="{{ $customer->id }}"
                                 data-name="{{ $customer->fname }} {{ $customer->lname }}"
                                 data-email="{{ $customer->email }}"
                                 data-phone="{{ $customer->phone }}"
                                 data-search="{{ strtolower($customer->fname . ' ' . $customer->lname . ' ' . $customer->email . ' ' . $customer->phone) }}"
                            >
                                <div class="avatar">
                                    @if($customer->photo)
                                        <img src="{{ asset('upload/user_images/'.$customer->photo) }}" alt="{{ $customer->fname }}" style="width:40px;height:40px;object-fit:cover;">
                                    @else
                                        {{ strtoupper(substr($customer->fname, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="info">
                                    <div class="name">{{ $customer->fname }} {{ $customer->lname }}</div>
                                    <div class="sub">
                                        {{ $customer->email }}
                                        <span class="role-badge {{ $customer->role === 'company_customer' ? 'company' : 'individual' }} ms-1">
                                            {{ $customer->role === 'company_customer' ? 'Company' : 'Individual' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @if($customers->isEmpty())
                            <div class="text-center text-muted py-4">No registered customers found.</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Selected Customer Details View --}}
                <div id="customerInfoPanel" style="display:none;" class="mt-4">
                    <div class="wz-section-title"><i class="bx bx-check-circle me-1"></i>Selected Customer Summary</div>
                    <div id="customerInfoContent"></div>
                </div>
            </div>
            <div class="wz-card-footer">
                <div></div>
                <button type="button" class="btn-wz-next" data-goto="2">
                    <span>Next: Cargo Details</span> <i class="bx bx-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             STEP 2: CARGO & SHIPMENT DETAILS
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel" id="step2">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-package"></i></div>
                <div>
                    <h4>Step 2: Cargo & Shipment Details</h4>
                    <p>Enter package count, dimensions, weight, HS Code, and cargo nature for the trip.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 2 of 9</span></div>
            </div>
            <div class="wz-card-body">
                <div class="prefill-banner mb-3" id="cargoPrefillBanner" style="display:none;">
                    <i class="bx bx-check-circle ic text-success"></i>
                    <div id="cargoPrefillText">
                        <strong>Pre-filled from Scheduled Trip:</strong> Cargo Type & Nature synced.
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Shipment / Cargo Label</label>
                        <input type="text" name="shipment_name" id="shipment_name" class="form-control" placeholder="e.g. Commercial Pallets for Riyadh">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Shipment Type</label>
                        <select name="shipment_type_id" id="shipment_type_id" class="form-select">
                            <option value="">-- Select Type --</option>
                            @foreach($shipmentTypes as $st)
                                <option value="{{ $st->id }}">{{ $st->name_en }} ({{ $st->name_ar }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Shipment Nature</label>
                        <select name="shipment_nature_id" id="shipment_nature_id" class="form-select">
                            <option value="">-- Select Nature --</option>
                            @foreach($shipmentNatures as $sn)
                                <option value="{{ $sn->id }}">{{ $sn->name_en }} ({{ $sn->name_ar }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="wz-section-title"><i class="bx bx-ruler me-1"></i>Dimensions & Weight</div>
                <div class="row g-4 mb-4">
                    <div class="col-6 col-md-3">
                        <label class="form-label">Length (cm)</label>
                        <input type="number" step="0.1" name="length" id="length" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Width (cm)</label>
                        <input type="number" step="0.1" name="width" id="width" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Height (cm)</label>
                        <input type="number" step="0.1" name="height" id="height" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Total Weight (Tons)</label>
                        <input type="number" step="0.1" name="weight" id="weight" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Number of Packages / Pallets</label>
                        <input type="number" name="packages_count" id="packages_count" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">HS Tariff Code (Harmonized System)</label>
                        <input type="text" name="hs_code" id="hs_code" class="form-control" placeholder="e.g. 8408.20.00">
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_fragile" value="1" id="is_fragile">
                            <label class="form-check-label text-warning fw-bold ms-2" for="is_fragile">
                                <i class="bx bx-shield-quarter me-1"></i>Fragile Cargo (Requires Careful Handling)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="wz-section-title"><i class="bx bx-detail me-1"></i>Cargo Description & Notes</div>
                <div class="row g-4">
                    <div class="col-12">
                        <textarea name="goods_description" id="goods_description" class="form-control" rows="3" placeholder="Provide description of the items, customs requirements or packaging specifications..."></textarea>
                    </div>
                </div>
            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev" data-goto="1">
                    <i class="bx bx-chevron-left fs-5"></i> <span>Back</span>
                </button>
                <button type="button" class="btn-wz-next" data-goto="3">
                    <span>Next: Truck Specifications</span> <i class="bx bx-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             STEP 3: TRUCK SPECIFICATIONS
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel" id="step3">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-truck"></i></div>
                <div>
                    <h4>Step 3: Truck Specifications</h4>
                    <p>Verified against the Scheduled Trip configuration. You may adjust or keep default.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 3 of 9</span></div>
            </div>
            <div class="wz-card-body">
                <div class="prefill-banner" id="truckPrefillBanner">
                    <i class="bx bx-info-circle ic"></i>
                    <div>
                        <strong>Inherited from Scheduled Trip:</strong> Truck type and sub-type are automatically synced with the trip's specifications.
                    </div>
                </div>

                {{-- 1. Truck Type Select Cards Grid (Matching Mobile App) --}}
                <div class="wz-section-title">
                    <i class="bx bx-grid-alt"></i>Select Suitable Truck
                </div>
                <div class="truck-select-grid" id="truckCardsGrid">
                    @foreach($truckTypes as $tt)
                        @php
                            $key = strtolower(trim($tt->name_en));
                            $icons = [
                                'flatbed'   => 'bx bx-layer',
                                'reefer'    => 'bx bx-snowflake',
                                'trailer'   => 'bx bxs-truck',
                                'closed'    => 'bx bx-package',
                                'truck'     => 'bx bx-truck',
                                'container' => 'bx bx-box',
                            ];
                            $icon = $icons[$key] ?? 'bx bx-truck';
                            $capacities = [
                                'flatbed'   => 'Up to 25 Tons',
                                'reefer'    => 'Up to 22 Tons',
                                'trailer'   => 'Up to 30 Tons',
                                'closed'    => 'Up to 15 Tons',
                                'truck'     => 'Up to 8 Tons',
                                'container' => 'Up to 28 Tons',
                            ];
                            $cap = $capacities[$key] ?? ($tt->max_weight ? 'Up to ' . (float)$tt->max_weight . ' Tons' : 'Standard Capacity');
                        @endphp
                        <div class="truck-select-card" data-id="{{ $tt->id }}" data-key="{{ $key }}" data-name="{{ $tt->name_en }}" data-name-ar="{{ $tt->name_ar }}">
                            <div class="card-top">
                                <div class="truck-icon-wrap">
                                    <i class="{{ $icon }}"></i>
                                </div>
                                <div class="radio-indicator">
                                    <i class="bx bx-check"></i>
                                </div>
                            </div>
                            <div class="card-bottom">
                                <div class="truck-title">{{ $tt->name_en }}</div>
                                <div class="truck-cap">{{ $cap }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Form Selects (Synced with Cards) --}}
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Truck Type <span class="text-danger">*</span></label>
                        <select name="truck_type_id" id="truck_type_id" class="form-select">
                            <option value="">-- Select Truck Type --</option>
                            @foreach($truckTypes as $tt)
                                <option value="{{ $tt->id }}" data-name="{{ $tt->name_en }}" data-name-ar="{{ $tt->name_ar }}">{{ $tt->name_en }} ({{ $tt->name_ar }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Truck Sub-Type (Optional)</label>
                        <select name="truck_sub_type_id" id="truck_sub_type_id" class="form-select">
                            <option value="">-- Select Truck Type First --</option>
                        </select>
                    </div>
                </div>

                {{-- 2. Selected Truck Specifications Highlight Card --}}
                <div class="truck-spec-highlight-card mb-4" id="truckSpecHighlightCard">
                    <div class="spec-header" id="specHighlightTitle">
                        <i class="bx bx-info-circle fs-5 me-1 text-info"></i>
                        <span>Trailer (جَرَّار) Specifications</span>
                    </div>
                    <div class="spec-desc" id="specHighlightDesc">
                        Heavy duty articulated trailer for large scale commercial shipments. Dimensions: 13.6m × 2.50m. Capacity: Up to 30 Tons.
                    </div>
                </div>

                {{-- 3. Number of Trucks Counter --}}
                <div class="mb-4">
                    <label class="form-label">Number of Trucks</label>
                    <div class="truck-counter-box">
                        <div class="truck-counter-text" id="truckCountDisplay">1 Truck</div>
                        <div class="truck-counter-controls">
                            <button type="button" class="truck-counter-btn dec disabled" id="btnDecTruck" title="Decrease">
                                <i class="bx bx-minus"></i>
                            </button>
                            <button type="button" class="truck-counter-btn inc" id="btnIncTruck" title="Increase">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="number_of_trucks" id="number_of_trucks" value="1">
                </div>

                {{-- 4. Special Equipment & Features (Optional) --}}
                <div class="mb-4">
                    <label class="form-label">Special Equipment & Features (Optional)</label>
                    <div class="equipment-chips-wrap" id="equipmentChipsWrap">
                        @php
                            $equipments = [
                                'Tail Lift',
                                'Hydraulic Ramp',
                                'GPS Tracking',
                                'Tarpaulin Cover',
                                'Hazardous Certified',
                                'Side Curtains',
                            ];
                        @endphp
                        @foreach($equipments as $eq)
                            <div class="equipment-chip" data-value="{{ $eq }}">
                                <i class="bx bx-check chip-check"></i>
                                <span>{{ $eq }}</span>
                                <input type="checkbox" name="special_equipment[]" value="{{ $eq }}" class="d-none eq-checkbox">
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Driver & Truck Notes (Optional) --}}
                <div class="mb-3">
                    <label class="form-label">Driver & Truck Notes (Optional)</label>
                    <textarea name="truck_notes" id="truck_notes" class="form-control" rows="2" placeholder="e.g. Requires driver assistance or specific offloading tools"></textarea>
                </div>
            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev" data-goto="2">
                    <i class="bx bx-chevron-left fs-5"></i> <span>Back</span>
                </button>
                <button type="button" class="btn-wz-next" data-goto="4">
                    <span>Next: Pickup Location</span> <i class="bx bx-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             STEP 4: PICKUP LOCATION
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel" id="step4">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-map-pin"></i></div>
                <div>
                    <h4>Step 4: Pickup Location (Origin)</h4>
                    <p>Pre-filled from the Scheduled Trip Route Origin. You can refine schedule, address & map pin.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 4 of 9</span></div>
            </div>
            <div class="wz-card-body">
                <div class="prefill-banner" id="pickupPrefillBanner">
                    <i class="bx bx-check-shield ic"></i>
                    <div>
                        <strong>Route Origin:</strong> Pre-filled from the Route Origin City & Country.
                    </div>
                </div>

                {{-- Schedule: Loading Date & Time --}}
                <div class="row g-4 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Loading date <span class="text-danger">*</span></label>
                        <div class="form-icon-wrap">
                            <input type="date" name="loading_date" id="loading_date" class="form-control" value="{{ date('Y-m-d') }}">
                            <i class="bx bx-calendar form-icon"></i>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Loading time <span class="text-danger">*</span></label>
                        <div class="form-icon-wrap">
                            <input type="time" name="loading_time" id="loading_time" class="form-control" value="14:00">
                            <i class="bx bx-time-five form-icon"></i>
                        </div>
                    </div>
                </div>

                {{-- Preferred Time Window Chips --}}
                <div class="mb-4">
                    <label class="form-label">Preferred Time Window</label>
                    <div class="timeslot-chips-wrap" id="pickupTimeSlotsWrap">
                        @php
                            $pickupSlots = [
                                'Morning (08:00 - 12:00)',
                                'Afternoon (12:00 - 16:00)',
                                'Evening (16:00 - 20:00)',
                                'Night (20:00 - 00:00)',
                            ];
                        @endphp
                        @foreach($pickupSlots as $slot)
                            <div class="timeslot-chip {{ $slot == 'Afternoon (12:00 - 16:00)' ? 'active' : '' }}" data-target="#loading_time_slot" data-value="{{ $slot }}">
                                <i class="bx bx-check slot-check"></i>
                                <span>{{ $slot }}</span>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="loading_time_slot" id="loading_time_slot" value="Afternoon (12:00 - 16:00)">
                </div>

                {{-- Country & City --}}
                <div class="row g-4 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Country</label>
                        <select name="pickup_country_id" id="pickup_country_id" class="form-select">
                            <option value="">-- Select Country --</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}">{{ $c->name_en }} ({{ $c->name_ar }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">City</label>
                        <select name="pickup_city_id" id="pickup_city_id" class="form-select">
                            <option value="">-- Select Country First --</option>
                        </select>
                    </div>
                </div>

                {{-- Area --}}
                <div class="mb-3">
                    <label class="form-label">Area</label>
                    <div class="form-icon-wrap icon-left">
                        <i class="bx bx-buildings form-icon"></i>
                        <input type="text" name="pickup_area" id="pickup_area" class="form-control" placeholder="e.g. Shuwaikh Industrial">
                    </div>
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="pickup_address" id="pickup_address" class="form-control" placeholder="e.g. Warehouse 12, Block 3, Street 40">
                </div>

                {{-- Pickup Instructions (Optional) --}}
                <div class="mb-4">
                    <label class="form-label">Pickup Instructions (Optional)</label>
                    <input type="text" name="pickup_instructions" id="pickup_instructions" class="form-control" placeholder="e.g. Gate 4 entrance, security badge required">
                </div>

                {{-- Map Location --}}
                <div class="wz-section-title"><i class="bx bx-map me-1"></i>Map Location</div>
                <div class="map-search-wrap mb-2">
                    <div class="map-search-inner">
                        <i class="bx bx-search text-info"></i>
                        <input type="text" id="pickupSearchInput" class="map-search-input" placeholder="Search location on map...">
                    </div>
                </div>
                <div class="map-container position-relative">
                    <div id="pickupMap"></div>
                    <div class="map-coords-badge" id="pickupCoordsLabel">
                        <i class="bx bx-crosshair me-1"></i>Coordinates not set
                    </div>
                </div>
                <input type="hidden" name="pickup_lat" id="pickup_lat" value="">
                <input type="hidden" name="pickup_lng" id="pickup_lng" value="">
            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev" data-goto="3">
                    <i class="bx bx-chevron-left fs-5"></i> <span>Back</span>
                </button>
                <button type="button" class="btn-wz-next" data-goto="5">
                    <span>Next: Delivery Location</span> <i class="bx bx-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             STEP 5: DELIVERY LOCATION
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel" id="step5">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-navigation"></i></div>
                <div>
                    <h4>Step 5: Delivery Location (Destination)</h4>
                    <p>Pre-filled from the Scheduled Trip Route Destination. You can refine schedule, address & map pin.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 5 of 9</span></div>
            </div>
            <div class="wz-card-body">
                <div class="prefill-banner" id="dropoffPrefillBanner">
                    <i class="bx bx-check-shield ic"></i>
                    <div>
                        <strong>Route Destination:</strong> Pre-filled from the Route Destination City & Country.
                    </div>
                </div>

                {{-- Schedule: Expecting Receiving Date & Time --}}
                <div class="row g-4 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Expecting receiving date <span class="text-danger">*</span></label>
                        <div class="form-icon-wrap">
                            <input type="date" name="receiving_date" id="receiving_date" class="form-control" value="{{ date('Y-m-d') }}">
                            <i class="bx bx-calendar form-icon"></i>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Expecting receiving time <span class="text-danger">*</span></label>
                        <div class="form-icon-wrap">
                            <input type="time" name="receiving_time" id="receiving_time" class="form-control" value="20:00">
                            <i class="bx bx-time-five form-icon"></i>
                        </div>
                    </div>
                </div>

                {{-- Preferred Delivery Window Chips --}}
                <div class="mb-4">
                    <label class="form-label">Preferred Delivery Window</label>
                    <div class="timeslot-chips-wrap" id="deliveryTimeSlotsWrap">
                        @php
                            $deliverySlots = [
                                'Morning (08:00 - 12:00)',
                                'Afternoon (12:00 - 16:00)',
                                'Evening (16:00 - 20:00)',
                                'Night (20:00 - 00:00)',
                            ];
                        @endphp
                        @foreach($deliverySlots as $slot)
                            <div class="timeslot-chip {{ $slot == 'Evening (16:00 - 20:00)' ? 'active' : '' }}" data-target="#receiving_time_slot" data-value="{{ $slot }}">
                                <i class="bx bx-check slot-check"></i>
                                <span>{{ $slot }}</span>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="receiving_time_slot" id="receiving_time_slot" value="Evening (16:00 - 20:00)">
                </div>

                {{-- Country & City --}}
                <div class="row g-4 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Country</label>
                        <select name="dropoff_country_id" id="dropoff_country_id" class="form-select">
                            <option value="">-- Select Country --</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}">{{ $c->name_en }} ({{ $c->name_ar }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">City</label>
                        <select name="dropoff_city_id" id="dropoff_city_id" class="form-select">
                            <option value="">-- Select Country First --</option>
                        </select>
                    </div>
                </div>

                {{-- Area --}}
                <div class="mb-3">
                    <label class="form-label">Area</label>
                    <div class="form-icon-wrap icon-left">
                        <i class="bx bx-buildings form-icon"></i>
                        <input type="text" name="dropoff_area" id="dropoff_area" class="form-control" placeholder="e.g. Al Faisaliyah">
                    </div>
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="dropoff_address" id="dropoff_address" class="form-control" placeholder="e.g. Depot 7, King Fahd Road, Al Faisaliyah">
                </div>

                {{-- Delivery Instructions (Optional) --}}
                <div class="mb-4">
                    <label class="form-label">Delivery Instructions (Optional)</label>
                    <input type="text" name="delivery_instructions" id="delivery_instructions" class="form-control" placeholder="e.g. Receiver dock 3, call before arrival">
                </div>

                {{-- Map Location --}}
                <div class="wz-section-title"><i class="bx bx-map me-1"></i>Map Location</div>
                <div class="map-search-wrap mb-2">
                    <div class="map-search-inner">
                        <i class="bx bx-search text-success"></i>
                        <input type="text" id="dropoffSearchInput" class="map-search-input" placeholder="Search destination on map...">
                    </div>
                </div>
                <div class="map-container position-relative">
                    <div id="dropoffMap"></div>
                    <div class="map-coords-badge" id="dropoffCoordsLabel">
                        <i class="bx bx-crosshair me-1"></i>Coordinates not set
                    </div>
                </div>
                <input type="hidden" name="dropoff_lat" id="dropoff_lat" value="">
                <input type="hidden" name="dropoff_lng" id="dropoff_lng" value="">
            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev" data-goto="4">
                    <i class="bx bx-chevron-left fs-5"></i> <span>Back</span>
                </button>
                <button type="button" class="btn-wz-next" data-goto="6">
                    <span>Next: Contacts</span> <i class="bx bx-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             STEP 6: CONTACTS
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel" id="step6">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-phone-call"></i></div>
                <div>
                    <h4>Step 6: Contacts & On-Site Personnel</h4>
                    <p>Designate point-of-contact at pickup dock and arrival facility.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 6 of 8</span></div>
            </div>
            <div class="wz-card-body">
                <div class="wz-section-title"><i class="bx bx-log-in me-1"></i>Loading Point Contact (Pickup)</div>
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Loading Contact Name</label>
                        <input type="text" name="loading_contact_name" id="loading_contact_name" class="form-control" placeholder="Full name of loading supervisor">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Loading Contact Phone</label>
                        <input type="text" name="loading_contact_phone" id="loading_contact_phone" class="form-control" placeholder="+965 XXXXXXXX">
                    </div>
                </div>

                <div class="wz-section-title"><i class="bx bx-log-out me-1"></i>Arrival Point Contact (Delivery)</div>
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Arrival Contact Name</label>
                        <input type="text" name="arrival_contact_name" id="arrival_contact_name" class="form-control" placeholder="Full name of receiver / storekeeper">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Arrival Contact Phone</label>
                        <input type="text" name="arrival_contact_phone" id="arrival_contact_phone" class="form-control" placeholder="+966 XXXXXXXXX">
                    </div>
                </div>
            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev" data-goto="5">
                    <i class="bx bx-chevron-left fs-5"></i> <span>Back</span>
                </button>
                <button type="button" class="btn-wz-next" data-goto="7">
                    <span>Next: Required Documents</span> <i class="bx bx-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             STEP 7: REQUIRED DOCUMENTS (المستندات المطلوبة للرحلة)
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel" id="step7">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-file"></i></div>
                <div>
                    <h4>Step 7: Required Documents (المستندات المطلوبة)</h4>
                    <p>Upload official shipment documents required for this scheduled trip route and truck.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 7 of 9</span></div>
            </div>
            <div class="wz-card-body">
                <div class="prefill-banner" id="tripDocsBanner">
                    <i class="bx bx-file-find ic text-info"></i>
                    <div id="tripDocsBannerText">
                        <strong>Trip Documentation:</strong> Select a scheduled trip to load required document guidelines.
                    </div>
                </div>

                {{-- Required Documents Container (Populated dynamically from selected trip's required_documents) --}}
                <div class="wz-section-title d-flex align-items-center justify-content-between mb-3">
                    <div><i class="bx bx-paperclip me-1 text-info"></i>Mandatory Trip Documents</div>
                    <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-2 py-1" id="docCountBadge">0 Documents Required</span>
                </div>

                <div class="doc-upload-grid" id="requiredDocsContainer">
                    {{-- Dynamically populated based on trip.required_documents --}}
                </div>

                {{-- Additional Supporting Documents Section --}}
                <div class="wz-section-title mb-2"><i class="bx bx-plus-circle me-1 text-success"></i>Additional Supporting Documents (Optional)</div>
                <p class="text-muted small mb-3">You can attach extra documents (e.g., customs permits, inspection certificates, photos, waybills).</p>
                
                <div class="doc-upload-grid" id="extraDocsContainer">
                    {{-- Dynamically added extra documents --}}
                </div>

                <div class="mb-4">
                    <button type="button" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2" id="btnAddExtraDoc">
                        <i class="bx bx-plus fs-5"></i>
                        <span>Attach Additional Document</span>
                    </button>
                </div>

                {{-- Guidelines Box --}}
                <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div class="d-flex align-items-center gap-2 mb-2 text-info fw-bold small">
                        <i class="bx bx-info-circle fs-5"></i>
                        <span>Upload Guidelines & Specifications</span>
                    </div>
                    <ul class="mb-0 text-muted small ps-3">
                        <li>Accepted formats: <strong>PDF, DOCX, XLS, XLSX, JPG, JPEG, PNG</strong>.</li>
                        <li>Maximum file size per document: <strong>15 MB</strong>.</li>
                        <li>Ensure all text, commercial stamps, invoice values and barcodes are clearly visible.</li>
                    </ul>
                </div>
            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev" data-goto="6">
                    <i class="bx bx-chevron-left fs-5"></i> <span>Back: Contacts</span>
                </button>
                <button type="button" class="btn-wz-next" data-goto="8">
                    <span>Next: Pricing & Capacity</span> <i class="bx bx-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             STEP 8: PRICING & BOOKED CAPACITY
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel" id="step8">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-dollar-circle"></i></div>
                <div>
                    <h4>Step 8: Pricing & Booked Capacity</h4>
                    <p>Configure number of booked units / truck spots and verify the final price calculation.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 8 of 9</span></div>
            </div>
            <div class="wz-card-body">
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Trip Base Unit Price (KWD)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="trip_price" id="trip_price" class="form-control fw-bold text-warning" readonly>
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-warning fw-bold">KWD</span>
                        </div>
                        <small class="text-muted">Fixed price per unit from Scheduled Trip</small>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">Booked Capacity Units <span class="text-danger">*</span></label>
                        <input type="number" name="booked_capacity" id="booked_capacity" class="form-control fw-bold" value="1" min="1">
                        <small class="text-info" id="capacityLimitLabel">Maximum available capacity: —</small>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">Total Computed Price (KWD)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="total_price" id="total_price" class="form-control fw-bold text-success" readonly>
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-success fw-bold">KWD</span>
                        </div>
                        <small class="text-muted">Auto-computed: Units × Unit Price</small>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Agreed / Custom Initial Price (Optional)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="initial_price" id="initial_price" class="form-control" placeholder="Leave empty to use computed total">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-info fw-bold">KWD</span>
                        </div>
                        <small class="text-muted">Overrides final invoice total if custom rate was negotiated</small>
                    </div>
                </div>

                {{-- Price Breakdown Banner --}}
                <div class="rv-card">
                    <div class="rv-card-title"><i class="bx bx-calculator me-1"></i>Calculation Breakdown</div>
                    <div class="rv-row">
                        <div class="lbl">Unit Base Price</div>
                        <div class="val text-warning" id="calcUnitPrice">0.00 KWD</div>
                    </div>
                    <div class="rv-row">
                        <div class="lbl">Booked Units Count</div>
                        <div class="val text-info" id="calcUnits">1 Unit</div>
                    </div>
                    <div class="rv-row">
                        <div class="lbl">Estimated Tax (15% VAT)</div>
                        <div class="val text-muted" id="calcTax">0.00 KWD</div>
                    </div>
                    <div class="rv-row">
                        <div class="lbl fs-6 text-white">Final Invoice Total</div>
                        <div class="val fs-6 text-success fw-bold" id="calcTotal">0.00 KWD</div>
                    </div>
                </div>
            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev" data-goto="7">
                    <i class="bx bx-chevron-left fs-5"></i> <span>Back: Documents</span>
                </button>
                <button type="button" class="btn-wz-next" data-goto="9">
                    <span>Next: Review & Confirm</span> <i class="bx bx-chevron-right fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             STEP 9: REVIEW & CONFIRM
        ══════════════════════════════════════════════════════ --}}
        <div class="wz-step-panel" id="step9">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-check-shield"></i></div>
                <div>
                    <h4>Step 9: Review & Confirm Order</h4>
                    <p>Verify all scheduled trip specs, customer info, cargo details, documents and pricing before submission.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 9 of 9</span></div>
            </div>
            <div class="wz-card-body">
                <div class="row g-4">
                    {{-- Tile 1: Linked Scheduled Trip --}}
                    <div class="col-12 col-md-6">
                        <div class="rv-card">
                            <div class="rv-card-title"><i class="bx bx-calendar-event me-1"></i>Linked Scheduled Trip</div>
                            <div class="rv-row"><div class="lbl">Trip ID</div><div class="val text-info" id="rvTripId">—</div></div>
                            <div class="rv-row"><div class="lbl">Route</div><div class="val" id="rvRoute">—</div></div>
                            <div class="rv-row"><div class="lbl">Departure Date & Time</div><div class="val" id="rvDateTime">—</div></div>
                            <div class="rv-row"><div class="lbl">Driver Assigned</div><div class="val" id="rvDriver">—</div></div>
                        </div>
                    </div>

                    {{-- Tile 2: Customer --}}
                    <div class="col-12 col-md-6">
                        <div class="rv-card">
                            <div class="rv-card-title"><i class="bx bx-user me-1"></i>Customer Details</div>
                            <div class="rv-row"><div class="lbl">Customer Name</div><div class="val" id="rvCustomerName">—</div></div>
                            <div class="rv-row"><div class="lbl">Email</div><div class="val" id="rvCustomerEmail">—</div></div>
                            <div class="rv-row"><div class="lbl">Phone</div><div class="val" id="rvCustomerPhone">—</div></div>
                        </div>
                    </div>

                    {{-- Tile 3: Cargo & Truck --}}
                    <div class="col-12 col-md-6">
                        <div class="rv-card">
                            <div class="rv-card-title"><i class="bx bx-package me-1"></i>Cargo & Truck Specifications</div>
                            <div class="rv-row"><div class="lbl">Shipment Label</div><div class="val" id="rvCargoLabel">—</div></div>
                            <div class="rv-row"><div class="lbl">Packages Count</div><div class="val" id="rvPackagesCount">1</div></div>
                            <div class="rv-row"><div class="lbl">Weight / Dimensions</div><div class="val" id="rvWeightDim">—</div></div>
                            <div class="rv-row"><div class="lbl">Truck Type</div><div class="val text-info" id="rvTruckType">—</div></div>
                            <div class="rv-row"><div class="lbl">Number of Trucks</div><div class="val text-info fw-bold" id="rvTruckCount">1 Truck</div></div>
                            <div class="rv-row"><div class="lbl">Special Equipment</div><div class="val text-warning" id="rvSpecialEquip">None</div></div>
                            <div class="rv-row"><div class="lbl">Truck Notes</div><div class="val text-truncate" style="max-width:200px;" id="rvTruckNotes">—</div></div>
                            <div class="rv-row"><div class="lbl">HS Tariff Code</div><div class="val" id="rvHsCode">—</div></div>
                        </div>
                    </div>

                    {{-- Tile 4: Route Locations --}}
                    <div class="col-12 col-md-6">
                        <div class="rv-card">
                            <div class="rv-card-title"><i class="bx bx-map-pin me-1"></i>Pickup & Delivery Locations</div>
                            <div class="rv-row"><div class="lbl">Pickup (Origin)</div><div class="val text-info" id="rvPickupLoc">—</div></div>
                            <div class="rv-row"><div class="lbl">Loading Schedule</div><div class="val text-info" id="rvLoadingSchedule">—</div></div>
                            <div class="rv-row"><div class="lbl">Delivery (Dest)</div><div class="val text-success" id="rvDropoffLoc">—</div></div>
                            <div class="rv-row"><div class="lbl">Delivery Schedule</div><div class="val text-success" id="rvDeliverySchedule">—</div></div>
                            <div class="rv-row"><div class="lbl">Loading Contact</div><div class="val" id="rvLoadingContact">—</div></div>
                            <div class="rv-row"><div class="lbl">Arrival Contact</div><div class="val" id="rvArrivalContact">—</div></div>
                        </div>
                    </div>

                    {{-- Tile 5: Attached Documents --}}
                    <div class="col-12 col-md-6">
                        <div class="rv-card">
                            <div class="rv-card-title"><i class="bx bx-paperclip me-1"></i>Attached Documents</div>
                            <div id="rvDocsList">
                                <div class="text-muted fst-italic">No documents uploaded</div>
                            </div>
                        </div>
                    </div>

                    {{-- Tile 6: Pricing Final Summary --}}
                    <div class="col-12 col-md-6">
                        <div class="rv-card" style="border-color: rgba(16,185,129,0.3); background: rgba(16,185,129,0.04);">
                            <div class="rv-card-title text-success"><i class="bx bx-receipt me-1"></i>Financial & Invoice Overview</div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="lbl text-muted small">Unit Price</div>
                                    <div class="val fs-5 text-warning fw-bold" id="rvUnitPrice">0.00 KWD</div>
                                </div>
                                <div class="col-6">
                                    <div class="lbl text-muted small">Booked Units</div>
                                    <div class="val fs-5 text-info fw-bold" id="rvBookedUnits">1 Unit</div>
                                </div>
                                <div class="col-6">
                                    <div class="lbl text-muted small">VAT (15%)</div>
                                    <div class="val fs-5 text-slate-300 fw-bold" id="rvVat">0.00 KWD</div>
                                </div>
                                <div class="col-6">
                                    <div class="lbl text-muted small">Total Invoice</div>
                                    <div class="val fs-5 text-success fw-bold" id="rvFinalTotal">0.00 KWD</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev" data-goto="8">
                    <i class="bx bx-chevron-left fs-5"></i> <span>Back: Pricing</span>
                </button>
                <button type="submit" class="btn-wz-submit" id="submitOrderBtn">
                    <i class="bx bx-check-circle fs-5"></i> <span>Publish Scheduled Trip Order</span>
                </button>
            </div>
        </div>

    </div>
</form>

{{-- ─── Google Maps API ─── --}}
<script>
    var pickupMap        = null;
    var dropoffMap       = null;
    var pickupMarker     = null;
    var dropoffMarker    = null;
    var mapsApiReady     = false;
    var pickupMapInited  = false;
    var dropoffMapInited = false;

    var darkMapStyles = [
        { elementType: 'geometry',           stylers: [{ color: '#0f172a' }] },
        { elementType: 'labels.text.fill',   stylers: [{ color: '#94a3b8' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#0f172a' }] },
        { featureType: 'administrative.country',  elementType: 'geometry.stroke', stylers: [{ color: '#38bdf8' }, { weight: 1.5 }] },
        { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#1e293b' }] },
        { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#334155' }] },
        { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0284c7' }] }
    ];

    function initMaps() {
        mapsApiReady = true;
    }

    function initPickupMap() {
        if (pickupMapInited && pickupMap) {
            google.maps.event.trigger(pickupMap, 'resize');
            return;
        }
        var lat = parseFloat($('#pickup_lat').val()) || 29.3759;
        var lng = parseFloat($('#pickup_lng').val()) || 47.9774;

        var center = { lat: lat, lng: lng };
        pickupMap = new google.maps.Map(document.getElementById('pickupMap'), {
            center: center,
            zoom: 12,
            styles: darkMapStyles,
            disableDefaultUI: false,
            zoomControl: true,
        });

        if ($('#pickup_lat').val() && $('#pickup_lng').val()) {
            setPickupCoords(lat, lng);
        }

        pickupMap.addListener('click', function(e) {
            setPickupCoords(e.latLng.lat(), e.latLng.lng());
        });

        // Search autocomplete
        var pickupInput = document.getElementById('pickupSearchInput');
        if (pickupInput) {
            var pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
            pickupAutocomplete.bindTo('bounds', pickupMap);
            pickupAutocomplete.addListener('place_changed', function() {
                var place = pickupAutocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) return;
                pickupMap.setCenter(place.geometry.location);
                pickupMap.setZoom(15);
                setPickupCoords(place.geometry.location.lat(), place.geometry.location.lng());
            });
        }

        pickupMapInited = true;
    }

    function initDropoffMap() {
        if (dropoffMapInited && dropoffMap) {
            google.maps.event.trigger(dropoffMap, 'resize');
            return;
        }
        var lat = parseFloat($('#dropoff_lat').val()) || 24.7136;
        var lng = parseFloat($('#dropoff_lng').val()) || 46.6753;

        var center = { lat: lat, lng: lng };
        dropoffMap = new google.maps.Map(document.getElementById('dropoffMap'), {
            center: center,
            zoom: 12,
            styles: darkMapStyles,
            disableDefaultUI: false,
            zoomControl: true,
        });

        if ($('#dropoff_lat').val() && $('#dropoff_lng').val()) {
            setDropoffCoords(lat, lng);
        }

        dropoffMap.addListener('click', function(e) {
            setDropoffCoords(e.latLng.lat(), e.latLng.lng());
        });

        // Search autocomplete
        var dropoffInput = document.getElementById('dropoffSearchInput');
        if (dropoffInput) {
            var dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInput);
            dropoffAutocomplete.bindTo('bounds', dropoffMap);
            dropoffAutocomplete.addListener('place_changed', function() {
                var place = dropoffAutocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) return;
                dropoffMap.setCenter(place.geometry.location);
                dropoffMap.setZoom(15);
                setDropoffCoords(place.geometry.location.lat(), place.geometry.location.lng());
            });
        }

        dropoffMapInited = true;
    }

    function setPickupCoords(lat, lng) {
        $('#pickup_lat').val(lat.toFixed(8));
        $('#pickup_lng').val(lng.toFixed(8));
        $('#pickupCoordsLabel').html('<i class="bx bx-map-pin me-1 text-info"></i>' + lat.toFixed(5) + ' , ' + lng.toFixed(5));
        if (pickupMarker) pickupMarker.setMap(null);
        pickupMarker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: pickupMap,
            animation: google.maps.Animation.DROP,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 12,
                fillColor: '#06B6D4',
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: 3
            }
        });
    }

    function setDropoffCoords(lat, lng) {
        $('#dropoff_lat').val(lat.toFixed(8));
        $('#dropoff_lng').val(lng.toFixed(8));
        $('#dropoffCoordsLabel').html('<i class="bx bx-map-pin me-1 text-success"></i>' + lat.toFixed(5) + ' , ' + lng.toFixed(5));
        if (dropoffMarker) dropoffMarker.setMap(null);
        dropoffMarker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: dropoffMap,
            animation: google.maps.Animation.DROP,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 12,
                fillColor: '#10B981',
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: 3
            }
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0cvICpKSuRBuCf2mN11rtLC5FsGFN2eI&callback=initMaps&libraries=places&loading=async" defer></script>

{{-- ─── Main Wizard Logic & Trip Pre-filler ─── --}}
<script>
$(document).ready(function() {

    var currentStep = 1;
    var totalSteps  = 9;
    var currentTripData = null;

    // ─── Step Navigation ───
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        // Validation on forward progress
        if (step > currentStep) {
            if (!validateStep(currentStep)) return;
        }

        $('.wz-step-panel').removeClass('active');
        $('#step' + step).addClass('active');

        // Progress icons update
        $('.wz-step-item').each(function() {
            var s = parseInt($(this).data('step'));
            $(this).removeClass('active done');
            if (s < step) {
                $(this).addClass('done').find('.wz-step-circle').html('<i class="bx bx-check"></i>');
            } else if (s === step) {
                $(this).addClass('active');
            } else {
                $(this).find('.wz-step-circle').html(s);
            }
        });

        currentStep = step;

        // Auto-center step item on mobile
        var $activeItem = $('.wz-step-item[data-step="' + step + '"]');
        var $wrap = $('.wz-progress-wrap');
        if ($activeItem.length && $wrap.length && $wrap.get(0).scrollWidth > $wrap.get(0).clientWidth) {
            var itemLeft = $activeItem.offset().left - $wrap.offset().left + $wrap.scrollLeft();
            var targetScroll = itemLeft - ($wrap.width() / 2) + ($activeItem.outerWidth() / 2);
            $wrap.animate({ scrollLeft: Math.max(0, targetScroll) }, 300);
        }

        // Lazy initialize maps
        if (step === 4) {
            setTimeout(function() {
                if (mapsApiReady) initPickupMap();
                else {
                    var chk = setInterval(function() { if (mapsApiReady) { clearInterval(chk); initPickupMap(); } }, 200);
                }
            }, 150);
        }
        if (step === 5) {
            setTimeout(function() {
                if (mapsApiReady) initDropoffMap();
                else {
                    var chk2 = setInterval(function() { if (mapsApiReady) { clearInterval(chk2); initDropoffMap(); } }, 200);
                }
            }, 150);
        }

        // Recalculate price
        if (step === 8) calculatePrices();

        // Build Review
        if (step === 9) buildReview();

        // Scroll up
        $('html, body').animate({ scrollTop: $('.wz-progress-wrap').offset().top - 80 }, 300);
    }

    $('[data-goto]').on('click', function() {
        goToStep(parseInt($(this).data('goto')));
    });

    $('.wz-step-item').on('click', function() {
        var s = parseInt($(this).data('step'));
        if (s <= currentStep) goToStep(s);
    });

    function validateStep(step) {
        if (!$('#hidden_trip_id').val()) {
            if (typeof toastr !== 'undefined') toastr.warning('Please select a Scheduled Trip at the top before proceeding.');
            return false;
        }
        if (step === 1) {
            if (!$('#selectedCustomerId').val()) {
                if (typeof toastr !== 'undefined') toastr.warning('Please select a customer in Step 1.');
                return false;
            }
        }
        return true;
    }

    // ─── Scheduled Trip Selector & Dynamic Pre-fill ───
    $('#scheduled_trip_select').on('change', function() {
        var tripId = $(this).val();
        $('#hidden_trip_id').val(tripId);
        if (!tripId) {
            $('#tripSpecsGrid').slideUp(200);
            $('#tripSyncStatus').hide();
            $('#cargoPrefillBanner').slideUp(150);
            return;
        }

        loadTripData(tripId);
    });

    // Check if pre-selected trip on page load
    var initTripId = $('#scheduled_trip_select').val();
    if (initTripId) {
        loadTripData(initTripId);
    }

    function loadTripData(tripId) {
        $.ajax({
            url: '{{ route("get.scheduled.trip.order.trip.data.ajax", ":id") }}'.replace(':id', tripId),
            type: 'GET',
            success: function(res) {
                if (res.status === 'success') {
                    currentTripData = res.trip;
                    applyTripData(res.trip);
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') toastr.error('Failed to load trip specifications.');
            }
        });
    }

    function applyTripData(trip) {
        $('#tripSpecsGrid').slideDown(250);
        $('#tripSyncStatus').show();

        // 1. Hero Pill Updates
        var originName = (trip.route && trip.route.origin_city) ? trip.route.origin_city : 'Origin';
        var destName   = (trip.route && trip.route.destination_city) ? trip.route.destination_city : 'Dest';
        $('#pillRoute').text(originName + ' ➔ ' + destName);
        $('#pillDateTime').text(trip.trip_date + (trip.trip_time ? ' (' + trip.trip_time + ')' : ''));
        $('#pillTruck').text((trip.truck_type ? trip.truck_type.name_en : 'Truck') + (trip.truck_sub_type ? ' (' + trip.truck_sub_type.name_en + ')' : ''));
        $('#pillPrice').text('KWD ' + trip.price.toFixed(2));

        // 2. Pre-fill Step 2 (Cargo default label, Shipment Type & Nature)
        if (!$('#shipment_name').val()) {
            $('#shipment_name').val('Cargo for Scheduled Trip #' + trip.id + ' (' + originName + ' ➔ ' + destName + ')');
        }

        if (trip.shipment_type_id) {
            $('#shipment_type_id').val(trip.shipment_type_id).trigger('change');
        }
        if (trip.shipment_nature_id) {
            $('#shipment_nature_id').val(trip.shipment_nature_id).trigger('change');
        }

        var cargoDetails = [];
        if (trip.shipment_type && trip.shipment_type.name_en) {
            var stLabel = trip.shipment_type.name_en + (trip.shipment_type.name_ar ? ' (' + trip.shipment_type.name_ar + ')' : '');
            cargoDetails.push('<span class="badge bg-info bg-opacity-25 text-info px-2 py-1"><i class="bx bx-package me-1"></i>' + stLabel + '</span>');
        }
        if (trip.shipment_nature && trip.shipment_nature.name_en) {
            var snLabel = trip.shipment_nature.name_en + (trip.shipment_nature.name_ar ? ' (' + trip.shipment_nature.name_ar + ')' : '');
            cargoDetails.push('<span class="badge bg-warning bg-opacity-25 text-warning px-2 py-1"><i class="bx bx-category me-1"></i>' + snLabel + '</span>');
        }
        if (cargoDetails.length > 0) {
            $('#cargoPrefillText').html('<div class="d-flex align-items-center flex-wrap gap-2"><strong>Pre-filled from Trip #' + trip.id + ':</strong> ' + cargoDetails.join(' ') + '</div>');
            $('#cargoPrefillBanner').slideDown(200);
        } else {
            $('#cargoPrefillBanner').slideUp(150);
        }

        // 3. Pre-fill Step 3 (Truck Specs)
        if (trip.truck_type && trip.truck_type.id) {
            $('#truck_type_id').val(trip.truck_type.id).trigger('change');
            var customsBadge = trip.customs_clearance 
                ? ' <span class="badge bg-success bg-opacity-25 text-success ms-2"><i class="bx bx-check-shield me-1"></i>Customs Included</span>' 
                : ' <span class="badge bg-secondary bg-opacity-25 text-slate-300 ms-2"><i class="bx bx-x-circle me-1"></i>No Customs Clearance</span>';
            $('#truckPrefillBanner').html('<i class="bx bx-check-circle ic text-success"></i><div><strong>Pre-filled from Trip #' + trip.id + ':</strong> ' + trip.truck_type.name_en + (trip.truck_sub_type ? ' (' + trip.truck_sub_type.name_en + ')' : '') + customsBadge + '</div>');
        }
        if (trip.number_of_trucks) {
            setTruckCount(trip.number_of_trucks);
        }

        // 4. Pre-fill Step 4 (Pickup / Origin) & Schedule
        if (trip.trip_date) {
            $('#loading_date').val(trip.trip_date);
            if (!$('#receiving_date').val() || $('#receiving_date').val() === '{{ date("Y-m-d") }}') {
                $('#receiving_date').val(trip.trip_date);
            }
        }
        if (trip.trip_time) {
            var formattedTime = trip.trip_time.length >= 5 ? trip.trip_time.substring(0, 5) : trip.trip_time;
            $('#loading_time').val(formattedTime);
        }

        if (trip.route) {
            if (trip.route.origin_country_id) {
                $('#pickup_country_id').val(trip.route.origin_country_id);
                loadCities(trip.route.origin_country_id, '#pickup_city_id', trip.route.origin_city_id);
            }
            if (trip.route.origin_address) $('#pickup_address').val(trip.route.origin_address);
            if (trip.route.origin_lat && trip.route.origin_lng) {
                $('#pickup_lat').val(trip.route.origin_lat);
                $('#pickup_lng').val(trip.route.origin_lng);
                $('#pickupCoordsLabel').html('<i class="bx bx-map-pin me-1 text-info"></i>' + parseFloat(trip.route.origin_lat).toFixed(5) + ' , ' + parseFloat(trip.route.origin_lng).toFixed(5));
                if (pickupMapInited && pickupMap) {
                    setPickupCoords(parseFloat(trip.route.origin_lat), parseFloat(trip.route.origin_lng));
                    pickupMap.setCenter({ lat: parseFloat(trip.route.origin_lat), lng: parseFloat(trip.route.origin_lng) });
                }
            }
            $('#pickupPrefillBanner').html('<i class="bx bx-check-shield ic text-success"></i><div><strong>Pre-filled from Route Origin:</strong> ' + (trip.route.origin_city || '') + ' (' + (trip.route.origin_country || '') + ')</div>');
        }

        // 5. Pre-fill Step 5 (Delivery / Destination)
        if (trip.route) {
            if (trip.route.destination_country_id) {
                $('#dropoff_country_id').val(trip.route.destination_country_id);
                loadCities(trip.route.destination_country_id, '#dropoff_city_id', trip.route.destination_city_id);
            }
            if (trip.route.destination_address) $('#dropoff_address').val(trip.route.destination_address);
            if (trip.route.destination_lat && trip.route.destination_lng) {
                $('#dropoff_lat').val(trip.route.destination_lat);
                $('#dropoff_lng').val(trip.route.destination_lng);
                $('#dropoffCoordsLabel').html('<i class="bx bx-map-pin me-1 text-success"></i>' + parseFloat(trip.route.destination_lat).toFixed(5) + ' , ' + parseFloat(trip.route.destination_lng).toFixed(5));
                if (dropoffMapInited && dropoffMap) {
                    setDropoffCoords(parseFloat(trip.route.destination_lat), parseFloat(trip.route.destination_lng));
                    dropoffMap.setCenter({ lat: parseFloat(trip.route.destination_lat), lng: parseFloat(trip.route.destination_lng) });
                }
            }
            $('#dropoffPrefillBanner').html('<i class="bx bx-check-shield ic text-success"></i><div><strong>Pre-filled from Route Destination:</strong> ' + (trip.route.destination_city || '') + ' (' + (trip.route.destination_country || '') + ')</div>');
        }

        // 6. Pre-fill Step 7 (Required Documents)
        renderTripRequiredDocuments(trip.required_documents || []);

        // 7. Pre-fill Step 8 (Pricing & Capacity)
        $('#trip_price').val(trip.price.toFixed(2));
        $('#capacityLimitLabel').text('Maximum available capacity on this trip: ' + trip.available_capacity + ' units');
        $('#booked_capacity').attr('max', trip.available_capacity);
        calculatePrices();
    }

    // ─── Price Calculator ───
    function calculatePrices() {
        var unitPrice = parseFloat($('#trip_price').val()) || 0;
        var units     = parseInt($('#booked_capacity').val()) || 1;
        var total     = unitPrice * units;
        var vat       = total * 0.15;
        var finalTot  = total + vat;

        $('#total_price').val(total.toFixed(2));
        $('#calcUnitPrice').text(unitPrice.toFixed(2) + ' KWD');
        $('#calcUnits').text(units + (units > 1 ? ' Units' : ' Unit'));
        $('#calcTax').text(vat.toFixed(2) + ' KWD');
        $('#calcTotal').text(finalTot.toFixed(2) + ' KWD');
    }

    $('#booked_capacity, #trip_price').on('input change', calculatePrices);

    // ─── Customer Selection & Search ───
    $('#customerSearch').on('input', function() {
        var q = $(this).val().toLowerCase();
        $('.customer-list-item').each(function() {
            var s = $(this).data('search');
            $(this).toggle(s.includes(q));
        });
    });

    $(document).on('click', '.customer-list-item', function() {
        $('.customer-list-item').removeClass('selected');
        $(this).addClass('selected');
        var cid = $(this).data('id');
        $('#selectedCustomerId').val(cid);

        var name  = $(this).data('name');
        var email = $(this).data('email');
        var phone = $(this).data('phone');

        $('#customerInfoPanel').show();
        $('#customerInfoContent').html(
            '<div class="rv-card">' +
                '<div class="rv-row"><div class="lbl">Customer Name</div><div class="val text-info">' + name + '</div></div>' +
                '<div class="rv-row"><div class="lbl">Email</div><div class="val">' + (email || '—') + '</div></div>' +
                '<div class="rv-row"><div class="lbl">Phone</div><div class="val">' + (phone || '—') + '</div></div>' +
            '</div>'
        );
    });

    // ─── AJAX Cities Loader ───
    function loadCities(countryId, targetSelect, selectedCityId) {
        if (!countryId) {
            $(targetSelect).html('<option value="">-- Select Country First --</option>');
            return;
        }
        $.ajax({
            url: '{{ route("get.shipment.cities.ajax", ":id") }}'.replace(':id', countryId),
            type: 'GET',
            success: function(data) {
                var cities = Array.isArray(data) ? data : (data.cities || []);
                var html = '<option value="">-- Select City --</option>';
                cities.forEach(function(c) {
                    var sel = (selectedCityId && selectedCityId == c.id) ? 'selected' : '';
                    html += '<option value="' + c.id + '" ' + sel + '>' + c.name_en + ' (' + (c.name_ar || c.name_en) + ')</option>';
                });
                $(targetSelect).html(html);
            }
        });
    }

    $('#pickup_country_id').on('change', function() {
        loadCities($(this).val(), '#pickup_city_id');
    });
    $('#dropoff_country_id').on('change', function() {
        loadCities($(this).val(), '#dropoff_city_id');
    });

    // ─── Truck Specifications App Data & Synchronizer ───
    var TRUCK_SPECS_DATA = {
        'flatbed': {
            name: 'Flatbed',
            arabicName: 'مسطحة',
            capacity: 'Up to 25 Tons',
            dimensions: '13.6m × 2.45m',
            description: 'Ideal for construction materials, pipes, machinery & heavy open freight'
        },
        'reefer': {
            name: 'Reefer',
            arabicName: 'المبردة',
            capacity: 'Up to 22 Tons',
            dimensions: '13.4m × 2.45m',
            description: 'Temperature controlled (-20°C to +20°C) for food, pharmaceuticals & perishables'
        },
        'trailer': {
            name: 'Trailer',
            arabicName: 'جَرَّار',
            capacity: 'Up to 30 Tons',
            dimensions: '13.6m × 2.50m',
            description: 'Heavy duty articulated trailer for large scale commercial shipments'
        },
        'closed': {
            name: 'Closed',
            arabicName: 'مغلق',
            capacity: 'Up to 15 Tons',
            dimensions: '8.5m × 2.40m',
            description: 'Fully enclosed weather-proof and secure box truck for sensitive retail cargo'
        },
        'truck': {
            name: 'Truck',
            arabicName: 'شاحنة',
            capacity: 'Up to 8 Tons',
            dimensions: '6.2m × 2.20m',
            description: 'Versatile medium duty truck (Dyna) for urban and intra-city logistics'
        },
        'container': {
            name: 'Container',
            arabicName: 'حاوية',
            capacity: 'Up to 28 Tons',
            dimensions: '20ft / 40ft standard',
            description: 'Chassis carrier specialized for standard 20ft & 40ft sea/land ISO containers'
        }
    };

    function updateTruckSpecHighlight(nameEn, nameAr) {
        var key = (nameEn || '').toLowerCase().trim();
        var spec = TRUCK_SPECS_DATA[key];
        var displayNameEn = spec ? spec.name : (nameEn || 'Truck');
        var displayNameAr = spec ? spec.arabicName : (nameAr || '');
        var arSuffix = displayNameAr ? ' (' + displayNameAr + ')' : '';
        var dimensions = spec ? spec.dimensions : '13.6m × 2.50m';
        var capacity = spec ? spec.capacity : 'Standard capacity';
        var description = spec ? spec.description : 'Commercial heavy transport vehicle';

        $('#specHighlightTitle span').text(displayNameEn + arSuffix + ' Specifications');
        $('#specHighlightDesc').text(description + '. Dimensions: ' + dimensions + '. Capacity: ' + capacity + '.');
    }

    function setTruckCount(count) {
        count = Math.max(1, parseInt(count) || 1);
        $('#number_of_trucks').val(count);
        $('#truckCountDisplay').text(count + (count > 1 ? ' Trucks' : ' Truck'));
        if (count <= 1) {
            $('#btnDecTruck').addClass('disabled');
        } else {
            $('#btnDecTruck').removeClass('disabled');
        }
        $('#booked_capacity').val(count);
        calculatePrices();
    }

    $('#btnDecTruck').on('click', function(e) {
        e.preventDefault();
        var current = parseInt($('#number_of_trucks').val()) || 1;
        if (current > 1) {
            setTruckCount(current - 1);
        }
    });

    $('#btnIncTruck').on('click', function(e) {
        e.preventDefault();
        var current = parseInt($('#number_of_trucks').val()) || 1;
        setTruckCount(current + 1);
    });

    // Special Equipment Chips Toggle
    $('.equipment-chip').on('click', function() {
        $(this).toggleClass('active');
        var isAct = $(this).hasClass('active');
        $(this).find('.eq-checkbox').prop('checked', isAct);
    });

    // Time Slot Chips Toggle
    $(document).on('click', '.timeslot-chip', function() {
        var $wrap = $(this).closest('.timeslot-chips-wrap');
        $wrap.find('.timeslot-chip').removeClass('active');
        $(this).addClass('active');
        var target = $(this).data('target');
        var val = $(this).data('value');
        if (target && val) {
            $(target).val(val);
        }
    });

    // Truck Card Selection in Grid
    $(document).on('click', '.truck-select-card', function() {
        var tId = $(this).data('id');
        $('.truck-select-card').removeClass('selected');
        $(this).addClass('selected');
        if (tId && $('#truck_type_id').val() != tId) {
            $('#truck_type_id').val(tId).trigger('change');
        }
    });

    // ─── AJAX Truck Sub-Types Loader & Cards Sync ───
    $('#truck_type_id').on('change', function() {
        var tId = $(this).val();
        $('.truck-select-card').removeClass('selected');
        if (tId) {
            var $card = $('.truck-select-card[data-id="' + tId + '"]');
            if ($card.length) {
                $card.addClass('selected');
                updateTruckSpecHighlight($card.data('name'), $card.data('name-ar'));
            } else {
                var $opt = $('#truck_type_id option:selected');
                updateTruckSpecHighlight($opt.data('name') || $opt.text(), $opt.data('name-ar') || '');
            }
        }
        if (!tId) {
            $('#truck_sub_type_id').html('<option value="">-- Select Truck Type First --</option>');
            return;
        }
        $.ajax({
            url: '{{ route("get.shipment.sub.types.ajax", ":id") }}'.replace(':id', tId),
            type: 'GET',
            success: function(data) {
                var subs = Array.isArray(data) ? data : (data.sub_types || []);
                var html = '<option value="">-- Select Sub-Type (Optional) --</option>';
                subs.forEach(function(s) {
                    var isSelected = (currentTripData && currentTripData.truck_sub_type && currentTripData.truck_sub_type.id == s.id) ? 'selected' : '';
                    html += '<option value="' + s.id + '" ' + isSelected + '>' + s.name_en + ' (' + (s.name_ar || s.name_en) + ')</option>';
                });
                $('#truck_sub_type_id').html(html);
            }
        });
    });

    // Initialize Truck Selection Card / Highlight on page load
    if ($('#truck_type_id').val()) {
        $('#truck_type_id').trigger('change');
    } else {
        var $defaultCard = $('.truck-select-card[data-key="trailer"]').length ? $('.truck-select-card[data-key="trailer"]') : $('.truck-select-card').first();
        if ($defaultCard.length) {
            $defaultCard.trigger('click');
        }
    }

    // ─── Step 7: Document Upload Helpers & Listeners ───
    function getDocIconInfo(title) {
        var t = (title || '').toLowerCase();
        if (t.includes('invoice') || t.includes('فاتورة')) {
            return { icon: 'bx bx-receipt', type: 'PDF', color: '#EF4444' };
        }
        if (t.includes('packing') || t.includes('تعبئة') || t.includes('قائمة')) {
            return { icon: 'bx bx-list-check', type: 'PDF/XLS', color: '#38BDF8' };
        }
        if (t.includes('customs') || t.includes('جمرك') || t.includes('تخليص')) {
            return { icon: 'bx bx-shield-quarter', type: 'PDF', color: '#10B981' };
        }
        if (t.includes('photo') || t.includes('صورة') || t.includes('inspection')) {
            return { icon: 'bx bx-camera', type: 'IMG', color: '#F59E0B' };
        }
        if (t.includes('lading') || t.includes('بوليصة') || t.includes('waybill')) {
            return { icon: 'bx bx-file-blank', type: 'PDF', color: '#8B5CF6' };
        }
        if (t.includes('origin') || t.includes('منشأ')) {
            return { icon: 'bx bx-badge-check', type: 'PDF', color: '#06B6D4' };
        }
        return { icon: 'bx bx-file', type: 'DOC', color: '#94A3B8' };
    }

    function createDocCardHtml(docName, isRequired, index, isExtra) {
        var info = getDocIconInfo(docName);
        var reqBadge = isRequired 
            ? '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1">Required</span>'
            : '<span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 px-2 py-1">Optional</span>';
        
        var removeBtn = isExtra 
            ? '<button type="button" class="btn btn-sm btn-outline-danger p-1 ms-2 btn-remove-extra-doc" title="Remove Document"><i class="bx bx-trash"></i></button>'
            : '';

        return '<div class="doc-upload-card" data-doc-title="' + docName + '">' +
            '<div class="card-head">' +
                '<div class="doc-title-row">' +
                    '<div class="doc-icon-badge" style="color:' + info.color + ';"><i class="' + info.icon + '"></i></div>' +
                    '<div>' +
                        '<div class="doc-name">' + docName + '</div>' +
                        '<div class="doc-hint">Accepted: PDF, DOC, XLS, PNG, JPG (Max 15MB)</div>' +
                    '</div>' +
                '</div>' +
                '<div class="d-flex align-items-center">' +
                    reqBadge +
                    removeBtn +
                '</div>' +
            '</div>' +
            '<input type="file" name="order_documents_files[]" class="doc-file-input d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">' +
            '<input type="hidden" name="order_documents_titles[]" value="' + docName + '">' +
            '<input type="hidden" name="order_documents_is_required[]" value="' + (isRequired ? '1' : '0') + '">' +
            '<div class="doc-dropzone-box">' +
                '<i class="bx bx-cloud-upload"></i>' +
                '<div class="dz-text">Click or drag file here to upload</div>' +
                '<div class="dz-sub">Select document file from your computer</div>' +
            '</div>' +
            '<div class="doc-preview-box">' +
                '<div class="file-info">' +
                    '<span class="file-type-pill">' + info.type + '</span>' +
                    '<div>' +
                        '<div class="file-name-text">—</div>' +
                        '<div class="file-size-text">—</div>' +
                    '</div>' +
                '</div>' +
                '<div class="d-flex align-items-center gap-2">' +
                    '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1"><i class="bx bx-check me-1"></i>Uploaded</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger p-1 btn-clear-file" title="Remove file"><i class="bx bx-trash"></i></button>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function renderTripRequiredDocuments(requiredDocs) {
        var $wrap = $('#requiredDocsContainer');
        $wrap.empty();

        if (Array.isArray(requiredDocs) && requiredDocs.length > 0) {
            $('#tripDocsBannerText').html('<strong>Mandatory Trip Documents:</strong> This scheduled trip requires <strong>' + requiredDocs.length + ' official document(s)</strong> configured by admin. Please attach them before proceeding to pricing.');
            $('#docCountBadge').text(requiredDocs.length + ' Document' + (requiredDocs.length > 1 ? 's' : '') + ' Required').removeClass('bg-secondary').addClass('bg-info');

            requiredDocs.forEach(function(docName, idx) {
                $wrap.append(createDocCardHtml(docName, true, idx, false));
            });
        } else {
            $('#tripDocsBannerText').html('<strong>Trip Documentation:</strong> No mandatory documents were specified by admin for this trip. You can optionally upload any supporting documents below (Commercial Invoice, Packing List, Cargo Photos, etc.).');
            $('#docCountBadge').text('Optional Documentation').removeClass('bg-info').addClass('bg-secondary');

            var standardDocs = ['Commercial Invoice', 'Packing List', 'Customs Clearance Certificate'];
            standardDocs.forEach(function(docName, idx) {
                $wrap.append(createDocCardHtml(docName, false, idx, false));
            });
        }
    }

    // Click dropzone to open file dialog
    $(document).on('click', '.doc-dropzone-box', function() {
        $(this).closest('.doc-upload-card').find('.doc-file-input').trigger('click');
    });

    // File selected
    $(document).on('change', '.doc-file-input', function() {
        var card = $(this).closest('.doc-upload-card');
        var files = this.files;
        if (files && files.length > 0) {
            var file = files[0];
            var sizeKb = (file.size / 1024).toFixed(1);
            var sizeStr = sizeKb > 1024 ? ((sizeKb / 1024).toFixed(2) + ' MB') : (sizeKb + ' KB');
            var ext = file.name.split('.').pop().toUpperCase();

            card.find('.file-name-text').text(file.name);
            card.find('.file-size-text').text(sizeStr);
            card.find('.file-type-pill').text(ext || 'FILE');

            card.find('.doc-dropzone-box').hide();
            card.find('.doc-preview-box').css('display', 'flex');
            card.addClass('has-file');
        }
    });

    // Clear file
    $(document).on('click', '.btn-clear-file', function(e) {
        e.stopPropagation();
        var card = $(this).closest('.doc-upload-card');
        card.find('.doc-file-input').val('');
        card.find('.doc-preview-box').hide();
        card.find('.doc-dropzone-box').show();
        card.removeClass('has-file');
    });

    // Add extra document
    var extraDocCounter = 1;
    $('#btnAddExtraDoc').on('click', function() {
        var docName = prompt('Enter Document Name (e.g. Health Certificate, Inspection Report, Import Permit):');
        if (docName && docName.trim()) {
            $('#extraDocsContainer').append(createDocCardHtml(docName.trim(), false, 'extra_' + extraDocCounter++, true));
        }
    });

    // Remove extra doc
    $(document).on('click', '.btn-remove-extra-doc', function(e) {
        e.stopPropagation();
        $(this).closest('.doc-upload-card').remove();
    });

    // Initial render of documents on page load
    renderTripRequiredDocuments([]);

    // ─── Review Generator ───
    function buildReview() {
        if (currentTripData) {
            $('#rvTripId').text('#' + currentTripData.id);
            var origin = (currentTripData.route && currentTripData.route.origin_city) ? currentTripData.route.origin_city : 'Origin';
            var dest   = (currentTripData.route && currentTripData.route.destination_city) ? currentTripData.route.destination_city : 'Destination';
            $('#rvRoute').text(origin + ' ➔ ' + dest);
            $('#rvDateTime').text(currentTripData.trip_date + (currentTripData.trip_time ? ' (' + currentTripData.trip_time + ')' : ''));
            $('#rvDriver').text(currentTripData.driver ? currentTripData.driver.name : 'Unassigned');
        }

        var $selCust = $('.customer-list-item.selected');
        if ($selCust.length) {
            $('#rvCustomerName').text($selCust.data('name'));
            $('#rvCustomerEmail').text($selCust.data('email') || '—');
            $('#rvCustomerPhone').text($selCust.data('phone') || '—');
        }

        $('#rvCargoLabel').text($('#shipment_name').val() || 'Scheduled Trip Cargo');
        $('#rvPackagesCount').text($('#packages_count').val() + ' Pcs');
        var wt = $('#weight').val() ? ($('#weight').val() + ' Tons') : '—';
        var dim = ($('#length').val() || '0') + 'x' + ($('#width').val() || '0') + 'x' + ($('#height').val() || '0') + ' cm';
        $('#rvWeightDim').text(wt + ' | ' + dim);
        $('#rvTruckType').text($('#truck_type_id option:selected').text() || '—');

        var truckCount = parseInt($('#number_of_trucks').val()) || 1;
        $('#rvTruckCount').text(truckCount + (truckCount > 1 ? ' Trucks' : ' Truck'));

        var equips = [];
        $('.equipment-chip.active').each(function() {
            equips.push($(this).data('value'));
        });
        $('#rvSpecialEquip').text(equips.length ? equips.join(', ') : 'None');

        var tNotes = $('#truck_notes').val();
        $('#rvTruckNotes').text(tNotes ? tNotes : '—');

        $('#rvHsCode').text($('#hs_code').val() || '—');

        var pCity = $('#pickup_city_id option:selected').text();
        var pCityText = (pCity && pCity !== '-- Select City --' && pCity !== '-- Select Country First --') ? pCity : '';
        var pArea = $('#pickup_area').val() ? (', ' + $('#pickup_area').val()) : '';
        var pAddr = $('#pickup_address').val() ? (' — ' + $('#pickup_address').val()) : '';
        $('#rvPickupLoc').text((pCityText + pArea + pAddr).trim() || '—');

        var pDate = $('#loading_date').val() || '';
        var pTime = $('#loading_time').val() || '';
        var pSlot = $('#loading_time_slot').val() || '';
        var pSched = (pDate ? pDate : '') + (pTime ? (' ' + pTime) : '') + (pSlot ? (' • ' + pSlot) : '');
        $('#rvLoadingSchedule').text(pSched.trim() || '—');

        var dCity = $('#dropoff_city_id option:selected').text();
        var dCityText = (dCity && dCity !== '-- Select City --' && dCity !== '-- Select Country First --') ? dCity : '';
        var dArea = $('#dropoff_area').val() ? (', ' + $('#dropoff_area').val()) : '';
        var dAddr = $('#dropoff_address').val() ? (' — ' + $('#dropoff_address').val()) : '';
        $('#rvDropoffLoc').text((dCityText + dArea + dAddr).trim() || '—');

        var dDate = $('#receiving_date').val() || '';
        var dTime = $('#receiving_time').val() || '';
        var dSlot = $('#receiving_time_slot').val() || '';
        var dSched = (dDate ? dDate : '') + (dTime ? (' ' + dTime) : '') + (dSlot ? (' • ' + dSlot) : '');
        $('#rvDeliverySchedule').text(dSched.trim() || '—');

        $('#rvLoadingContact').text($('#loading_contact_name').val() + ' (' + ($('#loading_contact_phone').val() || '') + ')');
        $('#rvArrivalContact').text($('#arrival_contact_name').val() + ' (' + ($('#arrival_contact_phone').val() || '') + ')');

        // Review Tile 5: Documents
        var uploadedDocs = [];
        $('.doc-upload-card.has-file').each(function() {
            var title = $(this).find('input[name="order_documents_titles[]"]').val();
            var fileName = $(this).find('.file-name-text').text();
            var fileSize = $(this).find('.file-size-text').text();
            uploadedDocs.push({ title: title, file: fileName, size: fileSize });
        });

        if (uploadedDocs.length > 0) {
            var docHtml = '';
            uploadedDocs.forEach(function(d) {
                docHtml += '<div class="rv-row">' +
                    '<div class="lbl text-truncate" style="max-width:180px;"><i class="bx bx-file me-1 text-info"></i>' + d.title + '</div>' +
                    '<div class="val text-success small">' + d.file + ' <span class="text-muted">(' + d.size + ')</span></div>' +
                '</div>';
            });
            $('#rvDocsList').html(docHtml);
        } else {
            $('#rvDocsList').html('<div class="text-muted fst-italic">No documents uploaded</div>');
        }

        var uPrice = parseFloat($('#trip_price').val()) || 0;
        var units  = parseInt($('#booked_capacity').val()) || 1;
        var tot    = uPrice * units;
        var vat    = tot * 0.15;
        var grand  = tot + vat;

        $('#rvUnitPrice').text(uPrice.toFixed(2) + ' KWD');
        $('#rvBookedUnits').text(units + (units > 1 ? ' Units' : ' Unit'));
        $('#rvVat').text(vat.toFixed(2) + ' KWD');
        $('#rvFinalTotal').text(grand.toFixed(2) + ' KWD');
    }

    // ─── Submit State ───
    $('#wizardOrderForm').on('submit', function() {
        $('#submitOrderBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Publishing Order...');
    });

});
</script>

@endsection
