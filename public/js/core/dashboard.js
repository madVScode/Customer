// ===============================================
// DASHBOARD & RESPONSIVE SIDEBAR FULL FIX
// Handles sidebar, mobile interactions, scroll, tables, audio
// ===============================================
(function() {
    'use strict';
    
    // ══════════════════════════════════════════════════════════
    // 🔥 PREVENT DOUBLE INITIALIZATION
    // ══════════════════════════════════════════════════════════
    if (window.dashboardModuleInitialized) {
        console.warn('⚠️ Dashboard module already initialized, skipping...');
        return;
    }
    window.dashboardModuleInitialized = true;
    console.log('🔄 Loading dashboard/responsive JS...');

    // ---------------------------
    // STATE
    // ---------------------------
    if (typeof window._closeTimer      === 'undefined') window._closeTimer      = null;
if (typeof window._sidebarIsClosing === 'undefined') window._sidebarIsClosing = false;
    let notificationSound = null;
    let currentRingtone   = localStorage.getItem('ringtone') || '';
    let ringtoneVolume    = parseFloat(localStorage.getItem('ringtoneVolume')) || 0.5;
    let customSounds      = JSON.parse(localStorage.getItem('customSounds') || '[]');

    // ---------------------------
    // INIT ON DOM READY
    // ---------------------------
    function initDashboard() {
        console.log('✅ DOM Ready, initializing dashboard...');
        createMobileMenuButton();
        createSidebarOverlay();
        attachMobileEvents();
        optimizeForMobile();
        initializeRingtone();

        console.log('✅ Dashboard features initialized!');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }

    // ===========================
    // MOBILE MENU & SIDEBAR
    // ===========================
    function createMobileMenuButton() {
        // Elak cipta button berganda
        if (document.getElementById('mobileMenuToggle')) {
            console.log('📱 Mobile menu button already exists');
            return;
        }

        const btn = document.createElement('button');
        btn.id = 'mobileMenuToggle';
        btn.className = 'mobile-menu-toggle';
        btn.setAttribute('aria-label', 'Toggle Menu');
        btn.setAttribute('aria-hidden', window.innerWidth >= 1025 ? 'true' : 'false');
        btn.innerHTML = '☰';

        // Paksa display ikut saiz skrin sekarang
        if (window.innerWidth >= 1025) {
            btn.style.setProperty('display', 'none', 'important');
        }

        document.body.insertBefore(btn, document.body.firstChild);

        // Update bila resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1025) {
                btn.style.setProperty('display', 'none', 'important');
                btn.setAttribute('aria-hidden', 'true');
                btn.disabled = true;
            } else {
                btn.style.setProperty('display', 'flex', 'important');
                btn.setAttribute('aria-hidden', 'false');
                btn.disabled = false;
            }
        });

        console.log('📱 Mobile menu button created');
    }

    function createSidebarOverlay() {
        if (document.getElementById('sidebarOverlay')) {
            console.log('🎭 Sidebar overlay already exists');
            return;
        }

        const overlay = document.createElement('div');
        overlay.id = 'sidebarOverlay';
        overlay.className = 'sidebar-overlay';
        document.body.insertBefore(overlay, document.body.firstChild);
        
        console.log('🎭 Sidebar overlay created');
    }

    function attachMobileEvents() {
    // ── Guard: elak double-bind ──────────────────────────────
    if (window._mobileEventsAttached) return;
    window._mobileEventsAttached = true;

    const toggleBtn = document.getElementById('mobileMenuToggle');
    const overlay   = document.getElementById('sidebarOverlay');

    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
    if (overlay)   overlay.addEventListener('click', closeSidebar);

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.sidebar-link');
        if (link && window.innerWidth <= 768) setTimeout(closeSidebar, 400);
    }, { passive: true });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleResize, 250);
    }, { passive: true });

    setupSwipeGestures();
    console.log('📱 Mobile events attached');
}

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;
        sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
    }

    function openSidebar() {
    window._sidebarIsClosing = false;
    clearTimeout(window._closeTimer);

    const sidebar   = document.querySelector('.sidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('mobileMenuToggle');

    if (sidebar)   sidebar.classList.add('active');
    if (overlay) { overlay.style.display = 'block'; overlay.classList.add('active'); }
    if (toggleBtn) toggleBtn.innerHTML = '✕';

    // ✅ FIX: Hanya mobile
    if (window.innerWidth <= 1024) {
        document.body.style.overflow = 'hidden';
    }
}

    function closeSidebar() {
    if (window._sidebarIsClosing) return; // ← Sekat spam call
    window._sidebarIsClosing = true;

    const sidebar   = document.querySelector('.sidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('mobileMenuToggle');

    if (sidebar)    sidebar.classList.remove('active');
    if (overlay)    overlay.classList.remove('active');
    if (toggleBtn)  toggleBtn.innerHTML = '☰';
    document.body.style.overflow = '';

    clearTimeout(window._closeTimer);
    window._closeTimer = setTimeout(() => {
        if (overlay) overlay.style.display = 'none';
        window._sidebarIsClosing = false; // ← Reset flag selepas animasi
    }, 300);
}

    // Export to window for external access
    window.closeSidebar = closeSidebar;

    function handleResize() {
        if (window.innerWidth > 768) closeSidebar();
        optimizeTableScroll();
    }

    function setupSwipeGestures() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        let startX = 0, endX = 0, startY = 0, endY = 0;
        
        sidebar.addEventListener('touchstart', e => {
            startX = e.changedTouches[0].screenX;
            startY = e.changedTouches[0].screenY;
        }, { passive: true });
        
        sidebar.addEventListener('touchend', e => {
            endX = e.changedTouches[0].screenX;
            endY = e.changedTouches[0].screenY;
            const xDiff = endX - startX;
            const yDiff = Math.abs(endY - startY);
            if (Math.abs(xDiff) > yDiff && xDiff < -50 && window.innerWidth <= 768) {
                closeSidebar();
            }
        }, { passive: true });
    }

    // ===========================
    // SCROLL & TABLE OPTIMIZATION
    // ===========================
    function optimizeTableScroll() {
        document.querySelectorAll('.table-wrapper').forEach(wrapper => {
            const table = wrapper.querySelector('table');
            if (!table) return;
            if (window.innerWidth <= 768 && table.offsetWidth > wrapper.offsetWidth) {
                if (!wrapper.querySelector('.scroll-indicator')) {
                    const ind = document.createElement('div');
                    ind.className = 'scroll-indicator';
                    wrapper.style.position = 'relative';
                    wrapper.appendChild(ind);
                }
            }
        });
    }

    function createScrollToTopButton() {
        if (document.getElementById('scrollToTop')) return;
        
        const btn = document.createElement('button');
        btn.id = 'scrollToTop';
        btn.innerHTML = '↑';
        btn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 999;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            font-size: 1.5rem;
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(btn);
        
        window.addEventListener('scroll', () => { 
            btn.style.display = window.scrollY > 300 ? 'block' : 'none'; 
        }, { passive: true });
        
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    function optimizeForMobile() {
        // Prevent iOS zoom on input focus
        if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            document.querySelectorAll('input, select, textarea').forEach(i => {
                if (parseInt(window.getComputedStyle(i).fontSize) < 16) {
                    i.style.fontSize = '16px';
                }
            });
        }
        
        createScrollToTopButton();
        optimizeTableScroll();
    }

    // ===========================
    // AUDIO / RINGTONE
    // ===========================
    function initializeRingtone() {
        if (customSounds.length > 0) {
            if (!currentRingtone || !customSounds.find(s => s.id === currentRingtone)) {
                currentRingtone = customSounds[0].id;
                localStorage.setItem('ringtone', currentRingtone);
            }
            setNotificationSound(currentRingtone);
        }
    }

    function setNotificationSound(id) {
        currentRingtone = id;
        localStorage.setItem('ringtone', id);
        
        if (notificationSound) { 
            notificationSound.pause(); 
            notificationSound = null; 
        }
        
        const s = customSounds.find(c => c.id === id);
        if (s) { 
            notificationSound = new Audio(s.data); 
            notificationSound.volume = ringtoneVolume; 
        }
    }

    function playNotificationSound() {
        if (notificationSound) { 
            notificationSound.currentTime = 0; 
            notificationSound.play().catch(e => console.log('Audio playback failed:', e)); 
        }
    }

    // Export to window
    window.playNotificationSound = playNotificationSound;

    console.log('✅ Dashboard/Responsive JS loaded successfully!');

})();