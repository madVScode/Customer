{{-- ============================================================ --}}
{{-- resources/views/partials/modals/customer-modal.blade.php    --}}
{{-- ============================================================ --}}
<div class="modal-overlay" id="customerModal">
    <div class="modal-content" style="max-width:750px;">
        <span class="modal-close" id="closeCustomerModal">✖</span>
        <h2 class="modal-header" id="modalTitle">Edit Customer</h2>

        <form id="customerForm">
            <input type="hidden" id="formMethod" value="POST">
            <input type="hidden" id="customerId">

            {{-- ── Tabs ── --}}
            <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;border-bottom:2px solid var(--border-color);padding-bottom:0;">
                <button type="button" class="modal-tab active" data-tab="basicTab"
                        style="padding:.6rem 1.2rem;border:none;background:none;cursor:pointer;font-weight:600;
                               color:var(--primary-color);border-bottom:3px solid var(--primary-color);margin-bottom:-2px;">
                    👤 Basic Info
                </button>
                <button type="button" class="modal-tab" data-tab="passportTab"
                        style="padding:.6rem 1.2rem;border:none;background:none;cursor:pointer;font-weight:600;
                               color:var(--text-muted);border-bottom:3px solid transparent;margin-bottom:-2px;">
                    🛂 Passport
                </button>
                <button type="button" class="modal-tab" data-tab="travelersTab"
                        style="padding:.6rem 1.2rem;border:none;background:none;cursor:pointer;font-weight:600;
                               color:var(--text-muted);border-bottom:3px solid transparent;margin-bottom:-2px;">
                    👥 Travelers
                </button>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- ── Tab 1 · Basic Info ── --}}
            {{-- ══════════════════════════════════════════ --}}
            <div id="basicTab" class="modal-tab-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">IC Number *</label>
                        <input id="icNumber" name="ic" type="text" placeholder="e.g., 950101-01-1234"
                               class="form-input" maxlength="14" required>
                        <small style="color:var(--text-muted);font-size:.75rem;">Format: YYMMDD-PB-XXXX</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Age</label>
                        <input id="age" name="age" type="text" placeholder="Auto-calculated"
                               class="form-input" readonly style="background:var(--input-readonly-bg);">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <input id="gender" name="gender" type="text" placeholder="Auto-detected"
                               class="form-input" readonly style="background:var(--input-readonly-bg);">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input id="editName" name="name" type="text" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input id="editEmail" name="email" type="email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input id="editPhone" name="phone" type="tel" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">State *</label>
                        <select id="selectState" name="state_id" class="form-select" required>
                            <option value="">-- Pilih Negeri --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address Line *</label>
                        <input id="editAddressLine" name="address_line" type="text" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Postcode *</label>
                        <input id="editPostcode" name="postcode" type="text" class="form-input" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country of Birth</label>
                        <select name="country_of_birth" id="editCountryOfBirth" class="form-select">
                            <option value="">-- Select Country --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>{{-- /basicTab --}}

            {{-- ══════════════════════════════════════════ --}}
            {{-- ── Tab 2 · Passport ── --}}
            {{-- ══════════════════════════════════════════ --}}
            <div id="passportTab" class="modal-tab-content" style="display:none;">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Passport No.</label>
                        <input id="editPassportNo" name="passport_no" type="text"
                               placeholder="e.g., A12345678" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nationality (English)</label>
                        <select name="pp_warganegara" id="editPpWarganegara" class="form-select">
                            <option value="">-- Select Nationality --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->nationality }}">
                                    {{ $country->nationality }} ({{ $country->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth (Passport)</label>
                        <input id="editPpTarikhLahir" name="pp_tarikh_lahir" type="date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender (Passport)</label>
                        <select id="editPpGender" name="pp_gender" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="M">Male / Lelaki</option>
                            <option value="F">Female / Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Issued</label>
                        <input id="editPpDateIssued" name="pp_date_issued" type="date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Expired</label>
                        <input id="editPpDateExpired" name="pp_date_expired" type="date" class="form-input">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Issuing Office</label>
                        <input id="editPpIssuingOffice" name="pp_issuing_office" type="text"
                               placeholder="e.g., Jabatan Imigresen Malaysia, Kuala Lumpur" class="form-input">
                    </div>
                </div>
            </div>{{-- /passportTab --}}

            {{-- ══════════════════════════════════════════ --}}
{{-- ── Tab 3 · Travelers ── --}}
{{-- ══════════════════════════════════════════ --}}
<div id="travelersTab" class="modal-tab-content" style="display:none;" data-booking-id="">

    {{-- ── Booking Selector (statik, JS populate options) ── --}}
    <div id="etBookingSelectorWrap" style="display:none;margin-bottom:1rem;">
        <label style="font-size:.75rem;font-weight:700;color:var(--text-muted);
                       display:block;margin-bottom:.35rem;text-transform:uppercase;
                       letter-spacing:.05em;">📋 Pilih Booking</label>
        <select id="etBookingSelect"
        style="width:100%;padding:.55rem .85rem;
               border:1.5px solid var(--border-color);border-radius:8px;
               font-family:inherit;font-size:.845rem;
               background:var(--card-bg);color:var(--text-primary);
               outline:none;transition:border-color .2s;">
            <option value="">-- Pilih Booking --</option>
        </select>
    </div>

    {{-- ── Info bar booking yang dipilih ── --}}
    <div id="etBookingInfo"
         style="display:none;font-size:.8rem;color:var(--text-muted);
                text-align:center;padding:.4rem 0;margin-bottom:.75rem;">
        ↑ Pilih booking untuk lihat atau edit travelers.
    </div>

    {{-- ── Notice (tiada booking / tiada traveler) ── --}}
    <div id="editTravelersNotice"
         style="text-align:center;padding:1rem;color:var(--text-muted);
                font-size:.85rem;border-radius:8px;
                background:var(--input-readonly-bg);margin-bottom:1rem;">
        ℹ️ Tiada data traveler.
    </div>

    <div id="editTravelersList"></div>

    <div style="display:flex;justify-content:space-between;align-items:center;
                margin-top:1rem;gap:.5rem;">
        <button type="button" id="btnAddTraveler"
                style="padding:.45rem 1rem;background:var(--primary-color);color:#fff;
                       border:none;border-radius:8px;font-weight:700;cursor:pointer;">
            ➕ Tambah Pelancong
        </button>

        <button type="button" id="btnSaveTravelers"
                style="display:none;padding:.45rem 1.2rem;
                       background:var(--success-color);color:#fff;
                       border:none;border-radius:8px;font-weight:700;cursor:pointer;">
            💾 Simpan Pelancong
        </button>
    </div>
</div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- ── Save Button ── --}}
            {{-- ══════════════════════════════════════════ --}}
            <div style="text-align:center;margin-top:2rem;">
                <button type="submit" class="btn btn-success btn-lg">💾 Save Customer</button>
            </div>

        </form>
    </div>
</div>