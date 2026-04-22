// ============================================================
// public/js/modules/trips.js
// Trips Module — CRUD, grid render, photo gallery, itinerary
// ✅ Edit Trip → page baru /dashboard/trips/{id}/edit
// ✅ Manage Customers → page baru /dashboard/trips/{id}/manage
// ✅ Itinerary: day_number editable, day_date, img upload
// ✅ Format tarikh: DD/MM/YYYY
// ============================================================

$(function () {

    let itineraryDayCount = 0;
    let tripImages  = [];
    let currentImgI = 0;

    // ── Format tarikh YYYY-MM-DD → DD/MM/YYYY ────────────────
    function formatDate(dateStr) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }


    // ── Load ─────────────────────────────────────────────────

    window.loadTrips = function(filters) {
        filters = filters || {};
        $('#tripsGrid').html(`
            <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--primary-color);">
                <div style="font-size:2rem;margin-bottom:0.5rem;">✈️</div>
                <div>Loading trips...</div>
            </div>
        `);
        $.ajax({
            url: '/trips', type: 'GET', data: filters,
            success: function(res) {
                if (res.success) {
                    allTrips = res.data;
                    window.renderTripsGrid(allTrips);
                }
            },
            error: () => window.showNotification('Failed to load trips', 'error')
        });
    };


    // ── Action buttons ────────────────────────────────────────

    function buildTripActions(trip) {
        const id     = trip.id;
        const count  = parseInt(trip.booking_count || 0);
        const locked = count > 0;

        if (locked) {
            return `
                <div class="trip-action-bar" data-trip-id="${id}"
                     style="display:flex;flex-direction:column;gap:0.4rem;align-items:flex-end;">
                    <div style="display:flex;align-items:center;gap:0.3rem;background:#fff3cd;
                                border:1px solid #ffc107;border-radius:20px;padding:3px 10px;
                                font-size:0.7rem;font-weight:700;color:#856404;">
                        🔒 <span class="trip-pax-count">${count}</span>&nbsp;Customer${count > 1 ? 's' : ''}
                    </div>
                    <div style="display:flex;gap:0.4rem;">
                        <button class="btn btn-primary view-trip-detail-btn"
                            data-id="${id}" style="font-size:0.74rem;padding:0.32rem 0.7rem;">👁️ View</button>
                        <button class="btn btn-info manage-trip-customers-btn"
                            data-id="${id}" style="font-size:0.74rem;padding:0.32rem 0.7rem;">👥 Manage</button>
                    </div>
                </div>`;
        }

        return `
            <div class="trip-action-bar" data-trip-id="${id}"
                 style="display:flex;gap:0.4rem;flex-wrap:wrap;justify-content:flex-end;">
                <button class="btn btn-primary view-trip-detail-btn"
                    data-id="${id}" style="font-size:0.74rem;padding:0.32rem 0.7rem;">👁️ View</button>
                <button class="btn btn-warning edit-trip-btn"
                    data-id="${id}" style="font-size:0.74rem;padding:0.32rem 0.7rem;">✏️ Edit</button>
                <button class="btn btn-info manage-trip-customers-btn"
                    data-id="${id}" style="font-size:0.74rem;padding:0.32rem 0.7rem;">👥</button>
                <button class="btn btn-danger delete-trip-btn"
                    data-id="${id}" style="font-size:0.74rem;padding:0.32rem 0.65rem;">🗑️</button>
            </div>`;
    }

    function refreshTripCard(tripId, newBookingsCount) {
        const trip = allTrips.find(t => t.id == tripId);
        if (trip) trip.booking_count = newBookingsCount;

        const $card = $('.trip-card[data-id="' + tripId + '"]');
        if (!$card.length) return;
        const $bar = $card.find('.trip-action-bar');
        if (!$bar.length) return;

        $bar.stop(true).animate({ opacity: 0 }, 200, function() {
            const tripData = allTrips.find(t => t.id == tripId);
            if (!tripData) return;
            $bar.parent().html(buildTripActions(tripData));
            $card.find('.trip-action-bar').hide().css('opacity', 1).fadeIn(300);
            if (newBookingsCount === 0) {
                $card.css({ 'border-color': '#4caf50', 'box-shadow': '0 0 0 3px #4caf5033' });
                setTimeout(() => $card.css({ 'border-color': '', 'box-shadow': '' }), 1500);
            }
        });
    }

    window.renderTripsGrid = function(trips) {
        const container = $('#tripsGrid');
        container.empty();
        $('#tripsCount').text('Showing ' + trips.length + ' trip(s)');

        if (trips.length === 0) {
            container.html(`
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);">
                    <div style="font-size:4rem;margin-bottom:1rem;">✈️</div>
                    <div style="font-size:1.2rem;font-weight:600;">No trips found</div>
                </div>
            `);
            return;
        }

        trips.forEach(function(trip) {
            const mainPic    = trip.pic1_url || trip.pic2_url || trip.pic3_url;
            const imgHtml    = mainPic
                ? `<img src="${mainPic}" alt="trip" style="width:100%;height:200px;object-fit:cover;">`
                : `<div style="width:100%;height:200px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:4rem;">✈️</div>`;
            const isUpcoming  = new Date(trip.date_from) > new Date();
            const statusColor = isUpcoming ? '#4caf50' : '#ff9800';
            const statusText  = isUpcoming ? 'Upcoming' : 'Past';
            const picCount    = [trip.pic1_url, trip.pic2_url, trip.pic3_url].filter(Boolean).length;
            const picBadge    = picCount > 1
                ? `<div style="position:absolute;bottom:10px;left:10px;background:rgba(0,0,0,0.6);color:white;font-size:0.75rem;padding:3px 8px;border-radius:20px;">📸 ${picCount} photos</div>` : '';
            const priceFrom = parseFloat(trip.price_adult || 0).toFixed(0);
            const itinArr = Array.isArray(trip.itineraries)
    ? trip.itineraries
    : Object.values(trip.itineraries || {});
const itinBadge = itinArr.length > 0
    ? `<div style="display:inline-flex;align-items:center;gap:0.3rem;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:2px 9px;border-radius:20px;font-size:0.7rem;font-weight:700;margin-top:0.35rem;">
           🗓️ ${itinArr.length} hari itinerary
       </div>`
    : '';

            const card = $(`
                <div class="trip-card" data-id="${trip.id}"
                    style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:16px;overflow:hidden;transition:transform 0.2s,box-shadow 0.2s,border-color 0.5s;">
                    <div style="position:relative;">
                        ${imgHtml}
                        <div style="position:absolute;top:10px;right:10px;background:${statusColor};color:white;font-size:0.7rem;font-weight:700;padding:4px 10px;border-radius:20px;">${statusText}</div>
                        <div style="position:absolute;top:10px;left:10px;background:rgba(0,0,0,0.6);color:white;font-size:0.75rem;padding:4px 10px;border-radius:20px;">🌍 ${trip.national_code || ''}</div>
                        ${picBadge}
                    </div>
                    <div style="padding:1.25rem;">
                        <div style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:0.25rem;">${trip.location || ''}</div>
                        <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.5rem;">🏢 ${trip.company ? trip.company.name : '-'}</div>
                        <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.5rem;">📍 ${trip.country || ''}${trip.state ? ', ' + trip.state : ''} &nbsp;•&nbsp; ⏱️ ${trip.duration || '-'} days</div>
                        <div style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:0.4rem;">📅 ${formatDate(trip.date_from)} → ${formatDate(trip.date_to)}</div>
${itinBadge}
                        <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border-color);padding-top:0.75rem;margin-top:0.5rem;">
                            <div>
                                <div style="font-size:0.7rem;color:var(--text-muted);">From</div>
                                <div style="font-size:1.1rem;font-weight:700;color:var(--success-color);">RM ${priceFrom}</div>
                            </div>
                            ${buildTripActions(trip)}
                        </div>
                    </div>
                </div>
            `);

            card.on('mouseenter', function() {
                $(this).css({ transform: 'translateY(-4px)', 'box-shadow': '0 8px 30px rgba(0,0,0,0.12)' });
            }).on('mouseleave', function() {
                $(this).css({ transform: '', 'box-shadow': '' });
            });

            container.append(card);
        });
    };


    // ── Edit Trip → page baru ─────────────────────────────────

    $(document).on('click', '.edit-trip-btn', function(e) {
        e.stopPropagation();
        const tripId = $(this).data('id');
        const trip   = allTrips.find(t => t.id == tripId);
        if (trip && parseInt(trip.booking_count || 0) > 0) {
            window.showNotification('Trip ini masih ada customer. Buang semua customer dahulu untuk edit.', 'error');
            return;
        }
        window.location.href = '/dashboard/trips/' + tripId + '/edit';
    });


    // ── Manage Customers → page baru ─────────────────────────

    $(document).on('click', '.manage-trip-customers-btn', function(e) {
        e.stopPropagation();
        const tripId = $(this).data('id');
        window.location.href = '/dashboard/trips/' + tripId + '/manage';
    });


    // ── Trip detail modal (View sahaja) ───────────────────────

    $(document).on('click', '.view-trip-detail-btn', function(e) {
        e.preventDefault(); e.stopPropagation();
        const tripId = $(this).data('id');
        const trip   = allTrips.find(t => t.id == tripId);
        if (!trip) { window.showNotification('Trip data not found', 'error'); return; }

        tripImages  = [trip.pic1_url, trip.pic2_url, trip.pic3_url].filter(Boolean);
        currentImgI = 0;

        let galleryHtml = '';
        if (tripImages.length > 0) {
            const thumbsHtml = tripImages.map((p, i) => `
                <img src="${p}" onclick="switchTripImage('${p}')"
                    style="width:80px;height:60px;object-fit:cover;border-radius:8px;cursor:pointer;
                    border:3px solid ${i === 0 ? 'var(--primary-color)' : 'transparent'};
                    transition:border-color 0.2s,transform 0.2s;opacity:${i === 0 ? '1' : '0.7'};"
                    onmouseover="this.style.transform='scale(1.05)'"
                    onmouseout="this.style.transform='scale(1)'" class="trip-thumb">
            `).join('');
            const navBtns = tripImages.length > 1 ? `
                <button onclick="prevTripImage()" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.6);color:white;border:none;border-radius:50%;width:40px;height:40px;font-size:1.2rem;cursor:pointer;z-index:2;">‹</button>
                <button onclick="nextTripImage()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.6);color:white;border:none;border-radius:50%;width:40px;height:40px;font-size:1.2rem;cursor:pointer;z-index:2;">›</button>
                <div style="position:absolute;bottom:12px;right:12px;background:rgba(0,0,0,0.6);color:white;font-size:0.8rem;padding:4px 10px;border-radius:20px;"><span id="tripImageCounter">1</span> / ${tripImages.length}</div>
            ` : '';
            const thumbRow = tripImages.length > 1
                ? `<div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;justify-content:center;">${thumbsHtml}</div>`
                : '<div style="margin-bottom:1.5rem;"></div>';
            galleryHtml = `
                <div style="position:relative;margin-bottom:0.75rem;border-radius:14px;overflow:hidden;background:#000;">
                    <img id="tripMainImage" src="${tripImages[0]}" style="width:100%;height:320px;object-fit:cover;display:block;transition:opacity 0.3s;">
                    ${navBtns}
                </div>${thumbRow}`;
        } else {
            galleryHtml = `<div style="width:100%;height:220px;border-radius:14px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:5rem;margin-bottom:1.5rem;">✈️</div>`;
        }

        let itineraryHtml = '';
const itinArray = Array.isArray(trip.itineraries)
    ? trip.itineraries
    : Object.values(trip.itineraries || {});

if (itinArray.length > 0) {
    const itRows = itinArray.sort((a,b) => (a.day_number||0) - (b.day_number||0)).map(it => {
                const dateRow = it.day_date
                    ? `<div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:0.2rem;">📅 ${formatDate(it.day_date)}</div>` : '';
                const timeRow = (it.time_from || it.time_to)
                    ? `<div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.25rem;">🕐 ${it.time_from || ''}${it.time_to ? ' → ' + it.time_to : ''}</div>` : '';
                const descRow = it.description
                    ? `<div style="font-size:0.85rem;color:var(--text-secondary);line-height:1.5;">${it.description}</div>` : '';
                const imgRow  = it.img_url
                    ? `<img src="${it.img_url}" style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;margin-top:.5rem;">` : '';
                return `<div style="display:flex;gap:1rem;padding:0.85rem 1rem;border-left:4px solid #667eea;margin-bottom:0.75rem;background:var(--input-readonly-bg);border-radius:0 10px 10px 0;">
                    <div style="min-width:65px;font-weight:700;color:#667eea;font-size:0.9rem;">Day ${it.day_number}</div>
                    <div style="flex:1;">
                        <div style="font-weight:600;color:var(--text-primary);margin-bottom:0.25rem;">${it.title || '-'}</div>
                        ${dateRow}${timeRow}${descRow}${imgRow}
                    </div>
                </div>`;
            }).join('');
            itineraryHtml = `<div style="margin-top:1.5rem;"><h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;">📅 Itinerary (${itinArray.length} days)</h3>${itRows}</div>`;
        }

        const isUpcoming  = new Date(trip.date_from) > new Date();
        const statusColor = isUpcoming ? '#4caf50' : '#ff9800';
        const statusText  = isUpcoming ? '🟢 Upcoming' : '🟡 Past Trip';
        const count       = parseInt(trip.booking_count || 0);

        $('#tripDetailContent').html(`
            ${galleryHtml}
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;">
                <div>
                    <h2 style="font-size:1.4rem;font-weight:700;color:var(--text-primary);margin:0 0 0.25rem;">${trip.location || ''}</h2>
                    <div style="font-size:0.9rem;color:var(--text-muted);">🏢 ${trip.company ? trip.company.name : '-'} &nbsp;•&nbsp; 🌍 ${trip.national_code || ''}</div>
                </div>
                <span style="background:${statusColor}22;color:${statusColor};border:1px solid ${statusColor}44;padding:0.35rem 0.9rem;border-radius:20px;font-size:0.85rem;font-weight:600;white-space:nowrap;">${statusText}</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:0.75rem;margin-bottom:1.5rem;padding:1rem;background:var(--input-readonly-bg);border-radius:12px;">
                <div style="text-align:center;padding:0.5rem;"><div style="font-size:1.5rem;margin-bottom:0.25rem;">📍</div><div style="font-size:0.75rem;color:var(--text-muted);">Destination</div><div style="font-weight:700;color:var(--text-primary);font-size:0.9rem;">${trip.country || ''}${trip.state ? ', ' + trip.state : ''}</div></div>
                <div style="text-align:center;padding:0.5rem;"><div style="font-size:1.5rem;margin-bottom:0.25rem;">📅</div><div style="font-size:0.75rem;color:var(--text-muted);">Departure</div><div style="font-weight:700;color:var(--text-primary);font-size:0.9rem;">${formatDate(trip.date_from)}</div></div>
                <div style="text-align:center;padding:0.5rem;"><div style="font-size:1.5rem;margin-bottom:0.25rem;">🏁</div><div style="font-size:0.75rem;color:var(--text-muted);">Return</div><div style="font-weight:700;color:var(--text-primary);font-size:0.9rem;">${formatDate(trip.date_to)}</div></div>
                <div style="text-align:center;padding:0.5rem;"><div style="font-size:1.5rem;margin-bottom:0.25rem;">⏱️</div><div style="font-size:0.75rem;color:var(--text-muted);">Duration</div><div style="font-weight:700;color:var(--text-primary);font-size:0.9rem;">${trip.duration || '-'} Days</div></div>
            </div>
            <div style="padding:1.25rem;border:2px solid var(--border-color);border-radius:12px;margin-bottom:1.25rem;">
                <div style="font-size:0.9rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;">💰 Pricing (RM)</div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;text-align:center;">
                    <div><div style="font-size:0.75rem;color:var(--text-muted);">👨 Adult</div><div style="font-size:1.2rem;font-weight:700;color:var(--success-color);">RM ${parseFloat(trip.price_adult||0).toFixed(0)}</div></div>
                    <div><div style="font-size:0.75rem;color:var(--text-muted);">👶 Child</div><div style="font-size:1.2rem;font-weight:700;color:#2196f3;">RM ${parseFloat(trip.price_child||0).toFixed(0)}</div></div>
                    <div><div style="font-size:0.75rem;color:var(--text-muted);">🍼 Baby</div><div style="font-size:1.2rem;font-weight:700;color:#ff9800;">RM ${parseFloat(trip.price_baby||0).toFixed(0)}</div></div>
                    <div><div style="font-size:0.75rem;color:var(--text-muted);">💳 Deposit</div><div style="font-size:1.2rem;font-weight:700;color:#9c27b0;">RM ${parseFloat(trip.deposit||0).toFixed(0)}</div></div>
                </div>
            </div>
            ${count > 0 ? `<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#856404;">🔒 Trip ini mempunyai <strong>${count} customer</strong>. Guna <strong>👥 Manage</strong> untuk urus customer.</div>` : ''}
            ${itineraryHtml}
            <div style="display:flex;justify-content:center;gap:.75rem;margin-top:1.5rem;flex-wrap:wrap;">
                <button class="btn btn-success btn-lg book-trip-btn"
                    data-id="${trip.id}" data-company-id="${trip.company_id || ''}"
                    style="padding:0.75rem 2rem;font-size:1rem;">📝 Book This Trip</button>
                ${count === 0 ? `<button class="btn btn-warning btn-lg" onclick="window.location.href='/dashboard/trips/${trip.id}/edit'" style="padding:0.75rem 2rem;font-size:1rem;">✏️ Edit Trip</button>` : ''}
                <button class="btn btn-info btn-lg" onclick="window.location.href='/dashboard/trips/${trip.id}/manage'" style="padding:0.75rem 2rem;font-size:1rem;">👥 Manage</button>
            </div>
        `);

        $('#tripDetailModal').addClass('show');
    });

    $('#closeTripDetailModal').click(() => $('#tripDetailModal').removeClass('show'));


    // ── Gallery controls ──────────────────────────────────────

    window.switchTripImage = function(src) {
        currentImgI = tripImages.indexOf(src);
        $('#tripMainImage').css('opacity', 0);
        setTimeout(function() {
            $('#tripMainImage').attr('src', src).css('opacity', 1);
            $('#tripImageCounter').text(currentImgI + 1);
            $('.trip-thumb').each(function(i) {
                $(this).css({ 'border-color': i === currentImgI ? 'var(--primary-color)' : 'transparent', 'opacity': i === currentImgI ? '1' : '0.7' });
            });
        }, 200);
    };
    window.prevTripImage = function() {
        if (tripImages.length <= 1) return;
        currentImgI = (currentImgI - 1 + tripImages.length) % tripImages.length;
        window.switchTripImage(tripImages[currentImgI]);
    };
    window.nextTripImage = function() {
        if (tripImages.length <= 1) return;
        currentImgI = (currentImgI + 1) % tripImages.length;
        window.switchTripImage(tripImages[currentImgI]);
    };


    // ── Book This Trip ────────────────────────────────────────

    $(document).on('click', '.book-trip-btn', function() {
        const tripId    = $(this).data('id');
        const companyId = $(this).data('company-id');
        $('#tripDetailModal').removeClass('show');
        if (companyId) sessionStorage.setItem('prefill_booking_company', companyId);
        if (tripId)    sessionStorage.setItem('prefill_booking_trip', tripId);
        window.location.href = '/dashboard/bookings/create';
    });


    // ── Delete trip ───────────────────────────────────────────

    $(document).on('click', '.delete-trip-btn', function(e) {
        e.stopPropagation();
        const tripId = $(this).data('id');
        const trip   = allTrips.find(t => t.id == tripId);
        if (trip && parseInt(trip.booking_count || 0) > 0) {
            window.showNotification('Trip ini masih ada customer. Buang semua customer dahulu.', 'error');
            return;
        }
        if (!confirm('Delete this trip? This cannot be undone.')) return;
        $.ajax({
            url: '/trips/' + tripId, type: 'DELETE',
            success: function(res) {
                if (res.success) {
                    window.showNotification('Trip deleted ✅', 'success');
                    window.loadTrips();
                }
            },
            error: () => window.showNotification('Failed to delete trip', 'error')
        });
    });


    // ── Add trip form — photo preview ─────────────────────────

    window.previewTripPic = function(num, input) {
        const file = input.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) { window.showNotification(`Photo ${num} must be < 5MB`, 'error'); input.value = ''; return; }
        const reader = new FileReader();
        reader.onload = function(e) {
            $(`#pic${num}Preview`).attr('src', e.target.result).show();
            $(`#pic${num}Placeholder`).hide();
            $(`#removePic${num}`).show();
            $(`#pic${num}Box`).css('border-color', 'var(--success-color)');
        };
        reader.readAsDataURL(file);
    };

    window.removeTripPic = function(num) {
        $(`#pic${num}Preview`).hide().attr('src', '');
        $(`#pic${num}Placeholder`).show();
        $(`#removePic${num}`).hide();
        $(`#pic${num}Box`).css('border-color', 'var(--border-color)');
        $(`#tripPic${num}`).val('');
    };

    window.resetTripForm = function() {
        $('#tripForm')[0].reset();
        [1, 2, 3].forEach(n => window.removeTripPic(n));
        itineraryDayCount = 0;
        $('#itineraryContainer').html(`<div id="itinHint" style="text-align:center;padding:1.75rem;color:var(--text-muted);background:var(--input-readonly-bg);border-radius:10px;font-size:.88rem;">📅 Klik <strong>"➕ Tambah Hari"</strong> untuk mula membina itinerari</div>`);
    };


    // ── Add trip — itinerary builder ──────────────────────────

    $('#addItineraryDayBtn').click(function() {
        itineraryDayCount++;
        const d = itineraryDayCount;
        $('#itinHint').remove();
        if (d === 1) $('#itineraryContainer').empty();

        $('#itineraryContainer').append(`
            <div class="itinerary-day-block" data-day="${d}"
                 style="border:1px solid var(--border-color);border-radius:12px;margin-bottom:1rem;overflow:hidden;">
                <div style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;padding:.75rem 1.25rem;display:flex;align-items:center;gap:.75rem;">
                    <span style="background:rgba(255,255,255,.25);font-size:.78rem;font-weight:800;padding:3px 12px;border-radius:20px;white-space:nowrap;flex-shrink:0;">Hari ${d}</span>
                    <input name="itineraries[${d}][title]" type="text"
                           placeholder="Tajuk aktiviti hari ini..." class="form-input"
                           style="flex:1;font-size:.88rem;padding:.4rem .85rem;background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff;">
                    <button type="button" onclick="removeItineraryDay(${d})"
                            style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.35);color:#fff;border-radius:8px;padding:5px 14px;cursor:pointer;font-size:.78rem;flex-shrink:0;">✖ Buang</button>
                </div>
                <div style="padding:1rem 1.25rem;">
                    <div style="display:flex;gap:1rem;margin-bottom:.85rem;flex-wrap:wrap;">
                        <div>
                            <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">📆 Hari Ke</label>
                            <input type="number" name="itineraries[${d}][day_number]" value="${d}" min="1"
                                   class="form-input" style="font-size:.9rem;padding:.5rem .75rem;width:90px;">
                        </div>
                        <div>
                            <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">📅 Tarikh</label>
                            <input type="date" name="itineraries[${d}][day_date]"
                                   class="form-input" style="font-size:.9rem;padding:.5rem .75rem;width:170px;">
                        </div>
                        <div>
                            <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">🕐 Masa Mula</label>
                            <input type="time" name="itineraries[${d}][time_from]"
                                   class="form-input" style="font-size:.9rem;padding:.5rem .75rem;width:150px;">
                        </div>
                        <div>
                            <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">🕐 Masa Tamat</label>
                            <input type="time" name="itineraries[${d}][time_to]"
                                   class="form-input" style="font-size:.9rem;padding:.5rem .75rem;width:150px;">
                        </div>
                    </div>
                    <div style="margin-bottom:.85rem;">
                        <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">📝 Penerangan</label>
                        <textarea name="itineraries[${d}][description]" rows="4"
                                  placeholder="Tulis aktiviti, lokasi yang dilawati, jadual terperinci, nota penting..."
                                  class="form-input" style="font-size:.9rem;resize:vertical;min-height:100px;line-height:1.6;width:100%;box-sizing:border-box;"></textarea>
                    </div>
                    <div>
                        <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">🖼️ Gambar (Optional)</label>
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div id="itinImgPreview${d}" style="width:80px;height:60px;border-radius:8px;border:2px dashed var(--border-color);background:var(--input-readonly-bg);display:flex;align-items:center;justify-content:center;font-size:1.5rem;overflow:hidden;flex-shrink:0;">🏔️</div>
                            <div>
                                <input type="file" name="itineraries[${d}][img]" accept="image/*"
                                       class="form-input" style="font-size:.82rem;padding:.35rem .75rem;"
                                       onchange="previewItinImg(this, '${d}')">
                                <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;">JPG/PNG, maks 2MB. Gambar membantu gambaran aktiviti.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    });

    window.removeItineraryDay = function(dayNum) {
        $(`.itinerary-day-block[data-day="${dayNum}"]`).remove();
        if (!$('.itinerary-day-block').length) {
            itineraryDayCount = 0;
            $('#itineraryContainer').html(`<div id="itinHint" style="text-align:center;padding:1.75rem;color:var(--text-muted);background:var(--input-readonly-bg);border-radius:10px;font-size:.88rem;">📅 Klik <strong>"➕ Tambah Hari"</strong> untuk mula membina itinerari</div>`);
        }
    };

    $('#tripForm').submit(function(e) {
        e.preventDefault();
        if (!$('#tripPic1')[0].files[0]) { window.showNotification('At least 1 photo is required', 'error'); return; }
        const formData = new FormData(this);
        if ($('#tripPic1')[0].files[0]) formData.append('pic1', $('#tripPic1')[0].files[0]);
        if ($('#tripPic2')[0].files[0]) formData.append('pic2', $('#tripPic2')[0].files[0]);
        if ($('#tripPic3')[0].files[0]) formData.append('pic3', $('#tripPic3')[0].files[0]);
        const btn = $(this).find('[type=submit]');
        btn.prop('disabled', true).text('⏳ Saving...');
        $.ajax({
            url: '/trips', type: 'POST', data: formData, processData: false, contentType: false,
            success: function(res) {
                if (res.success) {
                    window.showNotification('Trip created! ✈️', 'success');
                    window.resetTripForm();
                    window.location.href = '/dashboard/trips';
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('<br>')
                    : (xhr.responseJSON?.message || 'Failed to save trip');
                window.showNotification(msg, 'error');
            },
            complete: () => btn.prop('disabled', false).text('💾 Save Trip')
        });
    });


    // ── Filter & reset ────────────────────────────────────────

    $('#btnFilterTrips').click(function() {
        const companyId = $('#filterTripCompany').val();
        const country   = $('#filterTripCountry').val().trim().toLowerCase();
        const dateFrom  = $('#filterTripDateFrom').val();
        const filtered  = allTrips.filter(function(trip) {
            return (!companyId || trip.company_id == companyId)
                && (!country   || (trip.country || '').toLowerCase().includes(country))
                && (!dateFrom  || trip.date_from >= dateFrom);
        });
        window.renderTripsGrid(filtered);
        window.showNotification('Found ' + filtered.length + ' trip(s) ✅', 'info', false);
    });

    $('#btnResetTrips').click(function() {
        $('#filterTripCompany').val('');
        $('#filterTripDateFrom').val('');
        $('#filterTripCountry').val('');
        window.renderTripsGrid(allTrips);
        window.showNotification('Filter cleared ✅', 'info', false);
    });

});


// ============================================================
// EDIT ITINERARY — di luar $(function(){}) supaya accessible
// ✅ Dengan day_number editable, day_date, masa, gambar
// ============================================================

window._editItineraryCount = 0;

window.appendEditItineraryDay = function(data) {
    data = data || {};
    window._editItineraryCount = (window._editItineraryCount || 0) + 1;
    var d = window._editItineraryCount;

    $('#editItineraryContainer .et-itinerary-empty').remove();

    var id_field = data.id
        ? '<input type="hidden" name="itineraries[' + d + '][id]" value="' + data.id + '">'
        : '';

    var imgPreviewHtml = data.img_url
        ? '<img src="' + data.img_url + '" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">'
        : '🏔️';

    var $day = $(
        '<div class="et-itinerary-day" data-day="' + d + '" ' +
        'style="border:1px solid var(--border-color);border-radius:12px;margin-bottom:.75rem;overflow:hidden;">' +

        '<div style="background:linear-gradient(135deg,#667eea,#764ba2);padding:.75rem 1.25rem;' +
          'display:flex;align-items:center;gap:.75rem;">' +
          '<span style="background:rgba(255,255,255,.25);color:#fff;font-size:.75rem;font-weight:800;' +
            'padding:3px 12px;border-radius:20px;white-space:nowrap;flex-shrink:0;">Hari ' + d + '</span>' +
          '<input type="text" name="itineraries[' + d + '][title]" value="' + (data.title || '') + '" ' +
            'placeholder="Tajuk aktiviti hari ini..." class="form-input" ' +
            'style="flex:1;font-size:.88rem;padding:.4rem .85rem;background:rgba(255,255,255,.15);' +
            'border-color:rgba(255,255,255,.3);color:#fff;">' +
          '<button type="button" class="et-remove-day" data-day="' + d + '" ' +
            'style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.35);' +
            'color:#fff;border-radius:8px;padding:5px 14px;cursor:pointer;font-size:.78rem;flex-shrink:0;">✖ Buang</button>' +
        '</div>' +

        '<div style="padding:1rem 1.25rem;background:var(--card-bg);">' +
          id_field +

          '<div style="display:flex;gap:1rem;margin-bottom:.85rem;flex-wrap:wrap;">' +
            '<div>' +
              '<label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">📆 Hari Ke</label>' +
              '<input type="number" name="itineraries[' + d + '][day_number]" value="' + (data.day_number || d) + '" min="1" ' +
                'class="form-input" style="font-size:.9rem;padding:.5rem .75rem;width:90px;">' +
            '</div>' +
            '<div>' +
              '<label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">📅 Tarikh</label>' +
              '<input type="date" name="itineraries[' + d + '][day_date]" value="' + (data.day_date || '') + '" ' +
                'class="form-input" style="font-size:.9rem;padding:.5rem .75rem;width:170px;">' +
            '</div>' +
            '<div>' +
              '<label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">🕐 Masa Mula</label>' +
              '<input type="time" name="itineraries[' + d + '][time_from]" value="' + (data.time_from || '') + '" ' +
                'class="form-input" style="font-size:.9rem;padding:.5rem .75rem;width:150px;">' +
            '</div>' +
            '<div>' +
              '<label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">🕐 Masa Tamat</label>' +
              '<input type="time" name="itineraries[' + d + '][time_to]" value="' + (data.time_to || '') + '" ' +
                'class="form-input" style="font-size:.9rem;padding:.5rem .75rem;width:150px;">' +
            '</div>' +
          '</div>' +

          '<div style="margin-bottom:.85rem;">' +
            '<label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">📝 Penerangan</label>' +
            '<textarea name="itineraries[' + d + '][description]" rows="4" ' +
              'placeholder="Tulis aktiviti, lokasi yang dilawati, jadual terperinci, nota penting..." ' +
              'class="form-input" style="font-size:.9rem;resize:vertical;min-height:100px;line-height:1.6;width:100%;box-sizing:border-box;">' + (data.description || '') + '</textarea>' +
          '</div>' +

          '<div>' +
            '<label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:.35rem;">🖼️ Gambar (Optional)</label>' +
            '<div style="display:flex;align-items:center;gap:.75rem;">' +
              '<div id="etImgPreview' + d + '" style="width:80px;height:60px;border-radius:8px;border:2px solid var(--border-color);' +
                'background:var(--input-readonly-bg);display:flex;align-items:center;justify-content:center;font-size:1.5rem;overflow:hidden;flex-shrink:0;">' +
                imgPreviewHtml +
              '</div>' +
              '<div style="flex:1;">' +
                '<input type="file" name="itineraries[' + d + '][img]" accept="image/*" ' +
                  'class="form-input" style="font-size:.82rem;padding:.35rem .75rem;" ' +
                  'onchange="previewItinImg(this, \'et' + d + '\')">' +
                '<div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;">JPG/PNG, maks 2MB. Gambar membantu gambaran aktiviti.</div>' +
              '</div>' +
            '</div>' +
          '</div>' +

        '</div>' +
        '</div>'
    );

    $('#editItineraryContainer').append($day);
};


// ── Buang itinerary day ───────────────────────────────────

$(document).on('click', '.et-remove-day', function() {
    $(this).closest('.et-itinerary-day').slideUp(200, function() {
        $(this).remove();
        if (!$('#editItineraryContainer .et-itinerary-day').length) {
            window._editItineraryCount = 0;
            $('#editItineraryContainer').html(
                '<div class="et-itinerary-empty" style="text-align:center;padding:1.25rem;' +
                'color:var(--text-muted);font-size:.875rem;">Klik "Tambah Hari" untuk bina itinerary</div>'
            );
        }
    });
});


// ── Preview gambar itinerary ──────────────────────────────

window.previewItinImg = function(input, previewId) {
    var file = input.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        window.showNotification && window.showNotification('Gambar mesti < 2MB', 'error');
        input.value = '';
        return;
    }
    var reader = new FileReader();
    reader.onload = function(e) {
        var $preview = $('#itinImgPreview' + previewId).length
            ? $('#itinImgPreview' + previewId)
            : $('#etImgPreview' + previewId);
        $preview.html('<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">');
    };
    reader.readAsDataURL(file);
};