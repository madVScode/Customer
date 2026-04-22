{{-- ============================================================ --}}
{{-- resources/views/modules/dashboard/index.blade.php            --}}
{{-- Admin Dashboard: Pengurusan Tempahan — v5.0 (Table View)     --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Dashboard Tempahan')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

<style>
/* ── Reset & Variables ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy:      #0b1120;
    --blue:      #3b82f6;
    --blue-glow: rgba(59,130,246,.16);
    --indigo:    #4f46e5;
    --surface:   #ffffff;
    --surface-2: #f8fafd;
    --surface-3: #f1f5f9;
    --border:    #e4e9f0;
    --border-2:  #d1d9e6;
    --text-1:    #0f172a;
    --text-2:    #334155;
    --text-3:    #64748b;
    --text-4:    #94a3b8;
    --green:     #10b981;
    --amber:     #f59e0b;
    --rose:      #f43f5e;
    --violet:    #8b5cf6;
    --radius:    14px;
    --radius-lg: 20px;
    --radius-xl: 28px;
    --shadow-sm: 0 2px 8px rgba(15,23,42,.08);
    --shadow-lg: 0 8px 32px rgba(15,23,42,.12);
    --shadow-xl: 0 16px 48px rgba(15,23,42,.16);
    --font:      'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --ease:      cubic-bezier(.4,0,.2,1);
}

[data-theme="dark"] {
    --surface:   #111827; --surface-2: #1f2937; --surface-3: #374151;
    --border:    #1f2937; --border-2:  #374151;
    --text-1:    #f1f5f9; --text-2:    #cbd5e1; --text-3:    #94a3b8; --text-4:    #64748b;
    --shadow-sm: 0 2px 8px rgba(0,0,0,.35);
    --shadow-lg: 0 8px 32px rgba(0,0,0,.5);
    --shadow-xl: 0 16px 48px rgba(0,0,0,.6);
}

body { font-family: var(--font); background: var(--surface-2); color: var(--text-1); line-height: 1.6; }
.dash-wrap { padding: 1.5rem; max-width: 1600px; }

/* ── Hero ── */
.dash-hero {
    position: relative; overflow: hidden;
    background: var(--navy); border-radius: var(--radius-xl);
    padding: 2.25rem 2.75rem 2rem; margin-bottom: 1.75rem;
    color: #fff; isolation: isolate;
}
.dash-hero::before {
    content: ''; position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 55% 80% at 92% 8%,  rgba(59,130,246,.38) 0%, transparent 60%),
        radial-gradient(ellipse 35% 60% at 8%  92%, rgba(139,92,246,.22) 0%, transparent 55%);
    z-index: 0;
}
.dash-hero-grid {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
    background-size: 32px 32px; z-index: 0;
}
.dash-hero-content { position: relative; z-index: 1; }
.hero-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
    border-radius: 50px; padding: .28rem .85rem;
    font-size: .68rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
    color: rgba(255,255,255,.65); margin-bottom: .75rem; backdrop-filter: blur(8px);
}
.dash-hero h1 { font-size: 1.9rem; font-weight: 800; letter-spacing: -.5px; line-height: 1.2; margin-bottom: .3rem; }
.dash-hero p  { font-size: .85rem; color: rgba(255,255,255,.5); }

/* ── Stats Grid ── */
.stats-grid {
    display: grid; grid-template-columns: repeat(6, 1fr);
    gap: .75rem; margin-top: 1.5rem;
}
.stat-card {
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
    border-radius: var(--radius); padding: .9rem 1rem;
    backdrop-filter: blur(8px); transition: background .2s var(--ease);
    position: relative; overflow: hidden;
}
.stat-card::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0;
    height: 2px; opacity: 0; transition: opacity .2s;
}
.stat-card:hover { background: rgba(255,255,255,.12); }
.stat-card:hover::after { opacity: 1; }
.stat-card.s-pending::after  { background: var(--amber); }
.stat-card.s-confirm::after  { background: var(--green); }
.stat-card.s-today::after    { background: #60a5fa; }
.stat-card.s-need::after     { background: var(--rose); }
.stat-card.s-revenue::after  { background: var(--violet); }

.stat-num { font-size: 1.75rem; font-weight: 900; line-height: 1; margin-bottom: .2rem; color: #fff; font-family: var(--mono); }
.stat-card.s-pending .stat-num  { color: #fbbf24; }
.stat-card.s-confirm .stat-num  { color: #34d399; }
.stat-card.s-today   .stat-num  { color: #60a5fa; }
.stat-card.s-need    .stat-num  { color: #f87171; }
.stat-card.s-revenue .stat-num  { color: #c4b5fd; font-size: 1.15rem; }
.stat-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.45); }

/* ── Filter Panel ── */
.filter-panel {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 1.4rem 1.5rem;
    margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);
}
.filter-panel-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.1rem; }
.filter-panel-title  { font-size: .72rem; font-weight: 800; color: var(--text-3); text-transform: uppercase; letter-spacing: .1em; }
.filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: .8rem; align-items: end; }

.ff label {
    display: block; font-size: .68rem; font-weight: 700;
    color: var(--text-3); text-transform: uppercase; letter-spacing: .05em; margin-bottom: .32rem;
}
.ff input, .ff select {
    width: 100%; padding: .58rem .85rem;
    border: 1.5px solid var(--border); border-radius: 10px;
    background: var(--surface-2); color: var(--text-1);
    font-size: .845rem; font-family: var(--font);
    transition: border-color .2s, box-shadow .2s;
    appearance: none; -webkit-appearance: none;
}
.ff input::placeholder { color: var(--text-4); }
.ff input:focus, .ff select:focus {
    outline: none; border-color: var(--blue);
    box-shadow: 0 0 0 3px var(--blue-glow); background: var(--surface);
}
.select-wrap { position: relative; }
.select-wrap::after {
    content: '▾'; position: absolute; right: .75rem; top: 50%;
    transform: translateY(-50%); font-size: .68rem; color: var(--text-4); pointer-events: none;
}
.select-wrap select { padding-right: 2rem; }
.filter-actions { display: flex; gap: .45rem; align-items: flex-end; }

/* ── Buttons ── */
.btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .58rem 1.2rem; border: none; border-radius: 10px;
    font-family: var(--font); font-size: .845rem; font-weight: 700;
    cursor: pointer; transition: all .2s var(--ease);
    white-space: nowrap; text-decoration: none; line-height: 1;
}
.btn-primary { background: linear-gradient(135deg, var(--blue), var(--indigo)); color: #fff; box-shadow: 0 2px 8px rgba(59,130,246,.28); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(59,130,246,.38); }
.btn-ghost { background: var(--surface-3); color: var(--text-3); border: 1.5px solid var(--border); }
.btn-ghost:hover { background: var(--surface-2); color: var(--text-2); border-color: var(--border-2); }
.btn-danger { background: linear-gradient(135deg, #dc2626, #ef4444); color: #fff; box-shadow: 0 2px 8px rgba(239,68,68,.25); }
.btn-danger:hover { opacity: .9; transform: translateY(-1px); }

/* ── Results Bar ── */
.results-bar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1rem; flex-wrap: wrap; gap: .6rem;
}
.results-title { font-size: .9rem; font-weight: 700; color: var(--text-2); display: flex; align-items: center; gap: .5rem; }
.count-badge {
    background: linear-gradient(135deg, #eff6ff, #dbeafe); color: var(--blue);
    font-size: .7rem; font-weight: 800; padding: .22rem .75rem;
    border-radius: 50px; border: 1px solid #bfdbfe; font-family: var(--mono);
}
[data-theme="dark"] .count-badge { background: rgba(59,130,246,.12); border-color: rgba(59,130,246,.25); }
.results-meta { font-size: .72rem; color: var(--text-4); font-family: var(--mono); }

/* ── Table ── */
.table-wrap {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden;
    box-shadow: var(--shadow-sm); overflow-x: auto;
}
.bk-table { width: 100%; border-collapse: collapse; font-size: .82rem; min-width: 900px; }
.bk-table thead tr { background: var(--surface-2); border-bottom: 2px solid var(--border); }
.bk-table thead th {
    padding: .75rem 1rem; font-size: .67rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .06em; color: var(--text-3);
    white-space: nowrap; text-align: left;
}
.bk-table thead th:first-child { padding-left: 1.4rem; }
.bk-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }

.bk-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background .15s var(--ease);
    animation: rowIn .3s ease both;
}
.bk-table tbody tr:last-child  { border-bottom: none; }
.bk-table tbody tr:hover       { background: #f8fbff; }
[data-theme="dark"] .bk-table tbody tr:hover { background: rgba(59,130,246,.04); }

/* Status accent bar (left border) */
.bk-table tbody tr td:first-child { position: relative; padding-left: 1.4rem; }
.bk-table tbody tr td:first-child::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
}
.bk-table tbody tr.status-pending   td:first-child::before { background: var(--amber); }
.bk-table tbody tr.status-confirmed td:first-child::before { background: var(--green); }
.bk-table tbody tr.status-cancelled td:first-child::before { background: var(--rose);  }
.bk-table tbody tr.status-completed td:first-child::before { background: var(--blue);  }

.bk-table td { padding: .72rem 1rem; color: var(--text-2); vertical-align: middle; }
.bk-table td:last-child { padding-right: 1.4rem; text-align: right; }

/* Table text helpers */
.tbl-name  { font-weight: 700; color: var(--text-1); vertical-align: middle; }
.tbl-sub   { font-size: .7rem; color: var(--text-4); font-family: var(--mono); display: block; margin-top: .06rem; }
.tbl-mono  { font-family: var(--mono); font-size: .78rem; }
.tbl-muted { color: var(--text-4); font-size: .72rem; display: block; margin-top: .05rem; }

/* ── Status Badges ── */
.bk-badge { padding: .22rem .7rem; border-radius: 50px; font-size: .67rem; font-weight: 800; white-space: nowrap; }
.bk-badge.pending   { background: #fef3c7; color: #92400e; }
.bk-badge.confirmed { background: #d1fae5; color: #065f46; }
.bk-badge.cancelled { background: #fee2e2; color: #991b1b; }
.bk-badge.completed { background: #dbeafe; color: #1e40af; }
[data-theme="dark"] .bk-badge.pending   { background: rgba(245,158,11,.15); color: #fbbf24; }
[data-theme="dark"] .bk-badge.confirmed { background: rgba(16,185,129,.15); color: #34d399; }
[data-theme="dark"] .bk-badge.cancelled { background: rgba(239,68,68,.15);  color: #f87171; }
[data-theme="dark"] .bk-badge.completed { background: rgba(59,130,246,.15); color: #60a5fa; }

/* ── Payment Badges ── */
.pay-badge { padding: .18rem .6rem; border-radius: 50px; font-size: .64rem; font-weight: 700; white-space: nowrap; }
.pay-badge.paid    { background: #d1fae5; color: #065f46; }
.pay-badge.partial { background: #fef3c7; color: #92400e; }
.pay-badge.unpaid  { background: #fee2e2; color: #991b1b; }

/* ── Table Action Buttons ── */
.tbl-actions { display: inline-flex; gap: .3rem; }
.tbl-btn {
    padding: .32rem .65rem; border-radius: 7px; font-size: .72rem; font-weight: 700;
    cursor: pointer; font-family: var(--font); border: 1.5px solid var(--border);
    background: var(--surface-3); color: var(--text-3);
    transition: all .15s var(--ease); white-space: nowrap;
    display: inline-flex; align-items: center; gap: .25rem;
}
.tbl-btn:hover { background: #eff6ff; border-color: #bfdbfe; color: var(--blue); }
.tbl-btn.approve { background: linear-gradient(135deg, #059669, #10b981); color: #fff; border-color: transparent; }
.tbl-btn.approve:hover:not(:disabled) { box-shadow: 0 2px 8px rgba(16,185,129,.3); transform: translateY(-1px); }
.tbl-btn.approve:disabled { opacity: .35; cursor: not-allowed; }
.tbl-btn.reject { background: linear-gradient(135deg, #dc2626, #ef4444); color: #fff; border-color: transparent; }
.tbl-btn.reject:hover:not(:disabled) { box-shadow: 0 2px 8px rgba(239,68,68,.3); transform: translateY(-1px); }
.tbl-btn.reject:disabled { opacity: .35; cursor: not-allowed; }

/* ── Skeleton Loader ── */
.skeleton-tbl { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
.skeleton-tbl-head { background: var(--surface-2); padding: .75rem 1.4rem; border-bottom: 2px solid var(--border); display: flex; gap: 1rem; }
.skeleton-tbl-row  { padding: .85rem 1.4rem; border-bottom: 1px solid var(--border); display: flex; gap: 1rem; align-items: center; }
.skeleton-tbl-row:last-child { border-bottom: none; }

@keyframes shimmer {
    0%   { background-position: -600px 0; }
    100% { background-position:  600px 0; }
}
.skel {
    background: linear-gradient(90deg, var(--surface-3) 25%, var(--surface-2) 50%, var(--surface-3) 75%);
    background-size: 600px 100%; animation: shimmer 1.4s infinite linear; border-radius: 6px;
}

/* ── Empty State ── */
.state-box {
    text-align: center; padding: 4rem 2rem;
    background: var(--surface); border-radius: var(--radius-lg); border: 2px dashed var(--border);
}
.state-box-icon { font-size: 2.5rem; margin-bottom: .75rem; opacity: .45; }
.state-box-text { font-size: .9rem; font-weight: 700; color: var(--text-3); margin-bottom: .3rem; }
.state-box-sub  { font-size: .78rem; color: var(--text-4); }

/* ── Modals ── */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 9999;
    display: none; align-items: center; justify-content: center;
    padding: 1rem; backdrop-filter: blur(4px);
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: var(--surface); border-radius: var(--radius-lg); padding: 1.75rem;
    width: 100%; max-width: 440px; box-shadow: var(--shadow-xl);
    border: 1px solid var(--border); animation: rowIn .25s ease;
}
.modal-title { font-size: 1rem; font-weight: 800; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; color: var(--text-1); }
.modal-textarea {
    width: 100%; padding: .75rem .9rem;
    border: 1.5px solid var(--border); border-radius: 10px;
    font-family: var(--font); font-size: .875rem; color: var(--text-1);
    background: var(--surface-2); resize: none; outline: none;
}
.modal-textarea:focus { border-color: var(--rose); box-shadow: 0 0 0 3px rgba(244,63,94,.1); }
.modal-actions { display: flex; gap: .6rem; margin-top: 1rem; justify-content: flex-end; }

/* ── Detail Modal ── */
.detail-modal-box {
    background: var(--surface); border-radius: var(--radius-lg);
    width: 100%; max-width: 640px; max-height: 88vh; overflow-y: auto;
    box-shadow: var(--shadow-xl); border: 1px solid var(--border); animation: rowIn .25s ease;
}
.detail-modal-header {
    padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
    position: sticky; top: 0; background: var(--surface); z-index: 1;
}
.detail-modal-title { font-size: .95rem; font-weight: 800; color: var(--text-1); }
.detail-modal-body  { padding: 1.25rem 1.5rem; }
.detail-section     { margin-bottom: 1.4rem; }
.detail-section-title {
    font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
    color: var(--text-4); border-bottom: 1px solid var(--border);
    padding-bottom: .5rem; margin-bottom: .9rem;
}
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem 1.2rem; }
.di-label { font-size: .68rem; color: var(--text-4); margin-bottom: .12rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.di-value { font-size: .875rem; font-weight: 700; color: var(--text-1); }

.trav-modal-table { width: 100%; border-collapse: collapse; font-size: .78rem; }
.trav-modal-table th {
    background: var(--surface-3); padding: 7px 10px;
    text-align: left; font-size: .68rem; text-transform: uppercase;
    color: var(--text-3); font-weight: 700;
}
.trav-modal-table td { padding: 7px 10px; border-bottom: 1px solid var(--border); color: var(--text-2); }
.trav-modal-table tr:last-child td { border-bottom: none; }
.trav-modal-table tr:hover td { background: var(--surface-2); }

/* ── Animations ── */
@keyframes rowIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.bk-table tbody tr:nth-child(1) { animation-delay: .02s; }
.bk-table tbody tr:nth-child(2) { animation-delay: .04s; }
.bk-table tbody tr:nth-child(3) { animation-delay: .06s; }
.bk-table tbody tr:nth-child(4) { animation-delay: .08s; }
.bk-table tbody tr:nth-child(5) { animation-delay: .10s; }
.bk-table tbody tr:nth-child(n+6) { animation-delay: .12s; }

/* ── Responsive ── */
@media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 720px)  { .stats-grid { grid-template-columns: repeat(2, 1fr); } .filter-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px)  { .dash-hero { padding: 1.75rem 1.5rem 1.5rem; } .dash-hero h1 { font-size: 1.5rem; } .detail-grid { grid-template-columns: 1fr; } }
</style>

<div class="dash-wrap">

    {{-- ── Hero ── --}}
    <div class="dash-hero">
        <div class="dash-hero-grid"></div>
        <div class="dash-hero-content">
            <div class="hero-badge">⚡ Admin Dashboard</div>
            <h1>📋 Pengurusan Tempahan</h1>
            <p>Semak, luluskan atau tolak tempahan dari pelancong</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-num" id="statTotal">—</div>
                    <div class="stat-label">Jumlah</div>
                </div>
                <div class="stat-card s-pending">
                    <div class="stat-num" id="statPending">—</div>
                    <div class="stat-label">⏳ Menunggu</div>
                </div>
                <div class="stat-card s-confirm">
                    <div class="stat-num" id="statConfirmed">—</div>
                    <div class="stat-label">✅ Disahkan</div>
                </div>
                <div class="stat-card s-today">
                    <div class="stat-num" id="statToday">—</div>
                    <div class="stat-label">📅 Hari Ini</div>
                </div>
                <div class="stat-card s-need">
                    <div class="stat-num" id="statNeed">—</div>
                    <div class="stat-label">📝 Perlu Info</div>
                </div>
                <div class="stat-card s-revenue">
                    <div class="stat-num" id="statRevenue">—</div>
                    <div class="stat-label">💰 Revenue</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filter Panel ── --}}
    <div class="filter-panel">
        <div class="filter-panel-header">
            <span class="filter-panel-title">🔍 Penapis Carian</span>
            <button id="btnReset" class="btn btn-ghost" style="padding:.45rem .9rem;font-size:.78rem;">✕ Reset</button>
        </div>
        <div class="filter-grid">
            <div class="ff">
                <label>🏢 Syarikat</label>
                <div class="select-wrap">
                    <select id="fCompany"><option value="">Semua Syarikat</option></select>
                </div>
            </div>
            <div class="ff">
                <label>✈️ Booking Trip</label>
                <div class="select-wrap">
                    <select id="fTrip"><option value="">Semua Trip</option></select>
                </div>
            </div>
            <div class="ff">
                <label>👤 Nama Pelanggan</label>
                <input id="fName" type="text" placeholder="Nama pelanggan...">
            </div>
            <div class="ff">
                <label>🪪 No. IC</label>
                <input id="fIC" type="text" placeholder="950101-01-1234">
            </div>
            <div class="ff">
                <label>📧 Emel</label>
                <input id="fEmail" type="email" placeholder="email@gmail.com">
            </div>
            <div class="ff">
                <label>⚥ Jantina</label>
                <div class="select-wrap">
                    <select id="fGender">
                        <option value="">Semua</option>
                        <option value="male">Lelaki</option>
                        <option value="female">Perempuan</option>
                    </select>
                </div>
            </div>
            <div class="ff">
                <label>📍 Negeri</label>
                <div class="select-wrap">
                    <select id="fState">
                        <option value="">Semua Negeri</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="ff">
                <label>📊 Status</label>
                <div class="select-wrap">
                    <select id="fStatus">
    <option value="pending">⏳ Menunggu</option>
    <option value="confirmed">✅ Disahkan</option>
    <option value="cancelled">❌ Dibatal</option>
    <option value="completed">🏁 Selesai</option>
    <option value="no_booking">🆕 Tiada Booking</option>
</select>
                </div>
            </div>
            <div class="filter-actions">
                <button id="btnSearch" class="btn btn-primary">🔍 Cari</button>
            </div>
        </div>
    </div>

    {{-- ── Results Bar ── --}}
    <div class="results-bar">
        <div class="results-title">
            Senarai Tempahan
            <span class="count-badge" id="resultsCount">0 rekod</span>
        </div>
        <div class="results-meta" id="lastUpdate"></div>
    </div>

    {{-- ── Skeleton Loader ── --}}
    <div id="bkLoading" class="skeleton-tbl">
        <div class="skeleton-tbl-head">
            <div class="skel" style="height:11px;width:9%;"></div>
            <div class="skel" style="height:11px;width:15%;"></div>
            <div class="skel" style="height:11px;width:15%;"></div>
            <div class="skel" style="height:11px;width:11%;"></div>
            <div class="skel" style="height:11px;width:7%;"></div>
            <div class="skel" style="height:11px;width:9%;"></div>
            <div class="skel" style="height:11px;width:9%;"></div>
            <div class="skel" style="height:11px;width:9%;"></div>
            <div class="skel" style="height:11px;width:10%;margin-left:auto;"></div>
        </div>
        @for($i = 0; $i < 8; $i++)
        <div class="skeleton-tbl-row">
            <div style="flex:1.2;">
                <div class="skel" style="height:11px;width:80%;margin-bottom:.3rem;"></div>
                <div class="skel" style="height:10px;width:55%;"></div>
            </div>
            <div style="flex:1.8;">
                <div class="skel" style="height:12px;width:70%;margin-bottom:.3rem;"></div>
                <div class="skel" style="height:10px;width:50%;"></div>
            </div>
            <div style="flex:1.8;">
                <div class="skel" style="height:11px;width:75%;margin-bottom:.3rem;"></div>
                <div class="skel" style="height:10px;width:50%;"></div>
            </div>
            <div style="flex:1.1;"><div class="skel" style="height:10px;width:70%;"></div></div>
            <div style="flex:.7;"> <div class="skel" style="height:10px;width:60%;"></div></div>
            <div style="flex:.9;"> <div class="skel" style="height:10px;width:75%;"></div></div>
            <div style="flex:.9;"> <div class="skel" style="height:22px;width:75px;border-radius:50px;"></div></div>
            <div style="flex:.8;"> <div class="skel" style="height:22px;width:60px;border-radius:50px;"></div></div>
            <div style="flex:1.1;display:flex;gap:.3rem;justify-content:flex-end;">
                <div class="skel" style="height:28px;width:58px;border-radius:7px;"></div>
                <div class="skel" style="height:28px;width:58px;border-radius:7px;"></div>
                <div class="skel" style="height:28px;width:38px;border-radius:7px;"></div>
            </div>
        </div>
        @endfor
    </div>

    {{-- ── Table ── --}}
    <div id="bkTableWrap" class="table-wrap" style="display:none;">
        <table class="bk-table">
            <thead>
                <tr>
                    <th>No. Booking</th>
                    <th>Pelanggan</th>
                    <th>Trip</th>
                    <th>Syarikat</th>
                    <th>Pax</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Bayaran</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody id="bkTbody"></tbody>
        </table>
    </div>

    {{-- ── Empty State ── --}}
    <div id="bkEmpty" class="state-box" style="display:none;">
        <div class="state-box-icon">📋</div>
        <div class="state-box-text">Tiada tempahan dijumpai</div>
        <div class="state-box-sub">Cuba ubah filter atau kata kunci carian</div>
    </div>

</div>

{{-- ── Reject Modal ── --}}
<div class="modal-overlay" id="rejectModal">
    <div class="modal-box">
        <div class="modal-title">❌ Tolak Tempahan</div>
        <input type="hidden" id="rejectBookingId">
        <div style="font-size:.83rem;color:var(--text-3);margin-bottom:.85rem;">
            Booking: <strong id="rejectBookingNo" style="color:var(--text-1);font-family:var(--mono);"></strong>
        </div>
        <textarea id="rejectReason" class="modal-textarea" rows="3" placeholder="Sebab penolakan (pilihan)..."></textarea>
        <div class="modal-actions">
            <button class="btn btn-ghost" id="cancelRejectBtn">Batal</button>
            <button class="btn btn-danger" id="confirmRejectBtn">❌ Sahkan Penolakan</button>
        </div>
    </div>
</div>

{{-- ── View Detail Modal ── --}}
<div class="modal-overlay" id="viewModal">
    <div class="detail-modal-box">
        <div class="detail-modal-header">
            <div class="detail-modal-title" id="viewModalTitle">Detail Tempahan</div>
            <button id="closeViewModal" style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--text-4);line-height:1;padding:.25rem;">✕</button>
        </div>
        <div class="detail-modal-body" id="viewModalBody"></div>
    </div>
</div>

@endsection
@push('scripts')
<script>
$(function () {

    var searchTimer;
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    /* ─────────────────────────────────────────────────────────
     * Stats
     * ───────────────────────────────────────────────────────── */
    function loadStats() {
    $.get('/bookings/dash-stats', function (res) {
        if (!res.success) return;
        var d = res.data;
        $('#statTotal').text(d.total || 0);
        $('#statPending').text(d.pending || 0);
        $('#statConfirmed').text(d.confirmed || 0);
        $('#statToday').text(d.today || 0);
        $('#statNeed').text(d.need_details || 0);
        // ← buang baris statRevenue dari sini
    }).fail(function () {});
}

    /* ─────────────────────────────────────────────────────────
     * Filter dropdowns
     * ───────────────────────────────────────────────────────── */
    function loadCompanyFilter() {
        $.get('/companies', function (res) {
            if (!res.success) return;
            var $sel = $('#fCompany').find('option:not(:first)').remove().end();
            res.data.forEach(function (c) {
                $sel.append('<option value="' + c.id + '">' + c.name + '</option>');
            });
        }).fail(function () {});
    }

    function loadTripFilter() {
        $.get('/trips', function (res) {
            if (!res.success) return;
            var $sel = $('#fTrip').find('option:not(:first)').remove().end();
            res.data.forEach(function (t) {
                var label = t.location + (t.date_from ? ' (' + fmtDate(t.date_from) + ')' : '');
                $sel.append('<option value="' + t.id + '">' + label + '</option>');
            });
        }).fail(function () {});
    }

    /* ─────────────────────────────────────────────────────────
     * Event listeners
     * ───────────────────────────────────────────────────────── */
    $('#fName, #fIC, #fEmail').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 380);
    });

    $('#fName, #fIC, #fEmail').on('keypress', function (e) {
        if (e.which === 13) { clearTimeout(searchTimer); runSearch(); }
    });

    $('#fGender, #fState, #fCompany, #fTrip, #fStatus').on('change', runSearch);

    $('#btnSearch').on('click', function () {
        clearTimeout(searchTimer);
        runSearch();
    });

    $('#btnReset').on('click', function () {
        $('#fName, #fIC, #fEmail').val('');
        $('#fGender, #fState, #fCompany, #fTrip').val('');
        $('#fStatus').val('pending');
        runSearch();
    });

    /* ─────────────────────────────────────────────────────────
     * Search helpers
     * ───────────────────────────────────────────────────────── */
    function hasActiveFilter() {
        var vals = [
            $('#fName').val().trim(),
            $('#fIC').val().trim(),
            $('#fEmail').val().trim(),
            $('#fGender').val(),
            $('#fState').val(),
            $('#fCompany').val(),
            $('#fTrip').val(),
            $('#fStatus').val(),
        ];
        return vals.some(function (v) { return v !== '' && v !== null && v !== undefined; });
    }

    function resetView() {
    $('#bkLoading').hide();
    $('#bkTableWrap').hide();
    $('#bkTbody').empty();
    $('#bkEmpty').show();
    $('#resultsCount').text('0 rekod');
    $('#lastUpdate').text('');
    $('#statRevenue').text('RM 0.00');  // ← tambah ini
}

    /* ─────────────────────────────────────────────────────────
     * Search request
     * ───────────────────────────────────────────────────────── */
    function runSearch() {
    if (!hasActiveFilter()) { resetView(); return; }

    $('#bkTableWrap').hide();
    $('#bkTbody').empty();
    $('#bkEmpty').hide();
    $('#bkLoading').show();

    var status = $('#fStatus').val();

    // ── Jika "Tiada Booking", guna endpoint customer tanpa booking ──
    if (status === 'no_booking') {
    var companyVal = $('#fCompany').val();

    var params = {
        name       : $('#fName').val().trim(),
        ic         : $('#fIC').val().trim(),
        email      : $('#fEmail').val().trim(),
        gender     : $('#fGender').val(),
        state      : $('#fState').val(),
        company_id : companyVal || '',
    };

        $.ajax({
            url     : '/customers/no-booking',
            type    : 'GET',
            data    : params,
            success : function (res) {
                $('#bkLoading').hide();
                if (res.success) renderNoBookingTable(res.data);
                else showError('Carian gagal.');
            },
            error : function () {
                $('#bkLoading').hide();
                showError('Ralat server.');
            }
        });
        return;
    }

    // ── Carian biasa (booking) ──
    var params = {
        name           : $('#fName').val().trim(),
        ic             : $('#fIC').val().trim(),
        email          : $('#fEmail').val().trim(),
        gender         : $('#fGender').val(),
        state          : $('#fState').val(),
        company_id     : $('#fCompany').val(),
        trip_id        : $('#fTrip').val(),
        booking_status : status,
    };

    $.ajax({
        url     : '/bookings/search',
        type    : 'GET',
        data    : params,
        success : function (res) {
            $('#bkLoading').hide();
            if (res.success) renderTable(res.data);
            else showError('Carian gagal.');
        },
        error : function () {
            $('#bkLoading').hide();
            showError('Ralat server.');
        }
    });
}

    /* ─────────────────────────────────────────────────────────
     * Render table
     * ───────────────────────────────────────────────────────── */
    function renderTable(bookings) {
    var $tbody = $('#bkTbody');
    $tbody.empty();
    $('#resultsCount').text(bookings.length + ' rekod');
    $('#lastUpdate').text('Kemaskini: ' + new Date().toLocaleTimeString('ms-MY'));

    if (!bookings.length) {
        $('#bkEmpty').show();
        $('#statRevenue').text('RM 0.00');  // ← reset bila tiada rekod
        return;
    }

    // ── Kira revenue dari filtered results ───────────────────────
    var filteredRevenue = bookings.reduce(function (sum, b) {
        return sum + parseFloat(b.total_amount || 0);
    }, 0);
    $('#statRevenue').text('RM ' + numFmt(filteredRevenue));  // ← update stat card

    bookings.forEach(function (b) { $tbody.append(buildRow(b)); });
    $('#bkTableWrap').show();
}
    function renderNoBookingTable(customers) {
    var $tbody = $('#bkTbody');
    $tbody.empty();
    $('#resultsCount').text(customers.length + ' rekod');
    $('#lastUpdate').text('Kemaskini: ' + new Date().toLocaleTimeString('ms-MY'));

    if (!customers.length) {
        var companyName = $('#fCompany option:selected').text();
        var hasCompany  = !!$('#fCompany').val();

        $('.state-box-icon').text('🏢');
        $('.state-box-text').text(
            hasCompany
                ? 'Tiada customer untuk ' + companyName
                : 'Tiada customer tanpa booking'
        );
        $('.state-box-sub').text(
            hasCompany
                ? 'Company ini belum mempunyai customer yang berdaftar'
                : 'Cuba ubah filter carian'
        );
        $('#bkEmpty').show();
        return;
    }

    // Reset teks empty state untuk kegunaan seterusnya
    $('.state-box-icon').text('📋');
    $('.state-box-text').text('Tiada tempahan dijumpai');
    $('.state-box-sub').text('Cuba ubah filter atau kata kunci carian');

    customers.forEach(function (c) { $tbody.append(buildNoBookingRow(c)); });
    $('#bkTableWrap').show();
}

function buildNoBookingRow(c) {
    var isDeleted = !!c.deleted_at;

    return (
        '<tr class="status-' + (isDeleted ? 'cancelled' : 'pending') + '" id="bkRow-c-' + c.id + '">' +

            '<td>' +
                '<span class="tbl-mono" style="color:var(--text-4);">—</span>' +
                '<span class="tbl-muted">Tiada booking</span>' +
            '</td>' +

            '<td>' +
                '<span class="tbl-name">' + esc(c.name || '-') + '</span>' +
                '<span class="tbl-sub">' + fmtIC(c.ic) + '</span>' +
            '</td>' +

            '<td colspan="5" style="color:var(--text-4);font-size:.78rem;font-style:italic;">' +
                (isDeleted ? '🗑️ Customer telah dipadam' : 'Pelanggan belum membuat sebarang tempahan') +
            '</td>' +

            '<td>' +
                (isDeleted
                    ? '<span class="bk-badge cancelled">🗑️ Dipadam</span>'
                    : '<span class="bk-badge pending">🆕 Baru</span>'
                ) +
            '</td>' +

            '<td>' +
                '<div class="tbl-actions">' +
                    (!isDeleted
                        ? '<button class="tbl-btn btn-edit-customer" data-id="' + (c.unique_id || c.id) + '">✏️ Edit</button>'
                        : ''
                    ) +
                    (!isDeleted
                        ? '<button class="tbl-btn reject btn-delete-customer" data-id="' + (c.unique_id || c.id) + '" data-name="' + esc(c.name) + '">🗑️ Padam</button>'
                        : '<button class="tbl-btn approve btn-restore-customer" data-id="' + (c.unique_id || c.id) + '" data-name="' + esc(c.name) + '">♻️ Pulih</button>'
                    ) +
                '</div>' +
            '</td>' +

        '</tr>'
    );
}

    /* ─────────────────────────────────────────────────────────
     * Build one table row
     * ───────────────────────────────────────────────────────── */
    function buildRow(b) {
        var status  = b.booking_status || 'pending';
        var isPend  = status === 'pending';
        var c       = b.customer || {};
        var trip    = b.trip || {};
        var company = (trip && trip.company) ? trip.company : {};

        var statusLabel = {
            pending   : '⏳ Menunggu',
            confirmed : '✅ Disahkan',
            cancelled : '❌ Dibatal',
            completed : '🏁 Selesai'
        }[status] || status;

        var totalPax = (b.pax_adult || 0) + (b.pax_child || 0) + (b.pax_baby || 0);
        var dateFrom = trip.date_from ? fmtDate(trip.date_from) : '-';
        var dateTo   = trip.date_to   ? fmtDate(trip.date_to)   : '-';
        var payRaw   = (b.payment_status || '').toLowerCase();
        var payClass = payRaw === 'paid' ? 'paid' : (payRaw === 'partial' ? 'partial' : 'unpaid');
        var payLabel = b.payment_label || b.payment_status || '-';

        var disAttr = isPend ? '' : 'disabled';
        var bookingJson = JSON.stringify(b).replace(/'/g, "&#39;");

        return (
            '<tr class="status-' + status + '" id="bkRow-' + b.id + '">' +

                '<td>' +
                    '<span class="tbl-mono" style="color:var(--text-3);">' + esc(b.booking_no) + '</span>' +
                    '<span class="tbl-muted">' + fmtDate((b.created_at || '').substring(0, 10)) + '</span>' +
                '</td>' +

                '<td>' +
                    '<span class="tbl-name">' + esc(c.name || '-') + '</span>' +
                    '<span class="tbl-sub">' + fmtIC(c.ic) + '</span>' +
                '</td>' +

                '<td>' +
                    '<span style="font-weight:700;color:var(--text-1);">' + esc(trip.location || '-') + '</span>' +
                    '<span class="tbl-muted">' + dateFrom + ' → ' + dateTo + '</span>' +
                '</td>' +

                '<td style="color:var(--text-3);font-size:.8rem;">' + esc(company.name || '-') + '</td>' +

                '<td>' +
                    '<span style="font-weight:700;font-family:var(--mono);">' + totalPax + '</span>' +
                    '<span class="tbl-muted">' + (b.pax_adult || 0) + 'D ' + (b.pax_child || 0) + 'K ' + (b.pax_baby || 0) + 'B</span>' +
                '</td>' +

                '<td><span style="font-weight:800;color:var(--green);font-family:var(--mono);">RM ' + numFmt(b.total_amount || 0) + '</span></td>' +
                '<td><span class="bk-badge ' + status + '">' + statusLabel + '</span></td>' +
                '<td><span class="pay-badge ' + payClass + '">' + esc(payLabel) + '</span></td>' +

                '<td>' +
                    '<div class="tbl-actions">' +
                        '<button class="tbl-btn btn-edit-customer" data-id="' + (c.unique_id || c.id) + '">✏️ Edit</button>' +
                        '<button class="tbl-btn approve btn-approve" data-id="' + b.id + '" ' + disAttr + '>✅ Lulus</button>' +
                        '<button class="tbl-btn reject btn-reject-card" data-id="' + b.id + '" data-no="' + esc(b.booking_no) + '" ' + disAttr + '>❌ Tolak</button>' +
                        '<button class="tbl-btn btn-view" data-booking=\'' + bookingJson + '\'>👁</button>' +
                    '</div>' +
                '</td>' +

            '</tr>'
        );
    }

    /* ─────────────────────────────────────────────────────────
     * Approve
     * ───────────────────────────────────────────────────────── */
    $(document).on('click', '.btn-approve', function () {
        var $btn = $(this), id = $btn.data('id');
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true).text('⏳');

        $.ajax({
            url     : '/bookings/' + id + '/approve',
            type    : 'POST',
            headers : { 'X-CSRF-TOKEN': csrfToken },
            success : function (res) {
                if (res.success) {
                    window.showNotification && window.showNotification(res.message, 'success');
                    runSearch(); loadStats();
                } else {
                    window.showNotification && window.showNotification(res.message, 'error');
                    $btn.prop('disabled', false).text('✅ Lulus');
                }
            },
            error : function () {
                window.showNotification && window.showNotification('Gagal meluluskan.', 'error');
                $btn.prop('disabled', false).text('✅ Lulus');
            }
        });
    });

    /* ─────────────────────────────────────────────────────────
     * Reject modal
     * ───────────────────────────────────────────────────────── */
    $(document).on('click', '.btn-reject-card', function () {
        var $btn = $(this);
        if ($btn.prop('disabled')) return;
        $('#rejectBookingId').val($btn.data('id'));
        $('#rejectBookingNo').text($btn.data('no'));
        $('#rejectReason').val('');
        $('#rejectModal').addClass('show');
    });

    $('#cancelRejectBtn').on('click', function () { $('#rejectModal').removeClass('show'); });

    $('#rejectModal').on('click', function (e) {
        if ($(e.target).is('#rejectModal')) $(this).removeClass('show');
    });

    $('#confirmRejectBtn').on('click', function () {
        var $btn   = $(this);
        var id     = $('#rejectBookingId').val();
        var reason = $('#rejectReason').val().trim();
        $btn.prop('disabled', true).text('⏳ Memproses...');

        $.ajax({
            url     : '/bookings/' + id + '/reject',
            type    : 'POST',
            headers : { 'X-CSRF-TOKEN': csrfToken },
            data    : { reason: reason },
            success : function (res) {
                $btn.prop('disabled', false).text('❌ Sahkan Penolakan');
                $('#rejectModal').removeClass('show');
                if (res.success) {
                    window.showNotification && window.showNotification(res.message, 'success');
                    runSearch(); loadStats();
                } else {
                    window.showNotification && window.showNotification(res.message, 'error');
                }
            },
            error : function () {
                $btn.prop('disabled', false).text('❌ Sahkan Penolakan');
                window.showNotification && window.showNotification('Gagal menolak.', 'error');
            }
        });
    });

    /* ─────────────────────────────────────────────────────────
     * Edit customer modal
     * ───────────────────────────────────────────────────────── */
    $(document).on('click', '.btn-edit-customer', function () {
    var id = $(this).data('id');
    if (!id) { showError('ID customer tak sah'); return; }

    if ($('#customerForm').length) $('#customerForm')[0].reset();

    $.get('/customers/' + id, function (res) {
        if (!res.success) { showError('Gagal load data'); return; }

        // ✅ Guna populateEditModal dari customers.js
        // — ia handle booking dropdown, travelers, states, semua sekali
        if (typeof window.populateEditModal === 'function') {
            window.populateEditModal(res.data);
        } else {
            showError('populateEditModal tidak dijumpai. Semak urutan load script.');
        }

    }).fail(function () {
        showError('Ralat server. Cuba semula.');
    });
});
    /* ─────────────────────────────────────────────────────────
 * Delete customer
 * ───────────────────────────────────────────────────────── */
$(document).on('click', '.btn-delete-customer', function () {
    var $btn = $(this);
    var id   = $btn.data('id');
    var name = $btn.data('name');

    if (!confirm('Padam customer "' + name + '"?\n\nCustomer boleh dipulihkan kemudian.')) return;

    $btn.prop('disabled', true).text('⏳');

    $.ajax({
        url     : '/customers/' + id,
        type    : 'POST',
        headers : { 'X-CSRF-TOKEN': csrfToken },
        data    : { _method: 'DELETE' },
        success : function (res) {
            if (res.success) {
                window.showNotification && window.showNotification('Customer berjaya dipadam', 'success');
                runSearch();
            } else {
                window.showNotification && window.showNotification(res.message || 'Gagal memadam', 'error');
                $btn.prop('disabled', false).text('🗑️ Padam');
            }
        },
        error : function (xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message || 'Ralat server';
            window.showNotification && window.showNotification(msg, 'error');
            $btn.prop('disabled', false).text('🗑️ Padam');
        }
    });
});

/* ─────────────────────────────────────────────────────────
 * Restore customer
 * ───────────────────────────────────────────────────────── */
$(document).on('click', '.btn-restore-customer', function () {
    var $btn = $(this);
    var id   = $btn.data('id');
    var name = $btn.data('name');

    if (!confirm('Pulihkan customer "' + name + '"?')) return;

    $btn.prop('disabled', true).text('⏳');

    $.ajax({
        url     : '/customers/' + id + '/restore',
        type    : 'POST',
        headers : { 'X-CSRF-TOKEN': csrfToken },
        success : function (res) {
            if (res.success) {
                window.showNotification && window.showNotification('Customer berjaya dipulihkan', 'success');
                runSearch();
            } else {
                window.showNotification && window.showNotification(res.message || 'Gagal memulihkan', 'error');
                $btn.prop('disabled', false).text('♻️ Pulih');
            }
        },
        error : function (xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message || 'Ralat server';
            window.showNotification && window.showNotification(msg, 'error');
            $btn.prop('disabled', false).text('♻️ Pulih');
        }
    });
});

    $('#closeCustomerModal').on('click', function () { $('#customerModal').removeClass('show'); });

    $('#customerModal').on('click', function (e) {
        if ($(e.target).is('#customerModal')) $(this).removeClass('show');
    });

    $('#customerForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#customerId').val();

        $.ajax({
            url     : '/customers/' + id,
            type    : 'POST',
            headers : { 'X-CSRF-TOKEN': csrfToken },
            data    : $(this).serialize() + '&_method=PUT&traveler_details=' + encodeURIComponent(JSON.stringify(etCollect())),
            success : function (res) {
                if (res.success) {
                    $('#customerModal').removeClass('show');
                    window.showNotification && window.showNotification('Customer berjaya dikemaskini', 'success');
                    runSearch();
                } else {
                    showError(res.message);
                }
            },
            error : function () { showError('Gagal update customer'); }
        });
    });

    /* ─────────────────────────────────────────────────────────
     * View detail modal
     * ───────────────────────────────────────────────────────── */
    $(document).on('click', '.btn-view', function () {
        var b;
        try { b = JSON.parse($(this).attr('data-booking')); } catch (e) { return; }
        renderDetailModal(b);
        $('#viewModal').addClass('show');
    });

    $('#closeViewModal').on('click', function () { $('#viewModal').removeClass('show'); });

    $('#viewModal').on('click', function (e) {
        if ($(e.target).is('#viewModal')) $(this).removeClass('show');
    });

    function renderDetailModal(b) {
        var c    = b.customer || {};
        var trip = b.trip || {};
        var co   = (trip && trip.company) ? trip.company : {};

        var statusColor = { pending: '#92400e', confirmed: '#065f46', cancelled: '#991b1b', completed: '#1e40af' }[b.booking_status] || '#4a5568';
        var statusLabel = { pending: '⏳ Menunggu', confirmed: '✅ Disahkan', cancelled: '❌ Dibatal', completed: '🏁 Selesai' }[b.booking_status] || b.booking_status;

        var html = '';

        // Booking info
        html += '<div class="detail-section"><div class="detail-section-title">📋 Maklumat Tempahan</div><div class="detail-grid">' +
            di('No. Booking', '<span style="font-family:var(--mono);">' + esc(b.booking_no) + '</span>') +
            di('Status', '<span style="font-weight:800;color:' + statusColor + ';">' + statusLabel + '</span>') +
            di('Bayaran', b.payment_label || b.payment_status || '-') +
            di('Tarikh Tempahan', fmtDateTime(b.created_at)) +
            di('Jumlah', '<span style="color:var(--green);font-weight:800;font-family:var(--mono);">RM ' + numFmt(b.total_amount || 0) + '</span>') +
            di('Sumber', b.source || 'admin') +
        '</div></div>';

        // Customer info
        html += '<div class="detail-section"><div class="detail-section-title">👤 Maklumat Pelanggan</div><div class="detail-grid">' +
            di('Nama', c.name || '-') +
            di('No. IC', '<span style="font-family:var(--mono);">' + fmtIC(c.ic) + '</span>') +
            di('Telefon', c.phone || '-') +
            di('Emel', c.email || '-') +
            di('Jantina', c.gender === 'female' ? 'Perempuan' : (c.gender === 'male' ? 'Lelaki' : '-')) +
            di('Tempat Lahir', c.country_of_birth || '-') +
            di('Negeri', (c.state && c.state.name) ? c.state.name : (c.state || '-')) +
        '</div></div>';

        // Passport info
        html += '<div class="detail-section"><div class="detail-section-title">🛂 Maklumat Pasport</div><div class="detail-grid">' +
            di('No. Pasport', '<span style="font-family:var(--mono);">' + esc(c.passport_no || '-') + '</span>') +
            di('Warganegara', c.pp_warganegara || '-') +
            
            di('Tarikh Lahir', fmtDate(c.dob || c.birth_date)) +
            di('Dikeluarkan', fmtDate(c.pp_date_issued)) +
            di('Tarikh Luput', fmtDate(c.pp_date_expired)) +
        '</div></div>';

        // Trip info
        html += '<div class="detail-section"><div class="detail-section-title">✈️ Maklumat Trip</div><div class="detail-grid">' +
            di('Destinasi', esc((trip.location || '-') + ' (' + (trip.country || '-') + ')')) +
            di('Syarikat', esc(co.name || '-')) +
            di('Tarikh Bertolak', trip.date_from ? fmtDate(trip.date_from) : '-') +
            di('Tarikh Balik',    trip.date_to   ? fmtDate(trip.date_to)   : '-') +
            di('Dewasa',      (b.pax_adult || 0) + ' <span style="color:var(--text-4);font-size:.78rem;">(RM' + numFmt(trip.price_adult || 0) + '/org)</span>') +
            di('Kanak-kanak', (b.pax_child || 0) + ' <span style="color:var(--text-4);font-size:.78rem;">(RM' + numFmt(trip.price_child || 0) + '/org)</span>') +
            di('Bayi',        (b.pax_baby  || 0) + ' <span style="color:var(--text-4);font-size:.78rem;">(RM' + numFmt(trip.price_baby  || 0) + '/org)</span>') +
        '</div></div>';

        // Traveler list
        if (b.traveler_details && b.traveler_details.length) {
            html += '<div class="detail-section"><div class="detail-section-title">🛂 Senarai Pelancong</div>' +
                '<table class="trav-modal-table"><thead><tr>' +
                    '<th>#</th><th>Nama</th><th>IC</th><th>Tarikh Lahir</th><th>Jantina</th><th>No. Pasport</th>' +
                '</tr></thead><tbody>';
            b.traveler_details.forEach(function (t, i) {
                html += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + esc(t.name || '-') + '</td>' +
                    '<td>' + esc(t.ic || '-') + '</td>' +
                    '<td>' + (t.dob ? fmtDate(t.dob) : '-') + '</td>' +
                    '<td>' + esc(t.gender || '-') + '</td>' +
                    '<td>' + esc(t.passport_no || '-') + '</td>' +
                '</tr>';
            });
            html += '</tbody></table></div>';
        } else if (b.booking_status === 'confirmed') {
            html += '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:.75rem 1rem;font-size:.82rem;color:#92400e;margin-bottom:1rem;">⏳ Pelanggan belum mengisi maklumat pelancong</div>';
        }

        // Notes & rejection
        if (b.notes) {
            html += '<div class="detail-section"><div class="detail-section-title">📝 Nota</div>' +
                '<div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:.75rem 1rem;font-size:.84rem;color:var(--text-2);">' + esc(b.notes) + '</div></div>';
        }
        if (b.rejection_reason) {
            html += '<div style="background:#fff5f5;border:1px solid #fecdd3;border-radius:10px;padding:.75rem 1rem;font-size:.82rem;color:#b91c1c;">❌ Sebab Penolakan: <strong>' + esc(b.rejection_reason) + '</strong></div>';
        }

        $('#viewModalTitle').text('Detail — ' + (b.booking_no || ''));
        $('#viewModalBody').html(html);
    }

    function di(label, value) {
        return '<div><div class="di-label">' + label + '</div><div class="di-value">' + value + '</div></div>';
    }

    /* ─────────────────────────────────────────────────────────
     * Formatters & helpers
     * ───────────────────────────────────────────────────────── */
    function esc(s) {
        return (s || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function numFmt(n) {
        return parseFloat(n).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(val) {
        if (!val || val === 'null') return '-';
        var p = String(val).substring(0, 10).split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : val;
    }

    function fmtDateTime(val) {
        if (!val) return '-';
        var d = new Date(val);
        return isNaN(d) ? val : d.toLocaleDateString('en-MY') + ' ' + d.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' });
    }

    function fmtIC(ic) {
        var raw = (ic || '').replace(/\D/g, '');
        return raw.length >= 12 ? raw.slice(0, 6) + '-' + raw.slice(6, 8) + '-' + raw.slice(8) : (ic || '-');
    }

    function showError(msg) { window.showNotification && window.showNotification(msg, 'error'); }

    /* ─────────────────────────────────────────────────────────
     * Init
     * ───────────────────────────────────────────────────────── */
    loadStats();
    loadCompanyFilter();
    loadTripFilter();

    $('#fStatus').val('pending');
    runSearch();

    setInterval(loadStats, 120000);

});
</script>
@endpush