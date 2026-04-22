{{-- ============================================================ --}}
{{-- resources/views/modules/trips/index.blade.php               --}}
{{-- View: Senarai Trip dengan filter                            --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Available Trips')
@section('content')
<div id="viewTripsView">
    <h1 class="page-title">🗺️ Available Trip</h1>
    <div class="content-card">
        <div class="search-grid">
            <div class="form-group">
                <label class="form-label">Company</label>
                {{-- Native select — populate via JS, tiada TomSelect --}}
                <select id="filterTripCompany" class="form-select">
                    <option value="">All Companies</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Country</label>
                <input id="filterTripCountry" type="text" placeholder="e.g., Turkey" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Date From</label>
                <input id="filterTripDateFrom" type="date" class="form-input">
            </div>
            <div class="form-group" style="display:flex;align-items:flex-end;gap:.5rem;">
                <button id="btnFilterTrips" class="btn btn-primary" style="flex:1;">🔍 Filter</button>
                <button id="btnResetTrips"  class="btn btn-secondary" style="flex:1;">🔄 Reset</button>
            </div>
        </div>
    </div>
    <div id="tripsCount" style="color:var(--text-muted);font-size:.9rem;margin-bottom:1rem;padding:0 .5rem;"></div>
    <div id="tripsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1.5rem;"></div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Load companies untuk #filterTripCompany
    $.ajax({
        url: '/companies', type: 'GET',
        success: function (res) {
            if (!res.success) return;
            var $sel = $('#filterTripCompany');
            res.data.forEach(function (c) {
                $sel.append('<option value="' + c.id + '">' + c.name + '</option>');
            });
        }
    });

    // Load trips
    window.loadTrips && window.loadTrips();
});
</script>
@endpush