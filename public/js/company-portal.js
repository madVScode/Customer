// ============================================================
// public/js/modules/companies.js — v3.0.0
// ✅ Company CRUD + Owner Business account management
// ✅ tsUpdate untuk bookingCompanySelect & receiptCompanySelect
// ============================================================

$(function () {

    // ── Load ─────────────────────────────────────────────────

    window.loadCompanies = function(callback) {
        callback = callback || null;
        $.ajax({
            url: '/companies', type: 'GET',
            success: function(response) {
                if (response.success) {
                    allCompanies = response.data;
                    renderCompaniesGrid(allCompanies);
                    populateCompanyDropdowns();
                    if (typeof callback === 'function') callback();
                }
            },
            error: () => window.showNotification('Failed to load companies', 'error')
        });
    };


    // ── Render ────────────────────────────────────────────────

    function renderCompaniesGrid(companies) {
        const container = $('#companiesGrid');
        container.empty();

        if (!companies.length) {
            container.html(`
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);">
                    <div style="font-size:4rem;margin-bottom:1rem;">🏢</div>
                    <div style="font-size:1.2rem;font-weight:600;">Tiada syarikat</div>
                    <div style="margin-top:.5rem;">Klik "Add Company" untuk tambah</div>
                </div>
            `);
            return;
        }

        companies.forEach(function(company) {
            const logoHtml = company.logo_url
                ? `<img src="${company.logo_url}" style="width:60px;height:60px;object-fit:cover;border-radius:10px;border:2px solid var(--border-color);">`
                : `<div style="width:60px;height:60px;border-radius:10px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:1.75rem;">🏢</div>`;

            // Owner badge
            const ownerBadge = company.owner
                ? `<div style="font-size:.72rem;color:#166534;background:#dcfce7;border:1px solid #bbf7d0;padding:2px 8px;border-radius:10px;display:inline-block;margin-top:.3rem;">👤 ${company.owner.name}</div>`
                : `<div style="font-size:.72rem;color:#92400e;background:#fef3c7;border:1px solid #fcd34d;padding:2px 8px;border-radius:10px;display:inline-block;margin-top:.3rem;">⚠️ Tiada owner</div>`;

            container.append(`
                <div class="company-card" style="background:var(--card-bg);border:1px solid var(--border-color);border-radius:16px;padding:1.5rem;transition:box-shadow .2s;">
                    <div style="display:flex;gap:.85rem;align-items:flex-start;margin-bottom:1rem;">
                        ${logoHtml}
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:.2rem;">${company.name}</div>
                            <div style="font-size:.78rem;color:var(--text-muted);">📋 ${company.registration_no}</div>
                            ${ownerBadge}
                        </div>
                    </div>
                    <div style="font-size:.82rem;color:var(--text-secondary);margin-bottom:1rem;line-height:1.5;">📍 ${company.address || '-'}</div>
                    <div style="display:flex;gap:.5rem;">
                        <button class="btn btn-warning edit-company-btn"
                            data-id="${company.id}"
                            data-name="${(company.name||'').replace(/"/g,'&quot;')}"
                            data-reg="${company.registration_no||''}"
                            data-address="${(company.address||'').replace(/"/g,'&quot;')}"
                            data-owner-id="${company.owner ? company.owner.unique_id : ''}"
                            data-owner-name="${company.owner ? (company.owner.name||'').replace(/"/g,'&quot;') : ''}"
                            data-owner-email="${company.owner ? company.owner.email : ''}"
                            data-owner-phone="${company.owner ? (company.owner.phone||'') : ''}"
                            style="flex:1;font-size:.82rem;">✏️ Edit</button>
                        <button class="btn btn-danger delete-company-btn"
                            data-id="${company.id}"
                            style="flex:1;font-size:.82rem;">🗑️ Padam</button>
                    </div>
                </div>
            `);
        });
    }


    // ── Populate dropdowns ────────────────────────────────────

    function populateCompanyDropdowns() {
        var opts = allCompanies.map(function(c) {
            return { value: String(c.id), text: c.name };
        });

        // TomSelect dropdowns
        if (window.tsInstances && window.tsInstances['bookingCompanySelect']) {
            window.tsUpdate('bookingCompanySelect', opts, '-- Pilih Company --', true);
        }
        if (window.tsInstances && window.tsInstances['receiptCompanySelect']) {
            window.tsUpdate('receiptCompanySelect', opts, '-- Select Company --', true);
        }

        // Native dropdowns
        ['tripCompanySelect', 'filterTripCompany', 'customerCompanySelect'].forEach(function(id) {
            var $sel = $('#' + id);
            if (!$sel.length) return;
            $sel.find('option:not(:first)').remove();
            allCompanies.forEach(function(c) {
                $sel.append('<option value="' + c.id + '">' + c.name + '</option>');
            });
        });

        if (typeof window.populateReceiptCompanyDropdown === 'function') window.populateReceiptCompanyDropdown();
        if (typeof window.populateBookingCompanyDropdown === 'function') window.populateBookingCompanyDropdown();
    }

    window.populateCompanyDropdowns = populateCompanyDropdowns;


    // ── Tab switching dalam modal ─────────────────────────────

    $(document).on('click', '.co-tab', function() {
        var tabId = $(this).data('tab');
        $('.co-tab').css({'color':'var(--text-muted)','border-bottom-color':'transparent','font-weight':'600'})
                    .removeClass('active');
        $(this).css({'color':'var(--primary-color)','border-bottom-color':'var(--primary-color)','font-weight':'700'})
               .addClass('active');
        $('.co-tab-panel').hide();
        $('#' + tabId).show();
    });


    // ── Add company ───────────────────────────────────────────

    $('#addCompanyBtn').click(function() {
        $('#companyId').val('');
        $('#companyMethod').val('POST');
        $('#companyModalTitle').text('🏢 Add Company');
        $('#companyForm')[0].reset();
        $('#companyLogoPreview').hide().attr('src', '');
        $('#companyLogoPlaceholder').show();
        $('#existingOwnerInfo').hide();
        $('#ownerPwMatchErr').hide();
        // Reset tabs
        $('.co-tab').first().click();
        $('#companyModal').addClass('show');
    });

    $('#selectLogoBtn, #companyLogoPreviewBox').click(() => $('#companyLogoInput').click());

    $('#companyLogoInput').change(function(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) { window.showNotification('Logo mesti < 2MB', 'error'); return; }
        const reader = new FileReader();
        reader.onload = ev => {
            $('#companyLogoPreview').attr('src', ev.target.result).show();
            $('#companyLogoPlaceholder').hide();
        };
        reader.readAsDataURL(file);
    });

    // Password toggle
    $('#toggleOwnerPw').click(function() {
        var $p = $('#ownerPassword'), vis = $p.attr('type') === 'text';
        $p.attr('type', vis ? 'password' : 'text');
        $(this).text(vis ? '👁️' : '🙈');
    });

    // Password match check
    $(document).on('input', '#ownerPasswordConfirm, #ownerPassword', function() {
        var pw  = $('#ownerPassword').val();
        var cf  = $('#ownerPasswordConfirm').val();
        $('#ownerPwMatchErr').toggle(cf.length > 0 && pw !== cf);
    });


    // ── Edit company ──────────────────────────────────────────

    $(document).on('click', '.edit-company-btn', function() {
        const btn = $(this);
        $('#companyId').val(btn.data('id'));
        $('#companyMethod').val('PUT');
        $('#companyModalTitle').text('✏️ Edit Company');
        $('#companyName').val(btn.data('name'));
        $('#companyRegNo').val(btn.data('reg'));
        $('#companyAddress').val(btn.data('address'));
        $('#ownerPassword').val('').attr('type','password');
        $('#ownerPasswordConfirm').val('');
        $('#ownerPwMatchErr').hide();
        $('#toggleOwnerPw').text('👁️');
        $('.co-tab').first().click();

        // Tunjuk owner sedia ada
        const ownerEmail = btn.data('owner-email');
        if (ownerEmail) {
            $('#existingOwnerInfo').show();
            $('#existingOwnerDetails').html(
                '👤 <strong>' + (btn.data('owner-name') || '-') + '</strong>' +
                ' &nbsp;|&nbsp; 📧 ' + ownerEmail +
                (btn.data('owner-phone') ? ' &nbsp;|&nbsp; 📱 ' + btn.data('owner-phone') : '')
            );
            $('#ownerName').val(btn.data('owner-name') || '');
            $('#ownerEmail').val(ownerEmail);
            $('#ownerPhone').val(btn.data('owner-phone') || '');
        } else {
            $('#existingOwnerInfo').hide();
            $('#ownerName').val('');
            $('#ownerEmail').val('');
            $('#ownerPhone').val('');
        }

        $('#companyModal').addClass('show');
    });


    // ── Delete company ────────────────────────────────────────

    $(document).on('click', '.delete-company-btn', function() {
        if (!confirm('Padam syarikat ini? Ini akan menjejaskan trip dan customer berkaitan.')) return;
        const id = $(this).data('id');
        $.ajax({
            url: '/companies/' + id, type: 'DELETE',
            success: function(res) {
                if (res.success) {
                    window.showNotification('Syarikat dipadam ✅', 'success');
                    window.loadCompanies();
                }
            },
            error: () => window.showNotification('Gagal memadam syarikat', 'error')
        });
    });


    // ── Form submit ───────────────────────────────────────────

    $('#companyForm').submit(function(e) {
        e.preventDefault();

        // Validate password match
        const pw = $('#ownerPassword').val();
        const cf = $('#ownerPasswordConfirm').val();
        if (pw && pw !== cf) {
            window.showNotification('Kata laluan tidak sepadan!', 'error');
            return;
        }
        if (pw && pw.length < 6) {
            window.showNotification('Kata laluan mesti sekurang-kurangnya 6 aksara', 'error');
            return;
        }

        const id     = $('#companyId').val();
        const method = $('#companyMethod').val();
        const url    = method === 'PUT' ? '/companies/' + id : '/companies';

        const formData = new FormData();
        formData.append('name',            $('#companyName').val().trim());
        formData.append('registration_no', $('#companyRegNo').val().trim());
        formData.append('address',         $('#companyAddress').val().trim());
        if (method === 'PUT') formData.append('_method', 'PUT');
        const logo = $('#companyLogoInput')[0].files[0];
        if (logo) formData.append('logo', logo);

        // Owner business fields
        if ($('#ownerName').val())  formData.append('owner_name',  $('#ownerName').val().trim());
        if ($('#ownerEmail').val()) formData.append('owner_email', $('#ownerEmail').val().trim());
        if (pw)                     formData.append('owner_password', pw);
        if ($('#ownerPhone').val()) formData.append('owner_phone', $('#ownerPhone').val().trim());
        formData.append('owner_active', $('#ownerActive').is(':checked') ? '1' : '0');

        const $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('⏳ Menyimpan...');

        $.ajax({
            url, type: 'POST', data: formData, processData: false, contentType: false,
            success: function(res) {
                if (res.success) {
                    window.showNotification(method === 'PUT' ? 'Syarikat dikemaskini ✅' : 'Syarikat ditambah ✅', 'success');
                    $('#companyModal').removeClass('show');
                    window.loadCompanies();
                }
            },
            error: xhr => window.showNotification(xhr.responseJSON?.message || 'Gagal menyimpan syarikat', 'error'),
            complete: () => $btn.prop('disabled', false).text('💾 Simpan')
        });
    });


    // ── Close modal ───────────────────────────────────────────

    $('#closeCompanyModal').click(() => $('#companyModal').removeClass('show'));

});