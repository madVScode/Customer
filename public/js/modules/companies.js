// ============================================================
// public/js/modules/companies.js
// Companies Module — CRUD, grid render, dropdown populate
// ✅ bookingCompanySelect & receiptCompanySelect → tsUpdate
// ✅ tripCompanySelect, filterTripCompany, customerCompanySelect → native DOM
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

        if (companies.length === 0) {
            container.html(`
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);">
                    <div style="font-size:4rem;margin-bottom:1rem;">🏢</div>
                    <div style="font-size:1.2rem;font-weight:600;">No companies yet</div>
                    <div style="margin-top:0.5rem;">Click "Add Company" to get started</div>
                </div>
            `);
            return;
        }

        companies.forEach(function(company) {
            const logoHtml = company.logo_url
                ? `<img src="${company.logo_url}" alt="logo"
                       style="width:70px;height:70px;object-fit:cover;border-radius:12px;border:2px solid var(--border-color);">`
                : `<div style="width:70px;height:70px;border-radius:12px;
                       background:linear-gradient(135deg,#667eea,#764ba2);
                       display:flex;align-items:center;justify-content:center;font-size:2rem;">🏢</div>`;

            container.append(`
                <div class="company-card"
                    style="background:var(--card-bg);border:1px solid var(--border-color);
                    border-radius:16px;padding:1.5rem;transition:box-shadow 0.2s;">
                    <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1rem;">
                        ${logoHtml}
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin-bottom:0.25rem;">${company.name}</div>
                            <div style="font-size:0.8rem;color:var(--text-muted);">Reg: ${company.registration_no}</div>
                        </div>
                    </div>
                    <div style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:1rem;line-height:1.5;">📍 ${company.address}</div>
                    <div style="display:flex;gap:0.5rem;">
                        <button class="btn btn-warning edit-company-btn" data-id="${company.id}"
                            data-name="${company.name}"
                            data-phone="${company.phone || ''}"
                            data-reg="${company.registration_no}"
                            data-address="${(company.address || '').replace(/"/g, '&quot;')}"
                            style="flex:1;font-size:0.85rem;">✏️ Edit</button>
                        <button class="btn btn-danger delete-company-btn" data-id="${company.id}"
                            style="flex:1;font-size:0.85rem;">🗑️ Delete</button>
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

        // ── TomSelect dropdowns (booking & receipt) ───────────
        // ✅ Guna tsUpdate — selamat walaupun instance belum ready
        if (window.tsInstances && window.tsInstances['bookingCompanySelect']) {
            window.tsUpdate('bookingCompanySelect', opts, '-- Pilih Company --', true);
        }
        if (window.tsInstances && window.tsInstances['receiptCompanySelect']) {
            window.tsUpdate('receiptCompanySelect', opts, '-- Select Company --', true);
        }

        // ── Native DOM dropdowns ──────────────────────────────

        // tripCompanySelect (add trip form)
        var $tripCo = $('#tripCompanySelect');
        if ($tripCo.length) {
            $tripCo.find('option:not(:first)').remove();
            allCompanies.forEach(function(c) {
                $tripCo.append('<option value="' + c.id + '">' + c.name + '</option>');
            });
        }

        // filterTripCompany (trips index filter)
        var $filterCo = $('#filterTripCompany');
        if ($filterCo.length) {
            $filterCo.find('option:not(:first)').remove();
            allCompanies.forEach(function(c) {
                $filterCo.append('<option value="' + c.id + '">' + c.name + '</option>');
            });
        }

        // customerCompanySelect (add customer form)
        var $custCo = $('#customerCompanySelect');
        if ($custCo.length) {
            $custCo.find('option:not(:first)').remove();
            allCompanies.forEach(function(c) {
                $custCo.append('<option value="' + c.id + '">' + c.name + '</option>');
            });
        }

        // ── Delegated ke modules lain ─────────────────────────
        if (typeof window.populateReceiptCompanyDropdown === 'function') {
            window.populateReceiptCompanyDropdown();
        }
        if (typeof window.populateBookingCompanyDropdown === 'function') {
            window.populateBookingCompanyDropdown();
        }
    }

    window.populateCompanyDropdowns = populateCompanyDropdowns;


    // ── Add company ───────────────────────────────────────────

    $('#addCompanyBtn').click(function() {
        $('#companyId').val('');
        $('#companyMethod').val('POST');
        $('#companyModalTitle').text('🏢 Add Company');
        $('#companyForm')[0].reset();
        
        // ── Reset logo preview ─────────────────────────────────
        $('#companyLogoPreview').hide().attr('src', '');
        $('#companyLogoPlaceholder').show();
        
        // ── Reset owner tab ────────────────────────────────────
        $('#existingOwnerInfo').hide();
        $('#ownerPwMatchErr').hide();
        $('#ownerActive').prop('checked', true);
        
        // ── Reset to first tab ─────────────────────────────────
        $('.co-tab').removeClass('active');
        $('.co-tab[data-tab="coTabInfo"]').addClass('active');
        $('.co-tab-panel').hide();
        $('#coTabInfo').show();
        
        $('#companyModal').addClass('show');
    });

    $('#selectLogoBtn, #companyLogoPreviewBox').click(() => $('#companyLogoInput').click());

    $('#companyLogoInput').change(function(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            window.showNotification('Logo must be < 2MB', 'error'); return;
        }
        const reader = new FileReader();
        reader.onload = ev => {
            $('#companyLogoPreview').attr('src', ev.target.result).show();
            $('#companyLogoPlaceholder').hide();
        };
        reader.readAsDataURL(file);
    });


    // ── Edit company ──────────────────────────────────────────

    $(document).on('click', '.edit-company-btn', function() {
        const companyId = $(this).data('id');
        
        // Find full company data from allCompanies
        const company = allCompanies.find(c => c.id === companyId);
        
        if (!company) {
            window.showNotification('Company data not found', 'error');
            return;
        }
        
        // ── Populate Company Info Tab ─────────────────────────
        $('#companyId').val(company.id);
        $('#companyMethod').val('PUT');
        $('#companyModalTitle').text('✏️ Edit Company');
        $('#companyName').val(company.name);
        $('#companyPhone').val(company.phone || '');
        $('#companyRegNo').val(company.registration_no);
        $('#companyAddress').val(company.address);
        
        // ── Populate logo preview ──────────────────────────────
        if (company.logo_url) {
            $('#companyLogoPreview').attr('src', company.logo_url).show();
            $('#companyLogoPlaceholder').hide();
        } else {
            $('#companyLogoPreview').hide().attr('src', '');
            $('#companyLogoPlaceholder').show();
        }
        
        // ── Populate Owner Account Tab ────────────────────────
        if (company.owner) {
            $('#ownerName').val(company.owner.name || '');
            $('#ownerEmail').val(company.owner.email || '');
            $('#ownerPhone').val(company.owner.phone || '');
            
            // Show existing owner info
            $('#existingOwnerInfo').show();
            $('#existingOwnerDetails').html(`
                <div><strong>Name:</strong> ${company.owner.name || 'N/A'}</div>
                <div><strong>Email:</strong> ${company.owner.email || 'N/A'}</div>
                <div><strong>Phone:</strong> ${company.owner.phone || 'N/A'}</div>
            `);
        } else {
            // Clear owner fields if no owner exists
            $('#ownerName, #ownerEmail, #ownerPhone').val('');
            $('#existingOwnerInfo').hide();
        }
        
        // Clear password fields (never pre-fill passwords)
        $('#ownerPassword, #ownerPasswordConfirm').val('');
        $('#ownerPwMatchErr').hide();
        
        $('#companyModal').addClass('show');
    });


    // ── Delete company ────────────────────────────────────────

    $(document).on('click', '.delete-company-btn', function() {
        const id = $(this).data('id');
        if (!confirm('Delete this company? This may affect related trips and customers.')) return;
        $.ajax({
            url: `/companies/${id}`, type: 'DELETE',
            success: function(res) {
                if (res.success) {
                    window.showNotification('Company deleted ✅', 'success');
                    window.loadCompanies();
                }
            },
            error: () => window.showNotification('Failed to delete company', 'error')
        });
    });


    // ── Form submit ───────────────────────────────────────────

    $('#companyForm').submit(function(e) {
        e.preventDefault();
        
        // ── Validate owner password match ─────────────────────
        const ownerPw = $('#ownerPassword').val();
        const ownerPwConfirm = $('#ownerPasswordConfirm').val();
        
        if (ownerPw && ownerPw !== ownerPwConfirm) {
            $('#ownerPwMatchErr').show();
            window.showNotification('Kata laluan tidak sepadan', 'error');
            return;
        }
        
        const id     = $('#companyId').val();
        const method = $('#companyMethod').val();
        const url    = method === 'PUT' ? `/companies/${id}` : '/companies';

        const formData = new FormData();
        
        // ── Company Info ───────────────────────────────────────
        formData.append('name',            $('#companyName').val().trim());
        formData.append('phone',           $('#companyPhone').val().trim());
        formData.append('registration_no', $('#companyRegNo').val().trim());
        formData.append('address',         $('#companyAddress').val().trim());
        
        // ── Owner Account Info ─────────────────────────────────
        const ownerName = $('#ownerName').val().trim();
        const ownerEmail = $('#ownerEmail').val().trim();
        const ownerPhone = $('#ownerPhone').val().trim();
        const ownerActive = $('#ownerActive').is(':checked') ? '1' : '0';
        
        if (ownerName)  formData.append('owner_name', ownerName);
        if (ownerEmail) formData.append('owner_email', ownerEmail);
        if (ownerPhone) formData.append('owner_phone', ownerPhone);
        if (ownerPw)    formData.append('owner_password', ownerPw);
        formData.append('owner_active', ownerActive);
        
        // ── Logo Upload ────────────────────────────────────────
        const logo = $('#companyLogoInput')[0].files[0];
        if (logo) formData.append('logo', logo);
        
        if (method === 'PUT') formData.append('_method', 'PUT');

        $.ajax({
            url, type: 'POST', data: formData, processData: false, contentType: false,
            success: function(res) {
                if (res.success) {
                    window.showNotification(
                        method === 'PUT' ? 'Company updated ✅' : 'Company added ✅', 'success'
                    );
                    $('#companyModal').removeClass('show');
                    window.loadCompanies();
                }
            },
            error: xhr => window.showNotification(
                xhr.responseJSON?.message || 'Failed to save company', 'error'
            )
        });
    });


    // ── Close modal ───────────────────────────────────────────

    $('#closeCompanyModal').click(() => $('#companyModal').removeClass('show'));

});