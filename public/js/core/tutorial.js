// ============================================================
// public/js/core/tutorial.js  v3.0
// Tutorial interaktif untuk role Tester
// ✅ Tutorial auto-run SETIAP KALI login untuk role tester
// ✅ Session-based tracking (bukan localStorage untuk auto-run)
// ✅ Pilihan "Jangan tunjuk lagi" untuk skip permanently
// ✅ Auto-scroll ke element yang di-highlight
// ✅ Better animations dan transitions
// ✅ Resume functionality (sambung dari step terakhir)
// ✅ Mobile responsive
// ============================================================

(function () {
    'use strict';

    // ✅ Only activate for tester role
    if (typeof window.currentUserRole !== 'undefined' && window.currentUserRole !== 'tester') return;

    // ── CONFIGURATION ─────────────────────────────────────────
    var CONFIG = {
        autoRunOnLogin: true,           // Auto-run tutorial setiap login
        allowPermanentDismiss: true,    // Benarkan "Jangan tunjuk lagi"
        saveProgress: true,             // Simpan progress tutorial
        autoScroll: true,               // Auto scroll ke element
        smoothScrollDuration: 600,      // Durasi scroll animation (ms)
        delayBetweenSteps: 200,         // Delay antara steps (ms)
        storageKeys: {
            permanentDismiss: 'tut_permanent_dismiss',
            sessionActive: 'tut_session_active',
            lastStep: 'tut_last_step'
        }
    };

    // ── STEPS ─────────────────────────────────────────────────
    var STEPS = [
        {
            target   : null,
            title    : '👋 Selamat Datang ke Customer Pro!',
            content  : 'Anda log masuk sebagai <strong>Tester</strong>. Tutorial ringkas ini akan membawa anda mengenal semua fungsi utama sistem.<br><br>✨ Jangan risau — semua data dalam mod ini <strong>tidak disimpan</strong> secara kekal.',
            position : 'center',
            btnNext  : 'Mulakan Tour →',
            icon     : '🚀',
        },
        {
            target   : '.sidebar',
            title    : '🗂️ Menu Navigasi',
            content  : 'Panel navigasi utama sistem. Klik mana-mana menu untuk beralih antara modul. Semua fungsi utama boleh diakses dari sini.',
            position : 'right',
            icon     : '🗂️',
        },
        {
            target   : '#dashboardBtn',
            title    : '📊 Dashboard — Senarai Pelanggan',
            content  : 'Halaman utama yang memaparkan <strong>semua pelanggan berdaftar</strong>. Boleh cari mengikut nama, IC atau emel. Klik baris pelanggan untuk lihat maklumat lengkap.',
            position : 'right',
            icon     : '📊',
        },
        {
            target   : '#customersBtn',
            title    : '👤 Tambah Pelanggan Baharu',
            content  : 'Daftarkan pelanggan baharu di sini. Isi maklumat <strong>nama, IC, emel, telefon dan alamat</strong>. IC akan diformat secara automatik (xxxxxx-xx-xxxx).',
            position : 'right',
            icon     : '👤',
        },
        {
            target   : '#companiesBtn',
            title    : '🏢 Syarikat',
            content  : 'Urus <strong>senarai syarikat</strong> yang berdaftar. Setiap pelanggan, trip dan booking dikaitkan dengan syarikat tertentu. Admin boleh tambah, edit dan padam syarikat.',
            position : 'right',
            icon     : '🏢',
        },
        {
            target   : '#addTripBtn',
            title    : '✈️ Tambah Trip Baharu',
            content  : 'Cipta pakej pelancongan baharu lengkap dengan <strong>destinasi, tarikh, harga (dewasa/kanak/bayi), deposit</strong>, gambar (sehingga 3 keping) dan itinerari hari demi hari.',
            position : 'right',
            icon     : '✈️',
        },
        {
            target   : '#viewTripsBtn',
            title    : '🗺️ Senarai Trip',
            content  : 'Lihat semua trip dalam <strong>kad bergambar</strong>. Trip yang ada customer akan terkunci 🔒 — gunakan butang <em>"👥 Urus Pelanggan"</em> untuk lihat booking. Trip kosong boleh diedit atau dipadam.',
            position : 'right',
            icon     : '🗺️',
        },
        {
            target   : '#createBookingBtn',
            title    : '📝 Buat Booking',
            content  : 'Buat tempahan trip untuk pelanggan. Aliran mudah: pilih <strong>Syarikat → Trip → Pelanggan → Harga</strong>. Nombor booking akan dijanakan automatik.',
            position : 'right',
            icon     : '📝',
        },
        {
            target   : '#viewBookingsBtn',
            title    : '📋 Senarai Booking',
            content  : 'Lihat dan urus semua booking. Tukar <strong>status bayaran</strong> (Belum / Separa / Penuh) dan <strong>status booking</strong> (Menunggu / Disahkan / Selesai / Dibatal).',
            position : 'right',
            icon     : '📋',
        },
        {
            target   : '#createReceiptBtn',
            title    : '🧾 Jana Resit',
            content  : 'Jana resit pembayaran untuk pelanggan. Pilih pelanggan dan booking, masukkan <strong>jumlah bayaran dan kaedah</strong>. Resit boleh dimuat turun sebagai <strong>PDF</strong>.',
            position : 'right',
            icon     : '🧾',
        },
        {
            target   : '#viewReceiptsBtn',
            title    : '📄 Senarai Resit',
            content  : 'Lihat semua resit yang telah dijana. Boleh <strong>cari, tapis</strong> dan muat turun semula mana-mana resit dalam format PDF.',
            position : 'right',
            icon     : '📄',
        },
        {
            target   : '#settingsBtn',
            title    : '⚙️ Tetapan Sistem',
            content  : 'Tukar <strong>tema (Cerah / Gelap)</strong> mengikut keselesaan anda. Tetapan disimpan dalam peranti. Anda juga boleh urus bunyi notifikasi di sini.',
            position : 'right',
            icon     : '⚙️',
        },
        {
            target   : '#notificationsBell',
            title    : '🔔 Notifikasi Masa Nyata',
            content  : 'Loceng ini akan <strong>berbadge merah</strong> apabila ada aktiviti baharu dari pengguna lain — pelanggan ditambah, booking dibuat, dan lain-lain. Klik untuk lihat senarai.',
            position : 'bottom-left',
            icon     : '🔔',
        },
        {
            target   : '#profileTrigger',
            title    : '👤 Profil & Log Keluar',
            content  : 'Klik avatar anda untuk buka menu profil. Tukar <strong>gambar profil</strong> atau <strong>log keluar</strong> dari sini. Timer hijau menunjukkan tempoh sesi semasa.',
            position : 'bottom-left',
            icon     : '👤',
        },
        {
            target   : null,
            title    : '🎉 Anda Sudah Bersedia!',
            content  : 'Tutorial selesai! Kini anda boleh mula meneroka <strong>Customer Pro</strong> sendiri.<br><br>💡 <em>Untuk ulang tutorial ini pada bila-bila masa, klik butang <strong>"🎓 Tutorial"</strong> di bahagian bawah sidebar.</em>',
            position : 'center',
            btnNext  : '✅ Mula Guna Sistem',
            icon     : '🎊',
        },
    ];

    // ── STATE ─────────────────────────────────────────────────
    var state = {
        currentStep: 0,
        isActive: false,
        posTimer: null,
        scrollTimeout: null
    };

    var elements = {
        card: null,
        progressBar: null,
        stepDots: null,
        spotlight: null,
        arrow: null,
        background: null
    };

    // ── UTILITY FUNCTIONS ─────────────────────────────────────
    function saveProgress() {
        if (!CONFIG.saveProgress) return;
        sessionStorage.setItem(CONFIG.storageKeys.lastStep, state.currentStep);
        sessionStorage.setItem(CONFIG.storageKeys.sessionActive, '1');
    }

    function loadProgress() {
        if (!CONFIG.saveProgress) return 0;
        var saved = sessionStorage.getItem(CONFIG.storageKeys.lastStep);
        return saved ? parseInt(saved, 10) : 0;
    }

    function isPermanentlyDismissed() {
        if (!CONFIG.allowPermanentDismiss) return false;
        return localStorage.getItem(CONFIG.storageKeys.permanentDismiss) === '1';
    }

    function setPermanentDismiss() {
        if (!CONFIG.allowPermanentDismiss) return;
        localStorage.setItem(CONFIG.storageKeys.permanentDismiss, '1');
    }

    function clearProgress() {
        sessionStorage.removeItem(CONFIG.storageKeys.lastStep);
        sessionStorage.removeItem(CONFIG.storageKeys.sessionActive);
    }

    function smoothScrollTo(element, offset) {
        if (!CONFIG.autoScroll || !element) return;

        clearTimeout(state.scrollTimeout);
        
        var rect = element.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var targetY = rect.top + scrollTop - (offset || 100);

        state.scrollTimeout = setTimeout(function() {
            window.scrollTo({
                top: targetY,
                behavior: 'smooth'
            });
        }, 100);
    }

    // ── CSS ───────────────────────────────────────────────────
    function injectStyles() {
        if (document.getElementById('tut-styles')) return;
        var css = `
        /* Background overlay */
        #tut-bg {
            position:fixed;inset:0;z-index:89990;
            background:rgba(8,8,24,.8);
            backdrop-filter:blur(2px);
            display:none;
            transition:opacity .4s ease-in-out;
            opacity:0;
        }
        #tut-bg.show { 
            display:block;
            animation:fadeIn .4s forwards;
        }
        @keyframes fadeIn {
            to { opacity:1; }
        }

        /* Spotlight */
        #tut-spot {
            position:fixed;z-index:89991;
            border-radius:12px;
            box-shadow:0 0 0 9999px rgba(8,8,24,.8);
            transition:all .5s cubic-bezier(.4,0,.2,1);
            pointer-events:none;
            outline:3px solid rgba(102,126,234,.85);
            outline-offset:6px;
        }

        /* Main card */
        #tut-card {
            position:fixed;z-index:89995;
            width:min(360px, calc(100vw - 40px));
            background:#fff;
            border-radius:20px;
            box-shadow:0 32px 80px rgba(0,0,0,.3),
                       0 0 0 1px rgba(102,126,234,.15);
            overflow:hidden;
            display:none;
            transition:top .45s cubic-bezier(.4,0,.2,1),
                       left .45s cubic-bezier(.4,0,.2,1),
                       transform .45s cubic-bezier(.4,0,.2,1),
                       opacity .3s;
        }
        #tut-card.tut-show {
            display:block;
            animation:tutSlideIn .5s cubic-bezier(.16,1,.3,1);
        }
        @keyframes tutSlideIn {
            from{opacity:0;transform:scale(.92) translateY(12px)}
            to{opacity:1;transform:scale(1) translateY(0)}
        }

        /* Header */
        .tut-hd {
            background:linear-gradient(135deg,#667eea,#764ba2);
            padding:18px 22px 14px;
            position:relative;
        }
        .tut-step-lbl {
            font-size:.68rem;font-weight:700;color:rgba(255,255,255,.7);
            text-transform:uppercase;letter-spacing:.1em;display:block;margin-bottom:6px;
        }
        .tut-ico { 
            font-size:2rem;display:block;margin-bottom:6px;line-height:1;
            animation:bounceIn .6s cubic-bezier(.68,-.55,.265,1.55);
        }
        @keyframes bounceIn {
            0%{transform:scale(.3);opacity:0}
            50%{transform:scale(1.05)}
            100%{transform:scale(1);opacity:1}
        }
        .tut-ttl {
            font-size:.95rem;font-weight:800;color:#fff;margin:0;line-height:1.4;
        }
        .tut-prog-wrap {
            position:absolute;bottom:0;left:0;right:0;
            height:4px;background:rgba(255,255,255,.25);
        }
        .tut-prog-fill {
            height:100%;background:#fff;border-radius:0 3px 3px 0;
            transition:width .5s cubic-bezier(.4,0,.2,1);
            box-shadow:0 0 8px rgba(255,255,255,.4);
        }

        /* Body */
        .tut-bd {
            padding:18px 22px;font-size:.88rem;color:#374151;line-height:1.7;
        }
        .tut-bd strong{color:#4338ca;font-weight:700;}
        .tut-bd em{color:#6b7280;font-style:normal;}

        /* Progress dots */
        .tut-dots {
            display:flex;gap:6px;justify-content:center;padding:3px 22px 8px;
        }
        .tut-dot {
            width:7px;height:7px;border-radius:50%;background:#d1d5db;
            cursor:pointer;transition:all .3s cubic-bezier(.4,0,.2,1);
            flex-shrink:0;
        }
        .tut-dot:hover { 
            background:#a5b4fc;
            transform:scale(1.2);
        }
        .tut-dot.act {
            width:20px;border-radius:4px;
            background:linear-gradient(90deg,#667eea,#764ba2);
            box-shadow:0 2px 8px rgba(102,126,234,.4);
        }
        .tut-dot.dn { background:#a5b4fc; }

        /* Footer buttons */
        .tut-ft {
            display:flex;align-items:center;gap:9px;
            padding:12px 22px 20px;
        }
        .tb {
            padding:10px 18px;border-radius:11px;border:none;
            font-size:.84rem;font-weight:700;cursor:pointer;
            transition:all .2s ease;font-family:inherit;
        }
        .tb:active { transform:scale(.96); }
        
        .tb-skip{
            background:none;color:#9ca3af;padding:10px 8px;
        }
        .tb-skip:hover{color:#6b7280;text-decoration:underline;}
        
        .tb-dismiss{
            background:#fef3c7;color:#92400e;
            border:1px solid #fbbf24;
        }
        .tb-dismiss:hover{background:#fde68a;}
        
        .tb-back{background:#f3f4f6;color:#374151;}
        .tb-back:hover{background:#e5e7eb;transform:translateX(-2px);}
        
        .tb-next{
            background:linear-gradient(135deg,#667eea,#764ba2);
            color:#fff;flex:1;
            box-shadow:0 4px 16px rgba(102,126,234,.35);
        }
        .tb-next:hover{
            filter:brightness(1.08);
            transform:translateY(-1px);
            box-shadow:0 6px 20px rgba(102,126,234,.45);
        }

        /* Arrow pointer */
        #tut-arr {
            position:fixed;z-index:89994;
            width:0;height:0;display:none;
            transition:all .45s cubic-bezier(.4,0,.2,1);
            filter:drop-shadow(0 2px 4px rgba(0,0,0,.1));
        }
        .arr-r{border-top:11px solid transparent;border-bottom:11px solid transparent;border-right:14px solid #fff;}
        .arr-l{border-top:11px solid transparent;border-bottom:11px solid transparent;border-left:14px solid #fff;}
        .arr-t{border-left:11px solid transparent;border-right:11px solid transparent;border-bottom:14px solid #fff;}
        .arr-b{border-left:11px solid transparent;border-right:11px solid transparent;border-top:14px solid #fff;}

        /* Sidebar restart button */
        #tut-restart {
            display:flex;align-items:center;gap:10px;
            width:calc(100% - 20px);margin:6px 10px 4px;
            padding:11px 16px;border-radius:11px;
            border:2px dashed rgba(102,126,234,.4);
            background:linear-gradient(135deg,rgba(102,126,234,.08),rgba(118,75,162,.08));
            color:#667eea;font-size:.84rem;font-weight:700;
            cursor:pointer;text-align:left;
            transition:all .25s ease;font-family:inherit;
        }
        #tut-restart:hover{
            background:linear-gradient(135deg,rgba(102,126,234,.16),rgba(118,75,162,.16));
            border-color:rgba(102,126,234,.7);
            transform:translateX(2px);
        }

        /* Mobile responsive */
        @media (max-width: 480px) {
            #tut-card { width:calc(100vw - 20px); }
            .tut-ft { flex-wrap:wrap; }
            .tb-next { width:100%; }
        }

        /* Dark theme */
        body.dark #tut-bg,
        html[data-theme=dark] #tut-bg{background:rgba(0,0,0,.85);}
        
        body.dark #tut-card,
        html[data-theme=dark] #tut-card{
            background:#1a1f35;
            box-shadow:0 32px 80px rgba(0,0,0,.6),
                       0 0 0 1px rgba(102,126,234,.2);
        }
        body.dark .tut-bd,
        html[data-theme=dark] .tut-bd{color:#d1d5db;}
        body.dark .tut-bd strong,
        html[data-theme=dark] .tut-bd strong{color:#a5b4fc;}
        body.dark .tb-back,
        html[data-theme=dark] .tb-back{background:#252a42;color:#d1d5db;}
        body.dark .tb-back:hover,
        html[data-theme=dark] .tb-back:hover{background:#2e3450;}
        body.dark .tut-dot,
        html[data-theme=dark] .tut-dot{background:#374151;}
        body.dark .arr-r,
        html[data-theme=dark] .arr-r{border-right-color:#1a1f35;}
        body.dark .arr-l,
        html[data-theme=dark] .arr-l{border-left-color:#1a1f35;}
        body.dark .arr-t,
        html[data-theme=dark] .arr-t{border-bottom-color:#1a1f35;}
        body.dark .arr-b,
        html[data-theme=dark] .arr-b{border-top-color:#1a1f35;}
        `;
        var s = document.createElement('style');
        s.id = 'tut-styles';
        s.textContent = css;
        document.head.appendChild(s);
    }

    // ── BUILD DOM ─────────────────────────────────────────────
    function buildDOM() {
        // Background
        elements.background = createElement('div', 'tut-bg');
        document.body.appendChild(elements.background);

        // Spotlight
        elements.spotlight = createElement('div', 'tut-spot');
        elements.spotlight.style.cssText = 'display:none;top:0;left:0;width:0;height:0;';
        document.body.appendChild(elements.spotlight);

        // Arrow
        elements.arrow = createElement('div', 'tut-arr');
        document.body.appendChild(elements.arrow);

        // Card
        elements.card = createElement('div', 'tut-card');
        elements.card.innerHTML = `
            <div class="tut-hd">
                <span class="tut-step-lbl" id="tut-lbl"></span>
                <span class="tut-ico" id="tut-ico"></span>
                <p class="tut-ttl" id="tut-ttl"></p>
                <div class="tut-prog-wrap"><div class="tut-prog-fill" id="tut-prog"></div></div>
            </div>
            <div class="tut-bd" id="tut-bd"></div>
            <div class="tut-dots" id="tut-dots"></div>
            <div class="tut-ft">
                <button class="tb tb-skip" id="tut-skip">Langkau</button>
                <button class="tb tb-dismiss" id="tut-dismiss" style="display:none;">🚫 Jangan Tunjuk Lagi</button>
                <button class="tb tb-back" id="tut-back">← Balik</button>
                <button class="tb tb-next" id="tut-next">Seterusnya →</button>
            </div>`;
        document.body.appendChild(elements.card);

        // Event listeners
        document.getElementById('tut-next').onclick = nextStep;
        document.getElementById('tut-back').onclick = prevStep;
        document.getElementById('tut-skip').onclick = skipTutorial;
        document.getElementById('tut-dismiss').onclick = dismissPermanently;
        elements.background.onclick = function(e) { 
            if (e.target === this) skipTutorial(); 
        };

        elements.progressBar = document.getElementById('tut-prog');
        elements.stepDots = document.getElementById('tut-dots');
    }

    function createElement(tag, id) {
        var e = document.createElement(tag);
        e.id = id;
        return e;
    }

    // ── SIDEBAR BUTTON ────────────────────────────────────────
    function addRestartBtn() {
        if (document.getElementById('tut-restart')) return;
        
        var btn = document.createElement('button');
        btn.id = 'tut-restart';
        btn.innerHTML = '🎓 &nbsp;Tutorial Sistem';
        btn.onclick = function() {
            // Clear permanent dismiss if manually restarted
            if (CONFIG.allowPermanentDismiss) {
                localStorage.removeItem(CONFIG.storageKeys.permanentDismiss);
            }
            startTutorial();
        };

        var settingsBtn = document.getElementById('settingsBtn');
        if (settingsBtn && settingsBtn.parentNode) {
            settingsBtn.parentNode.insertBefore(btn, settingsBtn);
        } else {
            var sidebar = document.querySelector('.sidebar');
            if (sidebar) sidebar.appendChild(btn);
        }
    }

    // ── POSITIONING ───────────────────────────────────────────
    function positionStep(step) {
        clearTimeout(state.posTimer);

        // Center mode (no target)
        if (!step.target) {
            elements.background.classList.add('show');
            elements.spotlight.style.display = 'none';
            elements.arrow.style.display = 'none';
            elements.card.style.cssText = 'display:block;left:50%;top:50%;transform:translate(-50%,-50%);';
            elements.card.classList.add('tut-show');
            return;
        }

        var target = document.querySelector(step.target);
        if (!target) {
            // Element not found — show centered with warning
            elements.background.classList.add('show');
            elements.spotlight.style.display = 'none';
            elements.arrow.style.display = 'none';
            elements.card.style.cssText = 'display:block;left:50%;top:50%;transform:translate(-50%,-50%);';
            elements.card.classList.add('tut-show');
            console.warn('Tutorial target not found:', step.target);
            return;
        }

        // Auto-scroll to element
        smoothScrollTo(target);

        // Wait for scroll before positioning
        state.posTimer = setTimeout(function() {
            positionCardNearTarget(target, step);
        }, CONFIG.autoScroll ? 350 : 50);
    }

    function positionCardNearTarget(target, step) {
        elements.background.classList.remove('show');

        var r = target.getBoundingClientRect();
        var pad = 10;
        var cW = 360; // Max card width
        var vw = window.innerWidth;
        var vh = window.innerHeight;

        // Spotlight
        elements.spotlight.style.cssText = [
            'display:block',
            'top:' + (r.top - pad) + 'px',
            'left:' + (r.left - pad) + 'px',
            'width:' + (r.width + pad * 2) + 'px',
            'height:' + (r.height + pad * 2) + 'px',
        ].join(';');

        // Calculate card position
        var pos = step.position || 'right';
        var cx, cy, cH;

        elements.card.style.display = 'block';
        elements.card.classList.remove('tut-show');
        cH = elements.card.offsetHeight || 350;
        elements.card.classList.add('tut-show');

        elements.arrow.style.display = 'block';
        elements.arrow.className = '';
        elements.arrow.style.cssText = 'position:fixed;z-index:89994;display:block;';

        // Mobile: force bottom position
        if (vw < 480) {
            pos = 'bottom';
        }

        if (pos === 'right') {
            cx = r.right + pad + 16;
            cy = r.top + r.height / 2 - cH / 2;
            if (cx + cW > vw - 10) pos = 'left';
        }
        if (pos === 'left') {
            cx = r.left - cW - pad - 16;
            cy = r.top + r.height / 2 - cH / 2;
            if (cx < 10) pos = 'right';
        }
        if (pos === 'bottom' || pos === 'bottom-left') {
            cx = (pos === 'bottom-left' && vw > 480)
                ? Math.max(10, r.right - cW)
                : r.left + r.width / 2 - cW / 2;
            cy = r.bottom + pad + 16;
            if (cy + cH > vh - 10) {
                cy = r.top - cH - pad - 16;
                pos = 'top';
            }
        }
        if (pos === 'top') {
            cx = r.left + r.width / 2 - cW / 2;
            cy = r.top - cH - pad - 16;
            if (cy < 10) pos = 'bottom';
        }

        // Clamp inside viewport
        cx = Math.max(10, Math.min(cx, vw - cW - 10));
        cy = Math.max(10, Math.min(cy, vh - cH - 10));

        elements.card.style.cssText = 'display:block;width:' + cW + 'px;left:' + cx + 'px;top:' + cy + 'px;transform:none;';

        // Position arrow
        positionArrow(r, cx, cy, cW, cH, step.position || 'right');
    }

    function positionArrow(targetRect, cardX, cardY, cardW, cardH, basePos) {
        var r = targetRect;
        
        if (basePos === 'right' || basePos === 'left') {
            var finalPos = cardX > r.right ? 'right' : 'left';
            if (finalPos === 'right') {
                elements.arrow.classList.add('arr-r');
                elements.arrow.style.left = (cardX - 14) + 'px';
                elements.arrow.style.top = (r.top + r.height / 2 - 11) + 'px';
            } else {
                elements.arrow.classList.add('arr-l');
                elements.arrow.style.left = (cardX + cardW) + 'px';
                elements.arrow.style.top = (r.top + r.height / 2 - 11) + 'px';
            }
        } else if (basePos === 'bottom' || basePos === 'bottom-left') {
            if (cardY < r.top) {
                elements.arrow.classList.add('arr-b');
                elements.arrow.style.top = (cardY + cardH) + 'px';
                elements.arrow.style.left = (r.left + r.width / 2 - 11) + 'px';
            } else {
                elements.arrow.classList.add('arr-t');
                elements.arrow.style.top = (cardY - 14) + 'px';
                elements.arrow.style.left = (r.left + r.width / 2 - 11) + 'px';
            }
        } else {
            elements.arrow.style.display = 'none';
        }
    }

    // ── RENDER STEP ───────────────────────────────────────────
    function renderStep(idx) {
        var step = STEPS[idx];
        if (!step) return;

        document.getElementById('tut-lbl').textContent = 'Langkah ' + (idx + 1) + ' daripada ' + STEPS.length;
        document.getElementById('tut-ico').textContent = step.icon || '💡';
        document.getElementById('tut-ttl').textContent = step.title || '';
        document.getElementById('tut-bd').innerHTML = step.content || '';

        elements.progressBar.style.width = ((idx + 1) / STEPS.length * 100) + '%';

        // Render dots
        elements.stepDots.innerHTML = '';
        STEPS.forEach(function(_, i) {
            var d = document.createElement('div');
            d.className = 'tut-dot' + (i === idx ? ' act' : (i < idx ? ' dn' : ''));
            d.onclick = function() { goToStep(i); };
            d.title = 'Pergi ke langkah ' + (i + 1);
            elements.stepDots.appendChild(d);
        });

        // Button states
        var btnNext = document.getElementById('tut-next');
        var btnBack = document.getElementById('tut-back');
        var btnSkip = document.getElementById('tut-skip');
        var btnDismiss = document.getElementById('tut-dismiss');
        var isLast = idx === STEPS.length - 1;
        var isFirst = idx === 0;

        btnNext.textContent = step.btnNext || 'Seterusnya →';
        btnBack.style.display = isFirst ? 'none' : '';
        btnSkip.style.display = (isFirst || isLast) ? 'none' : '';
        
        // Show "Don't show again" button on first step if allowed
        if (CONFIG.allowPermanentDismiss && isFirst) {
            btnDismiss.style.display = '';
        } else {
            btnDismiss.style.display = 'none';
        }

        // Position the card
        positionStep(step);

        // Save progress
        saveProgress();
    }

    // ── NAVIGATION ────────────────────────────────────────────
    function nextStep() {
        if (state.currentStep < STEPS.length - 1) {
            state.currentStep++;
            setTimeout(function() {
                renderStep(state.currentStep);
            }, CONFIG.delayBetweenSteps);
        } else {
            endTutorial();
        }
    }

    function prevStep() {
        if (state.currentStep > 0) {
            state.currentStep--;
            setTimeout(function() {
                renderStep(state.currentStep);
            }, CONFIG.delayBetweenSteps);
        }
    }

    function goToStep(idx) {
        state.currentStep = idx;
        setTimeout(function() {
            renderStep(state.currentStep);
        }, CONFIG.delayBetweenSteps);
    }

    function skipTutorial() {
        endTutorial();
    }

    function dismissPermanently() {
        if (confirm('Adakah anda pasti tidak mahu melihat tutorial ini lagi?\n\nAnda masih boleh akses tutorial melalui butang "🎓 Tutorial" di sidebar.')) {
            setPermanentDismiss();
            endTutorial();
        }
    }

    // ── START / END ───────────────────────────────────────────
    function startTutorial(resumeFrom) {
        state.isActive = true;
        state.currentStep = resumeFrom || 0;
        
        elements.card.style.display = 'none';
        elements.card.classList.remove('tut-show');
        
        setTimeout(function() {
            renderStep(state.currentStep);
        }, 200);

        // Analytics (optional)
        if (typeof window.trackEvent === 'function') {
            window.trackEvent('tutorial_started', {role: 'tester'});
        }
    }

    function endTutorial() {
        state.isActive = false;
        
        elements.card.style.display = 'none';
        elements.spotlight.style.display = 'none';
        elements.arrow.style.display = 'none';
        elements.background.classList.remove('show');
        elements.card.classList.remove('tut-show');
        
        clearProgress();

        // Analytics (optional)
        if (typeof window.trackEvent === 'function') {
            window.trackEvent('tutorial_completed', {
                role: 'tester',
                completed_steps: state.currentStep + 1
            });
        }
    }

    // ── KEYBOARD CONTROLS ─────────────────────────────────────
    document.addEventListener('keydown', function(e) {
        if (!state.isActive) return;
        
        switch(e.key) {
            case 'ArrowRight':
            case 'Enter':
                e.preventDefault();
                nextStep();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                prevStep();
                break;
            case 'Escape':
                e.preventDefault();
                skipTutorial();
                break;
            case 'Home':
                e.preventDefault();
                goToStep(0);
                break;
            case 'End':
                e.preventDefault();
                goToStep(STEPS.length - 1);
                break;
        }
    });

    // ── RESIZE HANDLER ────────────────────────────────────────
    var resizeTimeout;
    window.addEventListener('resize', function() {
        if (!state.isActive) return;
        
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            renderStep(state.currentStep);
        }, 150);
    });

    // ── INITIALIZATION ────────────────────────────────────────
    function init() {
        injectStyles();
        buildDOM();

        // Wait for sidebar to render
        setTimeout(function() {
            addRestartBtn();

            // ✅ Check if permanently dismissed
            if (isPermanentlyDismissed()) {
                console.info('[Tutorial] Permanently dismissed by user');
                return;
            }

            // ✅ AUTO-RUN on fresh login for tester role
            if (CONFIG.autoRunOnLogin) {
                var savedStep = loadProgress();
                var wasInProgress = sessionStorage.getItem(CONFIG.storageKeys.sessionActive) === '1';
                
                if (wasInProgress && savedStep > 0) {
                    // Resume from last step
                    setTimeout(function() {
                        if (confirm('Anda ada tutorial yang belum selesai. Teruskan dari langkah ' + (savedStep + 1) + '?')) {
                            startTutorial(savedStep);
                        } else {
                            clearProgress();
                            startTutorial(0);
                        }
                    }, 1000);
                } else {
                    // Fresh start
                    setTimeout(startTutorial, 1200);
                }
            }
        }, 700);

        // Expose global functions
        window.startTutorial = startTutorial;
        window.endTutorial = endTutorial;
        window.tutorialGoToStep = goToStep;
    }

    // ── LOAD ──────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 350);
    }

})();