{{-- ============================================================ --}}
{{-- resources/views/partials/styles/app-styles.blade.php        --}}
{{-- CSS variables, component styles, dark mode overrides        --}}
{{-- ============================================================ --}}
<style>
/* ── TOURIST PORTAL BUTTON (sidebar) ────────────────────────── */
.sidebar-tourist-btn {
    display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;
    margin:0 .75rem .5rem;border-radius:10px;
    background:linear-gradient(135deg,rgba(0,212,170,.15),rgba(0,212,170,.08));
    border:1.5px solid rgba(0,212,170,.35);color:#00b894;
    font-size:.88rem;font-weight:700;text-decoration:none;cursor:pointer;transition:all .2s;
}
.sidebar-tourist-btn:hover{background:linear-gradient(135deg,rgba(0,212,170,.28),rgba(0,212,170,.15));border-color:#00d4aa;transform:translateX(3px);}
.sidebar-tourist-btn .sidebar-icon{font-size:1.1rem;width:22px;text-align:center;}

/* ── TRIP CARD ───────────────────────────────────────────────── */
.tc-card{
    background:var(--bg-primary);border:1px solid var(--border-color);
    border-radius:18px;overflow:hidden;
    box-shadow:0 2px 14px rgba(0,0,0,.055);
    transition:transform .2s cubic-bezier(.34,1.56,.64,1),box-shadow .2s;
}
.tc-card:hover{transform:translateY(-5px);box-shadow:0 12px 32px rgba(0,0,0,.12);}
.tc-img{aspect-ratio:16/9;background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;}
.tc-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s;}
.tc-card:hover .tc-img img{transform:scale(1.04);}
.tc-body{padding:1.15rem 1.2rem 1.2rem;}
.tc-title{font-size:1rem;font-weight:800;color:var(--text-primary);margin-bottom:.22rem;line-height:1.3;}
.tc-date{font-size:.77rem;color:var(--text-muted);margin-bottom:.85rem;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;}
.tc-prices{display:grid;grid-template-columns:repeat(2,1fr);gap:.38rem;margin-bottom:.85rem;}
.tc-pi{background:var(--input-readonly-bg);padding:.42rem .65rem;border-radius:9px;}
.tc-pi-label{font-size:.67rem;color:var(--text-muted);margin-bottom:1px;}
.tc-pi-val{font-weight:800;font-size:.82rem;}
.tc-actions{display:flex;gap:.42rem;}

/* ── BADGES ──────────────────────────────────────────────────── */
.badge-has-booking{font-size:.71rem;background:#ebf4ff;color:#3182ce;border:1px solid #bee3f8;border-radius:8px;padding:2px 9px;font-weight:700;}
.badge-kosong{font-size:.71rem;background:#f0fff4;color:#38a169;border:1px solid #c6f6d5;border-radius:8px;padding:2px 9px;font-weight:700;}
.badge-company{font-size:.7rem;background:rgba(255, 255, 255, 0.908);color:#667eea;border:1px solid rgb(0, 20, 110);border-radius:6px;padding:2px 8px;font-weight:700;}

/* ── MANAGE TRIP VIEW ────────────────────────────────────────── */
.mt-stat{background:var(--bg-primary);border:1px solid var(--border-color);border-radius:14px;padding:1.15rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.05);}
.mt-stat-num{font-size:1.9rem;font-weight:800;line-height:1.1;}
.mt-stat-label{font-size:.76rem;color:var(--text-muted);margin-top:4px;}
.mt-row:hover{background:var(--input-readonly-bg);}
.mt-row td{padding:.9rem 1.25rem;vertical-align:middle;border-top:1px solid var(--border-color);}

/* ── ITINERARY ───────────────────────────────────────────────── */
.itin-card{
    margin-bottom:.8rem;padding:1.15rem;
    border:1.5px solid var(--border-color);border-radius:13px;
    background:var(--bg-primary);
    box-shadow:0 1px 5px rgba(0,0,0,.04);
    transition:border-color .15s,box-shadow .15s;
}
.itin-card:hover{border-color:#667eea55;box-shadow:0 3px 12px rgba(102,126,234,.1);}
.itin-header{display:flex;align-items:center;gap:.75rem;margin-bottom:.85rem;flex-wrap:wrap;}
.itin-day-badge{display:inline-flex;align-items:center;gap:.4rem;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:9px;padding:.28rem .75rem;}
.itin-day-badge span{font-size:.75rem;font-weight:600;}
.itin-day-input{width:52px;background:rgba(255,255,255,.22);border:1.5px solid rgba(255,255,255,.42);color:#fff;font-weight:800;text-align:center;padding:.24rem .3rem;font-size:.92rem;border-radius:6px;outline:none;}
.itin-day-input:focus{border-color:rgba(255,255,255,.85);}
.itin-remove{margin-left:auto;background:rgba(229,62,62,.1);border:1.5px solid rgba(229,62,62,.3);color:#e53e3e;border-radius:8px;padding:4px 14px;cursor:pointer;font-size:.8rem;font-weight:700;transition:all .15s;}
.itin-remove:hover{background:#e53e3e;color:#fff;}
.itin-date-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.65rem;}
.itin-time-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
.itin-lbl{font-size:.76rem;font-weight:700;color:var(--text-secondary);margin-bottom:.28rem;display:block;}
.itin-day-chip{display:inline-flex;align-items:center;padding:.3rem .75rem;border-radius:8px;
    font-size:.8rem;font-weight:700;background:rgba(102,126,234,.13);color:#667eea;
    border:1.5px solid rgba(102,126,234,.28);white-space:nowrap;min-height:32px;}

/* ── EDIT TRIP PHOTO SLOTS ───────────────────────────────────── */
.et-photo-slot {
    flex: 1 1 0; min-width: 0;
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    background: var(--input-readonly-bg);
    transition: border-color .2s;
    position: relative;
}
.et-photo-slot:hover { border-color: #667eea; }
.et-photo-label {
    font-size: .72rem; font-weight: 700; color: var(--text-secondary);
    text-align: center; display: block; padding: .4rem 0 .3rem;
    background: var(--input-readonly-bg);
}
.et-photo-thumb {
    width: 100%; aspect-ratio: 16/9; object-fit: cover;
    display: none; cursor: pointer;
}
.et-photo-placeholder {
    aspect-ratio: 16/9;
    display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: .3rem; cursor: pointer;
    color: var(--text-muted); font-size: .78rem;
}
.et-photo-placeholder .et-icon { font-size: 1.6rem; }
.et-photo-actions {
    display: flex; gap: .35rem; padding: .45rem .5rem;
    background: var(--input-readonly-bg);
    border-top: 1px solid var(--border-color);
}
.et-photo-actions button {
    flex: 1; padding: 4px; font-size: .72rem; border-radius: 6px;
    border: 1.5px solid var(--border-color); cursor: pointer;
    background: var(--card-bg); color: var(--text-primary); font-weight: 600;
    transition: background .15s;
}
.et-photo-actions button:hover { background: #667eea; color: #fff; border-color: #667eea; }
.et-photo-actions .et-remove-btn { color: #e53e3e; border-color: #fc8181; display:none; }
.et-photo-actions .et-remove-btn:hover { background: #e53e3e; color: #fff; border-color: #e53e3e; }

/* ── SIDEBAR RESPONSIVE ──────────────────────────────────────── */
.sidebar {
    overflow-y: auto;
    overflow-x: hidden;
    scroll-behavior: smooth;
}
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.3); }

@media (max-width: 1024px) {
    .sidebar {
        position: fixed !important;
        top: 0 !important;
        left: -260px !important;
        width: 250px !important;
        height: 100vh !important;
        z-index: 900 !important;
        transition: left .3s cubic-bezier(.4,0,.2,1) !important;
        box-shadow: none;
        padding-top: 70px !important;
    }
    body.sidebar-open .sidebar {
        left: 0 !important;
        box-shadow: 4px 0 30px rgba(0,0,0,.25) !important;
    }
    .main-content { margin-left: 0 !important; width: 100% !important; }
    .top-navbar { left: 0 !important; }
}
@media (max-width: 768px) {
    .sidebar { width: 240px !important; left: -250px !important; padding-top: 70px !important; }
    body.sidebar-open .sidebar { left: 0 !important; }
    .main-content { padding: calc(70px + 1rem) 1rem 2rem !important; }
}

/* ── DARK MODE OVERRIDES ─────────────────────────────────────── */
body.dark [style*="background:#1a1a2e"]  { background: #0d1117 !important; }
body.dark [style*="background:#d4edda"]  { background:rgba(74,222,128,.15) !important; color:#4ade80 !important; }
body.dark [style*="background:#f8d7da"]  { background:rgba(248,113,113,.15) !important; color:#f87171 !important; }
body.dark [style*="background:#fff3cd"]  { background:rgba(251,191,36,.15)  !important; color:#fbbf24 !important; }
body.dark [style*="background:#d1ecf1"]  { background:rgba(96,165,250,.15)  !important; color:#60a5fa !important; }
body.dark [style*="background:#f0fff4"]  { background:rgba(74,222,128,.12)  !important; color:#4ade80 !important; }
body.dark [style*="background:#fffaf0"]  { background:rgba(251,191,36,.12)  !important; color:#fbbf24 !important; }
body.dark [style*="background:#f7fafc"],
body.dark [style*="background:#f7f9fb"],
body.dark [style*="background:#edf2f7"],
body.dark [style*="background:#ebf8ff"],
body.dark [style*="background:white"],
body.dark [style*="background:#ffffff"],
body.dark [style*="background: white"]   { background: var(--input-readonly-bg) !important; }
body.dark [style*="border:1px solid #e2e8f0"],
body.dark [style*="border-bottom:1px solid #e2e8f0"] { border-color: var(--border-color) !important; }
body.dark [style*="color:#666"],
body.dark [style*="color:#555"],
body.dark [style*="color:#777"],
body.dark [style*="color:#888"],
body.dark [style*="color:#999"]          { color: var(--text-secondary) !important; }
body.dark [style*="color:#2d3748"],
body.dark [style*="color:#333"]          { color: var(--text-primary) !important; }
body.dark [style*="color:#718096"],
body.dark [style*="color:#4a5568"],
body.dark [style*="color:#a0aec0"]       { color: var(--text-muted) !important; }
body.dark #uploadSoundBtn  { background: var(--bg-primary) !important; color: #818cf8 !important; }
body.dark select option    { background: var(--bg-primary); color: var(--text-primary); }
body.dark input::placeholder,
body.dark textarea::placeholder          { color: var(--text-muted); opacity:.7; }
body.dark kbd { background: var(--input-readonly-bg); color: var(--text-primary); border-color: var(--border-color); }

</style>