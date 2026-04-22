/* =========================================================
   🎨 THEME TOGGLE - Dark/Light Mode with Persistence
   ========================================================= */

$(document).ready(function() {
    
    // ================= Initialize Theme ================= 
    
    /**
     * Load saved theme from localStorage
     * If no saved preference, use light mode as default
     */
    function initializeTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        const isDark = savedTheme === 'dark';
        
        // Apply theme immediately (before page renders)
        if (isDark) {
            $('body').addClass('dark-mode');
            $('#darkModeToggle').prop('checked', true);
        } else {
            $('body').removeClass('dark-mode');
            $('#darkModeToggle').prop('checked', false);
        }
        
        // Update theme display text
        updateThemeDisplay(isDark);
        
        console.log('✅ Theme initialized:', savedTheme);
    }
    
    // ================= Theme Toggle Handler ================= 
    
    /**
     * Handle dark mode toggle switch
     */
    $('#darkModeToggle').on('change', function() {
        const isChecked = $(this).is(':checked');
        
        if (isChecked) {
            // Enable dark mode
            $('body').addClass('dark-mode');
            localStorage.setItem('theme', 'dark');
            updateThemeDisplay(true);
            
            // Show notification
            showNotification('🌙 Dark Mode Enabled', 'Your eyes will thank you!', 'success');
            
            console.log('🌙 Switched to Dark Mode');
        } else {
            // Enable light mode
            $('body').removeClass('dark-mode');
            localStorage.setItem('theme', 'light');
            updateThemeDisplay(false);
            
            // Show notification
            showNotification('☀️ Light Mode Enabled', 'Brightness at full power!', 'success');
            
            console.log('☀️ Switched to Light Mode');
        }
        
        // Update meta theme-color for mobile browsers
        updateMetaThemeColor(isChecked);
    });
    
    // ================= Update UI Text ================= 
    
    /**
     * Update theme-related UI text and icons
     */
    function updateThemeDisplay(isDark) {
        if (isDark) {
            // Dark mode UI
            $('#themeIcon').text('🌙');
            $('#themeText').text('Dark Mode');
            $('#currentTheme').text('Dark Mode');
        } else {
            // Light mode UI
            $('#themeIcon').text('☀️');
            $('#themeText').text('Light Mode');
            $('#currentTheme').text('Light Mode');
        }
    }
    
    // ================= Update Meta Theme Color ================= 
    
    /**
     * Update meta theme-color for mobile browser UI
     */
    function updateMetaThemeColor(isDark) {
        const metaThemeColor = $('meta[name="theme-color"]');
        
        if (isDark) {
            // Dark mode - dark blue-gray
            metaThemeColor.attr('content', '#1a202c');
        } else {
            // Light mode - gradient purple
            metaThemeColor.attr('content', '#667eea');
        }
    }
    
    // ================= Notification Helper ================= 
    
    /**
     * Show notification (if function exists)
     */
    function showNotification(title, message, type) {
        if (typeof window.showNotification === 'function') {
            window.showNotification(title, message, type);
        }
    }
    
    // ================= Auto-detect System Theme ================= 
    
    /**
     * Detect system dark mode preference
     * Only used on first visit if no saved preference
     */
    function detectSystemTheme() {
        // Check if user has saved preference
        const savedTheme = localStorage.getItem('theme');
        
        if (!savedTheme) {
            // No saved preference - check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (prefersDark) {
                $('body').addClass('dark-mode');
                $('#darkModeToggle').prop('checked', true);
                localStorage.setItem('theme', 'dark');
                updateThemeDisplay(true);
                console.log('🌙 Auto-detected Dark Mode preference');
            }
        }
    }
    
    // ================= Listen for System Theme Changes ================= 
    
    /**
     * Listen for system theme changes (optional feature)
     * Only applies if user hasn't manually set a preference
     */
    const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    darkModeMediaQuery.addEventListener('change', (e) => {
        // Only auto-switch if user hasn't set manual preference
        const userHasPreference = localStorage.getItem('theme');
        
        if (!userHasPreference) {
            const isDark = e.matches;
            
            if (isDark) {
                $('body').addClass('dark-mode');
                $('#darkModeToggle').prop('checked', true);
                updateThemeDisplay(true);
            } else {
                $('body').removeClass('dark-mode');
                $('#darkModeToggle').prop('checked', false);
                updateThemeDisplay(false);
            }
            
            console.log('🔄 System theme changed, auto-switching...');
        }
    });
    
    // ================= Keyboard Shortcut ================= 
    
    /**
     * Add keyboard shortcut: Ctrl/Cmd + Shift + D to toggle theme
     */
    $(document).on('keydown', function(e) {
        // Ctrl+Shift+D or Cmd+Shift+D
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            $('#darkModeToggle').trigger('click');
            
            const currentMode = $('body').hasClass('dark-mode') ? 'Dark' : 'Light';
            console.log(`⌨️ Keyboard shortcut used - Switched to ${currentMode} Mode`);
        }
    });
    
    // ================= Initialize on Page Load ================= 
    
    // Load saved theme immediately
    initializeTheme();
    
    // Optional: Detect system theme (only if no saved preference)
    // Uncomment line below to enable auto-detection
    // detectSystemTheme();
    
    console.log('✅ Theme toggle initialized');
    
});

// ================= Apply Theme BEFORE Page Load ================= 

/**
 * CRITICAL: Apply theme immediately to prevent flash
 * This runs before jQuery ready
 */
(function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
    }
})();