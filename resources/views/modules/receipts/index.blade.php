{{-- ============================================================ --}}
{{-- resources/views/modules/receipts/index.blade.php            --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'View Receipts')
@section('content')
<div id="viewReceiptsView">
    <h1 class="page-title">📋 View Receipts</h1>
    <div class="content-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:1rem;">
            <h2 style="font-size:1.3rem;font-weight:600;margin:0;">🔍 Search Receipts</h2>
            <span class="live-search-badge">Live Search</span>
        </div>
        <div class="search-grid">
            <div class="form-group"><label class="form-label">Receipt No.</label><input id="searchReceiptNo" type="text" placeholder="e.g., RCP-12345678-001" class="form-input"></div>
            <div class="form-group"><label class="form-label">Customer Name</label><input id="searchReceiptCustomer" type="text" placeholder="e.g., Ahmad" class="form-input"></div>
            <div class="form-group"><label class="form-label">Date From</label><input id="searchDateFrom" type="date" class="form-input"></div>
            <div class="form-group"><label class="form-label">Date To</label><input id="searchDateTo" type="date" class="form-input"></div>
            <div class="form-group">
                <label class="form-label">Payment Method</label>
                {{-- ✅ Native select — tiada TomSelect --}}
                <select id="searchPaymentMethod" class="form-select">
                    <option value="">All Methods</option>
                    <option value="cash">💵 Cash</option>
                    <option value="online_banking">🏦 Online Banking</option>
                    <option value="e-wallet">📱 E-Wallet</option>
                    <option value="credit_card">💳 Credit Card</option>
                    <option value="debit_card">💳 Debit Card</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Min Amount (RM)</label><input id="searchAmountMin" type="number" step="0.01" placeholder="e.g., 1000" class="form-input"></div>
            <div class="form-group"><label class="form-label">Max Amount (RM)</label><input id="searchAmountMax" type="number" step="0.01" placeholder="e.g., 10000" class="form-input"></div>
            <div class="form-group" style="display:flex;align-items:flex-end;gap:.5rem;">
                <button id="btnSearchReceipts" class="btn btn-primary" style="flex:1;">🔍 Search</button>
                <button id="btnResetReceiptSearch" class="btn btn-secondary" style="flex:1;">🔄 Reset</button>
            </div>
        </div>
    </div>
    <div class="content-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h2 style="font-size:1.3rem;font-weight:600;margin:0;">📄 All Receipts</h2>
            <div style="font-size:.9rem;color:var(--text-muted);">Total: <strong id="totalReceiptsCount">0</strong></div>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>No</th><th>Receipt No.</th><th>Date</th><th>Company</th><th>Customer</th><th>Trip</th><th>Amount (RM)</th><th>Payment</th><th>Actions</th></tr>
                </thead>
                <tbody id="receiptsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    window.loadCompanies && window.loadCompanies(function () {
        window.loadReceiptsFromServer && window.loadReceiptsFromServer();
    });
    window.loadTrips && window.loadTrips();
});
</script>
@endpush