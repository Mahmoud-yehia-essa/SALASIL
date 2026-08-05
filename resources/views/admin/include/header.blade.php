<header>
    <div class="topbar">
        <nav class="navbar">
            <!-- Left Section: Mobile Menu Toggle & Global Search -->
            <div class="header-left-section">
                <!-- Mobile Toggle Menu Hamburger Button -->
                <button type="button" class="btn p-0 border-0 mobile-toggle-menu" id="mobile-toggle-btn" title="Toggle Navigation" aria-label="Toggle Navigation">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); width: 40px; height: 40px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6H20M4 12H20M4 18H20" stroke="#38BDF8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </button>
            </div>

            <!-- Right Section: Theme Toggle, Notifications & User Profile -->
            <div class="header-right-section">
                <!-- Theme Mode Switcher Button (Dark / Light) -->
                <div class="nav-item">
                    <a class="nav-link text-white position-relative p-2 rounded-circle hover-bg-dark cursor-pointer" id="theme-toggle-btn" href="javascript:;" title="Toggle Dark/Light Mode">
                        <i class='bx bx-moon fs-4' id="theme-icon" style="color: #0284C7;"></i>
                    </a>
                </div>

                @php
                    $adminUser = Auth::user();
                    $unreadNotificationsCount = $adminUser ? $adminUser->unreadNotifications->count() : 0;
                    $latestNotifications = $adminUser ? $adminUser->notifications()->take(10)->get() : collect();
                @endphp

                <!-- Notification Bell Dropdown -->
                <div class="nav-item dropdown position-relative">
                    <a class="nav-link text-white position-relative p-2 rounded-circle hover-bg-dark d-inline-flex align-items-center justify-content-center cursor-pointer dropdown-toggle hide-arrow" 
                       href="javascript:;" 
                       id="notificationDropdown" 
                       role="button" 
                       data-bs-toggle="dropdown" 
                       aria-expanded="false" 
                       title="Notifications" 
                       style="width: 40px; height: 40px;">
                        <span class="position-relative d-inline-flex align-items-center justify-content-center">
                            <i class='bx bx-bell fs-4' style="color: #38BDF8;"></i>
                            <span id="notif-badge-count" class="position-absolute badge rounded-pill bg-danger shadow-sm {{ $unreadNotificationsCount > 0 ? '' : 'd-none' }}" 
                                  style="font-size: 0.65rem; top: -6px; right: -8px; padding: 0.25em 0.45em; border: 1.5px solid #FFFFFF;">
                                {{ $unreadNotificationsCount }}
                            </span>
                        </span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end notif-dropdown-box shadow-lg border-0 p-0 rounded-4 overflow-hidden" 
                         aria-labelledby="notificationDropdown" 
                         dir="ltr"
                         style="width: 360px; max-width: calc(100vw - 24px); right: 0 !important; left: auto !important; margin-top: 10px !important; direction: ltr !important; text-align: left !important;">
                        
                        {{-- Header --}}
                        <div class="p-3 notif-header d-flex align-items-center justify-content-between" style="direction: ltr !important; text-align: left !important;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-bell text-info fs-5"></i>
                                <h6 class="mb-0 fw-bold notif-header-title fs-7">Notifications</h6>
                                <span id="notif-header-badge" class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 rounded-pill fs-8">
                                    {{ $unreadNotificationsCount }} New
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-link text-info p-0 text-decoration-none fs-8 fw-semibold" onclick="markAllNotificationsAsRead(event)">
                                    Mark all read
                                </button>
                                <span class="text-slate-400 opacity-50 fs-9">|</span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none fs-8 fw-semibold" onclick="clearAllNotifications(event)">
                                    Clear all
                                </button>
                            </div>
                        </div>

                        {{-- Notifications Scrollable List --}}
                        <div id="notif-dropdown-list" class="overflow-y-auto" style="max-height: 360px; direction: ltr !important; text-align: left !important;">
                            @forelse($latestNotifications as $n)
                                @php
                                    $d = $n->data;
                                    $isUnread = is_null($n->read_at);
                                    $isAccepted = ($d['status'] ?? '') === 'accepted';
                                    $driverName = $d['driver_name'] ?? 'Driver';
                                    $shipmentTitle = $d['shipment_title'] ?? ('Shipment #' . ($d['shipment_id'] ?? ''));
                                    $title = $isAccepted ? 'Invitation Accepted' : 'Invitation Declined';
                                    $message = $isAccepted 
                                        ? "Driver {$driverName} accepted invitation for {$shipmentTitle}." 
                                        : "Driver {$driverName} declined invitation for {$shipmentTitle}.";
                                @endphp
                                <div class="dropdown-item notif-item p-3 text-wrap d-flex align-items-start gap-2.5 transition-all {{ $isUnread ? 'unread' : '' }} position-relative"
                                     style="direction: ltr !important; text-align: left !important; cursor: pointer;"
                                     onclick="window.location.href='{{ route('admin.notification.read', $n->id, false) }}'">
                                    
                                    {{-- Status Icon --}}
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" 
                                         style="width: 36px; height: 36px; {{ $isAccepted ? 'background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3);' : 'background: rgba(244, 63, 94, 0.15); color: #F43F5E; border: 1px solid rgba(244, 63, 94, 0.3);' }}">
                                        <i class="bx {{ $isAccepted ? 'bx-check-shield' : 'bx-x-circle' }} fs-5"></i>
                                    </div>

                                    {{-- Body --}}
                                    <div class="flex-grow-1 min-w-0" style="text-align: left !important; direction: ltr !important;">
                                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1" style="direction: ltr !important;">
                                            <span class="fw-bold fs-7 {{ $isAccepted ? 'notif-item-title-success' : 'notif-item-title-danger' }}">
                                                {{ $title }}
                                            </span>
                                            <small class="text-slate-400 fs-9 me-3">{{ $n->created_at ? $n->created_at->diffForHumans() : '' }}</small>
                                        </div>
                                        <p class="mb-1 fs-8 notif-item-msg text-truncate-2" style="line-height: 1.35; text-align: left !important; direction: ltr !important;">
                                            {{ $message }}
                                        </p>
                                        @if(!$isAccepted && !empty($d['rejection_reason']))
                                            <div class="fs-9 text-danger bg-danger bg-opacity-10 border border-danger border-opacity-20 rounded p-1 mt-1 text-truncate" style="text-align: left !important;">
                                                <strong>Reason:</strong> {{ $d['rejection_reason'] }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Actions Column: Delete + Unread Dot --}}
                                    <div class="d-flex flex-column align-items-end justify-content-between flex-shrink-0 ms-1" style="height: 100%;">
                                        <button type="button" class="btn btn-link p-0 text-slate-400 border-0 mb-1" 
                                                title="Delete Notification"
                                                style="line-height: 1; text-decoration: none; opacity: 0.6; transition: all 0.15s ease;"
                                                onmouseover="this.style.opacity='1'; this.style.color='#EF4444';"
                                                onmouseout="this.style.opacity='0.6'; this.style.color='';"
                                                onclick="deleteSingleNotification(event, '{{ $n->id }}')">
                                            <i class="bx bx-trash fs-6"></i>
                                        </button>
                                        @if($isUnread)
                                            <span class="rounded-circle bg-info flex-shrink-0" style="width: 7px; height: 7px; box-shadow: 0 0 8px #06B6D4;"></span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center notif-empty-state text-slate-400 fs-8" style="text-align: center !important;">
                                    <i class="bx bx-bell-off fs-2 d-block mb-1 opacity-50 text-center" style="text-align: center !important;"></i>
                                    <span class="d-block text-center" style="text-align: center !important;">No notifications yet</span>
                                </div>
                            @endforelse
                        </div>

                        {{-- Footer --}}
                        <div class="p-2.5 text-center notif-footer" style="direction: ltr !important;">
                            <a href="{{ route('all.shipment.invitations', [], false) }}" class="fw-bold text-info fs-8 text-decoration-none d-inline-flex align-items-center gap-1">
                                <span>View All Shipment Invitations</span> <i class="bx bx-right-arrow-alt fs-6"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <style>
                /* Notification Dropdown Theme Styles & LTR Alignment */
                .notif-dropdown-box,
                .notif-dropdown-box * {
                    direction: ltr !important;
                    text-align: left !important;
                }
                .notif-dropdown-box {
                    background: #FFFFFF !important;
                    border: 1px solid #E2E8F0 !important;
                    color: #0F172A !important;
                    box-shadow: 0 15px 45px rgba(0,0,0,0.12) !important;
                }
                .notif-dropdown-box .notif-header {
                    background: #F8FAFC !important;
                    border-bottom: 1px solid #E2E8F0 !important;
                }
                .notif-dropdown-box .notif-header-title {
                    color: #0F172A !important;
                }
                .notif-dropdown-box .notif-item {
                    background: #FFFFFF !important;
                    color: #0F172A !important;
                    border-bottom: 1px solid #F1F5F9 !important;
                }
                .notif-dropdown-box .notif-item.unread {
                    background: #F0F9FF !important;
                }
                .notif-dropdown-box .notif-item:hover {
                    background: #F8FAFC !important;
                }
                .notif-dropdown-box .notif-item-title-success {
                    color: #059669 !important;
                }
                .notif-dropdown-box .notif-item-title-danger {
                    color: #E11D48 !important;
                }
                .notif-dropdown-box .notif-item-msg {
                    color: #334155 !important;
                }
                .notif-dropdown-box .notif-footer,
                .notif-dropdown-box .notif-footer * {
                    background: #F8FAFC !important;
                    border-top: 1px solid #E2E8F0 !important;
                    direction: ltr !important;
                    text-align: center !important;
                }
                .notif-dropdown-box .notif-empty-state,
                .notif-dropdown-box .notif-empty-state * {
                    text-align: center !important;
                    margin-left: auto !important;
                    margin-right: auto !important;
                }

                /* Dark Theme Overrides */
                html.dark-theme .notif-dropdown-box,
                body.dark-theme .notif-dropdown-box {
                    background: #1E293B !important;
                    border: 1px solid rgba(255,255,255,0.12) !important;
                    color: #F8FAFC !important;
                    box-shadow: 0 20px 50px rgba(0,0,0,0.4) !important;
                }
                html.dark-theme .notif-dropdown-box .notif-header,
                body.dark-theme .notif-dropdown-box .notif-header {
                    background: rgba(15, 23, 42, 0.8) !important;
                    border-bottom: 1px solid rgba(255,255,255,0.1) !important;
                }
                html.dark-theme .notif-dropdown-box .notif-header-title,
                body.dark-theme .notif-dropdown-box .notif-header-title {
                    color: #FFFFFF !important;
                }
                html.dark-theme .notif-dropdown-box .notif-item,
                body.dark-theme .notif-dropdown-box .notif-item {
                    background: #1E293B !important;
                    color: #F8FAFC !important;
                    border-bottom: 1px solid rgba(255,255,255,0.06) !important;
                }
                html.dark-theme .notif-dropdown-box .notif-item.unread,
                body.dark-theme .notif-dropdown-box .notif-item.unread {
                    background: rgba(6, 182, 212, 0.08) !important;
                }
                html.dark-theme .notif-dropdown-box .notif-item:hover,
                body.dark-theme .notif-dropdown-box .notif-item:hover {
                    background: rgba(255,255,255,0.04) !important;
                }
                html.dark-theme .notif-dropdown-box .notif-item-title-success,
                body.dark-theme .notif-dropdown-box .notif-item-title-success {
                    color: #10B981 !important;
                }
                html.dark-theme .notif-dropdown-box .notif-item-title-danger,
                body.dark-theme .notif-dropdown-box .notif-item-title-danger {
                    color: #F43F5E !important;
                }
                html.dark-theme .notif-dropdown-box .notif-item-msg,
                body.dark-theme .notif-dropdown-box .notif-item-msg {
                    color: #CBD5E1 !important;
                }
                html.dark-theme .notif-dropdown-box .notif-footer,
                body.dark-theme .notif-dropdown-box .notif-footer {
                    background: rgba(15, 23, 42, 0.9) !important;
                    border-top: 1px solid rgba(255,255,255,0.1) !important;
                }
                </style>

                <script>
                function deleteSingleNotification(e, id) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    const url = "{{ url('/admin/notifications') }}/" + id;
                    fetch(url, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.status === 'success') {
                            fetchAdminNotifications();
                        }
                    }).catch(err => console.error(err));
                }

                function clearAllNotifications(e) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    if (!confirm("Are you sure you want to clear all notifications?")) {
                        return;
                    }
                    fetch("{{ route('admin.notifications.clear_all', [], false) }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.status === 'success') {
                            fetchAdminNotifications();
                        }
                    }).catch(err => console.error(err));
                }

                function markAllNotificationsAsRead(e) {
                    if (e) e.preventDefault();
                    fetch("{{ route('admin.notifications.mark_all_read', [], false) }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.status === 'success') {
                            const badge = document.getElementById('notif-badge-count');
                            if (badge) {
                                badge.innerText = '0';
                                badge.classList.add('d-none');
                            }
                            const headerBadge = document.getElementById('notif-header-badge');
                            if (headerBadge) headerBadge.innerText = '0 New';
                            
                            fetchAdminNotifications();
                        }
                    }).catch(err => console.error(err));
                }

                function fetchAdminNotifications() {
                    fetch("{{ route('admin.notifications.fetch', [], false) }}", {
                        headers: { "Accept": "application/json" }
                    }).then(res => res.json()).then(data => {
                        const badge = document.getElementById('notif-badge-count');
                        if (badge) {
                            if (data.unread_count > 0) {
                                badge.innerText = data.unread_count;
                                badge.classList.remove('d-none');
                            } else {
                                badge.innerText = '0';
                                badge.classList.add('d-none');
                            }
                        }
                        const headerBadge = document.getElementById('notif-header-badge');
                        if (headerBadge) headerBadge.innerText = data.unread_count + ' New';

                        const listContainer = document.getElementById('notif-dropdown-list');
                        if (listContainer && data.notifications) {
                            if (data.notifications.length === 0) {
                                listContainer.innerHTML = '<div class="p-4 text-center notif-empty-state text-slate-400 fs-8" style="text-align: center !important;"><i class="bx bx-bell-off fs-2 d-block mb-1 opacity-50 text-center" style="text-align: center !important;"></i><span class="d-block text-center" style="text-align: center !important;">No notifications yet</span></div>';
                                return;
                            }

                            let html = '';
                            data.notifications.forEach(n => {
                                const isAccepted = n.is_accepted === true || n.status === 'accepted';
                                const iconClass = isAccepted ? 'bx-check-shield' : 'bx-x-circle';
                                const statusColorClass = isAccepted ? 'notif-item-title-success' : 'notif-item-title-danger';
                                const iconStyle = isAccepted 
                                    ? 'background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3);' 
                                    : 'background: rgba(244, 63, 94, 0.15); color: #F43F5E; border: 1px solid rgba(244, 63, 94, 0.3);';
                                
                                const bgClass = !n.is_read ? 'unread' : '';
                                const dotHtml = !n.is_read ? '<span class="rounded-circle bg-info flex-shrink-0" style="width: 7px; height: 7px; box-shadow: 0 0 8px #06B6D4;"></span>' : '';
                                const reasonHtml = (!isAccepted && n.rejection_reason) ? `<div class="fs-9 text-danger bg-danger bg-opacity-10 border border-danger border-opacity-20 rounded p-1 mt-1 text-truncate"><strong>Reason:</strong> ${n.rejection_reason}</div>` : '';

                                html += `
                                <div class="dropdown-item notif-item p-3 text-wrap d-flex align-items-start gap-2.5 transition-all ${bgClass} position-relative" 
                                     dir="ltr" 
                                     style="direction: ltr !important; text-align: left !important; cursor: pointer;"
                                     onclick="window.location.href='${n.target_url}'">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 36px; height: 36px; ${iconStyle}">
                                        <i class="bx ${iconClass} fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0" style="text-align: left !important; direction: ltr !important;">
                                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1" style="direction: ltr !important;">
                                            <span class="fw-bold fs-7 ${statusColorClass}">${n.title_en}</span>
                                            <small class="text-slate-400 fs-9 me-3">${n.created_at}</small>
                                        </div>
                                        <p class="mb-1 fs-8 notif-item-msg text-truncate-2" style="line-height: 1.35; text-align: left !important; direction: ltr !important;">${n.message}</p>
                                        ${reasonHtml}
                                    </div>
                                    <div class="d-flex flex-column align-items-end justify-content-between flex-shrink-0 ms-1" style="height: 100%;">
                                        <button type="button" class="btn btn-link p-0 text-slate-400 border-0 mb-1" 
                                                title="Delete Notification"
                                                style="line-height: 1; text-decoration: none; opacity: 0.6; transition: all 0.15s ease;"
                                                onmouseover="this.style.opacity='1'; this.style.color='#EF4444';"
                                                onmouseout="this.style.opacity='0.6'; this.style.color='';"
                                                onclick="deleteSingleNotification(event, '${n.id}')">
                                            <i class="bx bx-trash fs-6"></i>
                                        </button>
                                        ${dotHtml}
                                    </div>
                                </div>`;
                            });
                            listContainer.innerHTML = html;
                        }
                    }).catch(err => console.error(err));
                }

                // Poll notifications every 15 seconds
                setInterval(fetchAdminNotifications, 15000);
                </script>

                <!-- User Profile Dropdown Menu -->
                <div class="user-box dropdown">
                    <a class="d-flex align-items-center nav-link dropdown-toggle gap-2 text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ (!empty(Auth()->user()->photo) && file_exists(public_path('upload/user_images/'.Auth()->user()->photo))) ? asset('upload/user_images/'.Auth()->user()->photo) : ((!empty(Auth()->user()->photo) && file_exists(public_path('upload/admin_images/'.Auth()->user()->photo))) ? asset('upload/admin_images/'.Auth()->user()->photo) : asset('upload/no_image.jpg')) }}" 
                             class="user-img" 
                             alt="User Avatar">
                        <div class="user-info d-none d-sm-block text-start">
                            <p class="user-name mb-0 text-white fw-bold fs-6" style="line-height: 1.2;">{{ Auth()->user()->fname ?? 'Admin' }} {{ Auth()->user()->lname ?? 'User' }}</p>
                            <p class="designattion mb-0 text-slate-400" style="color: #94A3B8; font-size: 0.78rem;">{{ Auth()->user()->email ?? 'admin@salasil.com' }}</p>
                        </div>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2" style="background-color: #1E293B; min-width: 220px; border: 1px solid rgba(255,255,255,0.1) !important;">
                        <li>
                            <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 text-white" href="{{ route('profile.edit') }}">
                                <i class="bx bx-user fs-5 text-info"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider border-secondary opacity-25">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                            </form>
                            <a href="{{ route('logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                               class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 text-danger">
                                <i class="bx bx-log-out-circle fs-5"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>
