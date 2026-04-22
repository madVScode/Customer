// ============================================================
// public/js/modules/bookings.js — v7.0.0
// ✅ Company, Trip, Customer — semua TomSelect
// ✅ Smooth cascade: company → trip → customer
// ✅ Format tarikh: DD/MM/YYYY
// ============================================================

$(function () {

    var selectedCompanyId = null;

    // ── Format tarikh YYYY-MM-DD → DD/MM/YYYY ────────────────
    function formatDate(dateStr) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    // ── Helper: tunggu TomSelect instance ready ───────────────
    function waitForTS(id, fn) {
        var attempts = 0;
        var timer = setInterval(function() {
            attempts++;
            if (window.tsInstances && window.tsInstances[id]) {
                clearInterval(timer);
                fn(window.tsInstances[id]);
            }
            if (attempts > 40) clearInterval(timer);
        }, 100);
    }

    // ── Booking number ────────────────────────────────────────
    window.generateBookingNo = function() {
        var t = Date.now().toString().slice(-8);
        var r = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return 'BKG-' + t + '-' + r;
    };

    if ($('#bookingNo').length && !$('#bookingNo').val()) {
        $('#bookingNo').val(window.generateBookingNo());
    }

    // ── Reset downstream ──────────────────────────────────────
    function resetStep2() {
        window.tsUpdate('bookingTripSelect', [], '-- Pilih Company Dahulu --', false);
        resetStep3();
    }

    function resetStep3() {
        window.tsUpdate('bookingCustomerSelect', [], '-- Pilih Trip Dahulu --', false);
        $('#tripPriceInfo').hide();
        $('#bookingAmount').val('');
    }

    // ── STEP 1: Populate company ──────────────────────────────
    window.populateBookingCompanyDropdown = function() {
        var opts = (allCompanies || []).map(function(c) {
            return { value: String(c.id), text: c.name };
        });
        window.tsUpdate('bookingCompanySelect', opts, '-- Pilih Company --', true);
        selectedCompanyId = null;
        resetStep2();
    };

    // ── STEP 1 → 2: Company change ───────────────────────────
    $(document).on('change', '#bookingCompanySelect', function() {
        selectedCompanyId = $(this).val() || null;
        resetStep2();
        if (!selectedCompanyId) return;

        var trips = (allTrips || []).filter(function(t) {
            return String(t.company_id) === String(selectedCompanyId);
        });

        if (!trips.length) {
            window.tsUpdate('bookingTripSelect', [], '-- Tiada trip untuk company ini --', false);
            window.showNotification && window.showNotification('Tiada trip untuk company ini', 'info', false);
            return;
        }

        window.tsUpdate(
            'bookingTripSelect',
            trips.map(function(t) {
                return {
                    value: String(t.id),
                    text: '✈️ ' + (t.location || t.country || '-') +
                          ' (' + formatDate(t.date_from) + ' → ' + formatDate(t.date_to) + ')'
                };
            }),
            '-- Pilih Trip --',
            true
        );
    });

    // ── STEP 2 → 3: Trip change ───────────────────────────────
    $(document).on('change', '#bookingTripSelect', function() {
        resetStep3();
        var tripId = $(this).val();
        if (!tripId || !selectedCompanyId) return;

        var trip = (allTrips || []).find(function(t) {
            return String(t.id) === String(tripId);
        });

        if (trip) {
            $('#refPriceAdult').text('RM ' + parseFloat(trip.price_adult || 0).toFixed(2));
            $('#refPriceChild').text('RM ' + parseFloat(trip.price_child || 0).toFixed(2));
            $('#refPriceBaby').text('RM '  + parseFloat(trip.price_baby  || 0).toFixed(2));
            $('#refDeposit').text('RM '    + parseFloat(trip.deposit     || 0).toFixed(2));
            if (parseFloat(trip.price_adult) > 0) {
                $('#bookingAmount').val(parseFloat(trip.price_adult).toFixed(2));
            }
            $('#tripPriceInfo').slideDown(200);
        }

        var customers = (allCustomers || []).filter(function(c) {
            return !c.deleted_at && String(c.company_id) === String(selectedCompanyId);
        });

        if (!customers.length) {
            window.tsUpdate('bookingCustomerSelect', [], '-- Tiada customer untuk company ini --', false);
            window.showNotification && window.showNotification('Tiada customer untuk company ini', 'info', false);
            return;
        }

        window.tsUpdate(
            'bookingCustomerSelect',
            customers.map(function(c) {
                return {
                    value: String(c.unique_id),
                    text: c.name + ' (IC: ' + (c.ic || c.ic_no || '') + ')'
                };
            }),
            '-- Pilih Customer --',
            true
        );
    });

    // ── STEP 4: Submit ────────────────────────────────────────
    $(document).on('submit', '#createBookingForm', function(e) {
        e.preventDefault();

        var coInst   = window.tsInstances && window.tsInstances['bookingCompanySelect'];
        var tripInst = window.tsInstances && window.tsInstances['bookingTripSelect'];
        var custInst = window.tsInstances && window.tsInstances['bookingCustomerSelect'];

        var companyId  = coInst   ? coInst.getValue()   : $('#bookingCompanySelect').val();
        var tripId     = tripInst ? tripInst.getValue()  : $('#bookingTripSelect').val();
        var customerId = custInst ? custInst.getValue()  : $('#bookingCustomerSelect').val();

        if (!companyId)  { window.showNotification('Sila pilih company',  'error'); return; }
        if (!tripId)     { window.showNotification('Sila pilih trip',     'error'); return; }
        if (!customerId) { window.showNotification('Sila pilih customer', 'error'); return; }

        var amount        = $('#bookingAmount').val();
        var paymentStatus = $('#bookingPaymentStatus').val();
        var depositStatus = $('#bookingDepositStatus').val();
        var bookingStatus = $('#bookingStatus').val();

        if (!amount || !paymentStatus || !depositStatus || !bookingStatus) {
            window.showNotification('Sila isi semua field yang wajib', 'error');
            return;
        }

        var $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('⏳ Saving...');

        // Kumpul traveler details dari form
var travelers = [];
var enabled   = document.getElementById('cbkEnableTravelers') &&
                document.getElementById('cbkEnableTravelers').checked;

if (enabled) {
    var totalTravelers = window.cbkTravelerCount || 0;
    for (var ti = 0; ti < totalTravelers; ti++) {
        var tName = $('[name="travelers[' + ti + '][name]"]').val();
        if (!tName) continue;

        // ✅ Normalize gender sama seperti etCollect()
        var rawGender = $('[name="travelers[' + ti + '][gender]"]').val() || '';
        var gender = rawGender === 'M' ? 'male'
                   : rawGender === 'F' ? 'female'
                   : rawGender;

        travelers.push({
            name        : tName,
            ic          : $('[name="travelers[' + ti + '][ic]"]').val()          || '',
            type        : $('[name="travelers[' + ti + '][type]"]').val()         || 'adult',
            dob         : $('[name="travelers[' + ti + '][dob]"]').val()          || '',
            gender      : gender,   // ✅ normalized
            passport_no : $('[name="travelers[' + ti + '][passport_no]"]').val()  || '',
            nationality : $('[name="travelers[' + ti + '][nationality]"]').val()  || '',
            pp_expire   : $('[name="travelers[' + ti + '][pp_expire]"]').val()    || '',
        });
    }
}

$.ajax({
    url: '/bookings',
    type: 'POST',
    contentType: 'application/json',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: JSON.stringify({
        company_id:        companyId,
        customer_id:       customerId,
        trip_id:           tripId,
        total_amount:      amount,
        payment_status:    paymentStatus,
        deposit_status:    depositStatus,
        booking_status:    bookingStatus,
        notes:             $('#bookingNotes').val(),
        pax_adult:         parseInt($('#cbkPaxAdultVal').val()) || 1,
        pax_child:         parseInt($('#cbkPaxChildVal').val()) || 0,
        pax_baby:          parseInt($('#cbkPaxBabyVal').val())  || 0,
        traveler_details:  travelers,          // ✅ array terus, buang JSON.stringify
        details_completed: travelers.length > 0 ? 1 : 0,
    }),
            success: function(res) {
                if (res.success) {
                    window.showNotification('Booking berjaya dibuat! 📝', 'success');
                    window.location.href = '/dashboard/bookings';
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('<br>')
                    : (xhr.responseJSON && xhr.responseJSON.message || 'Gagal membuat booking.');
                window.showNotification(msg, 'error');
                $btn.prop('disabled', false).text('💾 Create Booking');
            }
        });
    });

    // ── Load & render bookings ────────────────────────────────
    window.loadBookings = function(filters) {
        $('#bookingsTableBody').html(
            '<tr><td colspan="9" style="text-align:center;padding:2rem;">' +
            '<div style="font-size:2rem;margin-bottom:.5rem;">📋</div>' +
            '<div>Loading...</div></td></tr>'
        );
        $.get('/bookings', filters || {}, function(res) {
            if (res.success) {
                allBookings = res.data || [];
                renderBookingsTable(allBookings);
            }
        }).fail(function() {
            window.showNotification('Gagal memuatkan bookings', 'error');
        });
    };

    function renderBookingsTable(bookings) {
        var $body = $('#bookingsTableBody').empty();
        $('#totalBookingsCount').text(bookings.length);

        if (!bookings.length) {
            $body.html(
                '<tr><td colspan="9" style="text-align:center;padding:2rem;color:#a0aec0;">' +
                '<div style="font-size:3rem;margin-bottom:1rem;">📋</div>' +
                '<div style="font-size:1.1rem;font-weight:600;">Tiada booking</div></td></tr>'
            );
            return;
        }

        var pB = {
            unpaid:  { bg:'#ffebee', cl:'#c62828', tx:'❌ Unpaid'  },
            partial: { bg:'#fff8e1', cl:'#f57f17', tx:'⚡ Partial' },
            paid:    { bg:'#e8f5e9', cl:'#2e7d32', tx:'✅ Paid'    }
        };
        var dB = {
            not_paid: { bg:'#ffebee', cl:'#c62828', tx:'❌ Not Paid' },
            paid:     { bg:'#e8f5e9', cl:'#2e7d32', tx:'✅ Paid'     }
        };
        var sB = {
            pending:   { bg:'#fff8e1', cl:'#f57f17', tx:'🕐 Pending'   },
            confirmed: { bg:'#e8f5e9', cl:'#2e7d32', tx:'✅ Confirmed' },
            cancelled: { bg:'#ffebee', cl:'#c62828', tx:'❌ Cancelled' },
            completed: { bg:'#e3f2fd', cl:'#1565c0', tx:'🏁 Completed' }
        };

        function badge(obj, key) {
            var b = obj[key] || { bg:'#f5f5f5', cl:'#666', tx: key };
            return '<span style="background:' + b.bg + ';color:' + b.cl +
                   ';padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:600;white-space:nowrap;">' + b.tx + '</span>';
        }

        bookings.forEach(function(b, i) {
            var company  = b.trip && b.trip.company ? b.trip.company.name : '-';
            var location = b.trip ? (b.trip.location || b.trip.country || '-') : '-';
            var dateFrom = b.trip && b.trip.date_from ? formatDate(b.trip.date_from) : '';
            var dateTo   = b.trip && b.trip.date_to   ? formatDate(b.trip.date_to)   : '';
            var dates    = dateFrom ? dateFrom + ' → ' + dateTo : '';

            $body.append(
                '<tr>' +
                '<td>' + (i+1) + '</td>' +
                '<td><strong style="font-family:monospace;font-size:.82rem;color:var(--primary-color);">' + (b.booking_no||'-') + '</strong></td>' +
                '<td><div style="font-weight:600;">' + (b.customer ? b.customer.name : '-') + '</div>' +
                    '<div style="font-size:.75rem;color:var(--text-muted);">IC: ' + (b.customer ? (b.customer.ic || b.customer.ic_no || '-') : '-') + '</div></td>' +
                '<td><div style="font-size:.75rem;color:var(--primary-color);font-weight:600;">🏢 ' + company + '</div>' +
                    '<div>' + location + '</div>' +
                    '<div style="font-size:.75rem;color:var(--text-muted);">' + dates + '</div></td>' +
                '<td><strong>RM ' + parseFloat(b.total_amount||0).toFixed(2) + '</strong></td>' +
                '<td>' + badge(pB, b.payment_status) + '</td>' +
                '<td>' + badge(dB, b.deposit_status) + '</td>' +
                '<td>' + badge(sB, b.booking_status) + '</td>' +
                '<td>' +
                    '<button class="btn btn-warning edit-booking-btn" data-id="' + b.id + '" style="font-size:.8rem;padding:.3rem .7rem;">✏️ Edit</button> ' +
                    '<button class="btn btn-danger delete-booking-btn" data-id="' + b.id + '" style="font-size:.8rem;padding:.3rem .65rem;">🗑️</button>' +
                '</td>' +
                '</tr>'
            );
        });
    }

    // ── Edit booking ──────────────────────────────────────────
    $(document).on('click', '.edit-booking-btn', function() {
        var id = $(this).data('id');
        var b  = (allBookings || []).find(function(x) { return x.id == id; });
        if (!b) return;
        $('#editBookingId').val(b.id);
        $('#editTotalAmount').val(b.total_amount);
        $('#editBookingNotes').val(b.notes || '');
        $('#editPaymentStatus').val(b.payment_status);
        $('#editDepositStatus').val(b.deposit_status);
        $('#editBookingStatus').val(b.booking_status);
        $('#editBookingModal').addClass('show');
    });

    $(document).on('submit', '#editBookingForm', function(e) {
        e.preventDefault();
        var id   = $('#editBookingId').val();
        var $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('⏳ Updating...');
        $.ajax({
            url: '/bookings/' + id, type: 'POST',
            data: {
                _method:        'PUT',
                payment_status: $('#editPaymentStatus').val(),
                deposit_status: $('#editDepositStatus').val(),
                booking_status: $('#editBookingStatus').val(),
                total_amount:   $('#editTotalAmount').val(),
                notes:          $('#editBookingNotes').val()
            },
            success: function(res) {
                if (res.success) {
                    window.showNotification('Booking dikemaskini ✅', 'success');
                    $('#editBookingModal').removeClass('show');
                    window.loadBookings();
                }
            },
            error: function(xhr) {
                window.showNotification(xhr.responseJSON && xhr.responseJSON.message || 'Gagal', 'error');
            },
            complete: function() { $btn.prop('disabled', false).text('💾 Update Booking'); }
        });
    });

    $('#closeEditBookingModal').on('click', function() { $('#editBookingModal').removeClass('show'); });

    // ── Delete ────────────────────────────────────────────────
    $(document).on('click', '.delete-booking-btn', function() {
        var id = $(this).data('id');
        if (!confirm('Padam booking ini?')) return;
        $.ajax({
            url: '/bookings/' + id, type: 'DELETE',
            success: function(res) {
                if (res.success) {
                    window.showNotification('Booking dipadam ✅', 'success');
                    window.loadBookings();
                }
            },
            error: function() { window.showNotification('Gagal memadam booking', 'error'); }
        });
    });

    // ── Search ────────────────────────────────────────────────
    var searchTimer = null;
    function doSearch() {
        var name   = $('#searchBookingCustomer').val().trim().toLowerCase();
        var status = $('#searchBookingStatus').val();
        var pay    = $('#searchBookingPayment').val();
        renderBookingsTable((allBookings || []).filter(function(b) {
            return (!name   || (b.customer ? b.customer.name.toLowerCase() : '').includes(name))
                && (!status || b.booking_status === status)
                && (!pay    || b.payment_status === pay);
        }));
    }

    $('#searchBookingCustomer').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(doSearch, 400);
    });
    $('#searchBookingStatus, #searchBookingPayment').on('change', doSearch);
    $('#btnSearchBookings').on('click', function() { clearTimeout(searchTimer); doSearch(); });
    $('#btnResetBookings').on('click', function() {
        $('#searchBookingCustomer').val('');
        $('#searchBookingStatus').val('');
        $('#searchBookingPayment').val('');
        renderBookingsTable(allBookings || []);
    });

});