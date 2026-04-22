// ============================================================
// public/js/core/customers.js
// Customer Module — load, render, search, IC input, states
// ✅ Native DOM untuk semua dropdown kecuali customer TomSelect
// ============================================================

$(function () {

    let offset  = 0;
    let limit   = 50;
    let loading = false;

    // ── Format tarikh: ISO/datetime → YYYY-MM-DD (untuk input date) ──
    function formatDateYMD(val) {
        if (!val || val === 'null') return '';
        // Ambil 10 char: handles "2026-03-29T16:00:00.000000Z" → "2026-03-29"
        return String(val).substring(0, 10);
    }

    // ── Format tarikh untuk display: YYYY-MM-DD → DD-MM-YYYY ──
    window.formatDateDMY = function(val) {
        if (!val || val === 'null') return '';
        var d = String(val).substring(0, 10);
        var p = d.split('-');
        return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : d;
    };


    // ── Load ─────────────────────────────────────────────────

    window.loadCustomersFromServer = function(filters, reset) {
        filters = filters || {};
        reset   = reset   || false;
        if (loading) return;
        loading = true;

        if (reset) { allCustomers = []; offset = 0; }

        if (Object.keys(filters).length > 0) {
            $('#tableBody').html(`
                <tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--primary-color);">
                    <div style="font-size:2rem;margin-bottom:0.5rem;">🔍</div>
                    <div style="font-size:1.1rem;font-weight:600;">Searching...</div>
                </td></tr>
            `);
        }

        $.ajax({
            url: '/customers/search', type: 'GET',
            data: Object.assign({}, filters, { offset: offset, limit: limit }),
            success: function(response) {
                if (response.success) {
                    response.data.forEach(function(c) { allCustomers.push(c); });
                    window.renderCustomerTable(allCustomers);
                    window.updateSystemInfo();
                }
                loading = false;
            },
            error: function() {
                window.showNotification('Gagal memuatkan pelanggan', 'error');
                loading = false;
                $('#tableBody').html(`
                    <tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--error-color);">
                        <div style="font-size:2rem;margin-bottom:0.5rem;">❌</div>
                        <div>Failed to load customers</div>
                    </td></tr>
                `);
            }
        });
    };


    // ── Render ────────────────────────────────────────────────

    window.renderCustomerTable = function(customers) {
        $('#tableBody').empty();
        $('#resultsCount').text(customers.length);

        if (customers.length === 0) {
            $('#tableBody').html(`
                <tr><td colspan="10" style="text-align:center;padding:2rem;color:#a0aec0;">
                    <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
                    <div style="font-size:1.2rem;font-weight:600;">No customers found</div>
                    <div style="margin-top:0.5rem;">Try adjusting your search filters</div>
                </td></tr>
            `);
            return;
        }

        customers.forEach(function(customer, index) {
            const ic    = customer.ic ? customer.ic.toString() : '';
            const icFmt = ic.length >= 12
                ? ic.slice(0,6) + '-' + ic.slice(6,8) + '-' + ic.slice(8)
                : ic;

            const customerId = customer.unique_id || customer.id;
            const esc = function(str) {
                return (str || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            };

            const calcAge = function(icStr) {
                if (!icStr) return '';
                const raw = icStr.toString().replace(/\D/g, '');
                if (raw.length < 6) return '';
                const yy   = parseInt(raw.slice(0, 2));
                const year = yy > (new Date().getFullYear() % 100) ? 1900 + yy : 2000 + yy;
                return new Date().getFullYear() - year;
            };

            const displayAge = customer.age || calcAge(customer.ic) || '-';
            const stateId    = customer.state && customer.state.id   ? customer.state.id   : '';
            const stateName  = customer.state && customer.state.name ? customer.state.name : '-';

            let actionButtons = '';
            if (customer.deleted_at) {
                actionButtons = `<span class="undo-link" data-id="${customerId}" style="cursor:pointer;color:var(--primary-color);text-decoration:underline;">Undo Delete</span>`;
            } else {
                actionButtons = `
                    <button class="btn btn-warning edit-btn"
    data-id="${customerId}">
    ✏️ Edit
</button>
                    <button class="btn btn-danger delete-btn"
                        data-id="${customerId}" style="margin-left:0.5rem;">
                        🗑️ Delete
                    </button>
                `;
            }

            $('#tableBody').append(`
                <tr id="customer-${customerId}">
                    <td>${index + 1}</td>
                    <td>
                        <span style="font-family:monospace;font-size:0.78rem;
                            background:var(--input-readonly-bg);color:var(--primary-color);
                            padding:3px 8px;border-radius:6px;font-weight:600;
                            letter-spacing:0.5px;border:1px solid var(--border-color);">
                            ${customer.ref_id || '-'}
                        </span>
                    </td>
                    <td>${icFmt}</td>
                    <td>
                        <div class="customer-name">${customer.name || '-'}</div>
                        <div class="customer-email">${customer.email || '-'}</div>
                        <div class="customer-phone">${customer.phone || '-'}</div>
                    </td>
                    <td>${displayAge}</td>
                    <td style="text-transform:capitalize;">${customer.gender || '-'}</td>
                    <td>${stateName}</td>
                    <td>${customer.address && customer.address.address_line ? customer.address.address_line : '-'}</td>
                    <td>${customer.address && customer.address.postcode ? customer.address.postcode : '-'}</td>
                    <td>${actionButtons}</td>
                </tr>
            `);
        });
    };


    // ── IC Auto-format ────────────────────────────────────────

    function setupICInput(inputSel, ageSel, genderSel) {
        $(inputSel).on('input', function() {
            let raw = $(this).val().replace(/\D/g, '').slice(0, 12);
            let fmt = raw.slice(0, 6);
            if (raw.length > 6) fmt += '-' + raw.slice(6, 8);
            if (raw.length > 8) fmt += '-' + raw.slice(8);
            $(this).val(fmt);

            if (raw.length >= 6) {
                let yy   = parseInt(raw.slice(0, 2));
                let year = yy > (new Date().getFullYear() % 100) ? 1900 + yy : 2000 + yy;
                $(ageSel).val(new Date().getFullYear() - year);
            } else {
                $(ageSel).val('');
            }

            if (raw.length >= 12) {
                $(genderSel).val(parseInt(raw.slice(-1)) % 2 === 0 ? 'female' : 'male');
            } else {
                $(genderSel).val('');
            }
        });
    }

    setupICInput('#icNumberMain', '#ageMain', '#genderMain');
    setupICInput('#icNumber',     '#age',     '#gender');


    // ── Load States — native DOM ──────────────────────────────
    // ✅ Guna native .append() — selectState, selectStateMain, searchState
    //    bukan TomSelect (dah dibuang dari whitelist)

    window.loadStates = function(selectedStateId) {
        selectedStateId = selectedStateId || null;
        $.ajax({
            url: '/states', type: 'GET',
            success: function(response) {
                if (!response.success || !response.data.length) return;

                var states = response.data;

                // ── selectState (edit modal) ──────────────────
                var $sel = $('#selectState');
                if ($sel.length) {
                    $sel.find('option:not(:first)').remove();
                    states.forEach(function(s) {
                        $sel.append('<option value="' + s.id + '">' + s.name + '</option>');
                    });
                    if (selectedStateId) $sel.val(String(selectedStateId));
                }

                // ── selectStateMain (add customer form) ───────
                var $main = $('#selectStateMain');
                if ($main.length) {
                    $main.find('option:not(:first)').remove();
                    states.forEach(function(s) {
                        $main.append('<option value="' + s.id + '">' + s.name + '</option>');
                    });
                }

                // ── searchState (filter) ──────────────────────
                var $search = $('#searchState');
                if ($search.length) {
                    $search.find('option:not(:first)').remove();
                    states.forEach(function(s) {
                        $search.append('<option value="' + s.id + '">' + s.name + '</option>');
                    });
                }
            },
            error: function() { window.showNotification('Gagal memuat negeri', 'error'); }
        });
    };

    window.loadStates();


    // ── CRUD — Add Customer (main form) ───────────────────────

    $('#customerFormMain').submit(function(e) {
        e.preventDefault();

        var formData = {
            ic:                $('#icNumberMain').val().replace(/\D/g, ''),
            name:              $('[name="name"]',    this).val().trim(),
            email:             $('[name="email"]',   this).val().trim(),
            phone:             $('[name="phone"]',   this).val().trim(),
            state_id:          $('#selectStateMain').val(),       // ✅ native .val()
            address_line:      $('[name="address_line"]', this).val().trim(),
            postcode:          $('[name="postcode"]', this).val().trim(),
            company_id:        $('#customerCompanySelect').val() || '',  // ✅ native .val()
            country_of_birth:  $('#addCountryOfBirth').val(),    // ✅ native .val()
            passport_no:       $('[name="passport_no"]',       this).val().trim(),
            pp_warganegara:    $('#addPpWarganegara').val(),      // ✅ native .val()
            pp_tarikh_lahir:   $('[name="pp_tarikh_lahir"]',   this).val(),
            pp_gender:         $('[name="pp_gender"]',         this).val(),
            pp_date_issued:    $('[name="pp_date_issued"]',    this).val(),
            pp_date_expired:   $('[name="pp_date_expired"]',   this).val(),
            pp_issuing_office: $('[name="pp_issuing_office"]', this).val().trim(),
        };

        if (!formData.ic || !formData.name || !formData.email || !formData.phone ||
            !formData.state_id || !formData.address_line || !formData.postcode) {
            window.showNotification('Please fill in all required fields', 'error');
            return;
        }

        $.ajax({
            url: '/customers', type: 'POST', data: formData,
            success: function(response) {
                if (response.success) {
                    window.showNotification('Customer added successfully! ✅', 'success');
                    $('#customerFormMain')[0].reset();
                    $('#icNumberMain').trigger('input');
                    // ✅ native reset
                    $('#selectStateMain').val('');
                    $('#customerCompanySelect').val('');
                    $('#addCountryOfBirth').val('');
                    $('#addPpWarganegara').val('');

                    if (response.data) {
                        allCustomers.unshift(response.data);
                        window.renderCustomerTable(allCustomers);
                        window.updateSystemInfo();
                    } else {
                        window.loadCustomersFromServer({}, true);
                    }
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('<br>')
                    : (xhr.responseJSON && xhr.responseJSON.message || 'Failed to add customer.');
                window.showNotification(msg, 'error');
            }
        });
    });


    // ── CRUD — Edit Customer (modal form) ─────────────────────

    $('#customerForm').submit(function(e) {
        e.preventDefault();

        var method     = $('#formMethod').val();
        var customerId = $('#customerId').val();
        var url        = method === 'PUT' && customerId
            ? '/customers/' + customerId : '/customers';

        var formData = {
            ic:                $('#icNumber').val().replace(/\D/g, ''),
            name:              $('[name="name"]',         this).val().trim(),
            email:             $('[name="email"]',        this).val().trim(),
            phone:             $('[name="phone"]',        this).val().trim(),
            state_id:          $('#selectState').val(),            // ✅ native .val()
            address_line:      $('[name="address_line"]', this).val().trim(),
            postcode:          $('[name="postcode"]',     this).val().trim(),
            country_of_birth:  $('#editCountryOfBirth').val(),     // ✅ native .val()
            passport_no:       $('#editPassportNo').val().trim(),
            pp_warganegara:    $('#editPpWarganegara').val(),       // ✅ native .val()
            pp_tarikh_lahir:   $('#editPpTarikhLahir').val(),
            pp_gender:         $('#editPpGender').val(),
            pp_date_issued:    $('#editPpDateIssued').val(),
            pp_date_expired:   $('#editPpDateExpired').val(),
            pp_issuing_office: $('#editPpIssuingOffice').val().trim(),
        };

        if (!formData.name || !formData.email || !formData.phone ||
            !formData.state_id || !formData.address_line || !formData.postcode) {
            window.showNotification('Please fill in all required fields', 'error');
            return;
        }

        if (method === 'PUT') formData._method = 'PUT';

        $.ajax({
            url: url, type: 'POST', data: formData,
            success: function(response) {
                if (response.success) {
                    window.showNotification(
                        method === 'PUT' ? 'Customer updated! ✅' : 'Customer added! ✅',
                        'success'
                    );
                    $('#customerModal').removeClass('show');
                    $('#icNumber').prop('readonly', false);
                    window.loadCustomersFromServer({}, true);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('<br>')
                    : (xhr.responseJSON && xhr.responseJSON.message || 'Failed to save customer.');
                window.showNotification(msg, 'error');
            }
        });
    });

// ── Populate Edit Modal (Clean version) ─────────────────────
window.populateEditModal = function(c) {

    window._currentEditCustomer = c;  // ✅ simpan reference untuk travelers tab

    $('#formMethod').val('PUT');
    $('#customerId').val(c.unique_id || c.id);
    $('#modalTitle').text('Edit Customer');

    // ── Basic Info ──────────────────────────────────────
    $('#icNumber').val(c.ic || '').prop('readonly', true);
    $('#editName').val(c.name || '');
    $('#editEmail').val(c.email || '');
    $('#editPhone').val(c.phone || '');
    $('#selectState').val(c.state_id || '');
    $('#editAddressLine').val(c.address?.address_line ?? '');
    $('#editPostcode').val(c.address?.postcode ?? '');
    $('#editCountryOfBirth').val(c.country_of_birth ?? '');

    if (c.ic) $('#icNumber').trigger('input');

    // ── Passport ────────────────────────────────────────
    $('#editPassportNo').val(c.passport_no ?? '');
    $('#editPpWarganegara').val(c.pp_warganegara ?? '');
    $('#editPpTarikhLahir').val(formatDateYMD(c.pp_tarikh_lahir));
    $('#editPpGender').val(c.pp_gender ?? '');
    $('#editPpDateIssued').val(formatDateYMD(c.pp_date_issued));
    $('#editPpDateExpired').val(formatDateYMD(c.pp_date_expired));
    $('#editPpIssuingOffice').val(c.pp_issuing_office ?? '');

    // ── Travelers — reset ────────────────────────────────
    var $tab     = $('#travelersTab');
    var $notice  = $('#editTravelersNotice');
    var $list    = $('#editTravelersList');
    var $saveBtn = $('#btnSaveTravelers');

    $tab.attr('data-booking-id', '');
    $tab.data('bookings', []);
    $list.html('');
    $saveBtn.hide();
    etCount = 0;

    // ── Tiada booking langsung ───────────────────────────
    if (!c.bookings || !c.bookings.length) {
        $('#etBookingSelectorWrap').hide();
        $notice.html('ℹ️ Customer ini tiada booking lagi.').show();
        $('#etBookingInfo').hide();

    // ── Ada booking — populate dropdown statik ───────────
    } else {
        $tab.data('bookings', c.bookings);

        var $sel = $('#etBookingSelect');
        $sel.find('option:not(:first)').remove();

        c.bookings.forEach(function (b) {
            var travelers = b.traveler_details && b.traveler_details.length
                ? ' ✅ ' + b.traveler_details.length + ' traveler'
                : ' ○ Tiada traveler';
            var label = b.booking_no + ' · ' + b.booking_status + ' · ' +
                        (b.trip ? b.trip.location : '-') + travelers;
            $sel.append('<option value="' + b.id + '">' + label + '</option>');
        });

        $('#etBookingSelectorWrap').show();
        $notice.hide();
        $('#etBookingInfo')
            .text('↑ Pilih booking untuk lihat atau edit travelers.')
            .show();

        // Auto-pilih: utamakan yang ada travelers, fallback confirmed, fallback pertama
        var autoSelect =
            c.bookings.find(function (b) {
                return b.traveler_details && b.traveler_details.length > 0;
            }) ||
            c.bookings.find(function (b) { return b.booking_status === 'confirmed'; }) ||
            c.bookings[0];

        if (autoSelect) {
            setTimeout(function () {
                $('#etBookingSelect').val(autoSelect.id).trigger('change');
            }, 100);
        }
    }

    window.loadStates(c.state_id);
    $('.modal-tab[data-tab="basicTab"]').trigger('click');
    $('#customerModal').css('display', 'flex').addClass('show');
};
    // ── Modal — Edit button ───────────────────────────────────

    $(document).on('click', '.edit-btn', function() {

    var customerId = $(this).data('id');

    // Cari full object customer
    var customer = allCustomers.find(function(c) {
        return (c.unique_id || c.id) == customerId;
    });

    if (!customer) {
        window.showNotification('Customer data not found', 'error');
        return;
    }

    // Reset form dulu
    if ($('#customerForm').length) $('#customerForm')[0].reset();

    // Call function baru
    window.populateEditModal(customer);
});


    // ── Delete ────────────────────────────────────────────────

    $(document).on('click', '.delete-btn', function() {
        var customerId = $(this).data('id');
        if (!confirm('Are you sure you want to delete this customer?')) return;

        $.ajax({
            url: '/customers/' + customerId, type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    window.showNotification('Customer deleted! 🗑️', 'success');
                    window.loadCustomersFromServer({}, true);
                }
            },
            error: function(xhr) {
                window.showNotification(
                    xhr.responseJSON && xhr.responseJSON.message || 'Failed to delete customer.', 'error'
                );
            }
        });
    });
    /* ─────────────────────────────────────────────────────────
 * Edit Travelers Tab
 * ───────────────────────────────────────────────────────── */

var etCount = 0;

// Render semua traveler blocks dari data sedia ada
window.etRenderAll = function(travelers) {
    var list = document.getElementById('editTravelersList');
    var notice = document.getElementById('editTravelersNotice');
    if (!list) return;

    list.innerHTML = '';
    etCount = 0;

    if (!travelers || !travelers.length) {
        notice.style.display = 'block';
        return;
    }

    notice.style.display = 'none';
    travelers.forEach(function (t) {
        etAddBlock(t);
    });
}

// Bina satu traveler block
window.etAddBlock = function(data) {
    data = data || {};
    var i = etCount++;
    var notice = document.getElementById('editTravelersNotice');
    if (notice) notice.style.display = 'none';

    var typeOpts = ['adult', 'child', 'baby'].map(function (t) {
        return '<option value="' + t + '"' + (data.type === t ? ' selected' : '') + '>' +
               (t === 'adult' ? '👨 Dewasa' : t === 'child' ? '👶 Kanak-kanak' : '🍼 Bayi') +
               '</option>';
    }).join('');

    var genderOpts = [
        { v: 'male',   l: '♂ Lelaki' },
        { v: 'female', l: '♀ Perempuan' },
    ].map(function (g) {
        return '<option value="' + g.v + '"' + (data.gender === g.v ? ' selected' : '') + '>' + g.l + '</option>';
    }).join('');

    var html =
        '<div class="et-block" id="etBlock-' + i + '">' +
        '<div class="et-block-header">' +
            '<div class="et-block-title">👤 Pelancong #' + (i + 1) + '</div>' +
            '<button type="button" class="et-remove-btn" onclick="etRemoveBlock(' + i + ')">✕ Buang</button>' +
        '</div>' +
        '<div class="form-grid">' +

            '<div class="form-group">' +
                '<label class="form-label">Nama Penuh *</label>' +
                '<input type="text" name="et[' + i + '][name]" class="form-input" ' +
                    'placeholder="Nama penuh" required ' +
                    'value="' + (data.name || '') + '">' +
            '</div>' +

            '<div class="form-group">' +
                '<label class="form-label">No. IC / Pasport *</label>' +
                '<input type="text" name="et[' + i + '][ic]" class="form-input et-ic-input" ' +
                    'placeholder="000000-00-0000" maxlength="14" required ' +
                    'value="' + (data.ic || '') + '" ' +
                    'data-idx="' + i + '" oninput="etICInput(' + i + ', this)">' +
            '</div>' +

            '<div class="form-group">' +
                '<label class="form-label">Jenis</label>' +
                '<select name="et[' + i + '][type]" class="form-select">' +
                    typeOpts +
                '</select>' +
            '</div>' +

            '<div class="form-group">' +
                '<label class="form-label">Jantina</label>' +
                '<select name="et[' + i + '][gender]" class="form-select" id="etGender-' + i + '">' +
                    '<option value="">-- Auto dari IC --</option>' +
                    genderOpts +
                '</select>' +
            '</div>' +

            '<div class="form-group">' +
                '<label class="form-label">Tarikh Lahir</label>' +
                '<input type="date" name="et[' + i + '][dob]" class="form-input" ' +
                    'id="etDob-' + i + '" value="' + (data.dob || '') + '">' +
            '</div>' +

            '<div class="form-group">' +
                '<label class="form-label">No. Pasport</label>' +
                '<input type="text" name="et[' + i + '][passport_no]" class="form-input" ' +
                    'placeholder="A12345678" value="' + (data.passport_no || '') + '">' +
            '</div>' +

            '<div class="form-group">' +
                '<label class="form-label">Warganegara</label>' +
                '<input type="text" name="et[' + i + '][nationality]" class="form-input" ' +
                    'placeholder="Malaysian" value="' + (data.nationality || data.pp_warganegara || 'Malaysian') + '">' +
            '</div>' +

            '<div class="form-group">' +
                '<label class="form-label">Luput Pasport</label>' +
                '<input type="date" name="et[' + i + '][pp_expire]" class="form-input" ' +
                    'value="' + (data.pp_expire || data.pp_date_expired || '') + '">' +
            '</div>' +

        '</div>' +
        '</div>';

    document.getElementById('editTravelersList').insertAdjacentHTML('beforeend', html);
    $('#btnSaveTravelers').show();
}

// Auto-detect dari IC
window.etICInput = function (i, input) {
    var raw = input.value.replace(/\D/g, '').slice(0, 12);
    var fmt = raw.slice(0, 6);
    if (raw.length > 6) fmt += '-' + raw.slice(6, 8);
    if (raw.length > 8) fmt += '-' + raw.slice(8);
    input.value = fmt;

    // DOB
    if (raw.length >= 6) {
        var yy = parseInt(raw.slice(0, 2));
        var mm = parseInt(raw.slice(2, 4));
        var dd = parseInt(raw.slice(4, 6));
        var yr = yy > (new Date().getFullYear() % 100) ? 1900 + yy : 2000 + yy;
        if (mm >= 1 && mm <= 12 && dd >= 1 && dd <= 31) {
            var dob = document.getElementById('etDob-' + i);
            if (dob) dob.value = yr + '-' + String(mm).padStart(2,'0') + '-' + String(dd).padStart(2,'0');
        }
    }

    // Gender
    if (raw.length === 12) {
        var last   = parseInt(raw.slice(-1));
        var gender = document.getElementById('etGender-' + i);
        if (gender) gender.value = (last % 2 !== 0) ? 'male' : 'female';
    }
};

// Buang satu block
window.etRemoveBlock = function (i) {
    var block = document.getElementById('etBlock-' + i);
    if (block) block.remove();

    var remaining = document.querySelectorAll('#editTravelersList .et-block');
    if (!remaining.length) {
        var notice = document.getElementById('editTravelersNotice');
        if (notice) notice.style.display = 'block';
    }
};

// Tambah block kosong
document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'btnAddTraveler') {
        etAddBlock({});
    }
});

// Kumpul semua travelers untuk submit
window.etCollect = function() {
    var travelers = [];
    document.querySelectorAll('#editTravelersList .et-block').forEach(function (block) {
        var name = block.querySelector('[name*="[name]"]');
        if (!name || !name.value.trim()) return;

        // ✅ Normalize gender — tukar M/F kepada male/female
        var rawGender = (block.querySelector('[name*="[gender]"]') || {}).value || '';
        var gender = rawGender === 'M' ? 'male'
                   : rawGender === 'F' ? 'female'
                   : rawGender;  // kalau dah 'male'/'female', biar je

        travelers.push({
            name        : name.value.trim(),
            ic          : (block.querySelector('[name*="[ic]"]')         || {}).value || '',
            type        : (block.querySelector('[name*="[type]"]')        || {}).value || 'adult',
            gender      : gender,   // ✅ normalized
            dob         : (block.querySelector('[name*="[dob]"]')         || {}).value || '',
            passport_no : (block.querySelector('[name*="[passport_no]"]') || {}).value || '',
            nationality : (block.querySelector('[name*="[nationality]"]') || {}).value || '',
            pp_expire   : (block.querySelector('[name*="[pp_expire]"]')   || {}).value || '',
        });
    });
    return travelers;
}
// ── Save Travelers → BookingController@updateTravelers ────
$(document).on('click', '#btnSaveTravelers', function () {

    // Baca dari attr dulu, fallback ke select value
    var bookingId = $('#travelersTab').attr('data-booking-id') || $('#etBookingSelect').val();

    if (!bookingId || bookingId.trim() === '') {
        window.showNotification('Sila pilih booking dahulu.', 'warning');
        // Auto-scroll ke tab travelers dan fokus dropdown
        $('.modal-tab[data-tab="travelersTab"]').trigger('click');
        setTimeout(function () { $('#etBookingSelect').focus(); }, 200);
        return;
    }

    // ✅ Semak bookingId sebelum hantar
    if (!bookingId || bookingId.trim() === '') {
        window.showNotification('Tiada booking dikaitkan dengan customer ini.', 'error');
        return;
    }

    var travelers = window.etCollect();

    if (!travelers.length) {
        window.showNotification('Sila tambah sekurang-kurangnya satu pelancong.', 'warning');
        return;
    }

    var $btn = $(this);
    $btn.prop('disabled', true).text('⏳ Menyimpan...');

    // ✅ Hantar sebagai JSON string — Laravel decode sendiri
    $.ajax({
    url:         '/bookings/' + bookingId + '/travelers',
    type:        'POST',
    contentType: 'application/json',
    headers:     { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: JSON.stringify({
        traveler_details:  travelers,
        details_completed: travelers.length > 0 ? 1 : 0,
    }),
        success: function (response) {
            if (response.success) {
                window.showNotification('✅ Pelancong berjaya disimpan!', 'success');
                window.loadCustomersFromServer({}, true);
            } else {
                window.showNotification(response.message || 'Gagal menyimpan.', 'error');
            }
        },
        error: function (xhr) {
            var msg = xhr.responseJSON?.message || 'Server error semasa simpan travelers.';
            window.showNotification(msg, 'error');
        },
        complete: function () {
            $btn.prop('disabled', false).text('💾 Simpan Pelancong');
        }
    });
});
$(document).on('change', '#etBookingSelect', function () {
    var bookingId = $(this).val();
    var $tab      = $('#travelersTab');
    var $list     = $('#editTravelersList');
    var $saveBtn  = $('#btnSaveTravelers');
    var $info     = $('#etBookingInfo');

    $list.html('');
    etCount = 0;

    if (!bookingId) {
        $tab.attr('data-booking-id', '');
        $saveBtn.hide();
        $info.text('↑ Pilih booking untuk lihat atau edit travelers.').show();
        return;
    }

    var bookings = $tab.data('bookings') || [];
    var booking  = bookings.find(function(b) { return String(b.id) === String(bookingId); });
    if (!booking) return;

    $tab.attr('data-booking-id', String(bookingId));

    var statusColor = {
        pending: '#92400e', confirmed: '#065f46',
        cancelled: '#991b1b', completed: '#1e40af'
    }[booking.booking_status] || '#4a5568';

    $info.html(
        '📋 <strong>' + booking.booking_no + '</strong> &nbsp;|&nbsp; ' +
        '<span style="color:' + statusColor + ';font-weight:700;">' + booking.booking_status + '</span>' +
        ' &nbsp;|&nbsp; ' + (booking.trip ? booking.trip.location : '-') +
        ' &nbsp;|&nbsp; Pax: ' + ((booking.pax_adult||0) + (booking.pax_child||0) + (booking.pax_baby||0))
    ).show();

    var travelers = booking.traveler_details || [];

    if (travelers.length > 0) {
        // ✅ Ada travelers — render terus
        window.etRenderAll(travelers);
    } else {
        // ✅ Kosong — auto-populate dari customer sebagai dewasa pertama
        var c = window._currentEditCustomer || {};  // ambil customer data (tambah line bawah)

        var autoTravelers = [];

        // Auto-add customer sebagai adult pertama
        if (c.name) {
            autoTravelers.push({
                name        : c.name        || '',
                ic          : c.ic          || '',
                type        : 'adult',
                gender      : c.gender      || '',
                dob         : c.dob         || '',
                passport_no : c.passport_no || '',
                nationality : c.pp_warganegara || 'Malaysian',
                pp_expire   : c.pp_date_expired || '',
            });
        }

        if (autoTravelers.length) {
            window.etRenderAll(autoTravelers);
            $('#editTravelersNotice')
                .html('ℹ️ Auto-filled dari data customer. Tambah traveler lain jika perlu.')
                .show();
        } else {
            $('#editTravelersNotice')
                .html('ℹ️ Tiada traveler. Klik ➕ untuk tambah.')
                .show();
        }
    }

    $saveBtn.show();
});


    // ── Undo delete ───────────────────────────────────────────

    $(document).on('click', '.undo-link', function() {
        var customerId = $(this).data('id');
        $.ajax({
            url: '/customers/' + customerId + '/restore', type: 'POST',
            success: function(response) {
                if (response.success) {
                    window.showNotification('Customer restored! ✅', 'success');
                    window.loadCustomersFromServer({}, true);
                }
            },
            error: function() { window.showNotification('Failed to restore customer.', 'error'); }
        });
    });


    // ── Close modal ───────────────────────────────────────────

    $('#closeCustomerModal').click(function() {
        $('#customerModal').css('display', '').removeClass('show');
        $('#icNumber').prop('readonly', false);
    });


    // ── Modal tab handler ─────────────────────────────────────

    $(document).on('click', '.modal-tab', function() {
        var tabId = $(this).data('tab');
        $('.modal-tab').css({
            'color':               'var(--text-muted)',
            'border-bottom-color': 'transparent',
            'font-weight':         '500'
        });
        $(this).css({
            'color':               'var(--primary-color)',
            'border-bottom-color': 'var(--primary-color)',
            'font-weight':         '600'
        });
        $('.modal-tab-content').hide();
        $('#' + tabId).show();
    });


    // ── Search ────────────────────────────────────────────────

    var searchTimeout = null;

    function performCustomerSearch() {
        var filters = {
            name:     $('#searchName').val().trim(),
            ic:       $('#searchIC').val().trim().replace(/\D/g, ''),
            email:    $('#searchEmail').val().trim(),
            gender:   $('#searchGender').val(),    // ✅ native .val()
            age_min:  $('#searchAgeMin').val(),
            age_max:  $('#searchAgeMax').val(),
            state_id: $('#searchState').val()      // ✅ native .val()
        };
        Object.keys(filters).forEach(function(k) { if (!filters[k]) delete filters[k]; });
        window.loadCustomersFromServer(filters, true);
    }

    $('#searchName, #searchIC, #searchEmail, #searchAgeMin, #searchAgeMax').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performCustomerSearch, 500);
    });

    // ✅ native .change() — bukan TomSelect
    $('#searchGender, #searchState').change(performCustomerSearch);

    $('#btnSearch').click(function() { clearTimeout(searchTimeout); performCustomerSearch(); });
    $('#searchName, #searchIC, #searchEmail, #searchAgeMin, #searchAgeMax')
        .keypress(function(e) {
            if (e.which === 13) { clearTimeout(searchTimeout); performCustomerSearch(); }
        });


    // ── Utilities ─────────────────────────────────────────────

    window.updateSystemInfo = function() {
        $('#totalCustomersCount').text(allCustomers.filter(function(c) { return !c.deleted_at; }).length);
        $('#lastUpdated').text(new Date().toLocaleString('en-MY', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        }));
    };

    window.calculateAgeFromIC = function(ic) {
        if (!ic || ic.length < 6) return null;
        var raw  = ic.replace(/\D/g, '');
        var yy   = parseInt(raw.slice(0, 2));
        var year = yy > (new Date().getFullYear() % 100) ? 1900 + yy : 2000 + yy;
        return new Date().getFullYear() - year;
    };

    // Modal overlay click close
    $(document).on('click', '.modal-overlay', function(e) {
        if (e.target === this) {
            $(this).css('display', '').removeClass('show');
            if ($(this).attr('id') === 'customerModal') {
                $('#icNumber').prop('readonly', false);
            }
        }
    });

});