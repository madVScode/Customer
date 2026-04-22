<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tempahan Saya — ArsonTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --navy:#0f2027; --teal:#00d4aa; --teal-dark:#00b894; --mid:#2c5364;
            --bg:#f0f4f8; --text:#2d3748; --muted:#718096; --border:#e2e8f0;
            --radius-sm:8px; --radius-md:12px; --radius-lg:16px;
            --shadow-sm:0 2px 8px rgba(0,0,0,.07);
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);}

        .header{background:linear-gradient(135deg,var(--navy),var(--mid));color:white;
            padding:.85rem 1.25rem;display:flex;justify-content:space-between;align-items:center;
            position:sticky;top:0;z-index:100;box-shadow:0 2px 18px rgba(0,0,0,.28);}
        .header-brand{font-size:1.2rem;font-weight:800;}
        .header-brand span{color:var(--teal);}
        .back-link{display:inline-flex;align-items:center;gap:.3rem;color:rgba(255,255,255,.82);
            text-decoration:none;font-size:.82rem;font-weight:600;padding:.35rem .8rem;
            border-radius:var(--radius-sm);background:rgba(255,255,255,.1);
            border:1px solid rgba(255,255,255,.2);}

        .container{max-width:820px;margin:0 auto;padding:1.5rem 1.25rem;}

        .page-title{font-size:1.4rem;font-weight:800;margin-bottom:1.5rem;
            display:flex;align-items:center;gap:.5rem;}

        /* Alert */
        .alert-success{background:#f0fff4;border:1px solid #9ae6b4;border-radius:var(--radius-md);
            padding:.9rem 1.1rem;margin-bottom:1.25rem;color:#276749;font-size:.85rem;font-weight:600;}

        /* Booking card */
        .booking-card{background:white;border-radius:var(--radius-lg);
            box-shadow:var(--shadow-sm);margin-bottom:1.25rem;overflow:hidden;
            border:1px solid var(--border);}

        .bc-header{padding:1rem 1.25rem;display:flex;justify-content:space-between;
            align-items:flex-start;flex-wrap:wrap;gap:.75rem;
            border-bottom:1px solid var(--border);}
        .bc-booking-no{font-family:monospace;font-size:.9rem;font-weight:800;color:var(--text);}
        .bc-trip{font-size:1rem;font-weight:700;margin-top:.15rem;}
        .bc-dates{font-size:.78rem;color:var(--muted);margin-top:.1rem;}

        /* Status badges */
        .badge{display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .8rem;
            border-radius:20px;font-size:.72rem;font-weight:700;white-space:nowrap;}
        .badge-pending  {background:#fefcbf;color:#975a16;}
        .badge-confirmed{background:#c6f6d5;color:#276749;}
        .badge-cancelled{background:#fed7d7;color:#c53030;}
        .badge-completed{background:#bee3f8;color:#2b6cb0;}

        .bc-body{padding:1rem 1.25rem;}
        .bc-row{display:flex;flex-wrap:wrap;gap:.5rem 2rem;margin-bottom:.75rem;}
        .bc-item{font-size:.82rem;}
        .bc-item-label{color:var(--muted);font-size:.72rem;display:block;margin-bottom:.1rem;}
        .bc-item-value{font-weight:700;color:var(--text);}

        /* Progress indicator */
        .booking-progress{display:flex;gap:0;margin-bottom:1rem;}
        .bp-step{flex:1;text-align:center;font-size:.68rem;font-weight:700;
            padding:.4rem .2rem;border-top:3px solid var(--border);color:var(--muted);}
        .bp-step.done{border-top-color:var(--teal);color:var(--teal);}
        .bp-step.active{border-top-color:#f6ad55;color:#c05621;}

        /* Traveler form */
        .traveler-form-wrap{background:#f7fafc;border-top:2px dashed var(--border);padding:1.25rem;}
        .tf-title{font-size:.92rem;font-weight:700;margin-bottom:1rem;
            display:flex;align-items:center;gap:.5rem;}
        .traveler-block{background:white;border:1px solid var(--border);border-radius:var(--radius-md);
            padding:1rem;margin-bottom:.85rem;}
        .traveler-block-title{font-size:.82rem;font-weight:700;margin-bottom:.85rem;
            color:var(--mid);display:flex;align-items:center;gap:.4rem;}
        .tf-grid{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;}
        .tf-group{display:flex;flex-direction:column;gap:.25rem;}
        .tf-group.full{grid-column:1/-1;}
        .tf-label{font-size:.75rem;font-weight:600;color:#4a5568;}
        .tf-input{padding:.6rem .8rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);
            font-family:inherit;font-size:.85rem;color:var(--text);outline:none;transition:border-color .2s;min-height:40px;}
        .tf-input:focus{border-color:var(--teal);}

        /* Passport accordion */
        .pp-toggle{width:100%;padding:.5rem .8rem;border:none;background:#edf2f7;
            border-radius:var(--radius-sm);cursor:pointer;font-size:.75rem;font-weight:700;
            color:var(--muted);display:flex;justify-content:space-between;font-family:inherit;
            margin-top:.6rem;transition:background .15s;}
        .pp-toggle:hover{background:#e2e8f0;color:var(--text);}
        .pp-body{display:none;padding:.75rem 0 0;}

        /* Buttons */
        .btn-primary{padding:.65rem 1.3rem;background:linear-gradient(135deg,var(--teal-dark),var(--teal));
            color:var(--navy);border:none;border-radius:var(--radius-md);font-family:inherit;
            font-size:.9rem;font-weight:800;cursor:pointer;transition:all .2s;}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(0,212,170,.35);}
        .btn-secondary{padding:.65rem 1.3rem;background:white;color:var(--text);
            border:2px solid var(--border);border-radius:var(--radius-md);font-family:inherit;
            font-size:.9rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;
            align-items:center;gap:.4rem;transition:all .2s;}
        .btn-secondary:hover{border-color:var(--teal);color:var(--teal);}

        .bc-actions{padding:.85rem 1.25rem;border-top:1px solid var(--border);
            display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;}

        .empty-state{text-align:center;padding:3rem 2rem;background:white;
            border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);}
            /* ─── TOP NAV (desktop only) ────────────────────────────── */
.top-nav {
    display: none;
}
@media(min-width: 769px) {
    .top-nav {
        display: flex;
        position: sticky;
        top: 0;
        z-index: 199;
        background: var(--navy);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        padding: 0 1.5rem;
        align-items: center;
        gap: 0.5rem;
        height: 52px;
    }
    .top-nav a,
    .top-nav button {
        display: flex; align-items: center; gap: 0.4rem;
        text-decoration: none;
        color: rgba(255,255,255,0.65);
        font-family: 'Poppins', sans-serif;
        font-weight: 600; font-size: 0.85rem;
        padding: 0.45rem 0.9rem;
        border-radius: var(--radius-sm);
        border: none; background: none; cursor: pointer;
        transition: all 0.2s; white-space: nowrap;
    }
    .top-nav a:hover,
    .top-nav button:hover { background: rgba(255,255,255,0.1); color: white; }
    .top-nav a.current { background: rgba(0,212,170,0.15); color: var(--teal); }
    .top-nav .tn-danger { color: rgba(252,129,129,0.7); margin-left: auto; }
    .top-nav .tn-danger:hover { background: rgba(229,62,62,0.15); color: #fc8181; }
    .top-nav form { margin: 0; }
}

        /* Bottom Nav */
        .bottom-nav{position:fixed;bottom:0;left:0;right:0;z-index:200;
            background:var(--navy);box-shadow:0 -2px 16px rgba(0,0,0,.25);
        display:none; /* Sembunyikan pada desktop */}
        @media(max-width:768px){
    .bottom-nav { display:block; }
    body { padding-bottom:68px; }
}
        .bottom-nav-inner{display:flex;align-items:stretch;max-width:820px;margin:0 auto;}
        .bottom-nav-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:.18rem;padding:.55rem .25rem .5rem;color:rgba(255,255,255,.58);text-decoration:none;
            font-size:.65rem;font-weight:600;background:none;border:none;cursor:pointer;
            font-family:'Poppins',sans-serif;transition:color .18s,background .18s;}
        .bottom-nav-item:hover,.bottom-nav-item.current{color:var(--teal);}
        .bottom-nav-item.danger-item:hover{color:#fc8181;}
        .bn-icon{font-size:1.25rem;line-height:1;}

        @media(max-width:640px){
            .tf-grid{grid-template-columns:1fr;}
            .tf-group.full{grid-column:1;}
            .bc-header{padding:.9rem 1rem;}
            .bc-body{padding:.9rem 1rem;}
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-brand">✈️ Arson<span>Trip</span></div>
</header>
{{-- TOP NAV: desktop sahaja, gantikan bottom nav --}}
@auth
    @if(Auth::user()->isTourist())
    <nav class="top-nav">
        <a href="{{ route('tourist.index') }}">🌏 Trip</a>
        <a href="{{ route('tourist.dashboard') }}">🏠 Dashboard</a>
        <a href="{{ route('tourist.my-bookings') }}" class="current">📋 Booking</a>
        <form method="POST" action="{{ route('tourist.logout') }}">
            @csrf
            <button type="submit" class="tn-danger">🚪 Keluar</button>
        </form>
    </nav>
    @endif
@endauth

<div class="container">

    <div class="page-title">📋 Tempahan Saya</div>

    @if(session('success'))
    <div class="alert-success">✅ {{ session('success') }}</div>
    @endif

    @if($bookings->isEmpty())
    <div class="empty-state">
        <div style="font-size:3.5rem;margin-bottom:.75rem;">✈️</div>
        <div style="font-size:1.1rem;font-weight:700;margin-bottom:.4rem;">Tiada tempahan lagi</div>
        <div style="font-size:.85rem;color:var(--muted);margin-bottom:1.25rem;">Mulakan pengembaraan anda hari ini!</div>
        <a href="{{ route('tourist.dashboard') }}" class="btn-primary" style="text-decoration:none;display:inline-block;">
            🌏 Terokai Trip
        </a>
    </div>
    @else

    @foreach($bookings as $booking)
    @php
        $status     = $booking->booking_status ?? 'pending';
        $confirmed  = $status === 'confirmed';
        $completed  = $status === 'completed';
        $needDetail = ($confirmed || $completed) && !$booking->details_completed;
        $trip       = $booking->trip;
        $totalPax   = ($booking->pax_adult ?? 0) + ($booking->pax_child ?? 0) + ($booking->pax_baby ?? 0);
    @endphp

    <div class="booking-card">

        <!-- Header -->
        <div class="bc-header">
            <div>
                <div class="bc-booking-no">{{ $booking->booking_no }}</div>
                <div class="bc-trip">{{ $trip->location ?? '-' }}</div>
                <div class="bc-dates">
                    {{ $trip ? \Carbon\Carbon::parse($trip->date_from)->format('d/m/Y').' → '.\Carbon\Carbon::parse($trip->date_to)->format('d/m/Y') : '-' }}
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.35rem;align-items:flex-end;">
                <span class="badge badge-{{ $status }}">
                    @switch($status)
                        @case('pending') ⏳ Menunggu Lulusan @break
                        @case('confirmed') ✅ Disahkan @break
                        @case('cancelled') ❌ Dibatal @break
                        @case('completed') 🏁 Selesai @break
                    @endswitch
                </span>
                @if($needDetail)
                <span class="badge" style="background:#fef3c7;color:#92400e;">📝 Perlu Maklumat</span>
                @elseif($booking->details_completed)
                <span class="badge" style="background:#dbeafe;color:#1d4ed8;">✅ Maklumat Lengkap</span>
                @endif
            </div>
        </div>

        <!-- Progress bar -->
        <div class="booking-progress">
            <div class="bp-step done">① Tempahan</div>
            <div class="bp-step {{ in_array($status,['confirmed','completed']) ? 'done' : ($status === 'cancelled' ? '' : 'active') }}">
                {{ $status === 'cancelled' ? '❌ Ditolak' : '② Lulusan' }}
            </div>
            <div class="bp-step {{ $booking->details_completed ? 'done' : ($confirmed ? 'active' : '') }}">③ Maklumat</div>
        </div>

        <!-- Body -->
        <div class="bc-body">
            <div class="bc-row">
                <div class="bc-item">
                    <span class="bc-item-label">Pelancong</span>
                    <span class="bc-item-value">
                        {{ $booking->pax_adult ?? 0 }} Dewasa
                        @if($booking->pax_child) · {{ $booking->pax_child }} Kanak @endif
                        @if($booking->pax_baby) · {{ $booking->pax_baby }} Bayi @endif
                        ({{ $totalPax }} orang)
                    </span>
                </div>
                <div class="bc-item">
                    <span class="bc-item-label">Jumlah</span>
                    <span class="bc-item-value" style="color:var(--teal);">RM {{ number_format($booking->total_amount, 2) }}</span>
                </div>
                <div class="bc-item">
                    <span class="bc-item-label">Bayaran</span>
                    <span class="bc-item-value">{{ $booking->payment_label }}</span>
                </div>
                <div class="bc-item">
                    <span class="bc-item-label">Tarikh Tempahan</span>
                    <span class="bc-item-value">{{ $booking->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            @if($status === 'cancelled' && $booking->rejection_reason)
            <div style="background:#fff5f5;border:1px solid #fc8181;border-radius:var(--radius-sm);
                        padding:.7rem .9rem;font-size:.82rem;color:#c53030;margin-top:.25rem;">
                <strong>Sebab ditolak:</strong> {{ $booking->rejection_reason }}
            </div>
            @endif

            @if($status === 'pending')
            <div style="background:#fffbeb;border:1px solid #f6e05e;border-radius:var(--radius-sm);
                        padding:.7rem .9rem;font-size:.82rem;color:#975a16;margin-top:.25rem;">
                ⏳ Tempahan anda sedang menunggu kelulusan admin. Anda akan dihubungi melalui WhatsApp.
            </div>
            @endif
        </div>

        <!-- Traveler Details Form (shown when confirmed + not completed) -->
        @if($needDetail)
        <div class="traveler-form-wrap">
            <div class="tf-title">
                📝 Lengkapkan Maklumat Pelancong
                <span style="font-size:.75rem;font-weight:400;color:var(--muted);">
                    — Diperlukan untuk cetak resit PDF
                </span>
            </div>
            <form method="POST" action="{{ route('tourist.booking.travelers', $booking->id) }}">
                @csrf
                @php
                    $travelers = [];
                    $n = 1;
                    for($i=0; $i<($booking->pax_adult ?? 0); $i++) {
                        $travelers[] = ['type'=>'adult', 'label'=>'👨 Dewasa '.($i+1), 'index'=>$n++];
                    }
                    for($i=0; $i<($booking->pax_child ?? 0); $i++) {
                        $travelers[] = ['type'=>'child', 'label'=>'👶 Kanak-kanak '.($i+1), 'index'=>$n++];
                    }
                    for($i=0; $i<($booking->pax_baby ?? 0); $i++) {
                        $travelers[] = ['type'=>'baby', 'label'=>'🍼 Bayi '.($i+1), 'index'=>$n++];
                    }
                    $saved = $booking->traveler_details ?? [];
                @endphp

                @foreach($travelers as $ti => $tv)
                <div class="traveler-block">
                    <div class="traveler-block-title">{{ $tv['label'] }}</div>
                    <input type="hidden" name="travelers[{{ $ti }}][type]" value="{{ $tv['type'] }}">
                    <input type="hidden" name="travelers[{{ $ti }}][order]" value="{{ $tv['index'] }}">
                    <div class="tf-grid">
                        <div class="tf-group">
                            <label class="tf-label">Nama Penuh *</label>
                            <input type="text" name="travelers[{{ $ti }}][name]" class="tf-input" required
                                   value="{{ $saved[$ti]['name'] ?? '' }}"
                                   placeholder="Nama mengikut IC / Pasport">
                        </div>
                        <div class="tf-group">
                            <label class="tf-label">No. IC / Passport *</label>
                            <input type="text" name="travelers[{{ $ti }}][ic]" class="tf-input" required
                                   value="{{ $saved[$ti]['ic'] ?? '' }}"
                                   placeholder="IC atau Pasport">
                        </div>
                        <div class="tf-group">
                            <label class="tf-label">Tarikh Lahir</label>
                            <input type="date" name="travelers[{{ $ti }}][dob]" class="tf-input"
                                   value="{{ $saved[$ti]['dob'] ?? '' }}">
                        </div>
                        <div class="tf-group">
                            <label class="tf-label">Jantina</label>
                            <select name="travelers[{{ $ti }}][gender]" class="tf-input">
                                <option value="">-- Pilih --</option>
                                <option value="Lelaki"  {{ ($saved[$ti]['gender'] ?? '') === 'Lelaki'  ? 'selected' : '' }}>Lelaki</option>
                                <option value="Perempuan" {{ ($saved[$ti]['gender'] ?? '') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Passport toggle -->
                    <button type="button" class="pp-toggle"
                            onclick="var b=this.nextElementSibling; b.style.display=b.style.display==='none'?'block':'none'; this.textContent=(b.style.display==='block'?'▲':'▼')+' Maklumat Pasport (Pilihan)';">
                        ▼ Maklumat Pasport (Pilihan)
                    </button>
                    <div class="pp-body">
                        <div class="tf-grid">
                            <div class="tf-group">
                                <label class="tf-label">No. Pasport</label>
                                <input type="text" name="travelers[{{ $ti }}][passport_no]" class="tf-input"
                                       value="{{ $saved[$ti]['passport_no'] ?? '' }}"
                                       placeholder="A12345678">
                            </div>
                            <div class="tf-group">
                                <label class="tf-label">Warganegara</label>
                                <input type="text" name="travelers[{{ $ti }}][nationality]" class="tf-input"
                                       value="{{ $saved[$ti]['nationality'] ?? 'Malaysian' }}"
                                       placeholder="Malaysian">
                            </div>
                            <div class="tf-group">
                                <label class="tf-label">Tarikh Luput Pasport</label>
                                <input type="date" name="travelers[{{ $ti }}][pp_expire]" class="tf-input"
                                       value="{{ $saved[$ti]['pp_expire'] ?? '' }}">
                            </div>
                            <div class="tf-group">
                                <label class="tf-label">Tempat Lahir</label>
                                <input type="text" name="travelers[{{ $ti }}][birthplace]" class="tf-input"
                                       value="{{ $saved[$ti]['birthplace'] ?? '' }}"
                                       placeholder="Kuala Lumpur">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <div style="margin-top:.25rem;">
                    <button type="submit" class="btn-primary" style="width:100%;padding:.9rem;">
                        💾 Simpan Maklumat Pelancong
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Actions -->
        <div class="bc-actions">

            @if($booking->trip && $trip)
            @php
                $waAdmin = app(\App\Services\WhatsAppService::class)
                    ->getAdminWaLink('Salam, saya ingin bertanya tentang booking #'.$booking->booking_no.' — '.$trip->location);
            @endphp
            <a href="{{ $waAdmin }}" target="_blank" class="btn-secondary"
               style="text-decoration:none;">
                💬 Hubungi Admin
            </a>
            @endif
        </div>
    </div>
    @endforeach

    @endif
</div>

@auth
    @if(Auth::user()->isTourist())
    <nav class="bottom-nav">
        <div class="bottom-nav-inner">
            <a href="{{ route('tourist.index') }}" class="bottom-nav-item"><span class="bn-icon">🌏</span><span>Trip</span></a>
            <a href="{{ route('tourist.dashboard') }}" class="bottom-nav-item"><span class="bn-icon">🏠</span><span>Dashboard</span></a>
            <a href="{{ route('tourist.my-bookings') }}" class="bottom-nav-item current"><span class="bn-icon">📋</span><span>Booking</span></a>
            <form method="POST" action="{{ route('tourist.logout') }}" style="margin:0;flex:1;">
                @csrf
                <button type="submit" class="bottom-nav-item danger-item" style="width:100%;"><span class="bn-icon">🚪</span><span>Keluar</span></button>
            </form>
        </div>
    </nav>
    @endif
@endauth

</body>
</html>