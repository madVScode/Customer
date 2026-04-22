{{-- ============================================================ --}}
{{-- resources/views/modules/receipts/create.blade.php           --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Create Receipt')
@section('content')
<div id="createReceiptView">
    <h1 class="page-title">🧾 Create Receipt</h1>

    {{-- Company Preview --}}
    <div id="receiptCompanyPreview" style="display:none;margin-bottom:1.5rem;">
        <div class="content-card" style="padding:1.25rem;border-left:4px solid var(--primary-color);">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div id="receiptCompanyLogo" style="width:60px;height:60px;border-radius:10px;overflow:hidden;background:var(--input-readonly-bg);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">🏢</div>
                <div>
                    <div id="receiptCompanyName" style="font-size:1.1rem;font-weight:700;color:var(--text-primary);"></div>
                    <div id="receiptCompanyReg"  style="font-size:.8rem;color:var(--text-muted);"></div>
                    <div id="receiptCompanyAddr" style="font-size:.8rem;color:var(--text-secondary);margin-top:.2rem;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <form id="createReceiptForm">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Company *</label>
                    {{-- ✅ Native select — diisi via populateReceiptCompanyDropdown() --}}
                    <select id="receiptCompanySelect" class="form-select" required>
                        <option value="">-- Select Company --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer *</label>
                    {{-- ✅ TomSelect — list customer panjang, perlu search --}}
                    <select id="receiptCustomerSelect" class="form-select" required disabled>
                        <option value="">-- Select Company First --</option>
                    </select>
                    <small style="color:var(--text-muted);font-size:.75rem;">Customer difilter ikut company</small>
                    <input type="hidden" id="receiptBookingId" name="booking_id" value=""> {{-- ✅ Hidden field untuk simpan booking_id --}}
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Trip / Package *</label>
                {{-- ✅ Native select — diisi via nativePopulate() --}}
                <select id="receiptTripSelect" class="form-select" required disabled>
                    <option value="">-- Select Company First --</option>
                </select>
                <small style="color:var(--text-muted);font-size:.75rem;">Trip difilter ikut company</small>
            </div>

            {{-- Trip Info Box --}}
            <div id="receiptTripInfoBox" style="display:none;margin:.75rem 0 1.25rem;padding:1rem;background:var(--input-readonly-bg);border-radius:10px;border-left:4px solid var(--success-color);">
                <div style="font-weight:600;margin-bottom:.75rem;color:var(--text-primary);">✈️ Trip Details</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.75rem;font-size:.85rem;color:var(--text-secondary);">
                    <div>📍 Destination: <strong id="rTripCountry">-</strong></div>
                    <div>📅 Dates: <strong id="rTripDates">-</strong></div>
                    <div>⏱️ Duration: <strong id="rTripDuration">-</strong></div>
                    <div>🌍 Code: <strong id="rTripCode">-</strong></div>
                </div>
                <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px dashed var(--border-color);display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;font-size:.82rem;color:var(--text-secondary);">
                    <div>👨 Adult: <strong id="rPriceAdult" style="color:var(--success-color);">-</strong></div>
                    <div>👶 Child: <strong id="rPriceChild" style="color:#2196f3;">-</strong></div>
                    <div>🍼 Baby: <strong id="rPriceBaby"  style="color:#ff9800;">-</strong></div>
                    <div>💳 Deposit: <strong id="rPriceDeposit" style="color:#9c27b0;">-</strong></div>
                </div>
            </div>

            {{-- Pax --}}
            <div style="margin-bottom:1.25rem;padding:1rem;border:2px solid var(--border-color);border-radius:12px;">
                <div style="font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:.75rem;">👥 Number of Pax</div>
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">👨 Adult</label><input id="receiptPaxAdult" type="number" min="0" value="1" class="form-input"></div>
                    <div class="form-group"><label class="form-label">👶 Child</label><input id="receiptPaxChild" type="number" min="0" value="0" class="form-input"></div>
                    <div class="form-group"><label class="form-label">🍼 Baby</label><input id="receiptPaxBaby" type="number" min="0" value="0" class="form-input"></div>
                    <div class="form-group" style="display:flex;align-items:flex-end;">
                        <button type="button" id="btnAutoCalcAmount" class="btn btn-secondary" style="width:100%;font-size:.85rem;">🧮 Auto Calculate</button>
                    </div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Receipt Number</label>
                    <input id="receiptNumber" type="text" class="form-input" readonly style="background:var(--input-readonly-bg);">
                    <small style="color:var(--text-muted);font-size:.75rem;">Auto-generated</small>
                </div>
                <div class="form-group"><label class="form-label">Receipt Date *</label><input id="receiptDate" type="date" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Total Amount (RM) *</label><input id="receiptAmount" type="number" step="0.01" min="0" placeholder="e.g., 9000.00" class="form-input" required></div>
                <div class="form-group">
                    <label class="form-label">Deposit Paid (RM)</label>
                    <input id="receiptDepositAmount" type="number" step="0.01" min="0" placeholder="e.g., 1000.00" class="form-input">
                    <small style="color:var(--text-muted);font-size:.75rem;">Kosongkan jika tiada deposit</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Method *</label>
                    {{-- ✅ Native select — options statik --}}
                    <select id="receiptPaymentMethod" class="form-select" required>
                        <option value="">-- Select --</option>
                        <option value="cash">💵 Cash</option>
                        <option value="online_banking">🏦 Online Banking</option>
                        <option value="e-wallet">📱 E-Wallet</option>
                        <option value="credit_card">💳 Credit Card</option>
                        <option value="debit_card">💳 Debit Card</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Notes</label>
                    <textarea id="receiptDescription" rows="2" placeholder="e.g., Deposit pertama untuk trip Istanbul. Baki RM 8,000.00 perlu dibayar sebelum 1 Mei 2026." class="form-input"></textarea>
                </div>
            </div>

            <div style="text-align:center;margin-top:2rem;">
                <button type="submit" class="btn btn-success btn-lg">💾 Create Receipt</button>
                <button type="reset" id="btnResetReceipt" class="btn btn-warning btn-lg" style="margin-left:1rem;">🔄 Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Load data
    window.loadCompanies && window.loadCompanies(function () {
        window.populateReceiptCompanyDropdown && window.populateReceiptCompanyDropdown();
    });
    window.loadTrips && window.loadTrips();

    // Init auto-fill fields
    if ($('#receiptNumber').length && !$('#receiptNumber').val()) {
        $('#receiptNumber').val(window.generateReceiptNumber && window.generateReceiptNumber());
    }
    if ($('#receiptDate').length && !$('#receiptDate').val()) {
        $('#receiptDate').val(new Date().toISOString().split('T')[0]);
    }
});
</script>
@endpush