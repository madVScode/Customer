{{-- ============================================================ --}}
{{-- resources/views/modules/trips/add.blade.php                 --}}
{{-- View: Tambah Trip Baru + Itinerary                          --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Add New Trip')
@section('content')
<div id="addTripView">
    <h1 class="page-title">✈️ Add New Trip</h1>
    <div class="content-card">
        <form id="tripForm" enctype="multipart/form-data">

            {{-- ── Photos ── --}}
            <div style="margin-bottom:2rem;">
                <label class="form-label" style="font-size:1rem;font-weight:600;">Trip Photos *</label>
                <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem;">
                    Minimum 1, maximum 3 gambar (JPG/PNG, max 5MB setiap satu)
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
                    @foreach([
                        [1, 'Required'],
                        [2, 'Optional'],
                        [3, 'Optional']
                    ] as [$num, $label])
                    <div>
                        <div id="pic{{ $num }}Box" onclick="document.getElementById('tripPic{{ $num }}').click()"
                             style="aspect-ratio:4/3;border:2px dashed var(--border-color);border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--input-readonly-bg);cursor:pointer;overflow:hidden;transition:border-color .2s;position:relative;">
                            <img id="pic{{ $num }}Preview" src="" style="width:100%;height:100%;object-fit:contain;background:#1a1a2e;display:none;">
                            <div id="pic{{ $num }}Placeholder" style="text-align:center;color:var(--text-muted);">
                                <div style="font-size:2.5rem;">📸</div>
                                <div style="font-size:.8rem;margin-top:.5rem;">Photo {{ $num }} ({{ $label }})</div>
                            </div>
                            <button type="button" id="removePic{{ $num }}"
                                    onclick="event.stopPropagation();removeTripPic({{ $num }})"
                                    style="display:none;position:absolute;top:6px;right:6px;background:rgba(244,67,54,.9);color:white;border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;font-size:.8rem;">✖</button>
                        </div>
                        <input type="file" id="tripPic{{ $num }}" accept="image/*" style="display:none;" data-trip-pic="{{ $num }}">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Trip Details ── --}}
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Company *</label>
                    <select id="tripCompanySelect" name="company_id" class="form-select" required>
                        <option value="">-- Select Company --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Country *</label>
                    <select name="country" id="tripCountrySelect" class="form-select" required>
                        <option value="">-- Select Country --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}" data-iso="{{ $country->iso3_code }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">State / Region</label>
                    <input name="state" type="text" placeholder="e.g., Istanbul" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Location / Package Name *</label>
                    <input name="location" type="text" placeholder="e.g., Istanbul & Cappadocia 10D9N" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">National Code *</label>
                    <input name="national_code" id="tripNationalCode" type="text" placeholder="Auto-filled from country"
                           maxlength="3" class="form-input" required style="text-transform:uppercase;font-weight:700;letter-spacing:2px;">
                    <small style="color:var(--text-muted);font-size:.75rem;">3-letter ISO code, auto-filled when country selected</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Date From *</label>
                    <input id="tripDateFrom" name="date_from" type="date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date To *</label>
                    <input id="tripDateTo" name="date_to" type="date" class="form-input" required>
                </div>
            </div>

            {{-- ── Pricing ── --}}
            <div style="margin:1.5rem 0;padding:1.25rem;border:2px solid var(--border-color);border-radius:12px;">
                <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;">💰 Pricing (RM)</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Price Adult *</label>
                        <input name="price_adult" type="number" step="0.01" min="0" placeholder="e.g., 4500.00" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price Child *</label>
                        <input name="price_child" type="number" step="0.01" min="0" placeholder="e.g., 3500.00" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price Baby *</label>
                        <input name="price_baby" type="number" step="0.01" min="0" placeholder="e.g., 500.00" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deposit (RM) *</label>
                        <input name="deposit" type="number" step="0.01" min="0" placeholder="e.g., 1000.00" class="form-input" required>
                    </div>
                </div>
            </div>

            {{-- ── Itinerary ── --}}
            @include('modules.trips.partials.itinerary-builder')

            <div style="text-align:center;margin-top:1.5rem;">
                <button type="submit" class="btn btn-success btn-lg">💾 Save Trip</button>
                <button type="button" onclick="resetTripForm()" class="btn btn-warning btn-lg" style="margin-left:1rem;">🔄 Reset</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(function () {
        // Load companies untuk dropdown #tripCompanySelect
        window.loadCompanies && window.loadCompanies();

        // Auto-fill national code bila country dipilih
        $('#tripCountrySelect').on('change', function () {
            const iso = $(this).find(':selected').data('iso') || '';
            $('#tripNationalCode').val(iso.toUpperCase());
        });

        // Auto-calculate duration bila date berubah
        $('#tripDateFrom, #tripDateTo').on('change', function () {
            const from = $('#tripDateFrom').val();
            const to   = $('#tripDateTo').val();
            if (from && to) {
                const diff = Math.ceil((new Date(to) - new Date(from)) / (1000 * 60 * 60 * 24));
                if (diff > 0) $('#tripDuration').val(diff);
            }
        });
    });
</script>
@endpush
