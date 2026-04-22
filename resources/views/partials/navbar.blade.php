<!-- ================= TOP NAVBAR WITH AVATAR & LOGOUT ================= -->
<div class="top-navbar" id="mainNavbar">

    

    <div class="navbar-left">
    
        <div class="app-logo">
            <span class="logo-icon">👥</span>
            <span class="logo-text">Arson system</span>
        </div>
        <div class="online-status-indicator">
            <span class="status-dot online"></span>
            <span class="status-text">System Online</span>
        </div>
    </div>

    <div class="navbar-right">

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- TOURIST PORTAL BUTTON                                  --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <a href="{{ route('tourist.index') }}" class="btn-tourist-nav" title="Buka Portal Pelancong">
            <span>🌏</span>
            <span class="tourist-btn-label">Portal Pelancong</span>
        </a>

        <!-- Online Users Dropdown -->
<div class="ou-dropdown-wrap" id="onlineUsersDropdownWrap">
    <button class="ou-trigger" id="onlineUsersTrigger" title="Admin Online" type="button">
        <span class="ou-trigger-icon">👥</span>
        <span class="ou-trigger-count" id="onlineUsersCount">0</span>
    </button>

    <div class="ou-panel" id="onlineUsersPanel" style="display:none;">
        <div class="ou-panel-header">
            <span>🟢 Admin Online</span>
            <span class="ou-panel-badge" id="onlinePanelCount">0</span>
        </div>
        <div class="ou-panel-body" id="onlineUsersNavList">
            <div class="ou-empty">⏳ Memuatkan...</div>
        </div>
    </div>
</div>

        <!-- Notifications Bell -->
        <div class="notifications-bell" id="notificationsBell" title="Notifications">
            <span class="bell-icon">🔔</span>
            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>

            <!-- Notification Dropdown -->
            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position:absolute; right:0; top:50px; width:400px; background:var(--surface, white); border:1px solid var(--border-color, #e2e8f0); border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.15); z-index:9999; overflow:hidden;">
                <div class="dropdown-header" style="display:flex; justify-content:space-between; align-items:center; padding:15px; font-weight:bold; border-bottom:2px solid #eee; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white;">
                    <span style="font-size:1rem;">🔔 Notifications</span>
                    <button type="button" id="deleteAllBtn" style="font-size:0.75rem; background:rgba(255,255,255,0.3); color:white; border:none; border-radius:6px; padding:6px 12px; cursor:pointer; font-weight:600; transition: all 0.2s;">
                        🗑️ Clear All
                    </button>
                </div>
                <div class="dropdown-items" id="notificationList" style="max-height:450px; overflow-y:auto;">
                    <div style="padding:30px; text-align:center; color:#999;">
                        <div style="font-size:3rem; margin-bottom:10px;">🔔</div>
                        <div>No notifications</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="user-profile-dropdown" id="userProfileDropdown">
            @php
                $__initLetter = strtoupper(substr(auth()->user()->name, 0, 1));
                $__svgFallback = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" rx="50" fill="#667eea"/><text x="50" y="65" text-anchor="middle" font-family="Arial" font-size="45" font-weight="bold" fill="white">' . $__initLetter . '</text></svg>');
            @endphp
            <div class="profile-trigger" id="profileTrigger">
                <img src="{{ auth()->user()->avatar_url ?? $__svgFallback }}" alt="{{ auth()->user()->name }}" class="user-avatar" id="userAvatar" onerror="this.onerror=null;this.src='{{ $__svgFallback }}'">
                <div class="user-info">
                    <div class="user-name" id="userName">{{ auth()->user()->name }}</div>

                    {{-- ROLE BADGE IKUT ROLE --}}
                    @php $role = auth()->user()->role; @endphp
                    <div class="user-role-pill role-pill-{{ $role }}" id="userRole">
                        @if($role === 'tester')             🧪 Tester
                        @elseif($role === 'admin_system')   🔧 Admin System
                        @elseif($role === 'owner_business') 💼 Owner Business
                        @elseif($role === 'tourist')        ✈️ Tourist
                        @else {{ ucfirst($role) }}
                        @endif
                    </div>
                </div>
                <span class="dropdown-arrow">▼</span>
            </div>

            <!-- Dropdown Menu -->
            <div class="profile-dropdown-menu" id="profileDropdownMenu" style="display: none;">
                <div class="dropdown-header-profile">
                    <img src="{{ auth()->user()->avatar_url ?? $__svgFallback }}" alt="{{ auth()->user()->name }}" class="dropdown-avatar" onerror="this.onerror=null;this.src='{{ $__svgFallback }}'">
                    <div class="dropdown-user-info">
                        <div class="dropdown-user-name">{{ auth()->user()->name }}</div>
                        <div class="dropdown-user-email">{{ auth()->user()->email }}</div>

                        {{-- ROLE BADGE DALAM DROPDOWN --}}
                        @if($role === 'tester')
                            <div class="role-badge-big" style="background:linear-gradient(135deg,#ff6b35,#f7931e);">🧪 Tester (Demo Mode)</div>
                        @elseif($role === 'admin_system')
                            <div class="role-badge-big" style="background:linear-gradient(135deg,#9c27b0,#673ab7);">🔧 Admin System</div>
                        @elseif($role === 'owner_business')
                            <div class="role-badge-big" style="background:linear-gradient(135deg,#1565c0,#0288d1);">💼 Owner Business</div>
                        @elseif($role === 'tourist')
                            <div class="role-badge-big" style="background:linear-gradient(135deg,#2e7d32,#388e3c);">✈️ Tourist</div>
                        @endif

                        {{-- PERMISSION SUMMARY --}}
                        <div class="permission-summary">
                            @if($role === 'tester')
                                <span class="perm-tag">✅ Semua Fungsi</span>
                                <span class="perm-tag perm-warning">⚠️ Data Tidak Simpan</span>
                            @elseif($role === 'admin_system')
                                <span class="perm-tag">✅ Full Access</span>
                                <span class="perm-tag">👤 User Management</span>
                            @elseif($role === 'owner_business')
                                <span class="perm-tag">✅ Trip & Booking</span>
                                <span class="perm-tag">✅ Customer & Resit</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                {{-- TOURIST PORTAL LINK DALAM DROPDOWN --}}
                <a href="{{ route('tourist.index') }}" class="dropdown-item dropdown-item-tourist" target="_blank">
                    <span class="item-icon">🌏</span>
                    <span class="item-text">Portal Pelancong</span>
                    <span style="font-size:0.72rem; color:#00d4aa; font-weight:700; margin-left:auto;">↗ Buka</span>
                </a>

                <div class="dropdown-divider"></div>

                <a href="#" class="dropdown-item" id="uploadAvatarBtn">
                    <span class="item-icon">📷</span>
                    <span class="item-text">Tukar Avatar</span>
                </a>

                <a href="#" class="dropdown-item" id="viewProfileBtn">
                    <span class="item-icon">👤</span>
                    <span class="item-text">Profil Saya</span>
                </a>

                <div class="dropdown-item status-item" style="cursor:default;">
                    <div style="display:flex;align-items:center;gap:8px;flex:1;">
                        <span style="position:relative;display:inline-flex;">
                            <span style="width:10px;height:10px;border-radius:50%;background:#38a169;display:inline-block;"></span>
                            <span style="position:absolute;width:10px;height:10px;border-radius:50%;background:#38a169;animation:onlinePing 1.5s ease-in-out infinite;"></span>
                        </span>
                        <span class="item-text" style="color:#38a169;font-weight:700;">Sedang Online</span>
                    </div>
                    <span id="onlineTime" style="
                        font-size:0.72rem;font-weight:700;
                        color:#276749;background:#f0fff4;
                        border:1px solid #9ae6b4;
                        padding:2px 9px;border-radius:10px;
                        white-space:nowrap;
                    ">⏱️ --:--:--</span>
                </div>

                <div class="dropdown-divider"></div>

                <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="dropdown-item logout-item">
                        <span class="item-icon">🚪</span>
                        <span class="item-text">Log Keluar</span>
                        <span class="logout-arrow">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── SIDEBAR OVERLAY (tap to close on mobile) ── --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Hidden File Input for Avatar Upload -->
<input type="file" id="avatarFileInput" accept="image/png,image/jpeg,image/jpg" style="display: none;">

<!-- ✅ Avatar Preview Modal - FIXED VERSION -->
<div id="avatarPreviewModal" class="modal-overlay">
    <div class="modal-content avatar-modal">
        <span class="modal-close" id="closeAvatarModal">✖</span>
        <h2 class="modal-header">📷 Upload New Avatar</h2>
        <div class="avatar-preview-container">
            <div class="avatar-preview-box">
                <img src="" alt="Preview" id="avatarPreview" class="avatar-preview-image">
                <div class="avatar-preview-placeholder" id="avatarPlaceholder">
                    <span class="placeholder-icon">📷</span>
                    <span class="placeholder-text">No image selected</span>
                </div>
            </div>
            <div class="avatar-preview-info">
                <div class="info-item">
                    <span class="info-label">File:</span>
                    <span class="info-value" id="fileNameDisplay">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Size:</span>
                    <span class="info-value" id="fileSizeDisplay">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dimensions:</span>
                    <span class="info-value" id="fileDimensionsDisplay">-</span>
                </div>
            </div>
        </div>
        <div class="avatar-upload-requirements">
            <div class="requirement-title">📋 Requirements:</div>
            <ul class="requirement-list">
                <li>✅ Format: JPG or PNG only</li>
                <li>✅ Max size: 2MB</li>
                <li>✅ Recommended: Square image (500x500px)</li>
                <li>✅ Min dimensions: 200x200px</li>
            </ul>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelAvatarBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="selectAvatarBtn">📁 Select Image</button>
            <button type="button" class="btn btn-success" id="uploadAvatarSubmitBtn" style="display: none;">✅ Upload Avatar</button>
        </div>
    </div>
</div>

{{-- ================================================================== --}}
{{-- ✅ NOTIFICATION SYSTEM — diload terus dalam navbar                  --}}
{{-- Mesti di sini supaya berfungsi di semua halaman yang ada navbar ini --}}
{{-- ================================================================== --}}
<script>
// ── 1. Definisikan window.showNotification SEBELUM notifications.js load ──
(function() {
    if (typeof window.showNotification !== 'undefined') return;

    function getToastContainer() {
        var c = document.getElementById('_notif_toast_container');
        if (!c) {
            c = document.createElement('div');
            c.id = '_notif_toast_container';
            c.style.cssText = [
                'position:fixed',
                'top:80px',
                'right:20px',
                'z-index:99999',
                'display:flex',
                'flex-direction:column',
                'gap:8px',
                'pointer-events:none',
            ].join(';');
            document.body.appendChild(c);
        }
        return c;
    }

    var COLORS = {
        success : { bg: '#d1fae5', border: '#10b981', icon: '✅' },
        error   : { bg: '#fee2e2', border: '#ef4444', icon: '❌' },
        warning : { bg: '#fef3c7', border: '#f59e0b', icon: '⚠️' },
        info    : { bg: '#dbeafe', border: '#3b82f6', icon: 'ℹ️' },
    };

    window.showNotification = function(msg, type, persistent) {
        if (typeof window.showToast === 'function') {
            window.showToast(msg, type);
            return;
        }

        var c   = getToastContainer();
        var cfg = COLORS[type] || COLORS.info;
        var el  = document.createElement('div');
        el.style.cssText = [
            'display:flex',
            'align-items:flex-start',
            'gap:10px',
            'padding:12px 16px',
            'border-radius:10px',
            'border-left:4px solid ' + cfg.border,
            'background:' + cfg.bg,
            'box-shadow:0 4px 20px rgba(0,0,0,.12)',
            'font-size:13px',
            'font-weight:600',
            'color:#1a1a2e',
            'max-width:360px',
            'pointer-events:auto',
            'opacity:0',
            'transform:translateX(30px)',
            'transition:all .3s ease',
            'cursor:pointer',
        ].join(';');
        el.innerHTML = '<span style="font-size:1.1rem;flex-shrink:0;">' + cfg.icon + '</span>'
                     + '<span style="flex:1;line-height:1.5;">' + (msg || '') + '</span>';
        el.title = 'Klik untuk tutup';
        el.addEventListener('click', function() { dismiss(el); });
        c.appendChild(el);

        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                el.style.opacity = '1';
                el.style.transform = 'translateX(0)';
            });
        });

        if (!persistent) {
            setTimeout(function() { dismiss(el); }, 4000);
        }

        function dismiss(elem) {
            elem.style.opacity = '0';
            elem.style.transform = 'translateX(30px)';
            setTimeout(function() {
                if (elem.parentNode) elem.parentNode.removeChild(elem);
            }, 300);
        }
    };

    if (typeof window.showToast === 'undefined') {
        window.showToast = function(msg, type) {
            window.showNotification(msg, type);
        };
    }
})();

// ── Online session timer ───────────────────────────────────────
(function() {
    var _loginTime = null;

    var stored = sessionStorage.getItem('_session_start');
    if (stored) {
        _loginTime = new Date(parseInt(stored, 10));
    } else {
        _loginTime = new Date();
        sessionStorage.setItem('_session_start', String(_loginTime.getTime()));
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function updateTimer() {
        var $el = $('#onlineTime');
        if (!$el.length) return;

        var now     = new Date();
        var diff    = Math.floor((now - _loginTime) / 1000);
        var hours   = Math.floor(diff / 3600);
        var minutes = Math.floor((diff % 3600) / 60);
        var seconds = diff % 60;

        var label = '';
        if (hours > 0) {
            label = '⏱️ ' + hours + 'j ' + pad(minutes) + 'm ' + pad(seconds) + 's';
        } else if (minutes > 0) {
            label = '⏱️ ' + minutes + 'm ' + pad(seconds) + 's';
        } else {
            label = '⏱️ ' + seconds + 's';
        }

        $el.text(label);
    }

    $(function() {
        updateTimer();
        setInterval(updateTimer, 1000);
    });
})();
</script>

<style>
/* ===============================================
   TOP NAVBAR STYLES
   =============================================== */
.top-navbar {
    position: fixed;
    top: 0; left: 250px; right: 0;
    height: 70px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    z-index: 999;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.navbar-left { display:flex; align-items:center; gap:30px; }
.app-logo { display:flex; align-items:center; gap:10px; color:white; font-size:1.3rem; font-weight:700; }
.logo-icon { font-size:1.8rem; animation:bounce 2s infinite; }

.online-status-indicator {
    display:flex; align-items:center; gap:8px;
    background:rgba(255,255,255,0.2); padding:8px 16px;
    border-radius:20px; color:white; font-size:0.85rem;
}

.status-dot { width:10px; height:10px; border-radius:50%; background:#4caf50; animation:pulse 2s infinite; }
.status-dot.online { background:#4caf50; box-shadow:0 0 10px #4caf50; }

@keyframes pulse {
    0%,100% { opacity:1; transform:scale(1); }
    50% { opacity:0.7; transform:scale(1.2); }
}

.navbar-right { display:flex; align-items:center; gap:15px; }

/* ══════════════════════════════════════════════════
   TOURIST PORTAL BUTTON
══════════════════════════════════════════════════ */
.btn-tourist-nav {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.45rem 1.1rem; border-radius: 20px;
    background: rgba(0, 212, 170, 0.2);
    border: 1.5px solid rgba(0, 212, 170, 0.55);
    color: white; font-size: 0.82rem; font-weight: 700;
    text-decoration: none; white-space: nowrap; transition: all 0.25s;
    box-shadow: 0 2px 8px rgba(0,212,170,0.15);
}
.btn-tourist-nav:hover {
    background: rgba(0, 212, 170, 0.38); border-color: #00d4aa;
    transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,212,170,0.3);
}
.dropdown-item-tourist { border-left: 3px solid #00d4aa !important; }
.dropdown-item-tourist:hover { background: #f0fdf9 !important; }

/* ── ROLE PILLS ─────────────────────────────── */
.user-role-pill {
    font-size:0.7rem; font-weight:700; padding:2px 8px;
    border-radius:10px; display:inline-block; margin-top:2px;
}
.role-pill-tester         { background:#ff6b35; color:white; }
.role-pill-admin_system   { background:#9c27b0; color:white; }
.role-pill-owner_business { background:#1565c0; color:white; }
.role-pill-tourist        { background:#2e7d32; color:white; }

.role-badge-big {
    display:inline-block; padding:4px 12px; border-radius:12px;
    font-size:0.78rem; color:white; font-weight:700; margin-bottom:8px;
}

.permission-summary { display:flex; flex-wrap:wrap; gap:4px; margin-top:4px; }
.perm-tag { font-size:0.68rem; padding:2px 7px; border-radius:8px; background:rgba(255,255,255,0.2); color:white; font-weight:600; }
.perm-warning { background:rgba(255,152,0,0.4); }

/* Online Users Badge */
.online-users-badge {
    display:flex; align-items:center; gap:8px;
    background:rgba(255,255,255,0.2); padding:10px 16px;
    border-radius:25px; color:white; cursor:pointer; transition:all 0.3s;
}
.online-users-badge:hover { background:rgba(255,255,255,0.3); transform:translateY(-2px); }
.badge-icon { font-size:1.2rem; }
.badge-count { font-weight:700; font-size:0.95rem; }

/* Notifications Bell */
.notifications-bell {
    position:relative; background:rgba(255,255,255,0.2);
    width:45px; height:45px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; transition:all 0.3s;
}
.notifications-bell:hover { background:rgba(255,255,255,0.3); transform:scale(1.1); }
.bell-icon { font-size:1.3rem; }
.notification-badge {
    position:absolute; top:-5px; right:-5px;
    background:#f44336; color:white; font-size:0.7rem;
    font-weight:700; padding:3px 7px; border-radius:10px;
    min-width:20px; text-align:center;
}

/* User Profile Dropdown */
.user-profile-dropdown { position:relative; }
.profile-trigger {
    display:flex; align-items:center; gap:12px;
    background:rgba(255,255,255,0.2); padding:8px 16px 8px 8px;
    border-radius:30px; cursor:pointer; transition:all 0.3s;
}
.profile-trigger:hover { background:rgba(255,255,255,0.3); transform:translateY(-2px); }
.user-avatar {
    width:45px; height:45px; border-radius:50%;
    border:3px solid white; object-fit:cover; box-shadow:0 2px 10px rgba(0,0,0,0.2);
}
.user-info { display:flex; flex-direction:column; gap:1px; }
.user-name { color:white; font-weight:700; font-size:0.95rem; line-height:1; }
.dropdown-arrow { color:white; font-size:0.7rem; transition:transform 0.3s; }
.profile-trigger.active .dropdown-arrow { transform:rotate(180deg); }

.profile-dropdown-menu {
    position:absolute; top:60px; right:0; width:320px;
    background:white; border-radius:15px;
    box-shadow:0 10px 40px rgba(0,0,0,0.2);
    overflow:hidden; animation:slideDown 0.3s ease; z-index:1000;
}

@keyframes slideDown {
    from { opacity:0; transform:translateY(-10px); }
    to   { opacity:1; transform:translateY(0); }
}

.dropdown-header-profile {
    background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding:20px; display:flex; gap:15px; align-items:flex-start;
}
.dropdown-avatar { width:55px; height:55px; border-radius:50%; border:3px solid white; object-fit:cover; flex-shrink:0; }
.dropdown-user-info { flex:1; }
.dropdown-user-name { color:white; font-weight:700; font-size:1rem; margin-bottom:3px; }
.dropdown-user-email { color:rgba(255,255,255,0.85); font-size:0.78rem; margin-bottom:6px; }
.dropdown-divider { height:1px; background:#e2e8f0; margin:6px 0; }

.dropdown-item {
    display:flex; align-items:center; gap:12px; padding:13px 20px;
    color:#2d3748; text-decoration:none; transition:all 0.2s;
    cursor:pointer; border:none; background:none;
    width:100%; text-align:left; font-size:0.92rem;
}
.dropdown-item:hover { background:#f7fafc; color:#667eea; }
.item-icon { font-size:1.1rem; width:22px; text-align:center; }
.item-text { flex:1; font-weight:500; }
.status-item { cursor:default; }
.status-item:hover { background:transparent; color:#2d3748; }
.status-time { font-size:0.78rem; color:#a0aec0; }

@keyframes onlinePing {
    0%   { transform: scale(1);   opacity: 0.8; }
    70%  { transform: scale(2.2); opacity: 0;   }
    100% { transform: scale(1);   opacity: 0;   }
}
.logout-item { color:#f56565; font-weight:600; }
.logout-item:hover { background:#fff5f5; color:#e53e3e; }
.logout-arrow { font-size:1.1rem; transition:transform 0.2s; }
.logout-item:hover .logout-arrow { transform:translateX(5px); }

/* Avatar Upload Modal */
.avatar-modal { max-width:600px; }
.avatar-preview-container { display:flex; gap:20px; margin:20px 0; }
.avatar-preview-box {
    flex:1; aspect-ratio:1; border:3px dashed #cbd5e0;
    border-radius:15px; display:flex; align-items:center;
    justify-content:center; overflow:hidden; position:relative; background:#f7fafc;
}
.avatar-preview-image { width:100%; height:100%; object-fit:cover; display:none; }
.avatar-preview-image.show { display:block; }
.avatar-preview-placeholder { text-align:center; color:#a0aec0; }
.placeholder-icon { font-size:3.5rem; display:block; margin-bottom:8px; }
.placeholder-text { font-size:0.85rem; }
.avatar-preview-info { flex:1; display:flex; flex-direction:column; gap:12px; }
.info-item { display:flex; flex-direction:column; gap:4px; }
.info-label { font-size:0.82rem; color:#718096; font-weight:600; }
.info-value { font-size:0.9rem; color:#2d3748; padding:7px 10px; background:#f7fafc; border-radius:7px; }
.avatar-upload-requirements { background:#edf2f7; padding:14px; border-radius:10px; margin:15px 0; }
.requirement-title { font-weight:700; color:#2d3748; margin-bottom:8px; font-size:0.9rem; }
.requirement-list { list-style:none; padding:0; margin:0; }
.requirement-list li { padding:4px 0; font-size:0.85rem; color:#4a5568; }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; }

/* Notification Dropdown */
.notification-dropdown { animation:slideDownFade 0.3s ease; }
@keyframes slideDownFade {
    from { opacity:0; transform:translateY(-10px); }
    to   { opacity:1; transform:translateY(0); }
}
#deleteAllBtn:hover { background:rgba(255,255,255,0.5) !important; transform:scale(1.05); }

.notification-item-wrapper {
    display:flex; align-items:flex-start; gap:10px; padding:12px 15px;
    border-bottom:1px solid #eee; transition:background 0.2s; cursor:pointer;
}
.notification-item-wrapper:hover { background:#f8f9fa; }
.notification-item-wrapper:last-child { border-bottom:none; }
.notification-item-content { flex:1; }
.notification-item-delete {
    width:30px; height:30px; display:flex; align-items:center; justify-content:center;
    background:transparent; border:none; border-radius:6px; cursor:pointer;
    transition:all 0.2s; color:#999; font-size:1rem;
}
.notification-item-delete:hover { background:#fee; color:#f44336; }

/* Responsive */
@media (max-width: 1100px) {
    .tourist-btn-label { display: none; }
    .btn-tourist-nav   { padding: 0.45rem 0.7rem; }
}
@media (max-width: 768px) {
    .top-navbar              { left: 0; padding: 0 15px; }
    .app-logo .logo-text     { display: none; }
    .online-status-indicator { display: none; }
    .user-info               { display: none; }
    .profile-dropdown-menu   { width: 280px; }
    .btn-tourist-nav         { padding: 0.4rem 0.6rem; }
}

.main-content { margin-top: 70px; }
body.tester-active .top-navbar   { top: 34px; }
body.tester-active .main-content { margin-top: 104px; }

/* ══════════════════════════════════════════════
   DARK MODE
══════════════════════════════════════════════ */
body.dark .profile-dropdown-menu,
html[data-theme='dark'] .profile-dropdown-menu {
    background: #161b22; border: 1px solid #30363d;
    box-shadow: 0 10px 40px rgba(0,0,0,.5);
}
body.dark .dropdown-divider,
html[data-theme='dark'] .dropdown-divider { background: #30363d; }
body.dark .dropdown-item,
html[data-theme='dark'] .dropdown-item { color: #e6edf3; background: transparent; }
body.dark .dropdown-item:hover,
html[data-theme='dark'] .dropdown-item:hover { background: #21262d; color: #818cf8; }
body.dark .status-item:hover,
html[data-theme='dark'] .status-item:hover { background: transparent !important; color: #e6edf3 !important; }
body.dark .status-time,
html[data-theme='dark'] .status-time { color: #6e7681; }
body.dark .logout-item,
html[data-theme='dark'] .logout-item { color: #f87171; }
body.dark .logout-item:hover,
html[data-theme='dark'] .logout-item:hover { background: rgba(248,113,113,.12) !important; color: #fca5a5 !important; }
body.dark .notification-dropdown,
html[data-theme='dark'] .notification-dropdown { background: #161b22 !important; border: 1px solid #30363d; }
body.dark .notification-item-wrapper:hover,
html[data-theme='dark'] .notification-item-wrapper:hover { background: #21262d; }
body.dark .notification-item-wrapper,
html[data-theme='dark'] .notification-item-wrapper { border-bottom-color: #30363d; }
body.dark .avatar-preview-box,
html[data-theme='dark'] .avatar-preview-box { background: #21262d; border-color: #30363d; }
body.dark .info-value,
html[data-theme='dark'] .info-value { color: #e6edf3; background: #21262d; }
body.dark .avatar-upload-requirements,
html[data-theme='dark'] .avatar-upload-requirements { background: #21262d; border: 1px solid #30363d; }
body.dark .requirement-title,
html[data-theme='dark'] .requirement-title { color: #e6edf3; }
body.dark .requirement-list li,
html[data-theme='dark'] .requirement-list li { color: #8b949e; }
body.dark .dropdown-item-tourist:hover,
html[data-theme='dark'] .dropdown-item-tourist:hover { background: rgba(0,212,170,.1) !important; }


/* ══════════════════════════════════════════════
   HAMBURGER BUTTON
══════════════════════════════════════════════ */
.hamburger-btn {
    display: none !important;
    flex-direction: column; justify-content: center;
    align-items: center; gap: 5px;
    width: 40px; height: 40px;
    background: rgba(255,255,255,0.18) !important;
    border: 1.5px solid rgba(255,255,255,0.35) !important;
    border-radius: 10px; cursor: pointer; padding: 8px 7px;
    flex-shrink: 0; transition: background .2s;
}
.hamburger-btn:hover { background: rgba(255,255,255,0.3) !important; }

.hb-line {
    display: block; width: 20px; height: 2.5px;
    background: white !important; border-radius: 2px;
    transition: all .28s cubic-bezier(.4,0,.2,1); transform-origin: center;
}
body.sidebar-open .hb-line:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
body.sidebar-open .hb-line:nth-child(2) { opacity: 0; transform: scaleX(0); }
body.sidebar-open .hb-line:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

@media (min-width: 1025px) {
    .hamburger-btn {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
        width: 0 !important; height: 0 !important;
        padding: 0 !important; margin: 0 !important;
        border: none !important; overflow: hidden !important;
    }
}
@media (max-width: 1024px) {
    .hamburger-btn { display: flex !important; }
}

.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 850;
}
body.sidebar-open .sidebar-overlay { display: block; }

/* ══════════════════════════════════════════════
   ONLINE USERS DROPDOWN
══════════════════════════════════════════════ */
.ou-dropdown-wrap {
    position: relative;
}

/* Trigger button — sama gaya dengan notification bell */
.ou-trigger {
    display: flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,0.2);
    border: none;
    padding: 8px 14px;
    border-radius: 25px;
    color: white;
    cursor: pointer;
    transition: all 0.25s;
    font-family: inherit;
}
.ou-trigger:hover,
.ou-trigger.active {
    background: rgba(255,255,255,0.32);
    transform: translateY(-2px);
}
.ou-trigger-icon { font-size: 1.1rem; }
.ou-trigger-count { font-weight: 700; font-size: 0.95rem; }

/* Dropdown panel */
.ou-panel {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 300px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.18);
    border: 1px solid #e2e8f0;
    z-index: 9999;
    overflow: hidden;
    animation: slideDownFade 0.25s ease;
}

.ou-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 700;
    font-size: 0.85rem;
}
.ou-panel-badge {
    background: rgba(255,255,255,0.25);
    padding: 2px 9px;
    border-radius: 12px;
    font-size: 0.78rem;
    font-weight: 700;
}

.ou-panel-body {
    max-height: 320px;
    overflow-y: auto;
    padding: 8px;
}

/* Each user row */
.ou-user-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 10px;
    transition: background 0.15s;
}
.ou-user-row:hover { background: #f7fafc; }
.ou-user-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.ou-user-avatar-init {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 0.85rem; font-weight: 700;
    flex-shrink: 0;
}
.ou-user-info { flex: 1; min-width: 0; }
.ou-user-name {
    font-weight: 600;
    font-size: 0.85rem;
    color: #2d3748;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ou-user-role { font-size: 0.7rem; color: #718096; margin-top: 1px; }
.ou-user-seen {
    font-size: 0.68rem;
    color: #22c55e;
    font-weight: 600;
    margin-top: 2px;
}
.ou-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #22c55e;
    flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(34,197,94,0.22);
}
.ou-empty {
    padding: 24px;
    text-align: center;
    color: #a0aec0;
    font-size: 0.83rem;
}

/* Dark mode */
body.dark .ou-panel,
html[data-theme='dark'] .ou-panel {
    background: #161b22;
    border-color: #30363d;
}
body.dark .ou-user-row:hover,
html[data-theme='dark'] .ou-user-row:hover { background: #21262d; }
body.dark .ou-user-name,
html[data-theme='dark'] .ou-user-name { color: #e6edf3; }
body.dark .ou-user-role,
html[data-theme='dark'] .ou-user-role { color: #6e7681; }

@media (max-width: 768px) {
    .ou-panel { width: 260px; right: -60px; }
}
.sidebar {
    z-index: 900 !important;
}

.sidebar-overlay {
    z-index: 850 !important;
}

/* ══════════════════════════════════════════════
   ✅ MODAL SYSTEM - CONSOLIDATED & FIXED
══════════════════════════════════════════════ */

/* Base modal overlay - ALWAYS hidden by default */
.modal-overlay,
#avatarPreviewModal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

/* Show modal when .show class added */
.modal-overlay.show,
#avatarPreviewModal.show {
    display: flex !important;
}

/* Avatar modal gets higher z-index */
#avatarPreviewModal {
    z-index: 10001;
}

/* ══════════════════════════════════════════════
   SIDEBAR MOBILE — WAJIB ADA
══════════════════════════════════════════════ */
@media (max-width: 1024px) {

    /* Sidebar tersembunyi di kiri secara default */
    .sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        height: 100vh !important;
        width: 260px !important;
        transform: translateX(-100%) !important;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        z-index: 900 !important;
        overflow-y: auto !important;
    }

    /* Sidebar muncul bila .active */
    .sidebar.active {
        transform: translateX(0) !important;
        box-shadow: 4px 0 20px rgba(0,0,0,0.3) !important;
    }

    /* Navbar jadi full width pada mobile */
    .top-navbar {
        left: 0 !important;
    }

    /* Main content tidak perlu margin sidebar pada mobile */
    .main-content {
        margin-left: 0 !important;
    }
}

</style>  

{{-- ── JS Guard: MESTI di luar <style> ── --}}
<script>

// ── Online Users Dropdown toggle ─────────────────────────────
(function () {
    $(document).on('click', '#onlineUsersTrigger', function (e) {
        e.stopPropagation();
        var $panel  = $('#onlineUsersPanel');
        var $btn    = $('#onlineUsersTrigger');
        var visible = $panel.is(':visible');

        // Tutup semua dropdown lain
        $('#notificationDropdown').hide();
        $('#profileDropdownMenu').fadeOut(150);
        $('#profileTrigger').removeClass('active');

        if (visible) {
            $panel.hide();
            $btn.removeClass('active');
        } else {
            $panel.show();
            $btn.addClass('active');
        }
    });

    // Klik luar → tutup
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#onlineUsersDropdownWrap').length) {
            $('#onlineUsersPanel').hide();
            $('#onlineUsersTrigger').removeClass('active');
        }
    });

    // Jangan propagate klik dalam panel
    $(document).on('click', '#onlineUsersPanel', function (e) {
        e.stopPropagation();
    });
})();
</script>