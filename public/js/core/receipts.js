// ============================================================
// public/js/core/receipts.js
// ✅ Company, Trip, Customer — semua TomSelect
// ✅ Smooth cascade: company → trip → customer
// ============================================================
window.loadCompanies = function (callback) {
    $.get('/companies', function (res) {
        if (res.success) {
            window.allCompanies = res.data || [];
            if (typeof callback === 'function') callback();
        }
    }).fail(function () {
        window.allCompanies = window.allCompanies || [];
        if (typeof callback === 'function') callback();
    });
};

window.loadTrips = function (callback) {
    $.get('/trips', function (res) {
        if (res.success) {
            window.allTrips = res.data || [];
            if (typeof callback === 'function') callback();
        }
    }).fail(function () {
        window.allTrips = window.allTrips || [];
        if (typeof callback === 'function') callback();
    });
};
$(function () {

    let currentReceiptData = null;
    let _receiptCustomerCache = {};

    // ── Helpers ───────────────────────────────────────────────

    window.generateReceiptNumber = function() {
        var ts  = Date.now().toString().slice(-8);
        var rnd = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return 'RCP-' + ts + '-' + rnd;
    };

    // ── Populate company — tsUpdate ───────────────────────────
    window.populateReceiptCompanyDropdown = function() {
        var opts = (allCompanies || []).map(function(c) {
            return { value: String(c.id), text: c.name };
        });
        window.tsUpdate('receiptCompanySelect', opts, '-- Select Company --', true);
    };

    // ── Reset customer (TomSelect) ────────────────────────────
    function resetReceiptCustomerSelect(placeholder) {
        _receiptCustomerCache = {};
        window.tsUpdate('receiptCustomerSelect', [], placeholder || '-- Select Company First --', false);
    }

    // ── Reset trip (TomSelect) ────────────────────────────────
    function resetReceiptTripSelect(placeholder) {
        window.tsUpdate('receiptTripSelect', [], placeholder || '-- Select Company First --', false);
        $('#receiptTripInfoBox').hide();
    }


    // ── Load ─────────────────────────────────────────────────

    window.loadReceiptsFromServer = function(filters) {
        filters = filters || {};
        var hasFilter = Object.values(filters).some(function(v) { return v; });
        var url = hasFilter ? '/receipts/search' : '/receipts';

        $('#receiptsTableBody').html(
            '<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--primary-color);">' +
            '<div style="font-size:2rem;margin-bottom:0.5rem;">🔍</div>' +
            '<div>Loading receipts...</div></td></tr>'
        );

        $.ajax({
            url: url, type: 'GET', data: filters,
            success: function(response) {
                if (response.success) {
                    allReceipts = response.data || [];
                    renderReceiptsTable(allReceipts);
                }
            },
            error: function() {
                window.showNotification('Gagal memuatkan resit', 'error');
                $('#receiptsTableBody').html(
                    '<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--error-color);">' +
                    '<div style="font-size:2rem;">❌</div><div>Failed to load receipts</div></td></tr>'
                );
            }
        });
    };


    // ── Render ────────────────────────────────────────────────

    function renderReceiptsTable(receipts) {
        $('#receiptsTableBody').empty();
        $('#totalReceiptsCount').text(receipts.length);

        if (receipts.length === 0) {
            $('#receiptsTableBody').html(
                '<tr><td colspan="9" style="text-align:center;padding:2rem;color:#a0aec0;">' +
                '<div style="font-size:3rem;margin-bottom:1rem;">📄</div>' +
                '<div style="font-size:1.2rem;font-weight:600;">No receipts found</div>' +
                '<div style="margin-top:0.5rem;">Create your first receipt from the Create Receipt page</div>' +
                '</td></tr>'
            );
            return;
        }

        var paymentIcons = {
            cash: '💵', online_banking: '🏦',
            'e-wallet': '📱', credit_card: '💳', debit_card: '💳'
        };

        receipts.forEach(function(receipt, index) {
            var icon       = paymentIcons[receipt.payment_method] || '💰';
            var methodText = receipt.payment_method.replace(/_/g, ' ')
                .replace(/\b\w/g, function(l) { return l.toUpperCase(); });
            var date     = new Date(receipt.issued_at).toLocaleDateString('en-MY');
            var logoMini = receipt.company && receipt.company.logo_url
                ? '<img src="' + receipt.company.logo_url + '" style="width:28px;height:28px;object-fit:cover;border-radius:5px;margin-right:6px;vertical-align:middle;">'
                : '';

            $('#receiptsTableBody').append(
                '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td><strong style="font-size:0.85rem;">' + receipt.receipt_no + '</strong></td>' +
                '<td>' + date + '</td>' +
                '<td><div style="display:flex;align-items:center;">' + logoMini +
                    '<strong>' + (receipt.company ? receipt.company.name : '-') + '</strong></div></td>' +
                '<td>' + (receipt.customer ? receipt.customer.name : '-') + '</td>' +
                '<td style="font-size:0.8rem;">' + (receipt.trip ? receipt.trip.location : '-') + '</td>' +
                '<td><strong>RM ' + parseFloat(receipt.total_amount).toFixed(2) + '</strong></td>' +
                '<td>' + icon + ' ' + methodText + '</td>' +
                '<td>' +
                    '<button class="btn btn-primary view-receipt-btn" data-receipt-id="' + receipt.id + '">👁️ View</button> ' +
                    '<button class="btn btn-danger delete-receipt-btn" data-receipt-id="' + receipt.id + '" style="margin-left:0.5rem;">🗑️</button>' +
                '</td>' +
                '</tr>'
            );
        });
    }


    // ── Company change → load trips & customers ───────────────

    $(document).on('change', '#receiptCompanySelect', function () {
    var companyId = $(this).val();

    // ✅ Clear cache dulu
    _receiptCustomerCache = {};

    // Reset downstream
    $('#receiptTripSelect').val('').prop('disabled', true)
        .find('option:not(:first)').remove();
    $('#receiptCustomerSelect').val('').prop('disabled', true)
        .find('option:not(:first)').remove();
    $('#receiptTripInfoBox').hide();
    $('#receiptCompanyPreview').hide();
    $('#receiptBookingId').val('');

    if (!companyId) return;

        // Company preview
        var company = (allCompanies || []).find(function(c) {
            return String(c.id) === String(companyId);
        });
        if (company) {
            var logoHtml = company.logo_url
                ? '<img src="' + company.logo_url + '" style="width:100%;height:100%;object-fit:cover;">'
                : '🏢';
            $('#receiptCompanyLogo').html(logoHtml);
            $('#receiptCompanyName').text(company.name);
            $('#receiptCompanyReg').text('Reg: ' + (company.registration_no || '-'));
            $('#receiptCompanyAddr').text(company.address || '');
            $('#receiptCompanyPreview').slideDown(200);
        }

        // ✅ TomSelect — loading state untuk customer
        window.tsUpdate('receiptCustomerSelect', [], '-- Memuatkan...', false);

        // Load customers via AJAX
        $.ajax({
            url: '/receipts/customers-with-bookings', type: 'GET',
            data: { company_id: companyId },
            success: function(response) {
    if (response.success && response.data.length > 0) {
        _receiptCustomerCache = {};
        response.data.forEach(function(c) {
            _receiptCustomerCache[String(c.unique_id)] = c; // ✅ simpan bookings sekali
        });
        window.tsUpdate(
            'receiptCustomerSelect',
            response.data.map(function(c) {
                var bookingCount = c.bookings ? c.bookings.length : 0;
                return {
                    value: String(c.unique_id),
                    text:  c.name + ' — ' + bookingCount + ' booking confirmed'
                };
            }),
            '-- Select Customer --',
            true
        );
    } else {
        window.tsUpdate('receiptCustomerSelect', [], '⚠️ Tiada customer dengan booking confirmed', false);
    }
},
            error: function() {
                window.tsUpdate('receiptCustomerSelect', [], '-- Select Customer --', true);
            }
        });

        // ✅ Trip diisi bila customer dipilih — reset dulu
        resetReceiptTripSelect('-- Pilih Customer Dahulu --');
        $('#receiptTripInfoBox').hide();
    });

    // ── Customer change → populate trip dari booking confirmed ────

$(document).on('change', '#receiptCustomerSelect', function () {
    $('#receiptBookingId').val(''); // ✅ clear bila tukar customer
    var customerId = $(this).val();

    resetReceiptTripSelect('-- Pilih Trip --');
    $('#receiptPaxAdult').val(0);
    $('#receiptPaxChild').val(0);
    $('#receiptPaxBaby').val(0);

    if (!customerId) return;

    var customer = _receiptCustomerCache[String(customerId)];
    if (!customer || !customer.bookings || !customer.bookings.length) {
        window.tsUpdate('receiptTripSelect', [], '⚠️ Tiada booking confirmed', false);
        return;
    }

    // ✅ Populate trip dari booking customer sahaja
    window.tsUpdate(
        'receiptTripSelect',
        customer.bookings.map(function (b) {
            return {
                value: String(b.trip_id),
                text:  '✈️ ' + b.trip_name +
                       (b.date_from ? ' (' + b.date_from.substring(0, 10) + ')' : '') +
                       ' — ' + b.booking_no,
                // ✅ Simpan booking data dalam option untuk auto-fill pax
                booking_id:  b.id,
                pax_adult:   b.pax_adult,
                pax_child:   b.pax_child,
                pax_baby:    b.pax_baby,
            };
        }),
        '-- Pilih Trip --',
        true
    );

    // ✅ Auto-pilih jika hanya ada 1 booking
    if (customer.bookings.length === 1) {
        var b = customer.bookings[0];
        setTimeout(function () {
            var tripInst = window.tsInstances && window.tsInstances['receiptTripSelect'];
            if (tripInst) tripInst.setValue(String(b.trip_id));
            else $('#receiptTripSelect').val(String(b.trip_id)).trigger('change');
        }, 100);
    }
});


    // ── Trip change → show info box ───────────────────────────

    $(document).on('change', '#receiptTripSelect', function () {
    var tripId = $(this).val();
    if (!tripId) { $('#receiptTripInfoBox').hide(); return; }

    // ✅ Ambil trip data dari allTrips untuk info box
    var trip = (allTrips || []).find(function (t) {
        return String(t.id) === String(tripId);
    });
    if (trip) {
        $('#rTripCountry').text(trip.country   || '-');
        $('#rTripDates').text((trip.date_from  || '-') + ' → ' + (trip.date_to || '-'));
        $('#rTripDuration').text((trip.duration || '-') + ' days');
        $('#rTripCode').text(trip.national_code || '-');
        $('#rPriceAdult').text('RM '   + parseFloat(trip.price_adult || 0).toFixed(2));
        $('#rPriceChild').text('RM '   + parseFloat(trip.price_child || 0).toFixed(2));
        $('#rPriceBaby').text('RM '    + parseFloat(trip.price_baby  || 0).toFixed(2));
        $('#rPriceDeposit').text('RM ' + parseFloat(trip.deposit     || 0).toFixed(2));
        $('#receiptTripInfoBox').slideDown(200);
    }

    // ✅ Auto-fill pax dari booking customer yang dipilih
    var custInst   = window.tsInstances && window.tsInstances['receiptCustomerSelect'];
    var customerId = custInst ? custInst.getValue() : $('#receiptCustomerSelect').val();
    var customer   = _receiptCustomerCache[String(customerId)];

    if (customer && customer.bookings) {
        var booking = customer.bookings.find(function (b) {
            return String(b.trip_id) === String(tripId);
        });
        if (booking) {
            $('#receiptPaxAdult').val(booking.pax_adult || 0);
            $('#receiptPaxChild').val(booking.pax_child || 0);
            $('#receiptPaxBaby').val(booking.pax_baby  || 0);
            $('#receiptBookingId').val(booking.id); // ✅ simpan booking_id

            // ✅ Auto-calculate amount terus
            $('#btnAutoCalcAmount').trigger('click');

            window.showNotification &&
                window.showNotification(
                    'Pax auto-isi dari booking ' + booking.booking_no + ' ✅', 'info'
                );
        }
    }
});


    // ── Auto-calculate amount ─────────────────────────────────

    $(document).on('click', '#btnAutoCalcAmount', function() {
        var tripInst = window.tsInstances && window.tsInstances['receiptTripSelect'];
        var tripId   = tripInst ? tripInst.getValue() : $('#receiptTripSelect').val();

        if (!tripId) { window.showNotification('Please select a trip first', 'error'); return; }

        var trip = (allTrips || []).find(function(t) { return String(t.id) === String(tripId); });
        if (!trip) { window.showNotification('Trip data not found', 'error'); return; }

        var pAdult   = parseFloat(trip.price_adult || 0);
        var pChild   = parseFloat(trip.price_child || 0);
        var pBaby    = parseFloat(trip.price_baby  || 0);
        var pDeposit = parseFloat(trip.deposit     || 0);
        var nAdult   = parseInt($('#receiptPaxAdult').val() || 0);
        var nChild   = parseInt($('#receiptPaxChild').val() || 0);
        var nBaby    = parseInt($('#receiptPaxBaby').val()  || 0);

        var total   = (nAdult * pAdult) + (nChild * pChild) + (nBaby * pBaby);
        var deposit = pDeposit * (nAdult + nChild);

        $('#receiptAmount').val(total.toFixed(2));
        $('#receiptDepositAmount').val(deposit.toFixed(2));
        window.showNotification('Total: RM ' + total.toFixed(2) + ' | Deposit: RM ' + deposit.toFixed(2) + ' 🧮', 'info');
    });


    // ── Reset form ────────────────────────────────────────────

    $(document).on('click', '#btnResetReceipt', function() {
        $('#receiptCompanyPreview').hide();
        // ✅ TomSelect reset untuk semua 3
        window.tsUpdate('receiptCompanySelect', (allCompanies || []).map(function(c) {
            return { value: String(c.id), text: c.name };
        }), '-- Select Company --', true);
        resetReceiptCustomerSelect();
        resetReceiptTripSelect();
        $('#receiptBookingId').val('');
        $('#receiptNumber').val(window.generateReceiptNumber());
        $('#receiptDate').val(new Date().toISOString().split('T')[0]);
    });


    // ── View / Delete ─────────────────────────────────────────

    $(document).on('click', '.view-receipt-btn', function() {
        var receiptId = $(this).data('receipt-id');
        var receipt   = allReceipts.find(function(r) { return r.id == receiptId; });
        if (receipt) displayReceipt(receipt);
    });

    $(document).on('click', '.delete-receipt-btn', function() {
        var receiptId = $(this).data('receipt-id');
        if (!confirm('Are you sure you want to delete this receipt?')) return;
        $.ajax({
            url: '/receipts/' + receiptId, type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    window.showNotification('Receipt deleted! 🗑️', 'success');
                    window.loadReceiptsFromServer();
                }
            },
            error: function(xhr) {
                window.showNotification(xhr.responseJSON && xhr.responseJSON.message || 'Failed to delete receipt.', 'error');
            }
        });
    });


    // ── Create receipt form ───────────────────────────────────

    $('#createReceiptForm').submit(function(e) {
        e.preventDefault();

        // ✅ TomSelect getValue untuk semua 3
        var coInst   = window.tsInstances && window.tsInstances['receiptCompanySelect'];
        var tripInst = window.tsInstances && window.tsInstances['receiptTripSelect'];
        var custInst = window.tsInstances && window.tsInstances['receiptCustomerSelect'];

        var companyId  = coInst   ? coInst.getValue()   : $('#receiptCompanySelect').val();
        var tripId     = tripInst ? tripInst.getValue()  : $('#receiptTripSelect').val();
        var customerId = custInst ? custInst.getValue()  : $('#receiptCustomerSelect').val();

        if (!companyId)  { window.showNotification('Please select a company',  'error'); return; }
        if (!customerId) { window.showNotification('Please select a customer', 'error'); return; }
        if (!tripId)     { window.showNotification('Please select a trip',     'error'); return; }

        var selectedTrip     = (allTrips || []).find(function(t) { return String(t.id) === String(tripId); });
        var capturedDuration = selectedTrip ? (selectedTrip.duration || '-') : '-';

        var formData = {
            company_id:     companyId,
            customer_id:    customerId,
            trip_id:        tripId,
            booking_id:     $('#receiptBookingId').val() || '',
            receipt_no:     $('#receiptNumber').val(),
            issued_at:      $('#receiptDate').val(),
            total_amount:   $('#receiptAmount').val(),
            deposit_amount: $('#receiptDepositAmount').val() || 0,
            pax_adult:      $('#receiptPaxAdult').val() || 0,
            pax_child:      $('#receiptPaxChild').val() || 0,
            pax_baby:       $('#receiptPaxBaby').val()  || 0,
            payment_method: $('#receiptPaymentMethod').val(),
            notes:          $('#receiptDescription').val()
        };

        if (!formData.receipt_no || !formData.issued_at || !formData.total_amount || !formData.payment_method) {
            window.showNotification('Please fill in all required fields', 'error');
            return;
        }

        var submitBtn = $(this).find('[type=submit]');
        submitBtn.prop('disabled', true).text('⏳ Creating...');

        $.ajax({
            url: '/receipts', type: 'POST', data: formData,
            success: function(response) {
                if (response.success) {
                    window.showNotification('Receipt created! ✅', 'success');
                    $('#createReceiptForm')[0].reset();
                    $('#receiptNumber').val(window.generateReceiptNumber());
                    $('#receiptDate').val(new Date().toISOString().split('T')[0]);
                    $('#receiptCompanyPreview, #receiptTripInfoBox').hide();

                    // ✅ Reset semua 3 TomSelect
                    window.populateReceiptCompanyDropdown && window.populateReceiptCompanyDropdown();
                    resetReceiptCustomerSelect();
                    resetReceiptTripSelect();

                    if (response.data) {
                        if (response.data.trip && !response.data.trip.duration) {
                            response.data.trip.duration = capturedDuration;
                        }
                        allReceipts.unshift(response.data);
                        displayReceipt(response.data);
                    }
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('<br>')
                    : (xhr.responseJSON && xhr.responseJSON.message || 'Failed to create receipt.');
                window.showNotification(msg, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('💾 Create Receipt');
            }
        });
    });


    // ── Display receipt modal ─────────────────────────────────

    function displayReceipt(receipt) {
        var receiptDate   = new Date(receipt.issued_at);
        var formattedDate = receiptDate.toLocaleDateString('en-MY', {
            year: 'numeric', month: 'long', day: 'numeric'
        });
        $('#receiptDateDisplay').text(formattedDate);

        var paymentNames = {
            cash: 'Cash Payment', online_banking: 'Online Banking',
            'e-wallet': 'E-Wallet', credit_card: 'Credit Card', debit_card: 'Debit Card'
        };
        var customer    = receipt.customer;
        var company     = receipt.company;
        var trip        = receipt.trip;
        var customerAge = (customer && customer.age) || window.calculateAgeFromIC((customer && customer.ic) || '');

        var logoHtml = company && company.logo_url
            ? '<img src="' + company.logo_url + '" style="height:90px;max-width:220px;object-fit:contain;border-radius:8px;">'
            : '<div style="font-size:3rem;line-height:1;">🏢</div>';

        var paxParts = [];
        if (receipt.pax_adult > 0) paxParts.push('👨 ' + receipt.pax_adult + ' Adult');
        if (receipt.pax_child > 0) paxParts.push('👶 ' + receipt.pax_child + ' Child');
        if (receipt.pax_baby  > 0) paxParts.push('🍼 ' + receipt.pax_baby  + ' Baby');
        var paxSummary = paxParts.join(' | ') || '-';

        var tripHtml = trip
            ? '<div style="padding:1rem;border:2px solid var(--primary-color)22;border-radius:12px;background:linear-gradient(135deg,var(--primary-color)08,transparent);margin-bottom:1.25rem;">' +
              '<div style="font-weight:700;color:var(--primary-color);margin-bottom:0.75rem;font-size:0.9rem;">✈️ TRIP / PACKAGE DETAILS</div>' +
              '<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;font-size:0.85rem;">' +
              '<div><span style="color:var(--text-muted);">Package:</span> <strong>' + trip.location + '</strong></div>' +
              '<div><span style="color:var(--text-muted);">Destination:</span> <strong>' + trip.country + ' (' + trip.national_code + ')</strong></div>' +
              '<div><span style="color:var(--text-muted);">Departure:</span> <strong>' + formatDateMY(trip.date_from) + '</strong></div>' +
              '<div><span style="color:var(--text-muted);">Return:</span> <strong>' + formatDateMY(trip.date_to) + '</strong></div>' +
              '<div><span style="color:var(--text-muted);">Duration:</span> <strong>' + (trip.duration || '-') + ' Days</strong></div>' +
              '<div><span style="color:var(--text-muted);">Pax:</span> <strong>' + paxSummary + '</strong></div>' +
              '</div></div>'
            : '';

        var depositHtml = receipt.deposit_amount > 0
            ? '<div style="font-size:0.85rem;color:var(--text-muted);margin-top:0.5rem;">Deposit: RM ' +
              parseFloat(receipt.deposit_amount).toFixed(2) + ' | Balance: RM ' +
              (parseFloat(receipt.total_amount) - parseFloat(receipt.deposit_amount)).toFixed(2) + '</div>'
            : '';

        $('#receiptBody').html(
            '<div style="display:flex;justify-content:space-between;align-items:center;padding:1.25rem;background:var(--input-readonly-bg);border-radius:12px;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;">' +
              '<div style="display:flex;align-items:center;gap:1rem;">' +
                '<div style="width:220px;height:90px;display:flex;align-items:center;">' + logoHtml + '</div>' +
                '<div>' +
                  '<div style="font-size:1.1rem;font-weight:700;color:var(--text-primary);">' + (company ? company.name : '-') + '</div>' +
                  '<div style="font-size:0.8rem;color:var(--text-muted);">Reg: ' + (company ? company.registration_no : '-') + '</div>' +
                  '<div style="font-size:0.8rem;color:var(--text-secondary);">📍 ' + (company ? company.address : '-') + '</div>' +
                '</div>' +
              '</div>' +
              '<div style="text-align:right;">' +
                '<div style="font-size:1.5rem;font-weight:800;color:var(--primary-color);">' + receipt.receipt_no + '</div>' +
                '<div style="font-size:0.85rem;color:var(--text-muted);">' + formattedDate + '</div>' +
              '</div>' +
            '</div>' +
            tripHtml +
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">' +
              '<div style="padding:1rem;background:var(--input-readonly-bg);border-radius:10px;">' +
                '<div style="font-weight:700;color:var(--text-primary);margin-bottom:0.75rem;font-size:0.85rem;">👤 CUSTOMER DETAILS</div>' +
                '<div style="font-size:0.85rem;color:var(--text-secondary);line-height:2;">' +
                  '<div><strong>Name:</strong> ' + (customer ? customer.name : '-') + '</div>' +
                  '<div><strong>IC:</strong> ' + (customer ? customer.ic : '-') + '</div>' +
                  '<div><strong>Age:</strong> ' + (customerAge ? customerAge + ' years old' : '-') + '</div>' +
                  '<div><strong>Phone:</strong> ' + (customer ? customer.phone || '-' : '-') + '</div>' +
                  (customer && customer.passport_no ? '<div><strong>Passport:</strong> ' + customer.passport_no + '</div>' : '') +
                '</div>' +
              '</div>' +
              '<div style="padding:1rem;background:var(--input-readonly-bg);border-radius:10px;">' +
                '<div style="font-weight:700;color:var(--text-primary);margin-bottom:0.75rem;font-size:0.85rem;">💳 PAYMENT DETAILS</div>' +
                '<div style="font-size:0.85rem;color:var(--text-secondary);line-height:2;">' +
                  '<div><strong>Method:</strong> ' + (paymentNames[receipt.payment_method] || receipt.payment_method) + '</div>' +
                  '<div><strong>Date:</strong> ' + receiptDate.toLocaleDateString('en-MY') + '</div>' +
                  (receipt.deposit_amount > 0 ? '<div><strong>Deposit:</strong> RM ' + parseFloat(receipt.deposit_amount).toFixed(2) + '</div>' : '') +
                  (receipt.notes ? '<div><strong>Notes:</strong> ' + receipt.notes + '</div>' : '') +
                '</div>' +
              '</div>' +
            '</div>' +
            '<div style="background:linear-gradient(135deg,var(--success-color)15,transparent);border:2px solid var(--success-color)40;border-radius:12px;padding:1.25rem;text-align:center;">' +
              '<div style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.5rem;">TOTAL AMOUNT PAID</div>' +
              '<div style="font-size:2.5rem;font-weight:800;color:var(--success-color);">RM ' + parseFloat(receipt.total_amount).toFixed(2) + '</div>' +
              depositHtml +
            '</div>'
        );

        currentReceiptData = receipt;
        $('#receiptModal').addClass('show');
    }

    $('#closeReceiptModal').click(function() { $('#receiptModal').removeClass('show'); });

    $('#printReceiptBtn').click(function() {
        if (!currentReceiptData) { window.showNotification('No receipt data to print', 'error'); return; }
        generatePDFReceipt(currentReceiptData);
    });


    // ── Search ────────────────────────────────────────────────

    var receiptSearchTimeout = null;

    function performReceiptSearch() {
        var filters = {
            receipt_no:     $('#searchReceiptNo').val().trim(),
            customer_name:  $('#searchReceiptCustomer').val().trim(),
            date_from:      $('#searchDateFrom').val(),
            date_to:        $('#searchDateTo').val(),
            payment_method: $('#searchPaymentMethod').val(),
            amount_min:     $('#searchAmountMin').val(),
            amount_max:     $('#searchAmountMax').val()
        };
        Object.keys(filters).forEach(function(k) { if (!filters[k]) delete filters[k]; });
        window.loadReceiptsFromServer(filters);
    }

    $('#searchReceiptNo, #searchReceiptCustomer, #searchAmountMin, #searchAmountMax').on('input', function() {
        clearTimeout(receiptSearchTimeout);
        receiptSearchTimeout = setTimeout(performReceiptSearch, 500);
    });
    $('#searchDateFrom, #searchDateTo, #searchPaymentMethod').change(performReceiptSearch);
    $('#btnSearchReceipts').click(function() { clearTimeout(receiptSearchTimeout); performReceiptSearch(); });
    $('#btnResetReceiptSearch').click(function() {
        $('#searchReceiptNo, #searchReceiptCustomer, #searchDateFrom, #searchDateTo, #searchPaymentMethod, #searchAmountMin, #searchAmountMax').val('');
        window.loadReceiptsFromServer();
    });
 // ✅ Helper: Format date Malaysia (DD/M/YYYY)
    function formatDateMY(dateStr) {
        if (!dateStr) return '—';
        var d = new Date(dateStr);
        return d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear();
    }

    // ── PDF generation (unchanged) ────────────────────────────

    function generatePDFReceipt(receipt) {
        var logoPromise = new Promise(function(resolve) {
            if (!receipt.company || !receipt.company.logo_url) { resolve(null); return; }
            fetch(receipt.company.logo_url)
                .then(function(res) { return res.blob(); })
                .then(function(blob) {
                    var reader = new FileReader();
                    reader.onloadend = function() { resolve(reader.result); };
                    reader.onerror   = function() { resolve(null); };
                    reader.readAsDataURL(blob);
                })
                .catch(function() { resolve(null); });
        });

        function cropLogoRounded(dataUrl, size, radius) {
            return new Promise(function(resolve) {
                if (!dataUrl) { resolve(null); return; }
                var img = new Image();
                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    canvas.width = size; canvas.height = size;
                    var ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, size, size);
                    ctx.beginPath();
                    ctx.moveTo(radius, 0); ctx.lineTo(size - radius, 0);
                    ctx.quadraticCurveTo(size, 0, size, radius);
                    ctx.lineTo(size, size - radius);
                    ctx.quadraticCurveTo(size, size, size - radius, size);
                    ctx.lineTo(radius, size);
                    ctx.quadraticCurveTo(0, size, 0, size - radius);
                    ctx.lineTo(0, radius);
                    ctx.quadraticCurveTo(0, 0, radius, 0);
                    ctx.closePath(); ctx.clip();
                    var minDim = Math.min(img.width, img.height);
                    ctx.drawImage(img, (img.width-minDim)/2, (img.height-minDim)/2, minDim, minDim, 0, 0, size, size);
                    resolve(canvas.toDataURL('image/png'));
                };
                img.onerror = function() { resolve(null); };
                img.src = dataUrl;
            });
        }

        logoPromise
            .then(function(rawLogo) { return cropLogoRounded(rawLogo, 240, 36); })
            .then(function(logoDataUrl) {
                var jsPDF = window.jspdf.jsPDF;
                var doc   = new jsPDF({ unit: 'mm', format: 'a4' });
                var pageW = 210, margin = 14, contentW = pageW - margin * 2;
                var receiptDate = new Date(receipt.issued_at);
                var dateStr = receiptDate.toLocaleDateString('en-MY', { day: '2-digit', month: 'long', year: 'numeric' });
                var paymentNames = { cash:'Cash', online_banking:'Online Banking', 'e-wallet':'E-Wallet', credit_card:'Credit Card', debit_card:'Debit Card' };
                var customer = receipt.customer, company = receipt.company, trip = receipt.trip;
                var customerAge = (customer && customer.age) || window.calculateAgeFromIC((customer && customer.ic) || '');
                var C = { primary:[30,64,175], accent:[99,102,241], light:[238,242,255], success:[22,163,74], successBg:[240,253,244], white:[255,255,255], dark:[15,23,42], mid:[100,116,139], border:[226,232,240], bg:[248,250,252] };
                var fc = function(arr, t) { t=t||'text'; if(t==='fill') doc.setFillColor.apply(doc,arr); else if(t==='draw') doc.setDrawColor.apply(doc,arr); else doc.setTextColor.apply(doc,arr); };

                fc(C.primary,'fill'); doc.rect(0,0,pageW,3,'F');
                fc(C.bg,'fill'); doc.rect(0,3,pageW,47,'F');
                var logoX=margin, logoY=9, logoSz=31;
                if(logoDataUrl) { fc(C.border,'fill'); doc.roundedRect(logoX-.5,logoY-.5,logoSz+1,logoSz+1,4.5,4.5,'F'); doc.addImage(logoDataUrl,'PNG',logoX,logoY,logoSz,logoSz); }
                else { fc(C.light,'fill'); doc.roundedRect(logoX,logoY,logoSz,logoSz,4,4,'F'); fc(C.accent); doc.setFontSize(16); doc.setFont(undefined,'bold'); doc.text((company?company.name:'CO').substring(0,2).toUpperCase(), logoX+logoSz/2, logoY+logoSz/2+3, {align:'center'}); }
                var infoX=logoX+logoSz+7, infoW=pageW-infoX-50;
                fc(C.dark); doc.setFontSize(14); doc.setFont(undefined,'bold'); doc.text(company?company.name:'TRAVEL COMPANY', infoX, 18);
                fc(C.mid); doc.setFontSize(7.5); doc.setFont(undefined,'normal');
                if(company&&company.registration_no) doc.text('SSM No: '+company.registration_no, infoX, 24);
                if(company&&company.address) { var lines=doc.splitTextToSize(company.address,infoW); doc.text(lines[0],infoX,29.5); if(lines[1]) doc.text(lines[1],infoX,34); }
                var bW=38, bX=pageW-margin-bW;
                fc(C.primary,'fill'); doc.roundedRect(bX,9,bW,9,2,2,'F'); fc(C.white); doc.setFontSize(7); doc.setFont(undefined,'bold'); doc.text('OFFICIAL RECEIPT',bX+bW/2,14.5,{align:'center'});
                fc(C.dark); doc.setFontSize(9.5); doc.setFont(undefined,'bold'); doc.text(receipt.receipt_no,bX+bW,26,{align:'right'});
                fc(C.mid); doc.setFontSize(7.5); doc.setFont(undefined,'normal'); doc.text(dateStr,bX+bW,31.5,{align:'right'});
                fc(C.border,'draw'); doc.setLineWidth(0.25); doc.line(margin,51,pageW-margin,51);

                var yPos=59, cW=(contentW-5)/2, c1X=margin, c2X=margin+cW+5;
                var secTitle=function(label,x,y,w){ fc(C.light,'fill'); doc.roundedRect(x,y-4.5,w,7,1.5,1.5,'F'); fc(C.primary); doc.setFontSize(6.5); doc.setFont(undefined,'bold'); doc.text(label.toUpperCase(),x+3,y+.5); };
                var dRow=function(lbl,val,x,y){ fc(C.mid); doc.setFontSize(7); doc.setFont(undefined,'normal'); doc.text(lbl,x,y); fc(C.dark); doc.setFontSize(8.5); doc.setFont(undefined,'bold'); doc.text(String(val||'—'),x,y+5); return y+11; };

                secTitle('Trip / Package',c1X,yPos,cW);
                var y1=yPos+9;
                if(trip){ 
                    y1=dRow('Destination',trip.location||'—',c1X,y1); 
                    y1=dRow('Country',(trip.country||'—')+' ('+(trip.national_code||'')+')',c1X,y1); 
                    y1=dRow('Travel Dates', formatDateMY(trip.date_from) + ' – ' + formatDateMY(trip.date_to), c1X, y1); 
                    y1=dRow('Duration',(trip.duration||'—')+' Days',c1X,y1); 
                }
                else { fc(C.mid); doc.setFontSize(8); doc.text('No trip assigned',c1X,y1+5); y1+=16; }

                secTitle('Customer Info',c2X,yPos,cW);
                var y2=yPos+9;
                if(customer){ y2=dRow('Full Name',customer.name||'—',c2X,y2); y2=dRow('IC / ID',customer.ic||customer.passport_no||'—',c2X,y2); y2=dRow('Age',customerAge?customerAge+' years old':'—',c2X,y2); if(customer.phone) y2=dRow('Phone',customer.phone,c2X,y2); if(customer.state) y2=dRow('State',customer.state,c2X,y2); }

                yPos=Math.max(y1,y2)+7; fc(C.border,'draw'); doc.setLineWidth(0.2); doc.line(margin,yPos-3,pageW-margin,yPos-3);
                secTitle('Payment Details',margin,yPos,contentW); yPos+=10;

                var paxStr=buildPaxString(receipt), boxW=(contentW-9)/4, boxH=17;
                var pBoxes=[{label:'Payment Method',value:paymentNames[receipt.payment_method]||receipt.payment_method},{label:'Payment Date',value:receiptDate.toLocaleDateString('en-MY')},{label:'Pax',value:paxStr},{label:'Deposit',value:receipt.deposit_amount>0?'RM '+parseFloat(receipt.deposit_amount).toFixed(2):'No Deposit'}];
                pBoxes.forEach(function(box,i){ var bx=margin+i*(boxW+3); fc(C.white,'fill'); fc(C.border,'draw'); doc.setLineWidth(0.3); doc.roundedRect(bx,yPos,boxW,boxH,2,2,'FD'); fc(C.mid); doc.setFontSize(6.5); doc.setFont(undefined,'normal'); doc.text(box.label,bx+3,yPos+5.5); fc(C.dark); doc.setFontSize(8); doc.setFont(undefined,'bold'); doc.text(box.value,bx+3,yPos+13); });

                yPos+=boxH+9;
                var totalH=28; fc(C.successBg,'fill'); fc(C.success,'draw'); doc.setLineWidth(0.5); doc.roundedRect(margin,yPos,contentW,totalH,4,4,'FD'); fc(C.success,'fill'); doc.roundedRect(margin,yPos,3,totalH,2,2,'F'); fc(C.mid); doc.setFontSize(7.5); doc.setFont(undefined,'normal'); doc.text('TOTAL AMOUNT PAID',pageW/2,yPos+9,{align:'center'}); fc(C.success); doc.setFontSize(22); doc.setFont(undefined,'bold'); doc.text('RM '+parseFloat(receipt.total_amount).toFixed(2),pageW/2,yPos+21,{align:'center'});

                if(receipt.deposit_amount>0){ yPos+=totalH+5; var bal=parseFloat(receipt.total_amount)-parseFloat(receipt.deposit_amount); fc(C.mid); doc.setFontSize(7.5); doc.setFont(undefined,'normal'); doc.text('Deposit Paid: RM '+parseFloat(receipt.deposit_amount).toFixed(2)+'   ·   Balance Due: RM '+bal.toFixed(2),pageW/2,yPos,{align:'center'}); } else { yPos+=totalH; }
                // ── Travelers Table ───────────────────────────────────────────
var travelers = (receipt.booking && receipt.booking.traveler_details) || [];
if (travelers.length > 0) {
    yPos += 8;

    // Section title
    secTitle('Senarai Pelancong / Travelers', margin, yPos, contentW);
    yPos += 9;

    // Table header
    var cols = [
        { label: '#',           w: 8  },
        { label: 'Nama / Name', w: 52 },
        { label: 'No. IC',      w: 38 },
        { label: 'Jantina',     w: 22 },
        { label: 'Jenis / Type',w: 26 },
        { label: 'No. Pasport', w: 36 },
    ];
    var rowH  = 8;
    var headH = 9;

    // Header background
    fc(C.primary, 'fill');
    doc.roundedRect(margin, yPos, contentW, headH, 2, 2, 'F');

    // Header text
    fc(C.white);
    doc.setFontSize(6.5);
    doc.setFont(undefined, 'bold');
    var hx = margin + 2;
    cols.forEach(function (col) {
        doc.text(col.label, hx, yPos + 6);
        hx += col.w;
    });

    yPos += headH;

    // Rows
    travelers.forEach(function (t, idx) {
        // Check page overflow — tambah page baru kalau perlu
        if (yPos + rowH > 270) {
            doc.addPage();
            yPos = 20;
        }

        // Alternating row background
        if (idx % 2 === 0) {
            fc(C.light, 'fill');
            doc.rect(margin, yPos, contentW, rowH, 'F');
        }

        // Row border bottom
        fc(C.border, 'draw');
        doc.setLineWidth(0.15);
        doc.line(margin, yPos + rowH, margin + contentW, yPos + rowH);

        // Row data
        fc(C.dark);
        doc.setFontSize(7);
        doc.setFont(undefined, 'normal');

        var typeLabel = t.type === 'adult' ? 'Dewasa'
                      : t.type === 'child' ? 'Kanak-kanak'
                      : t.type === 'baby'  ? 'Bayi' : (t.type || '-');

        var genderLabel = t.gender === 'male'   ? 'Lelaki'
                        : t.gender === 'female' ? 'Perempuan'
                        : (t.gender || '-');

        var rowData = [
            String(idx + 1),
            t.name        || '-',
            t.ic          || '-',
            genderLabel,
            typeLabel,
            t.passport_no || '-',
        ];

        var rx = margin + 2;
        cols.forEach(function (col, ci) {
            // Bold nama sahaja
            doc.setFont(undefined, ci === 1 ? 'bold' : 'normal');
            // Truncate text kalau terlalu panjang
            var maxChars = Math.floor(col.w / 2.1);
            var txt = String(rowData[ci] || '-');
            if (txt.length > maxChars) txt = txt.substring(0, maxChars - 1) + '…';
            doc.text(txt, rx, yPos + 5.5);
            rx += col.w;
        });

        // Index number — bold
        fc(C.primary);
        doc.setFontSize(6.5);
        doc.setFont(undefined, 'bold');
        doc.text(String(idx + 1), margin + 2, yPos + 5.5);

        yPos += rowH;
    });

    // Table outer border
    fc(C.border, 'draw');
    doc.setLineWidth(0.3);
    doc.rect(margin, yPos - (travelers.length * rowH) - headH, contentW, (travelers.length * rowH) + headH);

    // Summary below table
    yPos += 4;
    fc(C.mid);
    doc.setFontSize(7);
    doc.setFont(undefined, 'italic');
    doc.text('Jumlah pelancong: ' + travelers.length + ' orang', margin, yPos);
    yPos += 4;
}
                if(receipt.notes){ yPos+=7; fc(C.light,'fill'); doc.roundedRect(margin,yPos,contentW,11,2,2,'F'); fc(C.mid); doc.setFontSize(7.5); doc.setFont(undefined,'normal'); doc.text('Note: '+receipt.notes,margin+4,yPos+7); }

                fc(C.border,'draw'); doc.setLineWidth(0.2); doc.line(margin,278,pageW-margin,278);
                fc(C.mid); doc.setFontSize(7); doc.setFont(undefined,'italic'); doc.text('Thank you for choosing '+(company?company.name:'us')+'. This is a computer-generated receipt.',pageW/2,283,{align:'center'});
                fc(C.primary,'fill'); doc.rect(0,291,pageW,6,'F');
                doc.save('Receipt_'+receipt.receipt_no+'.pdf');
                window.showNotification('Receipt PDF downloaded! 📄', 'success');
            });
    }

    function buildPaxString(receipt) {
        var parts = [];
        if (receipt.pax_adult > 0) parts.push(receipt.pax_adult + ' Adult');
        if (receipt.pax_child > 0) parts.push(receipt.pax_child + ' Child');
        if (receipt.pax_baby  > 0) parts.push(receipt.pax_baby  + ' Baby');
        return parts.length > 0 ? parts.join(', ') : '—';
    }

});