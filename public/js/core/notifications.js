// ============================================================
// public/js/core/notifications.js
// Notification System — guna table notifications
// ============================================================

console.log('🔔 Notifications system loading...');

if (typeof window.showNotification !== 'function') {
    window.showNotification = function(msg, type) { console.warn('[notif fallback]', type, msg); };
}

var _lastNotifId = parseInt(localStorage.getItem('notif_last_id') || '0', 10);


// ── Polling setiap 5 saat ─────────────────────────────────────
function checkNewNotifications() {
    $.get('/notifications/unread-count')
    .done(function(res) {
        if (!res || !res.success) return;

        var count = parseInt(res.count || 0, 10);
        if (count > 0) {
            $('#notificationBadge').text(count).show();
        } else {
            $('#notificationBadge').hide();
        }

        if (res.latest) {
            var latestId = parseInt(res.latest.id, 10);
            if (latestId > _lastNotifId) {
                _lastNotifId = latestId;
                localStorage.setItem('notif_last_id', String(latestId));

                window.showNotification(
                    (res.latest.icon || '🔔') + ' ' +
                    (res.latest.title  || '')  + ': ' +
                    (res.latest.message || ''),
                    'info'
                );

                if ($('#dashboardView').is(':visible') && typeof window.loadCustomersFromServer === 'function') {
                    setTimeout(function() { window.loadCustomersFromServer({}, true); }, 1200);
                }
            }
        }
    })
    .fail(function(xhr) {
        if (xhr.status !== 401) console.warn('⚠️ Notification poll failed:', xhr.status);
    });
}

setInterval(checkNewNotifications, 5000);
setTimeout(checkNewNotifications, 2000);


// ── Bell click ────────────────────────────────────────────────
$(document).on('click', '#notificationsBell', function(e) {
    e.preventDefault(); e.stopPropagation();
    var $dd = $('#notificationDropdown');
    if ($dd.is(':visible')) { $dd.hide(); } else { loadList(); $dd.show(); }
});


// ── Load list ─────────────────────────────────────────────────
function loadList() {
    $('#notificationList').html(
        '<div style="padding:30px;text-align:center;color:#999;">' +
        '<div style="font-size:2rem;">⏳</div><div style="margin-top:8px;font-size:.9rem;">Memuatkan...</div></div>'
    );
    $.get('/notifications').done(function(res) {
        renderList(res && res.data ? res.data : []);
    }).fail(function() {
        $('#notificationList').html(
            '<div style="padding:30px;text-align:center;color:#e53e3e;">' +
            '<div style="font-size:2rem;">❌</div><div style="margin-top:8px;">Gagal memuatkan</div></div>'
        );
    });
}

function renderList(items) {
    var $list = $('#notificationList').empty();
    var unread = (items || []).filter(function(n) { return !n.is_read; });

    if (!unread.length) {
        $list.html(
            '<div style="padding:40px 20px;text-align:center;">' +
            '<div style="font-size:3.5rem;opacity:.3;">✅</div>' +
            '<div style="font-size:1rem;font-weight:700;color:#4a5568;margin-top:10px;">Tiada notifikasi baru</div>' +
            '<div style="font-size:.85rem;color:#a0aec0;margin-top:4px;">Semua sudah dibaca</div></div>'
        );
        return;
    }

    unread.forEach(function(n) {
        var $item = $(
            '<div class="notification-item-wrapper" data-id="' + n.id + '" ' +
            'style="display:flex;align-items:flex-start;gap:10px;padding:13px 15px;' +
            'border-bottom:1px solid #eee;background:#fff;">' +
            '<div style="font-size:1.5rem;flex-shrink:0;">' + (n.icon || '🔔') + '</div>' +
            '<div class="notification-item-content" style="flex:1;min-width:0;cursor:pointer;">' +
                '<div style="font-weight:700;font-size:.9rem;color:#2d3748;margin-bottom:3px;">' + esc(n.title) + '</div>' +
                '<div style="font-size:.82rem;color:#718096;line-height:1.5;margin-bottom:4px;">' + esc(n.message) + '</div>' +
                '<div style="font-size:.72rem;color:#cbd5e0;">' + (n.created_at_human || '') + '</div>' +
            '</div>' +
            '<button class="notification-item-delete" data-id="' + n.id + '" ' +
            'style="width:30px;height:30px;background:none;border:none;cursor:pointer;' +
            'color:#bbb;font-size:.9rem;flex-shrink:0;border-radius:6px;">🗑️</button>' +
            '</div>'
        );
        $item.find('.notification-item-wrapper, .notification-item-content').hover(
            function() { $item.css('background','#f8f9fa'); },
            function() { $item.css('background','#fff'); }
        );
        $list.append($item);
    });
}

function esc(s) { return $('<div>').text(s || '').html(); }


// ── Mark read + tutup dropdown ────────────────────────────────
$(document).on('click', '.notification-item-content', function(e) {
    e.preventDefault(); e.stopPropagation();
    var $w = $(this).closest('.notification-item-wrapper');
    if ($w.data('busy')) return;
    $w.data('busy', true);
    var id = $w.data('id');

    $.post('/notifications/' + id + '/read').always(function() {
        $w.fadeOut(250, function() {
            $(this).remove();
            checkEmptyList();
        });
        checkNewNotifications();
        $('#notificationDropdown').hide();
    });
});


// ── Dismiss (tong sampah) ─────────────────────────────────────
$(document).on('click', '.notification-item-delete', function(e) {
    e.preventDefault(); e.stopPropagation();
    var $w  = $(this).closest('.notification-item-wrapper');
    if ($w.data('busy')) return;
    $w.data('busy', true).css({ opacity: '.4', 'pointer-events': 'none' });
    var id = $(this).data('id');

    $.post('/notifications/' + id + '/read').always(function() {
        $w.slideUp(180, function() {
            $(this).remove();
            checkEmptyList();
        });
        checkNewNotifications();
    });
});

function checkEmptyList() {
    if (!$('#notificationList .notification-item-wrapper').length) {
        $('#notificationList').html(
            '<div style="padding:40px 20px;text-align:center;">' +
            '<div style="font-size:3.5rem;opacity:.3;">✅</div>' +
            '<div style="font-size:1rem;font-weight:700;color:#4a5568;margin-top:10px;">Semua dibersihkan!</div></div>'
        );
    }
}


// ── Clear All ─────────────────────────────────────────────────
$(document).on('click', '#deleteAllBtn', function(e) {
    e.preventDefault(); e.stopPropagation();
    var count = $('#notificationList .notification-item-wrapper').length;
    if (!count) { window.showNotification('Tiada notifikasi', 'info'); return; }
    if (!confirm('Padam semua ' + count + ' notifikasi?')) return;
    var $btn = $(this).prop('disabled', true).text('⏳...');

    $.ajax({ url: '/notifications/delete-all', type: 'POST' })
    .done(function(res) {
        if (res && res.success) {
            $('#notificationList').html(
                '<div style="padding:40px 20px;text-align:center;">' +
                '<div style="font-size:3.5rem;opacity:.3;">✅</div>' +
                '<div style="font-size:1rem;font-weight:700;color:#4a5568;margin-top:10px;">Semua dipadamkan!</div></div>'
            );
            $('#notificationBadge').hide();
            _lastNotifId = 0;
            localStorage.removeItem('notif_last_id');
            window.showNotification((res.deleted_count || count) + ' notifikasi dipadamkan ✅', 'success');
        }
    })
    .fail(function() { window.showNotification('Gagal memadam', 'error'); })
    .always(function() { $btn.prop('disabled', false).text('🗑️ Clear All'); });
});


// ── Tutup bila klik luar ──────────────────────────────────────
$(document).on('click', function(e) {
    if (!$(e.target).closest('#notificationsBell, #notificationDropdown').length) {
        $('#notificationDropdown').hide();
    }
});
$(document).on('click', '#notificationDropdown', function(e) { e.stopPropagation(); });


console.log('🔔 Notifications system ready');