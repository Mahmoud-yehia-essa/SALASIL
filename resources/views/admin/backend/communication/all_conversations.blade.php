@extends('admin.master_admin')
@section('admin')

{{-- =========================================================================
     LIVE COMMUNICATIONS & DIRECT MESSAGING HUB — SALASIL CPANEL
     Architecture: Interactive Map Picker & Sleek Geolocation Chat Cards
========================================================================= --}}

<style>
:root {
    --hub-bg:           #090D16;
    --hub-card-bg:     rgba(15, 23, 42, 0.75);
    --hub-panel-bg:    rgba(30, 41, 59, 0.85);
    --hub-border:       rgba(255, 255, 255, 0.08);
    --hub-border-hover: rgba(6, 182, 212, 0.35);
    --hub-cyan:         #06B6D4;
    --hub-cyan-light:   #38BDF8;
    --hub-teal:         #14B8A6;
    --hub-indigo:       #6366F1;
    --hub-purple:       #8B5CF6;
    --hub-emerald:      #10B981;
    --hub-amber:        #F59E0B;
    --hub-rose:         #F43F5E;
    
    --bubble-me:        linear-gradient(135deg, #0284C7 0%, #06B6D4 100%);
    --bubble-other:     rgba(255, 255, 255, 0.06);
}

/* ─── Compact KPI Cards ─── */
.kpi-card-hub {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
    border: 1px solid var(--hub-border);
    border-radius: 14px;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: all 0.25s ease;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}
.kpi-card-hub:hover {
    transform: translateY(-2px);
    border-color: var(--hub-border-hover);
    box-shadow: 0 10px 25px rgba(6, 182, 212, 0.15);
}
.kpi-icon-wrap {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #ffffff;
    flex-shrink: 0;
}
.kpi-icon-wrap.total   { background: linear-gradient(135deg, #06B6D4, #38BDF8); box-shadow: 0 6px 14px rgba(6, 182, 212, 0.3); }
.kpi-icon-wrap.open    { background: linear-gradient(135deg, #10B981, #34D399); box-shadow: 0 6px 14px rgba(16, 185, 129, 0.3); }
.kpi-icon-wrap.support { background: linear-gradient(135deg, #8B5CF6, #A78BFA); box-shadow: 0 6px 14px rgba(139, 92, 246, 0.3); }
.kpi-icon-wrap.unread  { background: linear-gradient(135deg, #F43F5E, #FB7185); box-shadow: 0 6px 14px rgba(244, 63, 94, 0.3); }

.kpi-title-hub { font-size: 0.72rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.6px; }
.kpi-number-hub { font-size: 1.35rem; font-weight: 800; color: #F8FAFC; margin-top: 1px; line-height: 1.1; }

/* ─── Main Workspace Container ─── */
.hub-workspace {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
    border: 1px solid var(--hub-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
    height: calc(100vh - 245px);
    min-height: 520px;
    max-height: 720px;
    display: flex;
}

/* ─── Directory Sidebar (Left) ─── */
.hub-sidebar {
    width: 360px;
    min-width: 320px;
    border-right: 1px solid var(--hub-border);
    display: flex;
    flex-direction: column;
    background: rgba(15, 23, 42, 0.65);
    height: 100%;
}
.hub-sidebar-header {
    padding: 16px;
    border-bottom: 1px solid var(--hub-border);
    background: rgba(30, 41, 59, 0.4);
    flex-shrink: 0;
}
.hub-search-box {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #F8FAFC !important;
    border-radius: 12px !important;
    padding: 8px 14px 8px 38px !important;
    font-size: 0.85rem !important;
    transition: all 0.2s ease;
}
.hub-search-box:focus {
    border-color: var(--hub-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.18) !important;
    background: rgba(255, 255, 255, 0.08) !important;
}

.hub-filter-scroll {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding: 10px 0 2px 0;
}
.hub-filter-pill {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    color: #94A3B8;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.2s ease;
}
.hub-filter-pill:hover, .hub-filter-pill.active {
    background: linear-gradient(135deg, #0284C7, #06B6D4);
    color: #FFFFFF;
    border-color: var(--hub-cyan);
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
}

.hub-conv-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}
.conv-card-item {
    padding: 12px 14px;
    border-radius: 14px;
    margin-bottom: 6px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.02);
}
.conv-card-item:hover {
    background: rgba(255, 255, 255, 0.06);
}
.conv-card-item.active {
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.18) 0%, rgba(2, 132, 199, 0.1) 100%);
    border-color: rgba(6, 182, 212, 0.4);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}

.avatar-wrapper-hub {
    position: relative;
    width: 44px; height: 44px;
    flex-shrink: 0;
}
.avatar-img-hub {
    width: 44px; height: 44px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.1);
}
.live-status-dot {
    position: absolute;
    bottom: -2px; right: -2px;
    width: 12px; height: 12px;
    border-radius: 50%;
    border: 2px solid #0F172A;
}
.live-status-dot.open { background: var(--hub-emerald); box-shadow: 0 0 6px var(--hub-emerald); }
.live-status-dot.closed { background: #64748B; }

.conv-info-hub { flex: 1; min-width: 0; }
.conv-user-name { font-size: 0.88rem; font-weight: 700; color: #F8FAFC; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-snippet { font-size: 0.78rem; color: #94A3B8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.badge-channel {
    font-size: 0.62rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 5px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.badge-channel.in_app { background: rgba(6, 182, 212, 0.16); color: #38BDF8; border: 1px solid rgba(6, 182, 212, 0.35); }
.badge-channel.whatsapp { background: rgba(16, 185, 129, 0.16); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.35); }
.badge-channel.sms { background: rgba(139, 92, 246, 0.16); color: #A78BFA; border: 1px solid rgba(139, 92, 246, 0.35); }

.badge-type {
    font-size: 0.62rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 5px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.badge-type.support { background: rgba(245, 158, 11, 0.16); color: #FCD34D; border: 1px solid rgba(245, 158, 11, 0.35); }
.badge-type.direct { background: rgba(99, 102, 241, 0.16); color: #818CF8; border: 1px solid rgba(99, 102, 241, 0.35); }

/* ─── Active Chat Workspace Stage (Right) ─── */
.hub-main-stage {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    background: rgba(15, 23, 42, 0.4);
    height: 100%;
    overflow: hidden;
}

.stage-header {
    padding: 14px 22px;
    border-bottom: 1px solid var(--hub-border);
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(12px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    z-index: 5;
}
.stage-user-info { display: flex; align-items: center; gap: 14px; min-width: 0; }
.stage-avatar { width: 42px; height: 42px; border-radius: 12px; object-fit: cover; border: 2px solid rgba(255,255,255,0.15); flex-shrink: 0; }
.stage-title { font-size: 1.02rem; font-weight: 800; color: #F8FAFC; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stage-sub { font-size: 0.78rem; color: #94A3B8; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

.stage-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

/* ─── Messages Stream (Scrollable) ─── */
.stage-messages-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.chat-msg-wrapper {
    display: flex;
    flex-direction: column;
    max-width: 78%;
    width: auto;
}
.chat-msg-wrapper.me { align-self: flex-end; align-items: flex-end; }
.chat-msg-wrapper.other { align-self: flex-start; align-items: flex-start; }

.msg-sender-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #94A3B8;
    margin-bottom: 4px;
    padding: 0 4px;
}
.msg-bubble-box {
    padding: 12px 18px;
    border-radius: 16px;
    font-size: 0.9rem;
    line-height: 1.5;
    position: relative;
    word-break: break-word;
    overflow-wrap: anywhere;
    box-shadow: 0 4px 14px rgba(0,0,0,0.2);
    max-width: 100%;
}
.chat-msg-wrapper.me .msg-bubble-box {
    background: var(--bubble-me);
    color: #FFFFFF;
    border-bottom-right-radius: 4px;
}
.chat-msg-wrapper.other .msg-bubble-box {
    background: var(--bubble-other);
    color: #F8FAFC;
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-bottom-left-radius: 4px;
}

.msg-footer-info {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.68rem;
    color: rgba(255,255,255,0.65);
    margin-top: 4px;
    padding: 0 4px;
}
.chat-msg-wrapper.other .msg-footer-info { color: #64748B; }

.reply-quote-box {
    background: rgba(0, 0, 0, 0.25);
    border-left: 3px solid var(--hub-cyan);
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 0.78rem;
    margin-bottom: 8px;
}

/* Media Attachments */
.media-img-preview {
    max-width: 280px;
    max-height: 220px;
    border-radius: 12px;
    margin-top: 6px;
    cursor: pointer;
    border: 1px solid rgba(255,255,255,0.12);
    transition: transform 0.2s ease;
    display: block;
}
.media-img-preview:hover { transform: scale(1.02); }

.media-file-box {
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 6px;
    text-decoration: none;
    color: #38BDF8;
    transition: all 0.2s ease;
}
.media-file-box:hover { color: #7DD3FC; background: rgba(0, 0, 0, 0.45); border-color: var(--hub-cyan); }

/* ─── Location Card Styling ─── */
.chat-location-card {
    background: rgba(15, 23, 42, 0.75);
    border: 1px solid rgba(6, 182, 212, 0.35);
    border-radius: 14px;
    padding: 12px;
    margin-top: 4px;
    width: 100%;
    max-width: 310px;
    box-sizing: border-box;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.location-icon-badge {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #F43F5E, #FB7185);
    display: flex; align-items: center; justify-content: center;
    color: #ffffff; font-size: 18px;
    box-shadow: 0 4px 12px rgba(244, 63, 94, 0.35);
    flex-shrink: 0;
}

.location-card-text {
    flex: 1 1 auto;
    min-width: 0;
    overflow: hidden;
}

.location-title-text {
    font-size: 0.82rem;
    font-weight: 700;
    color: #FFFFFF;
    line-height: 1.35;
    word-break: break-word;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.location-gps-text {
    font-size: 0.72rem;
    color: #38BDF8;
    font-weight: 600;
    margin-top: 3px;
    font-family: monospace;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.location-preview-box {
    position: relative;
    width: 100%;
    height: 135px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.12);
    cursor: pointer;
    background: #0F172A;
}

.location-preview-iframe {
    width: 100%;
    height: 100%;
    border: none;
    pointer-events: none;
    filter: contrast(1.05) saturate(1.1);
}

.location-overlay-badge {
    position: absolute;
    bottom: 6px; right: 6px;
    padding: 3px 10px;
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #F8FAFC;
    font-size: 0.7rem;
    font-weight: 700;
    border-radius: 6px;
    backdrop-filter: blur(4px);
    display: flex; align-items: center; gap: 4px;
}

.btn-location-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 8px 12px;
    background: rgba(6, 182, 212, 0.15);
    border: 1px solid rgba(6, 182, 212, 0.4);
    color: #38BDF8;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
}
.btn-location-action:hover {
    background: linear-gradient(135deg, #0284C7, #06B6D4);
    color: #FFFFFF;
    border-color: #06B6D4;
    box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35);
}

/* ─── Fix Google Maps Autocomplete Dropdown in Bootstrap Modals ─── */
.pac-container {
    z-index: 999999 !important;
    background-color: #0F172A !important;
    border: 1px solid rgba(6, 182, 212, 0.4) !important;
    border-radius: 12px !important;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.7) !important;
    font-family: inherit !important;
    margin-top: 4px !important;
}
.pac-item {
    color: #F8FAFC !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    padding: 10px 14px !important;
    cursor: pointer !important;
    font-size: 0.88rem !important;
}
.pac-item:hover, .pac-item-selected {
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.25), rgba(2, 132, 199, 0.18)) !important;
    color: #FFFFFF !important;
}
.pac-item-query {
    color: #38BDF8 !important;
    font-weight: 700 !important;
}
.pac-matched {
    color: #06B6D4 !important;
}
.pac-icon {
    filter: invert(1);
}

/* ─── Footer Input Controls ─── */
.stage-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--hub-border);
    background: rgba(15, 23, 42, 0.95);
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-shrink: 0;
    z-index: 10;
}
.input-controls-row { display: flex; align-items: center; gap: 10px; }
.chat-actions-bar { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.chat-input-send-row { display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; gap: 8px; flex: 1; min-width: 0; }

.chat-input-textarea {
    flex: 1;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #F8FAFC !important;
    border-radius: 14px !important;
    padding: 10px 16px !important;
    font-size: 0.9rem !important;
    resize: none;
    max-height: 90px;
}
.chat-input-textarea:focus {
    border-color: var(--hub-cyan) !important;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.18) !important;
}

.btn-send-gradient {
    width: 44px; height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, #0284C7, #06B6D4);
    border: none;
    color: #ffffff;
    font-size: 20px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 6px 18px rgba(6, 182, 212, 0.35);
    flex-shrink: 0;
}
.btn-send-gradient:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 24px rgba(6, 182, 212, 0.45);
}

/* ─── WhatsApp Voice Note Recording Overlay ─── */
.voice-record-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.96);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    z-index: 20;
    border: 1px solid rgba(244, 63, 94, 0.35);
}

.rec-pulse-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #F43F5E;
    box-shadow: 0 0 12px #F43F5E;
    animation: recPulse 1s infinite alternate;
}
@keyframes recPulse {
    0% { transform: scale(0.85); opacity: 0.5; }
    100% { transform: scale(1.25); opacity: 1; }
}

.rec-timer-label {
    font-size: 0.9rem;
    font-weight: 800;
    color: #F8FAFC;
    font-family: monospace;
}

.rec-waveform-canvas {
    height: 34px;
    width: 160px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.2);
}

.btn-mic-record {
    width: 44px; height: 44px;
    border-radius: 14px;
    background: rgba(244, 63, 94, 0.15);
    border: 1px solid rgba(244, 63, 94, 0.35);
    color: #FB7185;
    font-size: 22px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.btn-mic-record:hover {
    background: linear-gradient(135deg, #E11D48, #F43F5E);
    color: #FFFFFF;
    box-shadow: 0 6px 18px rgba(244, 63, 94, 0.4);
    transform: scale(1.05);
}

/* ─── WhatsApp Voice Note Bubble Player ─── */
.chat-voicenote-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 14px;
    background: rgba(15, 23, 42, 0.65);
    border: 1px solid rgba(6, 182, 212, 0.3);
    min-width: 250px;
    max-width: 320px;
    margin-top: 4px;
}

.vn-avatar-badge {
    width: 38px; height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0284C7, #06B6D4);
    display: flex; align-items: center; justify-content: center;
    color: #FFFFFF; font-size: 18px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.btn-play-vn {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: #06B6D4;
    border: none;
    color: #FFFFFF;
    font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: transform 0.15s ease;
}
.btn-play-vn:hover { transform: scale(1.1); }

.vn-track-wrap {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.vn-seek-bar {
    width: 100%;
    accent-color: #06B6D4;
    height: 4px;
    cursor: pointer;
}

.vn-time-label {
    font-size: 0.7rem;
    color: #94A3B8;
    font-family: monospace;
    display: flex;
    justify-content: space-between;
}

.reply-bar-preview {
    background: rgba(6, 182, 212, 0.15);
    border: 1px solid rgba(6, 182, 212, 0.35);
    border-radius: 8px;
    padding: 6px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
    color: #38BDF8;
}

.reverb-status-tag {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
    background: rgba(16, 185, 129, 0.15);
    color: #34D399;
    border: 1px solid rgba(16, 185, 129, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Custom Scrollbars */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR COMMUNICATIONS & MESSAGING HUB
═════════════════════════════════════════════════════════════ */
html.light-theme .kpi-card-hub {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 6px 20px rgba(0,0,0,0.04) !important;
}
html.light-theme .kpi-title-hub {
    color: #64748B !important;
}
html.light-theme .kpi-number-hub {
    color: #0F172A !important;
}

html.light-theme .hub-workspace {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
}

html.light-theme .hub-sidebar {
    background: #F8FAFC !important;
    border-right-color: #E2E8F0 !important;
}
html.light-theme .hub-sidebar-header {
    background: #F1F5F9 !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .hub-search-box {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .hub-search-box:focus {
    background: #FFFFFF !important;
    border-color: #0284C7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.15) !important;
}
html.light-theme .hub-filter-pill {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #475569 !important;
}
html.light-theme .hub-filter-pill:hover,
html.light-theme .hub-filter-pill.active {
    background: linear-gradient(135deg, #0284C7, #06B6D4) !important;
    color: #FFFFFF !important;
    border-color: #0284C7 !important;
}

html.light-theme .conv-card-item {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .conv-card-item:hover {
    background: #F1F5F9 !important;
}
html.light-theme .conv-card-item.active {
    background: linear-gradient(135deg, rgba(2,132,199,0.12) 0%, rgba(6,182,212,0.06) 100%) !important;
    border-color: #0284C7 !important;
}
html.light-theme .conv-user-name {
    color: #0F172A !important;
}
html.light-theme .conv-snippet {
    color: #64748B !important;
}
html.light-theme .live-status-dot {
    border-color: #FFFFFF !important;
}

html.light-theme .hub-main-stage {
    background: #FFFFFF !important;
}
html.light-theme .stage-header {
    background: #F8FAFC !important;
    border-bottom-color: #E2E8F0 !important;
}
html.light-theme .stage-title {
    color: #0F172A !important;
}
html.light-theme .stage-sub {
    color: #64748B !important;
}

html.light-theme .msg-sender-label {
    color: #64748B !important;
}
html.light-theme .chat-msg-wrapper.other .msg-bubble-box {
    background: #F1F5F9 !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
}
html.light-theme .chat-msg-wrapper.other .msg-footer-info {
    color: #64748B !important;
}
html.light-theme .reply-quote-box {
    background: rgba(0,0,0,0.05) !important;
    border-left-color: #0284C7 !important;
    color: #334155 !important;
}

html.light-theme .chat-location-card {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05) !important;
}
html.light-theme .location-title-text {
    color: #0F172A !important;
}
html.light-theme .location-gps-text {
    color: #0284C7 !important;
}
html.light-theme .location-preview-box {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .location-overlay-badge {
    background: rgba(255,255,255,0.9) !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}

html.light-theme .media-file-box {
    background: #F1F5F9 !important;
    border-color: #CBD5E1 !important;
    color: #0284C7 !important;
}
html.light-theme .media-file-box:hover {
    background: #E0F2FE !important;
    border-color: #0284C7 !important;
}

html.light-theme .chat-voicenote-card {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .vn-time-label {
    color: #64748B !important;
}

html.light-theme .stage-footer {
    background: #F8FAFC !important;
    border-top-color: #E2E8F0 !important;
}
html.light-theme .chat-input-textarea {
    background: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .chat-input-textarea:focus {
    background: #FFFFFF !important;
    border-color: #0284C7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.15) !important;
}

html.light-theme .pac-container {
    background-color: #FFFFFF !important;
    border-color: #CBD5E1 !important;
    box-shadow: 0 12px 35px rgba(0,0,0,0.15) !important;
}
html.light-theme .pac-item {
    color: #0F172A !important;
    border-top-color: #E2E8F0 !important;
}
html.light-theme .pac-item:hover,
html.light-theme .pac-item-selected {
    background: #F1F5F9 !important;
    color: #0284C7 !important;
}
html.light-theme .pac-item-query {
    color: #0284C7 !important;
}
html.light-theme .pac-matched {
    color: #0369A1 !important;
}
html.light-theme .pac-icon {
    filter: none !important;
}

/* ─── Mobile View Responsive Optimizations for Live Communications Desk ─── */
@media (max-width: 991.98px) {
    .hub-workspace {
        height: calc(100vh - 180px) !important;
        min-height: 480px !important;
        border-radius: 14px !important;
        position: relative !important;
    }

    .hub-sidebar {
        width: 100% !important;
        min-width: 100% !important;
        border-right: none !important;
        display: flex !important;
    }
    .hub-main-stage {
        display: none !important;
        width: 100% !important;
    }

    .hub-workspace.mobile-chat-active .hub-sidebar {
        display: none !important;
    }
    .hub-workspace.mobile-chat-active .hub-main-stage {
        display: flex !important;
        width: 100% !important;
    }

    .stage-header {
        padding: 10px 12px !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }
    .stage-user-info {
        gap: 8px !important;
        max-width: 100% !important;
    }
    .stage-avatar {
        width: 36px !important;
        height: 36px !important;
    }
    .stage-title {
        font-size: 0.92rem !important;
    }
    .stage-sub {
        font-size: 0.72rem !important;
    }
    .stage-actions {
        gap: 4px !important;
        width: 100% !important;
        justify-content: flex-end !important;
        margin-top: 2px !important;
    }
    .stage-actions select {
        width: 95px !important;
        font-size: 0.75rem !important;
        padding: 4px 6px !important;
    }
    .stage-actions button {
        padding: 4px 8px !important;
        font-size: 0.75rem !important;
    }

    .stage-messages-body {
        padding: 12px 10px !important;
        gap: 10px !important;
    }
    .chat-msg-wrapper {
        max-width: 88% !important;
    }
    .msg-bubble-box {
        padding: 10px 14px !important;
        font-size: 0.85rem !important;
    }
    .chat-location-card,
    .chat-voicenote-card {
        max-width: 100% !important;
    }
    .media-img-preview {
        max-width: 100% !important;
        max-height: 180px !important;
    }

    .stage-footer {
        padding: 10px 12px !important;
    }
    .input-controls-row {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
    }
    .chat-actions-bar {
        width: 100% !important;
        justify-content: flex-start !important;
        gap: 6px !important;
    }
    .chat-input-send-row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        width: 100% !important;
        gap: 8px !important;
    }
    .chat-input-textarea {
        flex: 1 1 0% !important;
        width: 0 !important;
        min-width: 0 !important;
        padding: 8px 12px !important;
        font-size: 0.85rem !important;
    }
    .btn-send-gradient {
        flex: 0 0 auto !important;
        flex-shrink: 0 !important;
    }
    .btn-send-gradient,
    .btn-mic-record {
        width: 38px !important;
        height: 38px !important;
        font-size: 17px !important;
        border-radius: 10px !important;
    }
    .voice-record-overlay {
        padding: 0 10px !important;
    }
    .rec-waveform-canvas {
        width: 90px !important;
    }
}

@media (max-width: 767.98px) {
    .page-content {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
    .kpi-card-hub {
        padding: 10px 12px !important;
        gap: 10px !important;
    }
    .kpi-icon-wrap {
        width: 36px !important;
        height: 36px !important;
        font-size: 17px !important;
    }
    .kpi-title-hub {
        font-size: 0.68rem !important;
    }
    .kpi-number-hub {
        font-size: 1.12rem !important;
    }
}
</style>

<div class="page-content" style="padding-bottom: 10px;">

    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="mb-1 text-white font-weight-bold d-flex align-items-center gap-2">
                <i class='bx bx-conversation text-cyan fs-4'></i>
                Live Communications Desk
            </h5>
            <p class="text-secondary mb-0 fs-8">Real-time support tickets, member messaging, and multi-channel communications desk.</p>
        </div>
        <div>
            <button type="button" class="btn btn-info btn-sm px-3 py-1.5 rounded-3 text-white font-weight-bold shadow-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#createConversationModal">
                <i class='bx bx-plus-circle fs-6'></i>
                Start New Chat
            </button>
        </div>
    </div>

    <!-- Analytics KPI Cards -->
    <div class="row g-2.5 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-hub">
                <div class="kpi-icon-wrap total"><i class='bx bx-chat'></i></div>
                <div>
                    <div class="kpi-title-hub">Total Conversations</div>
                    <div class="kpi-number-hub">{{ number_format($stats['total_conversations']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-hub">
                <div class="kpi-icon-wrap open"><i class='bx bx-folder-open'></i></div>
                <div>
                    <div class="kpi-title-hub">Active Open Chats</div>
                    <div class="kpi-number-hub">{{ number_format($stats['open_conversations']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-hub">
                <div class="kpi-icon-wrap support"><i class='bx bx-support'></i></div>
                <div>
                    <div class="kpi-title-hub">Support Tickets</div>
                    <div class="kpi-number-hub">{{ number_format($stats['support_conversations']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card-hub">
                <div class="kpi-icon-wrap unread"><i class='bx bx-envelope-open'></i></div>
                <div>
                    <div class="kpi-title-hub">Unread Messages</div>
                    <div class="kpi-number-hub">{{ number_format($stats['unread_messages']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workspace Layout -->
    <div class="hub-workspace">

        <!-- Sidebar Directory (Left) -->
        <div class="hub-sidebar">
            <div class="hub-sidebar-header">
                <div class="position-relative">
                    <input type="text" id="convSearchInput" class="form-control hub-search-box" placeholder="Search name, phone, message...">
                    <i class='bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary'></i>
                </div>
                <div class="hub-filter-scroll">
                    <button class="hub-filter-pill active" onclick="filterConv('all', this)">All</button>
                    <button class="hub-filter-pill" onclick="filterConv('open', this)">Open</button>
                    <button class="hub-filter-pill" onclick="filterConv('support', this)">Support</button>
                    <button class="hub-filter-pill" onclick="filterConv('direct', this)">Direct</button>
                    <button class="hub-filter-pill" onclick="filterConv('in_app', this)">In-App</button>
                    <button class="hub-filter-pill" onclick="filterConv('whatsapp', this)">WhatsApp</button>
                    <button class="hub-filter-pill" onclick="filterConv('sms', this)">SMS</button>
                </div>
            </div>

            <div class="hub-conv-list" id="convListContainer">
                @forelse($conversations as $conv)
                    @php
                        $firstUser = $conv->users->where('id', '!=', auth()->id())->first() ?? $conv->users->first();
                        $userName = $firstUser ? trim(($firstUser->fname ?? '') . ' ' . ($firstUser->lname ?? '')) : 'User #' . $conv->id;
                        $userPhoto = ($firstUser && $firstUser->photo) ? asset('upload/admin_images/' . $firstUser->photo) : asset('upload/no_image.jpg');
                        $lastMsg = $conv->latestMessage;
                    @endphp
                    <div class="conv-card-item {{ ($selectedConvId == $conv->id) ? 'active' : '' }}"
                         data-id="{{ $conv->id }}"
                         data-status="{{ $conv->status }}"
                         data-type="{{ $conv->type }}"
                         data-channel="{{ $conv->channel }}"
                         onclick="selectConversation({{ $conv->id }}, this)">
                        <div class="avatar-wrapper-hub">
                            <img src="{{ $userPhoto }}" class="avatar-img-hub" alt="Avatar">
                            <span class="live-status-dot {{ $conv->status }}"></span>
                        </div>
                        <div class="conv-info-hub">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="conv-user-name">{{ $userName }}</span>
                                <span class="text-secondary fs-8 conv-time-label">{{ $lastMsg ? $lastMsg->created_at->format('H:i') : '' }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <span class="conv-snippet">
                                    {{ $lastMsg ? \Illuminate\Support\Str::limit($lastMsg->content, 28) : 'No messages yet' }}
                                </span>
                                @if($conv->unread_count > 0)
                                    <span class="badge bg-danger rounded-pill fs-8">{{ $conv->unread_count }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1 mt-1.5">
                                <span class="badge-channel badge-channel.{{ $conv->channel }}">{{ strtoupper(str_replace('_', ' ', $conv->channel)) }}</span>
                                <span class="badge-type badge-type.{{ $conv->type }}">{{ strtoupper($conv->type) }}</span>
                                @if($conv->shipment_id)
                                    <span class="badge bg-dark border border-secondary text-cyan fs-8"><i class='bx bx-package me-1'></i>#{{ $conv->shipment_id }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-secondary">
                        <i class='bx bx-chat fs-1 mb-2'></i>
                        <p class="mb-0">No conversations found</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Main Stage Workspace (Right) -->
        <div class="hub-main-stage">
            <!-- Header Bar -->
            <div id="chatHeader" class="stage-header" style="display: none;">
                <div class="stage-user-info">
                    <button type="button" class="btn btn-sm btn-outline-secondary text-cyan d-lg-none me-1 p-1 rounded-3" onclick="closeMobileChat()" title="Back to Conversations">
                        <i class="bx bx-left-arrow-alt fs-4"></i>
                    </button>
                    <img id="activeConvAvatar" src="{{ asset('upload/no_image.jpg') }}" class="stage-avatar" alt="Avatar">
                    <div>
                        <div id="activeConvTitle" class="stage-title">Select a Conversation</div>
                        <div class="stage-sub">
                            <span id="activeConvChannel" class="badge-channel">IN-APP</span>
                            <span id="activeConvType" class="badge-type">DIRECT</span>
                            <span id="activeConvStatusBadge" class="badge bg-success">OPEN</span>
                            <span id="activeShipmentTag" style="display: none;" class="badge bg-dark border border-cyan text-cyan ms-1">
                                <i class='bx bx-package me-1'></i><span id="activeShipmentNum"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stage-actions">
                    <!-- Channel Switcher -->
                    <select id="channelSelect" class="form-select form-select-sm bg-dark text-white border-secondary rounded-3" style="width: 115px;" onchange="changeChannel(this.value)">
                        <option value="in_app">In-App</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="sms">SMS</option>
                    </select>

                    <!-- Status Toggle Button -->
                    <button id="toggleStatusBtn" class="btn btn-sm btn-outline-warning rounded-3 font-weight-bold" onclick="toggleStatus()">
                        <i class='bx bx-lock-alt me-1'></i>Close Ticket
                    </button>

                    <!-- Manage Members -->
                    <button class="btn btn-sm btn-outline-info rounded-3" data-bs-toggle="modal" data-bs-target="#participantsModal" title="Manage Participants">
                        <i class='bx bx-group fs-5'></i>
                    </button>

                    <!-- Delete Conversation -->
                    <button class="btn btn-sm btn-outline-danger rounded-3" onclick="confirmDeleteConversation()" title="Delete Conversation">
                        <i class='bx bx-trash fs-5'></i>
                    </button>
                </div>
            </div>

            <!-- Messages Stream Area -->
            <div class="stage-messages-body" id="chatBody">
                <div class="text-center my-auto py-5 text-secondary" id="emptyStateMsg">
                    <i class='bx bx-conversation text-cyan' style="font-size: 64px;"></i>
                    <h5 class="mt-3 text-white font-weight-bold">Live Communications Desk</h5>
                    <p class="fs-7 text-secondary">Select a thread from the left directory to view messages & respond.</p>
                </div>
            </div>

            <!-- Footer Controls & Textarea Input -->
            <div id="chatFooter" class="stage-footer" style="display: none;">
                <!-- Reply Indicator -->
                <div id="replyIndicator" class="reply-bar-preview" style="display: none;">
                    <div>
                        <i class='bx bx-reply me-1'></i>Replying to <strong id="replySenderName"></strong>:
                        <span id="replyPreviewContent" class="text-white ms-1"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-2" onclick="cancelReply()"></button>
                </div>

                <div class="input-controls-row position-relative">
                    <!-- Voice Recording Live Overlay Bar -->
                    <div id="voiceRecordOverlay" class="voice-record-overlay" style="display: none;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rec-pulse-dot"></div>
                            <span class="rec-timer-label" id="recTimerText">00:00</span>
                        </div>
                        
                        <!-- Live Waveform Frequency Canvas -->
                        <canvas id="voiceWaveformCanvas" class="rec-waveform-canvas" width="160" height="32"></canvas>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 font-weight-bold" onclick="cancelVoiceRecording()">
                                <i class="bx bx-trash me-1"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-sm btn-success text-white rounded-3 font-weight-bold px-3" onclick="stopAndSendVoiceRecording()">
                                <i class="bx bx-send me-1"></i>Send
                            </button>
                        </div>
                    </div>

                    <!-- Actions & Tools Bar (Templates, Attachments, Mic) -->
                    <div class="chat-actions-bar">
                        <!-- Quick Response Templates -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm rounded-3 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown">
                                <i class='bx bx-zap me-1 text-warning'></i>Templates
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark shadow-lg">
                                <li><a class="dropdown-item fs-8" href="javascript:;" onclick="insertTemplate('Hello! How can we assist you today?')">Hello! How can we assist you today?</a></li>
                                <li><a class="dropdown-item fs-8" href="javascript:;" onclick="insertTemplate('Your shipment update request has been received and is being processed.')">Your request has been received.</a></li>
                                <li><a class="dropdown-item fs-8" href="javascript:;" onclick="insertTemplate('Thank you for reaching out to SALASIL support desk.')">Thank you for reaching out.</a></li>
                            </ul>
                        </div>

                        <!-- Attachment Menu -->
                        <div class="dropdown">
                            <button class="btn btn-outline-info btn-sm rounded-3 dropdown-toggle text-info" type="button" data-bs-toggle="dropdown">
                                <i class='bx bx-paperclip me-1'></i>Attach
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark shadow-lg">
                                <li><a class="dropdown-item fs-8" href="javascript:;" onclick="triggerFileSelect('image')"><i class='bx bx-image me-2 text-info'></i>Send Image</a></li>
                                <li><a class="dropdown-item fs-8" href="javascript:;" onclick="triggerFileSelect('file')"><i class='bx bx-file me-2 text-warning'></i>Send File</a></li>
                                <li><a class="dropdown-item fs-8" href="javascript:;" onclick="openLocationModal()"><i class='bx bx-map-pin me-2 text-danger'></i>Send Location</a></li>
                            </ul>
                        </div>

                        <!-- Hidden File Input -->
                        <input type="file" id="hiddenFileInput" style="display: none;" onchange="handleFileSelected(this)">

                        <!-- Voice Recording Button -->
                        <button type="button" class="btn-mic-record" id="voiceRecordBtn" onclick="startVoiceRecording()" title="Record Voice Note">
                            <i class='bx bx-microphone'></i>
                        </button>
                    </div>

                    <!-- Type Box & Send Button Row (Dedicated row on mobile) -->
                    <div class="chat-input-send-row">
                        <!-- Message Textarea -->
                        <textarea id="messageTextInput" class="form-control chat-input-textarea" rows="1" placeholder="Type message... (Enter to send)" onkeydown="checkEnterSend(event)"></textarea>

                        <!-- Send Button -->
                        <button type="button" class="btn-send-gradient" onclick="sendMessage()">
                            <i class='bx bx-send'></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 1: CREATE NEW CONVERSATION
========================================================================= -->
<div class="modal fade" id="createConversationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-secondary shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-weight-bold d-flex align-items-center gap-2">
                    <i class='bx bx-plus-circle text-info fs-4'></i>
                    Start New Conversation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('store.conversation') }}" method="POST" id="createConvForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold fs-8 text-secondary">Conversation Type</label>
                            <select name="type" class="form-select bg-dark text-white border-secondary rounded-3" required>
                                <option value="direct">Direct Chat</option>
                                <option value="support" selected>Support Ticket</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-weight-bold fs-8 text-secondary">Communication Channel</label>
                            <select name="channel" class="form-select bg-dark text-white border-secondary rounded-3" required>
                                <option value="in_app" selected>In-App Messaging</option>
                                <option value="whatsapp">WhatsApp Integration</option>
                                <option value="sms">SMS Gateway</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label font-weight-bold fs-8 text-secondary">Link to Shipment (Optional)</label>
                            <select name="shipment_id" class="form-select bg-dark text-white border-secondary rounded-3">
                                <option value="">-- No Shipment Link --</option>
                                @foreach($shipments as $sh)
                                    <option value="{{ $sh->id }}">Shipment #{{ $sh->id }} - {{ $sh->shipment_name ?? ('Shipment #' . $sh->id) }} (Status: {{ strtoupper($sh->status) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label font-weight-bold fs-8 text-secondary">Select Participants</label>
                            <select name="user_ids[]" class="form-select bg-dark text-white border-secondary rounded-3" multiple required style="height: 130px;">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">
                                        {{ trim($u->fname . ' ' . $u->lname) }} ({{ strtoupper($u->role) }}) — {{ $u->phone ?? $u->email }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-secondary">Hold Ctrl or Cmd to select multiple platform members.</small>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label font-weight-bold fs-8 text-secondary">Opening Message (Optional)</label>
                            <textarea name="initial_message" class="form-control bg-dark text-white border-secondary rounded-3" rows="3" placeholder="Type initial greeting or ticket description..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info px-4 rounded-3 text-white font-weight-bold">Create Conversation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 2: MANAGE PARTICIPANTS
========================================================================= -->
<div class="modal fade" id="participantsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-weight-bold"><i class='bx bx-group me-2 text-info'></i>Manage Thread Participants</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="input-group mb-4">
                    <select id="addParticipantSelect" class="form-select bg-dark text-white border-secondary">
                        <option value="">Select platform member to add...</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ trim($u->fname . ' ' . $u->lname) }} ({{ strtoupper($u->role) }})</option>
                        @endforeach
                    </select>
                    <button class="btn btn-info text-white" onclick="addParticipant()"><i class='bx bx-plus me-1'></i>Add</button>
                </div>
                <div class="list-group list-group-flush bg-transparent" id="participantsListContainer">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 3: GEOLOCATION PIN WITH INTERACTIVE MAP
========================================================================= -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-secondary shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-weight-bold d-flex align-items-center gap-2">
                    <i class='bx bx-map-pin text-danger fs-4'></i>
                    Send Geolocation Pin
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Search Box -->
                <div class="mb-3 position-relative">
                    <label class="form-label fs-8 text-secondary font-weight-bold">Search Location</label>
                    <input type="text" id="chatLocationSearchInput" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="🔍 Search city, landmark, or address...">
                </div>

                <!-- Map Container -->
                <div class="position-relative mb-3 rounded-3 overflow-hidden border border-secondary" style="height: 280px;">
                    <div id="chatLocationMap" style="width: 100%; height: 100%;"></div>
                    <div class="position-absolute bottom-0 start-0 m-2 px-3 py-1 bg-dark bg-opacity-75 text-cyan fs-8 rounded-3 border border-secondary" id="chatMapCoordsLabel">
                        <i class="bx bx-crosshair me-1"></i>Click map to set pin
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fs-8 text-secondary font-weight-bold">Latitude</label>
                        <input type="text" id="locLat" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. 24.7136">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-8 text-secondary font-weight-bold">Longitude</label>
                        <input type="text" id="locLng" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. 46.6753">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fs-8 text-secondary font-weight-bold">Address / Location Title</label>
                        <input type="text" id="locAddress" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. Riyadh Central Logistics Yard #4">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-3 font-weight-bold px-4" onclick="sendLocationMessage()">
                    <i class='bx bx-send me-1'></i>Send Location
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 4: ATTACHMENT & IMAGE PREVIEW WITH CAPTION
========================================================================= -->
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-weight-bold d-flex align-items-center gap-2">
                    <i class='bx bx-image text-info fs-4' id="attachModalIcon"></i>
                    <span id="attachModalTitle">Send Image Attachment</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Preview Container -->
                <div class="text-center mb-3 p-3 border border-secondary rounded-3" style="background: rgba(0,0,0,0.3);">
                    <img id="attachPreviewImg" src="" style="max-height: 220px; max-width: 100%; border-radius: 12px; display: none;" class="shadow-sm">
                    <div id="attachFileDetails" style="display: none;" class="py-2">
                        <i class='bx bx-file text-warning' style="font-size: 48px;"></i>
                        <div class="fw-bold fs-8 mt-2" id="attachFileName">filename.pdf</div>
                        <div class="fs-9 text-secondary" id="attachFileSize">0 KB</div>
                    </div>
                </div>

                <!-- Caption Text Input -->
                <div class="mb-3">
                    <label class="form-label font-weight-bold fs-8 text-secondary">Add Caption / Text Message (Optional)</label>
                    <textarea id="attachCaptionInput" class="form-control bg-dark text-white border-secondary rounded-3" rows="3" placeholder="Type a message to send along with this image..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info text-white font-weight-bold rounded-3 px-4" onclick="confirmSendAttachment()">
                    <i class='bx bx-send me-1'></i>Send Attachment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 5: ULTRA-PROFESSIONAL MEDIA & DOCUMENT VIEWER POPUP
========================================================================= -->
<div class="modal fade" id="mediaViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-white border-secondary shadow-lg overflow-hidden" style="border-radius: 18px;">
            <div class="modal-header border-secondary py-3 px-4" style="background: rgba(15, 23, 42, 0.95);">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="kpi-icon-wrap support" id="viewerTypeIcon" style="width: 38px; height: 38px; font-size: 18px;">
                        <i class='bx bx-image'></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="mb-0 text-white font-weight-bold text-truncate" id="viewerFileName" style="max-width: 450px;">Media Attachment</h6>
                        <small class="text-secondary" id="viewerFileType">File Preview</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a href="#" id="viewerDownloadBtn" download class="btn btn-sm btn-outline-info rounded-3 font-weight-bold d-flex align-items-center gap-1">
                        <i class='bx bx-download fs-6'></i> Download
                    </a>
                    <a href="#" id="viewerExternalBtn" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3 text-white d-flex align-items-center gap-1">
                        <i class='bx bx-external-link fs-6'></i> Open Full Page
                    </a>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0 d-flex align-items-center justify-content-center" style="background: #090D16; min-height: 480px; max-height: 80vh; overflow: auto;">
                <!-- Image Stage -->
                <img id="viewerImageElement" src="" style="max-height: 75vh; max-width: 100%; object-fit: contain; display: none;" class="p-3 shadow-lg">

                <!-- iFrame Stage (PDF / Docs) -->
                <iframe id="viewerIframeElement" src="" style="width: 100%; height: 75vh; border: none; display: none;"></iframe>

                <!-- Generic Fallback Stage -->
                <div id="viewerFallbackStage" style="display: none;" class="text-center py-5 px-4">
                    <div class="p-4 rounded-circle bg-info bg-opacity-10 border border-info d-inline-flex mb-3">
                        <i class='bx bx-file text-info' style="font-size: 56px;"></i>
                    </div>
                    <h5 class="text-white font-weight-bold mb-2" id="viewerFallbackName">Document File</h5>
                    <p class="text-secondary fs-8 mb-4">This file format does not support inline browser preview.</p>
                    <a href="#" id="viewerFallbackDownloadBtn" download class="btn btn-info text-white font-weight-bold px-4 py-2 rounded-3 shadow">
                        <i class='bx bx-download me-1'></i> Download File
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps JS API for Location Picker -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0cvICpKSuRBuCf2mN11rtLC5FsGFN2eI&libraries=places" defer></script>

<!-- Real-time Messaging JavaScript Engine -->
<script>
let currentConvId = {{ $selectedConvId ?? 'null' }};
let currentReplyTo = null;
let selectedFileType = null;
let activeEchoChannelId = null;

// Map Variables
let chatMap = null;
let chatMarker = null;
let chatMapInited = false;

const darkMapStyles = [
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

$(document.body).ready(function() {
    // Auto-select first thread if not set
    if (!currentConvId) {
        const firstItem = $('.conv-card-item').first();
        if (firstItem.length > 0) {
            currentConvId = firstItem.data('id');
            firstItem.addClass('active');
        }
    }

    if (currentConvId) {
        loadConversationMessages(currentConvId);
    }

    // Ultra-Fast Live Sync Fallback (1 Second)
    setInterval(function() {
        if (currentConvId) {
            syncActiveMessages(currentConvId);
        }
    }, 1000);
});

// Subscribe to Private Reverb Channel via Laravel Echo
function subscribeToReverbChannel(convId) {
    if (!window.Echo) {
        console.warn('Laravel Echo not loaded yet.');
        return;
    }

    if (activeEchoChannelId && activeEchoChannelId !== convId) {
        window.Echo.leave(`chat.${activeEchoChannelId}`);
    }

    activeEchoChannelId = convId;

    window.Echo.private(`chat.${convId}`)
        .listen('.message.sent', function(e) {
            handleIncomingLiveMessage(e);
        });
}

// Universal Real-time Message Handler & DOM Renderer
function handleIncomingLiveMessage(e) {
    if (!e || !e.message) return;
    const msg = e.message;
    const targetConvId = msg.conversation_id || currentConvId;

    if (targetConvId == currentConvId) {
        // Prevent duplicate rendering
        if ($(`#msg-${msg.id}`).length > 0) return;

        const isMe = (msg.sender_id == {{ auth()->id() }} || msg.is_me);
        const sender = msg.sender || {};
        const senderName = msg.sender_name || sender.name || ((sender.fname || '') + ' ' + (sender.lname || '')).trim() || 'User';
        const senderRole = (msg.sender_role || sender.role || 'member').toUpperCase();
        const createdTime = msg.created_at || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        let replyMarkup = '';
        if (msg.reply_to) {
            replyMarkup = `
                <div class="reply-quote-box">
                    <strong class="text-cyan">${msg.reply_to.sender_name || 'User'}:</strong> ${msg.reply_to.content || ''}
                </div>
            `;
        }

        let bodyMarkup = '';
        const rawContent = (msg.content || '').trim();
        const fileName = (msg.metadata && msg.metadata.file_name) ? msg.metadata.file_name.trim() : '';

        // Display Caption Text if it's not location and not identical to fallback filename
        if (msg.message_type !== 'location' && rawContent && rawContent !== fileName) {
            bodyMarkup += `<div class="mb-1">${rawContent}</div>`;
        }

        if (msg.message_type === 'image' && msg.metadata && msg.metadata.file_url) {
            const fName = (msg.metadata.file_name || 'Image Attachment').replace(/'/g, "\\'");
            bodyMarkup += `<img src="${msg.metadata.file_url}" class="media-img-preview" onclick="openMediaViewerModal('${msg.metadata.file_url}', '${fName}', 'image')">`;
        } else if (msg.message_type === 'file' && msg.metadata && msg.metadata.file_url) {
            const fName = (msg.metadata.file_name || 'Document File').replace(/'/g, "\\'");
            bodyMarkup += `
                <div class="media-file-box" onclick="openMediaViewerModal('${msg.metadata.file_url}', '${fName}', 'file')" style="cursor: pointer;">
                    <i class="bx bx-file fs-2"></i>
                    <div>
                        <div class="fw-bold fs-8">${msg.metadata.file_name || 'View Attachment'}</div>
                        <div class="fs-9 text-secondary">${Math.round((msg.metadata.file_size || 0)/1024)} KB</div>
                    </div>
                </div>
            `;
        } else if (msg.message_type === 'location' && msg.metadata) {
            const lat = parseFloat(msg.metadata.latitude || 24.7136).toFixed(5);
            const lng = parseFloat(msg.metadata.longitude || 46.6753).toFixed(5);
            const rawAddr = msg.metadata.address || 'Pinned Location';
            const addr = rawAddr.replace(/^Location Pin:\s*/i, '');
            const mapUrl = `https://www.google.com/maps?q=${lat},${lng}`;
            const embedMapUrl = `https://maps.google.com/maps?q=${lat},${lng}&z=14&output=embed`;

            bodyMarkup += `
                <div class="chat-location-card">
                    <div class="d-flex align-items-start gap-2.5 mb-2">
                        <div class="location-icon-badge"><i class="bx bx-map-pin"></i></div>
                        <div class="location-card-text">
                            <div class="location-title-text" title="${addr}">${addr}</div>
                            <div class="location-gps-text"><i class="bx bx-crosshair me-1"></i>${lat}, ${lng}</div>
                        </div>
                    </div>
                    <div class="location-preview-box my-2" onclick="window.open('${mapUrl}', '_blank')">
                        <iframe class="location-preview-iframe" src="${embedMapUrl}" frameborder="0" scrolling="no"></iframe>
                        <div class="location-overlay-badge">
                            <i class="bx bx-fullscreen me-1"></i>Open Map
                        </div>
                    </div>
                    <a href="${mapUrl}" target="_blank" class="btn-location-action">
                        <i class="bx bx-navigation fs-6"></i>
                        <span>Open Location in Google Maps</span>
                    </a>
                </div>
            `;
        } else if (msg.message_type === 'audio' && msg.metadata && msg.metadata.file_url) {
            const durSec = parseInt(msg.metadata.duration || 0);
            const mins = Math.floor(durSec / 60);
            const secs = String(durSec % 60).padStart(2, '0');
            const durText = durSec > 0 ? `${mins}:${secs}` : '0:00';

            bodyMarkup += `
                <div class="chat-voicenote-card">
                    <div class="vn-avatar-badge"><i class="bx bx-microphone"></i></div>
                    <button type="button" class="btn-play-vn" id="btn-play-${msg.id}" onclick="toggleVoicePlay(${msg.id})">
                        <i class="bx bx-play"></i>
                    </button>
                    <div class="vn-track-wrap">
                        <input type="range" class="vn-seek-bar" id="seek-${msg.id}" value="0" min="0" max="100" oninput="seekVoiceNote(${msg.id}, this.value)">
                        <div class="vn-time-label">
                            <span id="vn-timer-${msg.id}">0:00 / ${durText}</span>
                            <span><i class="bx bx-waveform"></i> Voice Note</span>
                        </div>
                    </div>
                    <audio id="audio-${msg.id}" src="${msg.metadata.file_url}" preload="auto" playsinline ontimeupdate="updateVoiceProgress(${msg.id})" onended="resetVoicePlay(${msg.id})"></audio>
                </div>
            `;
        } else if (msg.message_type === 'text' && !bodyMarkup) {
            bodyMarkup = `<div>${rawContent}</div>`;
        }

        const groupClass = isMe ? 'me' : 'other';
        const readIcon = isMe ? (msg.is_read ? '<i class="bx bx-check-double text-cyan"></i>' : '<i class="bx bx-check"></i>') : '';

        const msgHtml = `
            <div class="chat-msg-wrapper ${groupClass}" id="msg-${msg.id}">
                ${!isMe ? `<span class="msg-sender-label">${senderName} (${senderRole})</span>` : ''}
                <div class="msg-bubble-box">
                    ${replyMarkup}
                    ${bodyMarkup}
                </div>
                <div class="msg-footer-info">
                    <span>${createdTime}</span>
                    ${readIcon}
                    <a href="javascript:;" onclick="setReply(${msg.id}, '${senderName}', '${(rawContent||'').replace(/'/g, "\\'")}')" class="text-secondary ms-2" title="Reply"><i class="bx bx-reply"></i></a>
                    <a href="javascript:;" onclick="deleteMessage(${msg.id})" class="text-secondary ms-1" title="Delete"><i class="bx bx-trash"></i></a>
                </div>
            </div>
        `;
        $('#chatBody').append(msgHtml);

        // Auto Scroll to Bottom
        const container = $('#chatBody');
        container.scrollTop(container[0].scrollHeight);
    }

    // Update Left Sidebar Item Snippet & Position
    const convItem = $(`.conv-card-item[data-id="${targetConvId}"]`);
    if (convItem.length > 0) {
        convItem.find('.conv-snippet').text(msg.content || '[' + msg.message_type + ']');
        $('#convListContainer').prepend(convItem);
    }
}

// Select Thread
function selectConversation(id, elem) {
    $('.conv-card-item').removeClass('active');
    if (elem) {
        $(elem).addClass('active');
    } else {
        $(`.conv-card-item[data-id="${id}"]`).addClass('active');
    }
    currentConvId = id;
    $('.hub-workspace').addClass('mobile-chat-active');
    loadConversationMessages(id);
}

function closeMobileChat() {
    $('.hub-workspace').removeClass('mobile-chat-active');
}

// Fetch Thread Data & Initial Messages via AJAX
function loadConversationMessages(id) {
    subscribeToReverbChannel(id);

    $.ajax({
        url: "/admin/communications/messages/" + id,
        type: "GET",
        dataType: "json",
        success: function(res) {
            if (res.success) {
                const conv = res.conversation;
                const msgs = res.messages;

                $('#chatHeader').show();
                $('#chatFooter').show();
                $('#emptyStateMsg').hide();

                const pFirst = conv.participants.find(p => p.user_id != {{ auth()->id() }}) || conv.participants[0];
                if (pFirst) {
                    $('#activeConvTitle').text(pFirst.name);
                    $('#activeConvAvatar').attr('src', pFirst.photo);
                }

                $('#activeConvChannel').text(conv.channel.toUpperCase().replace('_', ' '))
                    .attr('class', 'badge-channel badge-channel.' + conv.channel);

                $('#activeConvType').text(conv.type.toUpperCase())
                    .attr('class', 'badge-type badge-type.' + conv.type);

                const isClosed = (conv.status == 'closed');
                $('#activeConvStatusBadge')
                    .text(isClosed ? 'CLOSED' : 'OPEN')
                    .attr('class', isClosed ? 'badge bg-secondary' : 'badge bg-success');

                $('#toggleStatusBtn')
                    .html(isClosed ? '<i class="bx bx-lock-open-alt me-1"></i>Reopen Ticket' : '<i class="bx bx-lock-alt me-1"></i>Close Ticket')
                    .attr('class', isClosed ? 'btn btn-sm btn-outline-success rounded-3' : 'btn btn-sm btn-outline-warning rounded-3');

                $('#channelSelect').val(conv.channel);

                if (conv.shipment) {
                    $('#activeShipmentTag').show();
                    $('#activeShipmentNum').text('#' + conv.shipment.id);
                } else {
                    $('#activeShipmentTag').hide();
                }

                // Render Participants List
                let partHtml = '';
                conv.participants.forEach(p => {
                    partHtml += `
                        <div class="d-flex align-items-center justify-content-between p-2.5 border-bottom border-secondary">
                            <div class="d-flex align-items-center gap-2.5">
                                <img src="${p.photo}" style="width:38px;height:38px;border-radius:10px;object-fit:cover;">
                                <div>
                                    <div class="fw-bold fs-8">${p.name} <span class="badge bg-dark border text-info fs-9">${p.role.toUpperCase()}</span></div>
                                    <div class="fs-9 text-secondary">Last Read: ${p.last_read_at}</div>
                                </div>
                            </div>
                            ${p.user_id != {{ auth()->id() }} ? `<button class="btn btn-sm btn-outline-danger" onclick="removeParticipant(${p.user_id})"><i class="bx bx-user-minus"></i></button>` : ''}
                        </div>
                    `;
                });
                $('#participantsListContainer').html(partHtml);

                // Render Messages Stream
                renderMessagesStream(msgs);
            }
        }
    });
}

// Sync Active Messages Poll Fallback & Instant Deletion Cleanup
function syncActiveMessages(id) {
    $.ajax({
        url: "/admin/communications/messages/" + id,
        type: "GET",
        dataType: "json",
        success: function(res) {
            if (res.success && res.messages) {
                const activeIds = new Set(res.messages.map(m => parseInt(m.id)));

                // 1. Render missing new messages
                res.messages.forEach(msg => {
                    if ($(`#msg-${msg.id}`).length === 0) {
                        msg.conversation_id = id;
                        handleIncomingLiveMessage({ message: msg });
                    }
                });

                // 2. Remove deleted messages from DOM immediately
                $('#chatBody .chat-msg-wrapper').each(function() {
                    const domId = parseInt($(this).attr('id').replace('msg-', ''));
                    if (domId && !activeIds.has(domId)) {
                        $(this).remove();
                    }
                });
            }
        }
    });
}

// Delete Single Message (Optimistic Instant Deletion)
function deleteMessage(msgId) {
    if (confirm('Are you sure you want to delete this message?')) {
        const msgEl = $(`#msg-${msgId}`);
        msgEl.fadeOut(150, function() {
            msgEl.remove();
        });

        $.ajax({
            url: "/admin/communications/delete-message/" + msgId,
            type: "GET",
            error: function() {
                loadConversationMessages(currentConvId);
            }
        });
    }
}

// Render Messages Stream HTML
function renderMessagesStream(messages) {
    const container = $('#chatBody');
    let html = '';

    messages.forEach(msg => {
        const isMe = msg.is_me;
        const groupClass = isMe ? 'me' : 'other';

        let replyMarkup = '';
        if (msg.reply_to) {
            replyMarkup = `
                <div class="reply-quote-box">
                    <strong class="text-cyan">${msg.reply_to.sender_name}:</strong> ${msg.reply_to.content}
                </div>
            `;
        }

        let bodyMarkup = '';
        const rawContent = (msg.content || '').trim();
        const fileName = (msg.metadata && msg.metadata.file_name) ? msg.metadata.file_name.trim() : '';

        // Display Caption Text if it's not location and not identical to fallback filename
        if (msg.message_type !== 'location' && rawContent && rawContent !== fileName) {
            bodyMarkup += `<div class="mb-1">${rawContent}</div>`;
        }

        if (msg.message_type === 'image' && msg.metadata && msg.metadata.file_url) {
            const fName = (msg.metadata.file_name || 'Image Attachment').replace(/'/g, "\\'");
            bodyMarkup += `<img src="${msg.metadata.file_url}" class="media-img-preview" onclick="openMediaViewerModal('${msg.metadata.file_url}', '${fName}', 'image')">`;
        } else if (msg.message_type === 'file' && msg.metadata && msg.metadata.file_url) {
            const fName = (msg.metadata.file_name || 'Document File').replace(/'/g, "\\'");
            bodyMarkup += `
                <div class="media-file-box" onclick="openMediaViewerModal('${msg.metadata.file_url}', '${fName}', 'file')" style="cursor: pointer;">
                    <i class="bx bx-file fs-2"></i>
                    <div>
                        <div class="fw-bold fs-8">${msg.metadata.file_name || 'View Attachment'}</div>
                        <div class="fs-9 text-secondary">${Math.round((msg.metadata.file_size || 0)/1024)} KB</div>
                    </div>
                </div>
            `;
        } else if (msg.message_type === 'location' && msg.metadata) {
            const lat = parseFloat(msg.metadata.latitude || 24.7136).toFixed(5);
            const lng = parseFloat(msg.metadata.longitude || 46.6753).toFixed(5);
            const rawAddr = msg.metadata.address || 'Pinned Location';
            const addr = rawAddr.replace(/^Location Pin:\s*/i, '');
            const mapUrl = `https://www.google.com/maps?q=${lat},${lng}`;
            const embedMapUrl = `https://maps.google.com/maps?q=${lat},${lng}&z=14&output=embed`;

            bodyMarkup += `
                <div class="chat-location-card">
                    <div class="d-flex align-items-start gap-2.5 mb-2">
                        <div class="location-icon-badge"><i class="bx bx-map-pin"></i></div>
                        <div class="location-card-text">
                            <div class="location-title-text" title="${addr}">${addr}</div>
                            <div class="location-gps-text"><i class="bx bx-crosshair me-1"></i>${lat}, ${lng}</div>
                        </div>
                    </div>
                    <div class="location-preview-box my-2" onclick="window.open('${mapUrl}', '_blank')">
                        <iframe class="location-preview-iframe" src="${embedMapUrl}" frameborder="0" scrolling="no"></iframe>
                        <div class="location-overlay-badge">
                            <i class="bx bx-fullscreen me-1"></i>Open Map
                        </div>
                    </div>
                    <a href="${mapUrl}" target="_blank" class="btn-location-action">
                        <i class="bx bx-navigation fs-6"></i>
                        <span>Open Location in Google Maps</span>
                    </a>
                </div>
            `;
        } else if (msg.message_type === 'audio' && msg.metadata && msg.metadata.file_url) {
            const durSec = parseInt(msg.metadata.duration || 0);
            const mins = Math.floor(durSec / 60);
            const secs = String(durSec % 60).padStart(2, '0');
            const durText = durSec > 0 ? `${mins}:${secs}` : '0:00';

            bodyMarkup += `
                <div class="chat-voicenote-card">
                    <div class="vn-avatar-badge"><i class="bx bx-microphone"></i></div>
                    <button type="button" class="btn-play-vn" id="btn-play-${msg.id}" onclick="toggleVoicePlay(${msg.id})">
                        <i class="bx bx-play"></i>
                    </button>
                    <div class="vn-track-wrap">
                        <input type="range" class="vn-seek-bar" id="seek-${msg.id}" value="0" min="0" max="100" oninput="seekVoiceNote(${msg.id}, this.value)">
                        <div class="vn-time-label">
                            <span id="vn-timer-${msg.id}">0:00 / ${durText}</span>
                            <span><i class="bx bx-waveform"></i> Voice Note</span>
                        </div>
                    </div>
                    <audio id="audio-${msg.id}" src="${msg.metadata.file_url}" preload="auto" playsinline ontimeupdate="updateVoiceProgress(${msg.id})" onended="resetVoicePlay(${msg.id})"></audio>
                </div>
            `;
        } else if (msg.message_type === 'text' && !bodyMarkup) {
            bodyMarkup = `<div>${rawContent}</div>`;
        }

        const readIcon = isMe ? (msg.is_read ? '<i class="bx bx-check-double text-cyan"></i>' : '<i class="bx bx-check"></i>') : '';

        html += `
            <div class="chat-msg-wrapper ${groupClass}" id="msg-${msg.id}">
                ${!isMe ? `<span class="msg-sender-label">${msg.sender_name} (${msg.sender_role.toUpperCase()})</span>` : ''}
                <div class="msg-bubble-box">
                    ${replyMarkup}
                    ${bodyMarkup}
                </div>
                <div class="msg-footer-info">
                    <span>${msg.created_at}</span>
                    ${readIcon}
                    <a href="javascript:;" onclick="setReply(${msg.id}, '${msg.sender_name}', '${(rawContent||'').replace(/'/g, "\\'")}')" class="text-secondary ms-2" title="Reply"><i class="bx bx-reply"></i></a>
                    <a href="javascript:;" onclick="deleteMessage(${msg.id})" class="text-secondary ms-1" title="Delete"><i class="bx bx-trash"></i></a>
                </div>
            </div>
        `;
    });

    container.html(html);
    container.scrollTop(container[0].scrollHeight);
}

// Send Message Action
function sendMessage() {
    const text = $('#messageTextInput').val().trim();
    if (!text && !selectedFileType) return;

    const data = new FormData();
    data.append('_token', '{{ csrf_token() }}');
    data.append('conversation_id', currentConvId);
    data.append('message_type', selectedFileType || 'text');
    data.append('content', text);

    if (currentReplyTo) {
        data.append('reply_to_id', currentReplyTo);
    }

    const fileInput = document.getElementById('hiddenFileInput');
    if (fileInput.files.length > 0) {
        data.append('file_attachment', fileInput.files[0]);
    }

    $.ajax({
        url: "{{ route('send.message.ajax') }}",
        type: "POST",
        data: data,
        contentType: false,
        processData: false,
        success: function(res) {
            if (res.success) {
                $('#messageTextInput').val('');
                fileInput.value = '';
                cancelReply();
                selectedFileType = null;

                if (res.message) {
                    res.message.conversation_id = res.message.conversation_id || currentConvId;
                    handleIncomingLiveMessage({ message: res.message });
                }
            }
        }
    });
}

// Enter Key Handler
function checkEnterSend(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// Reply Setup
function setReply(msgId, senderName, content) {
    currentReplyTo = msgId;
    $('#replySenderName').text(senderName);
    $('#replyPreviewContent').text(content.substring(0, 50));
    $('#replyIndicator').slideDown(150);
}

function cancelReply() {
    currentReplyTo = null;
    $('#replyIndicator').slideUp(150);
}

// Insert Canned Template
function insertTemplate(text) {
    $('#messageTextInput').val(text).focus();
}

// Trigger File Selector
function triggerFileSelect(type) {
    selectedFileType = type;
    const input = $('#hiddenFileInput');
    input.val('');
    if (type === 'image') {
        input.attr('accept', 'image/*');
    } else {
        input.removeAttr('accept');
    }
    input.click();
}

function handleFileSelected(input) {
    if (input.files.length > 0) {
        const file = input.files[0];

        const currentMainText = $('#messageTextInput').val().trim();
        $('#attachCaptionInput').val(currentMainText);

        if (selectedFileType === 'image' || file.type.startsWith('image/')) {
            $('#attachModalTitle').text('Send Image Attachment');
            $('#attachModalIcon').attr('class', 'bx bx-image text-info fs-4');
            $('#attachFileDetails').hide();
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#attachPreviewImg').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#attachModalTitle').text('Send File Attachment');
            $('#attachModalIcon').attr('class', 'bx bx-file text-warning fs-4');
            $('#attachPreviewImg').hide();
            $('#attachFileName').text(file.name);
            $('#attachFileSize').text(Math.round(file.size / 1024) + ' KB');
            $('#attachFileDetails').show();
        }

        $('#attachmentModal').modal('show');
    }
}

function confirmSendAttachment() {
    const captionText = $('#attachCaptionInput').val().trim();
    $('#messageTextInput').val(captionText);
    $('#attachmentModal').modal('hide');
    sendMessage();
}

// Ultra-Professional Media & Document Viewer Modal Popup
function openMediaViewerModal(fileUrl, fileName, fileType) {
    if (!fileUrl) return;

    const isImage = (fileType === 'image' || /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(fileUrl));
    const isPdf = (/\.pdf$/i.test(fileUrl));

    $('#viewerFileName').text(fileName || 'Media Attachment');
    $('#viewerDownloadBtn').attr('href', fileUrl);
    $('#viewerExternalBtn').attr('href', fileUrl);
    $('#viewerFallbackDownloadBtn').attr('href', fileUrl);
    $('#viewerFallbackName').text(fileName || 'Document File');

    $('#viewerImageElement').hide();
    $('#viewerIframeElement').hide();
    $('#viewerFallbackStage').hide();

    if (isImage) {
        $('#viewerTypeIcon').html('<i class="bx bx-image"></i>').attr('class', 'kpi-icon-wrap support');
        $('#viewerFileType').text('Image Preview');
        $('#viewerImageElement').attr('src', fileUrl).show();
    } else if (isPdf) {
        $('#viewerTypeIcon').html('<i class="bx bx-file"></i>').attr('class', 'kpi-icon-wrap open');
        $('#viewerFileType').text('PDF Document Preview');
        const pdfUrl = fileUrl.includes('#') ? fileUrl : (fileUrl + '#toolbar=1&navpanes=0&view=FitH');
        $('#viewerIframeElement').attr('src', pdfUrl).show();
    } else {
        $('#viewerTypeIcon').html('<i class="bx bx-file"></i>').attr('class', 'kpi-icon-wrap total');
        $('#viewerFileType').text('File Attachment');
        $('#viewerFallbackStage').show();
    }

    $('#mediaViewerModal').modal('show');
}

// Send Location Pin with Interactive Map
function openLocationModal() {
    $('#locationModal').modal('show');

    setTimeout(function() {
        if (typeof google !== 'undefined' && google.maps) {
            initChatLocationMap();
        }
    }, 250);
}

function initChatLocationMap() {
    if (chatMapInited) {
        google.maps.event.trigger(chatMap, 'resize');
        return;
    }

    const defaultCenter = { lat: 24.7136, lng: 46.6753 }; // Riyadh default

    chatMap = new google.maps.Map(document.getElementById('chatLocationMap'), {
        center: defaultCenter,
        zoom: 12,
        styles: darkMapStyles,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true
    });

    chatMap.addListener('click', function(e) {
        setChatMapCoords(e.latLng.lat(), e.latLng.lng());
    });

    // Autocomplete Search
    const searchInput = document.getElementById('chatLocationSearchInput');
    const auto = new google.maps.places.Autocomplete(searchInput, {
        fields: ['geometry', 'formatted_address', 'name']
    });
    auto.bindTo('bounds', chatMap);
    auto.addListener('place_changed', function() {
        const place = auto.getPlace();
        if (!place.geometry || !place.geometry.location) return;

        if (place.geometry.viewport) {
            chatMap.fitBounds(place.geometry.viewport);
        } else {
            chatMap.setCenter(place.geometry.location);
            chatMap.setZoom(15);
        }

        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();
        setChatMapCoords(lat, lng, place.formatted_address || place.name);
    });

    chatMapInited = true;
}

function setChatMapCoords(lat, lng, addressName) {
    $('#locLat').val(lat.toFixed(6));
    $('#locLng').val(lng.toFixed(6));
    $('#chatMapCoordsLabel').html(`<i class="bx bx-map-pin me-1"></i> ${lat.toFixed(5)}, ${lng.toFixed(5)}`);

    if (addressName) {
        $('#locAddress').val(addressName);
    } else {
        if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    $('#locAddress').val(results[0].formatted_address);
                }
            });
        }
    }

    if (chatMarker) chatMarker.setMap(null);
    chatMarker = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: chatMap,
        animation: google.maps.Animation.DROP,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: '#F43F5E',
            fillOpacity: 1,
            strokeColor: '#FFFFFF',
            strokeWeight: 3
        }
    });
}

function sendLocationMessage() {
    const lat = $('#locLat').val();
    const lng = $('#locLng').val();
    const addr = $('#locAddress').val() || 'Pinned Location';

    if (!lat || !lng) {
        alert('Please select a location on the map or enter Latitude and Longitude');
        return;
    }

    $.ajax({
        url: "{{ route('send.message.ajax') }}",
        type: "POST",
        data: {
            _token: '{{ csrf_token() }}',
            conversation_id: currentConvId,
            message_type: 'location',
            latitude: lat,
            longitude: lng,
            address: addr
        },
        success: function(res) {
            $('#locationModal').modal('hide');
            if (res.message) {
                res.message.conversation_id = res.message.conversation_id || currentConvId;
                handleIncomingLiveMessage({ message: res.message });
            }
        }
    });
}

// Toggle Thread Status (Open <-> Closed)
function toggleStatus() {
    const isClosed = $('#activeConvStatusBadge').text() === 'CLOSED';
    const newStatus = isClosed ? 'open' : 'closed';

    $.ajax({
        url: "{{ route('toggle.conversation.status.ajax') }}",
        type: "POST",
        data: {
            _token: '{{ csrf_token() }}',
            conversation_id: currentConvId,
            status: newStatus
        },
        success: function(res) {
            loadConversationMessages(currentConvId);
        }
    });
}

// Change Communication Channel
function changeChannel(channel) {
    $.ajax({
        url: "{{ route('change.conversation.channel.ajax') }}",
        type: "POST",
        data: {
            _token: '{{ csrf_token() }}',
            conversation_id: currentConvId,
            channel: channel
        },
        success: function(res) {
            loadConversationMessages(currentConvId);
        }
    });
}

// Delete Entire Conversation
function confirmDeleteConversation() {
    if (confirm('Are you sure you want to permanently delete this entire conversation and all its messages?')) {
        window.location.href = "/admin/communications/delete/" + currentConvId;
    }
}

// Add Participant to Thread
function addParticipant() {
    const userId = $('#addParticipantSelect').val();
    if (!userId) return;

    $.ajax({
        url: "{{ route('add.conversation.participant.ajax') }}",
        type: "POST",
        data: {
            _token: '{{ csrf_token() }}',
            conversation_id: currentConvId,
            user_id: userId
        },
        success: function(res) {
            loadConversationMessages(currentConvId);
        }
    });
}

// Remove Participant from Thread
function removeParticipant(userId) {
    $.ajax({
        url: "{{ route('remove.conversation.participant.ajax') }}",
        type: "POST",
        data: {
            _token: '{{ csrf_token() }}',
            conversation_id: currentConvId,
            user_id: userId
        },
        success: function(res) {
            loadConversationMessages(currentConvId);
        }
    });
}

// Filter Directory Items
function filterConv(filterKey, elem) {
    $('.hub-filter-pill').removeClass('active');
    $(elem).addClass('active');

    $('.conv-card-item').each(function() {
        const item = $(this);
        const status = item.data('status');
        const type = item.data('type');
        const channel = item.data('channel');

        if (filterKey === 'all') {
            item.show();
        } else if (filterKey === status || filterKey === type || filterKey === channel) {
            item.show();
        } else {
            item.hide();
        }
    });
}

// Quick Search Directory Filter
$('#convSearchInput').on('keyup', function() {
    const val = $(this).val().toLowerCase();
    $('.conv-card-item').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(val) > -1);
    });
});

// =========================================================================
// WHATSAPP-STYLE VOICE NOTE RECORDING & LIVE WAVEFORM VISUALIZER ENGINE
// =========================================================================
let mediaRecorder = null;
let audioChunks = [];
let recordStartTime = null;
let recordTimerInterval = null;
let audioCtx = null;
let analyserNode = null;
let micStreamNode = null;
let animFrameId = null;
let recordedDuration = 0;
let recordingMimeType = 'audio/webm';
let recordingFileExt = 'webm';

function getBestAudioMimeType() {
    if (typeof MediaRecorder !== 'undefined' && MediaRecorder.isTypeSupported) {
        if (MediaRecorder.isTypeSupported('audio/mp4')) {
            return { mimeType: 'audio/mp4', ext: 'mp4' };
        }
        if (MediaRecorder.isTypeSupported('audio/aac')) {
            return { mimeType: 'audio/aac', ext: 'm4a' };
        }
        if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
            return { mimeType: 'audio/webm;codecs=opus', ext: 'webm' };
        }
        if (MediaRecorder.isTypeSupported('audio/webm')) {
            return { mimeType: 'audio/webm', ext: 'webm' };
        }
        if (MediaRecorder.isTypeSupported('audio/ogg')) {
            return { mimeType: 'audio/ogg', ext: 'ogg' };
        }
    }
    return { mimeType: 'audio/webm', ext: 'webm' };
}

function startVoiceRecording() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Your browser does not support microphone voice recording.');
        return;
    }

    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(function(stream) {
            micStreamNode = stream;
            audioChunks = [];

            const bestFormat = getBestAudioMimeType();
            recordingMimeType = bestFormat.mimeType;
            recordingFileExt = bestFormat.ext;

            try {
                mediaRecorder = new MediaRecorder(stream, { mimeType: recordingMimeType });
            } catch(e) {
                console.warn('MediaRecorder with specific mimeType failed, falling back:', e);
                mediaRecorder = new MediaRecorder(stream);
                recordingMimeType = mediaRecorder.mimeType || 'audio/webm';
                recordingFileExt = (recordingMimeType.includes('mp4') || recordingMimeType.includes('aac')) ? 'mp4' : 'webm';
            }

            mediaRecorder.ondataavailable = function(e) {
                if (e.data && e.data.size > 0) {
                    audioChunks.push(e.data);
                }
            };

            mediaRecorder.start();
            recordStartTime = Date.now();

            // Reset & Start Recording Timer
            $('#recTimerText').text('00:00');
            recordTimerInterval = setInterval(function() {
                const elapsedSec = Math.floor((Date.now() - recordStartTime) / 1000);
                recordedDuration = elapsedSec;
                const mins = String(Math.floor(elapsedSec / 60)).padStart(2, '0');
                const secs = String(elapsedSec % 60).padStart(2, '0');
                $('#recTimerText').text(`${mins}:${secs}`);
            }, 1000);

            // Start Real-time Dynamic Sound Waveform Visualizer
            startAudioWaveformVisualizer(stream);

            // Fade in Live Recording Overlay
            $('#voiceRecordOverlay').fadeIn(150);
        })
        .catch(function(err) {
            console.error('Microphone Access Error:', err);
            alert('Could not access microphone for voice recording.');
        });
}

function startAudioWaveformVisualizer(stream) {
    try {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        analyserNode = audioCtx.createAnalyser();
        const source = audioCtx.createMediaStreamSource(stream);
        source.connect(analyserNode);

        analyserNode.fftSize = 64;
        const bufferLength = analyserNode.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);

        const canvas = document.getElementById('voiceWaveformCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        function drawWaveform() {
            animFrameId = requestAnimationFrame(drawWaveform);
            analyserNode.getByteFrequencyData(dataArray);

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const barWidth = (canvas.width / bufferLength) * 1.6;
            let x = 0;

            for (let i = 0; i < bufferLength; i++) {
                const barHeight = (dataArray[i] / 255) * canvas.height * 0.85;

                const grad = ctx.createLinearGradient(0, canvas.height, 0, 0);
                grad.addColorStop(0, '#06B6D4');
                grad.addColorStop(1, '#F43F5E');

                ctx.fillStyle = grad;
                ctx.fillRect(x, (canvas.height - barHeight) / 2, barWidth - 2, Math.max(barHeight, 4));

                x += barWidth + 1;
            }
        }

        drawWaveform();
    } catch(e) {
        console.warn('AudioContext visualizer error:', e);
    }
}

function cancelVoiceRecording() {
    stopRecordingCleanup();
    $('#voiceRecordOverlay').fadeOut(150);
}

function stopAndSendVoiceRecording() {
    if (!mediaRecorder) return;

    mediaRecorder.onstop = function() {
        const mimeToUse = (mediaRecorder && mediaRecorder.mimeType) ? mediaRecorder.mimeType : recordingMimeType;
        const audioBlob = new Blob(audioChunks, { type: mimeToUse });
        const audioFile = new File([audioBlob], `voice_note_${Date.now()}.${recordingFileExt}`, { type: mimeToUse });

        const data = new FormData();
        data.append('_token', '{{ csrf_token() }}');
        data.append('conversation_id', currentConvId);
        data.append('message_type', 'audio');
        data.append('content', 'Voice Note');
        data.append('duration', recordedDuration || 1);
        data.append('file_attachment', audioFile);

        $.ajax({
            url: "{{ route('send.message.ajax') }}",
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.message) {
                    res.message.conversation_id = res.message.conversation_id || currentConvId;
                    handleIncomingLiveMessage({ message: res.message });
                }
            }
        });

        stopRecordingCleanup();
        $('#voiceRecordOverlay').fadeOut(150);
    };

    mediaRecorder.stop();
}

function stopRecordingCleanup() {
    if (recordTimerInterval) clearInterval(recordTimerInterval);
    if (animFrameId) cancelAnimationFrame(animFrameId);
    if (micStreamNode) {
        micStreamNode.getTracks().forEach(track => track.stop());
    }
    if (audioCtx) {
        audioCtx.close();
    }
    mediaRecorder = null;
    audioChunks = [];
}

// =========================================================================
// WHATSAPP VOICE NOTE PLAYER ENGINE
// =========================================================================
function toggleVoicePlay(msgId) {
    const audio = document.getElementById(`audio-${msgId}`);
    const btn = document.getElementById(`btn-play-${msgId}`);
    if (!audio) return;

    $('audio').each(function() {
        if (this.id !== `audio-${msgId}`) {
            this.pause();
            const otherId = this.id.replace('audio-', '');
            $(`#btn-play-${otherId}`).html('<i class="bx bx-play"></i>');
        }
    });

    if (audio.paused) {
        const playPromise = audio.play();
        if (playPromise !== undefined) {
            playPromise.then(function() {
                if (btn) $(btn).html('<i class="bx bx-pause"></i>');
            }).catch(function(err) {
                console.error('Audio play error in Safari/Browser:', err);
                if (btn) $(btn).html('<i class="bx bx-play"></i>');
            });
        }
    } else {
        audio.pause();
        if (btn) $(btn).html('<i class="bx bx-play"></i>');
    }
}

function updateVoiceProgress(msgId) {
    const audio = document.getElementById(`audio-${msgId}`);
    const seek = document.getElementById(`seek-${msgId}`);
    const timer = document.getElementById(`vn-timer-${msgId}`);

    if (audio && seek && audio.duration) {
        const pct = (audio.currentTime / audio.duration) * 100;
        seek.value = pct;

        const curMins = Math.floor(audio.currentTime / 60);
        const curSecs = String(Math.floor(audio.currentTime % 60)).padStart(2, '0');
        const durMins = Math.floor(audio.duration / 60);
        const durSecs = String(Math.floor(audio.duration % 60)).padStart(2, '0');

        if (timer) {
            timer.innerText = `${curMins}:${curSecs} / ${durMins}:${durSecs}`;
        }
    }
}

function seekVoiceNote(msgId, val) {
    const audio = document.getElementById(`audio-${msgId}`);
    if (audio && audio.duration) {
        audio.currentTime = (val / 100) * audio.duration;
    }
}

function resetVoicePlay(msgId) {
    const btn = document.getElementById(`btn-play-${msgId}`);
    const seek = document.getElementById(`seek-${msgId}`);
    if (btn) $(btn).html('<i class="bx bx-play"></i>');
    if (seek) seek.value = 0;
}
</script>

@endsection
