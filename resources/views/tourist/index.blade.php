<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Pelancong — Cari Trip Impian Anda</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            --radius-lg: 18px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.07);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.10);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.14);
        }

        * { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
        html { scroll-behavior: smooth; }
        body { 
            font-family:'Poppins',sans-serif; 
            background:var(--bg); 
            color:var(--text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        button, a, .clickable { min-height: 44px; min-width: 44px; }
        .trip-card, .btn-like, .btn-dislike, .status-tab {
            -webkit-user-select: none; user-select: none;
        }

        /* ─── HEADER ─────────────────────────────────────────── */
        .header {
            background:linear-gradient(135deg,var(--navy),var(--mid));
            color:white; padding:0.85rem 1.25rem;
            display:flex; justify-content:space-between; align-items:center;
            position:sticky; top:0; z-index:200;
            box-shadow:0 2px 20px rgba(0,0,0,0.3);
            gap: 0.75rem;
        }
        .header-brand { font-size:1.25rem; font-weight:800; letter-spacing:-0.5px; white-space:nowrap; flex-shrink:0; }
        .header-brand span { color:var(--teal); }
        .header-nav { display:flex; gap:0.5rem; align-items:center; flex-wrap:nowrap; }
        .btn-nav {
            padding:0.4rem 0.9rem; border-radius:var(--radius-sm); font-family:inherit;
            font-size:0.82rem; font-weight:600; cursor:pointer; text-decoration:none;
            transition:all 0.2s; border:none; white-space:nowrap;
        }
        .btn-outline { background:transparent; border:1.5px solid rgba(255,255,255,0.35); color:white; }
        .btn-outline:hover { background:rgba(255,255,255,0.12); border-color:white; }
        .btn-primary-nav { background:var(--teal); color:var(--navy); border:1.5px solid var(--teal); }
        .btn-primary-nav:hover { background:var(--teal-dark); }
        .btn-danger-nav { background:rgba(229,62,62,0.15); color:#fc8181; border:1.5px solid rgba(252,129,129,0.35); }
        .header-user {
            font-size:0.78rem; color:rgba(255,255,255,0.7);
            padding:0.28rem 0.65rem; background:rgba(255,255,255,0.09);
            border-radius:20px; white-space:nowrap; overflow:hidden;
            text-overflow:ellipsis; max-width:130px;
        }
        .header-user strong { color:var(--teal); }
        .header-nav-links { display:flex; gap:0.5rem; align-items:center; }

        @media(max-width:768px) {
            .header { padding:0.7rem 1rem; }
            .header-brand { font-size:1.05rem; }
            .header-nav-links { display:none; }
            .header-user { max-width:100px; font-size:0.72rem; }
            .btn-nav { padding:0.35rem 0.75rem; font-size:0.78rem; }
            .header-nav { gap:0.4rem; }
        }
        @media(max-width:400px) {
            .header-brand { font-size:0.95rem; }
            .header-user { display:none; }
            .btn-nav { padding:0.32rem 0.6rem; font-size:0.74rem; }
        }

        /* ─── NAV STRIP ─────────────────────────────────────── */
        .nav-strip {
            background:white; border-bottom:1px solid var(--border);
            padding:0.5rem 1.25rem;
            display:flex; align-items:center; gap:0.4rem;
            overflow-x:auto; white-space:nowrap;
            -webkit-overflow-scrolling: touch;
        }
        .nav-strip::-webkit-scrollbar { display:none; }
        .nav-strip-link {
            display:inline-flex; align-items:center; gap:0.35rem;
            padding:0.4rem 0.9rem; border-radius:20px;
            font-family:inherit; font-size:0.82rem; font-weight:600;
            text-decoration:none; color:var(--muted);
            transition:all 0.2s; white-space:nowrap; border:none;
            background:transparent; cursor:pointer;
        }
        .nav-strip-link:hover { background:var(--bg); color:var(--text); }
        .nav-strip-link.current { background:linear-gradient(135deg,var(--navy),var(--mid)); color:white; }
        .nav-strip-divider { color:var(--border); }

        /* ─── HERO ───────────────────────────────────────────── */
        @keyframes heroGradient { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
        @keyframes floatUp { 0%{transform:translateY(0) scale(1);opacity:.15} 50%{transform:translateY(-60px) scale(1.1);opacity:.35} 100%{transform:translateY(-120px) scale(.8);opacity:0} }
        @keyframes fadeInUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        @keyframes shimmer { 0%{background-position:-200% center} 100%{background-position:200% center} }

        .hero {
            background:linear-gradient(-45deg,#0f2027,#1a3a4a,#203a43,#0d3b2e,#2c5364);
            background-size:400% 400%;
            animation:heroGradient 12s ease infinite;
            color:white; padding:3.5rem 1.25rem 3rem; text-align:center;
            position:relative; overflow:hidden;
        }
        .particle { position:absolute; border-radius:50%; background:rgba(0,212,170,0.18); pointer-events:none; animation:floatUp linear infinite; }
        .hero-content { position:relative; z-index:2; animation:fadeInUp 0.7s ease both; }
        .hero h1 { font-size:clamp(1.55rem,5vw,2.8rem); font-weight:800; margin-bottom:0.75rem; line-height:1.2; }
        .hero h1 span { color:var(--teal); }
        .hero p { font-size:0.95rem; color:rgba(255,255,255,0.72); max-width:540px; margin:0 auto 2rem; line-height:1.6; }

        .hero-stats {
            display:flex; justify-content:center;
            background:rgba(255,255,255,0.07);
            border:1px solid rgba(255,255,255,0.12);
            border-radius:16px; padding:0.4rem;
            max-width:600px; margin:0 auto;
            backdrop-filter:blur(10px);
            flex-wrap: wrap;
        }
        .stat { text-align:center; flex:1; min-width:90px; padding:0.85rem 0.75rem; border-right:1px solid rgba(255,255,255,0.1); }
        .stat:last-child { border-right:none; }
        .stat-num {
            font-size:1.9rem; font-weight:800; line-height:1; margin-bottom:0.2rem;
            background:linear-gradient(90deg,var(--teal),#00ffcc,var(--teal));
            background-size:200% auto; -webkit-background-clip:text;
            -webkit-text-fill-color:transparent; background-clip:text;
            animation:shimmer 3s linear infinite;
        }
        .stat-num.live-stat {
            font-size:1.6rem;
        }
        .stat-label { font-size:0.68rem; color:rgba(255,255,255,0.52); font-weight:500; }

        /* Live pulse dot */
        @keyframes livePulse {
            0%,100% { transform:scale(1); opacity:1; }
            50% { transform:scale(1.6); opacity:0.5; }
        }
        .live-dot {
            display:inline-block; width:7px; height:7px; border-radius:50%;
            background:#00ffaa; margin-right:4px; vertical-align:middle;
            animation:livePulse 1.4s ease-in-out infinite;
        }

        /* ─── TRUST BAR ──────────────────────────────────────── */
        .trust-bar {
            background:white; border-bottom:1px solid var(--border);
            padding:0.6rem 1.25rem;
            display:flex; align-items:center; justify-content:center;
            gap:0; overflow-x:auto; -webkit-overflow-scrolling:touch;
        }
        .trust-bar::-webkit-scrollbar { display:none; }
        .trust-item {
            display:flex; align-items:center; gap:0.4rem;
            font-size:0.75rem; font-weight:600; color:var(--muted);
            white-space:nowrap; padding:0.3rem 1rem;
            border-right:1px solid var(--border);
        }
        .trust-item:last-child { border-right:none; }
        .trust-item .icon { font-size:0.95rem; }
        .trust-item strong { color:var(--text); }

        /* ─── VIDEO SECTION ─────────────────────────────────── */
        @keyframes floatFlag { 
            0%, 100% { transform: translateY(0) rotate(0deg); } 
            50% { transform: translateY(-12px) rotate(2deg); } 
        }
        @keyframes fadeInScale { 
            from { opacity: 0; transform: scale(0.95); } 
            to { opacity: 1; transform: scale(1); } 
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 20px 60px rgba(0,212,170,0.15); }
            50% { box-shadow: 0 25px 80px rgba(0,212,170,0.25); }
        }

        .video-section {
            padding: 4rem 1.25rem;
            background: linear-gradient(180deg, #f7fafc 0%, white 40%, white 60%, #f7fafc 100%);
            position: relative; overflow: hidden;
        }
        .video-section::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, var(--teal), transparent);
            opacity: 0.4;
        }

        /* Video playing indicator */
        .video-playing-notice {
            display: none;
            background: linear-gradient(135deg, rgba(0,212,170,0.15), rgba(0,212,170,0.05));
            border: 1px solid rgba(0,212,170,0.4);
            border-radius: var(--radius-sm);
            padding: 0.5rem 1rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--teal-dark);
            text-align: center;
            margin-top: 1rem;
        }
        .video-playing-notice.show { display: block; }

        .video-container {
            max-width: 940px; margin: 0 auto;
            animation: fadeInScale 0.8s ease both;
        }
        .flag-malaysia {
            text-align: center; margin-bottom: 1.3rem;
            animation: floatFlag 3s ease-in-out infinite;
        }
        .flag-malaysia img {
            width: 115px; height: auto; border-radius: 16px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.15); border: 3px solid white;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .flag-malaysia:hover img { transform: scale(1.1) rotate(-2deg); box-shadow: 0 15px 50px rgba(0,0,0,0.2); }

        .video-header { margin-bottom: 2.2rem; text-align: center; }
        .video-header h2 {
            font-size: clamp(1.7rem, 4.5vw, 2.4rem); font-weight: 900; color: var(--navy);
            margin-bottom: 0.8rem; line-height: 1.2;
            background: linear-gradient(135deg, var(--navy) 0%, var(--mid) 50%, var(--teal) 100%);
            background-size: 200% auto; -webkit-background-clip: text;
            -webkit-text-fill-color: transparent; background-clip: text;
        }
        .video-header p {
            font-size: 1.02rem; color: var(--muted); line-height: 1.75;
            max-width: 680px; margin: 0 auto; font-weight: 400;
        }

        .video-wrapper {
            position: relative; width: 100%; padding-top: 56.25%;
            border-radius: 22px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            border: 5px solid white;
            background: linear-gradient(135deg, var(--navy), var(--mid));
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            touch-action: manipulation;
        }
        .video-wrapper:hover { transform: translateY(-10px) scale(1.015); animation: pulseGlow 2s ease-in-out infinite; }
        @media(max-width:768px) { .video-wrapper:hover { transform: none; animation: none; } }
        .video-wrapper::before {
            content: '▶'; position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%); font-size: 5rem;
            color: white; opacity: 0.25; z-index: 1; pointer-events: none;
            transition: all 0.3s ease;
        }
        .video-wrapper:hover::before { opacity: 0; transform: translate(-50%, -50%) scale(1.3); }
        .video-wrapper iframe {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; z-index: 2; border: none; pointer-events: auto;
        }

        .video-features {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem; margin-top: 2.5rem; padding-top: 2.5rem;
            border-top: 1px solid var(--border);
        }
        .video-feature {
            text-align: center; padding: 1.3rem 1rem; background: white;
            border-radius: var(--radius-md); border: 2px solid var(--border);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative; overflow: hidden;
        }
        .video-feature::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0,212,170,0.1), transparent);
            transition: left 0.5s ease;
        }
        .video-feature:hover::before { left: 100%; }
        .video-feature:hover { transform: translateY(-6px); border-color: var(--teal); box-shadow: 0 12px 30px rgba(0,212,170,0.2); }
        .video-feature-icon { font-size: 2.5rem; margin-bottom: 0.6rem; display: block; transition: transform 0.3s ease; }
        .video-feature:hover .video-feature-icon { transform: scale(1.15) rotate(-5deg); }
        .video-feature-text { font-size: 0.9rem; font-weight: 700; color: var(--text); line-height: 1.4; }

        @media(max-width:768px) {
            .video-section { padding: 3rem 1rem; }
            .flag-malaysia img { width: 90px; }
            .video-header h2 { font-size: 1.6rem; }
            .video-header p { font-size: 0.92rem; }
            .video-wrapper { border-radius: 18px; border-width: 4px; }
            .video-features { grid-template-columns: 1fr 1fr; gap: 1rem; }
            .video-feature { padding: 1rem 0.75rem; }
            .video-feature-icon { font-size: 2rem; }
            .video-feature-text { font-size: 0.82rem; }
        }

        /* ─── SEARCH BAR ─────────────────────────────────────── */
        .search-bar {
            background:white; padding:1.1rem 1.25rem;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
            display:flex; gap:0.65rem; align-items:flex-end; flex-wrap:wrap;
        }
        .search-group { position:relative; flex:2; min-width:200px; }
        .search-group label, .filter-group label { font-size:0.68rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; display:block; margin-bottom:0.3rem; }
        .search-input {
            width:100%; padding:0.7rem 1rem 0.7rem 2.5rem;
            border:2px solid var(--border); border-radius:var(--radius-sm);
            font-family:inherit; font-size:0.9rem; outline:none;
            transition:border-color 0.2s; color:var(--text);
        }
        .search-input:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(0,212,170,0.1); }
        .search-icon { position:absolute; left:0.85rem; bottom:0.73rem; font-size:0.95rem; pointer-events:none; }
        .search-spinner {
            position:absolute; right:0.85rem; bottom:0.76rem;
            width:15px; height:15px; border:2px solid var(--border);
            border-top-color:var(--teal); border-radius:50%;
            animation:spin .5s linear infinite; display:none;
        }
        @keyframes spin { to{transform:rotate(360deg)} }

        .filter-group { display:flex; flex-direction:column; gap:0.3rem; min-width:150px; flex:1; }
        .filter-select {
            padding:0.7rem 1rem; border:2px solid var(--border); border-radius:var(--radius-sm);
            font-family:inherit; font-size:0.88rem; color:var(--text);
            outline:none; cursor:pointer; background:white; transition:border-color 0.2s;
            appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23718096' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 0.9rem center; padding-right:2.2rem;
        }
        .filter-select:focus { border-color:var(--teal); }

        .status-tabs { display:flex; gap:0.4rem; flex-wrap:wrap; align-items:flex-end; }
        .status-tab {
            padding:0.45rem 0.85rem; border-radius:20px; font-family:inherit;
            font-size:0.78rem; font-weight:600; cursor:pointer;
            border:1.5px solid var(--border); background:white; color:var(--muted);
            transition:all 0.2s; white-space:nowrap; touch-action:manipulation;
            -webkit-tap-highlight-color:transparent; user-select:none;
            min-height:36px; display:inline-flex; align-items:center; justify-content:center;
        }
        .status-tab:active { transform:scale(0.96); }
        .status-tab.active { background:linear-gradient(135deg,var(--navy),var(--mid)); color:white; border-color:transparent; }

        .btn-reset-search {
            padding:0.7rem 1rem; background:var(--bg); color:var(--muted);
            border:1.5px solid var(--border); border-radius:var(--radius-sm);
            font-family:inherit; font-weight:600; font-size:0.84rem;
            cursor:pointer; white-space:nowrap; align-self:flex-end;
            transition:all 0.2s; display:none;
        }
        .btn-reset-search:hover { background:var(--border); color:var(--text); }

        .filter-tags-wrap { display:flex; gap:0.45rem; flex-wrap:wrap; padding:0.55rem 1.25rem 0; }
        .filter-tag {
            background:#e0fdf4; color:#065f46; border:1px solid #6ee7b7;
            padding:3px 10px 3px 12px; border-radius:20px;
            font-size:0.75rem; font-weight:600; display:flex; align-items:center; gap:0.35rem;
        }
        .filter-tag button { background:none; border:none; color:#059669; cursor:pointer; font-size:0.9rem; line-height:1; }

        /* ─── CONTAINER ──────────────────────────────────────── */
        .container { max-width:1200px; margin:0 auto; padding:1.5rem 1.25rem; }

        .section-header { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.5rem; }
        .section-label {
            display:inline-flex; align-items:center; gap:0.4rem;
            background:linear-gradient(135deg,#e0fdf4,#ccfbf1);
            color:#065f46; padding:3px 12px; border-radius:20px;
            font-size:0.72rem; font-weight:700; margin-bottom:0.45rem;
            border:1px solid #6ee7b7;
        }
        .section-title { font-size:1.4rem; font-weight:800; color:var(--text); }
        .result-info { font-size:0.82rem; color:var(--muted); }
        .result-info strong { color:var(--text); }

        /* ─── TRIPS GRID ─────────────────────────────────────── */
        .trips-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(288px,1fr)); gap:1.25rem; }

        /* ─── TRIP CARD ──────────────────────────────────────── */
        @keyframes revealCard { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        @keyframes fireGlow {
            0%,100%{box-shadow:0 0 10px 2px rgba(255,100,0,.55),0 0 30px 6px rgba(255,60,0,.3),0 0 55px 12px rgba(255,150,0,.15);border-color:rgba(255,120,0,.65)}
            40%{box-shadow:0 0 16px 5px rgba(255,50,0,.7),0 0 42px 12px rgba(255,80,0,.38),0 0 70px 20px rgba(255,200,0,.18);border-color:rgba(255,55,0,.9)}
        }
        @keyframes badgePulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.08)} }
        @keyframes fireRise { 0%{transform:translateY(0) scale(1);opacity:.9} 100%{transform:translateY(-44px) scale(.3);opacity:0} }
        @keyframes likePopHeart { 0%{transform:scale(1)} 30%{transform:scale(1.5) rotate(-10deg)} 60%{transform:scale(1.25) rotate(6deg)} 100%{transform:scale(1)} }
        @keyframes dislikeShake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-5px)} 60%{transform:translateX(5px)} 80%{transform:translateX(-3px)} }
        @keyframes countBounce { 0%,100%{transform:translateY(0) scale(1)} 40%{transform:translateY(-6px) scale(1.3)} 70%{transform:translateY(2px) scale(.95)} }
        @keyframes floatEmoji { 0%{transform:translateY(0) scale(1);opacity:1} 100%{transform:translateY(-55px) scale(1.4);opacity:0} }
        @keyframes rippleOut { 0%{transform:translate(-50%,-50%) scale(.7);opacity:.7} 100%{transform:translate(-50%,-50%) scale(2.5);opacity:0} }

        .trip-card {
            background:white; border-radius:var(--radius-lg); overflow:hidden;
            box-shadow:var(--shadow-sm);
            transition:transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s ease, border-color .3s ease;
            cursor:pointer; border:2px solid transparent;
            opacity:1; transform:translateY(0);
        }
        .trip-card.will-animate { opacity:0; transform:translateY(24px); }
        .trip-card.revealed { animation:revealCard .45s cubic-bezier(.34,1.56,.64,1) forwards; }
        .trip-card:hover { transform:translateY(-6px) scale(1.01); box-shadow:var(--shadow-lg); border-color:rgba(0,212,170,0.3); }
        .trip-card.is-past { filter:opacity(0.82); }
        .trip-card.is-past.revealed { opacity:0.82 !important; }
        .trip-card.on-fire { border:2px solid rgba(255,120,0,.65) !important; animation:fireGlow 1.9s ease-in-out infinite !important; }

        .fire-particles { position:absolute; bottom:0; left:0; right:0; height:60px; pointer-events:none; overflow:hidden; z-index:4; }
        .fire-particle { position:absolute; bottom:2px; animation:fireRise linear infinite; pointer-events:none; }

        .popular-badge {
            position:absolute; top:10px; right:10px;
            background:linear-gradient(135deg,#ff4500,#ff8c00,#ffd700);
            background-size:200% 200%; animation:badgePulse 1.6s ease infinite;
            color:white; padding:4px 11px; border-radius:20px;
            font-size:0.68rem; font-weight:800; box-shadow:0 2px 10px rgba(255,80,0,.5); z-index:5;
        }
        .new-badge {
            position:absolute; top:10px; right:10px;
            background:linear-gradient(135deg,#667eea,#764ba2);
            color:white; padding:4px 10px; border-radius:20px;
            font-size:0.68rem; font-weight:800; z-index:2;
        }

        @keyframes urgentPulse {
            0%,100% { transform:scale(1); box-shadow:0 2px 10px rgba(239,68,68,0.5); }
            50% { transform:scale(1.05); box-shadow:0 4px 20px rgba(239,68,68,0.8); }
        }
        @keyframes urgentGlow {
            0%,100% { border-color:rgba(239,68,68,0.6); }
            50% { border-color:rgba(239,68,68,1); }
        }

        .countdown-badge {
            position:absolute; top:50px; right:10px;
            background:linear-gradient(135deg,#f59e0b,#f97316);
            color:white; padding:4px 10px; border-radius:20px;
            font-size:0.68rem; font-weight:800; z-index:3;
            box-shadow:0 2px 10px rgba(245,158,11,0.5);
        }
        .countdown-badge-urgent { background:linear-gradient(135deg,#ef4444,#dc2626) !important; animation:urgentPulse 1.5s ease-in-out infinite; }
        .trip-card.trip-starting-soon { border:2px solid rgba(245,158,11,0.5) !important; }
        .trip-card.trip-starting-tomorrow { border:2px solid rgba(239,68,68,0.6) !important; animation:urgentGlow 2s ease-in-out infinite !important; }

        .trip-img-wrap {
            width:100%; height:195px; overflow:hidden; position:relative;
            background:linear-gradient(135deg,#203a43,var(--mid));
            display:flex; align-items:center; justify-content:center; font-size:3.5rem;
        }
        .trip-img-wrap img {
            width:100%; height:100%; object-fit:cover;
            transition:opacity .6s ease, transform .4s ease;
            position:absolute; top:0; left:0;
        }
        .trip-card:hover .trip-img-wrap img { transform:scale(1.05); }

        .img-dots { position:absolute; bottom:8px; left:50%; transform:translateX(-50%); display:flex; gap:5px; z-index:3; opacity:0; transition:opacity .2s; }
        .trip-card:hover .img-dots { opacity:1; }
        .img-dot { width:5px; height:5px; border-radius:50%; background:rgba(255,255,255,0.5); transition:all .2s; }
        .img-dot.active { background:white; transform:scale(1.3); }
        .img-counter { position:absolute; bottom:10px; right:10px; z-index:3; background:rgba(0,0,0,0.5); color:white; padding:2px 8px; border-radius:20px; font-size:0.66rem; font-weight:700; backdrop-filter:blur(4px); opacity:0; transition:opacity .2s; }
        .trip-card:hover .img-counter { opacity:1; }

        .trip-body { padding:1.1rem; }
        .trip-company { font-size:0.7rem; color:var(--teal); font-weight:700; text-transform:uppercase; letter-spacing:.04em; margin-bottom:0.35rem; }
        .trip-title { font-size:1rem; font-weight:700; color:var(--text); margin-bottom:0.45rem; line-height:1.3; }
        .trip-meta { font-size:0.78rem; color:var(--muted); margin-bottom:0.25rem; display:flex; align-items:center; gap:0.3rem; }

        .trip-footer { display:flex; justify-content:space-between; align-items:center; padding-top:0.9rem; border-top:1px solid var(--border); margin-top:0.9rem; }
        .trip-price { font-size:1.25rem; font-weight:800; color:var(--teal); }
        .trip-price-label { font-size:0.67rem; color:var(--muted); }

        .btn-book {
            padding:0.5rem 1.1rem;
            background:linear-gradient(135deg,var(--teal-dark),var(--teal));
            color:var(--navy); border:none; border-radius:var(--radius-sm); font-family:inherit;
            font-size:0.82rem; font-weight:800; cursor:pointer; text-decoration:none;
            display:inline-block; transition:all .22s;
            box-shadow:0 3px 10px rgba(0,212,170,0.28);
        }
        .btn-book:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,212,170,0.45); }
        .btn-view {
            padding:0.5rem 1.1rem; background:var(--bg); color:var(--muted);
            border:none; border-radius:var(--radius-sm); font-family:inherit;
            font-size:0.82rem; font-weight:700; cursor:pointer; text-decoration:none; display:inline-block; transition:all .2s;
        }
        .btn-view:hover { background:var(--border); color:#4a5568; }

        /* ─── LIKE BAR ───────────────────────────────────────── */
        .like-bar { display:flex; gap:0.45rem; padding:0.7rem 1.1rem 0; border-top:1px solid #f0f0f0; margin-top:0.7rem; }
        .btn-like, .btn-dislike {
            display:flex; align-items:center; gap:0.3rem;
            padding:0.32rem 0.8rem; border-radius:20px; border:1.5px solid var(--border);
            background:white; font-family:inherit; font-size:0.77rem; font-weight:600;
            cursor:pointer; transition:all .2s; color:var(--muted); position:relative;
            min-height:36px; touch-action:manipulation; -webkit-tap-highlight-color:transparent; user-select:none;
        }
        .btn-like:active, .btn-dislike:active { transform:scale(0.95); }
        .btn-like:hover { border-color:#e53e3e; color:#e53e3e; background:#fff5f5; }
        .btn-dislike:hover { border-color:var(--muted); color:#4a5568; background:var(--bg); }
        .btn-like.active,.btn-like.voted { border-color:#e53e3e !important; color:#e53e3e !important; background:#fff5f5 !important; }
        .btn-dislike.active,.btn-dislike.voted { border-color:#4a5568 !important; color:#4a5568 !important; background:#edf2f7 !important; }
        .btn-like.pop-anim { animation:likePopHeart .45s cubic-bezier(.34,1.56,.64,1) both; }
        .btn-dislike.pop-anim { animation:dislikeShake .38s ease both; }
        .like-count { font-weight:800; }
        .like-count.bounce { animation:countBounce .42s cubic-bezier(.34,1.56,.64,1) both; }
        .float-emoji { pointer-events:none; z-index:9999; font-size:1.3rem; animation:floatEmoji .7s ease-out forwards; user-select:none; }
        .like-ripple,.dislike-ripple { position:absolute; border-radius:50%; width:36px; height:36px; top:50%; left:50%; pointer-events:none; z-index:10; }
        .like-ripple { border:2px solid rgba(229,62,62,0.5); animation:rippleOut .5s ease-out forwards; }
        .dislike-ripple { border:2px solid rgba(113,128,150,0.4); animation:rippleOut .5s ease-out forwards; }

        /* ─── NO RESULTS / EMPTY ────────────────────────────── */
        .no-results { display:none; text-align:center; padding:3.5rem 1.5rem; color:#a0aec0; background:white; border-radius:var(--radius-lg); }
        .no-results .emoji { font-size:3.5rem; margin-bottom:0.75rem; display:block; }
        .empty-state { text-align:center; padding:3.5rem 1.5rem; color:#a0aec0; }
        .empty-state .icon { font-size:4.5rem; margin-bottom:0.85rem; display:block; }

        /* ─── CTA BANNER ─────────────────────────────────────── */
        .cta-banner {
            background:linear-gradient(135deg,var(--navy),var(--mid));
            color:white; padding:2.5rem 1.5rem; text-align:center;
            margin-top:2.5rem; border-radius:var(--radius-lg);
        }
        .cta-banner h2 { font-size:1.5rem; font-weight:800; margin-bottom:0.65rem; }
        .cta-banner p { color:rgba(255,255,255,0.72); margin-bottom:1.25rem; font-size:0.92rem; }
        .btn-cta {
            padding:0.8rem 2rem; background:var(--teal); color:var(--navy);
            border:none; border-radius:var(--radius-sm); font-family:inherit; font-size:0.95rem;
            font-weight:800; cursor:pointer; text-decoration:none; display:inline-block; transition:all .2s;
        }
        .btn-cta:hover { background:var(--teal-dark); transform:translateY(-2px); }

        /* ─── FOOTER ─────────────────────────────────────────── */
        .footer {
            background:linear-gradient(135deg,var(--navy),var(--mid));
            color:rgba(255,255,255,0.5);
            text-align:center; padding:1.75rem 1.25rem; margin-top:2.5rem; font-size:0.82rem;
        }
        .footer-inner { display:flex; justify-content:center; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:0.6rem; }
        .footer-link { color:rgba(255,255,255,0.4); text-decoration:none; font-size:0.78rem; transition:color .2s; }
        .footer-link:hover { color:var(--teal); }
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

        /* ─── BOTTOM NAV ─────────────────────────────────────── */
        .bottom-nav { 
            position:fixed; bottom:0; left:0; right:0; z-index:200; 
            background:var(--navy); box-shadow:0 -2px 16px rgba(0,0,0,.25); 
            display:none; /* Hidden by default on desktop */
        }
        .bottom-nav-inner { display:flex; align-items:stretch; max-width:820px; margin:0 auto; }
        .bottom-nav-item {
            flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center;
            gap:.18rem; padding:.55rem .25rem .5rem; color:rgba(255,255,255,.58); text-decoration:none;
            font-size:.65rem; font-weight:600; background:none; border:none; cursor:pointer;
            font-family:'Poppins',sans-serif; transition:color .18s,background .18s;
        }
        .bottom-nav-item:hover,.bottom-nav-item.current { color:var(--teal); }
        .bottom-nav-item.danger-item:hover { color:#fc8181; }
        .bn-icon { font-size:1.25rem; line-height:1; }

        /* ─── RESPONSIVE ─────────────────────────────────────── */
        @media(max-width:768px) {
            .bottom-nav { display:block; } /* Show on mobile */
            body { padding-bottom:68px; } /* Add padding on mobile only */
            .nav-strip { display:none; }
            .header-nav { gap:0.35rem; }
            .btn-nav { padding:0.35rem 0.7rem; font-size:0.78rem; }
            .hero { padding:2.5rem 1rem 2.2rem; }
            .search-bar { padding:0.9rem 1rem; gap:0.55rem; }
            .filter-group { min-width:calc(50% - 0.28rem); }
            .status-tabs { gap:0.3rem; }
            .status-tab { padding:0.38rem 0.7rem; font-size:0.75rem; }
            .container { padding:1.1rem 1rem; }
            .trips-grid { grid-template-columns:1fr; gap:1rem; }
            .trust-item { padding:0.3rem 0.65rem; font-size:0.7rem; }
            .trip-card { -webkit-tap-highlight-color:rgba(0,212,170,0.1); touch-action:manipulation; }
            .trip-card:active { transform:scale(0.98); transition:transform 0.1s; }
            .img-dots { opacity:1 !important; }
            .img-counter { opacity:1 !important; }
            .countdown-badge { font-size:0.64rem; padding:3px 8px; top:45px; }
            .stat-num.live-stat { font-size:1.3rem; }
        }
        @media(max-width:480px) {
            .header { padding:0.75rem 1rem; }
            .header-brand { font-size:1.1rem; }
            .hero h1 { font-size:1.45rem; }
            .hero p { font-size:0.88rem; }
            .stat-num { font-size:1.65rem; }
            .stat-label { font-size:0.62rem; }
            .filter-group { min-width:100%; }
            .search-group { min-width:100%; }
            .trips-grid { grid-template-columns:1fr; }
            .status-tabs { width:100%; }
            .status-tab { flex:1; text-align:center; font-size:0.72rem; padding:0.38rem 0.5rem; }
        }
        @media(min-width:640px) and (max-width:1023px) {
            .trips-grid { grid-template-columns:repeat(2,1fr); }
        }
    </style>
</head>
<body>

@php $today = now()->toDateString(); @endphp

<!-- HEADER -->
<header class="header">
    <div class="header-brand">✈️ Arson<span>Trip</span></div>
    <nav class="header-nav">
        @auth
            <span class="header-user">👤 <strong>{{ Auth::user()->name }}</strong></span>
            <div class="header-nav-links">
            </div>
        @else
            <a href="{{ route('tourist.login') }}" class="btn-nav btn-outline">Log Masuk</a>
            <a href="{{ route('tourist.register') }}" class="btn-nav btn-primary-nav">Daftar</a>
        @endauth
    </nav>
</header>


<!-- HERO -->
{{-- TOP NAV: desktop sahaja, gantikan bottom nav --}}
@auth
    @if(Auth::user()->isTourist())
    <nav class="top-nav">
        <a href="{{ route('tourist.index') }}" class="current">🌏 Trip</a>
        <a href="{{ route('tourist.dashboard') }}">🏠 Dashboard</a>
        <a href="{{ route('tourist.my-bookings') }}">📋 Booking</a>
        <form method="POST" action="{{ route('tourist.logout') }}">
            @csrf
            <button type="submit" class="tn-danger">🚪 Keluar</button>
        </form>
    </nav>
    @endif
@endauth
<section class="hero" id="heroSection">
    <div id="particles"></div>
    <div class="hero-content">
        <div class="hero-stats">
            <div class="stat">
                <div class="stat-num" id="heroTotalTrips" data-target="{{ $trips->count() }}">0</div>
                <div class="stat-label">Jumlah Trip</div>
            </div>
            <div class="stat">
                <div class="stat-num" id="heroOpenTrips" data-target="{{ $trips->where('date_from', '>=', $today)->count() }}">0</div>
                <div class="stat-label">Trip Terbuka</div>
            </div>
            <div class="stat">
                <div class="stat-num" id="heroCompanies" data-target="{{ $companies->count() }}">0</div>
                <div class="stat-label">Syarikat</div>
            </div>
            <div class="stat">
               <div class="stat-num live-stat" id="heroActiveUsers">...</div>
                <div class="stat-label"><span class="live-dot"></span>Aktif Kini</div>
            </div>
            <div class="stat">
                <div class="stat-num live-stat" id="heroVisitors">...</div>
                <div class="stat-label">📊 Total Pelawat</div>
            </div>
             <div>
                 <div class="flag-malaysia">
            <img src="https://flagcdn.com/w320/my.png" alt="Malaysia Flag">
        </div>
            <h2>Terokai Keindahan Malaysia</h2>
            <p>
                Ikuti perjalanan pelancong yang meneroka Malaysia 
                — sebuah destinasi penuh budaya, keindahan alam, dan pengalaman yang tidak dapat dilupakan.  
                Inspirasi untuk trip anda bermula di sini ✈️
            </p>
        </div>
        </div>
    </div>
</section>

<!-- VIDEO SECTION -->
<section class="video-section">
    <div class="video-container">
       
       
        <div class="video-wrapper">
            {{-- enablejsapi=1 diperlukan untuk YouTube IFrame API mengesan status play/pause --}}
            <iframe
                id="ytPlayer"
                src="https://www.youtube.com/embed/7sQN3kMNUGo?enablejsapi=1"
                title="Terokai Malaysia"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen>
            </iframe>
        </div>
        <div class="video-playing-notice" id="videoPlayingNotice">
            ⏸️ Slaid gambar dijeda semasa video dimainkan
        </div>
    </div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
    <div class="trust-item"><span class="icon">✅</span> <strong>Syarikat Berlesen</strong></div>
    <div class="trust-item"><span class="icon">🔒</span> <strong>Bayaran Selamat</strong></div>
    <div class="trust-item"><span class="icon">🌟</span> <strong>Tempahan Mudah</strong></div>
    <div class="trust-item"><span class="icon">📞</span> <strong>Sokongan 24/7</strong></div>
</div>

<!-- SEARCH BAR -->
<div class="search-bar">
    <div class="search-group">
        <label>🔍 Cari Trip</label>
        <span class="search-icon">🔍</span>
        <input type="text" id="liveSearch" class="search-input"
            placeholder="Destinasi, negara atau syarikat..." autocomplete="off">
        <div class="search-spinner" id="searchSpinner"></div>
    </div>
    <div class="filter-group">
        <label>🏢 Syarikat</label>
        <select id="companyFilter" class="filter-select">
            <option value="">Semua Syarikat</option>
            @foreach($companies as $company)
                <option value="{{ strtolower($company->name) }}">{{ $company->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group" style="min-width:auto;flex:none;">
        <label>📌 Status</label>
        <div class="status-tabs">
            <button class="status-tab active" data-status="all" onclick="setStatus('all',this)">Semua</button>
            <button class="status-tab" data-status="open" onclick="setStatus('open',this)">🟢 Terbuka</button>
            <button class="status-tab" data-status="closed" onclick="setStatus('closed',this)">🔒 Tamat</button>
        </div>
    </div>
    <button class="btn-reset-search" id="btnReset" onclick="resetAll()">✕ Reset</button>
</div>
<div class="filter-tags-wrap" id="filterTagsWrap"></div>

<!-- TRIPS -->
<div class="container">
    <div class="section-header">
        <div>
            <div class="section-label">✈️ Semua Destinasi</div>
            <h2 class="section-title">Trip Tersedia</h2>
        </div>
        <div class="result-info" id="resultInfo"></div>
    </div>

    @if($trips->isEmpty())
        <div class="empty-state">
            <span class="icon">✈️</span>
            <p style="font-size:1.1rem;font-weight:600;color:#4a5568;">Tiada trip tersedia buat masa ini</p>
            <p style="margin-top:0.4rem;font-size:0.88rem;">Semak semula kemudian</p>
        </div>
    @else
        <div class="trips-grid" id="tripsGrid">
            @foreach($trips as $trip)
                @php
                    $isPast = $trip->date_from < $today;
                    $pics = collect([$trip->pic1_url ?? $trip->image_url ?? null, $trip->pic2_url ?? null, $trip->pic3_url ?? null])->filter()->values();
                    $picCount = $pics->count();
                @endphp
                <div class="trip-card trip-item {{ $isPast ? 'is-past' : '' }}"
                     data-name="{{ strtolower($trip->location) }}"
                     data-country="{{ strtolower($trip->country) }}"
                     data-company="{{ strtolower($trip->company->name ?? '') }}"
                     data-status="{{ $isPast ? 'closed' : 'open' }}"
                     data-from="{{ $trip->date_from }}"
                     data-to="{{ $trip->date_to }}"
                     data-tripid="{{ $trip->id }}"
                     data-created="{{ $trip->created_at->diffInDays(now()) }}"
                     onclick="window.location='{{ route('tourist.trip.detail', $trip->id) }}'">
                    <div class="trip-img-wrap">
                        @if($picCount > 0)
                            @foreach($pics as $pi => $pic)
                                <img src="{{ $pic }}" alt="{{ $trip->location }} {{ $pi+1 }}"
                                     loading="{{ $pi===0?'eager':'lazy' }}"
                                     style="opacity:{{ $pi===0?'1':'0' }};z-index:{{ $pi===0?'1':'0' }};"
                                     onerror="this.style.display='none';">
                            @endforeach
                            @if($picCount > 1)
                                <div class="img-dots">
                                    @for($d=0;$d<$picCount;$d++)
                                        <div class="img-dot {{ $d===0?'active':'' }}"></div>
                                    @endfor
                                </div>
                                <div class="img-counter">📷 1 / {{ $picCount }}</div>
                            @endif
                        @else
                            ✈️
                        @endif
                        <div id="badge-{{ $trip->id }}"></div>
                        <div style="position:absolute;top:10px;left:10px;">
                            @if($isPast)
                                <span style="background:rgba(0,0,0,0.55);color:#a0aec0;padding:3px 10px;border-radius:20px;font-size:0.68rem;font-weight:800;backdrop-filter:blur(4px);">🔒 Tamat</span>
                            @else
                                <span style="background:var(--teal);color:var(--navy);padding:3px 10px;border-radius:20px;font-size:0.68rem;font-weight:800;">🟢 Terbuka</span>
                            @endif
                        </div>
                    </div>
                    <div class="trip-body">
                        <div class="trip-company">🏢 {{ $trip->company->name ?? '-' }}</div>
                        <div class="trip-title">{{ $trip->location }}</div>
                        <div class="trip-meta">📍 {{ $trip->country }} ({{ $trip->national_code }})</div>
                        <div class="trip-meta">📅 {{ \Carbon\Carbon::parse($trip->date_from)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($trip->date_to)->format('d/m/Y') }}</div>
                        <div class="trip-meta">⏱️ {{ $trip->duration ?? '-' }} hari</div>
                        <div class="trip-footer">
                            <div>
                                <div class="trip-price-label">{{ $isPast ? 'Harga' : 'Dari' }}</div>
                                <div class="trip-price" style="{{ $isPast?'color:var(--muted)':'' }}">RM {{ number_format($trip->price_adult,0) }}</div>
                            </div>
                            @if($isPast)
                                <a href="{{ route('tourist.trip.detail',$trip->id) }}" class="btn-view" onclick="event.stopPropagation()">Lihat Detail</a>
                            @else
                                <a href="{{ route('tourist.trip.detail',$trip->id) }}" class="btn-book" onclick="event.stopPropagation()">Tempah →</a>
                            @endif
                        </div>
                        <div class="like-bar" onclick="event.stopPropagation()">
                            <button class="btn-like" id="like-{{ $trip->id }}" onclick="toggleLike({{ $trip->id }},'like')">
                                ❤️ <span class="like-count" id="like-count-{{ $trip->id }}">0</span>
                            </button>
                            <button class="btn-dislike" id="dislike-{{ $trip->id }}" onclick="toggleLike({{ $trip->id }},'dislike')">
                                👎 <span class="like-count" id="dislike-count-{{ $trip->id }}">0</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="no-results" id="noResults">
            <span class="emoji">🔍</span>
            <p style="font-weight:700;font-size:0.95rem;color:#4a5568;">Tiada trip ditemui</p>
            <p style="font-size:0.84rem;margin-top:0.3rem;">Cuba carian lain atau tukar filter</p>
            <button onclick="resetAll()" style="margin-top:1rem;padding:0.5rem 1.2rem;background:var(--navy);color:white;border:none;border-radius:var(--radius-sm);font-family:inherit;font-weight:600;cursor:pointer;font-size:0.88rem;">
                Reset Carian
            </button>
        </div>
    @endif

    @guest
    <div class="cta-banner">
        <h2>Bersedia Untuk Perjalanan?</h2>
        <p>Daftar akaun percuma sekarang dan mula tempah trip impian anda!</p>
        <a href="{{ route('tourist.register') }}" class="btn-cta">🚀 Daftar Sekarang — Percuma!</a>
        <div style="margin-top:0.9rem;font-size:0.82rem;color:rgba(255,255,255,0.5);">
            Sudah ada akaun? <a href="{{ route('tourist.login') }}" style="color:var(--teal);">Log masuk di sini</a>
        </div>
    </div>
    @endguest
</div>

<div class="footer">
    <div class="footer-inner">
        <span class="footer-link">✈️ TravelPro</span>
        <span style="color:rgba(255,255,255,0.2);">|</span>
        <span class="footer-link">🔒 Selamat & Terpercaya</span>
        <span style="color:rgba(255,255,255,0.2);">|</span>
        <span class="footer-link">📞 Hubungi Kami</span>
    </div>
    <p>© {{ date('Y') }} TravelPro. Semua hak terpelihara.</p>
</div>

{{-- YouTube IFrame API — mesti ada sebelum <script> utama --}}
<script src="https://www.youtube.com/iframe_api"></script>

<script>
/* ═══════════════════════════════════════════════════════════════
   1. VISITOR & ACTIVE USER TRACKING (SERVER-SIDE — shared across all users)
   ═══════════════════════════════════════════════════════════════ */
(function(){
    // Jana session ID unik untuk tab ini (kekal dalam sessionStorage)
    var SESSION_KEY = 'tp_session_id';
    var sessionId = sessionStorage.getItem(SESSION_KEY);
    if (!sessionId) {
        sessionId = Date.now().toString(36) + Math.random().toString(36).substr(2, 8);
        sessionStorage.setItem(SESSION_KEY, sessionId);
    }

    var pingUrl = '{{ route("tourist.visitor.ping") }}';
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf     = csrfMeta ? csrfMeta.content : '';

    function formatNumber(num) {
        if (num >= 1000000) return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
        if (num >= 1000)    return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
        return num.toString();
    }

    function updateDisplay(data) {
        var auEl = document.getElementById('heroActiveUsers');
        if (auEl && auEl.textContent !== String(data.active)) {
            auEl.textContent = data.active;
        }
        var vEl = document.getElementById('heroVisitors');
        if (vEl) {
            vEl.textContent = formatNumber(data.total);
        }
    }

    function pingServer() {
        fetch(pingUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ session_id: sessionId }),
        })
        .then(function(r) { return r.ok ? r.json() : null; })
        .then(function(d) { if (d) updateDisplay(d); })
        .catch(function() {}); // senyap — jangan crash kalau offline
    }

    // Ping terus bila page load, lepas tu setiap 8 saat
    pingServer();
    setInterval(pingServer, 8000);

    // Expose untuk diakses luar jika perlu
    window._tp = { formatNumber: formatNumber };
})();


/* ═══════════════════════════════════════════════════════════════
   2. IMAGE SLIDESHOW — PERLAHAN (2500ms) + JEDA BILA VIDEO MAIN
   ═══════════════════════════════════════════════════════════════ */
var isVideoPlaying = false;               // flag global
var allSlideshowStops = [];               // senarai fungsi stop() setiap kad

function pauseAllSlideshows(){
    isVideoPlaying = true;
    allSlideshowStops.forEach(function(fn){ fn && fn(); });
    var notice = document.getElementById('videoPlayingNotice');
    if(notice) notice.classList.add('show');
}
function resumeAllSlideshows(){
    isVideoPlaying = false;
    var notice = document.getElementById('videoPlayingNotice');
    if(notice) notice.classList.remove('show');
    // Slideshow akan mula semula secara automatik bila hover/visible
}

(function(){
    var INTERVAL    = 2500;   // ← 2.5 saat (asalnya 900ms — terlalu laju)
    var isMobile    = window.innerWidth <= 768;
    var isTouchDev  = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    document.querySelectorAll('.trip-img-wrap').forEach(function(wrap){
        var imgs    = Array.from(wrap.querySelectorAll('img'));
        var dots    = Array.from(wrap.querySelectorAll('.img-dot'));
        var counter = wrap.querySelector('.img-counter');
        if(imgs.length <= 1) return;

        var current = 0;
        var timer   = null;

        function showImg(i){
            imgs.forEach(function(img,idx){
                img.style.opacity = idx === i ? '1' : '0';
                img.style.zIndex  = idx === i ? '1' : '0';
            });
            dots.forEach(function(d,idx){ d.classList.toggle('active', idx === i); });
            if(counter) counter.textContent = '📷 '+(i+1)+' / '+imgs.length;
            current = i;
        }

        function startCycle(){
            if(isVideoPlaying) return;        // ← JEDA bila video bermain
            if(timer) return;                 // ← elak double-timer
            timer = setInterval(function(){
                if(isVideoPlaying){ stopCycle(); return; }
                showImg((current + 1) % imgs.length);
            }, INTERVAL);
        }

        function stopCycle(){
            clearInterval(timer);
            timer = null;
            showImg(0);
        }

        // Daftar fungsi stop untuk digunakan semasa video main
        allSlideshowStops.push(stopCycle);

        var card = wrap.closest('.trip-card');
        if(!card) return;

        if(isMobile || isTouchDev){
            // Sentiasa tunjuk dots & counter di mobile
            var dotsEl = wrap.querySelector('.img-dots');
            if(dotsEl) dotsEl.style.opacity = '1';
            if(counter) counter.style.opacity = '1';

            // Auto-play bila kad visible, berhenti bila keluar viewport
            var obs = new IntersectionObserver(function(entries){
                entries.forEach(function(entry){
                    if(entry.isIntersecting){ startCycle(); }
                    else { stopCycle(); }
                });
            }, {threshold: 0.3});
            obs.observe(card);

            // Touch: jeda sementara semasa swipe
            var touching = false;
            card.addEventListener('touchstart', function(){ touching = true; stopCycle(); });
            card.addEventListener('touchend',   function(){
                touching = false;
                setTimeout(function(){ if(!touching) startCycle(); }, 1200);
            });
        } else {
            // Desktop: hover biasa
            card.addEventListener('mouseenter', startCycle);
            card.addEventListener('mouseleave', stopCycle);
        }
    });
})();


/* ═══════════════════════════════════════════════════════════════
   3. YOUTUBE IFRAME API — kesan play / pause / end
   ═══════════════════════════════════════════════════════════════ */
var ytPlayer;
window.onYouTubeIframeAPIReady = function(){
    ytPlayer = new YT.Player('ytPlayer', {
        events: {
            onStateChange: function(e){
                if(e.data === YT.PlayerState.PLAYING){
                    pauseAllSlideshows();
                } else {
                    // PAUSED, ENDED, BUFFERING, CUED — resume slaid
                    resumeAllSlideshows();
                }
            }
        }
    });
};


/* ═══════════════════════════════════════════════════════════════
   4. FILTER & SEARCH
   ═══════════════════════════════════════════════════════════════ */
var allCards=document.querySelectorAll('.trip-item'),totalCount=allCards.length,searchTimer=null,currentStatus='all';

function runFilter(){
    var q=document.getElementById('liveSearch').value.trim().toLowerCase(),
        company=document.getElementById('companyFilter').value.toLowerCase(),
        shown=0;
    var cardsArray=Array.from(allCards);
    cardsArray.forEach(function(c){
        var ok=(!q||(c.dataset.name||'').includes(q)||(c.dataset.country||'').includes(q)||(c.dataset.company||'').includes(q))
            &&(!company||(c.dataset.company||'').includes(company))
            &&(currentStatus==='all'||c.dataset.status===currentStatus);
        c.style.display=ok?'':'none';
        if(ok)shown++;
    });
    var grid=document.getElementById('tripsGrid');
    var visibleCards=cardsArray.filter(function(c){return c.style.display!=='none';});
    visibleCards.sort(function(a,b){
        var aU=a.classList.contains('trip-starting-tomorrow')?3:a.classList.contains('trip-starting-soon')?2:a.classList.contains('on-fire')?1:0;
        var bU=b.classList.contains('trip-starting-tomorrow')?3:b.classList.contains('trip-starting-soon')?2:b.classList.contains('on-fire')?1:0;
        return bU-aU;
    });
    visibleCards.forEach(function(card){grid.appendChild(card);});
    document.getElementById('heroTotalTrips').textContent=shown;
    var open=0;
    document.querySelectorAll('.trip-item[data-status="open"]').forEach(function(c){if(c.style.display!=='none')open++;});
    document.getElementById('heroOpenTrips').textContent=open;
    var info=document.getElementById('resultInfo');
    info.innerHTML=(q||company||currentStatus!=='all')?
        'Menunjukkan <strong>'+shown+'</strong> daripada <strong>'+totalCount+'</strong> trip':
        '<strong>'+totalCount+'</strong> trip dijumpai';
    document.getElementById('tripsGrid').style.display=shown>0?'grid':'none';
    document.getElementById('noResults').style.display=shown===0?'block':'none';
    document.getElementById('btnReset').style.display=(q||company||currentStatus!=='all')?'inline-block':'none';
    renderTags(q,company,currentStatus);
    document.getElementById('searchSpinner').style.display='none';
}

document.getElementById('liveSearch').addEventListener('input',function(){
    document.getElementById('searchSpinner').style.display='block';
    clearTimeout(searchTimer);searchTimer=setTimeout(runFilter,350);
});
document.getElementById('companyFilter').addEventListener('change',runFilter);
function setStatus(s,el){currentStatus=s;document.querySelectorAll('.status-tab').forEach(function(t){t.classList.remove('active');});el.classList.add('active');runFilter();}
function resetAll(){document.getElementById('liveSearch').value='';document.getElementById('companyFilter').value='';currentStatus='all';document.querySelectorAll('.status-tab').forEach(function(t){t.classList.remove('active');});document.querySelector('[data-status="all"]').classList.add('active');runFilter();}
function renderTags(q,company,status){
    var wrap=document.getElementById('filterTagsWrap');wrap.innerHTML='';
    if(q)wrap.appendChild(makeTag('🔍 "'+q+'"',function(){document.getElementById('liveSearch').value='';runFilter();}));
    if(company){var label=document.getElementById('companyFilter').options[document.getElementById('companyFilter').selectedIndex].text;wrap.appendChild(makeTag('🏢 '+label,function(){document.getElementById('companyFilter').value='';runFilter();}));}
    if(status!=='all')wrap.appendChild(makeTag(status==='open'?'🟢 Terbuka':'🔒 Tamat',function(){currentStatus='all';document.querySelectorAll('.status-tab').forEach(function(t){t.classList.remove('active');});document.querySelector('[data-status="all"]').classList.add('active');runFilter();}));
}
function makeTag(text,fn){var d=document.createElement('div');d.className='filter-tag';var s=document.createElement('span');s.textContent=text;var b=document.createElement('button');b.textContent='✕';b.onclick=fn;d.appendChild(s);d.appendChild(b);return d;}


/* ═══════════════════════════════════════════════════════════════
   5. PARTICLES + ANIMATED COUNTER + CARD REVEAL
   ═══════════════════════════════════════════════════════════════ */
(function(){
    var c=document.getElementById('particles');if(!c)return;
    var n=window.innerWidth<640?10:20;
    for(var i=0;i<n;i++){var p=document.createElement('div');p.className='particle';var s=Math.random()*8+3;p.style.cssText='width:'+s+'px;height:'+s+'px;left:'+(Math.random()*100)+'%;bottom:-'+s+'px;animation-duration:'+(Math.random()*10+7)+'s;animation-delay:-'+(Math.random()*12)+'s;';c.appendChild(p);}
})();

function animateCounter(el,target,dur){
    var s=0,step=target/(dur/16),t=setInterval(function(){
        s+=step;if(s>=target){s=target;clearInterval(t);}
        // Jangan override live stats yang dikawal oleh pingTab()
        if(!el.classList.contains('live-stat')) el.textContent=Math.floor(s);
    },16);
}
var ho=new IntersectionObserver(function(entries){
    entries.forEach(function(e){
        if(!e.isIntersecting)return;
        document.querySelectorAll('.stat-num[data-target]').forEach(function(el){
            animateCounter(el,parseInt(el.dataset.target),1300);
        });
        ho.disconnect();
    });
},{threshold:0.3});
var hs=document.getElementById('heroSection');if(hs)ho.observe(hs);

var cro=new IntersectionObserver(function(entries){
    entries.forEach(function(e){
        if(e.isIntersecting){e.target.classList.add('revealed');e.target.classList.remove('will-animate');cro.unobserve(e.target);}
    });
},{threshold:0.01,rootMargin:'80px 0px 80px 0px'});
document.querySelectorAll('.trip-card').forEach(function(c,i){
    c.classList.add('will-animate');c.style.animationDelay=Math.min(i*.07,.45)+'s';cro.observe(c);
});
setTimeout(function(){document.querySelectorAll('.trip-card.will-animate:not(.revealed)').forEach(function(c){c.classList.add('revealed');c.classList.remove('will-animate');});},500);


/* ═══════════════════════════════════════════════════════════════
   6. COUNTDOWN BADGES
   ═══════════════════════════════════════════════════════════════ */
function calculateDaysUntilTrip(dateFrom){
    var today=new Date();today.setHours(0,0,0,0);
    var tripDate=new Date(dateFrom);tripDate.setHours(0,0,0,0);
    return Math.ceil((tripDate-today)/(1000*60*60*24));
}
function addCountdownBadges(){
    document.querySelectorAll('.trip-item[data-status="open"]').forEach(function(card){
        var dateFrom=card.dataset.from;if(!dateFrom)return;
        var daysUntil=calculateDaysUntilTrip(dateFrom);
        var badgeContainer=card.querySelector('.trip-img-wrap');
        var existingBadge=badgeContainer.querySelector('.countdown-badge');
        if(existingBadge)existingBadge.remove();
        card.classList.remove('trip-starting-tomorrow','trip-starting-soon');
        if(daysUntil<=1&&daysUntil>=0){
            var badge=document.createElement('div');badge.className='countdown-badge countdown-badge-urgent';
            badge.innerHTML=daysUntil===0?'⚡ BERMULA HARI INI!':'⚡ BERMULA ESOK!';
            badgeContainer.appendChild(badge);card.classList.add('trip-starting-tomorrow');
        } else if(daysUntil>1&&daysUntil<=7){
            var badge=document.createElement('div');badge.className='countdown-badge';
            badge.innerHTML='🕐 '+daysUntil+' hari lagi';
            badgeContainer.appendChild(badge);card.classList.add('trip-starting-soon');
        }
    });
}

addCountdownBadges();

(function checkUrgentTrips(){
    var urgentCount=document.querySelectorAll('.trip-starting-tomorrow').length;
    var soonCount=document.querySelectorAll('.trip-starting-soon').length;
    if(urgentCount>0){setTimeout(function(){showIndexToast('⚡ '+urgentCount+' trip bermula esok! Tempah sekarang!','#ef4444');},1500);}
    else if(soonCount>0){setTimeout(function(){showIndexToast('🕐 '+soonCount+' trip bermula tidak lama lagi!','#f59e0b');},1500);}
})();

runFilter();
setInterval(function(){addCountdownBadges();runFilter();},60000);


/* ═══════════════════════════════════════════════════════════════
   7. LIKES
   ═══════════════════════════════════════════════════════════════ */
var csrfToken=(document.querySelector('meta[name="csrf-token"]')||{}).content||'';
var isLoggedIn={{ Auth::check()?'true':'false' }};
var likesGetUrl='{{ route("tourist.likes.get") }}';
var likePOSTBase='{{ url("tourist/trip") }}';

function renderLike(tripId,data){
    var lb=document.getElementById('like-'+tripId),db=document.getElementById('dislike-'+tripId);if(!lb)return;
    var old=parseInt(document.getElementById('like-count-'+tripId).textContent)||0,nw=data.likes||0;
    document.getElementById('like-count-'+tripId).textContent=nw;
    document.getElementById('dislike-count-'+tripId).textContent=data.dislikes||0;
    lb.classList.toggle('active',data.myVote==='like');db.classList.toggle('active',data.myVote==='dislike');
    if(nw!==old){var el=document.getElementById('like-count-'+tripId);el.classList.remove('bounce');void el.offsetWidth;el.classList.add('bounce');setTimeout(function(){el.classList.remove('bounce');},500);}
}
function spawnFloatEmoji(btn,emoji){var r=btn.getBoundingClientRect(),el=document.createElement('span');el.className='float-emoji';el.textContent=emoji;el.style.cssText='position:fixed;left:'+(r.left+r.width/2-10)+'px;top:'+(r.top-5)+'px;';document.body.appendChild(el);setTimeout(function(){el.remove();},750);}
function spawnRipple(btn,cls){var r=document.createElement('div');r.className=cls;btn.appendChild(r);setTimeout(function(){r.remove();},550);}
function animateLikeClick(tripId,type){
    var btn=document.getElementById((type==='like'?'like-':'dislike-')+tripId);if(!btn)return;
    if('vibrate' in navigator)navigator.vibrate(type==='like'?[10,5,10]:[15]);
    btn.classList.remove('pop-anim');void btn.offsetWidth;btn.classList.add('pop-anim');
    setTimeout(function(){btn.classList.remove('pop-anim');},500);
    spawnFloatEmoji(btn,type==='like'?'❤️':'👎');
    spawnRipple(btn,type==='like'?'like-ripple':'dislike-ripple');
}
function toggleLike(tripId,type){
    if(!isLoggedIn){showIndexToast('⚠️ Sila log masuk untuk memberi vote','#e53e3e');return;}
    animateLikeClick(tripId,type);
    fetch(likePOSTBase+'/'+tripId+'/like',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({type:type})})
    .then(function(r){if(r.status===419){window.location.reload();return null;}if(r.status===401){showIndexToast('⚠️ Sila log masuk','#e53e3e');return null;}return r.json();})
    .then(function(d){if(d)renderLike(tripId,d);}).catch(function(){showIndexToast('❌ Ralat menghantar vote','#e53e3e');});
}
function initLikes(){
    var ids=[];document.querySelectorAll('.trip-item[data-tripid]').forEach(function(c){ids.push(c.dataset.tripid);});if(!ids.length)return;
    fetch(likesGetUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({trip_ids:ids})})
    .then(function(r){if(r.status===419)return null;return r.json();})
    .then(function(d){if(!d)return;ids.forEach(function(id){if(d[id])renderLike(id,d[id]);});}).catch(function(){});
}
function showIndexToast(msg,bg){var t=document.createElement('div');t.style.cssText='position:fixed;bottom:5rem;left:50%;transform:translateX(-50%);background:'+(bg||'#2d3748')+';color:white;padding:.7rem 1.4rem;border-radius:10px;font-weight:600;z-index:9999;font-size:.85rem;box-shadow:0 4px 20px rgba(0,0,0,0.3);white-space:nowrap;';t.textContent=msg;document.body.appendChild(t);setTimeout(function(){t.remove();},3000);}
initLikes();

// Popular fire badges
var popularThreshold=3;
function spawnFireParticles(card){if(card.querySelector('.fire-particles'))return;var wrap=document.createElement('div');wrap.className='fire-particles';var emojis=['🔥','🔥','✨','💥'];for(var i=0;i<5;i++){(function(idx){var p=document.createElement('span');p.className='fire-particle';p.textContent=emojis[idx%emojis.length];p.style.cssText='left:'+(8+Math.random()*84).toFixed(1)+'%;font-size:'+(Math.random()*.45+.7).toFixed(2)+'rem;animation-duration:'+(Math.random()*1.2+1).toFixed(2)+'s;animation-delay:-'+(Math.random()*1.5).toFixed(2)+'s;';wrap.appendChild(p);})(i);}card.appendChild(wrap);}
function updateBadge(tripId,likeCount){var el=document.getElementById('badge-'+tripId),card=document.querySelector('[data-tripid="'+tripId+'"]');if(!el||!card)return;var days=parseInt(card.dataset.created||999);if(likeCount>=popularThreshold){el.innerHTML='<div class="popular-badge">🔥 Popular</div>';card.classList.add('on-fire');spawnFireParticles(card);}else if(days<=7){el.innerHTML='<div class="new-badge">✨ Baru</div>';card.classList.remove('on-fire');var fp=card.querySelector('.fire-particles');if(fp)fp.remove();}else{el.innerHTML='';card.classList.remove('on-fire');var fp2=card.querySelector('.fire-particles');if(fp2)fp2.remove();}}
var _orig=renderLike;renderLike=function(tripId,data){_orig(tripId,data);updateBadge(tripId,data.likes||0);};


/* ═══════════════════════════════════════════════════════════════
   8. MISC — lazy images, smooth scroll
   ═══════════════════════════════════════════════════════════════ */
if('IntersectionObserver' in window){
    var lazyImageObserver=new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
            if(entry.isIntersecting){var img=entry.target;if(img.dataset.src){img.src=img.dataset.src;img.removeAttribute('data-src');}lazyImageObserver.unobserve(img);}
        });
    });
    document.querySelectorAll('img[loading="lazy"]').forEach(function(img){lazyImageObserver.observe(img);});
}
document.querySelectorAll('a[href^="#"]').forEach(function(anchor){
    anchor.addEventListener('click',function(e){var target=document.querySelector(this.getAttribute('href'));if(target){e.preventDefault();target.scrollIntoView({behavior:'smooth',block:'start'});}});
});
</script>

@auth
    @if(Auth::user()->isTourist())
    <nav class="bottom-nav">
        <div class="bottom-nav-inner">
            <a href="{{ route('tourist.index') }}" class="bottom-nav-item current"><span class="bn-icon">🌏</span><span>Trip</span></a>
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