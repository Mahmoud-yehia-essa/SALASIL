<!doctype html>
<html lang="en" class="light-theme" dir="ltr">

<head>
    <script>
        (function() {
            var t = localStorage.getItem('salasil_theme') || 'light';
            if (t === 'dark') {
                document.documentElement.classList.remove('light-theme');
                document.documentElement.classList.add('dark-theme');
            } else {
                document.documentElement.classList.remove('dark-theme');
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SALASIL - Logistics Control Panel</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Ionicons & Icons -->
    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js" type="module"></script>
    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js" nomodule></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" />

    <!-- Plugins CSS -->
    <link href="{{ asset('backend/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet"/>
    <link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
    <link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('backend/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <script>
        window.paceOptions = {
            ajax: false,
            document: false,
            eventLag: false
        };
    </script>
    <link href="{{ asset('backend/assets/css/pace.min.css') }}" rel="stylesheet"/>
    <script src="{{ asset('backend/assets/js/pace.min.js') }}"></script>
    <!-- jQuery Library -->
    <script src="{{ asset('backend/assets/js/jquery.min.js') }}"></script>

    <!-- Bootstrap & App CSS -->
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/dark-theme.css') }}"/>
    <link rel="stylesheet" href="{{ asset('backend/assets/css/header-colors.css') }}"/>

    <!-- Select2 & Toastr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <!-- Laravel Reverb & Echo Scripts -->
    @vite(['resources/js/app.js'])

    <style>
        .pace, .pace-progress, .pace-activity {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        :root, html.dark-theme {
            --salasil-navy: #16284F;
            --salasil-navy-dark: #0A142F;
            --salasil-cyan: #06B6D4;
            --salasil-cyan-light: #38BDF8;
            --salasil-teal: #14B8A6;
            --salasil-accent: #0284C7;
            --salasil-bg: #0F172A;
            --salasil-sidebar-bg: #0F172A;
            --salasil-topbar-bg: rgba(15, 23, 42, 0.95);
            --salasil-card-bg: rgba(30, 41, 59, 0.75);
            --salasil-border: rgba(255, 255, 255, 0.08);
            --salasil-text-main: #F8FAFC;
            --salasil-text-muted: #94A3B8;
        }

        html.light-theme {
            --salasil-navy: #F8FAFC;
            --salasil-navy-dark: #E2E8F0;
            --salasil-cyan: #0284C7;
            --salasil-cyan-light: #0369A1;
            --salasil-teal: #0D9488;
            --salasil-accent: #0284C7;
            --salasil-bg: #F8FAFC;
            --salasil-sidebar-bg: #FFFFFF;
            --salasil-topbar-bg: rgba(255, 255, 255, 0.95);
            --salasil-card-bg: #FFFFFF;
            --salasil-border: #E2E8F0;
            --salasil-text-main: #0F172A;
            --salasil-text-muted: #64748B;
        }

        /* Full LTR Layout & Direction Enforcement */
        html[dir="ltr"], html[dir="ltr"] body, body {
            direction: ltr !important;
            text-align: left !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background-color: var(--salasil-bg) !important;
            color: var(--salasil-text-main) !important;
        }

        /* ═════════════════════════════════════════════════════════════
           COMPREHENSIVE LIGHT THEME OVERRIDES FOR ENTIRE DASHBOARD
        ═════════════════════════════════════════════════════════════ */
        html.light-theme body,
        html.light-theme .wrapper,
        html.light-theme .page-wrapper,
        html.light-theme .page-content {
            background-color: #F8FAFC !important;
            color: #0F172A !important;
        }

        html.light-theme .sidebar-wrapper,
        html.light-theme .sidebar-wrapper .sidebar-header {
            background: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.05) !important;
        }

        html.light-theme .sidebar-wrapper .metismenu a {
            color: #475569 !important;
        }

        html.light-theme .sidebar-wrapper .metismenu a:hover {
            color: #0284C7 !important;
            background: rgba(2, 132, 199, 0.08) !important;
        }

        html.light-theme .sidebar-wrapper .metismenu .menu-label {
            color: #94A3B8 !important;
        }

        html.light-theme .topbar {
            background: rgba(255, 255, 255, 0.95) !important;
            border-bottom: 1px solid #E2E8F0 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
        }

        html.light-theme .search-bar-box .search-input {
            background-color: #F1F5F9 !important;
            border-color: #CBD5E1 !important;
            color: #0F172A !important;
        }

        html.light-theme .topbar .user-name {
            color: #0F172A !important;
        }

        html.light-theme .topbar .designattion {
            color: #64748B !important;
        }

        html.light-theme .card,
        html.light-theme .tp-card,
        html.light-theme .side-card,
        html.light-theme .dash-card,
        html.light-theme .stat-card {
            background: #FFFFFF !important;
            color: #0F172A !important;
            border-color: #E2E8F0 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        }

        html.light-theme .card-header,
        html.light-theme .tp-card-header {
            background: #F8FAFC !important;
            border-bottom-color: #E2E8F0 !important;
        }

        html.light-theme h1,
        html.light-theme h2,
        html.light-theme h3,
        html.light-theme h4,
        html.light-theme h5,
        html.light-theme h6,
        html.light-theme .header-title,
        html.light-theme .card-title,
        html.light-theme .metric-value {
            color: #0F172A !important;
        }

        html.light-theme .text-white {
            color: #0F172A !important;
        }

        html.light-theme .text-slate-400,
        html.light-theme .text-muted,
        html.light-theme .text-secondary {
            color: #64748B !important;
        }

        html.light-theme .form-control,
        html.light-theme .form-select,
        html.light-theme .input-group-text {
            background: #F8FAFC !important;
            color: #0F172A !important;
            border-color: #CBD5E1 !important;
        }

        html.light-theme .form-control:focus,
        html.light-theme .form-select:focus {
            background: #FFFFFF !important;
            border-color: #0284C7 !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
            color: #0F172A !important;
        }

        html.light-theme .form-select option {
            background: #FFFFFF !important;
            color: #0F172A !important;
        }

        html.light-theme .table {
            color: #0F172A !important;
            border-color: #E2E8F0 !important;
        }

        html.light-theme .table th,
        html.light-theme .table thead th {
            background-color: #F1F5F9 !important;
            color: #334155 !important;
            border-color: #E2E8F0 !important;
        }

        html.light-theme .table td {
            border-color: #E2E8F0 !important;
            color: #0F172A !important;
        }

        html.light-theme .table-striped tbody tr:nth-of-type(odd) {
            background-color: #F8FAFC !important;
        }

        html.light-theme .table-hover tbody tr:hover {
            background-color: #F1F5F9 !important;
        }

        html.light-theme .dropdown-menu {
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }

        html.light-theme .dropdown-item {
            color: #0F172A !important;
        }

        html.light-theme .dropdown-item:hover {
            background-color: #F1F5F9 !important;
            color: #0284C7 !important;
        }

        html.light-theme .modal-content {
            background-color: #FFFFFF !important;
            color: #0F172A !important;
            border-color: #E2E8F0 !important;
        }

        html.light-theme .modal-header,
        html.light-theme .modal-footer {
            border-color: #E2E8F0 !important;
        }

        /* ═════════════════════════════════════════════════════════════
           DATATABLES & TABLES DARK & LIGHT MODE HIGH-CONTRAST ENGINE
        ═════════════════════════════════════════════════════════════ */
        html.dark-theme {
            --tbl-text: #F8FAFC;
            --tbl-border: rgba(255, 255, 255, 0.08);
            --tbl-header-bg: rgba(15, 23, 42, 0.85);
            --tbl-header-text: #38BDF8;
            --tbl-stripe: rgba(255, 255, 255, 0.02);
            --tbl-hover: rgba(6, 182, 212, 0.08);
            --dt-label: #CBD5E1;
            --dt-input-bg: rgba(15, 23, 42, 0.85);
            --dt-input-border: rgba(255, 255, 255, 0.15);
            --dt-input-text: #F8FAFC;
        }

        html.light-theme, html:not(.dark-theme) {
            --tbl-text: #0F172A;
            --tbl-border: #E2E8F0;
            --tbl-header-bg: #F1F5F9;
            --tbl-header-text: #0284C7;
            --tbl-stripe: #F8FAFC;
            --tbl-hover: #E2E8F0;
            --dt-label: #334155;
            --dt-input-bg: #FFFFFF;
            --dt-input-border: #CBD5E1;
            --dt-input-text: #0F172A;
        }

        .table {
            color: var(--tbl-text) !important;
            border-color: var(--tbl-border) !important;
        }

        .table th, .table thead th {
            background-color: var(--tbl-header-bg) !important;
            color: var(--tbl-header-text) !important;
            border-color: var(--tbl-border) !important;
            font-weight: 700 !important;
        }

        .table td {
            color: var(--tbl-text) !important;
            border-color: var(--tbl-border) !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: var(--tbl-stripe) !important;
        }

        .table-hover tbody tr:hover {
            background-color: var(--tbl-hover) !important;
        }

        /* DataTables Controls & Labels */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--dt-label) !important;
            margin-bottom: 12px;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background-color: var(--dt-input-bg) !important;
            color: var(--dt-input-text) !important;
            border: 1px solid var(--dt-input-border) !important;
            border-radius: 8px !important;
            padding: 6px 12px !important;
            outline: none !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--dt-label) !important;
            border: 1px solid var(--dt-input-border) !important;
            border-radius: 8px !important;
            background: var(--dt-input-bg) !important;
            padding: 5px 12px !important;
            margin: 0 2px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, #06B6D4, #38BDF8) !important;
            color: #FFFFFF !important;
            border-color: #06B6D4 !important;
            font-weight: 700 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #06B6D4 !important;
            color: #FFFFFF !important;
            border-color: #06B6D4 !important;
        }

        /* Global Badge Translucent High-Contrast Rules */
        .badge.bg-info.bg-opacity-20, .badge.bg-info.bg-opacity-25 {
            background-color: rgba(6, 182, 212, 0.18) !important;
            color: #38BDF8 !important;
            border: 1px solid rgba(6, 182, 212, 0.4) !important;
        }
        .badge.bg-primary.bg-opacity-20, .badge.bg-primary.bg-opacity-25 {
            background-color: rgba(59, 130, 246, 0.18) !important;
            color: #60A5FA !important;
            border: 1px solid rgba(59, 130, 246, 0.4) !important;
        }
        .badge.bg-warning.bg-opacity-20, .badge.bg-warning.bg-opacity-25 {
            background-color: rgba(245, 158, 11, 0.18) !important;
            color: #F59E0B !important;
            border: 1px solid rgba(245, 158, 11, 0.4) !important;
        }
        .badge.bg-danger.bg-opacity-20, .badge.bg-danger.bg-opacity-25 {
            background-color: rgba(244, 63, 94, 0.18) !important;
            color: #F43F5E !important;
            border: 1px solid rgba(244, 63, 94, 0.4) !important;
        }
        .badge.bg-secondary.bg-opacity-20, .badge.bg-secondary.bg-opacity-25 {
            background-color: rgba(148, 163, 184, 0.18) !important;
            color: #CBD5E1 !important;
            border: 1px solid rgba(148, 163, 184, 0.4) !important;
        }

        /* Light Theme Badge High-Contrast Overrides */
        html.light-theme .badge.bg-info.bg-opacity-20, html.light-theme .badge.bg-info.bg-opacity-25 {
            background-color: #E0F2FE !important;
            color: #0369A1 !important;
            border: 1px solid #BAE6FD !important;
        }
        html.light-theme .badge.bg-primary.bg-opacity-20, html.light-theme .badge.bg-primary.bg-opacity-25 {
            background-color: #EFF6FF !important;
            color: #1D4ED8 !important;
            border: 1px solid #BFDBFE !important;
        }
        html.light-theme .badge.bg-warning.bg-opacity-20, html.light-theme .badge.bg-warning.bg-opacity-25 {
            background-color: #FEF3C7 !important;
            color: #B45309 !important;
            border: 1px solid #FDE68A !important;
        }
        html.light-theme .badge.bg-danger.bg-opacity-20, html.light-theme .badge.bg-danger.bg-opacity-25 {
            background-color: #FEE2E2 !important;
            color: #B91C1C !important;
            border: 1px solid #FECACA !important;
        }
        html.light-theme .badge.bg-secondary.bg-opacity-20, html.light-theme .badge.bg-secondary.bg-opacity-25, html.light-theme .badge.bg-dark {
            background-color: #F1F5F9 !important;
            color: #475569 !important;
            border: 1px solid #CBD5E1 !important;
        }
        html.light-theme .badge.bg-dark.text-warning {
            background-color: #FEF3C7 !important;
            color: #B45309 !important;
            border: 1px solid #FDE68A !important;
        }
        html.light-theme .text-light {
            color: #0F172A !important;
        }

        h1, h2, h3, h4, h5, h6, .brand-title, .menu-title, .user-name {
            font-family: 'Outfit', sans-serif !important;
        }

        #showImage, .media-zoomable {
            cursor: pointer !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        #showImage:hover, .media-zoomable:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3) !important;
        }

        /* Sidebar Wrapper Positioning & Alignment */
        .sidebar-wrapper {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: auto !important;
            width: 250px !important;
            height: 100% !important;
            background: #0F172A !important;
            border-right: 1px solid var(--salasil-border) !important;
            border-left: none !important;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.3) !important;
            z-index: 1000 !important;
            text-align: left !important;
            transition: left 0.3s ease-in-out, width 0.3s ease-in-out !important;
        }

        /* Sidebar Header */
        .sidebar-wrapper .sidebar-header {
            width: 100% !important;
            height: 60px !important;
            position: relative !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 16px !important;
            background: #0F172A !important;
            border-bottom: 1px solid var(--salasil-border) !important;
            border-right: none !important;
            z-index: 1 !important;
        }

        .sidebar-wrapper .sidebar-header .logo-text {
            color: #38BDF8 !important;
            font-weight: 800 !important;
            font-size: 1.15rem !important;
            margin-bottom: 0 !important;
            margin-left: 8px !important;
        }

        .sidebar-wrapper .sidebar-header .toggle-icon {
            margin-left: auto !important;
            margin-right: 0 !important;
            color: #94A3B8 !important;
            font-size: 22px !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 4px !important;
            border-radius: 6px !important;
            transition: all 0.2s ease !important;
        }

        .sidebar-wrapper .sidebar-header .toggle-icon:hover {
            color: #38BDF8 !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }

        /* Sidebar Navigation Items */
        .sidebar-wrapper .metismenu {
            padding-top: 10px !important;
            padding-bottom: 30px !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin: 0 !important;
            list-style: none !important;
            background: transparent !important;
            text-align: left !important;
        }

        .sidebar-wrapper .metismenu .menu-label {
            font-size: 11px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            color: #64748B !important;
            padding: 16px 20px 6px 20px !important;
            text-align: left !important;
            letter-spacing: 0.8px !important;
            margin: 0 !important;
        }

        .sidebar-wrapper .metismenu li {
            position: relative !important;
            list-style: none !important;
            text-align: left !important;
        }

        .sidebar-wrapper .metismenu a {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            text-align: left !important;
            padding: 10px 18px !important;
            color: #94A3B8 !important;
            font-weight: 500 !important;
            font-size: 14px !important;
            border-radius: 10px !important;
            margin: 3px 12px !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            position: relative !important;
        }

        .sidebar-wrapper .metismenu a:hover,
        .sidebar-wrapper .metismenu .mm-active > a {
            color: #38BDF8 !important;
            background: rgba(6, 182, 212, 0.12) !important;
            border-left: 3px solid var(--salasil-cyan) !important;
        }

        .sidebar-wrapper .metismenu a .parent-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 20px !important;
            min-width: 32px !important;
            height: 32px !important;
            margin-right: 10px !important;
            margin-left: 0 !important;
            color: var(--salasil-cyan) !important;
            flex-shrink: 0 !important;
        }

        .sidebar-wrapper .metismenu a .menu-title {
            font-size: 14px !important;
            font-weight: 600 !important;
            white-space: nowrap !important;
            text-align: left !important;
            margin: 0 !important;
            flex-grow: 1 !important;
        }

        /* Simple Static Down/Up Arrow Indicator for Open/Closed Menu Items */
        .sidebar-wrapper .metismenu .has-arrow:before {
            display: none !important;
        }

        .sidebar-wrapper .metismenu .has-arrow:after {
            content: "\ea4a" !important; /* Simple Down Arrow (Closed) */
            font-family: 'boxicons' !important;
            font-size: 18px !important;
            font-style: normal !important;
            font-weight: normal !important;
            position: absolute !important;
            right: 16px !important;
            left: auto !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            transition: none !important;
            color: #94A3B8 !important;
            border: none !important;
            background: transparent !important;
            width: auto !important;
            height: auto !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            line-height: 1 !important;
        }

        .sidebar-wrapper .metismenu .has-arrow[aria-expanded="true"]:after,
        .sidebar-wrapper .metismenu .mm-active > .has-arrow[aria-expanded="true"]:after {
            content: "\ea47" !important; /* Simple Up Arrow (Open) */
            transform: translateY(-50%) !important;
            transition: none !important;
            color: #38BDF8 !important;
        }

        /* Submenu Dropdown List Styling */
        .sidebar-wrapper .metismenu ul {
            background: transparent !important;
            padding-left: 18px !important;
            padding-right: 0 !important;
            margin: 0 !important;
            list-style: none !important;
            display: none;
        }

        .sidebar-wrapper .metismenu li.mm-active > ul,
        .sidebar-wrapper .metismenu ul.mm-show {
            display: block !important;
        }

        .sidebar-wrapper .metismenu ul a {
            padding: 8px 15px !important;
            font-size: 13px !important;
            color: #94A3B8 !important;
            margin: 2px 8px !important;
        }

        .sidebar-wrapper .metismenu ul a:hover {
            color: #38BDF8 !important;
            background: rgba(6, 182, 212, 0.08) !important;
        }

        .sidebar-wrapper .metismenu ul a i {
            font-size: 16px !important;
            margin-right: 8px !important;
            color: var(--salasil-cyan) !important;
        }

        /* Topbar Header positioning */
        .topbar {
            position: fixed !important;
            top: 0 !important;
            left: 250px !important;
            right: 0 !important;
            height: 65px !important;
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid var(--salasil-border) !important;
            z-index: 1000 !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 !important;
            transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .topbar .navbar {
            width: 100% !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 24px !important;
            margin: 0 !important;
        }

        /* Left Section of Header: Mobile Toggle & Search Input */
        .topbar .header-left-section {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
        }

        .topbar .search-bar-box {
            position: relative !important;
            width: 340px !important;
        }

        .topbar .search-bar-box .search-input {
            width: 100% !important;
            height: 40px !important;
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px !important;
            padding-left: 42px !important;
            padding-right: 15px !important;
            color: #F8FAFC !important;
            font-size: 0.88rem !important;
            outline: none !important;
            transition: all 0.2s ease !important;
        }

        .topbar .search-bar-box .search-input:focus {
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: #06B6D4 !important;
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.2) !important;
        }

        .topbar .search-bar-box .search-icon {
            position: absolute !important;
            left: 14px !important;
            right: auto !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: #94A3B8 !important;
            font-size: 18px !important;
            pointer-events: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Right Section of Header: Notifications & User Profile */
        .topbar .header-right-section {
            display: flex !important;
            align-items: center !important;
            gap: 20px !important;
            margin-left: auto !important;
            margin-right: 0 !important;
        }

        .topbar .user-box {
            display: flex !important;
            align-items: center !important;
        }

        .topbar .user-box .nav-link {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 4px 8px !important;
            border-radius: 10px !important;
            transition: background 0.2s ease !important;
        }

        .topbar .user-box .nav-link:hover {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        .topbar .user-img {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 2px solid #06B6D4 !important;
        }

        /* Page Content Wrapper positioned on the LEFT */
        .page-wrapper {
            margin-left: 250px !important;
            margin-right: 0 !important;
            height: 100%;
            margin-top: 60px;
            margin-bottom: 30px;
            transition: margin-left 0.3s ease-in-out !important;
        }

        /* Footer positioned on the LEFT */
        .page-footer {
            left: 250px !important;
            right: 0 !important;
            background: #0F172A !important;
            border-top: 1px solid var(--salasil-border) !important;
            color: #64748B !important;
            transition: left 0.3s ease-in-out !important;
        }

        /* Overlay Backdrop for Mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.65) !important;
            backdrop-filter: blur(4px) !important;
            z-index: 10040 !important;
        }

        /* Toggled Desktop State (Above 1024px) */
        @media (min-width: 1025px) {
            .wrapper.toggled .sidebar-wrapper {
                left: 0 !important;
                right: auto !important;
                width: 70px !important;
            }
            .wrapper.toggled .topbar {
                left: 70px !important;
                right: 0 !important;
            }
            .wrapper.toggled .page-wrapper {
                margin-left: 70px !important;
                margin-right: 0 !important;
            }
            .wrapper.toggled .page-footer {
                left: 70px !important;
                right: 0 !important;
            }
        }

        /* Mobile Toggle Menu Hamburger Button */
        button.mobile-toggle-menu,
        div.mobile-toggle-menu,
        .mobile-toggle-menu {
            display: none !important;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            cursor: pointer !important;
        }

        /* Mobile View (Below 1024px) */
        @media (max-width: 1024px) {
            button.mobile-toggle-menu,
            div.mobile-toggle-menu,
            .mobile-toggle-menu {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                visibility: visible !important;
                opacity: 1 !important;
                z-index: 10050 !important;
            }
            .sidebar-wrapper {
                left: -250px !important;
            }
            .wrapper.toggled .sidebar-wrapper {
                left: 0 !important;
                width: 250px !important;
                box-shadow: 10px 0 35px rgba(0, 0, 0, 0.7) !important;
                z-index: 10060 !important;
            }
            .wrapper.toggled .overlay {
                display: block !important;
            }
            .topbar {
                left: 0 !important;
            }
            .page-wrapper {
                margin-left: 0 !important;
            }
            .page-footer {
                left: 0 !important;
            }
        }

        /* Card Overrides */
        .card {
            background-color: #1E293B !important;
            border: 1px solid var(--salasil-border) !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
        }

        /* Buttons & Badges */
        .btn-primary {
            background: linear-gradient(135deg, #16284F 0%, #0284C7 100%) !important;
            border: none !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.3) !important;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0F1C3F 0%, #06B6D4 100%) !important;
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4) !important;
        }

        /* Badge Styling for High Contrast in Dark & Light Themes */
        .badge {
            font-size: 0.82rem !important;
            letter-spacing: 0.3px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
        }

        /* Dark Theme Badge Color Overrides */
        html.dark-theme .badge.text-info, .badge.text-info { color: #38BDF8 !important; }
        html.dark-theme .badge.text-primary, .badge.text-primary { color: #60A5FA !important; }
        html.dark-theme .badge.text-success, .badge.text-success { color: #4ADE80 !important; }
        html.dark-theme .badge.text-warning, .badge.text-warning { color: #FBBF24 !important; }
        html.dark-theme .badge.text-danger, .badge.text-danger { color: #F87171 !important; }
        html.dark-theme .badge.text-secondary, .badge.text-secondary { color: #94A3B8 !important; }

        /* Light Theme Badge Color Overrides */
        html.light-theme .badge.text-info { color: #0284C7 !important; background-color: rgba(2, 132, 199, 0.12) !important; }
        html.light-theme .badge.text-primary { color: #2563EB !important; background-color: rgba(37, 99, 235, 0.12) !important; }
        html.light-theme .badge.text-success { color: #16A34A !important; background-color: rgba(22, 163, 74, 0.12) !important; }
        html.light-theme .badge.text-warning { color: #D97706 !important; background-color: rgba(217, 119, 6, 0.12) !important; }
        html.light-theme .badge.text-danger { color: #DC2626 !important; background-color: rgba(220, 38, 38, 0.12) !important; }
        html.light-theme .badge.text-secondary { color: #475569 !important; background-color: rgba(71, 85, 105, 0.12) !important; }

        /* Table Theme Overrides */
        .table {
            color: #F8FAFC !important;
        }

        .table-striped>tbody>tr:nth-of-type(odd)>* {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: #F8FAFC !important;
        }

        .table th {
            color: #94A3B8 !important;
            font-weight: 700;
            text-uppercase: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--salasil-border) !important;
        }

        .table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        }

        /* Form Controls & Inputs (Dark & Light Theme) */
        .form-control,
        .form-select {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #F8FAFC !important;
            border-radius: 10px !important;
            padding: 10px 14px !important;
            transition: all 0.2s ease !important;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(15, 23, 42, 0.8) !important;
            border-color: #06B6D4 !important;
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.2) !important;
            color: #FFFFFF !important;
        }

        .form-control::placeholder {
            color: #94A3B8 !important;
        }

        .form-label {
            color: #E2E8F0 !important;
            font-size: 0.9rem !important;
        }

        /* LTR Fix for Dropdown Select Arrows across all themes */
        .form-select,
        html.dark-theme .form-select,
        html.light-theme .form-select {
            padding-right: 2.75rem !important;
            padding-left: 1rem !important;
            background-position: right 0.85rem center !important;
            background-size: 16px 12px !important;
            background-repeat: no-repeat !important;
            text-align: left !important;
            direction: ltr !important;
        }

        /* Custom crisp dropdown chevron icon for Dark Mode */
        html.dark-theme .form-select,
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2338BDF8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        }

        /* Custom crisp dropdown chevron icon for Light Mode */
        html.light-theme .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%230284C7' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        }

        /* HTML5 Date Input Calendar Icon Styling (Hide Duplicate Right Indicator) */
        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent !important;
            color: transparent !important;
            opacity: 0 !important;
            width: 35px !important;
            height: 100% !important;
            position: absolute !important;
            right: 5px !important;
            top: 0 !important;
            cursor: pointer !important;
        }

        input[type="date"]::-webkit-inner-spin-button,
        input[type="date"]::-webkit-clear-button {
            display: none !important;
        }

        /* ==========================================================================
           Light Mode Theme Styles for SALASIL Platform
           ========================================================================== */
        html.light-theme,
        html.light-theme body {
            background-color: #F8FAFC !important;
            color: #0F172A !important;
        }

        /* Light Mode Header & Topbar */
        html.light-theme .topbar {
            background: rgba(255, 255, 255, 0.95) !important;
            border-bottom: 1px solid #E2E8F0 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
        }

        html.light-theme .topbar .search-bar-box .search-input {
            background: #F1F5F9 !important;
            border-color: #E2E8F0 !important;
            color: #0F172A !important;
        }

        html.light-theme .topbar .search-bar-box .search-input::placeholder {
            color: #94A3B8 !important;
        }

        html.light-theme .topbar .search-bar-box .search-icon {
            color: #64748B !important;
        }

        html.light-theme .topbar .user-info .user-name {
            color: #0F172A !important;
        }

        html.light-theme .topbar .user-info .designattion {
            color: #64748B !important;
        }

        /* Light Mode Sidebar */
        html.light-theme .sidebar-wrapper {
            background: #FFFFFF !important;
            border-right: 1px solid #E2E8F0 !important;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.04) !important;
        }

        html.light-theme .sidebar-wrapper .sidebar-header {
            background: #FFFFFF !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }

        html.light-theme .sidebar-wrapper .sidebar-header .logo-text {
            color: #0284C7 !important;
        }

        html.light-theme .sidebar-wrapper .metismenu .menu-label {
            color: #94A3B8 !important;
        }

        html.light-theme .sidebar-wrapper .metismenu a {
            color: #475569 !important;
        }

        html.light-theme .sidebar-wrapper .metismenu a:hover,
        html.light-theme .sidebar-wrapper .metismenu .mm-active > a {
            color: #0284C7 !important;
            background: rgba(2, 132, 199, 0.08) !important;
            border-left: 3px solid #0284C7 !important;
        }

        html.light-theme .sidebar-wrapper .metismenu a .parent-icon {
            color: #0284C7 !important;
        }

        html.light-theme .sidebar-wrapper .metismenu .has-arrow:after {
            color: #94A3B8 !important;
        }

        html.light-theme .sidebar-wrapper .metismenu .has-arrow[aria-expanded="true"]:after,
        html.light-theme .sidebar-wrapper .metismenu .mm-active > .has-arrow[aria-expanded="true"]:after {
            color: #0284C7 !important;
        }

        html.light-theme .sidebar-wrapper .metismenu ul a {
            color: #64748B !important;
        }

        html.light-theme .sidebar-wrapper .metismenu ul a:hover {
            color: #0284C7 !important;
            background: rgba(2, 132, 199, 0.06) !important;
        }

        /* Light Mode Cards & Content */
        html.light-theme .card,
        html.light-theme .salasil-stat-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
        }

        html.light-theme .salasil-stat-card:hover {
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.12) !important;
            border-color: rgba(2, 132, 199, 0.3) !important;
        }

        html.light-theme .stat-label {
            color: #64748B !important;
        }

        html.light-theme .stat-value {
            color: #0F172A !important;
        }

        html.light-theme h1, 
        html.light-theme h2, 
        html.light-theme h3, 
        html.light-theme h4, 
        html.light-theme h5, 
        html.light-theme h6,
        html.light-theme .fw-bold.text-white {
            color: #0F172A !important;
        }

        html.light-theme .text-slate-400 {
            color: #64748B !important;
        }

        /* Light Mode Form Control Overrides */
        html.light-theme .form-control,
        html.light-theme .form-select {
            background-color: #FFFFFF !important;
            border: 1px solid #CBD5E1 !important;
            color: #0F172A !important;
        }

        html.light-theme .form-control:focus,
        html.light-theme .form-select:focus {
            background-color: #FFFFFF !important;
            border-color: #0284C7 !important;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.15) !important;
            color: #0F172A !important;
        }

        html.light-theme .form-control::placeholder {
            color: #94A3B8 !important;
        }

        html.light-theme .form-label {
            color: #1E293B !important;
        }

        html.light-theme .header-title {
            color: #0F172A !important;
        }

        /* Light Mode Table Overrides */
        html.light-theme .table {
            color: #0F172A !important;
        }

        html.light-theme .client-name-text,
        html.light-theme .joined-date-text,
        html.light-theme .table td {
            color: #0F172A !important;
        }

        .client-name-text,
        .joined-date-text {
            color: #F8FAFC !important;
        }

        html.light-theme .table th {
            background: #F8FAFC !important;
            color: #475569 !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }

        html.light-theme .table td {
            border-bottom: 1px solid #F1F5F9 !important;
            color: #1E293B !important;
        }

        html.light-theme .table-striped>tbody>tr:nth-of-type(odd)>* {
            background-color: #F8FAFC !important;
            color: #0F172A !important;
        }

        html.light-theme .table td.fw-bold.text-white {
            color: #0F172A !important;
        }

        /* Light Mode Footer */
        html.light-theme .page-footer {
            background: #FFFFFF !important;
            border-top: 1px solid #E2E8F0 !important;
            color: #64748B !important;
        }
    </style>
</head>

<body>
    <!--wrapper-->
    <div class="wrapper">
        <!--sidebar wrapper -->
        @include('admin.include.sidebar')
        <!--end sidebar wrapper -->

        <!--start header -->
        @include('admin.include.header')
        <!--end header -->

        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content p-4">
                @yield('admin')
            </div>
        </div>
        <!--end page wrapper -->

        <!--start overlay-->
        <div class="overlay toggle-icon"></div>
        <!--end overlay-->

        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->

        @include('admin.include.footer')
    </div>
    <!--end wrapper-->

    <!-- Bootstrap & Plugins JS -->
    <script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('backend/assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('backend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- App JS -->
    <script src="{{ asset('backend/assets/js/app.js') }}"></script>

    <!-- Master Sidebar & Mobile Toggle & Theme Switcher Handlers -->
    <script>
        $(document).ready(function() {
            // Global SweetAlert2 Professional Delete Handler
            $(document).on('click', '.delete-client-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this client account? This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Account!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Truck Types
            $(document).on('click', '.delete-truck-type-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this truck type? This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Truck Type!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global Truck Type Status Change AJAX Handler
            $(document).on('change', '.truck-status-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var truckTypeId = select.attr('data-truck-id') || select.data('truck-id');
                var newStatus = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('truck.type.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        truck_type_id: truckTypeId,
                        status: newStatus
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        select.removeClass('status-badge-active status-badge-inactive');
                        select.addClass('status-badge-' + newStatus);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Truck type status updated successfully!');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Truck type status updated successfully!',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update truck type status.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Status Update Error',
                                text: msg
                            });
                        } else {
                            alert(msg);
                        }
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Truck Sub-Types
            $(document).on('click', '.delete-truck-sub-type-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this truck sub-type? This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Sub-Type!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global Truck Sub-Type Status Change AJAX Handler
            $(document).on('change', '.truck-sub-status-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var subTypeId = select.attr('data-sub-id') || select.data('sub-id');
                var isActive = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('truck.sub.type.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        sub_type_id: subTypeId,
                        is_active: isActive
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        select.removeClass('status-badge-active status-badge-inactive');
                        var badgeClass = (isActive == '1' || isActive == 1) ? 'status-badge-active' : 'status-badge-inactive';
                        select.addClass(badgeClass);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Sub-type status updated successfully!');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Sub-type status updated successfully!',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update sub-type status.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Status Update Error',
                                text: msg
                            });
                        } else {
                            alert(msg);
                        }
                    }
                });
            });

            // Global User Status Change AJAX Handler
            $(document).on('change', '.user-status-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var userId = select.attr('data-user-id') || select.data('user-id');
                var newStatus = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('change.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        user_id: userId,
                        status: newStatus
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        select.removeClass('status-badge-active status-badge-pending status-badge-inactive status-badge-banned');
                        select.addClass('status-badge-' + newStatus);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Status updated successfully!');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Status updated successfully!',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update client status.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Status Update Error',
                                text: msg
                            });
                        } else {
                            alert(msg);
                        }
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Driver Truck Assignments
            $(document).on('click', '.delete-driver-truck-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to remove this truck assignment from the driver? This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Remove Assignment!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global Driver Truck Verified Status Change AJAX Handler
            $(document).on('change', '.driver-truck-verified-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var itemId = select.attr('data-item-id') || select.data('item-id');
                var isVerified = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('change.driver.truck.verified.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        id: itemId,
                        is_verified: isVerified
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        select.removeClass('verified-badge-0 verified-badge-1');
                        select.addClass('verified-badge-' + isVerified);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Verification status updated successfully!');
                        }
                    },
                    error: function(xhr) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update verification status.';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                    }
                });
            });

            // Global Driver Truck Default Status Change AJAX Handler
            $(document).on('change', '.driver-truck-default-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var itemId = select.attr('data-item-id') || select.data('item-id');
                var isDefault = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('change.driver.truck.default.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        id: itemId,
                        is_default: isDefault
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Default active truck updated!');
                        }
                        setTimeout(function() { window.location.reload(); }, 600);
                    },
                    error: function(xhr) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update default status.';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Countries
            $(document).on('click', '.delete-country-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Deleting this country will also delete all associated cities! Action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Country!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global Country Status Change AJAX Handler
            $(document).on('change', '.country-status-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var countryId = select.attr('data-country-id') || select.data('country-id');
                var isActive = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('country.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        country_id: countryId,
                        is_active: isActive
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        select.removeClass('status-badge-active status-badge-inactive');
                        var badgeClass = (isActive == '1' || isActive == 1) ? 'status-badge-active' : 'status-badge-inactive';
                        select.addClass(badgeClass);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Country status updated successfully!');
                        }
                    },
                    error: function(xhr) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update country status.';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Cities
            $(document).on('click', '.delete-city-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this city? Action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete City!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global City Status Change AJAX Handler
            $(document).on('change', '.city-status-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var cityId = select.attr('data-city-id') || select.data('city-id');
                var isActive = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('city.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        city_id: cityId,
                        is_active: isActive
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        select.removeClass('status-badge-active status-badge-inactive');
                        var badgeClass = (isActive == '1' || isActive == 1) ? 'status-badge-active' : 'status-badge-inactive';
                        select.addClass(badgeClass);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'City status updated successfully!');
                        }
                    },
                    error: function(xhr) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update city status.';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Shipment Types
            $(document).on('click', '.delete-shipment-type-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this shipment type? Action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Shipment Type!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global Shipment Type Status Change AJAX Handler
            $(document).on('change', '.shipment-type-status-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var shipmentTypeId = select.attr('data-shipment-type-id') || select.data('shipment-type-id');
                var isActive = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('shipment.type.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        shipment_type_id: shipmentTypeId,
                        is_active: isActive
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        select.removeClass('status-badge-active status-badge-inactive');
                        var badgeClass = (isActive == '1' || isActive == 1) ? 'status-badge-active' : 'status-badge-inactive';
                        select.addClass(badgeClass);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Shipment type status updated successfully!');
                        }
                    },
                    error: function(xhr) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update shipment type status.';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Shipment Natures
            $(document).on('click', '.delete-shipment-nature-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this shipment nature? Action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Shipment Nature!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global Shipment Nature Status Change AJAX Handler
            $(document).on('change', '.shipment-nature-status-select', function(e) {
                e.preventDefault();
                var select = $(this);
                var shipmentNatureId = select.attr('data-shipment-nature-id') || select.data('shipment-nature-id');
                var isActive = select.val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

                select.prop('disabled', true);

                $.ajax({
                    url: "{{ route('shipment.nature.status.ajax') }}",
                    type: "POST",
                    data: {
                        _token: csrfToken,
                        shipment_nature_id: shipmentNatureId,
                        is_active: isActive
                    },
                    dataType: "json",
                    success: function(response) {
                        select.prop('disabled', false);
                        select.removeClass('status-badge-active status-badge-inactive');
                        var badgeClass = (isActive == '1' || isActive == 1) ? 'status-badge-active' : 'status-badge-inactive';
                        select.addClass(badgeClass);

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Shipment nature status updated successfully!');
                        }
                    },
                    error: function(xhr) {
                        select.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update shipment nature status.';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Truck Brands
            $(document).on('click', '.delete-truck-brand-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this truck brand? All linked models will also be deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Brand!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Global SweetAlert2 Delete Handler for Truck Models
            $(document).on('click', '.delete-truck-model-btn', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                var isLight = $('html').hasClass('light-theme');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this truck model? Action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Model!',
                    cancelButtonText: 'Cancel',
                    background: isLight ? '#FFFFFF' : '#1E293B',
                    color: isLight ? '#0F172A' : '#F8FAFC',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-semibold',
                        cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            // Theme Mode Toggle (Dark / Light) with LocalStorage persistence
            var currentTheme = localStorage.getItem('salasil_theme') || 'light';

            function applyTheme(theme) {
                if (theme === 'light') {
                    $('html').removeClass('dark-theme').addClass('light-theme');
                    $('#theme-icon').removeClass('bx-sun').addClass('bx-moon').css('color', '#0284C7');
                    localStorage.setItem('salasil_theme', 'light');
                } else {
                    $('html').removeClass('light-theme').addClass('dark-theme');
                    $('#theme-icon').removeClass('bx-moon').addClass('bx-sun').css('color', '#38BDF8');
                    localStorage.setItem('salasil_theme', 'dark');
                }
            }

            // Apply theme preference on page load
            applyTheme(currentTheme);

            // Theme Switcher Button click listener
            $(document).on('click', '#theme-toggle-btn', function(e) {
                e.preventDefault();
                var newTheme = $('html').hasClass('light-theme') ? 'dark' : 'light';
                applyTheme(newTheme);
            });

            // Delegated click handler for mobile toggle, close icon, and backdrop overlay
            $(document).on('click', '.mobile-toggle-menu, .toggle-icon, .overlay', function(e) {
                e.preventDefault();
                $('.wrapper').toggleClass('toggled');
            });

            // Bulletproof Sidebar Dropdown Toggle Handler for Main Menu Items
            $(document).on('click', '.sidebar-wrapper .metismenu a.has-arrow, .sidebar-wrapper .metismenu li > a:has(+ ul)', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var $thisLink = $(this);
                var $parentLi = $thisLink.closest('li');
                var $subMenu = $parentLi.children('ul');

                if ($parentLi.hasClass('mm-active')) {
                    $parentLi.removeClass('mm-active');
                    $thisLink.attr('aria-expanded', 'false');
                    $subMenu.slideUp(200, function() {
                        $(this).removeClass('mm-show').css('display', '');
                    });
                } else {
                    var $siblings = $parentLi.siblings('.mm-active');
                    $siblings.removeClass('mm-active');
                    $siblings.find('a.has-arrow').attr('aria-expanded', 'false');
                    $siblings.children('ul').slideUp(200, function() {
                        $(this).removeClass('mm-show').css('display', '');
                    });

                    $parentLi.addClass('mm-active');
                    $thisLink.attr('aria-expanded', 'true');
                    $subMenu.addClass('mm-show').slideDown(200);
                }
            });
        });
    </script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        @if(Session::has('message'))
            var type = "{{ Session::get('alert-type','info') }}";
            switch(type){
                case 'info':
                    toastr.info("{{ Session::get('message') }}");
                    break;
                case 'success':
                    toastr.success("{{ Session::get('message') }}");
                    break;
                case 'warning':
                    toastr.warning("{{ Session::get('message') }}");
                    break;
                case 'error':
                    toastr.error("{{ Session::get('message') }}");
                    break;
            }
        @endif
    </script>

    <!-- Datatable JS -->
    <script src="{{ asset('backend/assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            if ($('#example').length) {
                $('#example').DataTable({
                    "language": {
                        "search": "Search records:",
                        "lengthMenu": "Display _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries"
                    }
                });
            }
        });
    </script>

    <!-- Global Media & Document Preview Lightbox Modal -->
    <div class="modal fade" id="globalMediaPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-dark text-white border border-info border-opacity-25 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom border-secondary border-opacity-25 py-2 px-3 bg-navy">
                    <h6 class="modal-title fw-bold text-info d-flex align-items-center gap-2 mb-0" id="globalMediaPreviewTitle">
                        <i class="bx bx-show fs-5"></i> Media & Document Preview
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <a id="globalMediaExternalLink" href="#" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1" style="display: none;">
                            <i class="bx bx-link-external me-1"></i> Open in New Tab
                        </a>
                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body text-center p-3 bg-black bg-opacity-50 position-relative d-flex justify-content-center align-items-center" style="min-height: 380px; max-height: 82vh; overflow: auto;">
                    <img id="globalMediaPreviewImage" src="" class="img-fluid rounded-3 shadow-lg border border-secondary border-opacity-25" style="max-height: 78vh; object-fit: contain; display: none;">
                    <iframe id="globalMediaPreviewIframe" src="" style="width: 100%; height: 75vh; border: none; display: none;" class="rounded-3 shadow-lg"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function openGlobalMediaModal(src, title) {
            if (!src) return;
            var titleText = title || 'Document / Photo Preview';
            $('#globalMediaPreviewTitle').html('<i class="bx bx-show fs-5 me-1 text-info"></i> ' + titleText);
            $('#globalMediaExternalLink').attr('href', src).show();

            var cleanSrc = src.toLowerCase().split('?')[0];
            if (cleanSrc.endsWith('.pdf') || src.startsWith('data:application/pdf')) {
                $('#globalMediaPreviewImage').hide();
                $('#globalMediaPreviewIframe').attr('src', src).show();
            } else {
                $('#globalMediaPreviewIframe').hide();
                $('#globalMediaPreviewImage').attr('src', src).show();
            }
            $('#globalMediaPreviewModal').modal('show');
        }

        // Universal Click handler for zoomable thumbnails & image previews
        $(document).on('click', '.media-zoomable, #showImage', function(e) {
            e.preventDefault();
            var $elem = $(this);
            var src = $elem.attr('data-src') || 
                      $elem.attr('src') || 
                      $elem.find('img').attr('src') || 
                      $elem.attr('href');
                      
            var title = $elem.attr('data-title') || 
                        $elem.parents('.media-zoomable').attr('data-title') ||
                        $elem.attr('alt') || 
                        'Image Preview';

            if (src && src !== '#') {
                openGlobalMediaModal(src, title);
            }
        });

        // Automatic live file picker preview handler
        $(document).on('change', '.live-doc-picker', function(e) {
            var file = e.target.files[0];
            var targetPreview = $(this).attr('data-preview-box');
            var title = $(this).attr('data-title') || 'Uploaded Document';

            if (!targetPreview || !$(targetPreview).length) return;

            if (file) {
                var reader = new FileReader();
                var isImage = file.type.startsWith('image/');
                var isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');

                reader.onload = function(e) {
                    var dataUrl = e.target.result;
                    var html = '';

                    if (isImage) {
                        html = `
                            <div class="d-inline-flex align-items-center gap-2 p-1 pe-3 border border-info border-opacity-50 rounded-3 bg-dark media-zoomable cursor-pointer" data-src="${dataUrl}" data-title="${title}" style="cursor: pointer;">
                                <div class="position-relative">
                                    <img src="${dataUrl}" class="rounded-2 border border-info" style="width: 55px; height: 55px; object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-info text-dark rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 18px; height: 18px; font-size: 10px;">
                                        <i class="bx bx-search-alt-2"></i>
                                    </span>
                                </div>
                                <div class="text-start">
                                    <span class="d-block fw-semibold text-info fs-7 text-truncate" style="max-width: 180px;">${file.name}</span>
                                    <small class="text-slate-300 fs-8"><i class="bx bx-zoom-in text-cyan me-1"></i>Click to view enlarged photo</small>
                                </div>
                            </div>
                        `;
                    } else if (isPdf) {
                        html = `
                            <div class="d-inline-flex align-items-center gap-2 p-2 pe-3 border border-danger border-opacity-50 rounded-3 bg-dark media-zoomable cursor-pointer" data-src="${dataUrl}" data-title="${title}" style="cursor: pointer;">
                                <div class="rounded-2 bg-danger bg-opacity-25 p-2 text-danger d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="bx bxs-file-pdf fs-3"></i>
                                </div>
                                <div class="text-start">
                                    <span class="d-block fw-semibold text-light fs-7 text-truncate" style="max-width: 180px;">${file.name}</span>
                                    <small class="text-info fs-8"><i class="bx bx-show me-1"></i>Click to view PDF document</small>
                                </div>
                            </div>
                        `;
                    } else {
                        html = `
                            <div class="d-inline-flex align-items-center gap-2 p-2 pe-3 border border-secondary border-opacity-50 rounded-3 bg-dark">
                                <i class="bx bx-file fs-3 text-info"></i>
                                <span class="fw-semibold text-light fs-7">${file.name}</span>
                            </div>
                        `;
                    }
                    $(targetPreview).html(html).slideDown(200);
                }
                reader.readAsDataURL(file);
            } else {
                $(targetPreview).html('').slideUp(200);
            }
        });
    </script>
</body>
</html>
