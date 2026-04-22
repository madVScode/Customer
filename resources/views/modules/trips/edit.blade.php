{{-- ============================================================ --}}
{{-- resources/views/modules/trips/edit.blade.php                --}}
{{-- View: Edit Trip — page baru                                 --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Edit Trip')
@section('content')
<div id="editTripPageView">

    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap;">
        <a href="/dashboard/trips"
           style="display:inline-flex;align-items:center;gap:.4rem;padding:8px 18px;
                  background:var(--input-readonly-bg);border:1.5px solid var(--border-color);
                  border-radius:10px;font-size:.85rem;font-weight:700;
                  color:var(--text-secondary);text-decoration:none;">
            ← Kembali ke Trip
        </a>
        <h1 class="page-title" style="margin:0;">✏️ Edit Trip — {{ $trip->location }}</h1>
    </div>

    <div class="content-card" style="max-width:960px;margin:0 auto;">

        <div id="editTripErrors"
             style="display:none;margin-bottom:1rem;padding:.85rem 1rem;background:#fff5f5;
                    border:1.5px solid #fc8181;border-radius:10px;color:#c53030;
                    font-size:.84rem;font-weight:600;line-height:1.7;"></div>

        <form id="editTripForm">
            <input type="hidden" id="editTripId" value="{{ $trip->id }}">

            {{-- ── Photos ── --}}
            <div style="margin-bottom:1.4rem;">
                <div style="font-size:.85rem;font-weight:700;color:var(--text-primary);margin-bottom:.7rem;">📸 Gambar Trip</div>
                <div style="display:flex;gap:.75rem;">

                    {{-- Photo 1 --}}
                    @php $pic1 = $trip->pic1 ? asset("storage/" . $trip->pic1) : ""; @endphp
                    <div class="et-photo-slot" style="flex:1;display:flex;flex-direction:column;gap:.4rem;">
                        <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);">Foto 1 <span style="color:#e53e3e;">*</span></span>
                        <img id="editPic1Preview" src="{{ $pic1 }}"
                             style="width:100%;height:120px;object-fit:cover;border-radius:10px;border:2px solid var(--success-color);{{ $pic1 ? '' : 'display:none;' }}">
                        <div class="et-photo-placeholder" onclick="$('#editPic1Input').click()"
                             style="width:100%;height:120px;border:2px dashed var(--border-color);border-radius:10px;
                                    display:{{ $pic1 ? 'none' : 'flex' }};flex-direction:column;align-items:center;
                                    justify-content:center;cursor:pointer;gap:.25rem;background:var(--input-readonly-bg);"
                             onmouseover="this.style.borderColor='var(--primary-color)'"
                             onmouseout="this.style.borderColor='var(--border-color)'">
                            <span style="font-size:1.6rem;">🖼️</span>
                            <span style="font-size:.72rem;color:var(--text-muted);">Klik pilih</span>
                        </div>
                        <div style="display:flex;gap:.35rem;">
                            <button type="button" onclick="$('#editPic1Input').click()" class="btn btn-secondary" style="flex:1;font-size:.72rem;padding:.3rem .5rem;">📂 Pilih</button>
                            <button type="button" id="removePic1Btn" class="btn btn-danger" style="flex:1;font-size:.72rem;padding:.3rem .5rem;">🗑 Buang</button>
                        </div>
                        <input type="file" id="editPic1Input" accept="image/jpeg,image/png,image/webp" style="display:none;">
                        <input type="hidden" id="deletePic1Flag" value="0">
                    </div>

                    {{-- Photo 2 --}}
                    @php $pic2 = $trip->pic2 ? asset('storage/' . $trip->pic2) : ''; @endphp
                    <div class="et-photo-slot" style="flex:1;display:flex;flex-direction:column;gap:.4rem;">
                        <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);">Foto 2</span>
                        <img id="editPic2Preview" src="{{ $pic2 }}"
                             style="width:100%;height:120px;object-fit:cover;border-radius:10px;border:2px solid var(--success-color);{{ $pic2 ? '' : 'display:none;' }}">
                        <div class="et-photo-placeholder" onclick="$('#editPic2Input').click()"
                             style="width:100%;height:120px;border:2px dashed var(--border-color);border-radius:10px;
                                    display:{{ $pic2 ? 'none' : 'flex' }};flex-direction:column;align-items:center;
                                    justify-content:center;cursor:pointer;gap:.25rem;background:var(--input-readonly-bg);"
                             onmouseover="this.style.borderColor='var(--primary-color)'"
                             onmouseout="this.style.borderColor='var(--border-color)'">
                            <span style="font-size:1.6rem;">🖼️</span>
                            <span style="font-size:.72rem;color:var(--text-muted);">Klik pilih</span>
                        </div>
                        <div style="display:flex;gap:.35rem;">
                            <button type="button" onclick="$('#editPic2Input').click()" class="btn btn-secondary" style="flex:1;font-size:.72rem;padding:.3rem .5rem;">📂 Pilih</button>
                            <button type="button" id="removePic2Btn" class="btn btn-danger" style="flex:1;font-size:.72rem;padding:.3rem .5rem;">🗑 Buang</button>
                        </div>
                        <input type="file" id="editPic2Input" accept="image/jpeg,image/png,image/webp" style="display:none;">
                        <input type="hidden" id="deletePic2Flag" value="0">
                    </div>

                    {{-- Photo 3 --}}
                    @php $pic3 = $trip->pic3 ? asset('storage/' . $trip->pic3) : ''; @endphp
                    <div class="et-photo-slot" style="flex:1;display:flex;flex-direction:column;gap:.4rem;">
                        <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);">Foto 3</span>
                        <img id="editPic3Preview" src="{{ $pic3 }}"
                             style="width:100%;height:120px;object-fit:cover;border-radius:10px;border:2px solid var(--success-color);{{ $pic3 ? '' : 'display:none;' }}">
                        <div class="et-photo-placeholder" onclick="$('#editPic3Input').click()"
                             style="width:100%;height:120px;border:2px dashed var(--border-color);border-radius:10px;
                                    display:{{ $pic3 ? 'none' : 'flex' }};flex-direction:column;align-items:center;
                                    justify-content:center;cursor:pointer;gap:.25rem;background:var(--input-readonly-bg);"
                             onmouseover="this.style.borderColor='var(--primary-color)'"
                             onmouseout="this.style.borderColor='var(--border-color)'">
                            <span style="font-size:1.6rem;">🖼️</span>
                            <span style="font-size:.72rem;color:var(--text-muted);">Klik pilih</span>
                        </div>
                        <div style="display:flex;gap:.35rem;">
                            <button type="button" onclick="$('#editPic3Input').click()" class="btn btn-secondary" style="flex:1;font-size:.72rem;padding:.3rem .5rem;">📂 Pilih</button>
                            <button type="button" id="removePic3Btn" class="btn btn-danger" style="flex:1;font-size:.72rem;padding:.3rem .5rem;">🗑 Buang</button>
                        </div>
                        <input type="file" id="editPic3Input" accept="image/jpeg,image/png,image/webp" style="display:none;">
                        <input type="hidden" id="deletePic3Flag" value="0">
                    </div>

                </div>
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:.4rem;">Format: JPG, PNG, WEBP &bull; Maks 5MB setiap foto</div>
            </div>

            {{-- ── Maklumat Pakej ── --}}
            <div style="margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--border-color);">
                <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);margin-bottom:.75rem;text-transform:uppercase;">✈️ Maklumat Pakej</div>
                <div class="form-grid" style="grid-template-columns:repeat(2,1fr);gap:1.25rem;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Nama Pakej / Destinasi <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripLocation" type="text" value="{{ $trip->location }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Negara <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripCountry" type="text" value="{{ $trip->country }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Negeri / Kawasan</label>
                        <input id="editTripState" type="text" value="{{ $trip->state ?? '' }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kod Negara (ISO) <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripNationalCode" type="text" maxlength="3" value="{{ $trip->national_code }}"
                               class="form-input" style="text-transform:uppercase;font-weight:700;letter-spacing:2px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Syarikat</label>
                        <select id="editTripCompanyId" class="form-input">
                            <option value="">— Pilih Syarikat —</option>
                            @foreach($companies ?? [] as $company)
                                <option value="{{ $company->id }}" {{ $trip->company_id == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select id="editTripStatus" class="form-input">
                            <option value="active"   {{ $trip->status == 'active'   ? 'selected' : '' }}>🟢 Aktif</option>
                            <option value="inactive" {{ $trip->status == 'inactive' ? 'selected' : '' }}>🔴 Tidak Aktif</option>
                            <option value="draft"    {{ $trip->status == 'draft'    ? 'selected' : '' }}>📝 Draft</option>
                            <option value="full"     {{ $trip->status == 'full'     ? 'selected' : '' }}>🔒 Penuh</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── Tarikh & Kapasiti ── --}}
            <div style="margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--border-color);">
                <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);margin-bottom:.75rem;text-transform:uppercase;">📅 Tarikh & Kapasiti</div>
                <div class="form-grid" style="grid-template-columns:repeat(2,1fr);gap:1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Tarikh Mula <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripDateFrom" type="date" value="{{ $trip->date_from }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tarikh Tamat <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripDateTo" type="date" value="{{ $trip->date_to }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tempoh (Hari)</label>
                        <input id="editTripDuration" type="number" min="1" value="{{ $trip->duration ?? '' }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Pax</label>
                        <input id="editTripMaxPax" type="number" min="1" value="{{ $trip->max_pax ?? '' }}" class="form-input">
                    </div>
                </div>
            </div>

            {{-- ── Harga ── --}}
            <div style="margin-bottom:1rem;padding:1.15rem;border:2px solid var(--border-color);border-radius:12px;background:var(--input-readonly-bg);">
                <div style="font-size:.88rem;font-weight:700;color:var(--text-primary);margin-bottom:.9rem;">💰 Harga (RM)</div>
                <div class="form-grid" style="grid-template-columns:repeat(4,1fr);gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">👨 Dewasa <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripPriceAdult" type="number" step="0.01" min="0" value="{{ $trip->price_adult }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">👶 Kanak-kanak <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripPriceChild" type="number" step="0.01" min="0" value="{{ $trip->price_child }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">🍼 Bayi <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripPriceBaby" type="number" step="0.01" min="0" value="{{ $trip->price_baby }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">💳 Deposit <span style="color:var(--error-color);">*</span></label>
                        <input id="editTripDeposit" type="number" step="0.01" min="0" value="{{ $trip->deposit }}" class="form-input" required>
                    </div>
                </div>
            </div>

            {{-- ── Penerangan ── --}}
            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label">📝 Penerangan / Nota</label>
                <textarea id="editTripDescription" rows="3" class="form-input" style="resize:vertical;">{{ $trip->description ?? '' }}</textarea>
            </div>

            {{-- ── Itinerary ── --}}
            <div style="margin-bottom:1.25rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
                    <div>
                        <div style="font-size:.88rem;font-weight:700;color:var(--text-primary);">📅 Itinerary</div>
                        <div style="font-size:.73rem;color:var(--text-muted);">Susun aktiviti harian</div>
                    </div>
                    <button type="button" id="editAddItineraryDayBtn" class="btn btn-primary"
                            style="font-size:.78rem;padding:.35rem .85rem;">+ Tambah Hari</button>
                </div>
                <div id="editItineraryContainer">
                    <div class="et-itinerary-empty"
                         style="text-align:center;padding:1.5rem;color:var(--text-muted);
                                border:2px dashed var(--border-color);border-radius:12px;
                                background:var(--input-readonly-bg);">
                        Klik "Tambah Hari" untuk bina itinerary
                    </div>
                </div>
            </div>

            {{-- ── Actions ── --}}
            <div style="display:flex;justify-content:center;gap:.75rem;margin-top:1.25rem;">
                <button type="submit" id="editTripSubmitBtn" class="btn btn-success btn-lg">💾 Simpan Perubahan</button>
                <a href="/dashboard/trips" class="btn btn-secondary btn-lg">✖ Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {

    // ── Init itinerary dari DB ────────────────────────────────
    var itineraries = @json($trip->itineraries ?? []);
    if (itineraries.length > 0) {
        itineraries.forEach(function(it) {
            if (typeof window.appendEditItineraryDay === 'function') {
                window.appendEditItineraryDay(it);
            }
        });
    }

    // ── Itinerary button handlers ─────────────────────────────
    $(document).on('click', '#editAddItineraryDayBtn', function() {
        if (typeof window.appendEditItineraryDay === 'function') {
            window.appendEditItineraryDay({});
        }
    });

    $(document).on('click', '.et-remove-day', function() {
        $(this).closest('.et-itinerary-day').slideUp(200, function() { $(this).remove(); });
    });

    // ── Photo handlers ────────────────────────────────────────
    $(document).on('change', '#editPic1Input, #editPic2Input, #editPic3Input', function() {
        var n    = this.id.replace('editPic', '').replace('Input', '');
        var file = this.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            window.showNotification('Foto ' + n + ' mesti < 5MB', 'error');
            this.value = ''; return;
        }
        var reader = new FileReader();
        reader.onload = function(ev) {
            $('#editPic' + n + 'Preview').attr('src', ev.target.result).show();
            $('#editPic' + n + 'Preview').closest('.et-photo-slot').find('.et-photo-placeholder').hide();
            $('#deletePic' + n + 'Flag').val('0');
        };
        reader.readAsDataURL(file);
    });

    $(document).on('click', '#removePic1Btn, #removePic2Btn, #removePic3Btn', function() {
        var n = this.id.replace('removePic', '').replace('Btn', '');
        $('#editPic' + n + 'Preview').hide().attr('src', '');
        $('#editPic' + n + 'Preview').closest('.et-photo-slot').find('.et-photo-placeholder').show();
        $('#editPic' + n + 'Input').val('');
        $('#deletePic' + n + 'Flag').val('1');
    });

    // ── Form submit ───────────────────────────────────────────
    $('#editTripForm').on('submit', function(e) {
        e.preventDefault();
        var tripId   = $('#editTripId').val();
        var formData = new FormData();

        // Route is Route::post — no _method spoofing needed
        formData.append('location',      $('#editTripLocation').val());
        formData.append('national_code', $('#editTripNationalCode').val());
        formData.append('country',       $('#editTripCountry').val());
        formData.append('state',         $('#editTripState').val());
        formData.append('date_from',     $('#editTripDateFrom').val());
        formData.append('date_to',       $('#editTripDateTo').val());
        formData.append('duration',      $('#editTripDuration').val());
        formData.append('max_pax',       $('#editTripMaxPax').val());
        formData.append('price_adult',   $('#editTripPriceAdult').val());
        formData.append('price_child',   $('#editTripPriceChild').val());
        formData.append('price_baby',    $('#editTripPriceBaby').val());
        formData.append('deposit',       $('#editTripDeposit').val());
        formData.append('description',   $('#editTripDescription').val());
        formData.append('status',        $('#editTripStatus').val());
        formData.append('company_id',    $('#editTripCompanyId').val());

        // Photos
        [1, 2, 3].forEach(function(n) {
            var file = $('#editPic' + n + 'Input')[0].files[0];
            if (file) formData.append('pic' + n, file);
            formData.append('delete_pic' + n, $('#deletePic' + n + 'Flag').val());
        });

        // Itinerary
        $('.et-itinerary-day').each(function(i) {
            var idx = i + 1;
            var $d  = $(this);
            formData.append('itineraries[' + idx + '][day_number]', $d.find('[name$="[day_number]"]').val() || idx);
            formData.append('itineraries[' + idx + '][title]',      $d.find('[name$="[title]"]').val() || '');
            formData.append('itineraries[' + idx + '][time_from]',  $d.find('[name$="[time_from]"]').val() || '');
            formData.append('itineraries[' + idx + '][time_to]',    $d.find('[name$="[time_to]"]').val() || '');
            formData.append('itineraries[' + idx + '][description]',$d.find('[name$="[description]"]').val() || '');
            var idVal = $d.find('[name$="[id]"]').val();
            if (idVal) formData.append('itineraries[' + idx + '][id]', idVal);
        });

        $('#editTripErrors').hide().html('');
        var btn = $('#editTripSubmitBtn');
        btn.prop('disabled', true).text('⏳ Menyimpan...');

        $.ajax({
            url: '/trips/' + tripId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    window.showNotification('Trip berjaya dikemaskini! ✅', 'success');
                    setTimeout(function() { window.location.href = '/dashboard/trips'; }, 800);
                } else {
                    window.showNotification(res.message || 'Kemaskini gagal', 'error');
                    btn.prop('disabled', false).text('💾 Simpan Perubahan');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $('#editTripErrors').html(Object.values(xhr.responseJSON.errors).flat().join('<br>')).show();
                } else {
                    window.showNotification(xhr.responseJSON && xhr.responseJSON.message || 'Gagal kemaskini trip', 'error');
                }
                btn.prop('disabled', false).text('💾 Simpan Perubahan');
            }
        });
    });
});
</script>
@endpush