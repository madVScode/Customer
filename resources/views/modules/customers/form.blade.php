{{-- ============================================================ --}}
{{-- resources/views/modules/customers/form.blade.php            --}}
{{-- View: Tambah Customer Baru (Travelers dialih ke Bookings)    --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Add New Customer')
@section('content')

<style>
.cf-tabs {
    display: flex; gap: .4rem; margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--border-color); padding-bottom: 0;
}
.cf-tab-btn {
    padding: .6rem 1.2rem; border: none; background: none; cursor: pointer;
    font-weight: 700; font-size: .85rem; color: var(--text-muted);
    border-bottom: 3px solid transparent; margin-bottom: -2px;
    transition: all .2s; font-family: inherit;
    display: flex; align-items: center; gap: .4rem;
}
.cf-tab-btn.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }
.cf-tab-content { display: none; }
.cf-tab-content.active { display: block; }

.auto-detect-row { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
.auto-chip {
    padding: .65rem .9rem; background: #f0fff4; border: 1.5px solid #9ae6b4;
    border-radius: 8px; font-size: .88rem; color: #276749; font-weight: 600;
    min-height: 44px; display: flex; align-items: center; gap: .4rem;
}
.auto-chip.empty {
    background: var(--input-readonly-bg); border-color: var(--border-color);
    color: var(--text-muted); font-weight: 400;
}
.auto-chip-label {
    font-size: .72rem; font-weight: 700; color: var(--text-muted);
    margin-bottom: .35rem; display: flex; align-items: center; gap: .3rem;
}
.auto-badge {
    font-size: .65rem; background: #c6f6d5; color: #276749;
    padding: 1px 6px; border-radius: 10px; font-weight: 700;
}
.section-divider {
    display: flex; align-items: center; gap: .75rem; margin: 1.5rem 0 1.1rem;
}
.section-divider span {
    font-size: .75rem; font-weight: 800; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .07em; white-space: nowrap;
}
.section-divider::before, .section-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--border-color);
}
.passport-section {
    margin-top: .25rem; padding: 1.35rem;
    border: 1.5px solid var(--border-color); border-radius: 12px;
    background: var(--input-readonly-bg);
}
</style>

<div id="addCustomerView">
    <h1 class="page-title">👥 Add New Customer</h1>
    <div class="content-card">
        <form id="customerFormMain">
            @csrf

            {{-- ── Tab Navigation ── --}}
            <div class="cf-tabs">
                <button type="button" class="cf-tab-btn active" onclick="cfTab(this,'cfTabBasic')">
                    👤 Maklumat Asas
                </button>
                <button type="button" class="cf-tab-btn" onclick="cfTab(this,'cfTabPassport')">
                    🛂 Pasport
                    <span id="ppBadge" style="display:none;background:#c6f6d5;color:#276749;
                        font-size:.65rem;padding:1px 6px;border-radius:10px;">Ada</span>
                </button>
            </div>

            {{-- ════ TAB 1 — MAKLUMAT ASAS ════ --}}
            <div id="cfTabBasic" class="cf-tab-content active">

                <div class="section-divider"><span>IC & Auto-Detect</span></div>
                <div class="form-grid" style="margin-bottom:.85rem;">
                    <div class="form-group">
                        <label class="form-label">IC Number <span style="color:#e53e3e;">*</span></label>
                        <input id="icNumberMain" name="ic" type="text"
                               placeholder="950101-01-1234" class="form-input"
                               maxlength="14" required oninput="mainICInput(this)">
                        <small class="text-muted">Format: YYMMDD-PB-XXXX</small>
                    </div>
                    <div></div>
                </div>
                <div class="auto-detect-row" style="margin-bottom:1.25rem;">
                    <div>
                        <div class="auto-chip-label">Tarikh Lahir <span class="auto-badge">⚡ Auto dari IC</span></div>
                        <div class="auto-chip empty" id="mainDobChip">Masukkan IC untuk auto-kira</div>
                        <input type="hidden" id="dobMain" name="dob">
                    </div>
                    <div>
                        <div class="auto-chip-label">Jantina <span class="auto-badge">⚡ Auto dari IC</span></div>
                        <div class="auto-chip empty" id="mainGenderChip">Masukkan IC untuk auto-kesan</div>
                        <input type="hidden" id="genderMain" name="gender">
                    </div>
                </div>

                <div class="section-divider"><span>Maklumat Peribadi</span></div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name <span style="color:#e53e3e;">*</span></label>
                        <input name="name" type="text" class="form-input" required placeholder="Nama penuh mengikut IC">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span style="color:#e53e3e;">*</span></label>
                        <input name="email" type="email" class="form-input" required placeholder="customer@email.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone <span style="color:#e53e3e;">*</span></label>
                        <input name="phone" type="tel" class="form-input" required placeholder="0123456789">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company</label>
                        <select id="customerCompanySelect" name="company_id" class="form-select">
                            <option value="">-- Select Company --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="section-divider"><span>Alamat</span></div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">State <span style="color:#e53e3e;">*</span></label>
                        <select id="selectStateMain" name="state_id" class="form-select" required>
                            <option value="">Select State</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Postcode <span style="color:#e53e3e;">*</span></label>
                        <input name="postcode" type="text" maxlength="5" class="form-input" required placeholder="47500">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Address <span style="color:#e53e3e;">*</span></label>
                        <input name="address_line" type="text" class="form-input" required placeholder="No. rumah, jalan, kawasan">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country of Birth</label>
                        <select name="country_of_birth" id="addCountryOfBirth" class="form-select">
                            <option value="">-- Select Country --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ════ TAB 2 — PASPORT ════ --}}
            <div id="cfTabPassport" class="cf-tab-content">
                <div class="passport-section">
                    <div style="font-size:.95rem;font-weight:700;color:var(--text-primary);
                                margin-bottom:1.1rem;display:flex;align-items:center;gap:.5rem;">
                        🛂 Maklumat Pasport
                        <span style="font-size:.78rem;font-weight:400;color:var(--text-muted);">(Pilihan)</span>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Passport No.</label>
                            <input name="passport_no" id="mainPassportNo" type="text"
                                   placeholder="A12345678" class="form-input"
                                   oninput="checkPassportBadge()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nationality (English)</label>
                            <select name="pp_warganegara" id="addPpWarganegara" class="form-select">
                                <option value="">-- Select Nationality --</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->nationality }}">
                                        {{ $country->nationality }} ({{ $country->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth (Passport)</label>
                            <input name="pp_tarikh_lahir" type="date" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender (Passport)</label>
                            <select name="pp_gender" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="M">Male / Lelaki</option>
                                <option value="F">Female / Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date Issued</label>
                            <input name="pp_date_issued" type="date" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date Expired</label>
                            <input name="pp_date_expired" type="date" class="form-input">
                        </div>
                        <div class="form-group" style="grid-column:1/-1;">
                            <label class="form-label">Issuing Office</label>
                            <input name="pp_issuing_office" type="text"
                                   placeholder="Jabatan Imigresen Malaysia, Kuala Lumpur"
                                   class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align:center;margin-top:2rem;padding-top:1.5rem;
                        border-top:2px solid var(--border-color);
                        display:flex;justify-content:center;gap:.75rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-success btn-lg">💾 Save Customer</button>
                <button type="reset" class="btn btn-warning btn-lg" onclick="resetCustForm()">🔄 Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    window.cfTab = function (btn, tabId) {
        document.querySelectorAll('.cf-tab-btn').forEach(function (b) { b.classList.remove('active'); });
        document.querySelectorAll('.cf-tab-content').forEach(function (c) { c.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    };

    window.checkPassportBadge = function () {
        var val = (document.getElementById('mainPassportNo').value || '').trim();
        document.getElementById('ppBadge').style.display = val ? 'inline-block' : 'none';
    };

    window.mainICInput = function (input) {
        var raw = formatIC(input);
        updateICChips(raw, 'mainDobChip', 'mainGenderChip', 'dobMain', 'genderMain');
    };

    function formatIC(input) {
        var raw = input.value.replace(/\D/g, '').slice(0, 12);
        var fmt = raw.slice(0, 6);
        if (raw.length > 6) fmt += '-' + raw.slice(6, 8);
        if (raw.length > 8) fmt += '-' + raw.slice(8);
        input.value = fmt;
        return raw;
    }

    function updateICChips(raw, dobChipId, genderChipId, dobHiddenId, genderHiddenId) {
        var dobChip    = document.getElementById(dobChipId);
        var genderChip = document.getElementById(genderChipId);
        var months     = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];

        if (raw.length >= 6) {
            var yy = parseInt(raw.slice(0, 2));
            var mm = parseInt(raw.slice(2, 4));
            var dd = parseInt(raw.slice(4, 6));
            var yr = yy > (new Date().getFullYear() % 100) ? 1900 + yy : 2000 + yy;
            if (mm >= 1 && mm <= 12 && dd >= 1 && dd <= 31) {
                var dobStr = yr + '-' + String(mm).padStart(2,'0') + '-' + String(dd).padStart(2,'0');
                dobChip.textContent = '🎂 ' + dd + ' ' + months[mm-1] + ' ' + yr;
                dobChip.classList.remove('empty');
                if (dobHiddenId) document.getElementById(dobHiddenId).value = dobStr;
            }
        } else {
            dobChip.textContent = 'Masukkan IC untuk auto-kira';
            dobChip.classList.add('empty');
            if (dobHiddenId) document.getElementById(dobHiddenId).value = '';
        }

        if (raw.length === 12) {
            var last   = parseInt(raw.slice(-1));
            var isMale = (last % 2 !== 0);
            genderChip.textContent = isMale ? '♂ Lelaki' : '♀ Perempuan';
            genderChip.classList.remove('empty');
            if (genderHiddenId) document.getElementById(genderHiddenId).value = isMale ? 'male' : 'female';
        } else {
            genderChip.textContent = 'Masukkan IC untuk auto-kesan';
            genderChip.classList.add('empty');
            if (genderHiddenId) document.getElementById(genderHiddenId).value = '';
        }
    }

    // Expose updateICChips untuk digunakan semula jika perlu
    window._updateICChips = updateICChips;
    window._formatIC      = formatIC;

    window.resetCustForm = function () {
        ['mainDobChip','mainGenderChip'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.classList.add('empty'); el.textContent = 'Masukkan IC...'; }
        });
        ['dobMain','genderMain'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        document.getElementById('ppBadge').style.display = 'none';
        cfTab(document.querySelector('.cf-tab-btn'), 'cfTabBasic');
    };
})();
</script>
@endpush