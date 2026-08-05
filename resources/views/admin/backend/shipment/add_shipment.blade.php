@extends('admin.master_admin')
@section('admin')

{{-- ========================
     ADD SHIPMENT — 8-STEP WIZARD
======================== --}}

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

/* ─── Progress Bar ─── */
.wz-progress-wrap {
    background: rgba(255,255,255,0.04);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 32px;
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
.wz-step-item.active   .wz-step-circle {
    background: var(--wz-cyan);
    border-color: var(--wz-cyan);
    color: #fff;
    box-shadow: 0 0 20px rgba(6,182,212,0.5);
    transform: scale(1.15);
}
.wz-step-item.done     .wz-step-circle {
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

/* ─── Responsive Stepper for Mobile View ─── */
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
    .wz-progress-wrap::-webkit-scrollbar {
        height: 4px;
    }
    .wz-progress-wrap::-webkit-scrollbar-thumb {
        background: rgba(6,182,212,0.4);
        border-radius: 4px;
    }
    .wz-steps-track {
        justify-content: flex-start;
        gap: 12px;
        min-width: max-content;
        padding: 4px 6px;
    }
    .wz-steps-track::before {
        left: 20px;
        right: 20px;
        top: 18px;
    }
    .wz-step-item {
        flex: 0 0 auto;
        width: 72px;
    }
    .wz-step-circle {
        width: 36px;
        height: 36px;
        font-size: 12px;
    }
    .wz-step-label {
        font-size: 10px;
        margin-top: 6px;
        max-width: 72px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .wz-card-body {
        padding: 20px 14px !important;
    }
    .wz-card-footer {
        padding: 14px 12px !important;
        gap: 8px;
    }
    .wz-card-footer .btn-wz-prev,
    .wz-card-footer .btn-wz-next {
        padding: 8px 12px !important;
        font-size: 0.8rem !important;
        white-space: nowrap !important;
    }
    .pricing-card-glow {
        padding: 24px 12px !important;
        border-radius: 16px !important;
    }
    .pricing-card-glow .currency-symbol {
        font-size: 1.25rem !important;
    }
    .pricing-card-glow .price-input {
        font-size: 1.6rem !important;
        width: 130px !important;
        max-width: 65% !important;
        padding: 4px 6px !important;
    }
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
    background: linear-gradient(135deg, rgba(6,182,212,0.15) 0%, rgba(56,189,248,0.08) 100%);
    border-bottom: 1px solid rgba(6,182,212,0.2);
    padding: 24px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.wz-card-header .step-icon {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--wz-cyan), var(--wz-cyan-light));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(6,182,212,0.35);
    flex-shrink: 0;
}
.wz-card-header h4 { font-size: 1.25rem; font-weight: 800; color: #F8FAFC; margin:0; }
.wz-card-header p  { font-size: 0.88rem; color: #94A3B8; margin:0; }
.wz-card-body  { padding: 32px; }
.wz-card-footer {
    padding: 20px 32px;
    border-top: 1px solid var(--wz-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(0,0,0,0.1);
}

/* ─── Step Panel Transitions ─── */
.wz-step-panel { display: none; animation: none; }
.wz-step-panel.active {
    display: block;
    animation: wz-fadein 0.4s cubic-bezier(0.22,1,0.36,1) both;
}
@keyframes wz-fadein {
    from { opacity:0; transform: translateY(18px); }
    to   { opacity:1; transform: translateY(0); }
}

/* ─── Section Dividers ─── */
.wz-section-title {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--wz-cyan-light);
    margin-bottom: 18px;
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

/* ─── Customer Card (Step 1) ─── */
.customer-list-container {
    max-height: 220px;
    overflow-y: auto;
    border: 1px solid var(--wz-border);
    border-radius: 12px;
    margin-bottom: 16px;
    scrollbar-width: thin;
    scrollbar-color: var(--wz-cyan) transparent;
}
.customer-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.customer-list-item:last-child { border-bottom: none; }
.customer-list-item:hover { background: rgba(6,182,212,0.08); }
.customer-list-item.selected {
    background: rgba(6,182,212,0.15);
    border-left: 3px solid var(--wz-cyan);
}
.customer-list-item .avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(6,182,212,0.3);
    background: rgba(6,182,212,0.1);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700; color: var(--wz-cyan);
    overflow: hidden;
    flex-shrink: 0;
}
.customer-list-item .info .name { font-weight: 700; font-size: 0.9rem; color: #F8FAFC; }
.customer-list-item .info .sub  { font-size: 0.78rem; color: #94A3B8; }

/* ─── Customer Info Panel ─── */
#customerInfoPanel {
    transition: all 0.4s cubic-bezier(0.22,1,0.36,1);
    overflow: hidden;
    max-height: 0;
    opacity: 0;
}
#customerInfoPanel.visible {
    max-height: 1200px;
    opacity: 1;
}
.info-grid-card {
    background: rgba(6,182,212,0.06);
    border: 1px solid rgba(6,182,212,0.15);
    border-radius: 14px;
    padding: 20px 24px;
    margin-bottom: 20px;
}
.info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.info-row:last-child { margin-bottom: 0; }
.info-row .label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    min-width: 140px;
    flex-shrink: 0;
}
.info-row .value { font-size: 0.9rem; color: #F8FAFC; font-weight: 500; }

/* ─── New User Form Panel ─── */
#newUserPanel {
    transition: all 0.4s cubic-bezier(0.22,1,0.36,1);
    overflow: hidden;
    max-height: 0;
    opacity: 0;
}
#newUserPanel.visible {
    max-height: 2000px;
    opacity: 1;
}

.new-user-card {
    background: rgba(15, 23, 42, 0.65);
    border: 1px solid rgba(6, 182, 212, 0.25);
    border-radius: 16px;
    padding: 24px;
    backdrop-filter: blur(12px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
}
.new-user-card .form-label {
    color: #CBD5E1 !important;
    font-weight: 600 !important;
    font-size: 0.88rem !important;
}
.new-user-card .form-control,
.new-user-card .form-select {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #F8FAFC !important;
    border-radius: 10px !important;
    font-size: 0.9rem !important;
    padding: 10px 14px !important;
    transition: all 0.2s !important;
}
.new-user-card .form-control:focus,
.new-user-card .form-select:focus {
    background: rgba(6, 182, 212, 0.08) !important;
    border-color: var(--wz-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15) !important;
    color: #F8FAFC !important;
}
.new-user-card .form-select option {
    background: #1E293B !important;
    color: #F8FAFC !important;
}
.new-user-card .input-group {
    border-radius: 10px;
    overflow: hidden;
}
.new-user-card .input-group .form-select {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    border-right: none !important;
    background-color: rgba(6, 182, 212, 0.1) !important;
    color: #38BDF8 !important;
    font-weight: 700 !important;
}
.new-user-card .input-group .form-control {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

/* ─── Truck Type Card (Step 3) ─── */
.truck-type-card {
    background: rgba(255,255,255,0.03);
    border: 2px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.25s;
    text-align: center;
    height: 100%;
}
.truck-type-card:hover {
    border-color: rgba(6,182,212,0.4);
    background: rgba(6,182,212,0.06);
}
.truck-type-card.selected {
    border-color: var(--wz-cyan);
    background: rgba(6,182,212,0.12);
    box-shadow: 0 0 20px rgba(6,182,212,0.2);
}
.truck-type-card .truck-icon { font-size: 2.5rem; color: var(--wz-cyan); margin-bottom: 8px; }
.truck-type-card .truck-name { font-weight: 700; font-size: 0.9rem; color: #F8FAFC; }
.truck-type-card .truck-weight { font-size: 0.78rem; color: #94A3B8; margin-top: 4px; }

/* ─── Sub-type select ─── */
#subTypeContainer {
    transition: all 0.3s ease;
    overflow: hidden;
    max-height: 0;
    opacity: 0;
}
#subTypeContainer.visible {
    max-height: 200px;
    opacity: 1;
}

/* ─── Map ─── */
.map-container {
    height: 300px;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid var(--wz-border);
    position: relative;
}
.map-container iframe,
.map-container #pickupMap,
.map-container #dropoffMap {
    width: 100%; height: 100%; border: none; border-radius: 14px;
}
.map-coords-badge {
    position: absolute;
    bottom: 10px; left: 10px;
    background: rgba(15,23,42,0.85);
    backdrop-filter: blur(6px);
    border: 1px solid var(--wz-border);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 0.78rem;
    color: var(--wz-cyan-light);
    z-index: 10;
}

/* ─── Map Search Box ─── */
.map-search-wrap {
    position: relative;
    z-index: 5;
}
.map-search-inner {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 4px 14px;
    transition: all 0.25s ease;
}
.map-search-inner:focus-within {
    border-color: var(--wz-cyan);
    background: rgba(6,182,212,0.07);
    box-shadow: 0 0 0 3px rgba(6,182,212,0.12);
}
.map-search-icon {
    font-size: 18px;
    flex-shrink: 0;
    opacity: 0.8;
}
.map-search-input {
    flex: 1;
    background: transparent !important;
    border: none !important;
    outline: none !important;
    color: #F8FAFC !important;
    font-size: 0.9rem !important;
    padding: 8px 0 !important;
    box-shadow: none !important;
}
.map-search-input::placeholder {
    color: #475569 !important;
    font-size: 0.88rem !important;
}
.map-search-clear {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 6px;
    color: #94A3B8;
    width: 26px; height: 26px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s;
    flex-shrink: 0;
}
.map-search-clear:hover { background: rgba(244,63,94,0.15); border-color: rgba(244,63,94,0.3); color: #F43F5E; }

/* ─── Google Autocomplete Dropdown Dark Override ─── */
.pac-container {
    background: #1E293B !important;
    border: 1px solid rgba(6,182,212,0.25) !important;
    border-radius: 12px !important;
    margin-top: 4px !important;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5) !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    overflow: hidden !important;
}
.pac-item {
    background: transparent !important;
    border-top: 1px solid rgba(255,255,255,0.05) !important;
    color: #CBD5E1 !important;
    font-size: 0.85rem !important;
    padding: 10px 14px !important;
    cursor: pointer !important;
    transition: background 0.2s !important;
}
.pac-item:first-child { border-top: none !important; }
.pac-item:hover, .pac-item-selected {
    background: rgba(6,182,212,0.12) !important;
    color: #F8FAFC !important;
}
.pac-item-query {
    color: #38BDF8 !important;
    font-weight: 700 !important;
    font-size: 0.88rem !important;
}
.pac-icon { filter: invert(1) opacity(0.5) !important; }
.pac-logo::after { display: none !important; }

/* ─── Review Cards (Step 8) ─── */
.review-section {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--wz-border);
    border-radius: 14px;
    padding: 20px 24px;
    margin-bottom: 16px;
}
.review-section .review-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.review-section .review-header i { font-size: 20px; color: var(--wz-cyan); }
.review-section .review-header h6 { font-weight: 700; color: #F8FAFC; margin: 0; font-size: 0.95rem; }
.review-item {
    display: flex;
    gap: 12px;
    margin-bottom: 10px;
    align-items: flex-start;
    min-width: 0;
}
.review-item:last-child { margin-bottom: 0; }
.review-item .r-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    min-width: 130px;
    flex-shrink: 0;
}
.review-item .r-value {
    font-size: 0.88rem;
    color: #E2E8F0;
    font-weight: 500;
    flex: 1;
    min-width: 0;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.review-item .r-value.highlight { color: var(--wz-cyan-light); font-weight: 700; }

@media (max-width: 576px) {
    .review-item {
        flex-direction: column;
        gap: 3px;
        margin-bottom: 12px;
    }
    .review-item .r-label {
        min-width: auto;
        font-size: 0.72rem;
    }
    .review-item .r-value {
        font-size: 0.84rem;
        width: 100%;
    }
}

/* ─── Buttons ─── */
.btn-wz-next {
    background: linear-gradient(135deg, var(--wz-cyan), var(--wz-cyan-light));
    border: none;
    color: #0F172A;
    font-weight: 700;
    padding: 10px 28px;
    border-radius: 10px;
    transition: all 0.25s;
    box-shadow: 0 4px 15px rgba(6,182,212,0.3);
}
.btn-wz-next:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(6,182,212,0.4); }
.btn-wz-prev {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    color: #94A3B8;
    font-weight: 600;
    padding: 10px 24px;
    border-radius: 10px;
    transition: all 0.25s;
}
.btn-wz-prev:hover { background: rgba(255,255,255,0.1); color: #F8FAFC; }
.btn-submit-order {
    background: linear-gradient(135deg, var(--wz-success), #34D399);
    border: none;
    color: #fff;
    font-weight: 800;
    padding: 14px 40px;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.25s;
    box-shadow: 0 6px 20px rgba(16,185,129,0.35);
}
.btn-submit-order:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(16,185,129,0.45); }

/* ─── Form Controls in wizard ─── */
.wz-card .form-control,
.wz-card .form-select {
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #F8FAFC !important;
    border-radius: 10px !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
    padding-right: 14px !important;
    padding-left: 14px;
    transition: all 0.2s !important;
}
.wz-card .form-control.input-icon-pad,
.input-icon-pad {
    padding-left: 44px !important;
}
.wz-card .form-control:focus,
.wz-card .form-select:focus {
    background: rgba(6,182,212,0.08) !important;
    border-color: var(--wz-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.15) !important;
    color: #F8FAFC !important;
}
.wz-card .form-control::placeholder { color: #475569 !important; }
.wz-card .form-label { color: #CBD5E1 !important; font-weight: 600 !important; font-size: 0.88rem !important; }
.wz-card .form-select option { background: #1E293B; color: #F8FAFC; }

/* ─── Step Counter Badge ─── */
.step-counter {
    background: rgba(6,182,212,0.15);
    border: 1px solid rgba(6,182,212,0.3);
    border-radius: 50px;
    padding: 4px 14px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--wz-cyan-light);
    white-space: nowrap;
}

/* ─── Search Box for Customers ─── */
.customer-search {
    position: relative;
    margin-bottom: 10px;
}
.customer-search input {
    background: rgba(255,255,255,0.04) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    color: #F8FAFC !important;
    padding-left: 38px !important;
    border-radius: 10px !important;
}
.customer-search input:focus {
    border-color: var(--wz-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6,182,212,0.1) !important;
}
.customer-search .search-ic {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    color: #64748B;
    font-size: 16px;
}

/* ─── Role Badge ─── */
.role-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}
.role-badge.individual { background: rgba(56,189,248,0.15); color: #38BDF8; }
.role-badge.company    { background: rgba(245,158,11,0.15);  color: #F59E0B; }

/* ─── Dimensions Inputs ─── */
.dimension-wrap { position: relative; }
.dimension-wrap .dim-label {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--wz-cyan);
    pointer-events: none;
}

/* ─── Contact Section Split ─── */
.contact-section-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 14px;
    padding: 22px 24px;
}
.contact-section-card.loading { border-color: rgba(6,182,212,0.2); }
.contact-section-card.arrival { border-color: rgba(16,185,129,0.2); }

/* ─── Pricing card ─── */
.pricing-card-glow {
    background: linear-gradient(135deg, rgba(6,182,212,0.08), rgba(56,189,248,0.04));
    border: 1px solid rgba(6,182,212,0.2);
    border-radius: 20px;
    padding: 36px 20px;
    text-align: center;
    max-width: 100%;
    overflow: hidden;
}
.pricing-card-glow .currency-symbol { font-size: 1.8rem; color: var(--wz-cyan); font-weight: 800; flex-shrink: 0; }
.pricing-card-glow .price-input {
    font-size: 2.2rem !important;
    font-weight: 800 !important;
    text-align: center !important;
    background: transparent !important;
    border: none !important;
    border-bottom: 2px solid rgba(6,182,212,0.4) !important;
    border-radius: 0 !important;
    color: #F8FAFC !important;
    padding: 6px 12px !important;
    width: 180px;
    max-width: 100%;
}
.pricing-card-glow .price-input:focus {
    border-bottom-color: var(--wz-cyan) !important;
    box-shadow: none !important;
    background: transparent !important;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR ADD SHIPMENT 8-STEP WIZARD
═════════════════════════════════════════════════════════════ */
html.light-theme .wz-progress-wrap {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
}
html.light-theme .wz-steps-track::before {
    background: #E2E8F0 !important;
}
html.light-theme .wz-step-circle {
    background: #F1F5F9 !important;
    border-color: #CBD5E1 !important;
    color: #64748B !important;
}
html.light-theme .wz-step-label {
    color: #64748B !important;
}
html.light-theme .wz-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
}
html.light-theme .wz-card-header {
    background: linear-gradient(135deg, rgba(2,132,199,0.08) 0%, rgba(3,105,161,0.04) 100%) !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .wz-card-header h4 {
    color: #0F172A !important;
}
html.light-theme .wz-card-header p {
    color: #64748B !important;
}
html.light-theme .wz-card-footer {
    background: #F8FAFC !important;
    border-top-color: #E2E8F0 !important;
}
html.light-theme .customer-list-container {
    border-color: #E2E8F0 !important;
    background: #FFFFFF !important;
}
html.light-theme .customer-list-item {
    border-bottom-color: #F1F5F9 !important;
}
html.light-theme .customer-list-item:hover {
    background: #F1F5F9 !important;
}
html.light-theme .customer-list-item.selected {
    background: #E0F2FE !important;
}
html.light-theme .customer-list-item .info .name {
    color: #0F172A !important;
}
html.light-theme .customer-list-item .info .sub {
    color: #64748B !important;
}
html.light-theme .info-grid-card {
    background: #F0F9FF !important;
    border-color: #BAE6FD !important;
}
html.light-theme .info-row .value {
    color: #0F172A !important;
}
html.light-theme .new-user-card {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.04) !important;
}
html.light-theme .new-user-card .form-label,
html.light-theme .wz-card .form-label {
    color: #334155 !important;
}
html.light-theme .new-user-card .form-control,
html.light-theme .new-user-card .form-select,
html.light-theme .wz-card .form-control,
html.light-theme .wz-card .form-select {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .new-user-card .form-control:focus,
html.light-theme .new-user-card .form-select:focus,
html.light-theme .wz-card .form-control:focus,
html.light-theme .wz-card .form-select:focus {
    background: #FFFFFF !important;
    border-color: #0284C7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.15) !important;
    color: #0F172A !important;
}
html.light-theme .new-user-card .form-select option,
html.light-theme .wz-card .form-select option {
    background: #FFFFFF !important;
    color: #0F172A !important;
}
html.light-theme .truck-type-card {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .truck-type-card:hover {
    background: #F0F9FF !important;
    border-color: #0284C7 !important;
}
html.light-theme .truck-type-card.selected {
    background: #E0F2FE !important;
    border-color: #0284C7 !important;
}
html.light-theme .truck-type-card .truck-name {
    color: #0F172A !important;
}
html.light-theme .map-container {
    border-color: #CBD5E1 !important;
}
html.light-theme .map-coords-badge {
    background: rgba(255,255,255,0.9) !important;
    color: #0369A1 !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .map-search-inner {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .map-search-input {
    color: #0F172A !important;
}
html.light-theme .review-section {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .review-section .review-header {
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .review-section .review-header h6 {
    color: #0F172A !important;
}
html.light-theme .review-item .r-value {
    color: #0F172A !important;
}
html.light-theme .btn-wz-prev {
    background: #F1F5F9 !important;
    border-color: #CBD5E1 !important;
    color: #475569 !important;
}
html.light-theme .btn-wz-prev:hover {
    background: #E2E8F0 !important;
    color: #0F172A !important;
}
html.light-theme .pac-container {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}
html.light-theme .pac-item {
    border-top-color: #F1F5F9 !important;
    color: #334155 !important;
}
html.light-theme .pac-item:hover,
html.light-theme .pac-item-selected {
    background: #F0F9FF !important;
    color: #0284C7 !important;
}

/* ─── HS Code Component Styling & Light Theme Adaptive Rules ─── */
.hs-metric-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 10px 12px;
}
.hs-restrictions-card {
    background: rgba(245, 158, 11, 0.08);
    border: 1px solid rgba(245, 158, 11, 0.25);
    border-radius: 10px;
    padding: 14px 16px;
}
.hs-analysis-card {
    background: rgba(6, 182, 212, 0.06);
    border: 1px solid rgba(6, 182, 212, 0.2);
    border-radius: 10px;
    padding: 14px 16px;
}

html.light-theme .hs-code-wrap {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
}
html.light-theme #hs_code {
    background: #FFFFFF !important;
    color: #0F172A !important;
    border-color: #CBD5E1 !important;
}
html.light-theme #hsCodeResultPanel {
    background: #FFFFFF !important;
    border: 1px solid #CBD5E1 !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06) !important;
}
html.light-theme #hsCodeResultPanel .text-white {
    color: #0F172A !important;
}
html.light-theme #hsCodeResultPanel .text-slate-200,
html.light-theme #hsCodeResultPanel .text-slate-300,
html.light-theme #hsCodeResultPanel .text-slate-400 {
    color: #475569 !important;
}
html.light-theme #hsCodeResultPanel .text-info {
    color: #0284C7 !important;
}
html.light-theme #hsCodeResultPanel .border-secondary {
    border-color: #E2E8F0 !important;
}
html.light-theme .hs-metric-card {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .hs-restrictions-card {
    background: #FEF3C7 !important;
    border-color: #FDE68A !important;
}
html.light-theme .hs-restrictions-card h6 {
    color: #92400E !important;
}
html.light-theme .hs-restrictions-card li {
    color: #334155 !important;
}
html.light-theme .hs-analysis-card {
    background: #F0F9FF !important;
    border-color: #BAE6FD !important;
}
html.light-theme .hs-analysis-card h6 {
    color: #0F172A !important;
}
html.light-theme .hs-analysis-card li {
    color: #334155 !important;
}
html.light-theme .res-verified-badge {
    background-color: #DCFCE7 !important;
    color: #15803D !important;
    border-color: #86EFAC !important;
}
html.light-theme .contact-section-card {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .pricing-card-glow {
    background: linear-gradient(135deg, #F0F9FF, #E0F2FE) !important;
    border-color: #BAE6FD !important;
}
html.light-theme .pricing-card-glow .price-input {
    color: #0F172A !important;
    border-bottom-color: #0284C7 !important;
}
html.light-theme .customer-search input {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .step-counter {
    background: #E0F2FE !important;
    color: #0369A1 !important;
    border-color: #BAE6FD !important;
}
</style>

{{-- Page Header --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold mb-1 header-title">
            <i class="bx bx-package text-info me-2"></i>Add New Shipment Order
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Fill in the 8-step wizard to create a new shipment request on SALASIL.
        </p>
    </div>
    <div>
        <a href="{{ route('all.owners') }}" class="btn btn-outline-info rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-list-ul fs-5"></i>
            <span>View All Clients</span>
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger border-0 rounded-4 p-3 mb-4 shadow-sm" style="background: rgba(244,63,94,0.15); border: 1px solid rgba(244,63,94,0.3) !important;">
        <h5 class="fw-bold mb-2 text-danger"><i class="bx bx-error-circle me-1"></i> Order Submission Warning:</h5>
        <ul class="mb-0 ps-3 text-white">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Main Form --}}
<form method="POST" action="{{ route('store.shipment') }}" id="wizardForm" autocomplete="off">
    @csrf

    {{-- ─── Progress Steps ─── --}}
    <div class="wz-progress-wrap">
        <div class="wz-steps-track" id="stepsTrack">
            @php
            $steps = [
                ['icon'=>'bx bx-user', 'label'=>'Customer'],
                ['icon'=>'bx bx-package', 'label'=>'Details'],
                ['icon'=>'bx bx-truck', 'label'=>'Truck'],
                ['icon'=>'bx bx-map-pin', 'label'=>'Pickup'],
                ['icon'=>'bx bx-navigation', 'label'=>'Delivery'],
                ['icon'=>'bx bx-phone', 'label'=>'Contacts'],
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

    {{-- ─── Wizard Card ─── --}}
    <div class="wz-card">

        {{-- ════ STEP 1: Customer Information ════ --}}
        <div class="wz-step-panel active" id="step1">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-user-circle"></i></div>
                <div>
                    <h4>Customer Information</h4>
                    <p>Select an existing customer or add a new one to assign this shipment.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 1 of 8</span></div>
            </div>
            <div class="wz-card-body">

                {{-- Hidden field for customer_id --}}
                <input type="hidden" name="customer_id" id="selectedCustomerId" value="">

                {{-- Customer Search + List --}}
                <div class="wz-section-title"><i class="bx bx-search-alt me-1"></i>Select Customer</div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="customer-search">
                            <i class="bx bx-search-alt search-ic"></i>
                            <input type="text" id="customerSearch" class="form-control" placeholder="Search by name, email or phone...">
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
                            <div class="text-center text-muted py-4">No customers found.</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Add New Customer Button --}}
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="flex-grow-1" style="height:1px;background:rgba(255,255,255,0.06);"></div>
                    <span class="text-muted" style="font-size:0.8rem;">OR</span>
                    <div class="flex-grow-1" style="height:1px;background:rgba(255,255,255,0.06);"></div>
                </div>
                <div class="text-center mb-4">
                    <button type="button" id="toggleNewUserBtn" class="btn btn-outline-info rounded-3 px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                        <i class="bx bx-user-plus fs-5"></i>
                        <span>Add New Customer</span>
                    </button>
                </div>

                {{-- ── Customer Info Display Panel ── --}}
                <div id="customerInfoPanel">
                    <div class="wz-section-title mt-2"><i class="bx bx-info-circle me-1"></i>Customer Details</div>
                    <div id="customerInfoLoading" class="text-center py-4" style="display:none;">
                        <span class="wz-spinner"></span> <span class="text-info">Loading customer data...</span>
                    </div>
                    <div id="customerInfoContent"></div>
                </div>

                {{-- ── New User Form Panel ── --}}
                <div id="newUserPanel">
                    <div class="wz-section-title mt-2"><i class="bx bx-user-plus me-1"></i>New Customer Details</div>
                    <div class="new-user-card">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="new_fname" id="new_fname" class="form-control" placeholder="e.g. Ahmed">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="new_lname" id="new_lname" class="form-control" placeholder="e.g. Al-Qahtani">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="new_email" id="new_email" class="form-control" placeholder="ahmed@example.com">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <select name="new_country_code" class="form-select" style="max-width:120px;">
                                        <option value="+966">+966</option>
                                        <option value="+971">+971</option>
                                        <option value="+965">+965</option>
                                        <option value="+968">+968</option>
                                        <option value="+973">+973</option>
                                        <option value="+20">+20</option>
                                    </select>
                                    <input type="text" name="new_phone" class="form-control" placeholder="500000000">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Account Role <span class="text-danger">*</span></label>
                                <select name="new_role" id="new_role" class="form-select">
                                    <option value="individual_customer">Individual Client</option>
                                    <option value="company_customer">Company / Corporate</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="new_address" class="form-control" rows="2" placeholder="Full address..."></textarea>
                            </div>
                            {{-- Company fields (shown if company_customer) --}}
                            <div id="newCompanyFields" style="display:none;" class="col-12">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Company Legal Name <span class="text-danger">*</span></label>
                                        <input type="text" name="new_company_name" class="form-control" placeholder="Company Name LLC">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Commercial Register No. <span class="text-danger">*</span></label>
                                        <input type="text" name="new_commercial_register" class="form-control" placeholder="1010123456">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Civil ID <span class="text-danger">*</span></label>
                                        <input type="text" name="new_civil_id" class="form-control" placeholder="7001234567">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Tax / VAT Number</label>
                                        <input type="text" name="new_tax_number" class="form-control" placeholder="300012345600003">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Representative Name</label>
                                        <input type="text" name="new_representative_name" class="form-control">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Representative Position</label>
                                        <input type="text" name="new_representative_position" class="form-control">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Representative Phone</label>
                                        <input type="text" name="new_representative_phone" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" id="saveNewUserBtn" class="btn btn-wz-next px-4">
                                    <i class="bx bx-save me-1"></i> Create & Select Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="wz-card-footer">
                <div></div>
                <button type="button" class="btn-wz-next btn d-inline-flex align-items-center gap-2" id="nextBtn1">
                    Next: Shipment Details <i class="bx bx-right-arrow-alt fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ════ STEP 2: Shipment Details ════ --}}
        <div class="wz-step-panel" id="step2">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-package"></i></div>
                <div>
                    <h4>Shipment Details</h4>
                    <p>Enter the cargo information, dimensions, and shipment classification.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 2 of 8</span></div>
            </div>
            <div class="wz-card-body">

                <div class="row g-4">
                    {{-- Shipment Name --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label">Shipment Name</label>
                        <div class="position-relative">
                            <input type="text" name="shipment_name" id="shipment_name" class="form-control input-icon-pad" style="padding-left:44px !important;" placeholder="e.g. Electronics Batch #001">
                            <span class="position-absolute text-info" style="left:14px;top:50%;transform:translateY(-50%);pointer-events:none;"><i class="bx bx-tag fs-5"></i></span>
                        </div>
                    </div>

                    {{-- Shipment Type --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label">Shipment Type</label>
                        <select name="shipment_type_id" id="shipment_type_id" class="form-select">
                            <option value="">-- Select Type --</option>
                            @foreach($shipmentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name_en ?? $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Shipment Description --}}
                    <div class="col-12">
                        <label class="form-label">Shipment Description</label>
                        <textarea name="shipment_description" id="shipment_description" class="form-control" rows="3" placeholder="Brief description of this shipment..."></textarea>
                    </div>

                    {{-- Shipment Nature --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label">Shipment Nature</label>
                        <select name="shipment_nature_id" id="shipment_nature_id" class="form-select">
                            <option value="">-- Select Nature --</option>
                            @foreach($shipmentNatures as $nature)
                                <option value="{{ $nature->id }}">{{ $nature->name_en ?? $nature->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Weight --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label">Weight (kg)</label>
                        <div class="position-relative">
                            <input type="number" name="weight" id="weight" class="form-control input-icon-pad" style="padding-left:44px !important;" placeholder="0.00" step="0.01" min="0">
                            <span class="position-absolute text-info" style="left:14px;top:50%;transform:translateY(-50%);pointer-events:none;"><i class="bx bx-dumbbell fs-5"></i></span>
                        </div>
                    </div>

                    {{-- No. of Pieces --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label">No. of Pieces</label>
                        <div class="position-relative">
                            <input type="number" name="packages_count" id="packages_count" class="form-control input-icon-pad" style="padding-left:44px !important;" placeholder="1" min="1" value="1">
                            <span class="position-absolute text-info" style="left:14px;top:50%;transform:translateY(-50%);pointer-events:none;"><i class="bx bx-box fs-5"></i></span>
                        </div>
                    </div>

                    {{-- Dimensions --}}
                    <div class="col-12">
                        <label class="form-label">Dimensions (cm)</label>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="dimension-wrap">
                                    <input type="number" name="length" id="length" class="form-control pe-5" placeholder="Length" min="0" step="0.1">
                                    <span class="dim-label">L</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="dimension-wrap">
                                    <input type="number" name="width" id="width" class="form-control pe-5" placeholder="Width" min="0" step="0.1">
                                    <span class="dim-label">W</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="dimension-wrap">
                                    <input type="number" name="height" id="height" class="form-control pe-5" placeholder="Height" min="0" step="0.1">
                                    <span class="dim-label">H</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cargo Notes --}}
                    <div class="col-12">
                        <label class="form-label">Cargo Notes</label>
                        <textarea name="goods_description" id="goods_description" class="form-control" rows="3" placeholder="Special handling instructions, fragile items, notes..."></textarea>
                    </div>

                    {{-- HS Code (Harmonized System Tariff Code) Lookup --}}
                    <div class="col-12">
                        <div class="p-3.5 rounded-3 border hs-code-wrap" style="background: rgba(78, 205, 196, 0.05); border-color: rgba(78, 205, 196, 0.25) !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                <label class="form-label mb-0 fw-bold text-info d-flex align-items-center gap-2">
                                    <i class="bx bx-barcode-reader fs-5"></i> HS Code (Harmonized Tariff Code) <span class="badge bg-secondary bg-opacity-30 text-slate-300 fs-9 font-normal ms-1">Optional / اختياري</span>
                                </label>
                            </div>
                            
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-dark border-secondary text-info fw-bold"><i class="bx bx-barcode"></i></span>
                                <input type="text" name="hs_code" id="hs_code" class="form-control text-white fw-bold" placeholder="Enter HS Code (e.g. 8704.21 or 8471.30)" value="{{ old('hs_code') }}">
                                <button type="button" id="btnLookupHsCode" class="btn btn-info px-4 fw-semibold d-inline-flex align-items-center gap-1">
                                    <i class="bx bx-search fs-5"></i> <span>Lookup Code</span>
                                </button>
                            </div>

                            {{-- Dynamic HS Code Result Box --}}
                            <div id="hsCodeResultPanel" class="mt-3 p-3 rounded-3" style="display:none; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(78, 205, 196, 0.3);">
                                <div id="hsCodeLoader" class="text-center py-2" style="display:none;">
                                    <span class="spinner-border spinner-border-sm text-info me-2"></span> Searching Customs Tariff Catalog...
                                </div>
                                <div id="hsCodeDetailsContent"></div>
                            </div>
                            <input type="hidden" name="hs_code_description" id="hs_code_description_input" value="{{ old('hs_code_description') }}">
                        </div>
                    </div>
                </div>

            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev btn d-inline-flex align-items-center gap-2" data-goto="1">
                    <i class="bx bx-left-arrow-alt fs-5"></i> Back
                </button>
                <button type="button" class="btn-wz-next btn d-inline-flex align-items-center gap-2" data-goto="3">
                    Next: Truck Type <i class="bx bx-right-arrow-alt fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ════ STEP 3: Truck Type ════ --}}
        <div class="wz-step-panel" id="step3">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-truck"></i></div>
                <div>
                    <h4>Truck Type</h4>
                    <p>Choose the appropriate truck type for this shipment.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 3 of 8</span></div>
            </div>
            <div class="wz-card-body">

                <input type="hidden" name="truck_type_id" id="truck_type_id" value="">
                <input type="hidden" name="truck_sub_type_id" id="truck_sub_type_id" value="">

                <div class="wz-section-title"><i class="bx bx-truck me-1"></i>Select Truck Type</div>

                <div class="row g-3" id="truckTypeGrid">
                    @foreach($truckTypes as $tt)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="truck-type-card" data-id="{{ $tt->id }}" data-name="{{ $tt->name_en ?? $tt->name }}" onclick="selectTruckType(this)">
                            <div class="truck-icon"><i class="bx bx-truck"></i></div>
                            <div class="truck-name">{{ $tt->name_en ?? $tt->name }}</div>
                            @if($tt->max_weight)
                            <div class="truck-weight">Max {{ number_format($tt->max_weight) }} kg</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @if($truckTypes->isEmpty())
                    <div class="col-12 text-center text-muted py-4">No truck types found.</div>
                    @endif
                </div>

                {{-- Sub-types section --}}
                <div id="subTypeContainer" class="mt-4">
                    <div class="wz-section-title"><i class="bx bx-category me-1"></i>Select Sub-Type</div>
                    <div id="subTypeLoading" style="display:none;" class="mb-2">
                        <span class="wz-spinner"></span> <span class="text-info text-sm">Loading sub-types...</span>
                    </div>
                    <select name="_truck_sub_type_select" id="truckSubTypeSelect" class="form-select" style="max-width:360px;">
                        <option value="">-- Select Sub-Type --</option>
                    </select>
                </div>

            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev btn d-inline-flex align-items-center gap-2" data-goto="2">
                    <i class="bx bx-left-arrow-alt fs-5"></i> Back
                </button>
                <button type="button" class="btn-wz-next btn d-inline-flex align-items-center gap-2" data-goto="4">
                    Next: Pickup Location <i class="bx bx-right-arrow-alt fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ════ STEP 4: Pickup Location ════ --}}
        <div class="wz-step-panel" id="step4">
            <div class="wz-card-header">
                <div class="step-icon"><i class="bx bx-map-pin"></i></div>
                <div>
                    <h4>Pickup Location</h4>
                    <p>Specify where the cargo will be picked up from.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 4 of 8</span></div>
            </div>
            <div class="wz-card-body">

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Country</label>
                        <select name="pickup_country_id" id="pickup_country_id" class="form-select">
                            <option value="">-- Select Country --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">City</label>
                        <div id="pickupCityLoadingWrap" style="display:none;" class="mb-1">
                            <span class="wz-spinner"></span><span class="text-info text-sm">Loading cities...</span>
                        </div>
                        <select name="pickup_city_id" id="pickup_city_id" class="form-select" disabled>
                            <option value="">-- Select Country First --</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Area</label>
                        <input type="text" name="pickup_area" id="pickup_area" class="form-control" placeholder="e.g. Industrial Area, Zone 3">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Full Address <span class="text-danger">*</span></label>
                        <input type="text" name="pickup_address" id="pickup_address" class="form-control" placeholder="e.g. King Fahd Road, Building 12">
                    </div>
                    <div class="col-12">
                        <label class="form-label d-flex align-items-center gap-2">
                            <i class="bx bx-map text-info"></i>
                            Map Location
                            <small class="text-muted">(Search or click on the map to set the pickup pin)</small>
                        </label>
                        {{-- Map Search Box --}}
                        <div class="map-search-wrap mb-2" id="pickupSearchWrap">
                            <div class="map-search-inner">
                                <i class="bx bx-search map-search-icon text-info"></i>
                                <input type="text" id="pickupSearchInput" class="map-search-input" placeholder="🔍  Search for pickup location..." autocomplete="off">
                                <button type="button" class="map-search-clear" id="pickupSearchClear" title="Clear search" style="display:none;">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="map-container position-relative" id="pickupMapWrap">
                            <div id="pickupMap"></div>
                            <div class="map-coords-badge" id="pickupCoordsLabel">
                                <i class="bx bx-crosshair me-1"></i>Click map to set coordinates
                            </div>
                        </div>
                        <input type="hidden" name="pickup_lat" id="pickup_lat" value="">
                        <input type="hidden" name="pickup_lng" id="pickup_lng" value="">
                    </div>
                </div>

            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev btn d-inline-flex align-items-center gap-2" data-goto="3">
                    <i class="bx bx-left-arrow-alt fs-5"></i> Back
                </button>
                <button type="button" class="btn-wz-next btn d-inline-flex align-items-center gap-2" data-goto="5">
                    Next: Delivery Location <i class="bx bx-right-arrow-alt fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ════ STEP 5: Delivery Location ════ --}}
        <div class="wz-step-panel" id="step5">
            <div class="wz-card-header">
                <div class="step-icon" style="background:linear-gradient(135deg,#10B981,#34D399);box-shadow:0 8px 20px rgba(16,185,129,0.35);">
                    <i class="bx bx-navigation"></i>
                </div>
                <div>
                    <h4>Delivery Location</h4>
                    <p>Specify where the cargo will be delivered to.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 5 of 8</span></div>
            </div>
            <div class="wz-card-body">

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Country</label>
                        <select name="dropoff_country_id" id="dropoff_country_id" class="form-select">
                            <option value="">-- Select Country --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">City</label>
                        <div id="dropoffCityLoadingWrap" style="display:none;" class="mb-1">
                            <span class="wz-spinner"></span><span class="text-info text-sm">Loading cities...</span>
                        </div>
                        <select name="dropoff_city_id" id="dropoff_city_id" class="form-select" disabled>
                            <option value="">-- Select Country First --</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Area</label>
                        <input type="text" name="dropoff_area" id="dropoff_area" class="form-control" placeholder="e.g. Downtown, Block A">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Full Address <span class="text-danger">*</span></label>
                        <input type="text" name="dropoff_address" id="dropoff_address" class="form-control" placeholder="e.g. Prince Sultan Street, Villa 5">
                    </div>
                    <div class="col-12">
                        <label class="form-label d-flex align-items-center gap-2">
                            <i class="bx bx-map text-success"></i>
                            Map Location
                            <small class="text-muted">(Search or click on the map to set the delivery pin)</small>
                        </label>
                        {{-- Map Search Box --}}
                        <div class="map-search-wrap mb-2" id="dropoffSearchWrap">
                            <div class="map-search-inner">
                                <i class="bx bx-search map-search-icon text-success"></i>
                                <input type="text" id="dropoffSearchInput" class="map-search-input" placeholder="🔍  Search for delivery location..." autocomplete="off">
                                <button type="button" class="map-search-clear" id="dropoffSearchClear" title="Clear search" style="display:none;">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="map-container position-relative" id="dropoffMapWrap">
                            <div id="dropoffMap"></div>
                            <div class="map-coords-badge" id="dropoffCoordsLabel">
                                <i class="bx bx-crosshair me-1"></i>Click map to set coordinates
                            </div>
                        </div>
                        <input type="hidden" name="dropoff_lat" id="dropoff_lat" value="">
                        <input type="hidden" name="dropoff_lng" id="dropoff_lng" value="">
                    </div>
                </div>

            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev btn d-inline-flex align-items-center gap-2" data-goto="4">
                    <i class="bx bx-left-arrow-alt fs-5"></i> Back
                </button>
                <button type="button" class="btn-wz-next btn d-inline-flex align-items-center gap-2" data-goto="6">
                    Next: Contacts <i class="bx bx-right-arrow-alt fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ════ STEP 6: Contacts ════ --}}
        <div class="wz-step-panel" id="step6">
            <div class="wz-card-header">
                <div class="step-icon" style="background:linear-gradient(135deg,#8B5CF6,#A78BFA);box-shadow:0 8px 20px rgba(139,92,246,0.35);">
                    <i class="bx bx-phone"></i>
                </div>
                <div>
                    <h4>Pickup & Delivery Contact</h4>
                    <p>Enter the contact details for the loading and receiving parties.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 6 of 8</span></div>
            </div>
            <div class="wz-card-body">

                <div class="row g-4">
                    {{-- Loading Contact --}}
                    <div class="col-12 col-md-6">
                        <div class="contact-section-card loading">
                            <div class="wz-section-title" style="color:#38BDF8;"><i class="bx bx-upload me-1"></i>Loading Contact</div>
                            <div class="mb-3">
                                <label class="form-label">Contact Name</label>
                                <div class="position-relative">
                                    <input type="text" name="loading_contact_name" id="loading_contact_name" class="form-control input-icon-pad" style="padding-left:44px !important;" placeholder="e.g. Mohammed Saeed">
                                    <span class="position-absolute text-info" style="left:14px;top:50%;transform:translateY(-50%);pointer-events:none;"><i class="bx bx-user fs-5"></i></span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Phone Number</label>
                                <div class="position-relative">
                                    <input type="text" name="loading_contact_phone" id="loading_contact_phone" class="form-control input-icon-pad" style="padding-left:44px !important;" placeholder="e.g. +966501234567">
                                    <span class="position-absolute text-info" style="left:14px;top:50%;transform:translateY(-50%);pointer-events:none;"><i class="bx bx-phone fs-5"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Receiving Contact --}}
                    <div class="col-12 col-md-6">
                        <div class="contact-section-card arrival">
                            <div class="wz-section-title" style="color:#34D399;"><i class="bx bx-download me-1"></i>Receiving Contact</div>
                            <div class="mb-3">
                                <label class="form-label">Contact Name</label>
                                <div class="position-relative">
                                    <input type="text" name="arrival_contact_name" id="arrival_contact_name" class="form-control input-icon-pad" style="padding-left:44px !important;" placeholder="e.g. Khalid Al-Rashid">
                                    <span class="position-absolute text-success" style="left:14px;top:50%;transform:translateY(-50%);pointer-events:none;"><i class="bx bx-user fs-5"></i></span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Phone Number</label>
                                <div class="position-relative">
                                    <input type="text" name="arrival_contact_phone" id="arrival_contact_phone" class="form-control input-icon-pad" style="padding-left:44px !important;" placeholder="e.g. +966509876543">
                                    <span class="position-absolute text-success" style="left:14px;top:50%;transform:translateY(-50%);pointer-events:none;"><i class="bx bx-phone fs-5"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev btn d-inline-flex align-items-center gap-2" data-goto="5">
                    <i class="bx bx-left-arrow-alt fs-5"></i> Back
                </button>
                <button type="button" class="btn-wz-next btn d-inline-flex align-items-center gap-2" data-goto="7">
                    Next: Pricing <i class="bx bx-right-arrow-alt fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ════ STEP 7: Pricing ════ --}}
        <div class="wz-step-panel" id="step7">
            <div class="wz-card-header">
                <div class="step-icon" style="background:linear-gradient(135deg,#F59E0B,#FCD34D);box-shadow:0 8px 20px rgba(245,158,11,0.35);">
                    <i class="bx bx-dollar-circle"></i>
                </div>
                <div>
                    <h4>Pricing</h4>
                    <p>Set the estimated price for this shipment order.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 7 of 8</span></div>
            </div>
            <div class="wz-card-body">

                <div class="pricing-card-glow">
                    <div class="mb-3">
                        <i class="bx bx-dollar-circle" style="font-size:3rem;color:var(--wz-warning);"></i>
                    </div>
                    <h5 class="text-slate-300 mb-4">Estimated Shipment Price</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <span class="currency-symbol">KWD</span>
                        <input type="number" name="initial_price" id="initial_price" class="price-input" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <p class="text-muted mt-3" style="font-size:0.82rem;">This is the initial estimated price — it can be adjusted later.</p>
                </div>

            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev btn d-inline-flex align-items-center gap-2" data-goto="6">
                    <i class="bx bx-left-arrow-alt fs-5"></i> Back
                </button>
                <button type="button" class="btn-wz-next btn d-inline-flex align-items-center gap-2" data-goto="8">
                    Next: Review Order <i class="bx bx-right-arrow-alt fs-5"></i>
                </button>
            </div>
        </div>

        {{-- ════ STEP 8: Order Review ════ --}}
        <div class="wz-step-panel" id="step8">
            <div class="wz-card-header">
                <div class="step-icon" style="background:linear-gradient(135deg,#10B981,#06B6D4);box-shadow:0 8px 20px rgba(16,185,129,0.35);">
                    <i class="bx bx-check-shield"></i>
                </div>
                <div>
                    <h4>Order Review</h4>
                    <p>Review all shipment details before submitting the order.</p>
                </div>
                <div class="ms-auto"><span class="step-counter">Step 8 of 8</span></div>
            </div>
            <div class="wz-card-body">

                <div id="reviewContent">
                    {{-- Populated dynamically by JS --}}
                </div>

                <div class="text-center mt-4 pt-2">
                    <p class="text-muted" style="font-size:0.85rem;">By submitting, the shipment order will be created with status <strong class="text-info">New</strong>.</p>
                </div>

            </div>
            <div class="wz-card-footer">
                <button type="button" class="btn-wz-prev btn d-inline-flex align-items-center gap-2" data-goto="7">
                    <i class="bx bx-left-arrow-alt fs-5"></i> Back
                </button>
                <button type="submit" class="btn-submit-order btn d-inline-flex align-items-center gap-2" id="submitOrderBtn">
                    <i class="bx bx-send fs-5"></i>
                    <span>Submit Order</span>
                </button>
            </div>
        </div>

    </div>{{-- end wz-card --}}
</form>

{{-- ─── Google Maps API ─── --}}
<script>
    var pickupMap    = null;
    var dropoffMap   = null;
    var pickupMarker = null;
    var dropoffMapMarker = null;
    var mapsApiReady = false;
    var pickupMapInited  = false;
    var dropoffMapInited = false;

    var darkMapStyles = [
        { elementType: 'geometry',           stylers: [{ color: '#0f172a' }] },
        { elementType: 'labels.text.fill',   stylers: [{ color: '#94a3b8' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#0f172a' }] },
        { featureType: 'road',       elementType: 'geometry',         stylers: [{ color: '#1e293b' }] },
        { featureType: 'road',       elementType: 'labels.text.fill', stylers: [{ color: '#64748b' }] },
        { featureType: 'water',      elementType: 'geometry',         stylers: [{ color: '#0e1a2e' }] },
        { featureType: 'poi',        elementType: 'geometry',         stylers: [{ color: '#0f172a' }] },
        { featureType: 'transit',    elementType: 'geometry',         stylers: [{ color: '#1e293b' }] },
        { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#334155' }] }
    ];

    var defaultCenter = { lat: 24.7136, lng: 46.6753 }; // Riyadh, KSA

    // Called automatically by Google Maps JS loader
    function initMaps() {
        mapsApiReady = true;
        // Maps are initialized lazily when user navigates to step 4 or 5
        // to avoid blank map issue caused by hidden containers
    }

    function getActiveMapStyles() {
        return document.documentElement.classList.contains('light-theme') ? [] : darkMapStyles;
    }

    $(document).on('click', '#theme-toggle-btn', function() {
        setTimeout(function() {
            var activeStyles = getActiveMapStyles();
            if (pickupMap) pickupMap.setOptions({ styles: activeStyles });
            if (dropoffMap) dropoffMap.setOptions({ styles: activeStyles });
        }, 50);
    });

    function initPickupMap() {
        if (pickupMapInited) {
            google.maps.event.trigger(pickupMap, 'resize');
            return;
        }
        pickupMap = new google.maps.Map(document.getElementById('pickupMap'), {
            center: defaultCenter,
            zoom: 11,
            styles: getActiveMapStyles(),
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });
        pickupMap.addListener('click', function(e) {
            setPickupCoords(e.latLng.lat(), e.latLng.lng());
        });

        // ── Pickup Places Autocomplete ──
        var pickupInput = document.getElementById('pickupSearchInput');
        var pickupAuto  = new google.maps.places.Autocomplete(pickupInput, {
            fields: ['geometry', 'formatted_address', 'name']
        });
        pickupAuto.bindTo('bounds', pickupMap);
        pickupAuto.addListener('place_changed', function() {
            var place = pickupAuto.getPlace();
            if (!place.geometry || !place.geometry.location) {
                return;
            }
            // Pan & zoom to place
            if (place.geometry.viewport) {
                pickupMap.fitBounds(place.geometry.viewport);
            } else {
                pickupMap.setCenter(place.geometry.location);
                pickupMap.setZoom(15);
            }
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            setPickupCoords(lat, lng);
            // Auto-fill address field
            if (place.formatted_address) {
                var addrField = document.getElementById('pickup_address');
                if (addrField && !addrField.value) {
                    addrField.value = place.formatted_address;
                }
            }
            // Show clear button
            document.getElementById('pickupSearchClear').style.display = 'flex';
        });

        // ── Clear button ──
        document.getElementById('pickupSearchClear').addEventListener('click', function() {
            pickupInput.value = '';
            this.style.display = 'none';
            document.getElementById('pickup_lat').value = '';
            document.getElementById('pickup_lng').value = '';
            document.getElementById('pickupCoordsLabel').innerHTML =
                '<i class="bx bx-crosshair me-1"></i>Click map to set coordinates';
            if (pickupMarker) { pickupMarker.setMap(null); pickupMarker = null; }
        });
        pickupInput.addEventListener('input', function() {
            document.getElementById('pickupSearchClear').style.display = this.value ? 'flex' : 'none';
        });

        pickupMapInited = true;
    }

    function initDropoffMap() {
        if (dropoffMapInited) {
            google.maps.event.trigger(dropoffMap, 'resize');
            return;
        }
        dropoffMap = new google.maps.Map(document.getElementById('dropoffMap'), {
            center: defaultCenter,
            zoom: 11,
            styles: getActiveMapStyles(),
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });
        dropoffMap.addListener('click', function(e) {
            setDropoffCoords(e.latLng.lat(), e.latLng.lng());
        });

        // ── Dropoff Places Autocomplete ──
        var dropoffInput = document.getElementById('dropoffSearchInput');
        var dropoffAuto  = new google.maps.places.Autocomplete(dropoffInput, {
            fields: ['geometry', 'formatted_address', 'name']
        });
        dropoffAuto.bindTo('bounds', dropoffMap);
        dropoffAuto.addListener('place_changed', function() {
            var place = dropoffAuto.getPlace();
            if (!place.geometry || !place.geometry.location) {
                return;
            }
            // Pan & zoom to place
            if (place.geometry.viewport) {
                dropoffMap.fitBounds(place.geometry.viewport);
            } else {
                dropoffMap.setCenter(place.geometry.location);
                dropoffMap.setZoom(15);
            }
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            setDropoffCoords(lat, lng);
            // Auto-fill address field
            if (place.formatted_address) {
                var addrField = document.getElementById('dropoff_address');
                if (addrField && !addrField.value) {
                    addrField.value = place.formatted_address;
                }
            }
            // Show clear button
            document.getElementById('dropoffSearchClear').style.display = 'flex';
        });

        // ── Clear button ──
        document.getElementById('dropoffSearchClear').addEventListener('click', function() {
            dropoffInput.value = '';
            this.style.display = 'none';
            document.getElementById('dropoff_lat').value = '';
            document.getElementById('dropoff_lng').value = '';
            document.getElementById('dropoffCoordsLabel').innerHTML =
                '<i class="bx bx-crosshair me-1"></i>Click map to set coordinates';
            if (dropoffMapMarker) { dropoffMapMarker.setMap(null); dropoffMapMarker = null; }
        });
        dropoffInput.addEventListener('input', function() {
            document.getElementById('dropoffSearchClear').style.display = this.value ? 'flex' : 'none';
        });

        dropoffMapInited = true;
    }

    function setPickupCoords(lat, lng) {
        document.getElementById('pickup_lat').value = lat.toFixed(8);
        document.getElementById('pickup_lng').value = lng.toFixed(8);
        document.getElementById('pickupCoordsLabel').innerHTML =
            '<i class="bx bx-map-pin me-1"></i>' + lat.toFixed(5) + ' , ' + lng.toFixed(5);
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
        document.getElementById('dropoff_lat').value = lat.toFixed(8);
        document.getElementById('dropoff_lng').value = lng.toFixed(8);
        document.getElementById('dropoffCoordsLabel').innerHTML =
            '<i class="bx bx-map-pin me-1"></i>' + lat.toFixed(5) + ' , ' + lng.toFixed(5);
        if (dropoffMapMarker) dropoffMapMarker.setMap(null);
        dropoffMapMarker = new google.maps.Marker({
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

{{-- ─── Main Wizard JavaScript ─── --}}
<script>
$(document).ready(function() {

    var currentStep = 1;
    var totalSteps  = 8;

    // ── Navigate to step ──
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        // Hide all panels
        $('.wz-step-panel').removeClass('active');
        // Show target
        $('#step' + step).addClass('active');
        // Update progress
        $('.wz-step-item').each(function() {
            var s = parseInt($(this).data('step'));
            $(this).removeClass('active done');
            if (s < step) $(this).addClass('done').find('.wz-step-circle').html('<i class="bx bx-check"></i>');
            else if (s === step) $(this).addClass('active');
            else {
                // reset to number
                $(this).find('.wz-step-circle').html(s);
            }
        });
        currentStep = step;
        // Auto-scroll mobile stepper track to keep active step centered
        var $activeItem = $('.wz-step-item[data-step="' + step + '"]');
        var $wrap = $('.wz-progress-wrap');
        if ($activeItem.length && $wrap.length && $wrap.get(0).scrollWidth > $wrap.get(0).clientWidth) {
            var itemLeft = $activeItem.offset().left - $wrap.offset().left + $wrap.scrollLeft();
            var targetScroll = itemLeft - ($wrap.width() / 2) + ($activeItem.outerWidth() / 2);
            $wrap.animate({ scrollLeft: Math.max(0, targetScroll) }, 300);
        }
        // If navigating to review step, populate summary
        if (step === 8) buildReview();
        // Trigger map init / resize when entering map steps (lazy init for hidden containers)
        if (step === 4) {
            setTimeout(function() {
                if (mapsApiReady) {
                    initPickupMap();
                } else {
                    // API still loading — wait and retry
                    var checkInterval = setInterval(function() {
                        if (mapsApiReady) { clearInterval(checkInterval); initPickupMap(); }
                    }, 200);
                }
            }, 150);
        }
        if (step === 5) {
            setTimeout(function() {
                if (mapsApiReady) {
                    initDropoffMap();
                } else {
                    var checkInterval2 = setInterval(function() {
                        if (mapsApiReady) { clearInterval(checkInterval2); initDropoffMap(); }
                    }, 200);
                }
            }, 150);
        }
        // Scroll to top
        $('html, body').animate({ scrollTop: $('.wz-progress-wrap').offset().top - 80 }, 300);
    }

    // ── Next buttons with data-goto ──
    $('[data-goto]').on('click', function() {
        var target = parseInt($(this).data('goto'));
        if ($(this).hasClass('btn-wz-next')) {
            if (!validateStep(currentStep)) return;
        }
        goToStep(target);
    });

    // ── Direct step click ──
    $('.wz-step-item').on('click', function() {
        var s = parseInt($(this).data('step'));
        if (s <= currentStep) goToStep(s);
    });

    // ── Next Btn 1 (special case) ──
    $('#nextBtn1').on('click', function() {
        if (!validateStep(1)) return;
        goToStep(2);
    });

    // ── Validation ──
    function validateStep(step) {
        if (step === 1) {
            var cid = $('#selectedCustomerId').val();
            var newMode = $('#newUserPanel').hasClass('visible');
            if (!cid && !newMode) {
                toastr.warning('Please select a customer or add a new one.');
                return false;
            }
        }
        return true;
    }

    // ── Customer Search ──
    $('#customerSearch').on('input', function() {
        var q = $(this).val().toLowerCase();
        $('.customer-list-item').each(function() {
            var s = $(this).data('search');
            $(this).toggle(s.includes(q));
        });
    });

    // ── Customer List Item Click ──
    $(document).on('click', '.customer-list-item', function() {
        $('.customer-list-item').removeClass('selected');
        $(this).addClass('selected');
        var id = $(this).data('id');
        $('#selectedCustomerId').val(id);
        // Hide new user panel
        $('#newUserPanel').removeClass('visible');
        $('#toggleNewUserBtn').html('<i class="bx bx-user-plus fs-5"></i> <span>Add New Customer</span>');
        // Load user info
        loadCustomerInfo(id);
    });

    // ── Load Customer Info via AJAX ──
    function loadCustomerInfo(id) {
        $('#customerInfoPanel').addClass('visible');
        $('#customerInfoLoading').show();
        $('#customerInfoContent').empty();

        $.ajax({
            url: '{{ route("get.shipment.user.data.ajax", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function(res) {
                $('#customerInfoLoading').hide();
                if (res.status === 'success') {
                    renderCustomerInfo(res.user);
                }
            },
            error: function() {
                $('#customerInfoLoading').hide();
                $('#customerInfoContent').html('<div class="alert alert-danger rounded-3">Failed to load customer data.</div>');
            }
        });
    }

    function renderCustomerInfo(u) {
        var roleLabel = u.role === 'company_customer' ? '<span class="role-badge company">Company</span>' : '<span class="role-badge individual">Individual</span>';
        var statusClass = u.status === 'active' ? 'text-success' : 'text-warning';

        var html = '<div class="info-grid-card">';
        html += '<div class="row g-3">';
        html += '<div class="col-12 col-md-3 text-center">';
        if (u.photo) {
            html += '<img src="/upload/user_images/' + u.photo + '" class="rounded-circle border border-info p-1" style="width:80px;height:80px;object-fit:cover;">';
        } else {
            html += '<div class="rounded-circle bg-info bg-opacity-10 border border-info d-inline-flex align-items-center justify-content-center text-info fw-bold" style="width:80px;height:80px;font-size:2rem;">' + (u.fname ? u.fname[0].toUpperCase() : 'U') + '</div>';
        }
        html += '<div class="mt-2 fw-bold text-white">' + u.fname + ' ' + (u.lname || '') + '</div>';
        html += '<div class="mt-1">' + roleLabel + '</div>';
        html += '</div>';
        html += '<div class="col-12 col-md-9">';
        html += '<div class="row g-2">';
        html += infoRow('Email', u.email || '—');
        html += infoRow('Phone', (u.country_code || '') + ' ' + (u.phone || '—'));
        html += infoRow('Status', '<span class="' + statusClass + ' fw-bold">' + (u.status || '—') + '</span>');
        html += infoRow('Date of Birth', u.dateofbirth || '—');
        html += infoRow('Address', u.address || '—');
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Editable fields overlay
        html += '<hr class="border-secondary opacity-25 my-3">';
        html += '<div class="row g-3">';
        html += '<div class="col-12"><small class="text-info fw-bold">You can edit the information below before submitting:</small></div>';
        html += '<div class="col-12 col-md-6"><label class="form-label">First Name</label><input type="text" name="edit_fname" class="form-control" value="' + (u.fname || '') + '"></div>';
        html += '<div class="col-12 col-md-6"><label class="form-label">Last Name</label><input type="text" name="edit_lname" class="form-control" value="' + (u.lname || '') + '"></div>';
        html += '<div class="col-12 col-md-6"><label class="form-label">Email</label><input type="email" name="edit_email" class="form-control" value="' + (u.email || '') + '"></div>';
        html += '<div class="col-12 col-md-6"><label class="form-label">Phone</label><input type="text" name="edit_phone" class="form-control" value="' + (u.phone || '') + '"></div>';
        html += '<div class="col-12"><label class="form-label">Address</label><textarea name="edit_address" class="form-control" rows="2">' + (u.address || '') + '</textarea></div>';
        html += '</div>';

        if (u.company) {
            html += '<hr class="border-info border-opacity-25 my-3">';
            html += '<div class="wz-section-title"><i class="bx bx-building me-1"></i>Company Details</div>';
            html += '<div class="row g-2">';
            html += infoRow('Company', u.company.company_name || '—');
            html += infoRow('CR Number', u.company.commercial_register || '—');
            html += infoRow('Civil ID', u.company.civil_id || '—');
            html += infoRow('Tax No.', u.company.tax_number || '—');
            html += infoRow('Representative', u.company.representative_name || '—');
            html += infoRow('Rep. Position', u.company.representative_position || '—');
            html += infoRow('Rep. Phone', u.company.representative_phone || '—');
            html += infoRow('Verification', '<span class="badge ' + (u.company.verification_status==='verified'?'bg-success':'bg-warning text-dark') + '">' + (u.company.verification_status||'pending') + '</span>');
            html += '</div>';
        }

        html += '</div>';
        $('#customerInfoContent').html(html);
    }

    function infoRow(label, value) {
        return '<div class="col-12 col-md-6"><div class="info-row"><div class="label">' + label + '</div><div class="value">' + value + '</div></div></div>';
    }

    // ── Toggle New User Panel ──
    $('#toggleNewUserBtn').on('click', function() {
        var visible = $('#newUserPanel').hasClass('visible');
        if (visible) {
            $('#newUserPanel').removeClass('visible');
            $(this).html('<i class="bx bx-user-plus fs-5"></i> <span>Add New Customer</span>');
        } else {
            $('#newUserPanel').addClass('visible');
            // Clear customer selection
            $('.customer-list-item').removeClass('selected');
            $('#customerInfoPanel').removeClass('visible');
            $('#selectedCustomerId').val('');
            $(this).html('<i class="bx bx-x fs-5"></i> <span>Cancel</span>');
        }
    });

    // Toggle company fields in new user panel
    $('#new_role').on('change', function() {
        if ($(this).val() === 'company_customer') {
            $('#newCompanyFields').slideDown(250);
        } else {
            $('#newCompanyFields').slideUp(250);
        }
    });

    // Save new user via AJAX then select them
    $('#saveNewUserBtn').on('click', function() {
        var fname = $('#new_fname').val().trim();
        var email = $('#new_email').val().trim();
        var pwd   = $('[name="new_password"]').val().trim();
        var role  = $('#new_role').val();
        if (!fname || !email || !pwd) {
            toastr.warning('First name, email, and password are required for the new customer.');
            return;
        }
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="wz-spinner"></span> Saving...');

        $.ajax({
            url: '{{ route("store.user") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                fname:    fname,
                lname:    $('[name="new_lname"]').val(),
                email:    email,
                password: pwd,
                role:     role,
                status:   'active',
                country_code: $('[name="new_country_code"]').val(),
                phone:    $('[name="new_phone"]').val(),
                address:  $('[name="new_address"]').val(),
                company_name:           $('[name="new_company_name"]').val(),
                commercial_register:    $('[name="new_commercial_register"]').val(),
                civil_id:               $('[name="new_civil_id"]').val(),
                tax_number:             $('[name="new_tax_number"]').val(),
                representative_name:    $('[name="new_representative_name"]').val(),
                representative_position:$('[name="new_representative_position"]').val(),
                representative_phone:   $('[name="new_representative_phone"]').val(),
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Create & Select Customer');
                if (res && res.user_id) {
                    // Add to list and select
                    $('#selectedCustomerId').val(res.user_id);
                    $('#newUserPanel').removeClass('visible');
                    $('#toggleNewUserBtn').html('<i class="bx bx-user-plus fs-5"></i> <span>Add New Customer</span>');
                    toastr.success('New customer created and selected!');
                    loadCustomerInfo(res.user_id);
                } else {
                    toastr.success('Customer saved. Please select them from the list after reload, or proceed.');
                    toastr.info('Note: Reload the page to see the new customer in the list.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Create & Select Customer');
                var msg = 'Failed to create customer.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errs = Object.values(xhr.responseJSON.errors);
                    msg = errs[0][0] || msg;
                }
                toastr.error(msg);
            }
        });
    });

    // ── Truck Type Selection ──
    window.selectTruckType = function(el) {
        $('.truck-type-card').removeClass('selected');
        $(el).addClass('selected');
        var id = $(el).data('id');
        $('#truck_type_id').val(id);
        $('#truck_sub_type_id').val('');
        loadSubTypes(id);
    };

    function loadSubTypes(truckTypeId) {
        $('#subTypeLoading').show();
        $('#truckSubTypeSelect').hide().empty().append('<option value="">-- Select Sub-Type --</option>');
        $('#subTypeContainer').addClass('visible');

        $.ajax({
            url: '{{ route("get.shipment.sub.types.ajax", ":id") }}'.replace(':id', truckTypeId),
            type: 'GET',
            success: function(data) {
                $('#subTypeLoading').hide();
                if (data && data.length > 0) {
                    $.each(data, function(i, st) {
                        $('#truckSubTypeSelect').append('<option value="' + st.id + '">' + st.name_en + '</option>');
                    });
                    $('#truckSubTypeSelect').show();
                } else {
                    $('#subTypeContainer').removeClass('visible');
                    $('#truck_sub_type_id').val('');
                }
            },
            error: function() {
                $('#subTypeLoading').hide();
                $('#subTypeContainer').removeClass('visible');
            }
        });
    }

    $('#truckSubTypeSelect').on('change', function() {
        $('#truck_sub_type_id').val($(this).val());
    });

    // ── Country → City (Pickup) ──
    $('#pickup_country_id').on('change', function() {
        var cid = $(this).val();
        var citySelect = $('#pickup_city_id');
        citySelect.prop('disabled', true).empty().append('<option value="">-- Select City --</option>');
        if (!cid) return;
        $('#pickupCityLoadingWrap').show();
        $.ajax({
            url: '{{ route("get.shipment.cities.ajax", ":id") }}'.replace(':id', cid),
            type: 'GET',
            success: function(data) {
                $('#pickupCityLoadingWrap').hide();
                citySelect.prop('disabled', false);
                $.each(data, function(i, c) {
                    citySelect.append('<option value="' + c.id + '">' + c.name_en + '</option>');
                });
            },
            error: function() { $('#pickupCityLoadingWrap').hide(); citySelect.prop('disabled', false); }
        });
    });

    // ── Country → City (Dropoff) ──
    $('#dropoff_country_id').on('change', function() {
        var cid = $(this).val();
        var citySelect = $('#dropoff_city_id');
        citySelect.prop('disabled', true).empty().append('<option value="">-- Select City --</option>');
        if (!cid) return;
        $('#dropoffCityLoadingWrap').show();
        $.ajax({
            url: '{{ route("get.shipment.cities.ajax", ":id") }}'.replace(':id', cid),
            type: 'GET',
            success: function(data) {
                $('#dropoffCityLoadingWrap').hide();
                citySelect.prop('disabled', false);
                $.each(data, function(i, c) {
                    citySelect.append('<option value="' + c.id + '">' + c.name_en + '</option>');
                });
            },
            error: function() { $('#dropoffCityLoadingWrap').hide(); citySelect.prop('disabled', false); }
        });
    });

    // ── Build Review (Step 8) ──
    function buildReview() {
        var html = '';

        // 1. Customer Information
        var selectedCustItem = $('.customer-list-item.selected');
        var custName = 'Not Selected';
        var custEmail = '—';
        var custId = $('#selectedCustomerId').val() || '—';

        if (selectedCustItem.length) {
            custName = selectedCustItem.data('name') || selectedCustItem.find('.name').text().trim();
            custEmail = selectedCustItem.data('email') || selectedCustItem.find('.sub').text().trim();
        } else if ($('#new_fname').val()) {
            custName = $('#new_fname').val() + ' ' + ($('#new_lname').val() || '');
            custEmail = $('#new_email').val() || '—';
            custId = 'New Account (Pending)';
        }

        html += reviewSection('bx bx-user-circle', 'Customer Information', [
            ['Customer Name', custName],
            ['Email Address', custEmail],
            ['Customer ID', custId]
        ]);

        // 2. Shipment Details
        var typeOpt = $('#shipment_type_id option:selected');
        var typeName = (typeOpt.val() && typeOpt.val() !== '') ? typeOpt.text().trim() : 'Not Specified';

        var natureOpt = $('#shipment_nature_id option:selected');
        var natureName = (natureOpt.val() && natureOpt.val() !== '') ? natureOpt.text().trim() : 'Not Specified';

        var l = $('#length').val();
        var w = $('#width').val();
        var h = $('#height').val();
        var dimStr = (l || w || h) ? ((l || '0') + ' × ' + (w || '0') + ' × ' + (h || '0') + ' cm') : 'Not Specified';

        var weightVal = $('#weight').val();
        var weightStr = weightVal ? (parseFloat(weightVal).toFixed(2) + ' kg') : 'Not Specified';

        html += reviewSection('bx bx-package', 'Shipment & Cargo Details', [
            ['Shipment Name', $('#shipment_name').val() || 'General Freight'],
            ['Description', $('#shipment_description').val() || '—'],
            ['Shipment Type', typeName],
            ['Shipment Nature', natureName],
            ['Dimensions (L × W × H)', dimStr],
            ['Weight', weightStr],
            ['Packages / Pieces', $('#packages_count').val() || '1'],
            ['Cargo / Goods Notes', $('#goods_description').val() || '—']
        ]);

        // 3. Truck Requirements
        var selectedTruckCard = $('.truck-type-card.selected');
        var truckName = selectedTruckCard.length ? selectedTruckCard.data('name') : (selectedTruckCard.find('.truck-name').text().trim() || 'Not Selected');
        
        var subTypeOpt = $('#truckSubTypeSelect option:selected');
        var subName = (subTypeOpt.length && subTypeOpt.val() && subTypeOpt.val() !== '') ? subTypeOpt.text().trim() : 'Standard / None';

        html += reviewSection('bx bxs-truck', 'Truck & Fleet Requirements', [
            ['Truck Type', truckName],
            ['Sub-Type Variant', subName]
        ]);

        // 4. Pickup Location
        var pCountryOpt = $('#pickup_country_id option:selected');
        var pCountry = (pCountryOpt.val() && pCountryOpt.val() !== '') ? pCountryOpt.text().trim() : 'Not Specified';

        var pCityOpt = $('#pickup_city_id option:selected');
        var pCity = (pCityOpt.val() && pCityOpt.val() !== '') ? pCityOpt.text().trim() : 'Not Specified';

        var pLat = $('#pickup_lat').val();
        var pLng = $('#pickup_lng').val();
        var pCoords = (pLat && pLng) ? (pLat + ', ' + pLng) : 'Not Pinned';

        html += reviewSection('bx bx-map-pin', 'Pickup Location (Origin)', [
            ['Country', pCountry],
            ['City', pCity],
            ['District / Area', $('#pickup_area').val() || '—'],
            ['Full Address', $('#pickup_address').val() || 'Not Specified'],
            ['GPS Coordinates', pCoords]
        ]);

        // 5. Delivery Location
        var dCountryOpt = $('#dropoff_country_id option:selected');
        var dCountry = (dCountryOpt.val() && dCountryOpt.val() !== '') ? dCountryOpt.text().trim() : 'Not Specified';

        var dCityOpt = $('#dropoff_city_id option:selected');
        var dCity = (dCityOpt.val() && dCityOpt.val() !== '') ? dCityOpt.text().trim() : 'Not Specified';

        var dLat = $('#dropoff_lat').val();
        var dLng = $('#dropoff_lng').val();
        var dCoords = (dLat && dLng) ? (dLat + ', ' + dLng) : 'Not Pinned';

        html += reviewSection('bx bx-navigation', 'Delivery Destination (Dropoff)', [
            ['Country', dCountry],
            ['City', dCity],
            ['District / Area', $('#dropoff_area').val() || '—'],
            ['Full Address', $('#dropoff_address').val() || 'Not Specified'],
            ['GPS Coordinates', dCoords]
        ]);

        // 6. Contact Persons
        var loadName = $('#loading_contact_name').val();
        var loadPhone = $('#loading_contact_phone').val();
        var loadStr = (loadName || loadPhone) ? ((loadName || '—') + (loadPhone ? ' (' + loadPhone + ')' : '')) : 'Not Specified';

        var arrName = $('#arrival_contact_name').val();
        var arrPhone = $('#arrival_contact_phone').val();
        var arrStr = (arrName || arrPhone) ? ((arrName || '—') + (arrPhone ? ' (' + arrPhone + ')' : '')) : 'Not Specified';

        html += reviewSection('bx bx-phone', 'Contact Persons', [
            ['Loading Contact (Pickup)', loadStr],
            ['Receiving Contact (Delivery)', arrStr]
        ]);

        // 7. Pricing & Order Status
        var priceVal = $('#initial_price').val();
        var priceStr = priceVal ? ('KWD ' + parseFloat(priceVal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})) : 'KWD 0.00';

        html += reviewSection('bx bx-dollar-circle', 'Pricing & Order Summary', [
            ['Initial Price Estimate', priceStr],
            ['Order Status', 'New (Pending Dispatch)'],
            ['Payment Status', 'Unpaid (Invoice Auto-Generated)']
        ]);

        $('#reviewContent').html(html);
    }

    function reviewSection(icon, title, rows) {
        var html = '<div class="review-section">';
        html += '<div class="review-header"><i class="' + icon + '"></i><h6>' + title + '</h6></div>';
        html += '<div class="row g-2">';
        rows.forEach(function(r) {
            html += '<div class="col-12 col-md-6"><div class="review-item"><div class="r-label">' + r[0] + '</div><div class="r-value">' + r[1] + '</div></div></div>';
        });
        html += '</div></div>';
        return html;
    }

    // ── Form Submit Validation & Guard ──
    $('#wizardForm').on('submit', function(e) {
        var customerId = $('#selectedCustomerId').val();
        var pickupAddr = $('#pickup_address').val();
        var dropoffAddr = $('#dropoff_address').val();

        // 1. If user filled new customer form but didn't click Create button
        if (!customerId && $('#new_fname').val()) {
            e.preventDefault();
            toastr.info('Creating new customer account before submitting order...');
            $('#btnCreateNewUser').click();
            return false;
        }

        // 2. Ensure a customer is selected
        if (!customerId) {
            e.preventDefault();
            toastr.error('Please select a customer in Step 1 before submitting the order.');
            goToStep(1);
            return false;
        }

        // 3. Fallback for Pickup Address if blank
        if (!pickupAddr) {
            var pLat = $('#pickup_lat').val();
            var pLng = $('#pickup_lng').val();
            var pArea = $('#pickup_area').val();
            if (pLat && pLng) {
                $('#pickup_address').val('Map Pin (' + pLat + ', ' + pLng + ')');
            } else if (pArea) {
                $('#pickup_address').val(pArea);
            } else {
                $('#pickup_address').val('Pickup Location Pin');
            }
        }

        // 4. Fallback for Dropoff Address if blank
        if (!dropoffAddr) {
            var dLat = $('#dropoff_lat').val();
            var dLng = $('#dropoff_lng').val();
            var dArea = $('#dropoff_area').val();
            if (dLat && dLng) {
                $('#dropoff_address').val('Map Pin (' + dLat + ', ' + dLng + ')');
            } else if (dArea) {
                $('#dropoff_address').val(dArea);
            } else {
                $('#dropoff_address').val('Delivery Location Pin');
            }
        }

        $('#submitOrderBtn').prop('disabled', true).html('<span class="wz-spinner"></span> Submitting Order...');
    });

    // ── HS Code Lookup Handler ──
    $('#btnLookupHsCode').on('click', function() {
        var code = $('#hs_code').val().trim();
        if (!code) {
            $('#hsCodeResultPanel').hide();
            $('#hs_code_description_input').val('');
            return;
        }

        $('#hsCodeResultPanel').show();
        $('#hsCodeLoader').show();
        $('#hsCodeDetailsContent').empty();

        $.ajax({
            url: '{{ route("hscode.lookup.ajax") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                hs_code: code
            },
            success: function(res) {
                $('#hsCodeLoader').hide();
                if (res.status === 'success' && res.data) {
                    var d = res.data;
                    var desc = d.description + (d.description_ar ? ' | ' + d.description_ar : '');
                    $('#hs_code_description_input').val(desc);

                    var html = '';

                    // Primary Header & Verification Badge
                    html += '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 mb-3 border-bottom border-secondary border-opacity-25">';
                    html += '<div>';
                    html += '<span class="badge bg-info text-dark fw-bold px-3 py-1.5 rounded-pill font-monospace fs-6 me-2">HS Code: ' + d.code + '</span>';
                    html += '<span class="badge res-verified-badge bg-success bg-opacity-20 text-success border border-success border-opacity-40 px-3 py-1.5 rounded-pill font-monospace fs-7"><i class="bx bx-check-shield me-1"></i> Verified Customs Tariff Record</span>';
                    html += '</div>';
                    html += '</div>';

                    // Item Description En & Ar
                    html += '<h5 class="fw-bold text-white mb-1">' + d.description + '</h5>';
                    if (d.description_ar) {
                        html += '<h6 class="fw-bold text-info mb-3" style="font-family: system-ui, sans-serif;">' + d.description_ar + '</h6>';
                    }

                    // Tariff Metrics Grid
                    html += '<div class="row g-2 mb-3">';

                    html += '<div class="col-6 col-md-3"><div class="hs-metric-card">';
                    html += '<small class="text-slate-400 d-block fs-8 mb-0.5"><i class="bx bx-dollar-circle me-1"></i> Duty Rate (الجمارك)</small>';
                    html += '<span class="fs-5 fw-bold text-warning">' + (d.duty_rate || '5.0%') + '</span>';
                    html += '</div></div>';

                    html += '<div class="col-6 col-md-3"><div class="hs-metric-card">';
                    html += '<small class="text-slate-400 d-block fs-8 mb-0.5"><i class="bx bx-receipt me-1"></i> VAT Rate (الضريبة)</small>';
                    html += '<span class="fs-5 fw-bold text-info">' + (d.vat_rate || '15.0%') + '</span>';
                    html += '</div></div>';

                    html += '<div class="col-6 col-md-3"><div class="hs-metric-card">';
                    html += '<small class="text-slate-400 d-block fs-8 mb-0.5"><i class="bx bx-category me-1"></i> Category</small>';
                    html += '<span class="fs-7 fw-bold text-white text-truncate d-block" title="' + (d.category || 'General Cargo') + '">' + (d.category || 'General Cargo') + '</span>';
                    html += '</div></div>';

                    html += '<div class="col-6 col-md-3"><div class="hs-metric-card">';
                    html += '<small class="text-slate-400 d-block fs-8 mb-0.5"><i class="bx bx-layer me-1"></i> Section</small>';
                    html += '<span class="fs-7 fw-bold text-white text-truncate d-block" title="' + (d.section || 'Harmonized Section') + '">' + (d.section || 'Harmonized Section') + '</span>';
                    html += '</div></div>';

                    html += '</div>';

                    // Import Restrictions & Compliance
                    if (d.restrictions && d.restrictions.length > 0) {
                        html += '<div class="hs-restrictions-card mb-3">';
                        html += '<h6 class="fw-bold text-warning mb-2 fs-7 d-flex align-items-center gap-1.5"><i class="bx bx-shield-quarter fs-5"></i> Customs Clearance & Import Restrictions (شروط وتصاريح الاستيراد):</h6>';
                        html += '<ul class="mb-0 ps-3 text-slate-200 fs-8">';
                        d.restrictions.forEach(function(r) {
                            var textEn = typeof r === 'object' ? (r.en || '') : r;
                            var textAr = typeof r === 'object' && r.ar ? ' (' + r.ar + ')' : '';
                            html += '<li class="mb-1">' + textEn + textAr + '</li>';
                        });
                        html += '</ul>';
                        html += '</div>';
                    }

                    // Advanced Freight Safety Analysis Engine
                    if (d.analysis) {
                        var a = d.analysis;
                        var riskBg = a.risk_level === 'HIGH' ? 'bg-danger' : (a.risk_level === 'MEDIUM' ? 'bg-warning text-dark' : 'bg-success');
                        
                        html += '<div class="hs-analysis-card">';
                        html += '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">';
                        html += '<h6 class="fw-bold text-white mb-0 fs-7 d-flex align-items-center gap-1.5"><i class="bx bx-analyse text-info fs-5"></i> Advanced Freight & Safety Analysis (محرك تحليل خصائص السلامة والمخاطر):</h6>';
                        html += '<span class="badge ' + riskBg + ' fw-bold px-2.5 py-1 fs-8">Risk: ' + (a.risk_level || 'LOW') + '</span>';
                        html += '</div>';

                        if (a.flags) {
                            html += '<div class="d-flex flex-wrap gap-1.5 mb-2.5">';
                            if (a.flags.is_fragile) html += '<span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 fs-9"><i class="bx bx-wine me-1"></i>Fragile / قابل للكسر</span>';
                            if (a.flags.is_hazardous) html += '<span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30 fs-9"><i class="bx bx-error-circle me-1"></i>Hazardous / مواد خطرة</span>';
                            if (a.flags.requires_cold_chain || a.flags.is_temperature_controlled) html += '<span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 fs-9"><i class="bx bx-snowflake me-1"></i>Cold Chain / تبريد</span>';
                            if (a.flags.is_perishable) html += '<span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 fs-9"><i class="bx bx-food-tag me-1"></i>Perishable Cargo / مواد غذائية</span>';
                            if (a.flags.is_high_value) html += '<span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30 fs-9"><i class="bx bx-diamond me-1"></i>High-Value Cargo / عالية القيمة</span>';
                            if (a.flags.is_liquid_or_gas) html += '<span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 fs-9"><i class="bx bx-droplet me-1"></i>Liquid or Gas / سوائل و غازات</span>';
                            html += '</div>';
                        }

                        if (a.handling_instructions && a.handling_instructions.length > 0) {
                            html += '<div class="text-slate-300 fs-8"><strong class="text-info"><i class="bx bx-box me-1"></i>Transport Recommendations (تعليمات المناولة والنقل):</strong></div>';
                            html += '<ul class="mb-0 ps-3 text-slate-300 fs-8 mt-1">';
                            a.handling_instructions.forEach(function(h) {
                                var textEn = typeof h === 'object' ? (h.en || '') : h;
                                var textAr = typeof h === 'object' && h.ar ? ' <span class="text-info">(' + h.ar + ')</span>' : '';
                                html += '<li class="mb-1"><strong>' + textEn + '</strong>' + textAr + '</li>';
                            });
                            html += '</ul>';
                        }
                        html += '</div>';
                    }

                    $('#hsCodeDetailsContent').html(html);
                }
            },
            error: function(xhr) {
                $('#hsCodeLoader').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'HS Code not found in customs tariff catalog.';
                $('#hsCodeDetailsContent').html('<div class="text-warning fs-8"><i class="bx bx-info-circle me-1"></i> ' + msg + '</div>');
            }
        });
    });

    $('#hs_code').on('change', function() {
        if ($(this).val().trim().length >= 3) {
            $('#btnLookupHsCode').click();
        }
    });

});
</script>

@endsection
