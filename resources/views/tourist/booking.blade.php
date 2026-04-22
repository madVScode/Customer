<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tempahan — {{ $trip->location }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:#0f2027; --teal:#00d4aa; --teal-dark:#00b894; --mid:#2c5364;
            --bg:#f0f4f8; --text:#2d3748; --muted:#718096; --border:#e2e8f0;
            --radius-sm:8px; --radius-md:12px; --radius-lg:16px;
            --shadow-sm:0 2px 8px rgba(0,0,0,.07); --shadow-md:0 4px 18px rgba(0,0,0,.1);
        }
        *{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent;}
        body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);}

        /* ── Header ── */
        .header{background:linear-gradient(135deg,var(--navy),var(--mid));color:white;
            padding:.85rem 1.25rem;display:flex;justify-content:space-between;align-items:center;
            position:sticky;top:0;z-index:100;box-shadow:0 2px 18px rgba(0,0,0,.28);}
        .header-brand{font-size:1.2rem;font-weight:800;}
        .header-brand span{color:var(--teal);}
        .back-link{display:inline-flex;align-items:center;gap:.3rem;color:rgba(255,255,255,.82);
            text-decoration:none;font-size:.82rem;font-weight:600;padding:.35rem .8rem;
            border-radius:var(--radius-sm);background:rgba(255,255,255,.1);
            border:1px solid rgba(255,255,255,.2);transition:all .2s;}
        .back-link:hover{background:rgba(255,255,255,.18);color:white;}

        /* ── Progress ── */
        .progress-bar{background:white;padding:.9rem 1.25rem;border-bottom:1px solid var(--border);}
        .progress-steps{display:flex;align-items:center;justify-content:center;max-width:400px;margin:0 auto;}
        .step{display:flex;flex-direction:column;align-items:center;gap:.25rem;flex:1;}
        .step-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;
            justify-content:center;font-size:.75rem;font-weight:800;}
        .step-dot.active{background:linear-gradient(135deg,var(--navy),var(--mid));color:white;
            box-shadow:0 0 0 4px rgba(0,212,170,.2);}
        .step-dot.pending{background:var(--border);color:var(--muted);}
        .step-label{font-size:.62rem;font-weight:600;color:var(--muted);text-align:center;white-space:nowrap;}
        .step-dot.active+.step-label{color:var(--text);}
        .step-line{flex:1;height:2px;background:var(--border);position:relative;top:-8px;}

        .container{max-width:820px;margin:0 auto;padding:1.5rem 1.25rem;}

        /* ── Trip Summary ── */
        .trip-summary{background:linear-gradient(135deg,var(--navy),var(--mid));color:white;
            border-radius:var(--radius-lg);padding:1.35rem;margin-bottom:1.5rem;
            display:flex;gap:1.1rem;align-items:flex-start;flex-wrap:wrap;}
        .trip-summary img{width:100px;height:75px;object-fit:cover;border-radius:var(--radius-sm);
            border:2px solid rgba(255,255,255,.25);flex-shrink:0;}
        .trip-summary .img-ph{width:100px;height:75px;background:rgba(255,255,255,.1);
            border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;
            font-size:2.5rem;flex-shrink:0;}
        .trip-summary h2{font-size:1.2rem;font-weight:800;margin-bottom:.3rem;}
        .trip-summary p{color:rgba(255,255,255,.75);font-size:.83rem;margin-bottom:.2rem;}
        .price-chips{display:flex;gap:1rem;flex-wrap:wrap;margin-top:.6rem;}
        .price-chip span{font-size:.65rem;color:rgba(255,255,255,.58);display:block;}
        .price-chip strong{font-size:.9rem;}

        /* ── Form Card ── */
        .form-card{background:white;border-radius:var(--radius-lg);padding:1.35rem;
            box-shadow:var(--shadow-sm);margin-bottom:1.25rem;
            border:1px solid var(--border);}
        .form-card-title{font-size:.98rem;font-weight:700;margin-bottom:1.1rem;
            padding-bottom:.7rem;border-bottom:2px solid var(--bg);
            display:flex;align-items:center;justify-content:space-between;}
        .form-card-title-left{display:flex;align-items:center;gap:.5rem;}

        /* ── Returning user banner ── */
        .returning-banner{background:linear-gradient(135deg,#f0fff4,#e6fffa);
            border:1.5px solid #9ae6b4;border-radius:var(--radius-md);
            padding:.9rem 1.1rem;margin-bottom:1rem;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;}
        .returning-banner-text{font-size:.83rem;color:#276749;font-weight:600;
            display:flex;align-items:center;gap:.5rem;}
        .btn-edit-toggle{padding:.4rem 1rem;background:white;color:#276749;
            border:1.5px solid #9ae6b4;border-radius:var(--radius-sm);
            font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;
            transition:all .2s;white-space:nowrap;}
        .btn-edit-toggle:hover{background:#9ae6b4;color:#276749;}
        .btn-edit-toggle.editing{background:#fed7d7;border-color:#fc8181;color:#c53030;}
        .btn-edit-toggle.editing:hover{background:#fc8181;color:white;}

        /* ── Form layout ── */
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;}
        .form-group{display:flex;flex-direction:column;gap:.3rem;}
        .form-group.full{grid-column:1/-1;}
        .form-label{font-size:.8rem;font-weight:600;color:#4a5568;}
        .form-label .req{color:#e53e3e;}
        .form-input{padding:.7rem .9rem;border:2px solid var(--border);border-radius:var(--radius-sm);
            font-family:inherit;font-size:.9rem;color:var(--text);transition:all .2s;outline:none;min-height:44px;}
        .form-input:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,212,170,.1);}
        .form-input.readonly-style{background:#f9fafb;color:#4a5568;cursor:default;border-color:#e2e8f0;}
        .form-input.auto-filled{background:#f0fff4;border-color:#9ae6b4;color:#276749;}

        /* ── Passport tab system ── */
        .tab-nav{display:flex;gap:.4rem;margin-bottom:1.1rem;
            border-bottom:2px solid var(--bg);padding-bottom:0;}
        .tab-btn{padding:.5rem 1rem;border:none;background:none;cursor:pointer;
            font-weight:700;font-size:.82rem;border-bottom:3px solid transparent;
            margin-bottom:-2px;transition:all .2s;font-family:inherit;color:var(--muted);}
        .tab-btn.active{color:var(--teal);border-bottom-color:var(--teal);}
        .tab-content{display:none;}
        .tab-content.active{display:block;}

        /* ── Pax counter ── */
        .pax-row { display:flex; flex-direction:column; gap:.65rem; }
.pax-box {
    background:var(--bg); border:2px solid var(--border); border-radius:var(--radius-md);
    padding:.85rem 1rem; transition:border-color .2s;
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
}
.pax-box:focus-within { border-color:var(--teal); }
.pax-box-left { display:flex; flex-direction:column; gap:.1rem; }
.pax-label { font-size:.85rem; color:var(--text); font-weight:700; }
.pax-price { font-size:.8rem; color:var(--teal); font-weight:600; }
.pax-controls { display:flex; align-items:center; gap:.75rem; flex-shrink:0; }
.pax-btn {
    width:38px; height:38px;
    background:linear-gradient(135deg,var(--navy),var(--mid));
    color:white; border:none; border-radius:50%; font-size:1.2rem; cursor:pointer;
    display:flex; align-items:center; justify-content:center; touch-action:manipulation;
    flex-shrink:0;
}
.pax-btn:active { transform:scale(.92); }
.pax-num { font-size:1.25rem; font-weight:800; min-width:32px; text-align:center; }

@media(min-width:500px) {
    .pax-row { flex-direction:row; }
    .pax-box {
        flex:1; flex-direction:column; align-items:center;
        justify-content:center; text-align:center; padding:.9rem .75rem;
    }
    .pax-box-left { align-items:center; }
}

        /* ── Extra travelers ── */
        .extra-section{margin-top:1rem;}
        .btn-add-traveler{display:flex;align-items:center;gap:.5rem;padding:.65rem 1.1rem;
            background:#ebf8ff;border:1.5px dashed #63b3ed;border-radius:var(--radius-md);
            color:#2b6cb0;font-size:.85rem;font-weight:700;cursor:pointer;
            font-family:inherit;width:100%;justify-content:center;transition:all .2s;}
        .btn-add-traveler:hover{background:#bee3f8;border-style:solid;}

        .extra-traveler-block{background:#f7fafc;border:1px solid var(--border);
            border-radius:var(--radius-md);padding:1rem;margin-top:.75rem;position:relative;}
        .extra-traveler-title{font-size:.82rem;font-weight:700;color:var(--mid);
            margin-bottom:.85rem;display:flex;align-items:center;justify-content:space-between;}
        .btn-remove-traveler{background:#fed7d7;border:none;color:#c53030;
            border-radius:6px;padding:3px 10px;font-size:.75rem;font-weight:700;
            cursor:pointer;font-family:inherit;transition:all .2s;}
        .btn-remove-traveler:hover{background:#fc8181;color:white;}

        /* ── Auto-detect chips ── */
        .auto-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;}
        .auto-chip{padding:.7rem .9rem;background:#f0fff4;border:1.5px solid #9ae6b4;
            border-radius:var(--radius-sm);font-size:.85rem;color:#276749;font-weight:600;
            min-height:44px;display:flex;align-items:center;gap:.4rem;}
        .auto-chip-empty{color:var(--muted);background:#f9fafb;border-color:var(--border);}

        /* ── Total ── */
        .total-box{background:linear-gradient(135deg,var(--navy),var(--mid));color:white;
    border-radius:var(--radius-md);padding:1.25rem;
    display:flex;justify-content:space-between;align-items:center;
    flex-wrap:wrap;gap:.75rem;}
@media(max-width:480px){
    .total-box{flex-direction:column;align-items:stretch;text-align:center;}
    .total-box > div:last-child{display:flex;justify-content:center;}
    .total-amount{font-size:1.6rem;}
}
        .total-amount{font-size:1.9rem;font-weight:800;color:var(--teal);}
        .total-label{font-size:.72rem;color:rgba(255,255,255,.6);margin-bottom:.2rem;}
        .breakdown-row{display:flex;justify-content:space-between;padding:.25rem 0;
            border-bottom:1px dashed var(--border);font-size:.86rem;color:var(--muted);}
        .breakdown-row:last-child{border-bottom:none;}

        /* ── Submit ── */
        .btn-submit{width:100%;padding:1rem;background:linear-gradient(135deg,var(--teal-dark),var(--teal));
            color:var(--navy);border:none;border-radius:var(--radius-md);font-family:inherit;
            font-size:1rem;font-weight:800;cursor:pointer;margin-top:.9rem;
            box-shadow:0 4px 15px rgba(0,212,170,.3);min-height:52px;transition:all .2s;}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(0,212,170,.4);}
        .btn-submit:disabled{opacity:.6;cursor:not-allowed;transform:none;box-shadow:none;}

        .notice-text{text-align:center;font-size:.77rem;color:var(--muted);
            margin-top:.65rem;line-height:1.6;}
        .alert-error{background:#fff5f5;border:1px solid #fc8181;border-radius:var(--radius-sm);
            padding:.75rem 1rem;margin-bottom:1.1rem;color:#c53030;font-size:.83rem;}
        .info-box{background:#ebf8ff;border:1px solid #90cdf4;border-radius:var(--radius-md);
            padding:.9rem 1.1rem;margin-bottom:1.25rem;font-size:.83rem;color:#2b6cb0;line-height:1.7;}

        /* ── Section divider ── */
        .section-divider{display:flex;align-items:center;gap:.75rem;margin:.5rem 0 1rem;}
        .section-divider span{font-size:.75rem;font-weight:700;color:var(--muted);
            text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;}
        .section-divider::before,.section-divider::after{content:'';flex:1;height:1px;background:var(--border);}

        @media(max-width:640px){
            .form-grid,.auto-row{grid-template-columns:1fr;}
            .form-group.full{grid-column:1;}
            .pax-row{gap:.5rem;}
            .pax-box{padding:.75rem .5rem;}
            .trip-summary img,.trip-summary .img-ph{width:80px;height:60px;font-size:2rem;}
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-brand">✈️ Travel<span>Pro</span></div>
    <a href="{{ route('tourist.dashboard') }}" class="back-link">← Kembali</a>
</header>

<div class="progress-bar">
    <div class="progress-steps">
        <div class="step">
            <div class="step-dot active">1</div>
            <div class="step-label">Tempahan</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-dot pending">2</div>
            <div class="step-label">Lulusan</div>
        </div>
        </div>
    </div>
</div>

<div class="container">

    @if($errors->any())
    <div class="alert-error">
        <strong>⚠️ Sila betulkan ralat berikut:</strong>
        <ul style="margin-top:.4rem;padding-left:1.1rem;">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="info-box">
        📋 Isi maklumat dan bilangan pelancong. Booking perlu diluluskan admin, kemudian resit akan dihantar oleh admin melalui  <strong>(WhatsApp)</strong>.
    </div>

    <!-- Trip Summary -->
    <div class="trip-summary">
        @if($trip->pic1_url)
            <img src="{{ $trip->pic1_url }}" alt="{{ $trip->location }}">
        @else
            <div class="img-ph">✈️</div>
        @endif
        <div style="flex:1;min-width:0;">
            <h2>{{ $trip->location }}</h2>
            <p>📍 {{ $trip->country }}</p>
            <p>📅 {{ \Carbon\Carbon::parse($trip->date_from)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($trip->date_to)->format('d/m/Y') }}</p>
            <p>⏱️ {{ $trip->duration ?? '-' }} hari &nbsp;|&nbsp; 🏢 {{ $trip->company->name ?? '-' }}</p>
            <div class="price-chips">
                <div class="price-chip"><span>Dewasa</span><strong style="color:var(--teal);">RM {{ number_format($trip->price_adult,2) }}</strong></div>
                <div class="price-chip"><span>Kanak</span><strong style="color:#74b9ff;">RM {{ number_format($trip->price_child,2) }}</strong></div>
                <div class="price-chip"><span>Bayi</span><strong style="color:#fdcb6e;">RM {{ number_format($trip->price_baby,2) }}</strong></div>
                <div class="price-chip"><span>Deposit</span><strong style="color:#a29bfe;">RM {{ number_format($trip->deposit,2) }}</strong></div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('tourist.booking.create') }}" id="bookingForm">
        @csrf
        <input type="hidden" name="trip_id" value="{{ $trip->id }}">
        <input type="hidden" name="total_amount" id="totalAmountInput" value="0">

        {{-- ══════════════════════════════════════════════════════════
             SECTION 1 — MAKLUMAT KETUA PELANCONG
             State A: Returning user (customer exists) → pre-filled
             State B: First-time user → kosong, perlu isi
        ══════════════════════════════════════════════════════════ --}}
        <div class="form-card">
            <div class="form-card-title">
                <div class="form-card-title-left">
                    👤 Maklumat Ketua Pelancong
                    @if(!$isFirstTime)
                        <span style="background:#c6f6d5;color:#276749;font-size:.68rem;font-weight:700;
                                     padding:2px 8px;border-radius:20px;">✓ Data Sedia Ada</span>
                    @else
                        <span style="background:#fef3c7;color:#92400e;font-size:.68rem;font-weight:700;
                                     padding:2px 8px;border-radius:20px;">✦ Baru</span>
                    @endif
                </div>
                {{-- Edit toggle (hanya untuk returning user) --}}
                @if(!$isFirstTime)
                <button type="button" id="btnEditToggle" class="btn-edit-toggle"
                        onclick="toggleEdit()">✏️ Edit Maklumat</button>
                @endif
            </div>

            {{-- Returning user banner --}}
            @if(!$isFirstTime)
            <div class="returning-banner" id="returningBanner">
                <div class="returning-banner-text">
                    ✅ Maklumat anda telah diisi secara automatik. Semak dan teruskan.
                </div>
                <span style="font-size:.75rem;color:#276749;opacity:.7;">
                    Tempahan ke-{{ $customer->bookings()->count() + 1 }}
                </span>
            </div>
            @endif

            {{-- Tab nav (Maklumat Asas / Pasport) --}}
            <div class="tab-nav">
                <button type="button" class="tab-btn active" onclick="switchTab(this,'tabBasic')">
                    👤 Maklumat Asas
                </button>
                <button type="button" class="tab-btn" onclick="switchTab(this,'tabPassport')">
                    🛂 Pasport
                    @if($customer && $customer->passport_no)
                        <span style="font-size:.65rem;background:#bee3f8;color:#2b6cb0;
                                     padding:1px 6px;border-radius:10px;margin-left:.3rem;">Ada</span>
                    @endif
                </button>
            </div>

            {{-- ── Tab 1: Maklumat Asas ── --}}
            <div id="tabBasic" class="tab-content active">
                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label">Nama Penuh <span class="req">*</span></label>
                        <input type="text" name="tourist_name"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               id="inputName"
                               value="{{ old('tourist_name', $customer->name ?? $user->name) }}"
                               {{ !$isFirstTime ? 'readonly' : '' }} required
                               placeholder="Nama penuh mengikut IC">
                    </div>

                    <div class="form-group">
                        <label class="form-label">No. Telefon <span class="req">*</span></label>
                        <input type="tel" name="tourist_phone"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               id="inputPhone"
                               value="{{ old('tourist_phone', $customer->phone ?? $user->phone) }}"
                               {{ !$isFirstTime ? 'readonly' : '' }} required
                               placeholder="0123456789">
                    </div>

                    <div class="form-group">
                        <label class="form-label">No. IC (12 digit) <span class="req">*</span></label>
                        <input type="text" name="tourist_ic"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               id="icInput"
                               placeholder="000000-00-0000" maxlength="14"
                               value="{{ old('tourist_ic', $customer ? implode('-', array_filter([substr($customer->ic,0,6), substr($customer->ic,6,2), substr($customer->ic,8)])) : '') }}"
                               {{ !$isFirstTime ? 'readonly' : '' }} required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Emel</label>
                        <input type="email" class="form-input readonly-style"
                               value="{{ $user->email }}" readonly>
                    </div>

                    {{-- Auto-detect: DOB & Gender dari IC --}}
                    <div class="form-group">
                        <label class="form-label">Tarikh Lahir
                            <span style="font-size:.68rem;color:var(--teal);font-weight:600;">⚡ Auto dari IC</span>
                        </label>
                        <div class="auto-chip {{ ($customer && $customer->dob) ? '' : 'auto-chip-empty' }}" id="dobDisplay">
                            @if($customer && $customer->dob)
                                🎂 {{ \Carbon\Carbon::parse($customer->dob)->format('d/m/Y') }}
                            @else
                                <span style="color:var(--muted);">Masukkan IC untuk auto-kira</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jantina
                            <span style="font-size:.68rem;color:var(--teal);font-weight:600;">⚡ Auto dari IC</span>
                        </label>
                        <div class="auto-chip {{ ($customer && $customer->gender) ? '' : 'auto-chip-empty' }}" id="genderDisplay">
                            @if($customer && $customer->gender)
                                {{ $customer->gender === 'male' ? '♂ Lelaki' : '♀ Perempuan' }}
                            @else
                                <span style="color:var(--muted);">Masukkan IC untuk auto-kesan</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Negeri <span class="req">*</span></label>
                        <select name="state_id" id="inputState"
    class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
    {{ !$isFirstTime ? 'data-disabled=true' : '' }} required>
                            <option value="">-- Pilih Negeri --</option>
                            @foreach(\App\Models\State::orderBy('name')->get() as $state)
                                <option value="{{ $state->id }}"
                                    {{ old('state_id', $customer->state_id ?? '') == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Hidden input untuk disabled select --}}
                        @if(!$isFirstTime)
                        <input type="hidden" name="state_id" value="{{ $customer->state_id }}">
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">Poskod <span class="req">*</span></label>
                        <input type="text" name="postcode"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               id="inputPostcode"
                               value="{{ old('postcode', $address->postcode ?? '') }}"
                               {{ !$isFirstTime ? 'readonly' : '' }} required
                               placeholder="47500" maxlength="5">
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Alamat <span class="req">*</span></label>
                        <input type="text" name="address_line"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               id="inputAddress"
                               value="{{ old('address_line', $address->address_line ?? '') }}"
                               {{ !$isFirstTime ? 'readonly' : '' }} required
                               placeholder="No. rumah, jalan, kawasan">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Negara Kelahiran</label>
                        <select name="country_of_birth" id="inputCountry"
                                class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                                {{ !$isFirstTime ? 'disabled' : '' }}>
                            <option value="">-- Pilih --</option>
                            @foreach(\App\Models\Country::orderBy('name')->get() as $c)
                                <option value="{{ $c->name }}"
                                    {{ old('country_of_birth', $customer->country_of_birth ?? '') == $c->name ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @if(!$isFirstTime)
                        <input type="hidden" name="country_of_birth" value="{{ $customer->country_of_birth }}">
                        @endif
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Nota / Permintaan Khas</label>
                        <textarea name="notes" class="form-input" rows="2"
                                  placeholder="Contoh: Perlukan kerusi roda, vegetarian, dll.">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── Tab 2: Pasport ── --}}
            <div id="tabPassport" class="tab-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">No. Pasport</label>
                        <input type="text" name="passport_no"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               id="inputPassportNo"
                               value="{{ old('passport_no', $customer->passport_no ?? '') }}"
                               {{ !$isFirstTime ? 'readonly' : '' }}
                               placeholder="A12345678">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warganegara (Pasport)</label>
                        <select name="pp_warganegara" id="inputPpWarga"
                                class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                                {{ !$isFirstTime ? 'disabled' : '' }}>
                            <option value="">-- Pilih --</option>
                            @foreach(\App\Models\Country::orderBy('name')->get() as $c)
                                <option value="{{ $c->nationality }}"
                                    {{ old('pp_warganegara', $customer->pp_warganegara ?? 'Malaysian') == $c->nationality ? 'selected' : '' }}>
                                    {{ $c->nationality }} ({{ $c->name }})
                                </option>
                            @endforeach
                        </select>
                        @if(!$isFirstTime)
                        <input type="hidden" name="pp_warganegara" value="{{ $customer->pp_warganegara }}">
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tarikh Lahir (Pasport)</label>
                        <input type="date" name="pp_tarikh_lahir"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               id="inputPpDob"
                               value="{{ old('pp_tarikh_lahir', $customer && $customer->pp_tarikh_lahir ? $customer->pp_tarikh_lahir->format('Y-m-d') : '') }}"
                               {{ !$isFirstTime ? 'readonly' : '' }}>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jantina (Pasport)</label>
                        <select name="pp_gender" id="inputPpGender"
                                class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                                {{ !$isFirstTime ? 'disabled' : '' }}>
                            <option value="">-- Pilih --</option>
                            <option value="M" {{ old('pp_gender', $customer->pp_gender ?? '') == 'M' ? 'selected' : '' }}>Lelaki / Male</option>
                            <option value="F" {{ old('pp_gender', $customer->pp_gender ?? '') == 'F' ? 'selected' : '' }}>Perempuan / Female</option>
                        </select>
                        @if(!$isFirstTime)
                        <input type="hidden" name="pp_gender" value="{{ $customer->pp_gender }}">
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tarikh Dikeluarkan</label>
                        <input type="date" name="pp_date_issued"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               value="{{ old('pp_date_issued', $customer && $customer->pp_date_issued ? $customer->pp_date_issued->format('Y-m-d') : '') }}"
                               {{ !$isFirstTime ? 'readonly' : '' }}>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tarikh Tamat</label>
                        <input type="date" name="pp_date_expired"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               value="{{ old('pp_date_expired', $customer && $customer->pp_date_expired ? $customer->pp_date_expired->format('Y-m-d') : '') }}"
                               {{ !$isFirstTime ? 'readonly' : '' }}>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Pejabat Pengeluaran</label>
                        <input type="text" name="pp_issuing_office"
                               class="form-input {{ !$isFirstTime ? 'readonly-style' : '' }}"
                               value="{{ old('pp_issuing_office', $customer->pp_issuing_office ?? '') }}"
                               {{ !$isFirstTime ? 'readonly' : '' }}
                               placeholder="Jabatan Imigresen Malaysia, Kuala Lumpur">
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SECTION 2 — BILANGAN PELANCONG
        ══════════════════════════════════════════════════════════ --}}
        <div class="form-card">
            <div class="form-card-title">
                <div class="form-card-title-left">👥 Bilangan Pelancong</div>
            </div>
            <div class="pax-row">
    <div class="pax-box">
        <div class="pax-box-left">
            <div class="pax-label">👨 Dewasa</div>
            <div class="pax-price">RM {{ number_format($trip->price_adult,2) }} / orang</div>
        </div>
        <div class="pax-controls">
            <button type="button" class="pax-btn" onclick="changePax('adult',-1)">−</button>
            <span class="pax-num" id="paxAdult">1</span>
            <button type="button" class="pax-btn" onclick="changePax('adult',1)">+</button>
        </div>
        <input type="hidden" name="pax_adult" id="paxAdultInput" value="1">
    </div>
    <div class="pax-box">
        <div class="pax-box-left">
            <div class="pax-label">👶 Kanak-kanak</div>
            <div class="pax-price">RM {{ number_format($trip->price_child,2) }} / orang</div>
        </div>
        <div class="pax-controls">
            <button type="button" class="pax-btn" onclick="changePax('child',-1)">−</button>
            <span class="pax-num" id="paxChild">0</span>
            <button type="button" class="pax-btn" onclick="changePax('child',1)">+</button>
        </div>
        <input type="hidden" name="pax_child" id="paxChildInput" value="0">
    </div>
    <div class="pax-box">
        <div class="pax-box-left">
            <div class="pax-label">🍼 Bayi</div>
            <div class="pax-price">RM {{ number_format($trip->price_baby,2) }} / orang</div>
        </div>
        <div class="pax-controls">
            <button type="button" class="pax-btn" onclick="changePax('baby',-1)">−</button>
            <span class="pax-num" id="paxBaby">0</span>
            <button type="button" class="pax-btn" onclick="changePax('baby',1)">+</button>
        </div>
        <input type="hidden" name="pax_baby" id="paxBabyInput" value="0">
    </div>
</div>

            {{-- ── Pelancong Tambahan ── --}}
            <div class="extra-section">
                <div class="section-divider">
                    <span>Pelancong Lain (Pilihan)</span>
                </div>
                <div id="extraTravelersList"></div>
                <button type="button" class="btn-add-traveler" onclick="addExtraTraveler()"
                        id="btnAddTraveler">
                    ➕ Tambah Pelancong Lain
                </button>
                <p style="font-size:.74rem;color:var(--muted);text-align:center;margin-top:.5rem;">
                    Tambah ahli keluarga / rakan yang turut serta dalam trip ini
                </p>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SECTION 3 — RINGKASAN BAYARAN
        ══════════════════════════════════════════════════════════ --}}
        <div class="form-card">
            <div class="form-card-title">
                <div class="form-card-title-left">💰 Ringkasan Bayaran</div>
            </div>
            <div id="breakdownList" style="margin-bottom:1rem;"></div>
            <div class="total-box">
                <div>
                    <div class="total-label">JUMLAH KESELURUHAN</div>
                    <div class="total-amount" id="totalDisplay">RM 0.00</div>
                    <div style="font-size:.8rem;color:rgba(255,255,255,.6);margin-top:.2rem;"
                         id="depositInfo"></div>
                </div>
                <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);
            border-radius:10px;padding:.7rem .9rem;text-align:center;min-width:130px;">
    <div style="font-size:.63rem;color:rgba(255,255,255,.5);text-transform:uppercase;
                letter-spacing:.06em;margin-bottom:.3rem;">Bayaran melalui</div>
    <div style="font-size:1rem;font-weight:800;color:#25D366;line-height:1.2;">
        💬 WhatsApp
    </div>
    <div style="font-size:.65rem;color:rgba(255,255,255,.4);margin-top:.35rem;line-height:1.4;">
        Admin akan<br>hubungi anda
    </div>
</div>
            </div>
            <button type="submit" class="btn-submit" id="submitBtn">
                🎉 Hantar Tempahan Sekarang
            </button>
            <p class="notice-text">
                📲 Notifikasi akan dihantar kepada admin · Tempahan perlu diluluskan dahulu
            </p>
        </div>

    </form>
</div>

<script>
// ── Prices & config ────────────────────────────────────────────────────────
var prA = {{ $trip->price_adult }};
var prC = {{ $trip->price_child }};
var prB = {{ $trip->price_baby }};
var prD = {{ $trip->deposit }};
var pax = { adult: 1, child: 0, baby: 0 };
var isFirstTime = {{ $isFirstTime ? 'true' : 'false' }};
var isEditing   = false;
var extraCount  = 0;

// ── Tab switching ──────────────────────────────────────────────────────────
function switchTab(btn, tabId) {
    document.querySelectorAll('.tab-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(function(c) {
        c.classList.remove('active');
    });
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

// ── Edit toggle (returning user) ───────────────────────────────────────────
function toggleEdit() {
    const btn = document.getElementById('btnEditToggle');
    const form = document.getElementById('bookingForm');
    const banner = document.getElementById('banner');

    btn.classList.toggle('editing');
    const isEditing = btn.classList.contains('editing');

    const inputs = form.querySelectorAll('input, select, textarea');
    const returningFields = [
        'tourist_name','tourist_phone','tourist_ic','state_id','postcode',
        'address_line','country_of_birth','passport_no','pp_warganegara',
        'pp_tarikh_lahir','pp_gender','pp_date_issued','pp_date_expired','pp_issuing_office'
    ];

    inputs.forEach(el => {
        if (returningFields.includes(el.name)) {
            if (isEditing) {
                if (el.hasAttribute('readonly')) el.removeAttribute('readonly');
                if (el.hasAttribute('disabled')) el.removeAttribute('disabled');
                el.classList.remove('readonly-style');
            } else {
                if (el.tagName === 'SELECT') el.setAttribute('disabled', true);
                else el.setAttribute('readonly', true);
                el.classList.add('readonly-style');
            }
        }
    });

    if (isEditing) {
        btn.textContent = '✕ Batal Edit';
        if (banner) banner.style.display = 'none';
    } else {
        btn.textContent = '✏️ Edit Maklumat';
        if (banner) banner.style.display = 'flex';
    }
}

// ── IC auto-detect (main) ─────────────────────────────────────────────────
document.getElementById('icInput').addEventListener('input', function() {
    var raw = this.value.replace(/\D/g, '').slice(0, 12);
    var fmt = raw.slice(0, 6);
    if (raw.length > 6) fmt += '-' + raw.slice(6, 8);
    if (raw.length > 8) fmt += '-' + raw.slice(8);
    this.value = fmt;
    updateICDerived(raw, 'dobDisplay', 'genderDisplay');
});

function updateICDerived(raw, dobId, genderId) {
    var dobEl    = document.getElementById(dobId);
    var genderEl = document.getElementById(genderId);
    if (!dobEl || !genderEl) return;

    if (raw.length >= 6) {
        var yy = parseInt(raw.slice(0, 2));
        var mm = parseInt(raw.slice(2, 4));
        var dd = parseInt(raw.slice(4, 6));
        var yr = yy > (new Date().getFullYear() % 100) ? 1900 + yy : 2000 + yy;
        if (mm >= 1 && mm <= 12 && dd >= 1 && dd <= 31) {
            var months = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];
            dobEl.innerHTML = '🎂 ' + dd + ' ' + months[mm-1] + ' ' + yr;
            dobEl.classList.remove('auto-chip-empty');
        }
    } else {
        dobEl.innerHTML = '<span style="color:var(--muted);">Masukkan IC untuk auto-kira</span>';
        dobEl.classList.add('auto-chip-empty');
    }

    if (raw.length === 12) {
        var last = parseInt(raw.slice(-1));
        var gender = (last % 2 === 0) ? '♀ Perempuan' : '♂ Lelaki';
        genderEl.innerHTML = gender;
        genderEl.classList.remove('auto-chip-empty');
    } else {
        genderEl.innerHTML = '<span style="color:var(--muted);">Masukkan IC untuk auto-kesan</span>';
        genderEl.classList.add('auto-chip-empty');
    }
}

// ── Pax counter ────────────────────────────────────────────────────────────
function changePax(t, d) {
    pax[t] = Math.max(t === 'adult' ? 1 : 0, pax[t] + d);
    document.getElementById('pax' + cap(t)).textContent = pax[t];
    document.getElementById('pax' + cap(t) + 'Input').value = pax[t];
    recalc();
}
function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function recalc() {
    var tot = pax.adult * prA + pax.child * prC + pax.baby * prB;
    var dep = prD * (pax.adult + pax.child);
    document.getElementById('totalDisplay').textContent = 'RM ' + tot.toFixed(2);
    document.getElementById('totalAmountInput').value = tot.toFixed(2);
    document.getElementById('depositInfo').textContent =
        'Deposit: RM ' + dep.toFixed(2) + '  |  Baki: RM ' + (tot - dep).toFixed(2);
    var bd = '';
    if (pax.adult > 0) bd += row(pax.adult + ' Dewasa × RM ' + prA.toFixed(2), 'RM ' + (pax.adult * prA).toFixed(2));
    if (pax.child > 0) bd += row(pax.child + ' Kanak × RM ' + prC.toFixed(2), 'RM ' + (pax.child * prC).toFixed(2));
    if (pax.baby > 0)  bd += row(pax.baby  + ' Bayi × RM '  + prB.toFixed(2), 'RM ' + (pax.baby  * prB).toFixed(2));
    document.getElementById('breakdownList').innerHTML = bd;
}
function row(l, r) {
    return '<div class="breakdown-row"><span>' + l + '</span><strong>' + r + '</strong></div>';
}

// ── Extra travelers ────────────────────────────────────────────────────────
function addExtraTraveler() {
    var i = extraCount++;
    var html =
        '<div class="extra-traveler-block" id="etBlock-' + i + '">' +
            '<div class="extra-traveler-title">' +
                '🧍 Pelancong ' + (i + 2) +
                ' <button type="button" class="btn-remove-traveler" onclick="removeExtraTraveler(' + i + ')">✕ Buang</button>' +
            '</div>' +
            '<div class="form-grid">' +
                '<div class="form-group">' +
                    '<label class="form-label">Nama Penuh <span class="req">*</span></label>' +
                    '<input type="text" name="extra_travelers[' + i + '][name]" class="form-input" required placeholder="Nama penuh">' +
                '</div>' +
                '<div class="form-group">' +
                    '<label class="form-label">No. IC <span class="req">*</span></label>' +
                    '<input type="text" name="extra_travelers[' + i + '][ic]" class="form-input et-ic" ' +
                        'data-dob="etDob-' + i + '" data-gender="etGender-' + i + '" ' +
                        'placeholder="000000-00-0000" maxlength="14" required oninput="etICInput(this)">' +
                '</div>' +
                '<div class="form-group">' +
                    '<label class="form-label">Tarikh Lahir <span style="font-size:.68rem;color:var(--teal);">⚡ Auto</span></label>' +
                    '<div class="auto-chip auto-chip-empty" id="etDob-' + i + '">' +
                        '<span style="color:var(--muted);">Auto dari IC</span>' +
                    '</div>' +
                '</div>' +
                '<div class="form-group">' +
                    '<label class="form-label">Jantina <span style="font-size:.68rem;color:var(--teal);">⚡ Auto</span></label>' +
                    '<div class="auto-chip auto-chip-empty" id="etGender-' + i + '">' +
                        '<span style="color:var(--muted);">Auto dari IC</span>' +
                    '</div>' +
                '</div>' +
                '<div class="form-group">' +
                    '<label class="form-label">Jenis Pelancong</label>' +
                    '<select name="extra_travelers[' + i + '][type]" class="form-input">' +
                        '<option value="adult">👨 Dewasa</option>' +
                        '<option value="child">👶 Kanak-kanak</option>' +
                        '<option value="baby">🍼 Bayi</option>' +
                    '</select>' +
                '</div>' +
                '<div class="form-group">' +
                    '<label class="form-label">No. Pasport (Pilihan)</label>' +
                    '<input type="text" name="extra_travelers[' + i + '][passport_no]" class="form-input" placeholder="A12345678">' +
                '</div>' +
            '</div>' +
        '</div>';

    document.getElementById('extraTravelersList').insertAdjacentHTML('beforeend', html);
}

function removeExtraTraveler(i) {
    var el = document.getElementById('etBlock-' + i);
    if (el) el.remove();
}

// IC auto-detect untuk extra travelers
function etICInput(input) {
    var raw = input.value.replace(/\D/g, '').slice(0, 12);
    var fmt = raw.slice(0, 6);
    if (raw.length > 6) fmt += '-' + raw.slice(6, 8);
    if (raw.length > 8) fmt += '-' + raw.slice(8);
    input.value = fmt;
    updateICDerived(raw, input.dataset.dob, input.dataset.gender);
}
document.getElementById('bookingForm').addEventListener('submit', function() {

    var icInput = document.getElementById('icInput');
    icInput.value = icInput.value.replace(/\D/g, '');
    

});
document.querySelectorAll('.et-ic').forEach(function(el){
    el.value = el.value.replace(/\D/g, '');
    const ppGender = document.getElementById('inputPpGender');
    ppGender.removeAttribute('disabled');
});

// ── Form submit guard ──────────────────────────────────────────────────────
document.getElementById('bookingForm').addEventListener('submit', function() {
    if (parseFloat(document.getElementById('totalAmountInput').value) <= 0) {
        alert('Jumlah bayaran tidak sah!');
        return false;
    }
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Menghantar tempahan...';
});

recalc();
</script>
</body>
</html>