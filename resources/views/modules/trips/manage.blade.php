{{-- ============================================================ --}}
{{-- resources/views/modules/trips/manage.blade.php              --}}
{{-- View: Urus Pelanggan Trip                                   --}}
{{-- ============================================================ --}}
@extends('layouts.app')
@section('title', 'Manage Trip Customers')
@section('content')
<div id="manageTripView">

    {{-- Header --}}
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap;">
        <a href="/dashboard/trips"
           style="display:inline-flex;align-items:center;gap:.4rem;padding:8px 18px;
                  background:var(--input-readonly-bg);border:1.5px solid var(--border-color);
                  border-radius:10px;font-size:.85rem;font-weight:700;
                  color:var(--text-secondary);text-decoration:none;">
            ← Kembali ke Trip
        </a>
        <div>
            <h1 class="page-title" style="margin:0;font-size:1.35rem;">
                👥 Manage Customers — {{ $trip->location }}
            </h1>
            <p style="font-size:.82rem;color:var(--text-muted);margin:3px 0 0;">
                🏢 {{ $trip->company?->name ?? '-' }} &nbsp;•&nbsp;
                📅 {{ $trip->date_from }} → {{ $trip->date_to }}
            </p>
        </div>
    </div>

    {{-- Amaran --}}
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.82rem;color:#92400e;">
        ⚠️ <b>Buang SEMUA customer</b> untuk membuka semula butang ✏️ Edit & 🗑️ Delete pada trip ini.
    </div>

    {{-- Stats --}}
    <div id="manageTripStats" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:1rem;margin-bottom:1.5rem;"></div>

    {{-- Table Card --}}
    <div class="content-card" style="padding:0;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1.2rem 1.5rem;border-bottom:1px solid var(--border-color);flex-wrap:wrap;gap:1rem;">
            <div style="display:flex;align-items:center;gap:.75rem;">
                <div style="width:40px;height:40px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">👥</div>
                <div>
                    <div style="font-weight:700;font-size:.95rem;color:var(--text-primary);">Senarai Pelanggan Booking</div>
                    <div id="manageTripCountLabel" style="font-size:.78rem;color:var(--text-muted);">Memuatkan...</div>
                </div>
            </div>
            <button id="btnRefreshManageTrip"
                style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;background:var(--input-readonly-bg);border:1.5px solid var(--border-color);border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;color:var(--text-secondary);">
                ↺ Muat Semula
            </button>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--input-readonly-bg);">
                        <th style="padding:.7rem 1.25rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border-color);">Bil</th>
                        <th style="padding:.7rem 1.25rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border-color);">Pelanggan</th>
                        <th style="padding:.7rem 1.25rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border-color);">Telefon</th>
                        <th style="padding:.7rem 1.25rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border-color);">Booking No</th>
                        <th style="padding:.7rem 1.25rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border-color);">Nilai</th>
                        <th style="padding:.7rem 1.25rem;text-align:left;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border-color);">Status</th>
                        <th style="padding:.7rem 1.25rem;text-align:center;font-size:.7rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border-color);">Tindakan</th>
                    </tr>
                </thead>
                <tbody id="manageTripBody">
                    <tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--text-muted);">
                        <div style="font-size:2rem;margin-bottom:.5rem;">⏳</div>
                        <div>Memuatkan...</div>
                    </td></tr>
                </tbody>
            </table>
        </div>

        {{-- Empty State --}}
        <div id="manageTripEmpty" style="display:none;padding:5rem 2rem;text-align:center;">
            <div style="font-size:3.5rem;margin-bottom:1rem;">🔓</div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);margin-bottom:.5rem;">Tiada Pelanggan Aktif</div>
            <div style="font-size:.87rem;color:var(--text-muted);max-width:380px;margin:0 auto 1.5rem;">
                Trip ini kini tiada booking aktif. Butang ✏️ Edit &amp; 🗑️ Delete trip kini boleh digunakan.
            </div>
            <a href="/dashboard/trips" class="btn btn-primary">← Kembali ke Senarai Trip</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {

    var TRIP_ID = {{ $trip->id }};   // ✅ ID dari controller

    var statusColors = {
        confirmed : '#4caf50',
        pending   : '#ff9800',
        cancelled : '#f44336',
        completed : '#2196f3'
    };

    // ── Format wang dengan koma ──────────────────────────────
    function formatMoney(amount) {
        return parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // ── Load bookings ────────────────────────────────────────
    function loadBookings() {
        $('#manageTripCountLabel').text('Memuatkan...');
        $('#manageTripBody').html(
            '<tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--text-muted);">' +
            '<div style="font-size:2rem;margin-bottom:.5rem;">⏳</div><div>Memuatkan...</div></td></tr>'
        );

        $.ajax({
            url: '/trips/' + TRIP_ID + '/bookings',
            type: 'GET',
            success: function(res) {
                var bookings = res.data || res.bookings || [];
                renderBookings(bookings);
            },
            error: function() {
                window.showNotification && window.showNotification('Gagal memuatkan data', 'error');
                $('#manageTripBody').html(
                    '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--error-color);">❌ Gagal memuatkan data</td></tr>'
                );
            }
        });
    }

    // ── Render bookings table ─────────────────────────────────
    function renderBookings(bookings) {
    var $body = $('#manageTripBody');
    $body.empty();

    if (!bookings.length) {
        $body.html('<tr><td colspan="7" style="padding:0;"></td></tr>');
        $('#manageTripCountLabel').text('Tiada customer');
        $('#manageTripEmpty').show();
        renderStats(0, 0);
        return;
    }

    $('#manageTripEmpty').hide();
    $('#manageTripCountLabel').text(bookings.length + ' customer' + (bookings.length > 1 ? 's' : ''));

    // ── Kira total revenue ────────────────────────────────
    var totalRevenue = bookings.reduce(function(sum, b) {
        return sum + parseFloat(b.total_amount || 0);
    }, 0);

    renderStats(bookings.length, totalRevenue);

        bookings.forEach(function(b, i) {
            var customer = b.customer || {};
            var status   = b.booking_status || b.status || 'pending';
            var color    = statusColors[status] || '#9e9e9e';
            var avatar   = (customer.name || 'C').charAt(0).toUpperCase();

            $body.append(
                '<tr style="border-bottom:1px solid var(--border-color);transition:background .15s;" ' +
                'onmouseover="this.style.background=\'var(--hover-bg)\'" onmouseout="this.style.background=\'\'">' +
                '<td style="padding:.75rem 1.25rem;font-size:.85rem;">' + (i + 1) + '</td>' +
                '<td style="padding:.75rem 1.25rem;">' +
                    '<div style="display:flex;align-items:center;gap:.6rem;">' +
                    '<div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);' +
                        'display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0;">' + avatar + '</div>' +
                    '<div>' +
                        '<div style="font-weight:600;font-size:.875rem;color:var(--text-primary);">' + (customer.name || '-') + '</div>' +
                        '<div style="font-size:.75rem;color:var(--text-muted);">' + (customer.ic || customer.ic_no || '') + '</div>' +
                    '</div></div>' +
                '</td>' +
                '<td style="padding:.75rem 1.25rem;font-size:.82rem;color:var(--text-muted);">' + (customer.phone || '-') + '</td>' +
                '<td style="padding:.75rem 1.25rem;font-size:.82rem;font-family:monospace;color:var(--text-secondary);">' + (b.booking_no || '-') + '</td>' +
                '<td style="padding:.75rem 1.25rem;font-size:.85rem;font-weight:600;">RM ' + formatMoney(b.total_amount) + '</td>' +
                '<td style="padding:.75rem 1.25rem;">' +
                    '<span style="background:' + color + '22;color:' + color + ';border:1px solid ' + color + '44;' +
                    'padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;text-transform:capitalize;">' + status + '</span>' +
                '</td>' +
                '<td style="padding:.75rem 1.25rem;text-align:center;">' +
                    '<button class="btn btn-danger btn-sm remove-booking-btn" ' +
                        'data-booking-id="' + b.id + '" ' +
                        'style="font-size:.72rem;padding:.28rem .65rem;border-radius:8px;">🗑️ Buang</button>' +
                '</td>' +
                '</tr>'
            );
        });
    }

    // ── Stats boxes ───────────────────────────────────────────
function renderStats(total, revenue) {
    revenue = revenue || 0;
    $('#manageTripStats').html(
        '<div style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:12px;padding:1rem;text-align:center;">' +
            '<div style="font-size:1.8rem;font-weight:800;color:var(--primary-color);">' + total + '</div>' +
            '<div style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem;">Total Customer</div>' +
        '</div>' +
        '<div style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:12px;padding:1rem;text-align:center;">' +
            '<div style="font-size:1.4rem;font-weight:800;color:#4caf50;">RM ' + formatMoney(revenue) + '</div>' +
            '<div style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem;">💰 Jumlah Revenue</div>' +
        '</div>' +
        '<div style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:12px;padding:1rem;text-align:center;">' +
            '<div style="font-size:1.8rem;font-weight:800;color:' + (total > 0 ? '#ff9800' : '#4caf50') + ';">' +
                (total > 0 ? '🔒' : '🔓') + '</div>' +
            '<div style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem;">' +
                (total > 0 ? 'Edit & Delete Dikunci' : 'Edit & Delete Terbuka') + '</div>' +
        '</div>'
    );
}

    // ── Buang customer ────────────────────────────────────────
    $(document).on('click', '.remove-booking-btn', function() {
        var bookingId = $(this).data('booking-id');
        if (!confirm('Buang customer ini dari trip? Tindakan ini tidak boleh diundur.')) return;

        var $btn = $(this);
        var $row = $btn.closest('tr');
        $btn.prop('disabled', true).html('⏳');

        $.ajax({
            url: '/trips/' + TRIP_ID + '/bookings/' + bookingId,
            type: 'DELETE',
            success: function(res) {
                if (res.success) {
                    window.showNotification && window.showNotification('Customer berjaya dibuang ✅', 'success');
                    $row.fadeOut(280, function() {
    $(this).remove();
    var remaining = $('#manageTripBody tr').length;
    $('#manageTripCountLabel').text(remaining + ' customer' + (remaining > 1 ? 's' : ''));

    // ── Kira semula revenue dari rows yang tinggal ────────
    var newRevenue = 0;
    $('#manageTripBody tr').each(function() {
        var amtText = $(this).find('td:nth-child(5)').text().replace('RM', '').replace(/,/g, '').trim();
        newRevenue += parseFloat(amtText) || 0;
    });

    renderStats(remaining, newRevenue);

    if (remaining === 0) {
        $('#manageTripEmpty').show();
    }
});
                } else {
                    window.showNotification && window.showNotification(res.message || 'Gagal buang customer', 'error');
                    $btn.prop('disabled', false).html('🗑️ Buang');
                }
            },
            error: function() {
                window.showNotification && window.showNotification('Gagal buang customer', 'error');
                $btn.prop('disabled', false).html('🗑️ Buang');
            }
        });
    });

    // ── Buttons ───────────────────────────────────────────────
    $('#btnRefreshManageTrip').on('click', loadBookings);

    // ── Init ─────────────────────────────────────────────────
    loadBookings();
});
</script>
@endpush