// ============================================================
// public/js/core/heartbeat.js
// Heartbeat — session keepalive, online users, logout guard
// ✅ 60s interval, skip bila tab hidden
// ✅ Tapis tourist dari widget online admin
// ✅ last_seen null → teks bermakna (bukan "null")
// ============================================================

let heartbeatInterval;

function startHeartbeat() {
    clearInterval(heartbeatInterval);
    heartbeatInterval = setInterval(sendHeartbeat, 60000);
}

function sendHeartbeat() {
    if (document.hidden) return;

    $.ajax({
        url: '/heartbeat', type: 'POST',
        success: function(response) {
            if (response.success) updateOnlineUsers(response.online_users);
        },
        error: function(xhr) {
            if (xhr.status === 401) window.location.href = '/login';
        }
    });
}

// ── Format last_seen: null / diffForHumans → teks Melayu ────
function fmtSeen(val) {
    if (!val || val === 'null' || val === 'undefined' || String(val).trim() === '') {
        return 'Baru sahaja';
    }
    return String(val)
        .replace(/\d+ seconds? ago/,  'baru sahaja')
        .replace('a second ago',      'baru sahaja')
        .replace('just now',          'baru sahaja')
        .replace('a minute ago',      '1 minit lalu')
        .replace('minutes ago',       'minit lalu')
        .replace('minute ago',        'minit lalu')
        .replace('an hour ago',       '1 jam lalu')
        .replace('hours ago',         'jam lalu')
        .replace('hour ago',          'jam lalu')
        .replace('a day ago',         'semalam')
        .replace('days ago',          'hari lalu')
        .replace('day ago',           'hari lalu');
}

// ── Render online users — navbar dropdown + dashboard widget ──
// Dipanggil oleh heartbeat response dan initial load
function updateOnlineUsers(users) {

    // Tapis tourist
    var staff = (users || []).filter(function (u) {
        return u.role !== 'tourist';
    });

    // ── 1. Kemaskini count badge (navbar) ──────────────────────
    $('#onlineUsersCount, #onlinePanelCount').text(staff.length);

    // ── 2. Render navbar dropdown list ────────────────────────
    var $navList = $('#onlineUsersNavList');
    if ($navList.length) {
        if (!staff.length) {
            $navList.html('<div class="ou-empty">Tiada admin lain online</div>');
        } else {
            $navList.html(staff.map(function (user) {
                var seen      = fmtSeen(user.last_seen);
                var roleLabel = user.role === 'admin_system' ? '🔧 Admin System' : '💼 Owner Business';
                var initLtr   = (user.name || '?').charAt(0).toUpperCase();

                var avatarOk  = user.avatar
                    && user.avatar !== 'null'
                    && user.avatar !== 'undefined'
                    && !String(user.avatar).endsWith('/null');

                var avatarHtml = avatarOk
                    ? '<img src="' + user.avatar + '" class="ou-user-avatar"'
                      + ' onerror="this.outerHTML=\'<div class=&quot;ou-user-avatar-init&quot;>' + initLtr + '</div>\'">'
                    : '<div class="ou-user-avatar-init">' + initLtr + '</div>';

                return '<div class="ou-user-row">'
                    + avatarHtml
                    + '<div class="ou-user-info">'
                    +   '<div class="ou-user-name">' + (user.name || user.email) + '</div>'
                    +   '<div class="ou-user-role">' + roleLabel + '</div>'
                    +   '<div class="ou-user-seen">🟢 ' + seen + '</div>'
                    + '</div>'
                    + '<span class="ou-dot"></span>'
                    + '</div>';
            }).join(''));
        }
    }

    // ── 3. Render dashboard widget (jika ada) ──────────────────
    var $dashList = $('#onlineUsersList');
    if (!$dashList.length) return;

    if (!staff.length) {
        $dashList.html('<span style="color:var(--text-muted);font-size:.82rem;">Tiada admin lain online</span>');
        return;
    }

    $dashList.html(staff.map(function (user) {
        var seen      = fmtSeen(user.last_seen);
        var roleLabel = user.role === 'admin_system' ? '🔧 Admin' : '💼 Owner';
        return '<span style="display:inline-flex;align-items:center;gap:.45rem;'
            + 'background:var(--input-readonly-bg);border:1px solid var(--border-color);'
            + 'border-radius:20px;padding:5px 13px;margin:.2rem;font-size:.78rem;font-weight:600;">'
            + '<span style="width:7px;height:7px;background:#22c55e;border-radius:50%;'
            + 'flex-shrink:0;box-shadow:0 0 0 2px rgba(34,197,94,.2);display:inline-block;"></span>'
            + '<span>' + (user.name || user.email) + '</span>'
            + '<span style="font-size:.68rem;font-weight:500;color:var(--text-muted);'
            + 'background:rgba(0,0,0,.06);padding:1px 6px;border-radius:10px;">'
            + roleLabel + ' · ' + seen + '</span>'
            + '</span>';
    }).join(''));
}

$(document).ready(function() {
    setTimeout(sendHeartbeat, 5000);
    startHeartbeat();

    // Load online users sekali on init
    $.get('/online-users', function(users) { updateOnlineUsers(users); });

    // Pause/resume ikut tab visibility
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(heartbeatInterval);
        } else {
            sendHeartbeat();
            startHeartbeat();
        }
    });
});

$(document).on('submit', '#logoutForm', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to logout?')) this.submit();
});