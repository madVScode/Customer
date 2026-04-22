{{-- ============================================================ --}}
{{-- resources/views/auth/admin-login.blade.php                 --}}
{{-- ✅ HALAMAN LOGIN ADMIN BERASINGAN — Keselamatan ketat       --}}
{{-- URL: /admin/login  (dirahsiakan dari login utama)          --}}
{{-- ============================================================ --}}
@extends('layouts.auth')
@section('title', 'Admin Access — Arson Trip')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Sora:wght@400;500;600;700;800&display=swap');

    * { margin:0; padding:0; box-sizing:border-box; }

    :root {
        --navy:#0A0F1E;
        --navy-2:#111827;
        --navy-3:#1A2235;
        --panel:#141B2D;
        --border:#1E2D47;
        --accent:#3B82F6;
        --accent-h:#2563EB;
        --accent-glow:rgba(59,130,246,.18);
        --red:#EF4444;
        --red-soft:#FEE2E2;
        --green:#10B981;
        --green-soft:#D1FAE5;
        --text:#E2E8F0;
        --text-dim:#64748B;
        --text-muted:#334155;
        --mono:'JetBrains Mono',monospace;
        --sans:'Sora',-apple-system,sans-serif;
    }

    html, body { width:100%; height:100%; overflow:hidden; }

    body {
        font-family: var(--sans);
        background: var(--navy);
        display: flex;
        align-items: center;
        justify-content: center;
        -webkit-font-smoothing: antialiased;
        position: relative;
    }

    /* ── Grid noise background ── */
    body::before {
        content:'';
        position:fixed; inset:0;
        background-image:
            linear-gradient(rgba(59,130,246,.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(59,130,246,.04) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events:none;
        z-index:0;
    }

    /* ── Glow orb ── */
    body::after {
        content:'';
        position:fixed;
        width:600px; height:600px;
        border-radius:50%;
        background: radial-gradient(circle, rgba(59,130,246,.08) 0%, transparent 70%);
        top:-100px; left:-100px;
        pointer-events:none;
        z-index:0;
    }

    .wrapper {
        position:relative; z-index:1;
        width:100%; height:100%;
        display:flex; align-items:center; justify-content:center;
        padding:20px; overflow-y:auto;
    }

    /* ── Card ── */
    .card {
        width:100%; max-width:440px;
        background: var(--panel);
        border:1px solid var(--border);
        border-radius:16px;
        box-shadow:
            0 0 0 1px rgba(59,130,246,.08),
            0 32px 80px rgba(0,0,0,.6),
            0 0 60px rgba(59,130,246,.05);
        animation: cardIn .5s cubic-bezier(.16,1,.3,1);
        overflow:hidden;
    }
    @keyframes cardIn {
        from { opacity:0; transform:translateY(20px) scale(.98); }
        to   { opacity:1; transform:translateY(0) scale(1); }
    }

    /* ── Top security bar ── */
    .sec-bar {
        background: linear-gradient(90deg, var(--accent-h), var(--accent));
        padding:8px 20px;
        display:flex; align-items:center; gap:8px;
        font-family:var(--mono); font-size:10px; font-weight:600;
        letter-spacing:.12em; color:rgba(255,255,255,.9);
        text-transform:uppercase;
    }
    .sec-dot {
        width:7px; height:7px; border-radius:50%;
        background:white; opacity:.7;
        animation:pulse 2s ease-in-out infinite;
    }
    @keyframes pulse { 0%,100%{opacity:.7} 50%{opacity:1} }

    /* ── Header ── */
    .header { padding:32px 36px 24px; text-align:center; }
    .shield-wrap {
        width:64px; height:64px; margin:0 auto 18px;
        background:linear-gradient(135deg, var(--accent-h), var(--accent));
        border-radius:16px;
        display:flex; align-items:center; justify-content:center;
        font-size:1.8rem;
        box-shadow: 0 0 0 1px rgba(59,130,246,.3), 0 8px 24px rgba(59,130,246,.25);
        position:relative;
    }
    .shield-wrap::after {
        content:'';
        position:absolute; inset:-3px;
        border-radius:19px;
        border:1px solid rgba(59,130,246,.2);
        animation: ringPulse 3s ease-in-out infinite;
    }
    @keyframes ringPulse { 0%,100%{opacity:.6;transform:scale(1)} 50%{opacity:1;transform:scale(1.04)} }

    .title   { font-size:20px; font-weight:800; color:var(--text); letter-spacing:-.02em; }
    .sub     { font-size:12px; color:var(--text-dim); margin-top:5px; font-family:var(--mono); }

    /* ── Body ── */
    .body { padding:0 36px 36px; }

    /* ── Warning box ── */
    .warn-box {
        background:rgba(239,68,68,.07);
        border:1px solid rgba(239,68,68,.2);
        border-radius:10px;
        padding:11px 14px;
        margin-bottom:22px;
        display:flex; align-items:flex-start; gap:10px;
    }
    .warn-icon { font-size:13px; flex-shrink:0; margin-top:1px; }
    .warn-text { font-size:11px; color:#F87171; font-family:var(--mono); line-height:1.6; }
    .warn-text strong { display:block; margin-bottom:2px; color:#FCA5A5; }

    /* ── Form elements ── */
    .form-group { margin-bottom:18px; }
    .form-label {
        display:block; font-size:10px; font-weight:700;
        color:var(--text-dim); text-transform:uppercase;
        letter-spacing:.1em; margin-bottom:8px;
        font-family:var(--mono);
    }
    .input-wrap { position:relative; }
    .form-input {
        width:100%; padding:12px 16px;
        background:var(--navy-2);
        border:1px solid var(--border);
        border-radius:9px;
        font-size:14px; font-weight:500; color:var(--text);
        font-family:var(--sans);
        transition:all .2s;
    }
    .form-input:focus {
        outline:none;
        border-color: var(--accent);
        background: var(--navy-3);
        box-shadow: 0 0 0 3px var(--accent-glow);
    }
    .form-input::placeholder { color:var(--text-muted); }

    .pw-toggle {
        position:absolute; right:12px; top:50%; transform:translateY(-50%);
        background:none; border:none; cursor:pointer; font-size:15px; padding:4px;
        opacity:.6; transition:opacity .2s;
    }
    .pw-toggle:hover { opacity:1; }

    /* ── Extras row ── */
    .extras {
        display:flex; justify-content:space-between; align-items:center;
        margin-bottom:22px; font-size:12px;
    }
    .rem-label {
        display:flex; align-items:center; gap:7px;
        color:var(--text-dim); cursor:pointer; font-weight:500;
    }
    .rem-label input { accent-color:var(--accent); }
    .forgot-link {
        color:var(--accent); font-weight:600; text-decoration:none;
        font-size:12px; transition:color .2s;
    }
    .forgot-link:hover { color:#93C5FD; }

    /* ── Submit button ── */
    .btn-submit {
        width:100%; padding:14px; border:none; border-radius:10px;
        font-size:14px; font-weight:700; font-family:var(--sans);
        cursor:pointer; letter-spacing:.01em;
        color:white;
        background:linear-gradient(135deg, var(--accent-h), var(--accent));
        box-shadow: 0 4px 16px rgba(59,130,246,.3);
        transition:all .2s;
    }
    .btn-submit:hover { filter:brightness(1.1); transform:translateY(-1px); box-shadow:0 6px 22px rgba(59,130,246,.4); }
    .btn-submit:disabled { opacity:.5; cursor:not-allowed; transform:none; filter:none; }

    /* ── Back link ── */
    .back-link {
        display:block; text-align:center; margin-top:16px;
        font-size:12px; color:var(--text-dim); text-decoration:none;
        font-family:var(--mono); transition:color .2s;
    }
    .back-link:hover { color:var(--text); }

    /* ── Alert ── */
    .alert {
        padding:11px 14px; border-radius:9px; margin-bottom:18px;
        display:none; font-size:12px; font-weight:600;
        font-family:var(--mono); line-height:1.5;
        animation:alertIn .3s ease;
    }
    @keyframes alertIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
    .alert-error   { background:rgba(239,68,68,.1); color:#FCA5A5; border:1px solid rgba(239,68,68,.25); }
    .alert-success { background:rgba(16,185,129,.1); color:#6EE7B7; border:1px solid rgba(16,185,129,.25); }

    /* ── Footer stamp ── */
    .footer-stamp {
        padding:12px 36px;
        border-top:1px solid var(--border);
        display:flex; align-items:center; justify-content:center; gap:6px;
        font-family:var(--mono); font-size:10px; color:var(--text-muted);
        letter-spacing:.06em;
    }

    /* ── Spinner ── */
    .spinner {
        display:inline-block; width:14px; height:14px;
        border:2px solid rgba(255,255,255,.25);
        border-top-color:white; border-radius:50%;
        animation:spin .6s linear infinite; margin-right:7px; vertical-align:middle;
    }
    @keyframes spin { to { transform:rotate(360deg); } }

    /* ── Forgot modal ── */
    .modal-overlay {
        position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,.7); backdrop-filter:blur(8px);
        display:none; align-items:center; justify-content:center; padding:20px;
    }
    .modal-overlay.show { display:flex; }
    .modal-box {
        background:var(--panel); border:1px solid var(--border);
        border-radius:14px; max-width:400px; width:100%;
        box-shadow:0 32px 80px rgba(0,0,0,.6);
        padding:30px; animation:cardIn .3s cubic-bezier(.16,1,.3,1);
    }
    .modal-title {
        font-size:16px; font-weight:700; color:var(--text);
        margin-bottom:6px;
    }
    .modal-sub { font-size:12px; color:var(--text-dim); margin-bottom:20px; font-family:var(--mono); }
    .modal-close {
        float:right; background:var(--navy-3); border:1px solid var(--border);
        border-radius:6px; width:28px; height:28px; cursor:pointer;
        font-size:13px; color:var(--text-dim); display:flex; align-items:center; justify-content:center;
        transition:background .2s;
    }
    .modal-close:hover { background:var(--border); }

    @media (max-width:480px) {
        .card { border-radius:12px; }
        .header { padding:24px 24px 18px; }
        .body   { padding:0 24px 28px; }
        .footer-stamp { padding:12px 24px; }
    }
</style>
@endpush

@section('content')
<div class="wrapper">
  <div class="card">

    {{-- Security bar --}}
    <div class="sec-bar">
        <div class="sec-dot"></div>
        RESTRICTED ACCESS — ADMIN SYSTEM ONLY
    </div>

    {{-- Header --}}
    <div class="header">
        <div class="shield-wrap">🔐</div>
        <div class="title">Admin System Login</div>
        <div class="sub">admin_system</div>
    </div>

    {{-- Body --}}
    <div class="body">

        {{-- Alerts --}}
        <div class="alert alert-error"   id="errMsg"></div>
        <div class="alert alert-success" id="okMsg"></div>

        {{-- Warning notice --}}
        <div class="warn-box">
            <span class="warn-icon">⚠️</span>
            <div class="warn-text">
                <strong>AKSES TERHAD</strong>
                Halaman ini hanya untuk admin sistem yang sah. Semua percubaan log masuk direkodkan. Akses tidak sah akan dilaporkan.
            </div>
        </div>

        {{-- Form --}}
        <form id="fAdminLogin">
            @csrf

            <div class="form-group">
                <label class="form-label">Email Admin</label>
                <input type="email" id="aEmail" class="form-input"
                       placeholder="admin@syarikat.com"
                       required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label">Kata Laluan</label>
                <div class="input-wrap">
                    <input type="password" id="aPw" class="form-input"
                           placeholder="••••••••••••"
                           required autocomplete="current-password">
                    <button type="button" class="pw-toggle" id="pwToggle">
                        <span id="eyeIcon">👁️</span>
                    </button>
                </div>
            </div>

            <div class="extras">
                <label class="rem-label">
                    <input type="checkbox" id="aRem"> Ingat saya
                </label>
                <a href="#" class="forgot-link" id="forgotLink">Lupa kata laluan?</a>
            </div>

            <button type="submit" id="btnLogin" class="btn-submit">
                <span id="btnText">Log Masuk</span>
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link">← Kembali ke halaman login utama</a>
    </div>

    {{-- Footer stamp --}}
    <div class="footer-stamp">
        🔒 &nbsp;SESI DILINDUNGI &nbsp;·&nbsp; Arson system
    </div>

  </div>
</div>

{{-- Forgot Password Modal --}}
<div class="modal-overlay" id="forgotModal">
  <div class="modal-box">
    <button class="modal-close" id="closeForgot">✕</button>
    <div class="modal-title">🔑 Reset Kata Laluan</div>
    <div class="modal-sub">Masukkan emel admin untuk pautan reset</div>

    <div class="alert alert-error"   id="errForgot" style="display:none;"></div>
    <div class="alert alert-success" id="okForgot"  style="display:none;"></div>

    <form id="fForgot">
        @csrf
        <div class="form-group">
            <label class="form-label">Email Admin</label>
            <input type="email" id="resetEmail" class="form-input"
                   placeholder="admin@syarikat.com" required>
        </div>
        <button type="submit" id="btnForgot" class="btn-submit">
            <span id="txtForgot">Hantar Pautan Reset</span>
        </button>
        <button type="button" id="backForgot" class="back-link"
                style="background:none;border:none;cursor:pointer;margin-top:10px;">
            ← Kembali
        </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ── Password toggle ─────────────────────────────────
    $('#pwToggle').on('click', function () {
        var $inp = $('#aPw');
        var vis  = $inp.attr('type') === 'text';
        $inp.attr('type', vis ? 'password' : 'text');
        $('#eyeIcon').text(vis ? '👁️' : '🙈');
    });

    // ── Login form submit ────────────────────────────────
    $('#fAdminLogin').on('submit', function (e) {
        e.preventDefault();

        var $btn = $('#btnLogin');
        var $txt = $('#btnText');
        $btn.prop('disabled', true);
        $txt.html('<span class="spinner"></span>Mengesahkan...');
        $('#errMsg, #okMsg').hide();

        $.ajax({
            url:  '{{ route("admin.login.post") }}',
            type: 'POST',
            data: {
                email:    $('#aEmail').val(),
                password: $('#aPw').val(),
                remember: $('#aRem').is(':checked')
            },
            success: function (r) {
                if (r.success) {
                    $('#okMsg').text(r.message || 'Berjaya! Mengalihkan...').show();
                    $txt.html('✓ Akses Diberikan...');
                    setTimeout(function () { window.location.href = r.redirect; }, 900);
                }
            },
            error: function (x) {
                $btn.prop('disabled', false);
                $txt.html('Log Masuk');
                var msg = (x.responseJSON && x.responseJSON.message) || 'Log masuk gagal. Sila cuba semula.';
                $('#errMsg').text(msg).show();
            }
        });
    });

    // ── Enter key ────────────────────────────────────────
    $('#aEmail, #aPw').on('keypress', function (e) {
        if (e.which === 13) $('#fAdminLogin').submit();
    });

    // ── Forgot password modal ────────────────────────────
    $('#forgotLink').on('click', function (e) {
        e.preventDefault();
        $('#forgotModal').addClass('show');
        $('#resetEmail').focus();
    });
    $('#closeForgot, #backForgot').on('click', function () {
        $('#forgotModal').removeClass('show');
        $('#fForgot')[0].reset();
        $('#errForgot, #okForgot').hide();
    });
    $('#forgotModal').on('click', function (e) {
        if (e.target === this) $(this).removeClass('show');
    });

    $('#fForgot').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btnForgot'), $txt = $('#txtForgot');
        $btn.prop('disabled', true);
        $txt.html('<span class="spinner"></span>Menghantar...');
        $('#errForgot, #okForgot').hide();

        $.ajax({
            url:  '{{ route("password.forgot") }}',
            type: 'POST',
            data: { email: $('#resetEmail').val() },
            success: function (r) {
                if (r.success) {
                    $('#okForgot').text(r.message).show();
                    $txt.html('✓ Dihantar!');
                    setTimeout(function () {
                        $('#forgotModal').removeClass('show');
                        $btn.prop('disabled', false);
                        $txt.html('Hantar Pautan Reset');
                    }, 2500);
                }
            },
            error: function (x) {
                $btn.prop('disabled', false);
                $txt.html('Hantar Pautan Reset');
                $('#errForgot').text((x.responseJSON && x.responseJSON.message) || 'Gagal menghantar emel.').show();
            }
        });
    });

    // ── Auto focus ───────────────────────────────────────
    $('#aEmail').focus();
});
</script>
@endpush