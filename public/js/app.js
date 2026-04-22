// ============================================================
// public/js/app.js  — Entry Point
// Version : 2.5.0
// ============================================================
// Load order (in Blade layout):
//   1.  app.js            ← Global state, notification, CSRF
//   2.  core/audio.js     ← Audio / ringtone system
//   3.  core/customers.js ← Customer CRUD + IC input
//   4.  modules/companies.js
//   5.  modules/trips.js
//   6.  core/receipts.js  ← Receipt CRUD + PDF
//   7.  modules/bookings.js
//   8.  core/navigation.js ← Sidebar nav (needs all modules)
//   9.  core/settings.js  ← Theme + custom sounds
//   10. core/heartbeat.js ← Heartbeat + online users
//   11. core/avatar.js    ← Avatar upload
//   12. core/notifications.js ← Notification system
// ============================================================


// ============================================================
// SECTION 1 : GLOBAL STATE
// ============================================================

let allCustomers = [];
let allReceipts  = [];
let allCompanies = [];
let allTrips     = [];
let allBookings  = [];


// ============================================================
// NOTIFICATION HELPER
// ============================================================

/**
 * showNotification — papar toast notification
 * @param {string} message
 * @param {'success'|'error'|'info'} type
 * @param {boolean} playSound
 */
window.showNotification = function(message, type = 'success', playSound = true) {
    const colours = { error: '#f44336', info: '#2196f3', success: '#4caf50' };
    const bg      = colours[type] || colours.success;

    const toast = $(`
        <div style="
            position:fixed;top:20px;right:20px;
            background:${bg};color:white;
            padding:12px 18px;border-radius:8px;
            z-index:99999;box-shadow:0 6px 20px rgba(0,0,0,0.2);
            font-size:0.9rem;max-width:400px;
            animation:slideInRight 0.3s ease;
        ">${message}</div>
    `);

    $('body').append(toast);
    setTimeout(() => toast.fadeOut(300, () => toast.remove()), 4000);

    if (playSound && typeof window.playNotificationSound === 'function') {
        window.playNotificationSound();
    }
};


// ============================================================
// GLOBAL STYLES (keyframes + base classes)
// ============================================================

$('head').append(`<style>
@keyframes slideInRight {
    from { transform:translateX(120%); opacity:0; }
    to   { transform:translateX(0);    opacity:1; }
}
.processing-loader {
    color:#667eea;
    font-weight:600;
    animation:pulse 1s infinite;
}
@keyframes pulse {
    0%,100% { opacity:1   }
    50%      { opacity:0.5 }
}
.view-section { display:none; }
</style>`);


// ============================================================
// CSRF SETUP
// ============================================================

$(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    console.log('✅ App.js v2.5.0 — initialized');
});