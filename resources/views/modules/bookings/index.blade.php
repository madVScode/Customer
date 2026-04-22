{{-- ============================================================ --}}
{{-- resources/views/modules/bookings/index.blade.php            --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'View Bookings')
@section('content')
<div id="viewBookingsView">
    <h1 class="page-title">📄 View Bookings</h1>
    <div class="content-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:1rem;">
            <h2 style="font-size:1.3rem;font-weight:600;margin:0;">🔍 Search Bookings</h2>
            <span class="live-search-badge">Live Search</span>
        </div>
        <div class="search-grid">
            <div class="form-group">
                <label class="form-label">Customer Name</label>
                <input id="searchBookingCustomer" type="text" placeholder="e.g., Ahmad" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Booking Status</label>
                {{-- ✅ Native select — tiada TomSelect --}}
                <select id="searchBookingStatus" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending">🕐 Pending</option>
                    <option value="confirmed">✅ Confirmed</option>
                    <option value="cancelled">❌ Cancelled</option>
                    <option value="completed">🏁 Completed</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Payment Status</label>
                {{-- ✅ Native select — tiada TomSelect --}}
                <select id="searchBookingPayment" class="form-select">
                    <option value="">All</option>
                    <option value="unpaid">❌ Unpaid</option>
                    <option value="partial">⚡ Partial</option>
                    <option value="paid">✅ Paid</option>
                </select>
            </div>
            <div class="form-group" style="display:flex;align-items:flex-end;gap:.5rem;">
                <button id="btnSearchBookings" class="btn btn-primary" style="flex:1;">🔍 Search</button>
                <button id="btnResetBookings" class="btn btn-secondary" style="flex:1;">🔄 Reset</button>
            </div>
        </div>
    </div>
    <div class="content-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h2 style="font-size:1.3rem;font-weight:600;margin:0;">📋 All Bookings</h2>
            <div style="font-size:.9rem;color:var(--text-muted);">Total: <strong id="totalBookingsCount">0</strong></div>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th><th>Booking No.</th><th>Customer</th><th>Trip</th>
                        <th>Amount (RM)</th><th>Payment</th><th>Deposit</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Load semua data yang diperlukan
    window.loadCompanies && window.loadCompanies(function () {
        window.loadBookings && window.loadBookings();
    });
    window.loadTrips && window.loadTrips();
    // loadCustomers diperlukan untuk filter customer dalam booking
    if (typeof window.loadCustomersFromServer === 'function') {
        window.loadCustomersFromServer();
    }
});
</script>
@endpush