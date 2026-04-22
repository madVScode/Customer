{{-- resources/views/customers/index.blade.php --}}
{{-- ✅ notifications.js diload dalam app.blade.php — JANGAN load semula di sini --}}
@extends('layouts.app')
@section('title', 'Customer Management System')
@section('content')
    
    {{-- ======================================================= --}}
    {{-- UNIFIED DASHBOARD VIEW - MERGED + PASSPORT DROPDOWN --}}
    {{-- ============================================================ --}}
    <div id="dashboardView" class="view-section">
    
    <style>
/* ── Booking Dashboard v4.0 + Passport Dropdown ── */
.dash-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1d4ed8 100%);
    border-radius: 20px;
    padding: 2rem 2.5rem;
    margin-bottom: 1.75rem;
    color: white;
    position: relative;
    overflow: hidden;
}
.dash-hero::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:280px; height:280px; background:rgba(255,255,255,.04); border-radius:50%;
}
.dash-hero::after {
    content:''; position:absolute; bottom:-80px; left:25%;
    width:320px; height:320px; background:rgba(59,130,246,.08); border-radius:50%;
}
.hero-content { position:relative; z-index:1; }
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: .85rem;
    margin-top: 1.25rem;
}
.stat-card {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 14px;
    padding: 1rem 1.1rem;
    backdrop-filter: blur(8px);
    transition: background .2s;
}
.stat-card:hover { background: rgba(255,255,255,.13); }
.stat-num {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: .25rem;
}
.stat-label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    opacity: .65;
}
.stat-card.s-pending  .stat-num { color: #fbbf24; }
.stat-card.s-confirm  .stat-num { color: #34d399; }
.stat-card.s-today    .stat-num { color: #60a5fa; }
.stat-card.s-need     .stat-num { color: #f87171; }
.stat-card.s-revenue  .stat-num { color: #a78bfa; font-size:1.3rem; }
.filter-bar {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 1.1rem 1.4rem;
    margin-bottom: 1.1rem;
    display: flex;
    gap: .65rem;
    align-items: flex-end;
    flex-wrap: wrap;
}
.ff { flex: 1; min-width: 140px; }
.ff label {
    display: block;
    font-size: .7rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: .35rem;
}
.ff input, .ff select {
    width: 100%;
    padding: .5rem .8rem;
    border: 1.5px solid var(--border-color);
    border-radius: 10px;
    background: var(--input-bg);
    color: var(--text-primary);
    font-size: .85rem;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
}
.ff input:focus, .ff select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.1);
}
.passport-dd {
    padding: .4rem .7rem;
    border: 1.5px solid var(--border-color);
    border-radius: 8px;
    background: var(--input-bg);
    font-size: .78rem;
    font-weight: 600;
    color: var(--text-primary);
    min-width: 120px;
}
.passport-dd:focus { border-color: #3b82f6; }
.passport-dd.pending  { border-left: 3px solid #f59e0b; }
.passport-dd.approved { border-left: 3px solid #10b981; }
.passport-dd.missing  { border-left: 3px solid #ef4444; }
/* ... [rest dashboard CSS from backup] ... */
.results-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .85rem;
    flex-wrap: wrap;
    gap: .5rem;
}
.count-badge {
    background: #eff6ff;
    color: #1d4ed8;
    font-size: .75rem;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 20px;
    border: 1px solid #bfdbfe;
    margin-left: .4rem;
}
.bk-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 1rem;
}
.bk-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    overflow: hidden;
    transition: box-shadow .2s, transform .15s;
    position: relative;
}
.bk-card:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,.09);
    transform: translateY(-2px);
}
.bk-card.status-pending   { border-left: 4px solid #f59e0b; }
.bk-card.status-confirmed { border-left: 4px solid #10b981; }
.bk-card.status-cancelled { border-left: 4px solid #ef4444; }
.bk-card.status-completed { border-left: 4px solid #3b82f6; }
.bkh {
    padding: .85rem 1.1rem;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
    border-bottom: 1px solid var(--border-color);
}
.bk-no {
    font-family: monospace;
    font-size: .8rem;
    font-weight: 800;
    color: var(--text-muted);
    margin-bottom: .2rem;
}
.bk-trip {
    font-size: .95rem;
    font-weight: 800;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}
.bk-company {
    font-size: .72rem;
    color: var(--text-muted);
    margin-top: .1rem;
}
.bk-badge {
    padding: .22rem .75rem;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 800;
    white-space: nowrap;
    flex-shrink: 0;
}
.bk-badge.pending  { background:#fef3c7; color:#92400e; }
.bk-badge.confirmed{ background:#d1fae5; color:#065f46; }
.bk-badge.cancelled{ background:#fee2e2; color:#991b1b; }
.bk-badge.completed{ background:#dbeafe; color:#1e40af; }
.bkb { padding: .85rem 1.1rem; }
.bk-row {
    display: flex;
    align-items: center;
    gap: .45rem;
    padding: .22rem 0;
    font-size: .8rem;
    border-bottom: 1px solid var(--border-color);
}
.bk-row:last-child { border-bottom: none; }
.bk-row-icon  { font-size:.82rem; width:18px; text-align:center; flex-shrink:0; color:var(--text-muted); }
.bk-row-label { color:var(--text-muted); font-size:.7rem; width:70px; flex-shrink:0; }
.bk-row-value { color:var(--text-primary); font-weight:600; flex:1; }
.btn-approve, .btn-reject, .btn-view {
    padding: .42rem .9rem;
    border: none;
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    transition: opacity .2s, border-color .2s;
}
.btn-approve {
    background: linear-gradient(135deg, #059669, #10b981);
    color: white;
}
.btn-approve:hover { opacity: .88; }
.btn-approve:disabled { opacity: .45; cursor: not-allowed; }
.btn-reject {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: white;
}
.btn-reject:hover { opacity: .88; }
.btn-reject:disabled { opacity: .45; cursor: not-allowed; }
.btn-view {
    background: var(--input-readonly-bg);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
.btn-view:hover { border-color: #3b82f6; color: #2563eb; }
.bka {
    padding: .8rem 1.1rem;
    background: var(--input-readonly-bg);
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
}
.rejection-note {
    background: #fff5f5;
    border-top: 1px solid #fed7d7;
    padding: .6rem 1.1rem;
    font-size: .77rem;
    color: #c53030;
}
.details-alert {
    background: #fffbeb;
    border-top: 1px solid #fef08a;
    padding: .55rem 1.1rem;
    font-size: .77rem;
    color: #92400e;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.state-box {
    text-align: center;
    padding: 3rem 2rem;
    background: var(--card-bg);
    border-radius: 16px;
    border: 1px solid var(--border-color);
}
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 1.75rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
}
.modal-title {
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.modal-textarea {
    width: 100%;
    padding: .75rem .9rem;
    border: 1.5px solid var(--border-color);
    border-radius: 10px;
    font-family: inherit;
    font-size: .9rem;
    color: var(--text-primary);
    background: var(--input-bg);
    resize: none;
    outline: none;
    transition: border-color .2s;
}
.modal-textarea:focus { border-color: #ef4444; }
.modal-actions { 
    display: flex; 
    gap: .6rem; 
    margin-top: 1rem; 
    justify-content: flex-end; 
}
.detail-modal-box {
    background: var(--card-bg);
    border-radius: 16px;
    width: 100%;
    max-width: 620px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
}
.detail-modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: var(--card-bg);
    z-index: 1;
}
.detail-modal-body { padding: 1.25rem 1.5rem; }
.detail-section { margin-bottom: 1.25rem; }
.detail-section-title {
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-color);
    padding-bottom: .5rem;
    margin-bottom: .85rem;
}
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem 1rem; }
.di-label { font-size: .72rem; color: var(--text-muted); margin-bottom: .15rem; }
.di-value { font-size: .88rem; font-weight: 700; color: var(--text-primary); }
.trav-modal-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .8rem;
}
.trav-modal-table th {
    background: var(--input-readonly-bg);
    padding: 6px 10px;
    text-align: left;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing:.ConcurrentModificationException04em;
    color: var(--text-muted);
}
.trav-modal-table td {
    padding: 7px 10px;
    border-bottom: 1px solid var(--border-color);
}
.trav-modal-table tr:last-child td { border-bottom: none; }
@media (max-width: 640px) {
    .bk-grid { grid-template-columns: 1fr; }
    .filter-bar { flex-direction: column; }
    .ff { min-width: 100%; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
.passport-toggle {
    width: 100%;
    padding: .5rem 1.1rem;
    border: none;
    background: var(--input-readonly-bg);
    color: var(--text-secondary);
    font-size: .75rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    gap: .4rem;
    padding: .7rem 1.1rem;
    border-top: 1px solid var(--border-color);
    flex-wrap: wrap;
}
</style>

<div id="dashContainer">

    <!-- Hero & Stats -->
    <div class="dash-hero">
        <div class="hero-content">
            <div style="font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.6;margin-bottom:.4rem;">
                Admin Dashboard
            </div>
            <h1 style="font-size:1.55rem;font-weight:800;margin:0 0 .25rem;letter-spacing:-.5px;">
                📋 Pengurusan Tempahan
            </h1>
            <p style="font-size:.83rem;opacity:.6;margin:0;">
                Semak, luluskan atau tolak tempahan dari pelancong
            </p>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-num" id="dashStatTotal">—</div>
                    <div class="stat-label">Jumlah</div>
                </div>
                <div class="stat-card s-pending">
                    <div class="stat-num" id="dashStatPending">—</div>
                    <div class="stat-label">⏳ Menunggu</div>
                </div>
                <div class="stat-card s-confirm">
                    <div class="stat-num" id="dashStatConfirmed">—</div>
                    <div class="stat-label">✅ Disahkan</div>
                </div>
                <div class="stat-card s-today">
                    <div class="stat-num" id="dashStatToday">—</div>
                    <div class="stat-label">📅 Hari Ini</div>
                </div>
                <div class="stat-card s-need">
                    <div class="stat-num" id="dashStatNeed">—</div>
                    <div class="stat-label">📝 Perlu Info</div>
                </div>
                <div class="stat-card s-revenue">
                    <div class="stat-num" id="dashStatRevenue">—</div>
                    <div class="stat-label">💰 Revenue</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="ff">
            <label>🏢 Syarikat</label>
            <select id="dashFCompany">
                <option value="">Semua Syarikat</option>
            </select>
        </div>
        <div class="ff" style="max-width:165px;">
            <label>📊 Status</label>
            <select id="dashFStatus">
                <option value="">Semua Status</option>
                <option value="pending">⏳ Menunggu</option>
                <option value="confirmed">✅ Disahkan</option>
                <option value="cancelled">❌ Dibatal</option>
                <option value="completed">🏁 Selesai</option>
            </select>
        </div>
        <div class="ff" style="max-width:175px;">
            <label>💳 Bayaran</label>
            <select id="dashFPayment">
                <option value="">Semua</option>
                <option value="unpaid">Belum Bayar</option>
                <option value="partial">Deposit</option>
                <option value="paid">Lunas</option>
            </select>
        </div>
        <div class="ff">
            <label>🔍 Cari nama / no. booking</label>
            <input id="dashFSearch" type="text" placeholder="Nama pelanggan atau BKG-...">
        </div>
        <div class="ff" style="max-width:145px;">
            <label>📅 Dari</label>
            <input id="dashFDateFrom" type="date">
        </div>
        <div class="ff" style="max-width:145px;">
            <label>📅 Hingga</label>
            <input id="dashFDateTo" type="date">
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-end;">
            <button id="dashBtnSearch" class="btn btn-primary" style="padding:.5rem 1rem;font-size:.85rem;white-space:nowrap;">
                🔍 Cari
            </button>
            <button id="dashBtnReset" class="btn btn-secondary" style="padding:.5rem .85rem;font-size:.85rem;" title="Reset">
                ✕
            </button>
        </div>
    </div>

    <!-- Results Bar -->
    <div class="results-bar">
        <div style="font-size:.95rem;font-weight:700;color:var(--text-primary);">
            Senarai Tempahan
            <span class="count-badge" id="dashResultsCount">0 rekod</span>
        </div>
        <div id="dashLastUpdate" style="font-size:.73rem;color:var(--text-muted);"></div>
    </div>

    <!-- Booking Cards -->
    <div id="dashBkGrid" class="bk-grid"></div>
    <div id="dashBkEmpty" class="state-box" style="display:none;">
        <div style="font-size:3rem;margin-bottom:.75rem;">📋</div>
        <div style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:.4rem;">
            Tiada tempahan dijumpai
        </div>
        <div style="font-size:.85rem;color:var(--text-muted);">Cuba ubah filter carian</div>
    </div>
    <div id="dashBkLoading" class="state-box">
        <div style="font-size:1.75rem;margin-bottom:.6rem;">⏳</div>
        <div style="color:var(--primary-color);font-weight:600;font-size:.9rem;">Memuatkan tempahan...</div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="dashRejectModal">
    <div class="modal-box">
        <div class="modal-title">❌ Tolak Tempahan</div>
        <input type="hidden" id="dashRejectBookingId">
        <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:.85rem;">
            Booking: <strong id="dashRejectBookingNo" style="color:var(--text-primary);font-family:monospace;"></strong>
        </div>
        <textarea id="dashRejectReason" class="modal-textarea" rows="3" placeholder="Sebab penolakan (pilihan)..."></textarea>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="dashCancelRejectBtn">Batal</button>
            <button class="btn btn-danger" id="dashConfirmRejectBtn">❌ Sahkan Penolakan</button>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="dashViewModal">
    <div class="detail-modal-box">
        <div class="detail-modal-header">
            <div style="font-size:1rem;font-weight:800;" id="dashViewModalTitle">Detail Tempahan</div>
            <button id="dashCloseViewModal" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:var(--text-muted);">✕</button>
        </div>
        <div class="detail-modal-body" id="dashViewModalBody"></div>
    </div>
</div>

    @include('modules.customers.form')
    @include('modules.companies.index')
    @include('modules.trips.add')
    @include('modules.trips.index')
    @include('modules.trips.manage')
    @include('modules.receipts.create')
    @include('modules.receipts.index')
    @include('modules.bookings.create')
    @include('modules.bookings.index')
    @include('modules.settings.index')
@endsection

@push('scripts')
<script>
$(function() {
 
// ══════════════════════════════════════════════════════════
// DASHBOARD BOOKING MANAGEMENT
// ══════════════════════════════════════════════════════════
 
var dashSearchTimer, dashCsrfToken = $('meta[name="csrf-token"]').attr('content');
 
// Load stats
function dashLoadStats() {
    $.get('/bookings/dash-stats', function(res) {
        if (!res.success) return;
        var d = res.data;
        $('#dashStatTotal').text(d.total || 0);
        $('#dashStatPending').text(d.pending || 0);
        $('#dashStatConfirmed').text(d.confirmed || 0);
        $('#dashStatToday').text(d.today || 0);
        $('#dashStatNeed').text(d.need_details || 0);
        $('#dashStatRevenue').text(d.revenue > 0 ? 'RM ' + dashNumFmt(d.revenue) : 'RM 0');
    }).fail(function(){});
}
 
// Load company filter
function dashLoadCompanyFilter() {
    $.get('/companies', function(res) {
        if (!res.success) return;
        var $sel = $('#dashFCompany').find('option:not(:first)').remove().end();
        res.data.forEach(function(c) {
            $sel.append('<option value="' + c.id + '">' + c.name + '</option>');
        });
    }).fail(function(){});
}
 
// Search params
function dashGetSearchParams() {
    return {
        customer_name: $('#dashFSearch').val().trim(),
        booking_status: $('#dashFStatus').val(),
        payment_status: $('#dashFPayment').val(),
        company_id: $('#dashFCompany').val(),
        date_from: $('#dashFDateFrom').val(),
        date_to: $('#dashFDateTo').val(),
    };
}
 
// Run search
function dashRunSearch() {
    $('#dashBkGrid').empty();
    $('#dashBkEmpty').hide();
    $('#dashBkLoading').show();
 
    $.ajax({
        url: '/bookings/search', 
        type: 'GET', 
        data: dashGetSearchParams(),
        success: function(res) {
            $('#dashBkLoading').hide();
            if (res.success) dashRenderCards(res.data);
            else dashShowError('Carian gagal.');
        },
        error: function() {
            $('#dashBkLoading').hide();
            dashShowError('Ralat server.');
        }
    });
}
 
// Render booking cards WITH PASSPORT DROPDOWN
function dashRenderCards(bookings) {
    var $grid = $('#dashBkGrid');
    $grid.empty();
    $('#dashResultsCount').text(bookings.length + ' rekod');
    $('#dashLastUpdate').text('Kemaskini: ' + new Date().toLocaleTimeString('ms-MY'));
 
    if (!bookings.length) { 
        $('#dashBkEmpty').show(); 
        return; 
    }
    $('#dashBkEmpty').hide();
 
    bookings.forEach(function(b) {
        var status = b.booking_status || 'pending';
        var passportStatus = b.passport_status || ''; // NEW: Passport field
        
        // Status badges
        var statusLabel = {
            pending:'⏳ Menunggu', confirmed:'✅ Disahkan',
            cancelled:'❌ Dibatal', completed:'🏁 Selesai'
        }[status] || status;
 
        var passportLabel = {
            '':'📄 Pilih', 'pending':'⏳ Pending', 
            'approved':'✅ Approved', 'missing':'❌ Missing'
        }[passportStatus] || '📄 Pilih';
 
        // Row helpers
        function dashBkRow(icon, label, value) {
            return '<div class="bk-row">' +
                '<span class="bk-row-icon">' + icon + '</span>' +
                '<span class="bk-row-label">' + label + '</span>' +
                '<span class="bk-row-value">' + value + '</span>' +
            '</div>';
        }
 
        // Action buttons + NEW PASSPORT DROPDOWN
        var passportDD = '<select class="passport-dd ' + passportStatus + '" data-id="' + b.id + '">' +
            '<option value="">📄 Pilih</option>' +
            '<option value="pending" ' + (passportStatus === 'pending' ? 'selected' : '') + '>⏳ Pending</option>' +
            '<option value="approved" ' + (passportStatus === 'approved' ? 'selected' : '') + '>✅ Approved</option>' +
            '<option value="missing" ' + (passportStatus === 'missing' ? 'selected' : '') + '>❌ Missing</option>' +
            '</select>';
 
        var approveBtn = '<button class="btn-approve" data-id="' + b.id + '" ' + (status === 'pending' ? '' : 'disabled') + '>✅ Lulus</button>';
        var rejectBtn = '<button class="btn-reject" data-id="' + b.id + '" data-no="' + b.booking_no + '" ' + (status === 'pending' ? '' : 'disabled') + '>❌ Tolak</button>';
        var viewBtn = '<button class="btn-view" data-booking=\'' + JSON.stringify(b).replace(/'/g,"&#39;") + '\'>👁️ Detail</button>';
 
        var html = '<div class="bk-card status-' + status + '" id="dashBkCard-' + b.id + '">' +
            '<div class="bkh">' +
                '<div style="min-width:0;">' +
                    '<div class="bk-no">' + b.booking_no + '</div>' +
                    '<div class="bk-trip">' + (b.trip?.location || '-') + '</div>' +
                    '<div class="bk-company">🏢 ' + (b.trip?.company?.name || '-') + '</div>' +
                '</div>' +
                '<span class="bk-badge ' + status + '">' + statusLabel + '</span>' +
            '</div>' +
            '<div class="bkb">' +
                dashBkRow('👤', 'Pelanggan', b.customer?.name || '-') +
                dashBkRow('🪪', 'IC', dashFmtIC(b.customer?.ic)) +
                dashBkRow('📱', 'Telefon', b.customer?.phone || '-') +
                dashBkRow('✈️', 'Destinasi', (b.trip?.location || '-') + ' (' + dashFmtDate(b.trip?.date_from) + ' → ' + dashFmtDate(b.trip?.date_to) + ')') +
                dashBkRow('👥', 'Pelancong', (b.pax_adult||0) + 'D / ' + (b.pax_child||0) + 'K / ' + (b.pax_baby||0) + 'B') +
                dashBkRow('💰', 'Jumlah', '<strong style="color:#059669;">RM ' + dashNumFmt(b.total_amount||0) + '</strong>') +
                dashBkRow('💳', 'Bayaran', b.payment_label || b.payment_status || '-') +
                dashBkRow('📅', 'Tarikh', dashFmtDateTime(b.created_at)) +
            '</div>' +
            '<div class="bka">' +
                passportDD +
                approveBtn + rejectBtn + viewBtn +
            '</div>' +
            '</div>';
 
        $grid.append(html);
    });
}
 
// PASSPORT DROPDOWN HANDLER - NEW FEATURE
$(document).on('change', '.passport-dd', function() {
    var $select = $(this);
    var bookingId = $select.data('id');
    var status = $select.val();
 
    $select.prop('disabled', true).addClass('loading');
 
    $.ajax({
        url: '/bookings/' + bookingId + '/passport-status',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': dashCsrfToken },
        data: { passport_status: status },
        success: function(res) {
            if (res.success) {
                window.showNotification && window.showNotification('Passport status dikemaskini ✅', 'success');
                $select.removeClass('loading').addClass(status);
            } else {
                window.showNotification && window.showNotification(res.message || 'Gagal kemaskini', 'error');
                $select.val(''); // Reset
            }
        },
        error: function() {
            window.showNotification && window.showNotification('Ralat server', 'error');
            $select.val(''); // Reset
        },
        complete: function() {
            $select.prop('disabled', false).removeClass('loading');
        }
    });
});
 
// Search triggers
$('#dashFSearch').on('input', function() {
    clearTimeout(dashSearchTimer);
    dashSearchTimer = setTimeout(dashRunSearch, 380);
});
$('#dashFStatus, #dashFPayment, #dashFCompany').on('change', dashRunSearch);
$('#dashBtnSearch').on('click', dashRunSearch);
$('#dashBtnReset').on('click', function() {
    $('#dashFSearch, #dashFDateFrom, #dashFDateTo').val('');
    $('#dashFStatus, #dashFPayment, #dashFCompany').val('');
    dashRunSearch();
});
 
// Approve/Reject/View handlers
$(document).on('click', '.btn-approve', dashApprove);
$(document).on('click', '.btn-reject', dashOpenReject);
$(document).on('click', '.btn-view', dashViewDetail);
 
function dashApprove() {
    var $btn = $(this);
    var id = $btn.data('id');
    if ($btn.prop('disabled')) return;
    $btn.prop('disabled', true).text('⏳...');
    $.ajax({
        url: '/bookings/' + id + '/approve',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': dashCsrfToken },
        success: function(res) {
            if (res.success) {
                window.showNotification && window.showNotification(res.message, 'success');
                dashRunSearch();
                dashLoadStats();
            } else {
                $btn.prop('disabled', false).text('✅ Lulus');
                window.showNotification && window.showNotification(res.message, 'error');
            }
        },
        error: function() {
            $btn.prop('disabled', false).text('✅ Lulus');
            window.showNotification && window.showNotification('Gagal meluluskan.', 'error');
        }
    });
}
 
function dashOpenReject() {
    $('#dashRejectBookingId').val($(this).data('id'));
    $('#dashRejectBookingNo').text($(this).data('no'));
    $('#dashRejectReason').val('');
    $('#dashRejectModal').addClass('show');
}
 
$('#dashCancelRejectBtn').on('click', function() {
    $('#dashRejectModal').removeClass('show');
});
 
$('#dashConfirmRejectBtn').on('click', function() {
    var id = $('#dashRejectBookingId').val();
    var reason = $('#dashRejectReason').val().trim();
    $(this).prop('disabled', true).text('⏳...');
    $.ajax({
        url: '/bookings/' + id + '/reject',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': dashCsrfToken },
        data: { reason: reason },
        success: function(res) {
            $('#dashRejectModal').removeClass('show');
            if (res.success) {
                window.showNotification && window.showNotification(res.message, 'success');
                dashRunSearch();
                dashLoadStats();
            } else {
                window.showNotification && window.showNotification(res.message, 'error');
            }
        },
        error: function() {
            window.showNotification && window.showNotification('Gagal menolak.', 'error');
        },
        complete: function() {
            $('#dashConfirmRejectBtn').prop('disabled', false).text('❌ Sahkan Penolakan');
        }
    });
});
 
function dashViewDetail() {
    var b;
    try { b = JSON.parse($(this).attr('data-booking')); }
    catch(e) { return; }
    dashRenderDetailModal(b);
    $('#dashViewModal').addClass('show');
}
 
$('#dashCloseViewModal').on('click', function() {
    $('#dashViewModal').removeClass('show');
});
 
// Modal handlers
$(document).on('click', '#dashRejectModal, #dashViewModal', function(e) {
    if ($(e.target).is(this)) $(this).removeClass('show');
});
 
function dashRenderDetailModal(b) {
    var html = '<div class="detail-section">' +
        '<div class="detail-section-title">📋 Maklumat Tempahan</div>' +
        dashDi('No. Booking', b.booking_no) +
        dashDi('Status', b.booking_status || 'pending') +
        dashDi('Passport Status', b.passport_status || 'Pending') +
        '</div>';
    $('#dashViewModalTitle').text('Detail — ' + b.booking_no);
    $('#dashViewModalBody').html(html);
}
 
// DASHBOARD HELPERS
function dashEsc(s) { return (s || '').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function dashNumFmt(n) { return parseFloat(n).toLocaleString('en-MY', {minimumFractionDigits:2, maximumFractionDigits:2}); }
function dashFmtDate(val) { if (!val) return '-'; var p = String(val).substring(0,10).split('-'); return p.length === 3 ? p[2]+'/'+p[1]+'/'+p[0] : val; }
function dashFmtDateTime(val) { if (!val) return '-'; var d = new Date(val); return isNaN(d) ? val : d.toLocaleDateString('en-MY') + ' ' + d.toLocaleTimeString('en-MY', {hour:'2-digit', minute:'2-digit'}); }
function dashFmtIC(ic) { var raw = (ic||'').replace(/\D/g,''); if (raw.length >= 12) return raw.slice(0,6)+'-'+raw.slice(6,8)+'-'+raw.slice(8); return ic || '-'; }
function dashDi(label, value) { return '<div><div class="di-label">' + label + '</div><div class="di-value">' + value + '</div></div>'; }
function dashShowError(msg) { window.showNotification && window.showNotification(msg, 'error'); }
 
// ══════════════════════════════════════════════════════════
// FORMAT TARIKH
// ══════════════════════════════════════════════════════════
 
function formatDate(dateStr) {
    if (!dateStr || dateStr === '-') return dateStr || '-';
    var parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    return parts[2] + '/' + parts[1] + '/' + parts[0];
}
 
// ══════════════════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════════════════
 
var _manageTripId = null, _itinCount = 0;
function xEsc(s){ return $('<div>').text(s||'').html(); }
function xFmt(n){ return parseFloat(n||0).toLocaleString('ms-MY',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function xCsrf(){ return $('meta[name="csrf-token"]').attr('content'); }
 
// ══════════════════════════════════════════════════════════
// MANAGE TRIP
// ══════════════════════════════════════════════════════════
 
function goManageTrip(id, name) {
    _manageTripId = id;
    $('#manageTripTitle').text('Urus Pelanggan: ' + (name || 'Trip'));
    $('#manageTripSubtitle').text('');
    document.querySelectorAll('.view-section').forEach(function(el){ el.style.display='none'; });
    document.getElementById('manageTripView').style.display = 'block';
    document.querySelectorAll('.sidebar-link').forEach(function(el){ el.classList.remove('active'); });
    document.getElementById('viewTripsBtn').classList.add('active');
    loadManageTripData(id);
}
 
function goBackToTrips() {
    _manageTripId = null;
    document.querySelectorAll('.view-section').forEach(function(el){ el.style.display='none'; });
    document.getElementById('viewTripsView').style.display = 'block';
    loadTrips();
}
 
$('#btnBackToTrips').on('click', function(){ goBackToTrips(); });
$('#btnBackToTripsEmpty').on('click', function(){ goBackToTrips(); });
$('#btnRefreshManageTrip').on('click', function(){ if(_manageTripId) loadManageTripData(_manageTripId); });
 
// ══════════════════════════════════════════════════════════
// LOAD TRIPS
// ══════════════════════════════════════════════════════════
 
function loadTrips(params) {
    var p = {};
    var company=$('#filterTripCompany').val(), country=$('#filterTripCountry').val(), date=$('#filterTripDateFrom').val();
    if (company) p.company_id=company; if (country) p.country=country; if (date) p.date_from=date;
    if (params) $.extend(p, params);
    $('#tripsGrid').html('<div style="grid-column:1/-1;text-align:center;padding:3.5rem;color:var(--text-muted);"><div style="font-size:2.5rem;margin-bottom:.75rem;">⏳</div><div style="font-size:.9rem;font-weight:600;">Memuatkan trip...</div></div>');
    $.get('/trips', p, function(r) {
        if (!r.success) { $('#tripsGrid').html('<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);">Gagal memuatkan trip</div>'); return; }
        var data=r.data;
        $('#tripsCount').text('Jumlah: '+data.length+' trip');
        if (!data.length) { $('#tripsGrid').html('<div style="grid-column:1/-1;text-align:center;padding:4.5rem 2rem;"><div style="font-size:3.5rem;margin-bottom:1rem;">✈️</div><div style="font-weight:800;font-size:1.1rem;color:var(--text-primary);margin-bottom:.5rem;">Tiada trip ditemui</div><div style="color:var(--text-muted);font-size:.88rem;">Cuba ubah filter atau tambah trip baru</div></div>'); return; }
        var html='';
        data.forEach(function(t) {
            var bc=t.booking_count||0;
            var img=t.pic1_url?'<img src="'+t.pic1_url+'" alt="'+xEsc(t.location||t.country)+'">'  :'<div style="font-size:3.5rem;opacity:.3;">✈️</div>';
            var compBadge=(t.company&&t.company.name)?'<span class="badge-company">'+xEsc(t.company.name)+'</span>':'';
            var statusBadge=bc>0?'<span class="badge-has-booking">'+bc+' booking</span>':'<span class="badge-kosong">Kosong</span>';
            var dateFrom=formatDate(t.date_from||'-'), dateTo=formatDate(t.date_to||'-');
            var btns=bc>0
                ?'<button class="btn btn-primary tc-manage-btn" data-id="'+t.id+'" data-name="'+xEsc(t.location||t.country)+'" style="width:100%;font-size:.84rem;padding:.55rem;">👥 Urus Pelanggan <span style="background:rgba(255,255,255,.28);border-radius:10px;padding:1px 8px;font-size:.77rem;margin-left:4px;">'+bc+'</span></button>'
                :'<button class="btn btn-secondary tc-detail-btn" data-id="'+t.id+'" style="flex:1;font-size:.8rem;min-width:0;">📋 Detail</button><button class="btn btn-warning tc-edit-btn" data-id="'+t.id+'" style="padding:6px 11px;font-size:.85rem;">✏️</button><button class="btn btn-danger tc-del-btn" data-id="'+t.id+'" style="padding:6px 11px;font-size:.85rem;">🗑️</button>';
            html+='<div class="tc-card"><div class="tc-img">'+img+'<div style="position:absolute;top:.7rem;right:.7rem;display:flex;flex-direction:column;gap:.35rem;align-items:flex-end;">'+statusBadge+'</div>'+(compBadge?'<div style="position:absolute;bottom:.7rem;left:.7rem;">'+compBadge+'</div>':'')+'</div><div class="tc-body"><div class="tc-title">'+xEsc(t.location||t.country)+'</div><div class="tc-date"><span>📅</span><span>'+dateFrom+' — '+dateTo+'</span><span style="background:var(--input-readonly-bg);border-radius:5px;padding:1px 7px;font-size:.7rem;">'+xEsc(t.duration_label||t.duration+'d')+'</span></div><div class="tc-prices"><div class="tc-pi"><div class="tc-pi-label">👨 Dewasa</div><div class="tc-pi-val" style="color:var(--success-color);">RM '+xFmt(t.price_adult)+'</div></div><div class="tc-pi"><div class="tc-pi-label">👶 Kanak</div><div class="tc-pi-val" style="color:#2196f3;">RM '+xFmt(t.price_child)+'</div></div><div class="tc-pi"><div class="tc-pi-label">🍼 Bayi</div><div class="tc-pi-val" style="color:#ff9800;">RM '+xFmt(t.price_baby)+'</div></div><div class="tc-pi"><div class="tc-pi-label">💳 Deposit</div><div class="tc-pi-val" style="color:#9c27b0;">RM '+xFmt(t.deposit)+'</div></div></div><div class="tc-actions">'+btns+'</div></div></div>';
        });
        $('#tripsGrid').html(html);
    }).fail(function(){ $('#tripsGrid').html('<div style="grid-column:1/-1;text-align:center;padding:3rem;color:#e53e3e;">⚠️ Ralat memuatkan trip. Cuba semula.</div>'); });
}
 
$(document).on('click','.tc-manage-btn',function(){goManageTrip($(this).data('id'),$(this).data('name'));});
$(document).on('click','.tc-detail-btn',function(){loadTripDetailModal($(this).data('id'));});
$(document).on('click','.tc-edit-btn',function(){openEditTrip($(this).data('id'));});
$(document).on('click','.tc-del-btn',function(){deleteTripAdmin($(this).data('id'));});
 
function deleteTripAdmin(id){
    if(!confirm('Padam trip ini? Tindakan ini tidak boleh dibatalkan.'))return;
    $.ajax({url:'/trips/'+id,type:'POST',data:{_method:'DELETE'},headers:{'X-CSRF-TOKEN':xCsrf()},
        success:function(r){if(r.success){window.showNotification('Trip berjaya dipadam ✅','success');loadTrips();}else{window.showNotification(r.message||'Gagal memadam trip','error');}},
        error:function(x){window.showNotification((x.responseJSON&&x.responseJSON.message)||'Ralat pelayan','error');}
    });
}
 
function loadTripDetailModal(id){
    $.get('/trips/'+id,function(r){
        if(!r.success)return;
        var t=r.data;
        var imgs=[t.pic1_url,t.pic2_url,t.pic3_url].filter(Boolean).map(function(u){return'<img src="'+u+'" style="width:100%;border-radius:8px;aspect-ratio:16/9;object-fit:cover;">';}).join('');
        var itins='';
        if(t.itineraries&&t.itineraries.length){
            itins='<div style="margin-top:1.25rem;"><div style="font-weight:700;font-size:.9rem;margin-bottom:.75rem;">📅 Itinerari</div>';
            var _H=['Ahad','Isnin','Selasa','Rabu','Khamis','Jumaat','Sabtu'];
            t.itineraries.forEach(function(d){
                var hLbl='',tmLbl='';
                if(d.day_date){var _dd=new Date(d.day_date+'T00:00:00');hLbl=_H[_dd.getDay()]+', '+formatDate(d.day_date);}
                if(d.time_from||d.time_to)tmLbl=(d.time_from||'--:--')+' – '+(d.time_to||'--:--');
                itins+='<div style="display:flex;gap:.75rem;padding:.8rem 0;border-bottom:1px solid var(--border-color);"><div style="min-width:40px;"><div style="width:38px;height:38px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.72rem;font-weight:800;">H'+d.day_number+'</div></div><div style="flex:1;min-width:0;"><div style="font-weight:700;font-size:.88rem;color:var(--text-primary);">'+xEsc(d.title||'-')+'</div>'+(hLbl?'<div style="font-size:.74rem;color:#667eea;font-weight:600;margin-top:2px;">📅 '+hLbl+'</div>':'')+(tmLbl?'<div style="font-size:.74rem;color:var(--text-muted);margin-top:1px;">⏰ '+tmLbl+'</div>':'')+(d.description?'<div style="font-size:.79rem;color:var(--text-muted);margin-top:4px;line-height:1.5;">'+xEsc(d.description)+'</div>':'')+'</div></div>';
            });
            itins+='</div>';
        }
        var html='<h2 style="font-size:1.25rem;font-weight:800;margin-bottom:1rem;color:var(--text-primary);">'+xEsc(t.location||t.country)+'</h2>'+(imgs?'<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.65rem;margin-bottom:1.25rem;">'+imgs+'</div>':'')+'<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;font-size:.85rem;margin-bottom:1rem;background:var(--input-readonly-bg);padding:1rem;border-radius:10px;"><div style="color:var(--text-muted);">Negara</div><div style="font-weight:700;">'+xEsc(t.country||'-')+'</div><div style="color:var(--text-muted);">Tarikh Mula</div><div style="font-weight:700;">'+formatDate(t.date_from||'-')+'</div><div style="color:var(--text-muted);">Tarikh Tamat</div><div style="font-weight:700;">'+formatDate(t.date_to||'-')+'</div><div style="color:var(--text-muted);">Tempoh</div><div style="font-weight:700;">'+(t.duration_label||t.duration+' hari')+'</div></div><div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.45rem;margin-bottom:1rem;"><div style="background:var(--input-readonly-bg);padding:.65rem;border-radius:8px;text-align:center;"><div style="font-size:.68rem;color:var(--text-muted);">👨 Adult</div><strong style="color:var(--success-color);">RM '+xFmt(t.price_adult)+'</strong></div><div style="background:var(--input-readonly-bg);padding:.65rem;border-radius:8px;text-align:center;"><div style="font-size:.68rem;color:var(--text-muted);">👶 Child</div><strong style="color:#2196f3;">RM '+xFmt(t.price_child)+'</strong></div><div style="background:var(--input-readonly-bg);padding:.65rem;border-radius:8px;text-align:center;"><div style="font-size:.68rem;color:var(--text-muted);">🍼 Baby</div><strong style="color:#ff9800;">RM '+xFmt(t.price_baby)+'</strong></div><div style="background:var(--input-readonly-bg);padding:.65rem;border-radius:8px;text-align:center;"><div style="font-size:.68rem;color:var(--text-muted);">💳 Deposit</div><strong style="color:#9c27b0;">RM '+xFmt(t.deposit)+'</strong></div></div>'+itins;
        $('#tripDetailContent').html(html);
        $('#tripDetailModal').css('display','flex').addClass('active');
    });
}
 
function loadManageTripData(tripId){
    $('#manageTripEmpty').hide();
    $('#manageTripBody').html('<tr><td colspan="7" style="text-align:center;padding:2.5rem;color:var(--text-muted);">⏳ Memuatkan...</td></tr>');
    $('#manageTripStats').html('');
    $.get('/trips/'+tripId+'/bookings',function(r){
        if(!r.success){window.showNotification('Gagal memuatkan data','error');return;}
        var data=r.data,total=data.length;
        var confirmed=data.filter(function(b){return b.booking_status==='confirmed';}).length;
        var pending=data.filter(function(b){return b.booking_status==='pending';}).length;
        var totalAmt=data.reduce(function(s,b){return s+parseFloat(b.amount||0);},0);
        $('#manageTripSubtitle').text((r.trip_name||'')+' — '+total+' pelanggan aktif');
        $('#manageTripCountLabel').text(total+' pelanggan aktif');
        $('#manageTripStats').html([{num:total,label:'Jumlah Pelanggan',color:'#667eea'},{num:confirmed,label:'Disahkan',color:'#38a169'},{num:pending,label:'Menunggu',color:'#dd6b20'},{num:'RM '+xFmt(totalAmt),label:'Jumlah Nilai',color:'#9c27b0'}].map(function(s){return'<div class="mt-stat"><div class="mt-stat-num" style="color:'+s.color+';">'+s.num+'</div><div class="mt-stat-label">'+s.label+'</div></div>';}).join(''));
        if(!total){$('#manageTripBody').html('');$('#manageTripEmpty').show();loadTrips();return;}
        var rows='';
        data.forEach(function(b,i){
            var bsCl=b.booking_status==='confirmed'?'#38a169':'#dd6b20',bsBg=b.booking_status==='confirmed'?'#f0fff4':'#fffaf0',bsLb=b.booking_status==='confirmed'?'✅ Disahkan':'⏳ Menunggu';
            var psCl=b.payment_status==='paid'?'#38a169':(b.payment_status==='partial'?'#dd6b20':'#e53e3e'),psBg=b.payment_status==='paid'?'#f0fff4':(b.payment_status==='partial'?'#fffaf0':'#fff5f5'),psLb=b.payment_status==='paid'?'Bayar Penuh':(b.payment_status==='partial'?'Separa':'Belum Bayar');
            rows+='<tr class="mt-row"><td style="font-size:.84rem;color:var(--text-muted);">'+(i+1)+'</td><td><div style="font-weight:700;font-size:.9rem;color:var(--text-primary);">'+xEsc(b.customer_name||'-')+'</div><div style="font-size:.73rem;color:var(--text-muted);margin-top:2px;">'+xEsc(b.booking_no||'-')+'</div></td><td style="font-size:.83rem;font-family:monospace;color:var(--text-secondary);">'+xEsc(b.customer_ic||'-')+'</td><td style="font-size:.85rem;font-weight:700;color:var(--text-primary);">RM '+xFmt(b.amount)+'</td><td><span style="font-size:.74rem;font-weight:700;background:'+bsBg+';color:'+bsCl+';border:1px solid '+bsCl+'35;border-radius:8px;padding:3px 10px;white-space:nowrap;">'+bsLb+'</span></td><td><span style="font-size:.74rem;font-weight:700;background:'+psBg+';color:'+psCl+';border:1px solid '+psCl+'35;border-radius:8px;padding:3px 10px;white-space:nowrap;">'+psLb+'</span></td><td style="text-align:center;"><button class="btn btn-danger mt-remove-btn" style="font-size:.77rem;padding:5px 14px;" data-trip-id="'+tripId+'" data-booking-id="'+b.booking_id+'" data-name="'+xEsc(b.customer_name||'-')+'">🗑️ Keluarkan</button></td></tr>';
        });
        $('#manageTripBody').html(rows);
    }).fail(function(){window.showNotification('Ralat memuatkan data','error');});
}
 
$(document).on('click','.mt-remove-btn',function(){
    var tripId=$(this).data('trip-id'),bookingId=$(this).data('booking-id'),name=$(this).data('name');
    if(!confirm('Keluarkan "'+name+'" dari trip ini?'))return;
    var $btn=$(this).prop('disabled',true).text('⏳...');
    $.ajax({url:'/trips/'+tripId+'/bookings/'+bookingId,type:'POST',data:{_method:'DELETE'},headers:{'X-CSRF-TOKEN':xCsrf()},
        success:function(r){if(r.success){window.showNotification(name+' berjaya dikeluarkan ✅','success');loadManageTripData(tripId);}else{$btn.prop('disabled',false).text('🗑️ Keluarkan');window.showNotification(r.message||'Gagal','error');}},
        error:function(){$btn.prop('disabled',false).text('🗑️ Keluarkan');window.showNotification('Ralat pelayan','error');}
    });
});
 
// ══════════════════════════════════════════════════════════
// ITINERARY MANAGEMENT
// ══════════════════════════════════════════════════════════
 
var _HARI_MY=['Ahad','Isnin','Selasa','Rabu','Khamis','Jumaat','Sabtu'];
function _itinHariName(d){if(!d)return'—';var dt=new Date(d+'T00:00:00');return isNaN(dt.getTime())?'—':_HARI_MY[dt.getDay()];}
function _itinAutoDate(n){var b=$('#tripDateFrom').val();if(!b)return'';var d=new Date(b+'T00:00:00');d.setDate(d.getDate()+n-1);return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');}
function resetItinContainer(){_itinCount=0;$('#itineraryContainer').html('<div id="itinHint" style="text-align:center;padding:1.75rem;color:var(--text-muted);background:var(--input-readonly-bg);border-radius:10px;font-size:.88rem;">📅 Klik <strong>"➕ Tambah Hari"</strong> untuk mula membina itinerari</div>');}
 
$(document).on('change','#tripDateFrom',function(){$('#itineraryContainer .itin-card').each(function(){var $c=$(this),num=parseInt($c.find('.itin-day-input').val())||1,date=_itinAutoDate(num);$c.find('.itin-date-field').val(date);$c.find('.itin-day-chip').text(_itinHariName(date));});});
$(document).on('change','.itin-day-input',function(){var $c=$(this).closest('.itin-card'),num=parseInt($(this).val())||1,date=_itinAutoDate(num);$c.find('.itin-date-field').val(date);$c.find('.itin-day-chip').text(_itinHariName(date));});
$(document).on('change','.itin-date-field',function(){$(this).closest('.itin-card').find('.itin-day-chip').text(_itinHariName($(this).val()));});
 
$('#addItineraryDayBtn').off('click').on('click',function(){
    _itinCount++;var n=_itinCount,date=_itinAutoDate(n),hari=_itinHariName(date);
    $('#itinHint').remove();
    var card='<div id="itinCard_'+n+'" class="itin-card"><div class="itin-header"><div class="itin-day-badge"><span>Hari</span><input type="number" name="itinerary['+n+'][day_number]" value="'+n+'" min="1" max="99" class="itin-day-input"></div><span class="itin-day-chip">'+hari+'</span><button type="button" class="itin-remove" onclick="removeItinCard('+n+')">✖ Buang</button></div><div class="itin-date-grid"><div><label class="itin-lbl">📅 Tarikh</label><input type="date" name="itinerary['+n+'][day_date]" value="'+date+'" class="form-input itin-date-field" style="font-size:.84rem;"></div><div class="itin-time-grid" style="align-items:end;"><div><label class="itin-lbl">⏰ Masa Mula</label><input type="time" name="itinerary['+n+'][time_from]" class="form-input" style="font-size:.84rem;"></div><div><label class="itin-lbl">⏰ Masa Tamat</label><input type="time" name="itinerary['+n+'][time_to]" class="form-input" style="font-size:.84rem;"></div></div></div><div class="form-group" style="margin-bottom:.6rem;"><label class="itin-lbl">📌 Tajuk / Aktiviti Utama <span style="color:#e53e3e;">*</span></label><input type="text" name="itinerary['+n+'][title]" placeholder="e.g., Tiba & Lawatan Bandaraya" class="form-input"></div><div class="form-group"><label class="itin-lbl">📝 Penerangan Terperinci</label><textarea name="itinerary['+n+'][description]" rows="2" placeholder="e.g., Pagi: Tiba KLIA2. Petang: Lawatan Masjid Putra..." class="form-input" style="resize:vertical;"></textarea></div></div>';
    $('#itineraryContainer').append(card);
    setTimeout(function(){document.querySelector('#itinCard_'+n+' input[type="text"]').focus();},80);
});
 
function removeItinCard(n){$('#itinCard_'+n).slideUp(180,function(){$(this).remove();if(!$('#itineraryContainer .itin-card').length)resetItinContainer();});}
 
$('#sortItineraryBtn').on('click',function(){var cards=$('#itineraryContainer .itin-card').toArray();if(!cards.length)return;cards.sort(function(a,b){return(parseInt($(a).find('input[type="number"]').val())||0)-(parseInt($(b).find('input[type="number"]').val())||0);});var $c=$('#itineraryContainer');cards.forEach(function(c){$c.append(c);});window.showNotification('Itinerari disusun ✅','success');});
 
// ══════════════════════════════════════════════════════════
// EDIT TRIP
// ══════════════════════════════════════════════════════════
 
function _setupPhotoPreview(inputId,previewId,removeBtnId){
    $(document).on('change','#'+inputId,function(){var file=this.files[0];if(!file)return;var r=new FileReader();r.onload=function(ev){var $s=$('#'+previewId).closest('.et-photo-slot');$('#'+previewId).attr('src',ev.target.result).show();$s.find('.et-photo-placeholder').hide();$('#'+removeBtnId).show();};r.readAsDataURL(file);});
    $(document).on('click','#'+removeBtnId,function(){var $s=$('#'+previewId).closest('.et-photo-slot');$('#'+inputId).val('');$('#'+previewId).hide().attr('src','');$s.find('.et-photo-placeholder').show();$(this).hide();$s.find('.et-delete-flag').val('1');});
}
_setupPhotoPreview('editPic1Input','editPic1Preview','removePic1Btn');
_setupPhotoPreview('editPic2Input','editPic2Preview','removePic2Btn');
_setupPhotoPreview('editPic3Input','editPic3Preview','removePic3Btn');
 
function openEditTrip(id){
    ['1','2','3'].forEach(function(n){$('#editPic'+n+'Input').val('');$('#editPic'+n+'Preview').hide().attr('src','');$('#removePic'+n+'Btn').hide();$('#deletePic'+n+'Flag').val('0');});
    $('#editTripErrors').hide();
    $.get('/trips/'+id,function(r){
        if(!r.success){window.showNotification('Gagal memuatkan data trip','error');return;}
        var t=r.data;
        $('#editTripId').val(t.id);$('#editTripLocation').val(t.location||'');$('#editTripCountry').val(t.country||'');$('#editTripState').val(t.state||'');$('#editTripNationalCode').val((t.national_code||'').toUpperCase());$('#editTripDateFrom').val(t.date_from||'');$('#editTripDateTo').val(t.date_to||'');$('#editTripPriceAdult').val(t.price_adult||'');$('#editTripPriceChild').val(t.price_child||'');$('#editTripPriceBaby').val(t.price_baby||'');$('#editTripDeposit').val(t.deposit||'');
        ['1','2','3'].forEach(function(n){var url=t['pic'+n+'_url'];if(url){$('#editPic'+n+'Preview').attr('src',url).show();$('#editPic'+n+'Preview').closest('.et-photo-slot').find('.et-photo-placeholder').hide();$('#removePic'+n+'Btn').show();}else{$('#editPic'+n+'Preview').closest('.et-photo-slot').find('.et-photo-placeholder').show();}});
        $('#editTripModal').css('display','flex').addClass('active');setTimeout(function(){$('#editTripLocation').focus();},100);
    });
}
 
$(document).on('click','#closeEditTripModal, #btnCancelEditTrip',function(){$('#editTripModal').hide().removeClass('active');});
$(document).on('click','#editTripModal',function(e){if(e.target===this)$(this).hide().removeClass('active');});
$(document).on('click','#closeTripDetailModal',function(){$('#tripDetailModal').hide().removeClass('show active');});
$(document).on('click.tdm','#tripDetailModal',function(e){if(e.target===this)$(this).hide().removeClass('show active');});
 
$(document).on('submit','#editTripForm',function(e){
    e.preventDefault();
    var id=$('#editTripId').val();if(!id)return;
    var loc=$('#editTripLocation').val().trim(),cntry=$('#editTripCountry').val().trim(),code=$('#editTripNationalCode').val().trim(),dFrom=$('#editTripDateFrom').val(),dTo=$('#editTripDateTo').val(),pAdlt=$('#editTripPriceAdult').val();
    var errors=[];
    if(!loc)errors.push('Nama pakej wajib diisi');if(!cntry)errors.push('Negara wajib diisi');if(!code)errors.push('National code wajib diisi');if(!dFrom)errors.push('Tarikh mula wajib diisi');if(!dTo)errors.push('Tarikh tamat wajib diisi');if(dFrom&&dTo&&dFrom>dTo)errors.push('Tarikh tamat mesti selepas tarikh mula');if(!pAdlt||parseFloat(pAdlt)<0)errors.push('Harga dewasa tidak sah');
    if(errors.length){$('#editTripErrors').html(errors.map(function(e){return'<div>• '+xEsc(e)+'</div>';}).join('')).show();return;}
    $('#editTripErrors').hide();
    var $btn=$('#editTripSubmitBtn'),orig=$btn.html();$btn.prop('disabled',true).html('⏳ Menyimpan...');
    var fd=new FormData();
    fd.append('location',loc);fd.append('country',cntry);fd.append('state',$('#editTripState').val().trim());fd.append('national_code',code.toUpperCase());fd.append('date_from',dFrom);fd.append('date_to',dTo);fd.append('price_adult',pAdlt);fd.append('price_child',$('#editTripPriceChild').val());fd.append('price_baby',$('#editTripPriceBaby').val());fd.append('deposit',$('#editTripDeposit').val());
    ['1','2','3'].forEach(function(n){var file=$('#editPic'+n+'Input')[0].files[0];if(file)fd.append('pic'+n,file);fd.append('delete_pic'+n,$('#deletePic'+n+'Flag').val()||'0');});
    $.ajax({url:'/trips/'+id,type:'POST',data:fd,processData:false,contentType:false,headers:{'X-CSRF-TOKEN':xCsrf()},
        success:function(r){$btn.prop('disabled',false).html(orig);if(r.success){$('#editTripModal').hide().removeClass('active');window.showNotification('Trip berjaya dikemaskini ✅','success');loadTrips();}else{$('#editTripErrors').html('<div>⚠️ '+(r.message||'Ralat tidak diketahui')+'</div>').show();}},
        error:function(x){$btn.prop('disabled',false).html(orig);var msg='Ralat pelayan';if(x.responseJSON){if(x.responseJSON.errors)msg=Object.values(x.responseJSON.errors).map(function(v){return v.join(', ');}).join(' | ');else if(x.responseJSON.message)msg=x.responseJSON.message;}$('#editTripErrors').html('<div>⚠️ '+xEsc(msg)+'</div>').show();}
    });
});
 
// ══════════════════════════════════════════════════════════
// SIDEBAR & FILTER
// ══════════════════════════════════════════════════════════
 
$('#viewTripsBtn').off('click').on('click',function(e){e.preventDefault();document.querySelectorAll('.view-section').forEach(function(el){el.style.display='none';});document.getElementById('viewTripsView').style.display='block';document.querySelectorAll('.sidebar-link').forEach(function(el){el.classList.remove('active');});this.classList.add('active');if($('#filterTripCompany option').length<=1&&typeof loadCompanies==='function'){loadCompanies(function(){loadTrips();});}else{loadTrips();}});
 
$('#addTripBtn').off('click').on('click',function(e){e.preventDefault();document.querySelectorAll('.view-section').forEach(function(el){el.style.display='none';});document.getElementById('addTripView').style.display='block';document.querySelectorAll('.sidebar-link').forEach(function(el){el.classList.remove('active');});this.classList.add('active');resetItinContainer();});
 
if(typeof loadCompanies==='function'&&$('#filterTripCompany option').length<=1)loadCompanies();
$('#btnFilterTrips').off('click').on('click',function(){loadTrips();});
$('#btnResetTrips').off('click').on('click',function(){$('#filterTripCompany, #filterTripCountry, #filterTripDateFrom').val('');loadTrips();});
 
// ══════════════════════════════════════════════════════════
// FIX NULL AVATAR
// ══════════════════════════════════════════════════════════
 
var _patchAvatars=function(){$('#onlineUsersList img').each(function(){var src=$(this).attr('src')||'';if(!src||src==='null'||src==='undefined'||/\/null$/.test(src)){var ini=($(this).attr('alt')||'?').charAt(0).toUpperCase();$(this).replaceWith('<div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:.85rem;font-weight:700;flex-shrink:0;">'+ini+'</div>');}});};
var obs=new MutationObserver(_patchAvatars);var target=document.getElementById('onlineUsersList');if(target)obs.observe(target,{childList:true,subtree:true});
$(document).ajaxComplete(function(){setTimeout(_patchAvatars,100);});
 
// ══════════════════════════════════════════════════════════
// INITIALIZE DASHBOARD
// ══════════════════════════════════════════════════════════
 
// Init dashboard when DOM ready
dashLoadStats();
dashLoadCompanyFilter(); 
dashRunSearch();
setInterval(dashLoadStats, 120000); // Refresh stats every 2 minutes
 
});

</script>
@endpush