// ============================================================
// public/js/core/settings.js
// Settings — theme toggle & custom sound management
// Depends: app.js, audio.js
// ============================================================

$(function () {

    // ── Theme ─────────────────────────────────────────────────

    function setTheme(theme) {
        const isDark = theme === 'dark';
        $('html').attr('data-theme', theme);
        $('#darkModeToggle').prop('checked', isDark);
        $('#themeIcon').text(isDark ? '🌙' : '☀️');
        $('#themeText, #currentTheme').text(isDark ? 'Dark Mode' : 'Light Mode');
        localStorage.setItem('theme', theme);
    }

    setTheme(localStorage.getItem('theme') || 'light');

    $('#darkModeToggle').change(function() {
        const theme = $(this).is(':checked') ? 'dark' : 'light';
        setTheme(theme);
        window.showNotification(`Switched to ${theme} mode! 🎨`, 'info');
    });


    // ── Volume slider ─────────────────────────────────────────

    function updateVolumeSliderBackground() {
        const v = $('#volumeSlider').val();
        $('#volumeSlider').css('background',
            `linear-gradient(to right, var(--primary-color) 0%, var(--primary-color) ${v}%,
             var(--toggle-bg) ${v}%, var(--toggle-bg) 100%)`
        );
    }

    if ($('#volumeSlider').length) {
        const vol = window.getRingtoneVolume ? window.getRingtoneVolume() : 0.5;
        $('#volumeSlider').val(vol * 100);
        $('#volumeValue').text(Math.round(vol * 100) + '%');
        updateVolumeSliderBackground();
    }

    $('#ringtoneSelect').change(function() {
        const sel = $(this).val();
        if (sel) {
            window.setNotificationSound(sel);
            window.showNotification('Ringtone updated! 🔔', 'info');
        }
    });

    $('#volumeSlider').on('input', function() {
        const vol = $(this).val() / 100;
        window.setRingtoneVolume(vol);
        $('#volumeValue').text(Math.round(vol * 100) + '%');
        updateVolumeSliderBackground();
    });

    $('#testRingtoneBtn').click(() => window.playNotificationSound());


    // ── Upload custom sound ───────────────────────────────────

    $('#uploadSoundBtn').click(() => $('#soundFileInput').click());

    $('#soundFileInput').change(function(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('audio/')) {
            window.showNotification('Please select an audio file (MP3, WAV, etc.)', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            window.showNotification('File size must be less than 5MB', 'error');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) {
            const soundId = 'custom_' + Date.now();
            const name    = file.name.replace(/\.[^/.]+$/, '');
            const soundObj = { id: soundId, name, data: ev.target.result };
            window.addCustomSound(soundObj);

            const sounds = window.getCustomSounds();
            if (sounds.length === 1 || !window.getCurrentRingtone()) {
                window.setNotificationSound(soundId);
            }
            updateRingtoneDropdown();
            renderCustomSoundsList();
            window.showNotification(`Sound "${name}" uploaded! 🎵`, 'success');
        };
        reader.readAsDataURL(file);
        $(this).val('');
    });


    // ── Sound list ────────────────────────────────────────────

    window.loadCustomSounds = function() {
        updateRingtoneDropdown();
        renderCustomSoundsList();
    };

    function updateRingtoneDropdown() {
        const sounds        = window.getCustomSounds ? window.getCustomSounds() : [];
        const currentRingtone = window.getCurrentRingtone ? window.getCurrentRingtone() : '';
        const $sel = $('#ringtoneSelect');
        $sel.empty();
        if (sounds.length === 0) {
            $sel.append('<option value="">No ringtones uploaded yet</option>').prop('disabled', true);
        } else {
            $sel.prop('disabled', false);
            sounds.forEach(s => {
                $sel.append(`<option value="${s.id}" ${s.id === currentRingtone ? 'selected' : ''}>${s.name}</option>`);
            });
        }
    }

    function renderCustomSoundsList() {
        const sounds    = window.getCustomSounds ? window.getCustomSounds() : [];
        const container = $('#customSoundsList');
        container.empty();
        if (sounds.length === 0) {
            container.html(`
                <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                    <div style="font-size:2rem;margin-bottom:0.5rem;">🎵</div>
                    <div>No custom sounds uploaded yet</div>
                </div>
            `);
            return;
        }
        sounds.forEach(sound => {
            container.append(`
                <div class="custom-sound-item">
                    <div class="sound-info">
                        <span class="sound-icon">🎵</span>
                        <span class="sound-name">${sound.name}</span>
                    </div>
                    <div class="sound-actions">
                        <button class="btn-sound-play"   data-sound-id="${sound.id}">▶️</button>
                        <button class="btn-sound-delete" data-sound-id="${sound.id}">🗑️</button>
                    </div>
                </div>
            `);
        });
    }

    $(document).on('click', '.btn-sound-play', function() {
        const sounds = window.getCustomSounds ? window.getCustomSounds() : [];
        const sound  = sounds.find(s => s.id === $(this).data('sound-id'));
        if (sound) {
            const vol = window.getRingtoneVolume ? window.getRingtoneVolume() : 0.5;
            const a   = new Audio(sound.data);
            a.volume  = vol;
            a.play();
        }
    });

    $(document).on('click', '.btn-sound-delete', function() {
        const sounds = window.getCustomSounds ? window.getCustomSounds() : [];
        const id     = $(this).data('sound-id');
        const sound  = sounds.find(s => s.id === id);
        if (!confirm(`Delete "${sound.name}"?`)) return;
        window.removeCustomSound(id);
        updateRingtoneDropdown();
        renderCustomSoundsList();
        window.showNotification('Sound deleted ✅', 'success');
    });


    // ── Init ─────────────────────────────────────────────────
    window.loadCustomSounds();
    // ── Total Customers count ─────────────────────────────────
$.get('/customers/count', function(res) {
    if (res.success) {
        $('#totalCustomersCount').text(res.count);
    }
});

});