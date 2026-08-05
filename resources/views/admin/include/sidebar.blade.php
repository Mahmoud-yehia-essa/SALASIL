<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-white rounded-3 px-2 py-1 d-flex align-items-center justify-content-center shadow-sm" style="height: 38px;">
                <img src="{{ asset('backend/assets/images/salasil-logo.svg') }}" style="max-height: 30px; width: auto;" alt="SALASIL Logo">
            </div>
            <h4 class="logo-text">SALASIL</h4>
        </div>
        <div class="toggle-icon ms-auto cursor-pointer" title="Toggle Menu">
            <i class='bx bx-x fs-3 text-info'></i>
        </div>
    </div>
    
    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li class="menu-label">Core Navigation</li>
        
        <li>
            <a href="{{ route('dashboard') }}">
                <div class="parent-icon"><i class='bx bx-grid-alt'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        <li class="menu-label">Fleet & Operations</li>
        
        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-user-check'></i></div>
                <div class="menu-title">Clients</div>
            </a>
            <ul>
                <li><a href="{{ route('add.user') }}"><i class='bx bx-radio-circle'></i>Add New Client</a></li>
                <li><a href="{{ route('all.owners') }}"><i class='bx bx-radio-circle'></i>All Clients</a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-truck'></i></div>
                <div class="menu-title">Trucks</div>
            </a>
            <ul>
                <li><a href="{{ route('add.truck.brand') }}"><i class='bx bx-radio-circle'></i>Add Brand</a></li>
                <li><a href="{{ route('all.truck.brands') }}"><i class='bx bx-radio-circle'></i>All Brands</a></li>
                <li><a href="{{ route('add.truck.model') }}"><i class='bx bx-radio-circle'></i>Add Model</a></li>
                <li><a href="{{ route('all.truck.models') }}"><i class='bx bx-radio-circle'></i>All Models</a></li>
                <li><a href="{{ route('add.truck.type') }}"><i class='bx bx-radio-circle'></i>Add Truck Type</a></li>
                <li><a href="{{ route('all.truck.types') }}"><i class='bx bx-radio-circle'></i>All Truck Types</a></li>
                <li><a href="{{ route('add.truck.sub.type') }}"><i class='bx bx-radio-circle'></i>Add Sub-Type</a></li>
                <li><a href="{{ route('all.truck.sub.types') }}"><i class='bx bx-radio-circle'></i>All Sub-Types</a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-link-alt'></i></div>
                <div class="menu-title">Driver & Truck</div>
            </a>
            <ul>
                <li><a href="{{ route('add.driver.truck') }}"><i class='bx bx-radio-circle'></i>Assign Truck to Driver</a></li>
                <li><a href="{{ route('all.driver.trucks') }}"><i class='bx bx-radio-circle'></i>All Driver Trucks</a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-globe'></i></div>
                <div class="menu-title">Countries & Cities</div>
            </a>
            <ul>
                <li><a href="{{ route('add.country') }}"><i class='bx bx-radio-circle'></i>Add Country</a></li>
                <li><a href="{{ route('all.countries') }}"><i class='bx bx-radio-circle'></i>All Countries</a></li>
                <li><a href="{{ route('add.city') }}"><i class='bx bx-radio-circle'></i>Add City</a></li>
                <li><a href="{{ route('all.cities') }}"><i class='bx bx-radio-circle'></i>All Cities</a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-package'></i></div>
                <div class="menu-title">Shipments</div>
            </a>
            <ul>
                <li><a href="{{ route('add.shipment') }}"><i class='bx bx-radio-circle'></i>Add New Shipment</a></li>
                <li><a href="{{ route('all.shipments') }}"><i class='bx bx-radio-circle'></i>All Shipments</a></li>
                <li><a href="{{ route('track.shipments') }}"><i class='bx bx-radar text-danger'></i>Shipment Tracking</a></li>
                <li><a href="{{ route('hscode.lookup') }}"><i class='bx bx-barcode text-info'></i>Shipment HS code</a></li>
                <li><a href="{{ route('add.shipment.type') }}"><i class='bx bx-radio-circle'></i>Add Shipment Type</a></li>
                <li><a href="{{ route('all.shipment.types') }}"><i class='bx bx-radio-circle'></i>All Shipment Types</a></li>
                <li><a href="{{ route('add.shipment.nature') }}"><i class='bx bx-radio-circle'></i>Add Shipment Nature</a></li>
                <li><a href="{{ route('all.shipment.natures') }}"><i class='bx bx-radio-circle'></i>All Shipment Natures</a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-send'></i></div>
                <div class="menu-title">Driver Invitations</div>
            </a>
            <ul>
                <li><a href="{{ route('add.shipment.invitation') }}"><i class='bx bx-radio-circle'></i>Invite Driver</a></li>
                <li><a href="{{ route('all.shipment.invitations') }}"><i class='bx bx-radio-circle'></i>All Invitations</a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-calendar-event'></i></div>
                <div class="menu-title">Scheduled Trips & Routes</div>
            </a>
            <ul>
                <li><a href="{{ route('add.route') }}"><i class='bx bx-radio-circle'></i>Add Fixed Route</a></li>
                <li><a href="{{ route('all.routes') }}"><i class='bx bx-radio-circle'></i>All Routes</a></li>
                <li><a href="{{ route('add.scheduled.trip') }}"><i class='bx bx-radio-circle'></i>Add Scheduled Trip</a></li>
                <li><a href="{{ route('all.scheduled.trips') }}"><i class='bx bx-radio-circle'></i>All Scheduled Trips</a></li>
            </ul>
        </li>

        <li class="menu-label">Financial & Billing System</li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-receipt'></i></div>
                <div class="menu-title">Invoices & Billing</div>
            </a>
            <ul>
                <li><a href="{{ route('all.invoices') }}"><i class='bx bx-radio-circle'></i>All Invoices</a></li>
                <li><a href="{{ route('add.payment') }}"><i class='bx bx-radio-circle'></i>Record Payment</a></li>
                <li><a href="{{ route('all.payments') }}"><i class='bx bx-radio-circle'></i>All Payments</a></li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow" aria-expanded="false">
                <div class="parent-icon"><i class='bx bx-wallet'></i></div>
                <div class="menu-title">Wallets & Balances</div>
            </a>
            <ul>
                <li><a href="{{ route('add.wallet.transaction') }}"><i class='bx bx-radio-circle'></i>Deposit / Withdraw</a></li>
                <li><a href="{{ route('all.wallets') }}"><i class='bx bx-radio-circle'></i>All User Wallets</a></li>
            </ul>
        </li>

        <li class="menu-label">Communication</li>

        <li>
            <a href="{{ route('all.conversations') }}">
                <div class="parent-icon"><i class='bx bx-conversation'></i></div>
                <div class="menu-title">Direct Chat & System</div>
            </a>
        </li>
    </ul>
    <!--end navigation-->
</div>

<style>
/* ─── Active MetisMenu Parent & Sub-item Persistent Styling ─── */
.metismenu .mm-active > a.has-arrow {
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.18) 0%, rgba(15, 23, 42, 0.95) 100%) !important;
    border: 2px solid #06B6D4 !important;
    border-radius: 14px !important;
    color: #38BDF8 !important;
    box-shadow: 0 4px 20px rgba(6, 182, 212, 0.3) !important;
}

.metismenu .mm-active > a.has-arrow .parent-icon,
.metismenu .mm-active > a.has-arrow .menu-title {
    color: #38BDF8 !important;
    font-weight: 700 !important;
}

.metismenu ul.mm-show {
    display: block !important;
}

/* Active Sub-item Pill */
.metismenu ul li.mm-active > a,
.metismenu ul li a.active {
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.25) 0%, rgba(2, 132, 199, 0.35) 100%) !important;
    border-radius: 12px !important;
    color: #38BDF8 !important;
    font-weight: 700 !important;
    box-shadow: 0 4px 15px rgba(6, 182, 212, 0.25) !important;
}

.metismenu ul li.mm-active > a i,
.metismenu ul li a.active i {
    color: #38BDF8 !important;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var currentUrl = window.location.href.split('#')[0].split('?')[0];
    if (currentUrl.endsWith('/') && currentUrl.length > 1) {
        currentUrl = currentUrl.slice(0, -1);
    }

    var links = document.querySelectorAll("#menu a");
    links.forEach(function (link) {
        var href = link.getAttribute("href");
        if (!href || href === "javascript:;" || href === "#") return;

        var cleanHref = href.split('#')[0].split('?')[0];
        if (cleanHref.endsWith('/') && cleanHref.length > 1) {
            cleanHref = cleanHref.slice(0, -1);
        }

        if (cleanHref === currentUrl) {
            // Highlight sub-link
            link.classList.add("active");
            var li = link.closest("li");
            if (li) li.classList.add("mm-active");

            // Expand all parent ULs and LIs
            var parentUl = link.closest("ul");
            while (parentUl && parentUl.id !== "menu") {
                parentUl.classList.add("mm-show");
                var parentLi = parentUl.closest("li");
                if (parentLi) {
                    parentLi.classList.add("mm-active");
                    var anchor = parentLi.querySelector("a.has-arrow");
                    if (anchor) {
                        anchor.setAttribute("aria-expanded", "true");
                    }
                }
                parentUl = parentLi ? parentLi.closest("ul") : null;
            }
        }
    });
});
</script>
