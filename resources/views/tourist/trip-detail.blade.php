<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $trip->location }} — Detail Trip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0f2027; --teal: #00d4aa; --teal-dark: #00b894; --mid: #2c5364;
            --bg: #f0f4f8; --text: #2d3748; --muted: #718096; --border: #e2e8f0;
            --radius-sm: 8px; --radius-md: 12px; --radius-lg: 16px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06); --shadow-md: 0 4px 18px rgba(0,0,0,0.1);
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
        }
        .header-brand { font-size:1.2rem; font-weight:800; }
        .header-brand span { color:var(--teal); }
        .back-link {
            display:inline-flex; align-items:center; gap:0.3rem;
            color:rgba(255,255,255,0.82); text-decoration:none; font-size:0.82rem; font-weight:600;
            padding:0.35rem 0.8rem; border-radius:var(--radius-sm);
            background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
            transition:all .2s;
        }
        .back-link:hover { background:rgba(255,255,255,0.18); color:white; }

        /* ─── HERO ───────────────────────────────────────────── */
        .trip-hero {
            position:relative; height:360px; overflow:hidden;
            background:linear-gradient(135deg,var(--navy),var(--mid));
            cursor:zoom-in;
        }
        .trip-hero img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s ease; }
        .trip-hero:hover img { transform:scale(1.03); }
        .hero-overlay {
            position:absolute; bottom:0; left:0; right:0;
            background:linear-gradient(transparent, rgba(0,0,0,0.72));
            padding:1.75rem 1.5rem; pointer-events:none;
        }
        .hero-overlay h1 { color:white; font-size:clamp(1.4rem,4vw,2.2rem); font-weight:800; margin-bottom:0.35rem; text-shadow:0 2px 10px rgba(0,0,0,0.5); }
        .hero-overlay p { color:rgba(255,255,255,0.82); font-size:0.88rem; }
        .zoom-hint {
            position:absolute; top:1rem; right:1rem;
            background:rgba(0,0,0,0.48); color:white;
            padding:4px 11px; border-radius:20px; font-size:0.72rem;
            font-weight:600; backdrop-filter:blur(6px); pointer-events:none;
        }
        .hero-empty { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:6rem; }

        /* ─── GALLERY ────────────────────────────────────────── */
        .gallery {
            display:flex; gap:0.55rem; padding:0.7rem 1rem;
            overflow-x:auto; background:#1a1a2e;
            -webkit-overflow-scrolling:touch;
        }
        .gallery::-webkit-scrollbar { height:3px; }
        .gallery::-webkit-scrollbar-thumb { background:var(--mid); border-radius:3px; }
        .gallery-thumb {
            flex-shrink:0; width:100px; height:68px; border-radius:var(--radius-sm);
            overflow:hidden; cursor:pointer; border:2px solid transparent;
            transition:border-color .2s, transform .2s;
        }
        .gallery-thumb:hover { transform:translateY(-2px); }
        .gallery-thumb.active { border-color:var(--teal); }
        .gallery-thumb img { width:100%; height:100%; object-fit:cover; opacity:0.6; transition:opacity .2s; }
        .gallery-thumb.active img, .gallery-thumb:hover img { opacity:1; }

        /* ─── LIGHTBOX ───────────────────────────────────────── */
        .lightbox {
            position:fixed; inset:0; z-index:9998;
            background:rgba(0,0,0,0.93); display:flex; align-items:center; justify-content:center;
            opacity:0; pointer-events:none; transition:opacity .25s ease;
            backdrop-filter:blur(10px);
        }
        .lightbox.open { opacity:1; pointer-events:all; }
        .lightbox-inner { display:flex; flex-direction:column; align-items:center; gap:1.1rem; max-width:92vw; }
        .lightbox-img-wrap { position:relative; display:flex; align-items:center; justify-content:center; }
        .lightbox-img { max-width:88vw; max-height:78vh; object-fit:contain; border-radius:var(--radius-sm); box-shadow:0 30px 70px rgba(0,0,0,0.7); transition:opacity .2s ease, transform .22s ease; }
        .lightbox-img.fade { opacity:0; transform:scale(0.96); }
        .lb-arrow {
            position:absolute; top:50%; transform:translateY(-50%);
            width:44px; height:44px; border-radius:50%;
            background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
            color:white; font-size:1.1rem; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            transition:background .2s; backdrop-filter:blur(4px); z-index:2;
        }
        .lb-arrow:hover { background:rgba(255,255,255,0.25); }
        .lb-arrow.prev { left:-58px; }
        .lb-arrow.next { right:-58px; }
        .lb-dots { display:flex; gap:0.45rem; }
        .lb-dot { width:7px; height:7px; border-radius:50%; background:rgba(255,255,255,0.3); cursor:pointer; transition:all .2s; }
        .lb-dot.active { background:var(--teal); transform:scale(1.4); }
        .lb-caption { color:rgba(255,255,255,0.55); font-size:0.8rem; }
        .lb-close {
            position:fixed; top:1.1rem; right:1.1rem; width:42px; height:42px; border-radius:50%;
            background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
            color:white; font-size:1rem; cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:background .2s; backdrop-filter:blur(4px); z-index:9999;
        }
        .lb-close:hover { background:rgba(255,255,255,0.25); }

        /* ─── MAIN LAYOUT ────────────────────────────────────── */
        .container { max-width:1000px; margin:0 auto; padding:1.75rem 1.25rem; }
        .content-grid { display:grid; grid-template-columns:1fr 300px; gap:1.75rem; align-items:start; }

        /* ─── CARD ───────────────────────────────────────────── */
        .info-card {
            background:white; border-radius:var(--radius-lg); padding:1.35rem;
            box-shadow:var(--shadow-sm); margin-bottom:1.25rem;
        }
        .info-card:last-child { margin-bottom:0; }
        .card-title {
            font-size:0.95rem; font-weight:700; color:var(--text);
            margin-bottom:1rem; padding-bottom:0.7rem; border-bottom:2px solid var(--bg);
            display:flex; align-items:center; gap:0.45rem;
        }

        /* ─── META GRID ──────────────────────────────────────── */
        .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.7rem; }
        .meta-item { background:var(--bg); border-radius:var(--radius-sm); padding:0.8rem 0.9rem; }
        .meta-label { font-size:0.67rem; color:var(--muted); font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
        .meta-val { font-size:0.9rem; font-weight:700; color:var(--text); margin-top:0.18rem; }

        /* ─── PRICE TABLE ────────────────────────────────────── */
        .price-table { width:100%; border-collapse:collapse; }
        .price-table td { padding:0.58rem 0.7rem; font-size:0.88rem; border-bottom:1px solid var(--border); }
        .price-table td:first-child { color:var(--muted); font-weight:600; }
        .price-table td:last-child { font-weight:800; color:var(--teal); text-align:right; font-size:0.98rem; }
        .price-table tr:last-child td { border-bottom:none; }

        /* ─── ITINERARY ──────────────────────────────────────── */
        .itinerary-timeline { position:relative; padding-left:0; }

        .itin-item {
            display:flex; gap:1rem; margin-bottom:1.35rem;
            padding-bottom:1.35rem; border-bottom:1px solid var(--border);
            position:relative;
        }
        .itin-item:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }

        .itin-left { display:flex; flex-direction:column; align-items:center; gap:0.4rem; flex-shrink:0; }
        .day-badge {
            background:linear-gradient(135deg,var(--navy),var(--mid));
            color:white; border-radius:var(--radius-sm); padding:0.38rem 0.7rem;
            font-size:0.72rem; font-weight:800; white-space:nowrap; text-align:center;
            min-width:58px;
        }
        .day-date-badge {
            background:#e0fdf4; color:#065f46; border:1px solid #6ee7b7;
            border-radius:20px; padding:2px 8px; font-size:0.65rem; font-weight:700;
            white-space:nowrap; text-align:center;
        }
        .time-badge {
            background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;
            border-radius:20px; padding:2px 8px; font-size:0.64rem; font-weight:600;
            white-space:nowrap; text-align:center;
        }

        .itin-right { flex:1; min-width:0; }
        .itin-title { font-size:0.92rem; font-weight:700; color:var(--text); margin-bottom:0.3rem; }
        .itin-desc { font-size:0.82rem; color:var(--muted); line-height:1.65; margin-bottom:0.65rem; }

        /* Itinerary image */
        .itin-img-wrap {
            width:100%; height:160px; border-radius:var(--radius-sm); overflow:hidden;
            margin-top:0.55rem; cursor:zoom-in; background:var(--bg);
        }
        .itin-img-wrap img { width:100%; height:100%; object-fit:cover; transition:transform .3s ease; display:block; }
        .itin-img-wrap:hover img { transform:scale(1.04); }

        /* ─── LIKE BAR ───────────────────────────────────────── */
        .like-bar-wrap { display:flex; align-items:center; gap:0.7rem; flex-wrap:wrap; }
        .like-bar-label { font-size:0.82rem; font-weight:700; color:var(--muted); }
        .btn-vote {
            display:inline-flex; align-items:center; gap:0.4rem;
            padding:0.48rem 1.05rem; border-radius:20px; border:1.5px solid var(--border);
            background:white; color:var(--muted); font-family:inherit; font-size:0.85rem;
            font-weight:700; cursor:pointer; transition:all .2s;
        }
        .btn-vote:hover { opacity:0.85; transform:scale(1.04); }
        .btn-vote.voted-like { background:#276749 !important; color:white !important; border-color:#276749 !important; }
        .btn-vote.voted-dislike { background:#C53030 !important; color:white !important; border-color:#C53030 !important; }
        .login-hint { font-size:0.77rem; color:#a0aec0; }
        .login-hint a { color:var(--teal); font-weight:700; text-decoration:none; }

        /* ─── COMMENTS ───────────────────────────────────────── */
        .comment-form-wrap { background:var(--bg); border-radius:var(--radius-md); padding:1.1rem; margin-bottom:1.25rem; }
        .star-row { display:flex; gap:0.25rem; align-items:center; margin-bottom:0.65rem; }
        .star { font-size:1.25rem; cursor:pointer; opacity:0.3; transition:opacity .15s; user-select:none; }
        .star.on { opacity:1; }
        .star-label { font-size:0.75rem; color:var(--muted); margin-left:0.4rem; }
        .comment-textarea {
            width:100%; padding:0.7rem 0.9rem; border:2px solid var(--border);
            border-radius:var(--radius-sm); font-family:inherit; font-size:0.86rem;
            color:var(--text); resize:vertical; min-height:86px; outline:none;
            transition:border-color .2s;
        }
        .comment-textarea:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(0,212,170,0.1); }
        .comment-form-footer { display:flex; justify-content:space-between; align-items:center; margin-top:0.6rem; flex-wrap:wrap; gap:0.5rem; }
        .char-count { font-size:0.72rem; color:#a0aec0; }
        .btn-comment {
            padding:0.48rem 1.2rem; background:linear-gradient(135deg,var(--navy),var(--mid));
            color:white; border:none; border-radius:var(--radius-sm); font-family:inherit;
            font-size:0.85rem; font-weight:700; cursor:pointer; transition:opacity .2s;
        }
        .btn-comment:disabled { opacity:0.5; cursor:not-allowed; }

        .login-prompt { background:#fffbeb; border:1px solid #fbd38d; border-radius:var(--radius-sm); padding:0.8rem 1rem; font-size:0.85rem; color:#744210; text-align:center; margin-bottom:1rem; }
        .login-prompt a { color:var(--teal); font-weight:700; text-decoration:none; }

        .comment-item { background:white; border-radius:var(--radius-md); padding:0.95rem 1.1rem; margin-bottom:0.7rem; border:1px solid var(--border); }
        .comment-hdr { display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem; flex-wrap:wrap; gap:0.3rem; }
        .comment-author { font-weight:700; font-size:0.85rem; color:var(--text); display:flex; align-items:center; gap:0.45rem; }
        .comment-avatar { width:26px; height:26px; background:linear-gradient(135deg,var(--navy),var(--mid)); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:0.7rem; font-weight:800; flex-shrink:0; }
        .comment-meta-right { display:flex; align-items:center; gap:0.6rem; }
        .comment-time { font-size:0.72rem; color:#a0aec0; }
        .comment-stars { color:#f6ad55; font-size:0.82rem; margin-bottom:0.28rem; }
        .comment-text { font-size:0.85rem; color:#4a5568; line-height:1.65; }
        .btn-del-comment { background:none; border:none; color:#fc8181; cursor:pointer; font-size:0.76rem; font-weight:600; padding:0; }
        .btn-del-comment:hover { color:#e53e3e; }
        .no-comments { text-align:center; padding:2rem 1rem; color:#a0aec0; font-size:0.86rem; }

        /* ─── SIDEBAR ────────────────────────────────────────── */
        .booking-sidebar {
            background:white; border-radius:var(--radius-lg); padding:1.35rem;
            box-shadow:var(--shadow-md); position:sticky; top:76px;
        }
        .sidebar-title { font-size:0.95rem; font-weight:700; color:var(--text); margin-bottom:1.1rem; padding-bottom:0.7rem; border-bottom:2px solid var(--bg); }
        .price-highlight {
            background:linear-gradient(135deg,var(--navy),var(--mid));
            color:white; border-radius:var(--radius-md); padding:1.1rem; margin-bottom:1.1rem; text-align:center;
        }
        .price-highlight .from { font-size:0.7rem; color:rgba(255,255,255,0.65); margin-bottom:0.2rem; }
        .price-highlight .amount { font-size:1.9rem; font-weight:800; color:var(--teal); line-height:1; }
        .price-highlight .per { font-size:0.7rem; color:rgba(255,255,255,0.55); margin-top:0.2rem; }

        .quick-info { margin-bottom:1.1rem; }
        .quick-row { display:flex; justify-content:space-between; align-items:center; padding:0.48rem 0; border-bottom:1px solid #fafafa; font-size:0.83rem; }
        .quick-row:last-child { border-bottom:none; }
        .quick-label { color:var(--muted); }
        .quick-val { font-weight:700; color:var(--text); text-align:right; }

        .btn-book-now {
            display:block; width:100%; padding:0.85rem;
            background:linear-gradient(135deg,var(--teal-dark),var(--teal));
            color:var(--navy); border:none; border-radius:var(--radius-md); font-family:inherit;
            font-size:0.95rem; font-weight:800; cursor:pointer; text-decoration:none; text-align:center;
            transition:all .22s; box-shadow:0 4px 14px rgba(0,212,170,0.28); min-height:48px;
        }
        .btn-book-now:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,212,170,0.4); }
        .btn-closed {
            display:block; width:100%; padding:0.85rem; background:#e2e8f0; color:#a0aec0;
            border:none; border-radius:var(--radius-md); font-family:inherit;
            font-size:0.95rem; font-weight:800; cursor:not-allowed; text-align:center; min-height:48px;
        }
        .btn-back-list {
            display:block; width:100%; padding:0.6rem; background:var(--bg); color:var(--muted);
            border:none; border-radius:var(--radius-sm); font-family:inherit; font-size:0.88rem;
            font-weight:600; cursor:pointer; text-decoration:none; text-align:center; margin-top:0.65rem; transition:all .2s;
        }
        .btn-back-list:hover { background:var(--border); color:var(--text); }

        .company-box { background:var(--bg); border-radius:var(--radius-sm); padding:0.8rem 0.9rem; display:flex; align-items:center; gap:0.7rem; margin-top:0.9rem; }
        .company-box img { width:38px; height:38px; object-fit:cover; border-radius:6px; }
        .company-icon { font-size:1.7rem; }
        .company-name { font-weight:700; font-size:0.82rem; color:var(--text); }
        .company-reg { font-size:0.68rem; color:var(--muted); }

        .status-badge {
            display:inline-block; padding:3px 12px; border-radius:20px; font-size:0.75rem; font-weight:700; margin-bottom:0.9rem;
        }
        .badge-open { background:#e8f5e9; color:#2e7d32; }
        .badge-closed { background:#f0f0f0; color:#718096; }

        /* ─── TOAST ──────────────────────────────────────────── */
        .toast {
            position:fixed; bottom:5rem; left:50%;
            transform:translateX(-50%) translateY(120px);
            background:#2d3748; color:white;
            padding:0.8rem 1.6rem; border-radius:var(--radius-md);
            font-size:0.87rem; font-weight:600;
            box-shadow:0 8px 25px rgba(0,0,0,0.3); z-index:99999;
            transition:transform .35s cubic-bezier(.34,1.56,.64,1);
            display:flex; align-items:center; gap:0.5rem; white-space:nowrap;
        }
        .toast.show { transform:translateX(-50%) translateY(0); }

        /* ─── BOTTOM NAV ─────────────────────────────────────── */
        .bottom-nav { display:none; position:fixed; bottom:0; left:0; right:0; z-index:200; background:white; border-top:1px solid var(--border); box-shadow:0 -3px 20px rgba(0,0,0,0.1); }
        .bottom-nav-inner { display:flex; justify-content:space-around; align-items:stretch; padding:0.3rem 0; }
        .bottom-nav-item { display:flex; flex-direction:column; align-items:center; gap:0.12rem; text-decoration:none; color:#a0aec0; font-size:0.65rem; font-weight:600; padding:0.4rem 0.5rem; border-radius:var(--radius-sm); transition:all .2s; border:none; background:none; cursor:pointer; font-family:inherit; flex:1; min-height:52px; justify-content:center; }
        .bottom-nav-item .bn-icon { font-size:1.3rem; line-height:1; }
        .bottom-nav-item.current { color:var(--navy); }
        .bottom-nav-item.current .bn-icon { background:linear-gradient(135deg,var(--navy),var(--mid)); border-radius:var(--radius-sm); padding:0.22rem 0.55rem; display:inline-block; }
        .bottom-nav-item.danger-item { color:#fc8181; }

        /* ─── RESPONSIVE ─────────────────────────────────────── */
        @media(max-width:768px) {
            .content-grid { grid-template-columns:1fr; }
            .booking-sidebar { position:static; order:1; }
            .main-col { order:2; }
            .meta-grid { grid-template-columns:1fr 1fr; }
            .trip-hero { height:240px; }
            .lb-arrow.prev { left:-36px; width:34px; height:34px; font-size:0.95rem; }
            .lb-arrow.next { right:-36px; width:34px; height:34px; font-size:0.95rem; }
            .bottom-nav { display:block; }
            body { padding-bottom:68px; }
        }
        @media(max-width:480px) {
            .container { padding:1.1rem 1rem; }
            .meta-grid { grid-template-columns:1fr; }
            .itin-img-wrap { height:130px; }
            .trip-hero { height:200px; }
        }
    </style>
</head>
<body>

@php
    $isPast = $trip->date_from < now()->toDateString();
    $images = collect([$trip->pic1_url, $trip->pic2_url, $trip->pic3_url])->filter()->values();

    // ✅ Sort itinerary by day_number
    $itineraries = ($trip->itineraries ?? collect())->sortBy('day_number')->values();
@endphp

<!-- TOAST -->
<div class="toast" id="toast">⛔ Trip ini sudah tidak tersedia untuk tempahan.</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightboxOutside(event)">
    <button class="lb-close" onclick="closeLightbox()">✕</button>
    <div class="lightbox-inner">
        <div class="lightbox-img-wrap">
            <button class="lb-arrow prev" id="lbPrev" onclick="lbNav(-1)">&#8592;</button>
            <img src="" alt="" id="lbImg" class="lightbox-img">
            <button class="lb-arrow next" id="lbNext" onclick="lbNav(1)">&#8594;</button>
        </div>
        <div class="lb-dots" id="lbDots"></div>
        <div class="lb-caption" id="lbCaption"></div>
    </div>
</div>

<!-- HEADER -->
<header class="header">
    <div class="header-brand">✈️ Travel<span>Pro</span></div>
    <a href="{{ url()->previous() }}" class="back-link">← Kembali</a>
</header>

<!-- HERO -->
<div class="trip-hero" onclick="openLightbox(0)">
    @if($images->count())
        <img src="{{ $images[0] }}" alt="{{ $trip->location }}" id="heroImage">
        <div class="zoom-hint">🔍 Klik untuk besar</div>
    @else
        <div class="hero-empty">✈️</div>
    @endif
    @if($isPast)
        <div style="position:absolute;top:1rem;left:1rem;background:rgba(0,0,0,0.58);color:#a0aec0;padding:5px 13px;border-radius:20px;font-size:0.75rem;font-weight:800;backdrop-filter:blur(4px);">
            🔒 Tempahan Ditutup
        </div>
    @endif
    <div class="hero-overlay">
        <h1>{{ $trip->location }}</h1>
        <p>📍 {{ $trip->country }} ({{ $trip->national_code }}) &nbsp;|&nbsp; 🏢 {{ $trip->company->name ?? '-' }}</p>
    </div>
</div>

<!-- GALLERY THUMBNAILS -->
@if($images->count() > 1)
<div class="gallery">
    @foreach($images as $i => $img)
        <div class="gallery-thumb {{ $i===0?'active':'' }}" onclick="selectThumb(this,'{{ $img }}',{{ $i }})">
            <img src="{{ $img }}" alt="Gambar {{ $i+1 }}">
        </div>
    @endforeach
</div>
@endif

<!-- MAIN CONTENT -->
<div class="container">
    <div class="content-grid">

        <!-- LEFT / MAIN COLUMN -->
        <div class="main-col">

            <!-- Trip Info -->
            <div class="info-card">
                <div class="card-title">📋 Maklumat Trip</div>
                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">Destinasi</div>
                        <div class="meta-val">{{ $trip->location }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Negara</div>
                        <div class="meta-val">{{ $trip->country }} ({{ $trip->national_code }})</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Tarikh Berlepas</div>
                        <div class="meta-val">📅 {{ \Carbon\Carbon::parse($trip->date_from)->format('d/m/Y') }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Tarikh Pulang</div>
                        <div class="meta-val">📅 {{ \Carbon\Carbon::parse($trip->date_to)->format('d/m/Y') }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Tempoh</div>
                        <div class="meta-val">⏱️ {{ $trip->duration ?? '-' }} Hari</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Syarikat</div>
                        <div class="meta-val">🏢 {{ $trip->company->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Price List -->
            <div class="info-card">
                <div class="card-title">💰 Senarai Harga</div>
                <table class="price-table">
                    <tr><td>👨 Dewasa</td><td>RM {{ number_format($trip->price_adult,2) }}</td></tr>
                    <tr><td>👶 Kanak-kanak</td><td>RM {{ number_format($trip->price_child,2) }}</td></tr>
                    <tr><td>🍼 Bayi</td><td>RM {{ number_format($trip->price_baby,2) }}</td></tr>
                    <tr><td>💳 Deposit (dewasa)</td><td>RM {{ number_format($trip->deposit,2) }}</td></tr>
                </table>
            </div>

            <!-- ✅ ITINERARY — synced with admin: day_number, day_date, time_from, time_to, img -->
            @if($itineraries->count() > 0)
            <div class="info-card">
                <div class="card-title">🗓️ Itinerari Perjalanan
                    <span style="background:var(--bg);color:var(--muted);padding:2px 9px;border-radius:20px;font-size:0.68rem;font-weight:700;margin-left:0.3rem;">
                        {{ $itineraries->count() }} hari
                    </span>
                </div>
                <div class="itinerary-timeline">
                    @foreach($itineraries as $itin)
                        <div class="itin-item">
                            <!-- LEFT: Day badge + date + time -->
                            <div class="itin-left">
                                <div class="day-badge">Hari {{ $itin->day_number }}</div>

                                {{-- ✅ Papar day_date jika ada --}}
                                @if($itin->day_date)
                                    <div class="day-date-badge">
                                        {{ \Carbon\Carbon::parse($itin->day_date)->format('d/m/Y') }}
                                    </div>
                                @endif

                                {{-- ✅ Papar masa jika ada --}}
                                @if($itin->time_from || $itin->time_to)
                                    <div class="time-badge">
                                        @if($itin->time_from){{ \Carbon\Carbon::parse($itin->time_from)->format('H:i') }}@endif
                                        @if($itin->time_from && $itin->time_to) – @endif
                                        @if($itin->time_to){{ \Carbon\Carbon::parse($itin->time_to)->format('H:i') }}@endif
                                    </div>
                                @endif
                            </div>

                            <!-- RIGHT: Content + image -->
                            <div class="itin-right">
                                <div class="itin-title">{{ $itin->title ?? 'Aktiviti Hari '.$itin->day_number }}</div>

                                @if($itin->description)
                                    <div class="itin-desc">{{ $itin->description }}</div>
                                @endif

                                {{-- ✅ Papar gambar itinerary dari admin --}}
                                @if($itin->img_url)
                                    <div class="itin-img-wrap" onclick="openItinLightbox('{{ $itin->img_url }}')">
                                        <img src="{{ $itin->img_url }}"
                                             alt="Hari {{ $itin->day_number }} — {{ $itin->title }}"
                                             loading="lazy"
                                             onerror="this.closest('.itin-img-wrap').style.display='none'">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Like / Dislike -->
            <div class="info-card">
                <div class="card-title">👍 Penilaian Trip</div>
                <div class="like-bar-wrap">
                    <button id="btnLike" class="btn-vote" onclick="handleVote('like')">
                        ❤️ Suka <span id="likeCount_{{ $trip->id }}">0</span>
                    </button>
                    <button id="btnDislike" class="btn-vote" onclick="handleVote('dislike')">
                        👎 Tidak Suka <span id="dislikeCount_{{ $trip->id }}">0</span>
                    </button>
                    @guest
                        <span class="login-hint">
                            <a href="{{ route('tourist.login') }}">Log masuk</a> untuk bagi penilaian
                        </span>
                    @endguest
                </div>
            </div>

            <!-- Comments -->
            <div class="info-card">
                <div class="card-title">💬 Komen & Ulasan
                    <span style="background:var(--bg);color:var(--muted);padding:2px 9px;border-radius:20px;font-size:0.68rem;font-weight:700;margin-left:0.3rem;" id="commentCount">0</span>
                </div>

                @auth
                    @if(Auth::user()->isTourist())
                    <div class="comment-form-wrap">
                        <div class="star-row" id="starRow">
                            @for($s=1;$s<=5;$s++)
                                <span class="star" data-v="{{ $s }}">⭐</span>
                            @endfor
                            <span class="star-label" id="starLabel">Beri penilaian (pilihan)</span>
                        </div>
                        <textarea class="comment-textarea" id="commentInput"
                            placeholder="Kongsi pengalaman atau soalan anda tentang trip ini..."></textarea>
                        <div class="comment-form-footer">
                            <span class="char-count" id="charCount">0 / 500</span>
                            <button class="btn-comment" id="submitCommentBtn" onclick="submitComment()">Hantar Komen</button>
                        </div>
                    </div>
                    @endif
                @else
                    <div class="login-prompt">
                        <a href="{{ route('tourist.login') }}">Log masuk</a> untuk meninggalkan komen dan rating.
                    </div>
                @endauth

                <div id="commentsList">
                    <div class="no-comments">⏳ Memuatkan komen...</div>
                </div>
            </div>

        </div><!-- /main-col -->

        <!-- SIDEBAR -->
        <div class="booking-sidebar">
            <span class="status-badge {{ $isPast?'badge-closed':'badge-open' }}">
                {{ $isPast ? '🔒 Tempahan Ditutup' : '🟢 Trip Tersedia' }}
            </span>

            <div class="sidebar-title">{{ $isPast ? '💳 Maklumat Harga' : '💳 Buat Tempahan' }}</div>

            <div class="price-highlight">
                <div class="from">Harga bermula dari</div>
                <div class="amount">RM {{ number_format($trip->price_adult,0) }}</div>
                <div class="per">setiap dewasa</div>
            </div>

            <div class="quick-info">
                <div class="quick-row">
                    <span class="quick-label">📅 Berlepas</span>
                    <span class="quick-val">{{ \Carbon\Carbon::parse($trip->date_from)->format('d/m/Y') }}</span>
                </div>
                <div class="quick-row">
                    <span class="quick-label">📅 Pulang</span>
                    <span class="quick-val">{{ \Carbon\Carbon::parse($trip->date_to)->format('d/m/Y') }}</span>
                </div>
                <div class="quick-row">
                    <span class="quick-label">⏱️ Tempoh</span>
                    <span class="quick-val">{{ $trip->duration ?? '-' }} hari</span>
                </div>
                <div class="quick-row">
                    <span class="quick-label">💳 Deposit</span>
                    <span class="quick-val">RM {{ number_format($trip->deposit,2) }}</span>
                </div>
            </div>

            @if($isPast)
                <button class="btn-closed" onclick="showClosedToast()">🔒 Tempahan Ditutup</button>
            @else
                @auth
                    <a href="{{ route('tourist.booking.show',$trip->id) }}" class="btn-book-now">🎉 Tempah Sekarang</a>
                @else
                    <a href="{{ route('tourist.register') }}" class="btn-book-now">🚀 Daftar & Tempah</a>
                    <p style="text-align:center;font-size:0.76rem;color:#a0aec0;margin-top:0.5rem;">
                        Sudah ada akaun? <a href="{{ route('tourist.login') }}" style="color:var(--teal);font-weight:700;">Log masuk</a>
                    </p>
                @endauth
            @endif

            <a href="{{ route('tourist.index') }}" class="btn-back-list">← Lihat Trip Lain</a>

            @if($trip->company)
            <div class="company-box">
                @if(!empty($trip->company->logo_url))
                    <img src="{{ $trip->company->logo_url }}" alt="{{ $trip->company->name }}">
                @else
                    <span class="company-icon">🏢</span>
                @endif
                <div>
                    <div class="company-name">{{ $trip->company->name }}</div>
                    <div class="company-reg">Reg: {{ $trip->company->registration_no ?? '-' }}</div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

<script>
// ─── LIGHTBOX SETUP ───────────────────────────────────────────────────────────
var lbImages = @json($images->values());
var lbCurrent = 0;
var itinLightbox = false; // flag — itin image pakai single image mode

function openLightbox(index) {
    if (!lbImages.length) return;
    itinLightbox = false;
    lbCurrent = index;
    buildDots();
    renderLbImage();
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function openItinLightbox(src) {
    itinLightbox = true;
    var img = document.getElementById('lbImg');
    img.src = src;
    document.getElementById('lbCaption').textContent = '';
    document.getElementById('lbDots').innerHTML = '';
    document.getElementById('lbPrev').style.display = 'none';
    document.getElementById('lbNext').style.display = 'none';
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
}
function closeLightboxOutside(e) { if(e.target.id==='lightbox') closeLightbox(); }

function renderLbImage() {
    var img = document.getElementById('lbImg');
    img.classList.add('fade');
    setTimeout(function(){
        img.src = lbImages[lbCurrent];
        img.classList.remove('fade');
    }, 150);
    document.getElementById('lbCaption').textContent = 'Gambar '+(lbCurrent+1)+' / '+lbImages.length;
    document.querySelectorAll('.lb-dot').forEach(function(d,i){ d.classList.toggle('active',i===lbCurrent); });
    var show = lbImages.length > 1;
    document.getElementById('lbPrev').style.display = show ? 'flex' : 'none';
    document.getElementById('lbNext').style.display = show ? 'flex' : 'none';
}
function lbNav(dir) {
    if(itinLightbox) return;
    lbCurrent = (lbCurrent + dir + lbImages.length) % lbImages.length;
    renderLbImage();
}
function buildDots() {
    var wrap = document.getElementById('lbDots'); wrap.innerHTML = '';
    if(lbImages.length <= 1) return;
    lbImages.forEach(function(_,i){
        var d = document.createElement('div'); d.className='lb-dot'+(i===lbCurrent?' active':'');
        d.onclick=function(){ lbCurrent=i; renderLbImage(); }; wrap.appendChild(d);
    });
}
function selectThumb(el,src,index) {
    var hero=document.getElementById('heroImage'); if(hero) hero.src=src;
    document.querySelectorAll('.gallery-thumb').forEach(function(t){ t.classList.remove('active'); });
    el.classList.add('active'); openLightbox(index);
}
document.addEventListener('keydown',function(e){
    if(!document.getElementById('lightbox').classList.contains('open')) return;
    if(e.key==='Escape') closeLightbox();
    if(e.key==='ArrowLeft'  && !itinLightbox) lbNav(-1);
    if(e.key==='ArrowRight' && !itinLightbox) lbNav(1);
});

// ─── TOAST ────────────────────────────────────────────────────────────────────
function showClosedToast() {
    var t=document.getElementById('toast'); t.classList.add('show');
    setTimeout(function(){ t.classList.remove('show'); },3000);
}
function showToast(msg,bg) {
    var t=document.getElementById('toast'); t.textContent=msg;
    t.style.background=bg||'#2d3748'; t.classList.add('show');
    setTimeout(function(){ t.classList.remove('show'); },3000);
}

// ─── LIKE / DISLIKE ───────────────────────────────────────────────────────────
var tripId   = {{ $trip->id }};
var csrfToken= document.querySelector('meta[name="csrf-token"]').content;
var isLoggedIn={{ Auth::check()?'true':'false' }};
var likeCountEl   = document.getElementById('likeCount_'+tripId);
var dislikeCountEl= document.getElementById('dislikeCount_'+tripId);
var btnLike   = document.getElementById('btnLike');
var btnDislike= document.getElementById('btnDislike');

// Load initial likes
fetch('/tourist/likes',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({trip_ids:[tripId]})})
.then(function(r){return r.json();})
.then(function(data){
    var info=data[tripId]||{};
    if(likeCountEl)    likeCountEl.textContent   =info.likes||0;
    if(dislikeCountEl) dislikeCountEl.textContent=info.dislikes||0;
    if(info.myVote==='like'    && btnLike)    btnLike.classList.add('voted-like');
    if(info.myVote==='dislike' && btnDislike) btnDislike.classList.add('voted-dislike');
}).catch(function(){});

function handleVote(type) {
    if(!isLoggedIn){ showToast('⚠️ Sila log masuk untuk bagi penilaian','#e53e3e'); return; }
    fetch('/tourist/trip/'+tripId+'/like',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({type:type})})
    .then(function(r){
        if(r.status===419){window.location.reload();return null;}
        if(r.status===401){window.location.href='{{ route("tourist.login") }}';return null;}
        return r.json();
    })
    .then(function(data){
        if(!data)return;
        if(likeCountEl)    likeCountEl.textContent   =data.likes;
        if(dislikeCountEl) dislikeCountEl.textContent=data.dislikes;
        [btnLike,btnDislike].forEach(function(b){if(b){b.classList.remove('voted-like','voted-dislike');}});
        if(data.myVote==='like'    && btnLike)    btnLike.classList.add('voted-like');
        if(data.myVote==='dislike' && btnDislike) btnDislike.classList.add('voted-dislike');
    }).catch(function(e){console.error('Like error:',e);});
}

// ─── COMMENTS ────────────────────────────────────────────────────────────────
var selectedRating=0;
var stars=document.querySelectorAll('.star');
stars.forEach(function(star){
    star.addEventListener('mouseover',function(){ var v=parseInt(star.dataset.v); stars.forEach(function(s,i){s.classList.toggle('on',i<v);}); });
    star.addEventListener('mouseout',function(){  stars.forEach(function(s,i){s.classList.toggle('on',i<selectedRating);}); });
    star.addEventListener('click',function(){
        selectedRating=parseInt(star.dataset.v);
        stars.forEach(function(s,i){s.classList.toggle('on',i<selectedRating);});
        var lbl=document.getElementById('starLabel'); if(lbl) lbl.textContent=selectedRating+' bintang';
    });
});

var commentInput=document.getElementById('commentInput');
if(commentInput){
    commentInput.addEventListener('input',function(){
        var cnt=document.getElementById('charCount'); if(cnt) cnt.textContent=commentInput.value.length+' / 500';
    });
}

function loadComments(){
    fetch('/tourist/trip/'+tripId+'/comments',{headers:{'Accept':'application/json'}})
    .then(function(r){return r.json();})
    .then(function(data){
        var cnt=document.getElementById('commentCount'); if(cnt) cnt.textContent=data.length;
        var list=document.getElementById('commentsList');
        if(!data.length){ list.innerHTML='<div class="no-comments">Belum ada komen. Jadilah yang pertama! 😊</div>'; return; }
        list.innerHTML=data.map(function(c){
            var starsHtml=c.rating?'⭐'.repeat(c.rating):'';
            return '<div class="comment-item" id="ci-'+c.id+'">' +
                '<div class="comment-hdr">' +
                    '<div class="comment-author"><div class="comment-avatar">'+c.user_name.charAt(0).toUpperCase()+'</div>'+escHtml(c.user_name)+'</div>' +
                    '<div class="comment-meta-right"><span class="comment-time">'+c.created_at+'</span>'+(c.is_mine?'<button class="btn-del-comment" onclick="deleteComment('+c.id+')">🗑</button>':'')+' </div>' +
                '</div>' +
                (starsHtml?'<div class="comment-stars">'+starsHtml+'</div>':'')+
                '<div class="comment-text">'+escHtml(c.comment)+'</div>'+
            '</div>';
        }).join('');
    })
    .catch(function(){ document.getElementById('commentsList').innerHTML='<div class="no-comments">Gagal memuatkan komen.</div>'; });
}

function submitComment(){
    if(!isLoggedIn) return;
    var text=commentInput?commentInput.value.trim():''; if(!text){ if(commentInput) commentInput.focus(); return; }
    if(text.length>500){ alert('Komen terlalu panjang (max 500 aksara)'); return; }
    var btn=document.getElementById('submitCommentBtn'); if(btn){btn.disabled=true;btn.textContent='Menghantar...';}
    fetch('/tourist/trip/'+tripId+'/comment',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({comment:text,rating:selectedRating||null})})
    .then(function(r){return r.json();})
    .then(function(data){
        if(data.error){alert(data.error);return;}
        if(commentInput) commentInput.value='';
        selectedRating=0; stars.forEach(function(s){s.classList.remove('on');});
        var lbl=document.getElementById('starLabel'); if(lbl) lbl.textContent='Beri penilaian (pilihan)';
        var cnt=document.getElementById('charCount'); if(cnt) cnt.textContent='0 / 500';
        loadComments();
    })
    .catch(function(){alert('Gagal menghantar komen.');})
    .finally(function(){ if(btn){btn.disabled=false;btn.textContent='Hantar Komen';} });
}

function deleteComment(commentId){
    if(!confirm('Padam komen ini?')) return;
    fetch('/tourist/comment/'+commentId,{method:'DELETE',credentials:'same-origin',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json();})
    .then(function(){
        var el=document.getElementById('ci-'+commentId); if(el) el.remove();
        var cnt=document.getElementById('commentCount'); if(cnt) cnt.textContent=Math.max(0,parseInt(cnt.textContent)-1);
    });
}

function escHtml(str){ return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

loadComments();
</script>

@auth
    @if(Auth::user()->isTourist())
    <nav class="bottom-nav">
        <div class="bottom-nav-inner">
            <a href="{{ route('tourist.index') }}" class="bottom-nav-item"><span class="bn-icon">🌏</span><span>Trip</span></a>
            <a href="{{ route('tourist.dashboard') }}" class="bottom-nav-item"><span class="bn-icon">🏠</span><span>Dashboard</span></a>
            <a href="{{ route('tourist.my-bookings') }}" class="bottom-nav-item"><span class="bn-icon">📋</span><span>Booking</span></a>
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