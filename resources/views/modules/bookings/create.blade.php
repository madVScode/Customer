{{-- ============================================================ --}}
{{-- resources/views/modules/bookings/create.blade.php           --}}
{{-- View: Buat Booking Baru + Pax Counter + Traveler Details    --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Create Booking')
@section('content')

<style>
/* ── Pax Counter ── */
.pax-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .85rem; margin-bottom: 1rem; }
.pax-card {
    background: var(--input-readonly-bg);
    border: 1.5px solid var(--border-color);
    border-radius: 12px; padding: 1rem .85rem; text-align: center;
    transition: border-color .2s;
}
.pax-card:focus-within { border-color: var(--primary-color); }
.pax-card-label { font-size: .78rem; color: var(--text-muted); font-weight: 700; margin-bottom: .2rem; }
.pax-card-price { font-size: .75rem; color: var(--success-color); font-weight: 700; margin-bottom: .65rem; }
.pax-controls { display: flex; align-items: center; justify-content: center; gap: .6rem; }
.pax-btn {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--primary-dark, #1e3a5f), var(--primary-color, #2563eb));
    color: white; border: none; border-radius: 50%; font-size: 1rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: opacity .2s;
}
.pax-btn:hover { opacity: .85; }
.pax-btn:active { transform: scale(.9); }
.pax-num { font-size: 1.2rem; font-weight: 800; min-width: 26px; text-align: center;
           color: var(--text-primary); }

/* ── Price ref box ── */
.price-ref-box {
    background: var(--input-readonly-bg); border: 1px solid var(--border-color);
    border-radius: 10px; padding: .85rem 1rem; margin-bottom: 1rem;
    display: none;
}
.price-ref-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: .5rem; text-align: center; }
.price-ref-label { font-size: .7rem; color: var(--text-muted); margin-bottom: .2rem; }
.price-ref-val { font-weight: 800; font-size: .9rem; }

/* ── Traveler blocks ── */
.traveler-section-wrap {
    background: var(--input-readonly-bg); border: 1.5px solid var(--border-color);
    border-radius: 12px; padding: 1.1rem; margin-top: .5rem;
}
.traveler-section-title {
    font-size: .9rem; font-weight: 800; color: var(--text-primary);
    margin-bottom: 1rem; display: flex; align-items: center;
    justify-content: space-between; flex-wrap: wrap; gap: .5rem;
}
.traveler-pax-summary {
    display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: 1rem;
}
.pax-pill {
    display: inline-flex; align-items: center; gap: .25rem;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 20px; padding: 3px 10px;
    font-size: .75rem; font-weight: 700;
    color: var(--text-primary);
}
.traveler-block {
    background: var(--card-bg); border: 1.5px solid var(--border-color);
    border-radius: 10px; padding: 1rem; margin-bottom: .75rem;
}
.traveler-block-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: .75rem; padding-bottom: .6rem;
    border-bottom: 1px solid var(--border-color);
}
.traveler-block-title {
    font-size: .85rem; font-weight: 800; color: var(--text-primary);
    display: flex; align-items: center; gap: .4rem;
}
.traveler-type-chip {
    font-size: .68rem; font-weight: 700; padding: 2px 8px;
    border-radius: 20px; background: #dbeafe; color: #1d4ed8;
}
.auto-chip-sm {
    padding: .55rem .8rem; background: #f0fff4; border: 1.5px solid #9ae6b4;
    border-radius: 8px; font-size: .82rem; color: #276749; font-weight: 600;
    min-height: 40px; display: flex; align-items: center; gap: .35rem;
}
.auto-chip-sm.empty {
    background: var(--input-readonly-bg); border-color: var(--border-color);
    color: var(--text-muted); font-weight: 400; font-size: .78rem;
}
.auto-chip-sm-label {
    font-size: .7rem; font-weight: 700; color: var(--text-muted);
    margin-bottom: .25rem; display: flex; align-items: center; gap: .25rem;
}
.auto-sm-badge {
    font-size: .62rem; background: #c6f6d5; color: #276749;
    padding: 1px 5px; border-radius: 8px; font-weight: 700;
}
.no-pax-notice {
    text-align: center; padding: 1.5rem; color: var(--text-muted);
    font-size: .85rem;
}

@media(max-width:640px) {
    .pax-grid { grid-template-columns: 1fr; }
    .price-ref-grid { grid-template-columns: repeat(2,1fr); }
}
</style>

<div id="createBookingView">
    <h1 class="page-title">📝 Create Booking</h1>
    <div class="content-card">
        <form id="createBookingForm">
            <div class="form-grid">

                {{-- Company --}}
                <div class="form-group">
                    <label class="form-label">🏢 Company <span style="color:var(--error-color)">*</span></label>
                    <select id="bookingCompanySelect" class="form-input" required></select>
                    <small style="color:var(--text-muted);font-size:.78rem;">
                        Pilih company dahulu. Trip & Customer akan difilter ikut company.
                    </small>
                </div>

                {{-- Trip --}}
                <div class="form-group">
                    <label class="form-label">✈️ Trip / Package <span style="color:var(--error-color)">*</span></label>
                    <select id="bookingTripSelect" class="form-input" required></select>
                </div>

                {{-- Customer --}}
                <div class="form-group">
                    <label class="form-label">👤 Customer <span style="color:var(--error-color)">*</span></label>
                    <select id="bookingCustomerSelect" class="form-input" required></select>
                </div>

                {{-- Booking No --}}
                <div class="form-group">
                    <label class="form-label">Booking No.</label>
                    <input id="bookingNo" type="text" class="form-input" readonly
                           style="background:var(--input-readonly-bg);">
                    <small style="color:var(--text-muted);font-size:.75rem;">Auto-generated</small>
                </div>

                {{-- Payment Status --}}
                <div class="form-group">
                    <label class="form-label">Payment Status <span style="color:var(--error-color)">*</span></label>
                    <select id="bookingPaymentStatus" class="form-input" required>
                        <option value="">-- Select --</option>
                        <option value="unpaid">❌ Unpaid</option>
                        <option value="partial">⚡ Partial</option>
                        <option value="paid">✅ Paid</option>
                    </select>
                </div>

                {{-- Deposit Status --}}
                <div class="form-group">
                    <label class="form-label">Deposit Status <span style="color:var(--error-color)">*</span></label>
                    <select id="bookingDepositStatus" class="form-input" required>
                        <option value="">-- Select --</option>
                        <option value="not_paid">❌ Not Paid</option>
                        <option value="paid">✅ Paid</option>
                    </select>
                </div>

                {{-- Booking Status --}}
                <div class="form-group">
                    <label class="form-label">Booking Status <span style="color:var(--error-color)">*</span></label>
                    <select id="bookingStatus" class="form-input" required>
                        <option value="">-- Select --</option>
                        <option value="pending">🕐 Pending</option>
                        <option value="confirmed">✅ Confirmed</option>
                        <option value="cancelled">❌ Cancelled</option>
                        <option value="completed">🏁 Completed</option>
                    </select>
                </div>

                {{-- Notes --}}
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Notes</label>
                    <textarea id="bookingNotes" rows="2"
                              placeholder="e.g., 2 adults, 1 child. Room preference: non-smoking."
                              class="form-input"></textarea>
                </div>
            </div>

            {{-- ── Harga Rujukan ── --}}
            <div id="tripPriceInfo" class="price-ref-box">
                <div style="font-size:.78rem;font-weight:800;color:var(--text-muted);margin-bottom:.65rem;">
                    💰 HARGA RUJUKAN TRIP
                </div>
                <div class="price-ref-grid">
                    <div>
                        <div class="price-ref-label">👨 Adult</div>
                        <div class="price-ref-val" id="refPriceAdult" style="color:var(--success-color);">-</div>
                    </div>
                    <div>
                        <div class="price-ref-label">👶 Child</div>
                        <div class="price-ref-val" id="refPriceChild" style="color:#2196f3;">-</div>
                    </div>
                    <div>
                        <div class="price-ref-label">🍼 Baby</div>
                        <div class="price-ref-val" id="refPriceBaby" style="color:#ff9800;">-</div>
                    </div>
                    <div>
                        <div class="price-ref-label">💳 Deposit</div>
                        <div class="price-ref-val" id="refDeposit" style="color:#9c27b0;">-</div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 PAX COUNTER
            ══════════════════════════════════════════════ --}}
            <div style="margin-bottom:1.25rem;">
                <div style="font-size:.82rem;font-weight:800;color:var(--text-muted);
                            text-transform:uppercase;letter-spacing:.07em;
                            border-bottom:1px solid var(--border-color);
                            padding-bottom:.5rem;margin-bottom:.9rem;">
                    👥 Bilangan Pelancong
                </div>
                <div class="pax-grid">
                    <div class="pax-card">
                        <div class="pax-card-label">👨 Dewasa</div>
                        <div class="pax-card-price" id="paxPriceAdult">-</div>
                        <div class="pax-controls">
                            <button type="button" class="pax-btn" onclick="cbkChangePax('adult',-1)">−</button>
                            <span class="pax-num" id="cbkPaxAdult">1</span>
                            <button type="button" class="pax-btn" onclick="cbkChangePax('adult',1)">+</button>
                        </div>
                        <input type="hidden" id="cbkPaxAdultVal" value="1">
                    </div>
                    <div class="pax-card">
                        <div class="pax-card-label">👶 Kanak-kanak</div>
                        <div class="pax-card-price" id="paxPriceChild">-</div>
                        <div class="pax-controls">
                            <button type="button" class="pax-btn" onclick="cbkChangePax('child',-1)">−</button>
                            <span class="pax-num" id="cbkPaxChild">0</span>
                            <button type="button" class="pax-btn" onclick="cbkChangePax('child',1)">+</button>
                        </div>
                        <input type="hidden" id="cbkPaxChildVal" value="0">
                    </div>
                    <div class="pax-card">
                        <div class="pax-card-label">🍼 Bayi</div>
                        <div class="pax-card-price" id="paxPriceBaby">-</div>
                        <div class="pax-controls">
                            <button type="button" class="pax-btn" onclick="cbkChangePax('baby',-1)">−</button>
                            <span class="pax-num" id="cbkPaxBaby">0</span>
                            <button type="button" class="pax-btn" onclick="cbkChangePax('baby',1)">+</button>
                        </div>
                        <input type="hidden" id="cbkPaxBabyVal" value="0">
                    </div>
                </div>

                {{-- Auto-calculate total --}}
                <div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:white;
                            border-radius:10px;padding:.85rem 1.1rem;
                            display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
                    <div>
                        <div style="font-size:.68rem;opacity:.6;margin-bottom:.1rem;">JUMLAH AUTO-KIRA</div>
                        <div style="font-size:1.5rem;font-weight:900;color:#00d4aa;" id="cbkTotalDisplay">RM 0.00</div>
                    </div>
                    <div style="text-align:right;">
                        <button type="button" onclick="cbkApplyTotal()"
                                style="padding:.45rem 1rem;background:#00d4aa;color:#0f172a;
                                       border:none;border-radius:8px;font-weight:800;
                                       font-size:.82rem;cursor:pointer;font-family:inherit;">
                            ✓ Guna Jumlah Ini
                        </button>
                        <div style="font-size:.68rem;opacity:.5;margin-top:.25rem;">
                            atau edit manual di Total Amount
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Amount (manual override) --}}
            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label">Total Amount (RM) <span style="color:var(--error-color)">*</span></label>
                <input id="bookingAmount" type="number" step="0.01" min="0"
                       placeholder="0.00" class="form-input" required>
            </div>

            {{-- ══════════════════════════════════════════════
                 TRAVELER DETAILS
            ══════════════════════════════════════════════ --}}
            <div class="traveler-section-wrap">
                <div class="traveler-section-title">
                    <span>🛂 Maklumat Pelancong <span style="font-size:.75rem;font-weight:400;color:var(--text-muted);">(Pilihan — boleh isi kemudian)</span></span>
                    <label style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;font-weight:600;cursor:pointer;">
                        <input type="checkbox" id="cbkEnableTravelers" onchange="cbkToggleTravelers()"
                               style="width:16px;height:16px;accent-color:var(--primary-color);">
                        Isi sekarang
                    </label>
                </div>

                <div id="cbkTravelersWrap" style="display:none;">
                    <div class="traveler-pax-summary" id="cbkPaxSummary"></div>
                    <div id="cbkTravelersList"></div>
                </div>

                <div id="cbkTravelersNotice" style="text-align:center;padding:1rem;
                     color:var(--text-muted);font-size:.83rem;">
                    ℹ️ Tandakan "Isi sekarang" untuk masukkan maklumat setiap pelancong.
                    Maklumat ini boleh ditambah kemudian melalui butang <strong>Edit Travelers</strong> dalam senarai booking.
                </div>
            </div>

            <div style="text-align:center;margin-top:2rem;display:flex;justify-content:center;gap:.75rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-success btn-lg">💾 Create Booking</button>
                <button type="button" id="btnResetBookingForm" class="btn btn-warning btn-lg">🔄 Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {

    // ── Booking No ────────────────────────────────────────────
    if ($('#bookingNo').length && !$('#bookingNo').val()) {
        $('#bookingNo').val(window.generateBookingNo && window.generateBookingNo());
    }

    // ── Load dropdowns ────────────────────────────────────────
    var _companiesDone = false, _tripsDone = false, _customersDone = false;

    function tryPopulate() {
        if (!_companiesDone || !_tripsDone || !_customersDone) return;
        var attempts = 0;
        var wait = setInterval(function () {
            attempts++;
            if (window.tsInstances && window.tsInstances['bookingCompanySelect']) {
                clearInterval(wait);
                window.populateBookingCompanyDropdown && window.populateBookingCompanyDropdown();
                var prefillCompany = sessionStorage.getItem('prefill_booking_company');
                var prefillTrip    = sessionStorage.getItem('prefill_booking_trip');
                if (prefillCompany) {
                    sessionStorage.removeItem('prefill_booking_company');
                    sessionStorage.removeItem('prefill_booking_trip');
                    setTimeout(function () {
                        window.tsSetValue && window.tsSetValue('bookingCompanySelect', String(prefillCompany), false);
                        $('#bookingCompanySelect').trigger('change');
                        if (prefillTrip) {
                            setTimeout(function () {
                                window.tsSetValue && window.tsSetValue('bookingTripSelect', String(prefillTrip), false);
                                $('#bookingTripSelect').trigger('change');
                            }, 400);
                        }
                    }, 200);
                }
            }
            if (attempts > 30) clearInterval(wait);
        }, 100);
    }

    window.loadCompanies && window.loadCompanies(function () { _companiesDone = true; tryPopulate(); });
    window.loadTrips && window.loadTrips();
    setTimeout(function () { _tripsDone = true; tryPopulate(); }, 500);
    window.loadCustomersFromServer && window.loadCustomersFromServer({}, true);
    setTimeout(function () { _customersDone = true; tryPopulate(); }, 600);

    // ── Trip selected → update price ref & pax prices ────────
    // ── Trip selected → baca harga dari DOM selepas bookings.js proses ────────
$(document).on('change', '#bookingTripSelect', function () {
    var tripId = $(this).val();

    if (!tripId) {
        cbkCurrentPrices = null;
        cbkRecalc();
        return;
    }

    // Bagi bookings.js masa set DOM dahulu (~100ms), kemudian baca nilai dari sana
    setTimeout(function () {
        var adult   = parseFloat(($('#refPriceAdult').text() || '').replace(/[^\d.]/g, '')) || 0;
        var child   = parseFloat(($('#refPriceChild').text() || '').replace(/[^\d.]/g, '')) || 0;
        var baby    = parseFloat(($('#refPriceBaby').text()  || '').replace(/[^\d.]/g, '')) || 0;
        var deposit = parseFloat(($('#refDeposit').text()    || '').replace(/[^\d.]/g, '')) || 0;

        if (adult > 0 || child > 0 || baby > 0) {
            cbkCurrentPrices = { adult: adult, child: child, baby: baby, deposit: deposit };

            // Kemaskini label harga dalam pax counter
            $('#paxPriceAdult').text('RM ' + cbkFmt(adult));
            $('#paxPriceChild').text('RM ' + cbkFmt(child));
            $('#paxPriceBaby').text('RM '  + cbkFmt(baby));
        } else {
            cbkCurrentPrices = null;
        }

        cbkRecalc();
    }, 150);
});
// ── Customer selected → simpan data untuk auto-fill traveler ─
    $(document).on('change', '#bookingCustomerSelect', function () {
        var customerId = $(this).val();
        window.cbkSelectedCustomer = null;
        if (!customerId) return;

        $.get('/customers/' + customerId, function (res) {
            if (res.success) {
                window.cbkSelectedCustomer = res.data;
                if (document.getElementById('cbkEnableTravelers').checked) {
                    cbkBuildTravelerBlocks();
                }
            }
        });
    });

    // ── Reset customer data bila company atau trip bertukar ───────
    $(document).on('change', '#bookingCompanySelect', function () {
        window.cbkSelectedCustomer = null;
    });

    // ── Reset ─────────────────────────────────────────────────
    $('#btnResetBookingForm').on('click', function () {
        $('#createBookingForm')[0].reset();
        $('#bookingNo').val(window.generateBookingNo && window.generateBookingNo());
        $('#tripPriceInfo').hide();
        cbkCurrentPrices = null;
        cbkPax = { adult: 1, child: 0, baby: 0 };
        ['adult','child','baby'].forEach(function (t) {
            document.getElementById('cbkPax'+cap(t)).textContent = cbkPax[t];
            document.getElementById('cbkPax'+cap(t)+'Val').value  = cbkPax[t];
        });
        cbkRecalc();
        // Reset travelers
        document.getElementById('cbkEnableTravelers').checked = false;
        cbkToggleTravelers();
        window.cbkSelectedCustomer = null;
        window.populateBookingCompanyDropdown && window.populateBookingCompanyDropdown();
    });
});

// ── Pax state ─────────────────────────────────────────────────────────────
var cbkPax          = { adult: 1, child: 0, baby: 0 };
var cbkCurrentPrices = null;
var cbkTravelerCount = 0;
var cbkTravelerData  = {};

function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function cbkFmt(n) { return parseFloat(n || 0).toFixed(2); }

// ── Pax counter ───────────────────────────────────────────────────────────
window.cbkChangePax = function (type, delta) {
    cbkPax[type] = Math.max(type === 'adult' ? 1 : 0, cbkPax[type] + delta);
    document.getElementById('cbkPax' + cap(type)).textContent         = cbkPax[type];
    document.getElementById('cbkPax' + cap(type) + 'Val').value       = cbkPax[type];
    cbkRecalc();
    if (document.getElementById('cbkEnableTravelers').checked) {
        cbkBuildTravelerBlocks();
    }
};

function cbkRecalc() {
    if (!cbkCurrentPrices) {
        document.getElementById('cbkTotalDisplay').textContent = 'RM 0.00';
        return;
    }
    var tot = cbkPax.adult * cbkCurrentPrices.adult +
              cbkPax.child * cbkCurrentPrices.child +
              cbkPax.baby  * cbkCurrentPrices.baby;
    document.getElementById('cbkTotalDisplay').textContent = 'RM ' + cbkFmt(tot);
}

window.cbkApplyTotal = function () {
    if (!cbkCurrentPrices) {
        window.showNotification && window.showNotification('Sila pilih trip dahulu.', 'warning');
        return;
    }
    var tot = cbkPax.adult * cbkCurrentPrices.adult +
              cbkPax.child * cbkCurrentPrices.child +
              cbkPax.baby  * cbkCurrentPrices.baby;
    document.getElementById('bookingAmount').value = cbkFmt(tot);
    // Inject pax values into form
    document.getElementById('cbkPaxAdultVal').value = cbkPax.adult;
    document.getElementById('cbkPaxChildVal').value = cbkPax.child;
    document.getElementById('cbkPaxBabyVal').value  = cbkPax.baby;
    window.showNotification && window.showNotification('Jumlah RM ' + cbkFmt(tot) + ' telah digunakan.', 'success');
};

// ── Travelers toggle ──────────────────────────────────────────────────────
window.cbkToggleTravelers = function () {
    var enabled = document.getElementById('cbkEnableTravelers').checked;
    document.getElementById('cbkTravelersWrap').style.display  = enabled ? 'block' : 'none';
    document.getElementById('cbkTravelersNotice').style.display = enabled ? 'none' : 'block';
    if (enabled) cbkBuildTravelerBlocks();
};

// ── Build traveler blocks ikut pax ────────────────────────────────────────
function cbkBuildTravelerBlocks() {
    var list = document.getElementById('cbkTravelersList');
    list.innerHTML = '';
    cbkTravelerData  = {};
    cbkTravelerCount = 0;

    // Summary pills
    var summaryHtml = '';
    if (cbkPax.adult > 0) summaryHtml += '<span class="pax-pill">👨 ' + cbkPax.adult + ' Dewasa</span>';
    if (cbkPax.child > 0) summaryHtml += '<span class="pax-pill">👶 ' + cbkPax.child + ' Kanak</span>';
    if (cbkPax.baby  > 0) summaryHtml += '<span class="pax-pill">🍼 ' + cbkPax.baby  + ' Bayi</span>';
    document.getElementById('cbkPaxSummary').innerHTML = summaryHtml;

    // Build blocks: adult first, then child, then baby
    var entries = [];
    for (var a = 0; a < cbkPax.adult; a++) entries.push({ type:'adult', label:'👨 Dewasa', num: a+1 });
    for (var c = 0; c < cbkPax.child; c++) entries.push({ type:'child', label:'👶 Kanak-kanak', num: c+1 });
    for (var b = 0; b < cbkPax.baby;  b++) entries.push({ type:'baby',  label:'🍼 Bayi', num: b+1 });

    if (!entries.length) {
        list.innerHTML = '<div class="no-pax-notice">Tiada pelancong ditetapkan.</div>';
        return;
    }

    entries.forEach(function (entry, idx) {
        var i = cbkTravelerCount++;
        cbkTravelerData[i] = { name: '', ic: '', type: entry.type };

        var typeColors = {
            adult: '#dbeafe:#1d4ed8',
            child: '#d1fae5:#065f46',
            baby:  '#fef3c7:#92400e'
        };
        var tc = (typeColors[entry.type] || '#dbeafe:#1d4ed8').split(':');

        var html =
            '<div class="traveler-block" id="cbkTb-' + i + '">' +
            '<div class="traveler-block-header">' +
                '<div class="traveler-block-title">' +
                    entry.label + ' #' + entry.num +
                    '<span class="traveler-type-chip" style="background:' + tc[0] + ';color:' + tc[1] + ';">' +
                    entry.type + '</span>' +
                '</div>' +
            '</div>' +
            '<input type="hidden" name="travelers[' + i + '][type]" value="' + entry.type + '">' +
            '<input type="hidden" name="travelers[' + i + '][order]" value="' + (idx+1) + '">' +
            '<div class="form-grid">' +

                // Name
                '<div class="form-group">' +
                    '<label class="form-label">Nama Penuh <span style="color:#e53e3e;">*</span></label>' +
                    '<input type="text" name="travelers[' + i + '][name]" class="form-input" ' +
                        'placeholder="Nama penuh" required ' +
                        'oninput="cbkTbUpdateName(' + i + ',this.value)">' +
                '</div>' +

                // IC
                '<div class="form-group">' +
                    '<label class="form-label">No. IC / Pasport <span style="color:#e53e3e;">*</span></label>' +
                    '<input type="text" name="travelers[' + i + '][ic]" class="form-input" ' +
                        'placeholder="000000-00-0000" maxlength="14" required ' +
                        'oninput="cbkTbICInput(' + i + ',this)">' +
                '</div>' +

                // DOB auto
                '<div class="form-group">' +
                    '<div class="auto-chip-sm-label">Tarikh Lahir <span class="auto-sm-badge">⚡ Auto</span></div>' +
                    '<div class="auto-chip-sm empty" id="cbkTbDob-' + i + '">Auto dari IC</div>' +
                    '<input type="hidden" name="travelers[' + i + '][dob]" id="cbkTbDobVal-' + i + '">' +
                '</div>' +

                // Gender auto
                '<div class="form-group">' +
                    '<div class="auto-chip-sm-label">Jantina <span class="auto-sm-badge">⚡ Auto</span></div>' +
                    '<div class="auto-chip-sm empty" id="cbkTbGender-' + i + '">Auto dari IC</div>' +
                    '<input type="hidden" name="travelers[' + i + '][gender]" id="cbkTbGenderVal-' + i + '">' +
                '</div>' +

                // Passport No
                '<div class="form-group">' +
                    '<label class="form-label">No. Pasport (Pilihan)</label>' +
                    '<input type="text" name="travelers[' + i + '][passport_no]" class="form-input" ' +
                        'placeholder="A12345678">' +
                '</div>' +

                // Nationality
                '<div class="form-group">' +
                    '<label class="form-label">Warganegara</label>' +
                    '<input type="text" name="travelers[' + i + '][nationality]" class="form-input" ' +
                        'placeholder="Malaysian" value="Malaysian">' +
                '</div>' +

                // Passport Expiry
                '<div class="form-group">' +
                    '<label class="form-label">Luput Pasport</label>' +
                    '<input type="date" name="travelers[' + i + '][pp_expire]" class="form-input">' +
                '</div>' +

            '</div>' + // end form-grid
            '</div>';  // end traveler-block

        list.insertAdjacentHTML('beforeend', html);

        // ── Auto-fill block pertama dengan data customer ──────
        if (idx === 0 && window.cbkSelectedCustomer) {
            var c = window.cbkSelectedCustomer;

            var nameInput = list.querySelector('[name="travelers[' + i + '][name]"]');
            if (nameInput && c.name) nameInput.value = c.name;

            var icInput = list.querySelector('[name="travelers[' + i + '][ic]"]');
            if (icInput && c.ic) {
                icInput.value = c.ic;
                var raw = (c.ic || '').replace(/\D/g, '');
                cbkUpdateICChips(raw,
                    'cbkTbDob-'    + i, 'cbkTbGender-'    + i,
                    'cbkTbDobVal-' + i, 'cbkTbGenderVal-' + i
                );
            }

            var ppInput = list.querySelector('[name="travelers[' + i + '][passport_no]"]');
            if (ppInput && c.passport_no) ppInput.value = c.passport_no;

            var natInput = list.querySelector('[name="travelers[' + i + '][nationality]"]');
            if (natInput && c.pp_warganegara) natInput.value = c.pp_warganegara;

            var ppExpInput = list.querySelector('[name="travelers[' + i + '][pp_expire]"]');
            if (ppExpInput && c.pp_date_expired) ppExpInput.value = c.pp_date_expired;
        }
    });
}

// ── IC auto-detect (traveler blocks) ────────────────────────────────────
window.cbkTbICInput = function (i, input) {
    var raw = cbkFormatIC(input);
    cbkUpdateICChips(raw, 'cbkTbDob-' + i, 'cbkTbGender-' + i,
                          'cbkTbDobVal-' + i, 'cbkTbGenderVal-' + i);
};

window.cbkTbUpdateName = function (i, val) {
    if (cbkTravelerData[i]) cbkTravelerData[i].name = val;
};

function cbkFormatIC(input) {
    var raw = input.value.replace(/\D/g, '').slice(0, 12);
    var fmt = raw.slice(0, 6);
    if (raw.length > 6) fmt += '-' + raw.slice(6, 8);
    if (raw.length > 8) fmt += '-' + raw.slice(8);
    input.value = fmt;
    return raw;
}

function cbkUpdateICChips(raw, dobChipId, genderChipId, dobHiddenId, genderHiddenId) {
    var dobChip    = document.getElementById(dobChipId);
    var genderChip = document.getElementById(genderChipId);
    var months     = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];

    if (!dobChip || !genderChip) return;

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
        dobChip.textContent = 'Auto dari IC';
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
        genderChip.textContent = 'Auto dari IC';
        genderChip.classList.add('empty');
        if (genderHiddenId) document.getElementById(genderHiddenId).value = '';
    }
}
</script>
@endpush