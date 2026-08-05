@extends('admin.master_admin')
@section('admin')

{{-- ===============================================
     REAL-TIME SHIPMENT TRACKING MAP VIEW (REVERB)
   =============================================== --}}

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<style>
:root {
    --sh-cyan:       #06B6D4;
    --sh-cyan-light: #38BDF8;
    --sh-navy:       #0F172A;
    --sh-card:       #1E293B;
    --sh-border:     rgba(255,255,255,0.1);
    --sh-success:    #10B981;
    --sh-warning:    #F59E0B;
    --sh-danger:     #F43F5E;
    --sh-purple:     #8B5CF6;
}

.map-container-wrapper {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid var(--sh-border);
    box-shadow: 0 20px 50px rgba(0,0,0,0.4);
    background: #0F172A;
}

#shipment-map {
    width: 100%;
    height: 650px;
    z-index: 1;
    background: #0b1329;
}

.map-container-wrapper:fullscreen,
.map-container-wrapper:-webkit-full-screen,
.map-container-wrapper.is-fullscreen {
    width: 100vw !important;
    height: 100vh !important;
    border-radius: 0 !important;
    border: none !important;
    max-height: 100vh !important;
}

.map-container-wrapper:fullscreen #shipment-map,
.map-container-wrapper:-webkit-full-screen #shipment-map,
.map-container-wrapper.is-fullscreen #shipment-map {
    height: 100vh !important;
}

/* Custom dark tile styling overlay for Leaflet */
.leaflet-tile-pane {
    filter: brightness(0.85) contrast(1.15) saturate(0.9);
}

/* Overlay controls on map */
.map-overlay-controls {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* ─── Map Control Toolbar Above Map Container ─── */
.map-control-toolbar {
    background: rgba(30, 41, 59, 0.75);
    border: 1px solid var(--sh-border);
    backdrop-filter: blur(12px);
}

html.light-theme .map-control-toolbar {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
}

.map-toolbar-btn {
    background: rgba(15, 23, 42, 0.6);
    color: #F8FAFC;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 8px 16px;
    font-size: 0.83rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: pointer;
}

.map-toolbar-btn:hover {
    background: rgba(6, 182, 212, 0.18);
    border-color: #06B6D4;
    color: #38BDF8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
}

.map-toolbar-btn.active-sat-btn {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
    border-color: #10B981 !important;
    color: #ffffff !important;
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.5) !important;
}

html.light-theme .map-toolbar-btn {
    background: #F8FAFC !important;
    color: #0F172A !important;
    border-color: #CBD5E1 !important;
}

html.light-theme .map-toolbar-btn:hover {
    background: #E0F2FE !important;
    color: #0284C7 !important;
    border-color: #0284C7 !important;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.15) !important;
}

/* Status Indicator */
.connection-pill {
    background: rgba(15, 23, 42, 0.9);
    border: 1px solid rgba(16, 185, 129, 0.4);
    padding: 6px 14px;
    border-radius: 30px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
}

.pulse-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #10B981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: reverb-pulse 1.8s infinite;
}

@keyframes reverb-pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Sidebar Info Cards */
.side-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.95) 0%, rgba(15,23,42,0.98) 100%);
    border: 1px solid var(--sh-border);
    border-radius: 18px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}

.metric-box {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 14px;
    padding: 14px;
    text-align: center;
}
.metric-value {
    font-size: 1.4rem;
    font-weight: 800;
    color: #38BDF8;
}
.metric-title {
    font-size: 0.75rem;
    color: #94A3B8;
    font-weight: 700;
    text-transform: uppercase;
}

/* Timeline */
.tracking-timeline {
    position: relative;
    padding-left: 24px;
    margin-top: 15px;
}
.tracking-timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: rgba(255,255,255,0.1);
}
.timeline-item {
    position: relative;
    margin-bottom: 18px;
}
.timeline-dot {
    position: absolute;
    left: -24px;
    top: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #06B6D4;
    border: 3px solid #0F172A;
    box-shadow: 0 0 10px rgba(6,182,212,0.5);
}
.timeline-dot.active {
    background: #10B981;
    box-shadow: 0 0 12px rgba(16,185,129,0.8);
}
.timeline-content {
    background: rgba(15,23,42,0.5);
    border-radius: 10px;
    padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.05);
}

/* ─── Leaflet Smooth Marker GPU Transition ─── */
.truck-marker-icon {
    transition: transform 1.2s cubic-bezier(0.25, 1, 0.5, 1) !important;
}

.truck-vehicle-card {
    position: relative;
    width: 52px;
    height: 52px;
    background: #0F172A;
    border: 3px solid #06B6D4;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 25px rgba(6, 182, 212, 0.8), 0 8px 20px rgba(0,0,0,0.5);
}

.truck-icon-inner {
    color: #38BDF8;
    font-size: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.5s ease-out;
}

.truck-speed-pill {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.95);
    color: #38BDF8;
    border: 1px solid rgba(6, 182, 212, 0.5);
    border-radius: 20px;
    padding: 1px 8px;
    font-size: 0.7rem;
    font-weight: 800;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}

.truck-aura-ring {
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    border: 2px solid rgba(6, 182, 212, 0.4);
    animation: aura-pulse 2s infinite ease-out;
    pointer-events: none;
}

@keyframes aura-pulse {
    0% { transform: scale(0.9); opacity: 0.9; }
    100% { transform: scale(1.4); opacity: 0; }
}

.shipment-info-banner {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    border: 1px solid rgba(6,182,212,0.2) !important;
}

/* ═════════════════════════════════════════════════════════════
   LIGHT MODE OVERRIDES FOR LIVE TRACKING MAP VIEW
═════════════════════════════════════════════════════════════ */
html.light-theme .shipment-info-banner {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
}
html.light-theme .shipment-info-banner h4,
html.light-theme .shipment-info-banner .text-white,
html.light-theme .side-card .text-white,
html.light-theme .timeline-content .text-white,
html.light-theme .breadcrumb-title {
    color: #0F172A !important;
}
html.light-theme .shipment-info-banner .text-secondary,
html.light-theme .side-card .text-secondary,
html.light-theme .timeline-content .text-secondary {
    color: #64748B !important;
}
html.light-theme .shipment-info-banner .bg-dark,
html.light-theme .side-card .bg-dark {
    background-color: #F8FAFC !important;
    border-color: #CBD5E1 !important;
}
html.light-theme .btn-outline-light {
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
}
html.light-theme .btn-outline-light:hover {
    background-color: #F1F5F9 !important;
    color: #0F172A !important;
}
html.light-theme .map-container-wrapper {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
}
html.light-theme #shipment-map {
    background: #F8FAFC !important;
}
html.light-theme .leaflet-tile-pane {
    filter: none !important;
}
html.light-theme .map-btn {
    background: #FFFFFF !important;
    color: #0F172A !important;
    border-color: #CBD5E1 !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06) !important;
}
html.light-theme .connection-pill {
    background: #FFFFFF !important;
    border-color: #A7F3D0 !important;
    color: #0F172A !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06) !important;
}
html.light-theme .side-card {
    background: #FFFFFF !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
}
html.light-theme .metric-box {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
}
html.light-theme .metric-title {
    color: #64748B !important;
}
html.light-theme .metric-value {
    color: #0284C7 !important;
}
html.light-theme .timeline-content {
    background: #F8FAFC !important;
    border-color: #E2E8F0 !important;
    color: #0F172A !important;
}
html.light-theme .tracking-timeline::before {
    background: #E2E8F0 !important;
}
html.light-theme .truck-vehicle-card {
    background: #FFFFFF !important;
}
html.light-theme .truck-speed-pill {
    background: #FFFFFF !important;
    color: #0284C7 !important;
    border-color: #38BDF8 !important;
}
</style>

<div class="page-content">
    <!-- Breadcrumb Header -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-white fw-bold">Shipments</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt text-info"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('track.shipments') }}" class="text-secondary">Shipment Selection</a></li>
                    <li class="breadcrumb-item active text-info" aria-current="page">Live Map Tracking</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <div class="connection-pill">
                <span class="pulse-dot"></span>
                <span class="text-white fs-7 fw-bold" id="connection-status-text">Reverb Stream Connected</span>
            </div>
            <a href="{{ route('track.shipments') }}" class="btn btn-outline-light rounded-pill px-3">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Shipment Banner Info -->
    <div class="card mb-4 border-0 overflow-hidden shipment-info-banner" style="border-radius: 18px;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded-4 border border-info border-opacity-25 text-info">
                            <i class="bx bxs-truck fs-1"></i>
                        </div>
                        <div>
                            <span class="badge bg-info bg-opacity-25 text-info mb-1">Shipment #SHP-{{ sprintf('%04d', $shipment->id) }}</span>
                            <h4 class="fw-bold text-white mb-0">{{ $shipment->shipment_name ?? 'Custom Freight Cargo' }}</h4>
                            <small class="text-secondary">{{ $shipment->goods_description ?? 'General Goods' }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-dark bg-opacity-50 border border-white border-opacity-10">
                        <div>
                            <small class="text-secondary d-block">Customer</small>
                            <span class="text-white fw-bold">{{ $shipment->customer ? ($shipment->customer->fname . ' ' . $shipment->customer->lname) : 'Unassigned' }}</span>
                        </div>
                        <div class="text-end">
                            <small class="text-secondary d-block">Driver & Truck</small>
                            <span class="text-info fw-bold">{{ $shipment->driver ? ($shipment->driver->fname . ' ' . $shipment->driver->lname) : 'No Driver Assigned' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-flex flex-column align-items-lg-end">
                        <small class="text-secondary mb-1">Current Status</small>
                        <span class="badge bg-success bg-opacity-25 text-success fs-6 px-3 py-2 border border-success border-opacity-25 rounded-pill" id="current-status-badge">
                            <i class="bx bx-radar me-1 animate__animated animate__pulse animate__infinite"></i> {{ ucfirst($shipment->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Map & Control Drawer Grid -->
    <div class="row g-4">
        <!-- Interactive Map View -->
        <div class="col-xl-8">
            <!-- Map Controls Toolbar (Clean Horizontal Bar Above Map) -->
            <div class="map-control-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 p-2.5 rounded-4 shadow-sm">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button class="map-toolbar-btn" onclick="recenterMap()" title="Center on Vehicle">
                        <i class="bx bx-target-lock text-info fs-5"></i> <span>Center Vehicle</span>
                    </button>
                    <button class="map-toolbar-btn" onclick="fitRouteBounds()" title="Fit Full Route Bounds">
                        <i class="bx bx-map-alt text-success fs-5"></i> <span>Fit Route</span>
                    </button>
                    <button class="map-toolbar-btn" onclick="toggleTrajectoryLine()" title="Toggle Trajectory Line">
                        <i class="bx bx-line-chart text-warning fs-5"></i> <span>Trajectory</span>
                    </button>
                    <button class="map-toolbar-btn" id="btn-toggle-satellite" onclick="toggleSatelliteView()" title="Toggle Satellite View Mode">
                        <i class="bx bx-planet text-purple fs-5"></i> <span>Satellite</span>
                    </button>
                </div>
                <div>
                    <button class="map-toolbar-btn fw-bold" id="btn-toggle-fullscreen" onclick="toggleMapFullscreen()" title="Toggle Fullscreen Mode">
                        <i class="bx bx-fullscreen text-info fs-5" id="fullscreen-icon"></i> <span id="fullscreen-text">Fullscreen</span>
                    </button>
                </div>
            </div>

            <div class="map-container-wrapper">
                <div id="shipment-map"></div>
            </div>
        </div>

        <!-- Sidebar Telematics & Controls -->
        <div class="col-xl-4">
            <!-- Real-time Metrics Card -->
            <div class="side-card">
                <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bx bx-tachometer text-info"></i> Live tracking
                </h6>
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <div class="metric-box">
                            <div class="metric-value" id="metric-speed">0 <small class="fs-7">km/h</small></div>
                            <div class="metric-title">SPEED</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 rounded-3 bg-dark bg-opacity-40 border border-white border-opacity-10">
                    <div class="d-flex justify-content-between text-secondary fs-7 mb-1">
                        <span>Latest GPS Received:</span>
                        <span class="text-info font-monospace" id="latest-coords-text">
                            {{ number_format($currentLat, 5) }}, {{ number_format($currentLng, 5) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary fs-7">
                        <span>Update Timestamp:</span>
                        <span class="text-white" id="latest-time-text">Live Now</span>
                    </div>
                </div>
            </div>

            <!-- Route Controls -->
            <div class="side-card border-info border-opacity-30">
                <div class="d-flex flex-column gap-2">
                    <button class="btn btn-outline-warning btn-sm w-100 rounded-3" id="btn-auto-sim" onclick="toggleAutoRouteSimulation()">
                        <i class="bx bx-play-circle me-1"></i> Auto-Simulate Route Movement
                    </button>
                    <button class="btn btn-outline-danger btn-sm w-100 rounded-3" onclick="confirmClearTrackingHistory()">
                        <i class="bx bx-trash me-1"></i> Clear Tracking History & Reset Route
                    </button>
                </div>
            </div>

            <!-- History Timeline Log Drawer -->
            <div class="side-card">
                <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bx bx-history text-success"></i> Tracking Event History
                </h6>
                <div class="tracking-timeline" id="tracking-timeline-container">
                    @forelse($shipment->trackings as $tracking)
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $loop->first ? 'active' : '' }}"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-white fs-7">{{ $tracking->status }}</span>
                                    <small class="text-secondary fs-7">{{ $tracking->created_at ? $tracking->created_at->format('H:i') : '' }}</small>
                                </div>
                                <small class="text-secondary d-block">{{ $tracking->location_description ?? 'Milestone Location Updated' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary fs-7 text-center py-3">No prior tracking logs recorded</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================
// REAL-TIME LEAFLET MAP & REVERB JS ENGINE
// ==========================================

let map, driverMarker, pickupMarker, dropoffMarker, trajectoryPolyline;
let streetTileLayer, satelliteTileLayer;
let isSatelliteMode = false;
let trajectoryCoords = [];
let isSimulating = false;
let simInterval = null;

// Initial Coordinates Passed from Backend
const pickupCoords  = [{{ $pickupLat }}, {{ $pickupLng }}];
const dropoffCoords = [{{ $dropoffLat }}, {{ $dropoffLng }}];
let currentCoords   = [{{ $currentLat }}, {{ $currentLng }}];
const shipmentId    = {{ $shipment->id }};

document.addEventListener("DOMContentLoaded", function () {
    initLeafletMap();
    initReverbEchoListener();
});

/**
 * Initialize Leaflet Interactive Map
 */
function initLeafletMap() {
    // 1. Instantiate Map centered at driver current coordinates
    map = L.map('shipment-map', {
        center: currentCoords,
        zoom: 12,
        zoomControl: true
    });

    // 2. Tile Layers — Streets & High-Res Esri Satellite
    streetTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors | SALASIL Logistics'
    });

    satelliteTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles © Esri — Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and GIS User Community'
    });

    // Add default Streets layer
    streetTileLayer.addTo(map);

    // 3. Custom SVG Icons
    const pickupIcon = L.divIcon({
        className: 'custom-pin-pickup',
        html: `<div style="background:#10B981; color:#fff; width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid #fff; box-shadow:0 0 15px rgba(16,185,129,0.7);"><i class="bx bx-store-alt fs-4"></i></div>`,
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    const dropoffIcon = L.divIcon({
        className: 'custom-pin-dropoff',
        html: `<div style="background:#F43F5E; color:#fff; width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid #fff; box-shadow:0 0 15px rgba(244,63,94,0.7);"><i class="bx bx-flag fs-4"></i></div>`,
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    const truckIcon = L.divIcon({
        className: 'truck-marker-icon',
        html: `
            <div class="truck-vehicle-card">
                <div class="truck-speed-pill" id="truck-tag-speed">0 km/h</div>
                <div class="truck-icon-inner" id="truck-icon-inner">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L19 21L12 17L5 21L12 2Z" fill="#38BDF8" stroke="#FFFFFF" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="truck-aura-ring"></div>
            </div>
        `,
        iconSize: [52, 52],
        iconAnchor: [26, 26]
    });

    // 4. Add Pickup & Dropoff Markers
    pickupMarker = L.marker(pickupCoords, { icon: pickupIcon }).addTo(map)
        .bindPopup(`<div class="text-dark p-1"><strong>Pickup Location</strong><br>${'{{ addslashes($shipment->pickup_address) }}'}</div>`);

    dropoffMarker = L.marker(dropoffCoords, { icon: dropoffIcon }).addTo(map)
        .bindPopup(`<div class="text-dark p-1"><strong>Delivery Destination</strong><br>${'{{ addslashes($shipment->dropoff_address) }}'}</div>`);

    // 5. Add Driver Truck Marker
    driverMarker = L.marker(currentCoords, { icon: truckIcon }).addTo(map)
        .bindPopup(`<div class="text-dark p-1"><strong>Vehicle Position</strong><br>Shipment #SHP-${shipmentId}</div>`);

    // 6. Draw Initial Breadcrumb Trajectory Path
    @if(count($shipment->trackingLogs) > 0)
        @foreach($shipment->trackingLogs as $log)
            trajectoryCoords.push([{{ $log->latitude }}, {{ $log->longitude }}]);
        @endforeach
    @else
        trajectoryCoords = [pickupCoords, currentCoords];
    @endif

    trajectoryPolyline = L.polyline(trajectoryCoords, {
        color: '#06B6D4',
        weight: 5,
        opacity: 0.85,
        dashArray: '8, 8'
    }).addTo(map);

    // Fit Map bounds to show route
    fitRouteBounds();
}

/**
 * Toggle Satellite View Mode (Esri World Imagery)
 */
function toggleSatelliteView() {
    const btn = document.getElementById('btn-toggle-satellite');
    const mapWrapper = document.querySelector('.map-container-wrapper');

    if (isSatelliteMode) {
        map.removeLayer(satelliteTileLayer);
        streetTileLayer.addTo(map);
        isSatelliteMode = false;

        btn.innerHTML = `<i class="bx bx-planet fs-5"></i> Satellite Imagery`;
        btn.classList.remove('active-sat-btn');
        if (mapWrapper) mapWrapper.classList.remove('satellite-active');
    } else {
        map.removeLayer(streetTileLayer);
        satelliteTileLayer.addTo(map);
        isSatelliteMode = true;

        btn.innerHTML = `<i class="bx bx-map fs-5"></i> Street Map`;
        btn.classList.add('active-sat-btn');
        if (mapWrapper) mapWrapper.classList.add('satellite-active');
    }
}

/**
 * Recenter map to current truck position
 */
function recenterMap() {
    map.panTo(currentCoords, { animate: true, duration: 1 });
}

/**
 * Fit map to encompass pickup, dropoff and driver
 */
function fitRouteBounds() {
    const bounds = L.latLngBounds([pickupCoords, dropoffCoords, currentCoords]);
    map.fitBounds(bounds, { padding: [50, 50] });
}

/**
 * Toggle trajectory polyline visibility
 */
function toggleTrajectoryLine() {
    if (map.hasLayer(trajectoryPolyline)) {
        map.removeLayer(trajectoryPolyline);
    } else {
        trajectoryPolyline.addTo(map);
    }
}

/**
 * Toggle Map Fullscreen Mode
 */
function toggleMapFullscreen() {
    const mapWrapper = document.querySelector('.map-container-wrapper');
    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
        if (mapWrapper.requestFullscreen) {
            mapWrapper.requestFullscreen();
        } else if (mapWrapper.webkitRequestFullscreen) {
            mapWrapper.webkitRequestFullscreen();
        } else if (mapWrapper.msRequestFullscreen) {
            mapWrapper.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

document.addEventListener('fullscreenchange', handleFullscreenChange);
document.addEventListener('webkitfullscreenchange', handleFullscreenChange);

function handleFullscreenChange() {
    const mapWrapper = document.querySelector('.map-container-wrapper');
    const icon = document.getElementById('fullscreen-icon');
    const text = document.getElementById('fullscreen-text');
    const isFS = !!(document.fullscreenElement || document.webkitFullscreenElement);

    if (isFS) {
        if (mapWrapper) mapWrapper.classList.add('is-fullscreen');
        if (icon) icon.className = 'bx bx-exit-fullscreen fs-5';
        if (text) text.innerText = 'Exit Fullscreen';
    } else {
        if (mapWrapper) mapWrapper.classList.remove('is-fullscreen');
        if (icon) icon.className = 'bx bx-fullscreen fs-5';
        if (text) text.innerText = 'Fullscreen';
    }

    setTimeout(() => {
        if (map) map.invalidateSize();
    }, 200);
}

/**
 * Listen to Laravel Reverb WebSockets via Echo
 */
function initReverbEchoListener() {
    if (typeof window.Echo !== 'undefined') {
        console.log('Connecting to Reverb private channel: shipment-tracking.' + shipmentId);
        
        window.Echo.private('shipment-tracking.' + shipmentId)
            .listen('.shipment.location.updated', (event) => {
                console.log('🚀 Live Location Update Event Received from Reverb:', event);
                onLiveLocationReceived(event);
            })
            .listen('.shipment.status.updated', (event) => {
                console.log('📢 Status Update Event Received from Reverb:', event);
                onLiveStatusReceived(event);
            });
            
        document.getElementById('connection-status-text').innerText = "Reverb Stream Connected";
    } else {
        console.warn('Echo is not defined on window. Reverb fallback active.');
    }
}

/**
 * Calculate accurate Geodesic Bearing from Point A to Point B (0° to 360°)
 */
function calculateGeodesicBearing(lat1, lng1, lat2, lng2) {
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const lat1Rad = lat1 * Math.PI / 180;
    const lat2Rad = lat2 * Math.PI / 180;
    const y = Math.sin(dLng) * Math.cos(lat2Rad);
    const x = Math.cos(lat1Rad) * Math.sin(lat2Rad) - Math.sin(lat1Rad) * Math.cos(lat2Rad) * Math.cos(dLng);
    let bearing = Math.atan2(y, x) * 180 / Math.PI;
    return (bearing + 360) % 360;
}

/**
 * Handle Live Location Event Payload — Native Butter-Smooth Movement
 */
function onLiveLocationReceived(data) {
    const newLat = parseFloat(data.latitude);
    const newLng = parseFloat(data.longitude);
    const speed = data.speed || 0;

    const prevLat = currentCoords[0];
    const prevLng = currentCoords[1];

    currentCoords = [newLat, newLng];

    // Accurate Bearing calculation
    let bearing = data.heading;
    if (!bearing && (Math.abs(newLat - prevLat) > 0.00001 || Math.abs(newLng - prevLng) > 0.00001)) {
        bearing = calculateGeodesicBearing(prevLat, prevLng, newLat, newLng);
    }
    bearing = bearing || 0;

    // Rotate SVG arrow to face exact movement heading
    const truckInner = document.getElementById('truck-icon-inner');
    if (truckInner) {
        truckInner.style.transform = `rotate(${bearing}deg)`;
    }

    // Update Floating Speed Pill
    const speedTag = document.getElementById('truck-tag-speed');
    if (speedTag) {
        speedTag.innerText = `${Math.round(speed)} km/h`;
    }

    // Move Driver Marker natively — CSS transition handles butter-smooth 60fps sliding!
    driverMarker.setLatLng(currentCoords);

    // Append to Trajectory Line
    trajectoryCoords.push(currentCoords);
    trajectoryPolyline.setLatLngs(trajectoryCoords);

    // Update UI Telematics Cards
    const speedElem = document.getElementById('metric-speed');
    if (speedElem) speedElem.innerHTML = `${Math.round(speed)} <small class="fs-7">km/h</small>`;
    const bearingElem = document.getElementById('metric-bearing');
    if (bearingElem) bearingElem.innerText = `${Math.round(bearing)}°`;
    const pointsElem = document.getElementById('metric-points');
    if (pointsElem) pointsElem.innerText = trajectoryCoords.length;
    const coordsElem = document.getElementById('latest-coords-text');
    if (coordsElem) coordsElem.innerText = `${newLat.toFixed(5)}, ${newLng.toFixed(5)}`;
    const timeElem = document.getElementById('latest-time-text');
    if (timeElem) timeElem.innerText = data.timestamp || new Date().toLocaleTimeString('en-US');

    // Pan map gently only if truck moves outside view
    if (!map.getBounds().contains(currentCoords)) {
        map.panTo(currentCoords, { animate: true, duration: 1 });
    }

    // Append Live Item to Tracking Event History Timeline
    const timelineContainer = document.getElementById('tracking-timeline-container');
    if (timelineContainer) {
        // Clear placeholder text if present
        if (timelineContainer.children.length === 1 && timelineContainer.children[0].classList.contains('text-center')) {
            timelineContainer.innerHTML = '';
        }

        // Deactivate previous active dots
        const activeDots = timelineContainer.querySelectorAll('.timeline-dot.active');
        activeDots.forEach(dot => dot.classList.remove('active'));

        const timeString = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const locationDesc = data.locationDescription || `GPS Point #${trajectoryCoords.length} Recorded`;
        const statusText = data.status || 'Live GPS Update';

        const timelineHtml = `
            <div class="timeline-item">
                <div class="timeline-dot active"></div>
                <div class="timeline-content border-info border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-info fs-7"><i class="bx bx-radar me-1"></i> ${statusText}</span>
                        <small class="text-secondary fs-7">${timeString}</small>
                    </div>
                    <small class="text-white d-block fw-semibold">${locationDesc}</small>
                    <small class="text-secondary font-monospace d-block fs-7 mt-1">Lat: ${newLat.toFixed(5)}, Lng: ${newLng.toFixed(5)} • ${Math.round(speed)} km/h</small>
                </div>
            </div>
        `;
        timelineContainer.insertAdjacentHTML('afterbegin', timelineHtml);
    }

    playBeepSound();
}

/**
 * Handle Live Status Event Payload
 */
function onLiveStatusReceived(data) {
    if (data.status) {
        document.getElementById('current-status-badge').innerHTML = `<i class="bx bx-radar me-1"></i> ${data.status}`;
        
        // Add item to timeline
        const container = document.getElementById('tracking-timeline-container');
        const itemHtml = `
            <div class="timeline-item">
                <div class="timeline-dot active"></div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-white fs-7">${data.status}</span>
                        <small class="text-secondary fs-7">${new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'})}</small>
                    </div>
                    <small class="text-secondary d-block">${data.locationDescription || 'Tracking status updated'}</small>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('afterbegin', itemHtml);
    }
}

/**
 * Handle Manual Simulated Location Form Submission
 */
function handleSendSimulatedLocation(e) {
    if (e) e.preventDefault();
    const elLat = document.getElementById('sim-lat');
    const elLng = document.getElementById('sim-lng');
    const elSpeed = document.getElementById('sim-speed');
    const elHeading = document.getElementById('sim-heading');
    if (!elLat || !elLng) return;
    const lat = elLat.value;
    const lng = elLng.value;
    const speed = elSpeed ? elSpeed.value : 0;
    const heading = elHeading ? elHeading.value : 0;

    fetch("{{ route('shipment.track.update-location') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            shipment_id: shipmentId,
            latitude: lat,
            longitude: lng,
            speed: speed,
            heading: heading,
            location_description: 'Simulation Location Update'
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            onLiveLocationReceived({
                latitude: lat,
                longitude: lng,
                speed: speed,
                heading: heading,
                timestamp: new Date().toLocaleTimeString('en-US')
            });
        }
    })
    .catch(err => console.error('Sim error:', err));
}

/**
 * Automatic Route Simulation along path from Pickup to Dropoff
 */
function toggleAutoRouteSimulation() {
    const btn = document.getElementById('btn-auto-sim');
    if (isSimulating) {
        clearInterval(simInterval);
        isSimulating = false;
        btn.innerHTML = `<i class="bx bx-play-circle me-1"></i> Auto-Simulate Route Movement`;
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-warning');
        return;
    }

    isSimulating = true;
    btn.innerHTML = `<i class="bx bx-stop-circle me-1"></i> Stop Live Simulation`;
    btn.classList.remove('btn-outline-warning');
    btn.classList.add('btn-danger');

    // Create 40 smooth interpolated steps along the route
    let step = 0;
    const stepsCount = 40;
    const startLat = currentCoords[0];
    const startLng = currentCoords[1];
    const targetLat = dropoffCoords[0];
    const targetLng = dropoffCoords[1];

    simInterval = setInterval(() => {
        step++;
        if (step > stepsCount) {
            clearInterval(simInterval);
            isSimulating = false;
            btn.innerHTML = `<i class="bx bx-play-circle me-1"></i> Auto-Simulate Route Movement`;
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-outline-warning');
            return;
        }

        const ratio = step / stepsCount;
        const simLat = startLat + (targetLat - startLat) * ratio;
        const simLng = startLng + (targetLng - startLng) * ratio;
        const simSpeed = Math.floor(65 + Math.sin(step) * 15);
        const simHeading = Math.round(calculateGeodesicBearing(startLat, startLng, targetLat, targetLng));

        const simLatElem = document.getElementById('sim-lat');
        if (simLatElem) simLatElem.value = simLat.toFixed(6);
        const simLngElem = document.getElementById('sim-lng');
        if (simLngElem) simLngElem.value = simLng.toFixed(6);

        fetch("{{ route('shipment.track.update-location') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                shipment_id: shipmentId,
                latitude: simLat,
                longitude: simLng,
                speed: simSpeed,
                heading: simHeading < 0 ? simHeading + 360 : simHeading,
                location_description: `Reverb Telematics Simulation — Step ${step}/${stepsCount}`
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.status === 'success') {
                onLiveLocationReceived({
                    latitude: simLat,
                    longitude: simLng,
                    speed: simSpeed,
                    heading: simHeading < 0 ? simHeading + 360 : simHeading,
                    timestamp: new Date().toLocaleTimeString('en-US')
                });
            }
        })
        .catch(err => console.error('Auto-sim update error:', err));
    }, 1200);
}

/**
 * Clear tracking logs and reset route trajectory
 */
function confirmClearTrackingHistory() {
    if (!confirm("Are you sure you want to clear all tracking history logs for this shipment and reset to start point?")) {
        return;
    }

    if (isSimulating) {
        toggleAutoRouteSimulation();
    }

    fetch("{{ route('shipment.track.clear-logs', $shipment->id) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Reset coordinates to pickup location
            currentCoords = [...pickupCoords];
            driverMarker.setLatLng(currentCoords);

            // Reset trajectory polyline
            trajectoryCoords = [pickupCoords];
            trajectoryPolyline.setLatLngs(trajectoryCoords);

            // Reset UI Telematics
            const rSpeed = document.getElementById('metric-speed'); if (rSpeed) rSpeed.innerHTML = `0 <small class="fs-7">km/h</small>`;
            const rBearing = document.getElementById('metric-bearing'); if (rBearing) rBearing.innerText = `0°`;
            const rPoints = document.getElementById('metric-points'); if (rPoints) rPoints.innerText = 1;
            const rCoords = document.getElementById('latest-coords-text'); if (rCoords) rCoords.innerText = `${pickupCoords[0].toFixed(5)}, ${pickupCoords[1].toFixed(5)}`;
            const rSimLat = document.getElementById('sim-lat'); if (rSimLat) rSimLat.value = pickupCoords[0];
            const rSimLng = document.getElementById('sim-lng'); if (rSimLng) rSimLng.value = pickupCoords[1];

            // Reset timeline container
            document.getElementById('tracking-timeline-container').innerHTML = `
                <div class="text-secondary fs-7 text-center py-3">Tracking history logs reset successfully</div>
            `;

            map.panTo(pickupCoords);

            alert(data.message);
        }
    })
    .catch(err => {
        console.error('Error clearing tracking logs:', err);
        alert('An error occurred while clearing tracking logs.');
    });
}

/**
 * Web Audio API synthesize subtle ping sound
 */
function playBeepSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.value = 880;
        gain.gain.setValueAtTime(0.05, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.15);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.15);
    } catch(e) {}
}
</script>

@endsection
