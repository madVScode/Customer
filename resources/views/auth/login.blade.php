{{-- ============================================================ --}}
{{-- resources/views/auth/login.blade.php                        --}}
{{-- ✅ Halaman login utama — Syarikat & Tester sahaja           --}}
{{-- ✅ Tiada link ke admin (keselamatan)                        --}}
{{-- ============================================================ --}}
@extends('layouts.auth')
@section('title', 'Sign In - Customer Pro')

@push('styles')
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    :root {
        --blue:#2D8CFF; --blue-h:#0B5CFF;
        --purple:#667eea; --purple-d:#764ba2;
        --dark:#1C1C1E; --gray:#747487;
        --light:#F5F5F7; --border:#E5E5EA;
    }
    html,body { width:100%;height:100%;overflow:hidden; }
    body { font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;
           background:linear-gradient(135deg,#667eea,#764ba2);
           display:flex;align-items:center;justify-content:center;
           -webkit-font-smoothing:antialiased; }

    .wrapper { width:100%;height:100%;display:flex;align-items:center;justify-content:center;
               padding:20px;overflow-y:auto;animation:fadeIn .5s ease; }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }

    .card { background:white;border-radius:20px;width:100%;max-width:460px;
            box-shadow:0 24px 60px rgba(0,0,0,.28);animation:slideUp .5s cubic-bezier(.16,1,.3,1); }
    @keyframes slideUp { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }

    /* ── Role Selector ── */
    .role-header { padding:36px 36px 28px;text-align:center; }
    .role-title { font-size:22px;font-weight:800;color:var(--dark);margin-bottom:6px; }
    .role-sub   { font-size:13px;color:var(--gray); }

    .role-grid  { display:grid;grid-template-columns:repeat(2,1fr);gap:12px;
                  padding:0 28px 36px; }
    .role-btn   { padding:20px 12px;border:2px solid var(--border);border-radius:14px;
                  background:var(--light);cursor:pointer;transition:all .2s;
                  font-family:'Inter',sans-serif;text-align:center; }
    .role-btn:hover  { border-color:var(--purple);background:#f3f0ff; }
    .role-btn.active { border-color:var(--purple);background:linear-gradient(135deg,#667eea15,#764ba215);
                       box-shadow:0 4px 16px rgba(102,126,234,.2); }
    .role-icon  { font-size:32px;margin-bottom:10px; }
    .role-label { font-size:13px;font-weight:700;color:var(--dark); }
    .role-desc  { font-size:11px;color:var(--gray);margin-top:4px; }

    /* ── Form Panel ── */
    .form-panel { display:none;padding:0 36px 36px;animation:fadeIn .3s ease; }
    .form-panel.show { display:block; }

    .form-header { display:flex;align-items:center;gap:12px;margin-bottom:24px;
                   padding-bottom:16px;border-bottom:2px solid var(--light); }
    .form-logo   { width:44px;height:44px;border-radius:12px;display:flex;
                   align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0; }
    .logo-owner  { background:linear-gradient(135deg,#667eea,#764ba2); }
    .logo-tester { background:linear-gradient(135deg,#ff9800,#f44336); }
    .form-title  { font-size:16px;font-weight:700;color:var(--dark); }
    .form-sub    { font-size:12px;color:var(--gray);margin-top:2px; }

    .form-group  { margin-bottom:16px; }
    .form-label  { display:block;font-size:12px;font-weight:700;color:var(--dark);
                   margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em; }
    .input-wrap  { position:relative; }
    .form-input  { width:100%;padding:12px 16px;border:2px solid var(--border);border-radius:10px;
                   font-size:14px;color:var(--dark);background:var(--light);
                   font-family:'Inter',sans-serif;transition:all .2s; }
    .form-input:focus { outline:none;border-color:var(--purple);background:white;
                        box-shadow:0 0 0 3px rgba(102,126,234,.12); }
    .form-input::placeholder { color:#C7C7CC; }
    .pw-toggle   { position:absolute;right:12px;top:50%;transform:translateY(-50%);
                   background:none;border:none;cursor:pointer;font-size:17px;padding:4px; }

    .btn-login   { width:100%;padding:14px;border:none;border-radius:12px;font-size:15px;
                   font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;
                   color:white;transition:all .2s;margin-top:4px; }
    .btn-owner   { background:linear-gradient(135deg,#667eea,#764ba2);
                   box-shadow:0 4px 14px rgba(102,126,234,.35); }
    .btn-tester  { background:linear-gradient(135deg,#ff9800,#f44336);
                   box-shadow:0 4px 14px rgba(255,152,0,.35); }
    .btn-login:hover    { filter:brightness(1.08);transform:translateY(-1px); }
    .btn-login:disabled { opacity:.6;cursor:not-allowed;transform:none;filter:none; }

    .btn-back    { display:flex;align-items:center;gap:6px;background:none;border:none;
                   cursor:pointer;font-size:13px;color:var(--gray);font-family:'Inter',sans-serif;
                   margin-bottom:20px;padding:0;transition:color .2s; }
    .btn-back:hover { color:var(--dark); }

    .alert { padding:12px 14px;border-radius:10px;margin-bottom:16px;display:none;
             font-size:13px;font-weight:500;line-height:1.5;animation:slideDown .3s ease; }
    @keyframes slideDown { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
    .alert-error   { background:#FFF5F5;color:#C53030;border:1px solid #FEB2B2; }
    .alert-success { background:#F0FFF4;color:#276749;border:1px solid #9AE6B4; }

    /* ── Tester credentials box ── */
    .cred-box    { background:linear-gradient(135deg,#fff8e1,#fff3e0);border:2px solid #ffc107;
                   border-radius:12px;padding:14px 16px;margin-bottom:16px; }
    .cred-title  { font-size:11px;font-weight:800;color:#856404;text-transform:uppercase;
                   letter-spacing:.06em;margin-bottom:10px; }
    .cred-row    { display:flex;align-items:center;justify-content:space-between;
                   margin-bottom:6px;gap:8px; }
    .cred-label  { font-size:11px;color:#92400e;font-weight:600;flex-shrink:0; }
    .cred-value  { font-family:monospace;font-size:12px;background:white;border:1px solid #fcd34d;
                   padding:4px 10px;border-radius:6px;cursor:pointer;color:var(--dark);
                   font-weight:700;flex:1;text-align:center;transition:background .15s;
                   user-select:all; }
    .cred-value:hover { background:#fffbeb; }
    .cred-hint   { font-size:10px;color:#92400e;margin-top:4px; }

    .spinner { display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.3);
               border-top-color:white;border-radius:50%;animation:spin .6s linear infinite;
               margin-right:7px;vertical-align:middle; }
    @keyframes spin { to{transform:rotate(360deg)} }
</style>
@endpush

@section('content')
<div class="wrapper">
  <div class="card">

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- Step 1: Role selector                                      --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div id="stepRole">
        <div class="role-header">
            <div style="font-size:2.5rem;margin-bottom:12px;">👥</div>
            <div class="role-title">Customer Pro</div>
            <div class="role-sub">Pilih jenis akaun untuk log masuk</div>
        </div>

        <div class="role-grid">
            <button class="role-btn" data-role="owner_business">
                <div class="role-icon">🏢</div>
                <div class="role-label">Syarikat</div>
                <div class="role-desc">Owner business</div>
            </button>
            <button class="role-btn" data-role="tester">
                <div class="role-icon">🧪</div>
                <div class="role-label">Tester</div>
                <div class="role-desc">Demo sahaja</div>
            </button>
        </div>
    </div>{{-- /stepRole --}}

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- Step 2: Login Forms (tersembunyi secara lalai)             --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div id="stepForm" style="display:none;padding:28px 0 0;">

        {{-- ── Owner Business form ─────────────────────────── --}}
        <div id="formOwner" class="form-panel">
            <div style="padding:0 36px;">
                <button class="btn-back" id="backOwner">← Tukar jenis akaun</button>
                <div class="alert alert-error"   id="errOwner"></div>
                <div class="alert alert-success" id="okOwner"></div>
                <div class="form-header">
                    <div class="form-logo logo-owner">🏢</div>
                    <div>
                        <div class="form-title">Log Masuk Syarikat</div>
                        <div class="form-sub">Akaun owner business</div>
                    </div>
                </div>
                <form id="fOwner">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">No. Pendaftaran Syarikat</label>
                        <input type="text" id="oReg" class="form-input"
                               placeholder="e.g., 202001012345" required autocomplete="off">
                        <small style="color:var(--gray);font-size:11px;margin-top:4px;display:block;">
                            Masukkan no. pendaftaran syarikat yang didaftarkan oleh admin
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kata Laluan</label>
                        <div class="input-wrap">
                            <input type="password" id="oPw" class="form-input"
                                   placeholder="Kata laluan akaun anda" required>
                            <button type="button" class="pw-toggle" data-target="oPw" data-eye="oEye">
                                <span id="oEye">👁️</span>
                            </button>
                        </div>
                    </div>
                    <div style="margin-bottom:18px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;font-weight:500;">
                            <input type="checkbox" id="oRem" style="accent-color:var(--purple);"> Ingat saya
                        </label>
                    </div>
                    <button type="submit" id="btnOwner" class="btn-login btn-owner">
                        <span id="txtOwner">Log Masuk</span>
                    </button>
                </form>
            </div>
        </div>{{-- /formOwner --}}

        {{-- ── Tester form ─────────────────────────────────── --}}
        <div id="formTester" class="form-panel">
            <div style="padding:0 36px;">
                <button class="btn-back" id="backTester">← Tukar jenis akaun</button>
                <div class="alert alert-error"   id="errTester"></div>
                <div class="alert alert-success" id="okTester"></div>
                <div class="form-header">
                    <div class="form-logo logo-tester">🧪</div>
                    <div>
                        <div class="form-title">Tester Log Masuk</div>
                        <div class="form-sub">Mod demo — tiada data disimpan</div>
                    </div>
                </div>
                <div class="cred-box">
                    <div class="cred-title">🔑 Kelayakan Demo — Klik untuk salin</div>
                    <div class="cred-row">
                        <span class="cred-label">Email</span>
                        <span class="cred-value" data-fill="tEmail">tester@system.local</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-label">Kata Laluan</span>
                        <span class="cred-value" data-fill="tPw">tester123</span>
                    </div>
                    <div class="cred-hint">💡 Klik pada nilai di atas untuk isi automatik ke dalam form</div>
                </div>
                <form id="fTester">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" id="tEmail" class="form-input"
                               placeholder="tester@system.local" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kata Laluan</label>
                        <div class="input-wrap">
                            <input type="password" id="tPw" class="form-input"
                                   placeholder="Kata laluan" required>
                            <button type="button" class="pw-toggle" data-target="tPw" data-eye="tEye">
                                <span id="tEye">👁️</span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" id="btnTester" class="btn-login btn-tester" style="margin-bottom:32px;">
                        <span id="txtTester">Log Masuk sebagai Tester</span>
                    </button>
                </form>
            </div>
        </div>{{-- /formTester --}}

    </div>{{-- /stepForm --}}

  </div>{{-- /card --}}
</div>{{-- /wrapper --}}
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ── Role selector ─────────────────────────────────────────
    $('.role-btn').on('click', function () {
        var role = $(this).data('role');
        $('.role-btn').removeClass('active');
        $(this).addClass('active');

        $('#stepRole').hide();
        $('#stepForm').show();
        $('.form-panel').removeClass('show');

        if (role === 'owner_business') { $('#formOwner').addClass('show');  $('#oReg').focus(); }
        if (role === 'tester')         { $('#formTester').addClass('show'); $('#tEmail').focus(); }
    });

    // ── Back buttons ──────────────────────────────────────────
    $('#backOwner, #backTester').on('click', function () {
        $('#stepForm').hide();
        $('#stepRole').show();
        $('.form-panel').removeClass('show');
        $('.alert').hide();
    });

    // ── Password toggle ───────────────────────────────────────
    $(document).on('click', '.pw-toggle', function () {
        var target = $(this).data('target');
        var eye    = $(this).data('eye');
        var $inp   = $('#' + target);
        var vis    = $inp.attr('type') === 'text';
        $inp.attr('type', vis ? 'password' : 'text');
        $('#' + eye).text(vis ? '👁️' : '🙈');
    });

    // ── Tester credentials auto-fill ─────────────────────────
    $('.cred-value').on('click', function () {
        var fillId = $(this).data('fill');
        var val    = $(this).text().trim();
        var $inp   = $('#' + fillId);
        if ($inp.attr('type') === 'password') $inp.attr('type', 'text');
        $inp.val(val).css('border-color', '#ffc107');
        setTimeout(function () { $inp.css('border-color', ''); }, 800);
    });

    // ── Generic login submit ──────────────────────────────────
    function doLogin(opts) {
        var $btn = $('#' + opts.btnId);
        var $txt = $('#' + opts.txtId);
        var $err = $('#' + opts.errId);
        var $ok  = $('#' + opts.okId);

        $btn.prop('disabled', true);
        $txt.html('<span class="spinner"></span>Log masuk...');
        $err.hide(); $ok.hide();

        $.ajax({
            url:  '{{ route("login.post") }}',
            type: 'POST',
            data: opts.data,
            success: function (r) {
                if (r.success) {
                    $ok.text(r.message || 'Berjaya!').show();
                    $txt.html('✓ Berjaya! Mengalihkan...');
                    setTimeout(function () { window.location.href = r.redirect; }, 900);
                }
            },
            error: function (x) {
                $btn.prop('disabled', false);
                $txt.html(opts.btnLabel || 'Log Masuk');
                var msg = (x.responseJSON && x.responseJSON.message) || 'Log masuk gagal. Sila cuba semula.';
                $err.text(msg).show();
            }
        });
    }

    // ── Owner Business form ───────────────────────────────────
    $('#fOwner').on('submit', function (e) {
        e.preventDefault();
        doLogin({
            btnId: 'btnOwner', txtId: 'txtOwner', btnLabel: 'Log Masuk',
            errId: 'errOwner', okId: 'okOwner',
            data: {
                registration_no: $('#oReg').val(),
                password:        $('#oPw').val(),
                remember:        $('#oRem').is(':checked'),
                login_type:      'owner_business'
            }
        });
    });

    // ── Tester form ───────────────────────────────────────────
    $('#fTester').on('submit', function (e) {
        e.preventDefault();
        doLogin({
            btnId: 'btnTester', txtId: 'txtTester', btnLabel: 'Log Masuk sebagai Tester',
            errId: 'errTester', okId: 'okTester',
            data: { email: $('#tEmail').val(), password: $('#tPw').val() }
        });
    });

    // ── Enter key support ─────────────────────────────────────
    $('#oReg, #oPw').on('keypress',   function (e) { if (e.which === 13) $('#fOwner').submit(); });
    $('#tEmail, #tPw').on('keypress', function (e) { if (e.which === 13) $('#fTester').submit(); });
});
</script>
@endpush