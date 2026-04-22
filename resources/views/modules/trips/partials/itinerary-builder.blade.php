{{-- ============================================================ --}}
{{-- resources/views/modules/trips/partials/itinerary-builder.blade.php --}}
{{-- Reusable: Itinerary builder dengan tarikh, masa & gambar    --}}
{{-- ============================================================ --}}
<div style="margin-bottom:1.5rem;padding:1.25rem;border:2px solid var(--border-color);border-radius:12px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin:0 0 .2rem;">📅 Itinerary</h3>
            <p style="font-size:.77rem;color:var(--text-muted);margin:0;">
                Tetapkan nombor hari, tarikh, masa dan gambar untuk setiap hari
            </p>
        </div>
        <div style="display:flex;gap:.45rem;">
            <button type="button" id="addItineraryDayBtn" class="btn btn-secondary"
                    style="font-size:.83rem;padding:.48rem 1rem;">➕ Tambah Hari</button>
            <button type="button" id="sortItineraryBtn" class="btn btn-secondary"
                    style="font-size:.83rem;padding:.48rem .85rem;" title="Susun mengikut nombor hari">⇅ Susun</button>
        </div>
    </div>
    <div id="itineraryContainer">
        <div id="itinHint"
             style="text-align:center;padding:1.75rem;color:var(--text-muted);background:var(--input-readonly-bg);border-radius:10px;font-size:.88rem;">
            📅 Klik <strong>"➕ Tambah Hari"</strong> untuk mula membina itinerari
        </div>
    </div>
</div>