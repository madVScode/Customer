<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelancong</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0f2027;
            --teal: #00d4aa;
            --teal-dark: #00b894;
            --mid: #2c5364;
            --bg: #f0f4f8;
            --text: #2d3748;
            --muted: #718096;
            --border: #e2e8f0;
            --white: #ffffff;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 18px rgba(0,0,0,0.09);
        }
        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
        body { font-family:'Poppins',sans-serif; background:var(--bg); color:var(--text); }

        /* ─── HEADER ─────────────────────────────────────────── */
.header {
    background:linear-gradient(135deg,var(--navy),var(--mid));
    color:white; padding:0.85rem 1.25rem;
    display:flex; justify-content:space-between; align-items:center;
    position:sticky; top:0; z-index:200;
    box-shadow:0 2px 18px rgba(0,0,0,0.28);
    gap:0.75rem;
}
.header-left { display:flex; align-items:center; gap:0.85rem; flex-shrink:0; }
.header-brand { font-size:1.2rem; font-weight:800; }
.header-brand span { color:var(--teal); }
.btn-back {
    display:inline-flex; align-items:center; gap:0.3rem;
    padding:0.38rem 0.85rem; border-radius:var(--radius-sm);
    background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.22);
    color:rgba(255,255,255,0.85); font-family:inherit; font-size:0.8rem;
    font-weight:600; text-decoration:none; cursor:pointer; transition:all .2s; white-space:nowrap;
}
.btn-back:hover { background:rgba(255,255,255,0.18); color:white; }
.header-right { display:flex; align-items:center; gap:0.5rem; flex-wrap:nowrap; }
.user-chip {
    font-size:0.78rem; color:rgba(255,255,255,0.75);
    background:rgba(255,255,255,0.1); padding:0.25rem 0.65rem;
    border-radius:20px; white-space:nowrap;
    overflow:hidden; text-overflow:ellipsis; max-width:140px;
}
.user-chip strong { color:white; display:block; font-size:0.82rem; }
.nav-link {
    padding:0.38rem 0.85rem; border-radius:var(--radius-sm); font-size:0.8rem; font-weight:600;
    text-decoration:none; transition:all .2s; cursor:pointer; font-family:inherit; border:none; white-space:nowrap;
}
.nav-link-ghost { background:rgba(255,255,255,0.1); color:white; border:1px solid rgba(255,255,255,0.28); }
.nav-link-ghost:hover { background:rgba(255,255,255,0.2); }
.nav-link-danger { background:rgba(229,62,62,0.15); color:#fc8181; border:1px solid rgba(252,129,129,0.3); }

/* Sembunyikan pada mobile — bottom nav ambil alih */
.header-nav-links { display:flex; gap:0.5rem; align-items:center; }

@media(max-width:768px) {
    .header-nav-links { display:none; }
    .user-chip { max-width:100px; font-size:0.72rem; }
}
@media(max-width:400px) {
    .header-brand { font-size:1rem; }
    .user-chip { display:none; }
    .btn-back { font-size:0.72rem; padding:0.3rem 0.6rem; }
}

        /* ─── CONTAINER ──────────────────────────────────────── */
        .container { max-width:1200px; margin:0 auto; padding:1.5rem 1.25rem; }

        /* ─── WELCOME BANNER ─────────────────────────────────── */
        .welcome-banner {
            background:linear-gradient(135deg,var(--navy),var(--mid));
            color:white; border-radius:var(--radius-lg); padding:1.75rem;
            margin-bottom:1.5rem; display:flex; justify-content:space-between;
            align-items:center; flex-wrap:wrap; gap:1rem;
        }
        .welcome-banner h2 { font-size:1.35rem; font-weight:800; margin-bottom:0.2rem; }
        .welcome-banner p { color:rgba(255,255,255,0.72); font-size:0.87rem; }
        .company-badge { background:rgba(0,212,170,0.18); border:1px solid var(--teal); color:var(--teal); padding:0.38rem 1rem; border-radius:20px; font-size:0.78rem; font-weight:700; white-space:nowrap; }

        /* ─── STATS ROW ──────────────────────────────────────── */
        .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:0.85rem; margin-bottom:1.5rem; }
        .stat-card { background:white; border-radius:var(--radius-md); padding:1.1rem 1rem; box-shadow:var(--shadow-sm); text-align:center; }
        .stat-card .icon { font-size:1.7rem; margin-bottom:0.35rem; display:block; }
        .stat-card .num { font-size:1.65rem; font-weight:800; color:var(--text); line-height:1; }
        .stat-card .label { font-size:0.7rem; color:var(--muted); margin-top:0.3rem; }

        /* ─── TABS ───────────────────────────────────────────── */
        .tabs-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; margin-bottom:1.25rem; }
        .tabs-wrap::-webkit-scrollbar { display:none; }
        .tabs { display:flex; gap:0.45rem; white-space:nowrap; padding-bottom:2px; min-width:max-content; }
        .tab {
            padding:0.55rem 1.2rem; border-radius:25px; font-family:inherit;
            font-size:0.85rem; font-weight:600; cursor:pointer; border:1.5px solid var(--border);
            background:white; color:var(--muted); transition:all .2s; white-space:nowrap;
        }
        .tab.active { background:linear-gradient(135deg,var(--navy),var(--mid)); color:white; border-color:transparent; }
        .tab-content { display:none; }
        .tab-content.active { display:block; }

        /* ─── FILTER BAR ─────────────────────────────────────── */
        .filter-bar {
            background:white; border-radius:var(--radius-lg); padding:1.1rem 1.25rem;
            box-shadow:var(--shadow-sm); margin-bottom:0.85rem;
            display:flex; gap:0.65rem; flex-wrap:wrap; align-items:flex-end;
        }
        .filter-group { display:flex; flex-direction:column; gap:0.28rem; flex:1; min-width:140px; }
        .filter-group label { font-size:0.68rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
        .filter-input {
            padding:0.65rem 0.9rem; border:2px solid var(--border); border-radius:var(--radius-sm);
            font-family:inherit; font-size:0.88rem; color:var(--text);
            transition:border-color .2s; outline:none; width:100%;
        }
        .filter-input:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(0,212,170,0.1); }
        .search-group { position:relative; flex:2; min-width:200px; }
        .search-group label { font-size:0.68rem; font-weight:700; color:var(--muted); text-transform:uppercase; display:block; margin-bottom:0.28rem; }
        .search-group .filter-input { padding-left:2.4rem; }
        .search-icon { position:absolute; left:0.85rem; bottom:0.7rem; font-size:0.88rem; pointer-events:none; }
        .search-spinner { position:absolute; right:0.85rem; bottom:0.72rem; width:15px; height:15px; border:2px solid var(--border); border-top-color:var(--teal); border-radius:50%; animation:spin .5s linear infinite; display:none; }
        @keyframes spin { to{transform:rotate(360deg)} }
        .btn-reset { padding:0.65rem 1rem; background:var(--bg); color:var(--muted); border:1.5px solid var(--border); border-radius:var(--radius-sm); font-family:inherit; font-weight:600; font-size:0.82rem; cursor:pointer; white-space:nowrap; align-self:flex-end; transition:all .2s; display:none; }
        .btn-reset:hover { background:var(--border); color:var(--text); }

        .filter-tags { display:flex; gap:0.45rem; flex-wrap:wrap; margin-bottom:0.7rem; }
        .filter-tag { background:#e0fdf4; color:#065f46; border:1px solid #6ee7b7; padding:3px 10px 3px 12px; border-radius:20px; font-size:0.74rem; font-weight:600; display:flex; align-items:center; gap:0.35rem; }
        .filter-tag button { background:none; border:none; color:#059669; cursor:pointer; font-size:0.9rem; }

        .result-info { font-size:0.8rem; color:var(--muted); margin-bottom:0.85rem; min-height:1.2em; }
        .result-info strong { color:var(--text); }

        /* ─── TRIP CARDS ─────────────────────────────────────── */
        .trips-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(270px,1fr)); gap:1.1rem; }
        .trip-card { background:white; border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); transition:transform .22s,box-shadow .22s; }
        .trip-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); }
        .trip-img-box { height:170px; overflow:hidden; background:linear-gradient(135deg,#203a43,var(--mid)); position:relative; display:flex; align-items:center; justify-content:center; }
        .trip-img-box img { width:100%; height:170px; object-fit:cover; display:block; transition:transform .3s; }
        .trip-card:hover .trip-img-box img { transform:scale(1.04); }
        .trip-img-placeholder { width:100%; height:170px; display:flex; align-items:center; justify-content:center; font-size:3rem; color:rgba(255,255,255,0.6); flex-direction:column; gap:0.4rem; }
        .trip-img-placeholder span { font-size:0.7rem; color:rgba(255,255,255,0.4); }
        .trip-body { padding:1rem; }
        .trip-co { font-size:0.68rem; color:var(--teal); font-weight:700; text-transform:uppercase; }
        .trip-name { font-size:0.95rem; font-weight:700; color:var(--text); margin:0.22rem 0 0.35rem; }
        .trip-detail { font-size:0.78rem; color:var(--muted); margin-bottom:0.2rem; }
        .trip-footer { display:flex; justify-content:space-between; align-items:center; padding-top:0.7rem; border-top:1px solid #f0f0f0; margin-top:0.7rem; }
        .price { font-size:1.15rem; font-weight:800; color:var(--teal); }
        .btn-book { padding:0.42rem 0.95rem; background:linear-gradient(135deg,var(--navy),var(--mid)); color:white; border:none; border-radius:var(--radius-sm); font-family:inherit; font-size:0.8rem; font-weight:700; cursor:pointer; text-decoration:none; display:inline-block; white-space:nowrap; }

        /* ─── NO RESULTS ─────────────────────────────────────── */
        .no-results { display:none; text-align:center; padding:3rem 1.5rem; background:white; border-radius:var(--radius-lg); color:#a0aec0; }
        .no-results .emoji { font-size:3.2rem; margin-bottom:0.65rem; display:block; }

        /* ─── BOOKING TABLE — SCROLLABLE ─────────────────────── */
        .table-scroll-wrap {
            width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;
            border-radius:var(--radius-lg);
            box-shadow:var(--shadow-sm);
            position:relative;
        }
        /* Fade indicator on right side when scrollable */
        .table-scroll-wrap::after {
            content:'';
            position:absolute; top:0; right:0; width:40px; height:100%;
            background:linear-gradient(to right, transparent, rgba(255,255,255,0.9));
            pointer-events:none; border-radius:0 var(--radius-lg) var(--radius-lg) 0;
            opacity:1; transition:opacity .3s;
        }
        .table-scroll-wrap.no-fade::after { opacity:0; }

        .bookings-table { width:100%; min-width:680px; border-collapse:collapse; background:white; }
        .bookings-table th { background:linear-gradient(135deg,var(--navy),var(--mid)); color:white; padding:0.78rem 1rem; text-align:left; font-size:0.78rem; font-weight:700; white-space:nowrap; }
        .bookings-table th:first-child { border-radius:0; }
        .bookings-table td { padding:0.82rem 1rem; border-bottom:1px solid #f0f0f0; font-size:0.83rem; white-space:nowrap; }
        .bookings-table tr:last-child td { border-bottom:none; }
        .bookings-table tr:hover td { background:#fafbfc; }

        /* Mobile scroll hint */
        .scroll-hint { display:none; align-items:center; gap:0.4rem; font-size:0.73rem; color:var(--muted); padding:0.5rem 0 0.65rem; }
        .scroll-hint .arrow { animation:scrollPulse 1.5s ease infinite; }
        @keyframes scrollPulse { 0%,100%{transform:translateX(0);opacity:.7} 50%{transform:translateX(5px);opacity:1} }

        .badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; }
        .badge-pending   { background:#fff8e1; color:#f57f17; }
        .badge-confirmed { background:#e8f5e9; color:#2e7d32; }
        .badge-cancelled { background:#ffebee; color:#c62828; }
        .badge-unpaid    { background:#ffebee; color:#c62828; }
        .badge-paid      { background:#e8f5e9; color:#2e7d32; }

        .empty-state { text-align:center; padding:3rem 1.5rem; color:#a0aec0; background:white; border-radius:var(--radius-lg); }
        .alert-success { background:#f0fdf4; border:1px solid #6ee7b7; border-radius:var(--radius-sm); padding:0.7rem 1rem; margin-bottom:1.25rem; color:#065f46; font-size:0.88rem; }
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
    display:none; /* Sembunyikan pada desktop */
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

        /* ─── RESPONSIVE ─────────────────────────────────────── */
        @media(max-width:768px) {
            .bottom-nav { display:block; }
            body { padding-bottom:68px; }
            .header-right .user-chip { display:none; }
            .stats-row { grid-template-columns:repeat(2,1fr); gap:0.65rem; }
            .container { padding:1.1rem 1rem; }
            .welcome-banner { padding:1.35rem; }
            .welcome-banner h2 { font-size:1.15rem; }
            .filter-bar { padding:0.9rem 1rem; gap:0.55rem; }
            .search-group { min-width:100%; }
            .filter-group { min-width:calc(50% - 0.28rem); }
            .trips-grid { grid-template-columns:1fr; gap:0.85rem; }
            .scroll-hint { display:flex; }
        }
        @media(max-width:480px) {
            .header { padding:0.72rem 1rem; }
            .btn-back { font-size:0.75rem; padding:0.32rem 0.7rem; }
            .welcome-banner { padding:1.1rem; }
            .stat-card { padding:0.9rem 0.75rem; }
            .stat-card .num { font-size:1.4rem; }
            .filter-group { min-width:100%; }
        }
        @media(min-width:640px) and (max-width:1023px) {
            .trips-grid { grid-template-columns:repeat(2,1fr); }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-left">
        <div class="header-brand">✈️ Arson<span>Trip</span></div>
    </div>
    <div class="header-right">
    </div>
</header>
{{-- TOP NAV: desktop sahaja, gantikan bottom nav --}}
@auth
    @if(Auth::user()->isTourist())
    <nav class="top-nav">
        <a href="{{ route('tourist.index') }}" >🌏 Trip</a>
        <a href="{{ route('tourist.dashboard') }}"class="current">🏠 Dashboard</a>
        <a href="{{ route('tourist.my-bookings') }}">📋 Booking</a>
        <form method="POST" action="{{ route('tourist.logout') }}">
            @csrf
            <button type="submit" class="tn-danger">🚪 Keluar</button>
        </form>
    </nav>
    @endif
@endauth

<div class="container">

    @if(session('success'))
        <div class="alert-success">✅ {{ session('success') }}</div>
    @endif

    <div class="welcome-banner">
        <div>
            <h2>Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
            <p>Pilih trip impian anda dan buat tempahan sekarang.</p>
        </div>
        @if($company)
            <div class="company-badge">🏢 {{ $company->name }}</div>
        @endif
    </div>

    <div class="stats-row">
        <div class="stat-card"><span class="icon">✈️</span><div class="num" id="statUpcoming">{{ $upcomingTrips->count() }}</div><div class="label">Trip Tersedia</div></div>
        <div class="stat-card"><span class="icon">📋</span><div class="num">{{ $bookings->count() }}</div><div class="label">Booking Saya</div></div>
        <div class="stat-card"><span class="icon">✅</span><div class="num">{{ $bookings->where('booking_status','confirmed')->count() }}</div><div class="label">Disahkan</div></div>
        <div class="stat-card"><span class="icon">⏳</span><div class="num">{{ $bookings->where('booking_status','pending')->count() }}</div><div class="label">Menunggu</div></div>
    </div>

    <div class="tabs-wrap">
        <div class="tabs">
            <button class="tab active" onclick="switchTab('upcoming',this)">✈️ Akan Datang (<span id="tabCountUpcoming">{{ $upcomingTrips->count() }}</span>)</button>
            <button class="tab" onclick="switchTab('past',this)">🕐 Trip Lepas ({{ $pastTrips->count() }})</button>
        </div>
    </div>

    <!-- ─── UPCOMING TRIPS ────────────────────────────────── -->
    <div class="tab-content active" id="tab-upcoming">
        <div class="filter-bar">
            <div class="search-group">
                <label>🔍 Cari Destinasi</label>
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" class="filter-input" placeholder="Nama trip, destinasi atau negara..." autocomplete="off">
                <div class="search-spinner" id="searchSpinner"></div>
            </div>
            <div class="filter-group">
                <label>📅 Dari</label>
                <input type="date" id="dateFrom" class="filter-input">
            </div>
            <div class="filter-group">
                <label>📅 Hingga</label>
                <input type="date" id="dateTo" class="filter-input">
            </div>
            <button class="btn-reset" id="btnReset" onclick="resetFilters()">✕ Reset</button>
        </div>
        <div class="filter-tags" id="filterTags"></div>
        <div class="result-info" id="resultInfo"></div>

        <div class="trips-grid" id="tripsGrid">
            @foreach($upcomingTrips as $trip)
                @php
                    $imgUrl = $trip->image_url ?? $trip->pic1_url ?? null;
                    if ($imgUrl && !str_starts_with($imgUrl,'http')) { $imgUrl = asset('storage/'.ltrim($imgUrl,'/')); }
                @endphp
                <div class="trip-card trip-item"
                     data-name="{{ strtolower($trip->location) }}"
                     data-country="{{ strtolower($trip->country) }}"
                     data-company="{{ strtolower($trip->company->name ?? '') }}"
                     data-from="{{ $trip->date_from }}"
                     data-to="{{ $trip->date_to }}">
                    <div class="trip-img-box">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $trip->location }}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div class="trip-img-placeholder" style="display:none;">✈️<span>{{ $trip->location }}</span></div>
                        @else
                            <div class="trip-img-placeholder">✈️<span>{{ $trip->location }}</span></div>
                        @endif
                        <div style="position:absolute;top:10px;left:10px;background:var(--teal);color:var(--navy);padding:3px 10px;border-radius:20px;font-size:0.68rem;font-weight:800;">🟢 Terbuka</div>
                    </div>
                    <div class="trip-body">
                        <div class="trip-co">{{ $trip->company->name ?? '-' }}</div>
                        <div class="trip-name">{{ $trip->location }}</div>
                        <div class="trip-detail">📍 {{ $trip->country }} ({{ $trip->national_code }})</div>
                        <div class="trip-detail">📅 {{ \Carbon\Carbon::parse($trip->date_from)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($trip->date_to)->format('d/m/Y') }}</div>
                        <div class="trip-detail">⏱️ {{ $trip->duration ?? '-' }} hari</div>
                        <div class="trip-footer">
                            <div>
                                <div style="font-size:0.67rem;color:var(--muted);">Dari</div>
                                <div class="price">RM {{ number_format($trip->price_adult,0) }}</div>
                            </div>
                            <a href="{{ route('tourist.booking.show',$trip->id) }}" class="btn-book">Tempah →</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="no-results" id="noResults">
            <span class="emoji">🔍</span>
            <p style="font-weight:700;font-size:0.92rem;color:#4a5568;">Tiada trip ditemui</p>
            <p style="font-size:0.82rem;margin-top:0.3rem;">Cuba carian lain atau ubah tarikh</p>
            <button onclick="resetFilters()" style="margin-top:0.85rem;padding:0.45rem 1.1rem;background:var(--navy);color:white;border:none;border-radius:var(--radius-sm);font-family:inherit;font-weight:600;cursor:pointer;font-size:0.85rem;">Reset Carian</button>
        </div>
    </div>

    <!-- ─── PAST TRIPS ────────────────────────────────────── -->
    <div class="tab-content" id="tab-past">
        @if($pastTrips->isEmpty())
            <div class="empty-state"><div style="font-size:3.5rem;margin-bottom:0.85rem;">🕐</div><p style="font-size:1rem;font-weight:600;">Tiada rekod trip lepas</p></div>
        @else
            <div class="trips-grid">
                @foreach($pastTrips as $trip)
                    @php
                        $imgUrl = $trip->image_url ?? $trip->pic1_url ?? null;
                        if ($imgUrl && !str_starts_with($imgUrl,'http')) { $imgUrl = asset('storage/'.ltrim($imgUrl,'/')); }
                    @endphp
                    <div class="trip-card" style="opacity:0.82;">
                        <div class="trip-img-box">
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $trip->location }}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="trip-img-placeholder" style="display:none;">✈️<span>{{ $trip->location }}</span></div>
                            @else
                                <div class="trip-img-placeholder">✈️<span>{{ $trip->location }}</span></div>
                            @endif
                            <div style="position:absolute;top:10px;left:10px;background:rgba(0,0,0,0.5);color:#a0aec0;padding:3px 10px;border-radius:20px;font-size:0.68rem;font-weight:800;backdrop-filter:blur(4px);">🔒 Tamat</div>
                        </div>
                        <div class="trip-body">
                            <div class="trip-co">{{ $trip->company->name ?? '-' }}</div>
                            <div class="trip-name">{{ $trip->location }}</div>
                            <div class="trip-detail">📍 {{ $trip->country }} ({{ $trip->national_code }})</div>
                            <div class="trip-detail">📅 {{ \Carbon\Carbon::parse($trip->date_from)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($trip->date_to)->format('d/m/Y') }}</div>
                            <div class="trip-detail">⏱️ {{ $trip->duration ?? '-' }} hari</div>
                            <div class="trip-footer">
                                <div>
                                    <div style="font-size:0.67rem;color:var(--muted);">Harga</div>
                                    <div class="price" style="color:var(--muted);">RM {{ number_format($trip->price_adult,0) }}</div>
                                </div>
                                <a href="{{ route('tourist.trip.detail',$trip->id) }}" style="padding:0.42rem 0.95rem;background:var(--bg);color:var(--muted);border-radius:var(--radius-sm);font-size:0.8rem;font-weight:700;text-decoration:none;">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ─── BOOKINGS TAB ──────────────────────────────────── -->
    <div class="tab-content" id="tab-bookings">
        @if($bookings->isEmpty())
            <div class="empty-state"><div style="font-size:3.5rem;margin-bottom:0.85rem;">📋</div><p style="font-size:1rem;font-weight:600;">Belum ada booking</p></div>
        @else
            <div class="scroll-hint">
                <span>Scroll untuk lihat lebih</span>
                <span class="arrow">→</span>
            </div>
            <div class="table-scroll-wrap" id="tableWrap">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>#</th><th>No. Booking</th><th>Trip</th>
                            <th>Tarikh</th><th>Jumlah</th><th>Bayaran</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $i => $booking)
                            <tr>
                                <td style="color:var(--muted);font-size:0.78rem;">{{ $i+1 }}</td>
                                <td><strong style="font-family:monospace;color:#2c5364;font-size:0.88rem;">{{ $booking->booking_no }}</strong></td>
                                <td>
                                    <div style="font-weight:600;font-size:0.85rem;">{{ $booking->trip->location ?? '-' }}</div>
                                    <div style="font-size:0.72rem;color:var(--muted);">🏢 {{ $booking->trip->company->name ?? '-' }}</div>
                                </td>
                                <td style="font-size:0.78rem;">
                                    {{ $booking->trip->date_from ? \Carbon\Carbon::parse($booking->trip->date_from)->format('d/m/Y') : '-' }}<br>
                                    <span style="color:var(--muted);">→ {{ $booking->trip->date_to ? \Carbon\Carbon::parse($booking->trip->date_to)->format('d/m/Y') : '-' }}</span>
                                </td>
                                <td><strong>RM {{ number_format($booking->total_amount,2) }}</strong></td>
                                <td>
                                    <span class="badge {{ $booking->payment_status==='paid'?'badge-paid':'badge-unpaid' }}">
                                        {{ $booking->payment_status==='paid'?'✅ Bayar':'⏳ Belum' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $sc=match($booking->booking_status){'confirmed'=>'badge-confirmed','cancelled'=>'badge-cancelled','completed'=>'badge-confirmed',default=>'badge-pending'};
                                        $st=match($booking->booking_status){'confirmed'=>'✅ Disahkan','cancelled'=>'❌ Dibatal','completed'=>'🏁 Selesai',default=>'🕐 Menunggu'};
                                    @endphp
                                    <span class="badge {{ $sc }}">{{ $st }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

<script>
function switchTab(name,el){
    document.querySelectorAll('.tab').forEach(function(t){t.classList.remove('active');});
    document.querySelectorAll('.tab-content').forEach(function(c){c.classList.remove('active');});
    document.getElementById('tab-'+name).classList.add('active');
    if(el)el.classList.add('active');
}

var searchTimer=null,allCards=document.querySelectorAll('.trip-item'),totalCount=allCards.length;
function runFilter(){
    var q=document.getElementById('searchInput').value.trim().toLowerCase();
    var from=document.getElementById('dateFrom').value,to=document.getElementById('dateTo').value;
    var shown=0;
    allCards.forEach(function(c){
        var textOk=!q||c.dataset.name.includes(q)||c.dataset.country.includes(q)||c.dataset.company.includes(q);
        var dateOk=true;if(from&&c.dataset.to<from)dateOk=false;if(to&&c.dataset.from>to)dateOk=false;
        var vis=textOk&&dateOk;c.style.display=vis?'':'none';if(vis)shown++;
    });
    document.getElementById('tabCountUpcoming').textContent=shown;
    document.getElementById('statUpcoming').textContent=shown;
    document.getElementById('resultInfo').innerHTML=(q||from||to)?'Menunjukkan <strong>'+shown+'</strong> daripada <strong>'+totalCount+'</strong> trip':'';
    document.getElementById('tripsGrid').style.display=shown>0?'grid':'none';
    document.getElementById('noResults').style.display=shown===0?'block':'none';
    document.getElementById('btnReset').style.display=(q||from||to)?'inline-block':'none';
    renderTags(q,from,to);
    document.getElementById('searchSpinner').style.display='none';
}
document.getElementById('searchInput').addEventListener('input',function(){document.getElementById('searchSpinner').style.display='block';clearTimeout(searchTimer);searchTimer=setTimeout(runFilter,350);});
document.getElementById('dateFrom').addEventListener('change',runFilter);
document.getElementById('dateTo').addEventListener('change',runFilter);
function resetFilters(){document.getElementById('searchInput').value='';document.getElementById('dateFrom').value='';document.getElementById('dateTo').value='';runFilter();}
function renderTags(q,from,to){
    var wrap=document.getElementById('filterTags');wrap.innerHTML='';
    if(q)wrap.appendChild(makeTag('🔍 "'+q+'"',function(){document.getElementById('searchInput').value='';runFilter();}));
    if(from)wrap.appendChild(makeTag('📅 Dari: '+from,function(){document.getElementById('dateFrom').value='';runFilter();}));
    if(to)wrap.appendChild(makeTag('📅 Hingga: '+to,function(){document.getElementById('dateTo').value='';runFilter();}));
}
function makeTag(text,fn){var d=document.createElement('div');d.className='filter-tag';var s=document.createElement('span');s.textContent=text;var b=document.createElement('button');b.textContent='✕';b.onclick=fn;d.appendChild(s);d.appendChild(b);return d;}

// Table scroll fade
var tw=document.getElementById('tableWrap');
if(tw){tw.addEventListener('scroll',function(){if(this.scrollLeft+this.clientWidth>=this.scrollWidth-5){this.classList.add('no-fade');}else{this.classList.remove('no-fade');}});}
</script>

<nav class="bottom-nav">
    <div class="bottom-nav-inner">
        <a href="{{ route('tourist.index') }}" class="bottom-nav-item"><span class="bn-icon">🌏</span><span>Trip</span></a>
        <a href="{{ route('tourist.dashboard') }}" class="bottom-nav-item current"><span class="bn-icon">🏠</span><span>Dashboard</span></a>
        <a href="{{ route('tourist.my-bookings') }}" class="bottom-nav-item"><span class="bn-icon">📋</span><span>Booking</span></a>
        <form method="POST" action="{{ route('tourist.logout') }}" style="margin:0;flex:1;">
            @csrf
            <button type="submit" class="bottom-nav-item danger-item" style="width:100%;"><span class="bn-icon">🚪</span><span>Keluar</span></button>
        </form>
    </div>
</nav>
</body>
</html>