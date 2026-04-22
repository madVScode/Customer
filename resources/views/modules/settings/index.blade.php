{{-- ============================================================ --}}
{{-- resources/views/modules/settings/index.blade.php            --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div id="settingsView">
    <h1 class="page-title">⚙️ Settings</h1>
    <div class="settings-container">

        {{-- Appearance --}}
        <div class="settings-section">
            <h3>🎨 Appearance</h3>
            <div class="setting-item">
                <div class="setting-info">
                    <div class="setting-title"><span id="themeIcon">☀️</span><span id="themeText">Light Mode</span></div>
                    <div class="setting-description">Switch between light and dark theme</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        {{-- Tourist Portal --}}
        <div class="settings-section">
            <h3>🌏 Tourist Portal</h3>
            <div class="setting-item" style="flex-direction:column;align-items:flex-start;background:linear-gradient(135deg,#0f2027,#2c5364);padding:1.5rem;border-radius:12px;">
                <div class="setting-title" style="margin-bottom:.5rem;color:white;font-size:1.05rem;">🌏 Portal Pelancong TravelPro</div>
                <div class="setting-description" style="margin-bottom:1rem;color:rgba(255,255,255,.8);">Akses portal awam untuk pelancong — lihat trip, buat tempahan, dan lebih lagi.</div>
                <a href="{{ route('tourist.index') }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.25rem;border-radius:20px;background:rgba(0,212,170,.25);border:1.5px solid rgba(0,212,170,.6);color:white;font-size:.85rem;font-weight:700;text-decoration:none;">
                    🌏 Buka Portal Pelancong ↗
                </a>
            </div>
        </div>

        {{-- Sound & Notifications --}}
        <div class="settings-section">
            <h3>🔔 Sound & Notifications</h3>
            <div class="setting-item" style="flex-direction:column;align-items:flex-start;background:linear-gradient(135deg,#667eea,#764ba2);padding:1.5rem;border-radius:12px;margin-bottom:1.5rem;">
                <div class="setting-title" style="margin-bottom:.5rem;color:white;font-size:1.1rem;">📤 Upload Notification Sound</div>
                <div class="setting-description" style="margin-bottom:1rem;color:rgba(255,255,255,.9);">Upload MP3 atau WAV (max 5MB setiap satu)</div>
                <button id="uploadSoundBtn" class="btn btn-primary" style="background:white;color:#667eea;font-weight:600;">📤 Choose Sound File</button>
                <input type="file" id="soundFileInput" accept="audio/*" style="display:none;">
            </div>
            <div class="setting-item">
                <div class="setting-info">
                    <div class="setting-title">🎵 Active Notification Ringtone</div>
                    <div class="setting-description">Pilih bunyi notifikasi</div>
                </div>
                <div class="ringtone-controls">
                    <select id="ringtoneSelect" class="ringtone-select"><option value="">No ringtones uploaded yet</option></select>
                    <button id="testRingtoneBtn" class="test-ringtone-btn">▶️ Test</button>
                </div>
            </div>
            <div class="setting-item">
                <div class="setting-info">
                    <div class="setting-title">🔊 Notification Volume</div>
                    <div class="setting-description">Laraskan kelantangan bunyi notifikasi</div>
                </div>
                <div class="volume-control">
                    <span class="volume-icon">🔇</span>
                    <div class="volume-slider-container"><input type="range" min="0" max="100" value="50" class="volume-slider" id="volumeSlider"></div>
                    <span class="volume-icon">🔊</span>
                    <span class="volume-value" id="volumeValue">50%</span>
                </div>
            </div>
            <div class="setting-item" style="flex-direction:column;align-items:flex-start;">
                <div class="setting-title" style="margin-bottom:1rem;">📋 My Uploaded Sounds</div>
                <div id="customSoundsList" style="width:100%;"></div>
            </div>
        </div>

        {{-- System Info --}}
        <div class="settings-section">
            <h3>ℹ️ System Information</h3>
            <div class="setting-item" style="flex-direction:column;align-items:flex-start;">
                <div style="width:100%;color:var(--text-secondary);line-height:1.8;">
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border-color);"><span><strong>Version:</strong></span><span>2.4.0</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border-color);"><span><strong>Total Customers:</strong></span><span id="totalCustomersCount">0</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border-color);"><span><strong>Last Updated:</strong></span><span id="lastUpdated">Just now</span></div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;"><span><strong>Current Theme:</strong></span><span id="currentTheme">Light Mode</span></div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection