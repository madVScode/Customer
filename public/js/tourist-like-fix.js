// ================================================================
// tourist-like-fix.js
// Fix CSRF 419 untuk like/dislike button dalam tourist interface
// Include: <script src="{{ asset('js/tourist-like-fix.js') }}"></script>
// ================================================================

(function () {
    'use strict';

    // ── Setup jQuery AJAX global CSRF token ──────────────────────────
    var metaCsrf = document.querySelector('meta[name="csrf-token"]');
    if (metaCsrf && typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': metaCsrf.getAttribute('content') }
        });
    }

    // ── Helper: get CSRF token ───────────────────────────────────────
    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // ── Core like toggle function ────────────────────────────────────
    window.toggleTripLike = function (tripId, type, onSuccess) {
        var token = getCsrfToken();
        if (!token) {
            console.error('[like] CSRF token tidak ditemui. Pastikan <meta name="csrf-token"> ada dalam <head>.');
            return;
        }

        fetch('/tourist/trip/' + tripId + '/like', {
            method:      'POST',
            credentials: 'same-origin',   // PENTING: hantar cookies (session)
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  token,
                'Accept':        'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ type: type }),
        })
        .then(function (res) {
            if (res.status === 419) {
                // CSRF expired — reload page untuk refresh token
                console.warn('[like] CSRF token expired. Refreshing page...');
                window.location.reload();
                return null;
            }
            if (res.status === 401 || res.status === 403) {
                alert('Sila log masuk untuk memberi penilaian.');
                return null;
            }
            return res.json();
        })
        .then(function (data) {
            if (!data) return;
            if (data.error) { console.warn('[like]', data.error); return; }
            if (typeof onSuccess === 'function') onSuccess(data);
        })
        .catch(function (err) {
            console.error('[like] fetch error:', err);
        });
    };

    // ── Auto-bind like buttons ───────────────────────────────────────
    // Works for buttons with data-trip-id and data-type attributes
    // e.g. <button class="btn-like" data-trip-id="4" data-type="like">👍</button>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-like, .btn-dislike, [data-toggle-like]');
        if (!btn) return;

        var tripId = btn.getAttribute('data-trip-id') || btn.dataset.tripId;
        var type   = btn.getAttribute('data-type')    || btn.dataset.type || 'like';
        if (!tripId) return;

        e.preventDefault();
        btn.disabled = true;

        window.toggleTripLike(tripId, type, function (data) {
            btn.disabled = false;
            // Update like count if element with matching ID exists
            var likeEl    = document.querySelector('[data-like-count="'   + tripId + '"], #likeCount_'    + tripId + ', #likeCount');
            var dislikeEl = document.querySelector('[data-dislike-count="' + tripId + '"], #dislikeCount_' + tripId + ', #dislikeCount');

            if (likeEl)    likeEl.textContent    = data.likes;
            if (dislikeEl) dislikeEl.textContent = data.dislikes;

            // Toggle active state
            document.querySelectorAll('[data-trip-id="' + tripId + '"]').forEach(function (b) {
                b.classList.remove('active', 'voted');
            });
            if (data.myVote) {
                var activeBtn = document.querySelector('[data-trip-id="' + tripId + '"][data-type="' + data.myVote + '"]');
                if (activeBtn) activeBtn.classList.add('active', 'voted');
            }
        });

        setTimeout(function () { btn.disabled = false; }, 2000); // safety unlock
    });

})();