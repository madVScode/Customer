// ============================================================
// public/js/core/audio.js
// Audio System — ringtone management & playback
// Depends: app.js (showNotification)
// ============================================================

$(function () {

    // ── State ────────────────────────────────────────────────
    let notificationSound = null;
    let currentRingtone   = localStorage.getItem('ringtone') || '';
    let ringtoneVolume    = parseFloat(localStorage.getItem('ringtoneVolume')) || 0.5;
    let audioUnlocked     = false; // Unlock audio after user interaction

    // Expose custom sounds
    let _customSounds = JSON.parse(localStorage.getItem('customSounds') || '[]');
    window._audio = { get customSounds() { return _customSounds; } };


    // ── Unlock audio on first user interaction ─────────────────
    function unlockAudio() {
        if (!audioUnlocked) {
            const dummy = new Audio();
            dummy.play().catch(() => {});
            audioUnlocked = true;
            console.log('🔓 Audio unlocked');
        }
    }
    $(document).one('click', unlockAudio);


    // ── Init ─────────────────────────────────────────────────
    function initializeSound() {
        if (_customSounds.length > 0) {
            if (!currentRingtone || !_customSounds.find(s => s.id === currentRingtone)) {
                currentRingtone = _customSounds[0].id;
                localStorage.setItem('ringtone', currentRingtone);
            }
            setNotificationSound(currentRingtone);
        }
    }


    // ── Public API ───────────────────────────────────────────

    window.setNotificationSound = function(ringtoneId) {
        currentRingtone = ringtoneId;
        localStorage.setItem('ringtone', ringtoneId);
        if (notificationSound) { notificationSound.pause(); notificationSound = null; }
        const sound = _customSounds.find(s => s.id === ringtoneId);
        if (sound) {
            notificationSound = new Audio(sound.data);
            notificationSound.volume = ringtoneVolume;
        }
    };

    window.setRingtoneVolume = function(volume) {
        ringtoneVolume = volume;
        localStorage.setItem('ringtoneVolume', volume);
        if (notificationSound) notificationSound.volume = volume;
    };

    window.playNotificationSound = function() {
        if (notificationSound && audioUnlocked) {
            notificationSound.currentTime = 0;
            notificationSound.play().catch(e => console.log('Audio play failed:', e));
        }
    };

    window.getCustomSounds    = () => _customSounds;
    window.getCurrentRingtone = () => currentRingtone;
    window.getRingtoneVolume  = () => ringtoneVolume;

    window.addCustomSound = function(soundObj) {
        _customSounds.push(soundObj);
        localStorage.setItem('customSounds', JSON.stringify(_customSounds));
        if (!currentRingtone) {
            window.setNotificationSound(soundObj.id);
        }
    };

    window.removeCustomSound = function(id) {
        _customSounds = _customSounds.filter(s => s.id !== id);
        localStorage.setItem('customSounds', JSON.stringify(_customSounds));
        if (currentRingtone === id) {
            if (_customSounds.length > 0) {
                window.setNotificationSound(_customSounds[0].id);
            } else {
                currentRingtone = '';
                localStorage.setItem('ringtone', '');
                notificationSound = null;
            }
        }
    };

    // ── Initialize on ready ──────────────────────────────────
    initializeSound();

});