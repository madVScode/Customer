// ========================================
// 🔥 DEFINE GLOBAL FUNCTIONS FIRST!
// ========================================

// ================= TOAST NOTIFICATION (DEFINED FIRST!) =================
window.showNotification = function(message, type = 'success') {
    const bg = type === 'error' ? '#f44336' : type === 'info' ? '#2196f3' : '#4caf50';

    const toast = $(`
        <div style="
            position:fixed;
            top:20px;
            right:20px;
            background:${bg};
            color:white;
            padding:12px 18px;
            border-radius:8px;
            z-index:99999;
            box-shadow:0 6px 20px rgba(0,0,0,0.2);
            font-size:0.9rem;
            max-width:400px;
        ">
            ${message}
        </div>
    `);

    $('body').append(toast);
    setTimeout(() => toast.fadeOut(300, () => toast.remove()), 4000);
};

// ========================================
// 🔥 MAIN APP CODE STARTS HERE
// ========================================

$(function () {

    // ================= CSRF SETUP =================
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let currentCustomerData = null;
    let currentReceiptData = null;
    let offset = 0;
    let limit = 50;
    let loading = false;

    // ================= Audio System =================
    let notificationSound = null;
    let currentRingtone = localStorage.getItem('ringtone') || '';
    let ringtoneVolume = parseFloat(localStorage.getItem('ringtoneVolume')) || 0.5;
    let customSounds = JSON.parse(localStorage.getItem('customSounds') || '[]');

    function initializeSound() {
        if (customSounds.length > 0) {
            if (!currentRingtone || !customSounds.find(s => s.id === currentRingtone)) {
                currentRingtone = customSounds[0].id;
                localStorage.setItem('ringtone', currentRingtone);
            }
            setNotificationSound(currentRingtone);
        }
    }

    function setNotificationSound(ringtoneId) {
        currentRingtone = ringtoneId;
        localStorage.setItem('ringtone', ringtoneId);

        if (notificationSound) {
            notificationSound.pause();
            notificationSound = null;
        }

        const sound = customSounds.find(s => s.id === ringtoneId);
        if (sound) {
            notificationSound = new Audio(sound.data);
            notificationSound.volume = ringtoneVolume;
        }
    }

    function setRingtoneVolume(volume) {
        ringtoneVolume = volume;
        localStorage.setItem('ringtoneVolume', volume);
        if (notificationSound) notificationSound.volume = volume;
    }

    window.playNotificationSound = function() {
        if (notificationSound) {
            notificationSound.currentTime = 0;
            notificationSound.play().catch(e => {
                console.log('Audio play failed:', e);
            });
        }
    };

    function updateRingtone(ringtoneId) {
        setNotificationSound(ringtoneId);
    }

    function updateVolume(volume) {
        setRingtoneVolume(volume);
    }

    function testRingtone() {
        window.playNotificationSound();
    }

    // ================= Data Store =================
    let allCustomers = [];
    let allReceipts = [];

    // ================= Load Customers from Server =================
    window.loadCustomersFromServer = function(filters = {}, reset = false) {
        if (loading) return;
        loading = true;
        if (reset) {
            allCustomers = [];
            offset = 0;
        }

        if (Object.keys(filters).length > 0) {
            $('#tableBody').html(`
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2rem; color: var(--primary-color);">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔍</div>
                        <div style="font-size: 1.1rem; font-weight: 600;">Searching...</div>
                    </td>
                </tr>
            `);
        }

        $.ajax({
            url: '/customers/search',
            type: 'GET',
            data: { ...filters, offset, limit },
            success: function (response) {
                if (response.success) {
                    response.data.forEach(customer => {
                        allCustomers.push(customer);
                    });
                    renderCustomerTable(allCustomers);
                    updateSystemInfo();
                    populateCustomerDropdowns();
                }
                loading = false;
            },
            error: function (xhr) {
                window.showNotification('Failed to load customers', 'error');
                loading = false;
                $('#tableBody').html(`
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem; color: var(--error-color);">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">❌</div>
                            <div style="font-size: 1.1rem; font-weight: 600;">Failed to load customers</div>
                        </td>
                    </tr>
                `);
            }
        });
    };

    loadCustomersFromServer();

    // ================= Load Receipts from Server =================
    function loadReceiptsFromServer(filters = {}) {
        let url = '/receipts';

        if (Object.keys(filters).length > 0 && Object.values(filters).some(v => v)) {
            url = '/receipts/search';
        }

        if (Object.keys(filters).length > 0) {
            $('#receiptsTableBody').html(`
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--primary-color);">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔍</div>
                        <div style="font-size: 1.1rem; font-weight: 600;">Searching...</div>
                    </td>
                </tr>
            `);
        }

        $.ajax({
            url: url,
            type: 'GET',
            data: filters,
            success: function (response) {
                if (response.success) {
                    allReceipts = response.data || [];
                    renderReceiptsTable(allReceipts);
                }
            },
            error: function (xhr) {
                window.showNotification('Failed to load receipts', 'error');
                $('#receiptsTableBody').html(`
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--error-color);">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">❌</div>
                            <div style="font-size: 1.1rem; font-weight: 600;">Failed to load receipts</div>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ================= Populate Customer Dropdowns =================
    function populateCustomerDropdowns() {
        const customers = allCustomers.filter(c => !c.deleted_at);
        $('#receiptCustomerSelect').empty().append('<option value="">-- Select Customer --</option>');

        customers.forEach(customer => {
            const option = `<option value="${customer.unique_id}" data-customer='${JSON.stringify(customer)}'>${customer.name} (${customer.ic})</option>`;
            $('#receiptCustomerSelect').append(option);
        });
    }

    // ================= Generate Receipt Number =================
    function generateReceiptNumber() {
        const prefix = 'RCP';
        const timestamp = Date.now().toString().slice(-8);
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return `${prefix}-${timestamp}-${random}`;
    }

    // ================= Load States =================
    function loadStates(selectedStateId = null) {
        $.ajax({
            url: '/states',
            type: 'GET',
            success: function (response) {
                if (response.success && response.data.length > 0) {
                    const states = response.data;

                    $('#selectState').empty().append('<option value="">-- Pilih Negeri --</option>');
                    $('#selectStateMain').empty().append('<option value="">-- Pilih Negeri --</option>');
                    $('#searchState').empty().append('<option value="">All States</option>');

                    states.forEach(state => {
                        const option = `<option value="${state.id}" ${selectedStateId == state.id ? 'selected' : ''}>${state.name}</option>`;
                        $('#selectState').append(option);
                        $('#selectStateMain').append(option);
                        $('#searchState').append(option);
                    });
                } else {
                    $('#selectState, #selectStateMain, #searchState').empty().append('<option value="">No states available</option>');
                }
            },
            error: function () {
                window.showNotification('Gagal memuat negeri', 'error');
            }
        });
    }

    loadStates();

    // ================= View Switching =================
    window.showView = function(viewId) {
        $('.view-section').hide();
        $('#' + viewId).fadeIn(300);
    };

    // ================= Sidebar Navigation =================
    $('#dashboardBtn').click(function (e) {
        e.preventDefault();
        $('.sidebar-link').removeClass('active');
        $(this).addClass('active');
        showView('dashboardView');
    });

    $('#customersBtn').click(function (e) {
        e.preventDefault();
        $('.sidebar-link').removeClass('active');
        $(this).addClass('active');
        showView('addCustomerView');
        if ($('#customerFormMain').length) {
            $('#customerFormMain')[0].reset();
            $('#icNumberMain').trigger('input');
        }
    });

    $('#createReceiptBtn').click(function (e) {
        e.preventDefault();
        $('.sidebar-link').removeClass('active');
        $(this).addClass('active');
        showView('createReceiptView');

        $('#receiptNumber').val(generateReceiptNumber());
        $('#receiptDate').val(new Date().toISOString().split('T')[0]);
    });

    $('#viewReceiptsBtn').click(function (e) {
        e.preventDefault();
        $('.sidebar-link').removeClass('active');
        $(this).addClass('active');
        showView('viewReceiptsView');
        loadReceiptsFromServer();
    });

    $('#settingsBtn').click(function (e) {
        e.preventDefault();
        $('.sidebar-link').removeClass('active');
        $(this).addClass('active');
        showView('settingsView');
        updateSystemInfo();
        loadCustomSounds();
    });

    // ================= Dark Mode Toggle =================
    function setTheme(theme) {
        if (theme === 'dark') {
            $('html').attr('data-theme', 'dark');
            $('#darkModeToggle').prop('checked', true);
            $('#themeIcon').text('🌙');
            $('#themeText').text('Dark Mode');
            $('#currentTheme').text('Dark Mode');
            localStorage.setItem('theme', 'dark');
        } else {
            $('html').attr('data-theme', 'light');
            $('#darkModeToggle').prop('checked', false);
            $('#themeIcon').text('☀️');
            $('#themeText').text('Light Mode');
            $('#currentTheme').text('Light Mode');
            localStorage.setItem('theme', 'light');
        }
    }

    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);

    $('#darkModeToggle').change(function () {
        const newTheme = $(this).is(':checked') ? 'dark' : 'light';
        setTheme(newTheme);
        window.showNotification(`Switched to ${newTheme} mode! 🎨`, 'info');
    });

    // ================= Ringtone Settings =================
    function loadCustomSounds() {
        customSounds = JSON.parse(localStorage.getItem('customSounds') || '[]');
        updateRingtoneDropdown();
        initializeSound();
    }

    function updateRingtoneDropdown() {
        const $select = $('#ringtoneSelect');
        $select.empty();

        if (customSounds.length === 0) {
            $select.append('<option value="">No ringtones uploaded yet</option>');
            $select.prop('disabled', true);
        } else {
            $select.prop('disabled', false);

            customSounds.forEach(sound => {
                const selected = sound.id === currentRingtone ? 'selected' : '';
                $select.append(`<option value="${sound.id}" ${selected}>🎵 ${sound.name}</option>`);
            });
        }
    }

    if ($('#ringtoneSelect').length) {
        updateRingtoneDropdown();
    }

    if ($('#volumeSlider').length) {
        $('#volumeSlider').val(ringtoneVolume * 100);
        $('#volumeValue').text(Math.round(ringtoneVolume * 100) + '%');
        updateVolumeSliderBackground();
    }

    function updateVolumeSliderBackground() {
        const value = $('#volumeSlider').val();
        $('#volumeSlider').css('background',
            `linear-gradient(to right, var(--primary-color) 0%, var(--primary-color) ${value}%, var(--toggle-bg) ${value}%, var(--toggle-bg) 100%)`
        );
    }

    $('#ringtoneSelect').change(function () {
        const selectedRingtone = $(this).val();

        if (selectedRingtone) {
            updateRingtone(selectedRingtone);
            window.showNotification('Ringtone updated! 🔔', 'info');
        }
    });

    $('#volumeSlider').on('input', function () {
        const volume = $(this).val() / 100;
        updateVolume(volume);
        $('#volumeValue').text(Math.round(volume * 100) + '%');
        updateVolumeSliderBackground();
    });

    $('#testRingtoneBtn').click(function () {
        testRingtone();
    });

    // ================= Custom Sound Upload =================
    $('#uploadSoundBtn').click(function () {
        $('#soundFileInput').click();
    });

    $('#soundFileInput').change(function (e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('audio/')) {
            window.showNotification('Please select an audio file (MP3, WAV, etc.)', 'error');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            window.showNotification('File size must be less than 5MB', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            const soundId = 'custom_' + Date.now();
            const soundName = file.name.replace(/\.[^/.]+$/, "");

            const newSound = {
                id: soundId,
                name: soundName,
                data: event.target.result
            };

            customSounds.push(newSound);
            localStorage.setItem('customSounds', JSON.stringify(customSounds));

            if (customSounds.length === 1 || !currentRingtone) {
                updateRingtone(soundId);
            }

            updateRingtoneDropdown();
            renderCustomSoundsList();
            window.showNotification(`Sound "${soundName}" uploaded successfully! 🎵`, 'success');
        };

        reader.readAsDataURL(file);
        $(this).val('');
    });

    function renderCustomSoundsList() {
        const container = $('#customSoundsList');
        container.empty();

        if (customSounds.length === 0) {
            container.html(`
                <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎵</div>
                    <div>No custom sounds uploaded yet</div>
                    <div style="font-size: 0.875rem; margin-top: 0.5rem;">Click the button above to upload your first sound</div>
                </div>
            `);
            return;
        }

        customSounds.forEach(sound => {
            const soundItem = $(`
                <div class="custom-sound-item">
                    <div class="sound-info">
                        <span class="sound-icon">🎵</span>
                        <span class="sound-name">${sound.name}</span>
                    </div>
                    <div class="sound-actions">
                        <button class="btn-sound-play" data-sound-id="${sound.id}">
                            <span>▶️</span>
                        </button>
                        <button class="btn-sound-delete" data-sound-id="${sound.id}">
                            <span>🗑️</span>
                        </button>
                    </div>
                </div>
            `);
            container.append(soundItem);
        });
    }

    $(document).on('click', '.btn-sound-play', function () {
        const soundId = $(this).data('sound-id');
        const sound = customSounds.find(s => s.id === soundId);
        if (sound) {
            const audio = new Audio(sound.data);
            audio.volume = ringtoneVolume;
            audio.play();
        }
    });

    $(document).on('click', '.btn-sound-delete', function () {
        const soundId = $(this).data('sound-id');
        const sound = customSounds.find(s => s.id === soundId);

        if (confirm(`Are you sure you want to delete "${sound.name}"?`)) {
            customSounds = customSounds.filter(s => s.id !== soundId);
            localStorage.setItem('customSounds', JSON.stringify(customSounds));

            if (currentRingtone === soundId) {
                if (customSounds.length > 0) {
                    updateRingtone(customSounds[0].id);
                } else {
                    currentRingtone = '';
                    localStorage.setItem('ringtone', '');
                    notificationSound = null;
                }
            }

            updateRingtoneDropdown();
            renderCustomSoundsList();
            window.showNotification('Sound deleted successfully', 'success');
        }
    });

    // ================= Update System Info =================
    function updateSystemInfo() {
        const activeCustomers = allCustomers.filter(c => !c.deleted_at).length;
        $('#totalCustomersCount').text(activeCustomers);

        const now = new Date();
        $('#lastUpdated').text(now.toLocaleString('en-MY', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }));
    }

    // ================= IC Auto-format + Auto Age/Gender =================
    function setupICInput(inputSelector, ageSelector, genderSelector) {
        $(inputSelector).on('input', function () {
            let input = $(this);
            let value = input.val();

            let raw = value.replace(/\D/g, '');
            if (raw.length > 12) raw = raw.slice(0, 12);

            let formatted = '';
            if (raw.length > 0) formatted += raw.slice(0, 6);
            if (raw.length > 6) formatted += '-' + raw.slice(6, 8);
            if (raw.length > 8) formatted += '-' + raw.slice(8);

            input.val(formatted);

            if (raw.length >= 6) {
                let yy = parseInt(raw.slice(0, 2));
                let currentYear = new Date().getFullYear();
                let year = yy > (currentYear % 100) ? 1900 + yy : 2000 + yy;
                let age = currentYear - year;
                $(ageSelector).val(age);
            } else {
                $(ageSelector).val('');
            }

            if (raw.length >= 12) {
                let lastDigit = parseInt(raw.slice(-1));
                $(genderSelector).val(lastDigit % 2 === 0 ? 'female' : 'male');
            } else {
                $(genderSelector).val('');
            }
        });
    }

    setupICInput('#icNumberMain', '#ageMain', '#genderMain');
    setupICInput('#icNumber', '#age', '#gender');

    // ================= Render Customer Table =================
    function renderCustomerTable(customers) {
        $('#tableBody').empty();
        $('#resultsCount').text(customers.length);

        if (customers.length === 0) {
            $('#tableBody').html(`
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2rem; color: #a0aec0;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                        <div style="font-size: 1.2rem; font-weight: 600;">No customers found</div>
                        <div style="margin-top: 0.5rem;">Try adjusting your search filters</div>
                    </td>
                </tr>
            `);
            return;
        }

        customers.forEach((customer, index) => {
            const ic = customer.ic ? customer.ic.toString() : '';
            const icFormatted = ic.length >= 12 ?
                ic.slice(0, 6) + '-' + ic.slice(6, 8) + '-' + ic.slice(8) : ic;

            const customerId = customer.unique_id || customer.id;

            const escapedName = (customer.name || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const escapedEmail = (customer.email || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const escapedPhone = (customer.phone || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const escapedAddress = (customer.address?.address_line || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const escapedPostcode = (customer.address?.postcode || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

            const stateId = customer.state?.id || '';
            const stateName = customer.state?.name || '-';

            let actionButtons = '';
            if (customer.deleted_at) {
                actionButtons = `<span class="undo-link" data-id="${customerId}">Undo Delete</span>`;
            } else {
                actionButtons = `
                    <button class="btn btn-warning edit-btn" 
                        data-id="${customerId}"
                        data-ic="${customer.ic || ''}"
                        data-name="${escapedName}"
                        data-email="${escapedEmail}"
                        data-phone="${escapedPhone}"
                        data-state-id="${stateId}"
                        data-state-name="${stateName}"
                        data-address="${escapedAddress}"
                        data-postcode="${escapedPostcode}"
                        data-age="${customer.age || ''}"
                        data-gender="${customer.gender || ''}">
                        ✏️ Edit
                    </button>
                    <button class="btn btn-danger delete-btn" data-id="${customerId}" style="margin-left: 0.5rem;">
                        🗑️ Delete
                    </button>
                `;
            }

            const row = `
                <tr id="customer-${customerId}">
                    <td>${index + 1}</td>
                    <td>${icFormatted}</td>
                    <td>
                        <div class="customer-name">${customer.name || '-'}</div>
                        <div class="customer-email">${customer.email || '-'}</div>
                        <div class="customer-phone">${customer.phone || '-'}</div>
                    </td>
                    <td>${customer.age || '-'}</td>
                    <td style="text-transform: capitalize;">${customer.gender || '-'}</td>
                    <td>${stateName}</td>
                    <td>${customer.address?.address_line || '-'}</td>
                    <td>${customer.address?.postcode || '-'}</td>
                    <td>${actionButtons}</td>
                </tr>
            `;

            $('#tableBody').append(row);
        });
    }

    // ================= Render Receipts Table =================
    function renderReceiptsTable(receipts) {
        $('#receiptsTableBody').empty();
        $('#totalReceiptsCount').text(receipts.length);

        if (receipts.length === 0) {
            $('#receiptsTableBody').html(`
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #a0aec0;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                        <div style="font-size: 1.2rem; font-weight: 600;">No receipts found</div>
                        <div style="margin-top: 0.5rem;">Create your first receipt from the Create Receipt page</div>
                    </td>
                </tr>
            `);
            return;
        }

        receipts.forEach((receipt, index) => {
            const paymentMethodIcons = {
                'cash': '💵',
                'online_banking': '🏦',
                'e-wallet': '📱',
                'credit_card': '💳',
                'debit_card': '💳'
            };

            const icon = paymentMethodIcons[receipt.payment_method] || '💰';
            const methodText = receipt.payment_method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const receiptDate = new Date(receipt.issued_at).toLocaleDateString('en-MY');

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${receipt.receipt_no}</strong></td>
                    <td>${receiptDate}</td>
                    <td>${receipt.customer?.name || '-'}</td>
                    <td><strong>RM ${parseFloat(receipt.total_amount).toFixed(2)}</strong></td>
                    <td>${icon} ${methodText}</td>
                    <td>
                        <button class="btn btn-primary view-receipt-btn" data-receipt-id="${receipt.id}">
                            👁️ View
                        </button>
                        <button class="btn btn-danger delete-receipt-btn" data-receipt-id="${receipt.id}" style="margin-left: 0.5rem;">
                            🗑️ Delete
                        </button>
                    </td>
                </tr>
            `;

            $('#receiptsTableBody').append(row);
        });
    }

    // ================= Edit Customer Modal =================
    $(document).on('click', '.edit-btn', function () {
        const btn = $(this);

        if ($('#customerForm').length) {
            $('#customerForm')[0].reset();
        }

        const customerId = btn.data('id');
        $('#customerId').val(customerId);
        $('#formMethod').val('PUT');
        $('#modalTitle').text('Edit Customer');

        const ic = btn.data('ic') ? btn.data('ic').toString() : '';
        let icFormatted = '';

        if (ic) {
            const icRaw = ic.replace(/\D/g, '');
            if (icRaw.length >= 12) {
                icFormatted = icRaw.slice(0, 6) + '-' + icRaw.slice(6, 8) + '-' + icRaw.slice(8);
            } else {
                icFormatted = ic;
            }
        }
        $('#icNumber').val(icFormatted).prop('readonly', true);

        $('#age').val(btn.data('age') || '');
        $('#gender').val(btn.data('gender') || '');
        $('#customerForm input[name="name"]').val(btn.data('name') || '');
        $('#customerForm input[name="email"]').val(btn.data('email') || '');
        $('#customerForm input[name="phone"]').val(btn.data('phone') || '');
        $('#customerForm input[name="address_line"]').val(btn.data('address') || '');
        $('#customerForm input[name="postcode"]').val(btn.data('postcode') || '');

        const stateId = btn.data('state-id');
        if (stateId) {
            loadStates(stateId);
        } else {
            loadStates();
        }

        currentCustomerData = {
            id: customerId,
            ic: icFormatted,
            name: btn.data('name') || '',
            email: btn.data('email') || '',
            phone: btn.data('phone') || '',
            age: btn.data('age') || '',
            gender: btn.data('gender') || '',
            state: btn.data('state-name') || '',
            address: btn.data('address') || '',
            postcode: btn.data('postcode') || ''
        };

        $('#customerModal').addClass('show');
    });

    // ================= View Receipt =================
    $(document).on('click', '.view-receipt-btn', function () {
        const receiptId = $(this).data('receipt-id');
        const receipt = allReceipts.find(r => r.id == receiptId);

        if (receipt) {
            displayReceipt(receipt);
        }
    });

    // ================= Delete Receipt =================
    $(document).on('click', '.delete-receipt-btn', function () {
        const receiptId = $(this).data('receipt-id');

        if (confirm('Are you sure you want to delete this receipt?')) {
            $.ajax({
                url: `/receipts/${receiptId}`,
                type: 'DELETE',
                success: function (response) {
                    if (response.success) {
                        window.showNotification('Receipt deleted successfully! 🗑️', 'success');
                        loadReceiptsFromServer();
                    }
                },
                error: function (xhr) {
                    let errorMsg = 'Failed to delete receipt.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    window.showNotification(errorMsg, 'error');
                }
            });
        }
    });

    function displayReceipt(receipt) {
        const receiptDate = new Date(receipt.issued_at);
        const formattedDate = receiptDate.toLocaleDateString('en-MY', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        $('#receiptDateDisplay').text(formattedDate);

        const paymentMethodNames = {
            'cash': 'Cash Payment',
            'online_banking': 'Online Banking',
            'e-wallet': 'E-Wallet',
            'credit_card': 'Credit Card',
            'debit_card': 'Debit Card'
        };

        const receiptHTML = `
            <div style="padding: 1.5rem 0; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Receipt Number</div>
                        <div style="font-weight: 600; color: var(--text-primary); font-size: 1.1rem;">${receipt.receipt_no}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Receipt Date</div>
                        <div style="font-weight: 600; color: var(--text-primary);">${receiptDate.toLocaleDateString('en-MY')}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Customer Name</div>
                        <div style="font-weight: 600; color: var(--text-primary);">${receipt.customer?.name || '-'}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">IC Number</div>
                        <div style="font-weight: 600; color: var(--text-primary);">${receipt.customer?.ic || '-'}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Age</div>
                        <div style="font-weight: 600; color: var(--text-primary);">${receipt.customer?.age || '-'} years old</div>
                    </div>
                    <div style="grid-column: 1 / -1; background: var(--input-readonly-bg); padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Amount Paid</div>
                        <div style="font-weight: 700; color: var(--success-color); font-size: 1.8rem;">RM ${parseFloat(receipt.total_amount).toFixed(2)}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Payment Method</div>
                        <div style="font-weight: 600; color: var(--text-primary);">${paymentMethodNames[receipt.payment_method] || receipt.payment_method}</div>
                    </div>
                    ${receipt.notes ? `
                    <div style="grid-column: 1 / -1;">
                        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Description / Notes</div>
                        <div style="font-weight: 600; color: var(--text-primary);">${receipt.notes}</div>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

        $('#receiptBody').html(receiptHTML);
        currentReceiptData = receipt;
        $('#receiptModal').addClass('show');
    }

    // ================= Close Modals =================
    $('#closeCustomerModal').click(function () {
        $('#customerModal').removeClass('show');
        $('#icNumber').prop('readonly', false);
    });

    $('#closeReceiptModal').click(function () {
        $('#receiptModal').removeClass('show');
    });

    $('.modal-overlay').click(function (e) {
        if (e.target === this) {
            $(this).removeClass('show');
            if ($(this).attr('id') === 'customerModal') {
                $('#icNumber').prop('readonly', false);
            }
        }
    });

    // ================= Search Functionality =================

    let searchTimeout = null;

    function performCustomerSearch() {
        const filters = {
            name: $('#searchName').val().trim(),
            ic: $('#searchIC').val().trim().replace(/\D/g, ''),
            email: $('#searchEmail').val().trim(),
            gender: $('#searchGender').val(),
            age_min: $('#searchAgeMin').val(),
            age_max: $('#searchAgeMax').val(),
            state_id: $('#searchState').val()
        };

        Object.keys(filters).forEach(key => {
            if (filters[key] === '' || filters[key] === null || filters[key] === undefined) {
                delete filters[key];
            }
        });

        loadCustomersFromServer(filters, true);
    }

    $('#searchName, #searchIC, #searchEmail, #searchAgeMin, #searchAgeMax').on('input', function () {
        clearTimeout(searchTimeout);
        $('#resultsCount').html('<span style="color: var(--primary-color);">🔍 Searching...</span>');
        searchTimeout = setTimeout(function () {
            performCustomerSearch();
        }, 500);
    });

    $('#searchGender, #searchState').change(function () {
        performCustomerSearch();
    });

    $('#btnSearch').click(function () {
        clearTimeout(searchTimeout);
        performCustomerSearch();
    });

    $('#searchName, #searchIC, #searchEmail, #searchAgeMin, #searchAgeMax').keypress(function (e) {
        if (e.which === 13) {
            clearTimeout(searchTimeout);
            performCustomerSearch();
        }
    });

    // ================= Receipt Search Functionality =================

    let receiptSearchTimeout = null;

    function performReceiptSearch() {
        const filters = {
            receipt_no: $('#searchReceiptNo').val().trim(),
            customer_name: $('#searchReceiptCustomer').val().trim(),
            date_from: $('#searchDateFrom').val(),
            date_to: $('#searchDateTo').val(),
            payment_method: $('#searchPaymentMethod').val(),
            amount_min: $('#searchAmountMin').val(),
            amount_max: $('#searchAmountMax').val()
        };

        Object.keys(filters).forEach(key => {
            if (filters[key] === '' || filters[key] === null || filters[key] === undefined) {
                delete filters[key];
            }
        });

        loadReceiptsFromServer(filters);
    }

    $('#searchReceiptNo, #searchReceiptCustomer, #searchAmountMin, #searchAmountMax').on('input', function () {
        clearTimeout(receiptSearchTimeout);
        $('#totalReceiptsCount').html('<span style="color: var(--primary-color);">🔍 Searching...</span>');
        receiptSearchTimeout = setTimeout(function () {
            performReceiptSearch();
        }, 500);
    });

    $('#searchDateFrom, #searchDateTo, #searchPaymentMethod').change(function () {
        performReceiptSearch();
    });

    $('#btnSearchReceipts').click(function () {
        clearTimeout(receiptSearchTimeout);
        performReceiptSearch();
    });

    $('#btnResetReceiptSearch').click(function () {
        clearTimeout(receiptSearchTimeout);
        $('#searchReceiptNo').val('');
        $('#searchReceiptCustomer').val('');
        $('#searchDateFrom').val('');
        $('#searchDateTo').val('');
        $('#searchPaymentMethod').val('');
        $('#searchAmountMin').val('');
        $('#searchAmountMax').val('');
        loadReceiptsFromServer();
    });

    // ================= Form Submissions (🔥 ALL FIXED WITH window.showNotification!) =================

    $('#customerFormMain').submit(function (e) {
        e.preventDefault();

        const formData = {
            ic: $('#icNumberMain').val().replace(/\D/g, ''),
            name: $('#customerFormMain input[name="name"]').val().trim(),
            email: $('#customerFormMain input[name="email"]').val().trim(),
            phone: $('#customerFormMain input[name="phone"]').val().trim(),
            state_id: $('#selectStateMain').val(),
            address_line: $('#customerFormMain input[name="address_line"]').val().trim(),
            postcode: $('#customerFormMain input[name="postcode"]').val().trim()
        };

        if (!formData.ic || !formData.name || !formData.email || !formData.phone ||
            !formData.state_id || !formData.address_line || !formData.postcode) {
            window.showNotification('Please fill in all required fields', 'error');  // 🔥 FIXED!
            return;
        }

        $.ajax({
            url: '/customers',
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    window.showNotification('Customer added successfully! ✅', 'success');  // 🔥 FIXED!

                    if ($('#customerFormMain').length) {
                        $('#customerFormMain')[0].reset();
                        $('#icNumberMain').trigger('input');
                        $('#selectStateMain').val('');
                    }

                    if (response.data) {
                        allCustomers.unshift(response.data);
                        renderCustomerTable(allCustomers);
                        updateSystemInfo();
                        populateCustomerDropdowns();
                    } else {
                        loadCustomersFromServer({}, true);
                    }
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to add customer. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('<br>');
                }
                window.showNotification(errorMsg, 'error');  // 🔥 FIXED!
            }
        });
    });

    $('#customerForm').submit(function (e) {
        e.preventDefault();

        const method = $('#formMethod').val();
        const customerId = $('#customerId').val();

        let url = '/customers';
        if (method === 'PUT' && customerId) {
            url = `/customers/${customerId}`;
        }

        const formData = {
            ic: $('#icNumber').val().replace(/\D/g, ''),
            name: $('#customerForm input[name="name"]').val().trim(),
            email: $('#customerForm input[name="email"]').val().trim(),
            phone: $('#customerForm input[name="phone"]').val().trim(),
            state_id: $('#selectState').val(),
            address_line: $('#customerForm input[name="address_line"]').val().trim(),
            postcode: $('#customerForm input[name="postcode"]').val().trim()
        };

        if (!formData.ic || !formData.name || !formData.email || !formData.phone ||
            !formData.state_id || !formData.address_line || !formData.postcode) {
            window.showNotification('Please fill in all required fields', 'error');  // 🔥 FIXED!
            return;
        }

        if (method === 'PUT') {
            formData._method = 'PUT';
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    const successMsg = method === 'PUT' ?
                        'Customer updated successfully! ✅' :
                        'Customer added successfully! ✅';

                    window.showNotification(successMsg, 'success');  // 🔥 FIXED!

                    $('#customerModal').removeClass('show');
                    if ($('#customerForm').length) {
                        $('#customerForm')[0].reset();
                        $('#selectState').val('');
                    }
                    $('#icNumber').prop('readonly', false);

                    loadCustomersFromServer({}, true);
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to save customer. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('<br>');
                }
                window.showNotification(errorMsg, 'error');  // 🔥 FIXED!
            }
        });
    });


    $('#createReceiptForm').submit(function (e) {
        e.preventDefault();

        const selectedCustomer = $('#receiptCustomerSelect option:selected');
        const customerData = selectedCustomer.data('customer');

        if (!customerData) {
            window.showNotification('Please select a customer', 'error');  // 🔥 FIXED!
            return;
        }

        const formData = {
            customer_id: selectedCustomer.val(),
            receipt_no: $('#receiptNumber').val(),
            issued_at: $('#receiptDate').val(),
            total_amount: $('#receiptAmount').val(),
            payment_method: $('#receiptPaymentMethod').val(),
            notes: $('#receiptDescription').val()
        };

        if (!formData.receipt_no || !formData.issued_at || !formData.total_amount || !formData.payment_method) {
            window.showNotification('Please fill in all required fields', 'error');  // 🔥 FIXED!
            return;
        }

        $.ajax({
            url: '/receipts',
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    window.showNotification('Receipt created successfully! ✅', 'success');  // 🔥 FIXED!

                    $('#createReceiptForm')[0].reset();
                    $('#receiptNumber').val(generateReceiptNumber());
                    $('#receiptDate').val(new Date().toISOString().split('T')[0]);

                    if (response.data) {
                        allReceipts.unshift(response.data);
                        renderReceiptsTable(allReceipts);
                        displayReceipt(response.data);
                    }
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to create receipt. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('<br>');
                }
                window.showNotification(errorMsg, 'error');  // 🔥 FIXED!
            }
        });
    });

    $(document).on('click', '.delete-btn', function () {
        const customerId = $(this).data('id');

        if (confirm('Are you sure you want to delete this customer?')) {
            $.ajax({
                url: `/customers/${customerId}`,
                type: 'DELETE',
                success: function (response) {
                    if (response.success) {
                        window.showNotification('Customer deleted successfully! 🗑️', 'success');  // 🔥 FIXED!
                        loadCustomersFromServer({}, true);
                    }
                },
                error: function (xhr) {
                    let errorMsg = 'Failed to delete customer.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    window.showNotification(errorMsg, 'error');  // 🔥 FIXED!
                }
            });
        }
    });

    $(document).on('click', '.undo-link', function () {
        const customerId = $(this).data('id');

        $.ajax({
            url: `/customers/${customerId}/restore`,
            type: 'POST',
            success: function (response) {
                if (response.success) {
                    window.showNotification('Customer restored successfully! ↩️', 'success');  // 🔥 FIXED!
                    loadCustomersFromServer({}, true);
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to restore customer.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                window.showNotification(errorMsg, 'error');  // 🔥 FIXED!
            }
        });
    });

    $('#printReceiptBtn').click(function () {
        if (!currentReceiptData) {
            window.showNotification('No receipt data to print', 'error');  // 🔥 FIXED!
            return;
        }

        generatePDFReceipt(currentReceiptData);
    });

    function generatePDFReceipt(receipt) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const receiptDate = new Date(receipt.issued_at);
        const paymentMethodNames = {
            'cash': 'Cash Payment',
            'online_banking': 'Online Banking',
            'e-wallet': 'E-Wallet',
            'credit_card': 'Credit Card',
            'debit_card': 'Debit Card'
        };

        doc.setFillColor(74, 144, 226);
        doc.rect(0, 0, 210, 40, 'F');

        doc.setTextColor(255, 255, 255);
        doc.setFontSize(24);
        doc.setFont(undefined, 'bold');
        doc.text('PAYMENT RECEIPT', 105, 20, { align: 'center' });

        doc.setFontSize(12);
        doc.setFont(undefined, 'normal');
        doc.text('Customer Pro Management System', 105, 30, { align: 'center' });

        doc.setTextColor(0, 0, 0);
        doc.setFontSize(10);
        let yPos = 55;

        doc.setFont(undefined, 'bold');
        doc.text('Receipt Number:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(receipt.receipt_no, 70, yPos);

        yPos += 8;
        doc.setFont(undefined, 'bold');
        doc.text('Date:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(receiptDate.toLocaleDateString('en-MY'), 70, yPos);

        yPos += 10;
        doc.setDrawColor(200, 200, 200);
        doc.line(20, yPos, 190, yPos);

        yPos += 10;
        doc.setFontSize(14);
        doc.setFont(undefined, 'bold');
        doc.text('Customer Details', 20, yPos);

        yPos += 10;
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.text('Name:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(receipt.customer?.name || '-', 70, yPos);

        yPos += 8;
        doc.setFont(undefined, 'bold');
        doc.text('IC Number:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(receipt.customer?.ic || '-', 70, yPos);

        yPos += 8;
        doc.setFont(undefined, 'bold');
        doc.text('Age:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text((receipt.customer?.age || '-') + ' years old', 70, yPos);

        yPos += 10;
        doc.line(20, yPos, 190, yPos);

        yPos += 10;
        doc.setFontSize(14);
        doc.setFont(undefined, 'bold');
        doc.text('Payment Details', 20, yPos);

        yPos += 10;
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.text('Payment Method:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(paymentMethodNames[receipt.payment_method] || receipt.payment_method, 70, yPos);

        if (receipt.notes) {
            yPos += 8;
            doc.setFont(undefined, 'bold');
            doc.text('Notes:', 20, yPos);
            doc.setFont(undefined, 'normal');
            const notes = doc.splitTextToSize(receipt.notes, 120);
            doc.text(notes, 70, yPos);
            yPos += (notes.length * 5);
        }

        yPos += 15;
        doc.setFillColor(240, 249, 255);
        doc.roundedRect(20, yPos, 170, 25, 3, 3, 'F');
        doc.setDrawColor(74, 144, 226);
        doc.setLineWidth(0.5);
        doc.roundedRect(20, yPos, 170, 25, 3, 3, 'S');

        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(74, 144, 226);
        doc.text('Amount Paid', 105, yPos + 10, { align: 'center' });

        doc.setFontSize(20);
        doc.setTextColor(0, 128, 0);
        doc.text(`RM ${parseFloat(receipt.total_amount).toFixed(2)}`, 105, yPos + 20, { align: 'center' });

        yPos += 40;
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.setFont(undefined, 'italic');
        doc.text('Thank you for your payment!', 105, yPos, { align: 'center' });

        yPos += 6;
        doc.setFontSize(8);
        doc.text('This is a computer-generated receipt', 105, yPos, { align: 'center' });

        doc.save(`Receipt_${receipt.receipt_no}.pdf`);
        window.showNotification('Receipt PDF generated successfully! 📄', 'success');  // 🔥 FIXED!
    }
    
    loadCustomSounds();
    renderCustomSoundsList();
    window.showNotification('Welcome to Customer Pro! 🎉', 'info');
    updateSystemInfo();

}); // ← END OF $(function() {}) - PROPER CLOSING!

// ========================================
// 🔥 CODE OUTSIDE document.ready STARTS HERE
// ========================================

// ================= AUTHENTICATION & HEARTBEAT =================
let heartbeatInterval;

function startHeartbeat() {
    heartbeatInterval = setInterval(function () {
        $.ajax({
            url: '/heartbeat',
            type: 'POST',
            success: function (response) {
                if (response.success) {
                    updateOnlineUsers(response.online_users);
                }
            },
            error: function () {
                window.location.href = '/login';
            }
        });
    }, 30000);
}

function updateOnlineUsers(users) {
    const container = $('#onlineUsersList');
    if (!container.length) return;
    
    container.empty();

    if (users.length === 0) {
        container.html('<div style="color: var(--text-muted);">No other admins online</div>');
        return;
    }

    users.forEach(function (user) {
        const userHtml = `
            <div class="online-user-item" style="display: flex; align-items: center; gap: 10px; padding: 10px; margin-bottom: 8px; background: var(--input-readonly-bg); border-radius: 8px;">
                <img src="${user.avatar}" style="width: 40px; height: 40px; border-radius: 50%;">
                <div>
                    <div style="font-weight: 600;">${user.name}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">${user.role_name}</div>
                    <div style="font-size: 0.75rem; color: #4caf50;">🟢 Online - ${user.last_seen}</div>
                </div>
            </div>
        `;
        container.append(userHtml);
    });
}

$(document).ready(function () {
    startHeartbeat();

    $.get('/online-users', function (users) {
        updateOnlineUsers(users);
    });
});

// ================= LOGOUT FORM =================
$(document).on('submit', '#logoutForm', function (e) {
    e.preventDefault();
    
    if (confirm('Are you sure you want to logout?')) {
        this.submit();
    }
});

// ================= AVATAR UPLOAD SYSTEM =================
$(document).ready(function() {
    let selectedAvatarFile = null;

    // Profile trigger click
    $(document).on('click', '#profileTrigger', function (e) {
        e.preventDefault(); // 🔥 CRITICAL
        e.stopPropagation(); // 🔥 CRITICAL
        
        $(this).toggleClass('active');
        $('#profileDropdownMenu').fadeToggle(200);
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.user-profile-dropdown').length) {
            $('#profileTrigger').removeClass('active');
            $('#profileDropdownMenu').fadeOut(200);
        }
    });

    // Prevent dropdown from closing when clicking inside
    $(document).on('click', '#profileDropdownMenu', function (e) {
        e.stopPropagation();
    });

    // Upload avatar button
    $(document).on('click', '#uploadAvatarBtn', function (e) {
        e.preventDefault(); // 🔥 CRITICAL
        e.stopPropagation(); // 🔥 CRITICAL
        
        console.log('📷 Upload avatar clicked');
        $('#profileDropdownMenu').fadeOut(200);
        $('#avatarPreviewModal').addClass('show');
    });

    // Select avatar button
    $(document).on('click', '#selectAvatarBtn', function (e) {
        e.preventDefault();
        console.log('📁 Select file clicked');
        $('#avatarFileInput').click();
    });

    $('#avatarFileInput').change(function (e) {
        const file = e.target.files[0];

        if (!file) return;

        console.log('📂 File selected:', file.name);

        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Please select JPG or PNG only!');
            $(this).val('');
            return;
        }

        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File must be less than 2MB!');
            $(this).val('');
            return;
        }

        $('#avatarPlaceholder').html('<div class="processing-loader">⏳ Processing...</div>');

        compressImage(file, function (compressedFile) {
            selectedAvatarFile = compressedFile;

            const reader = new FileReader();
            reader.onload = function (event) {
                $('#avatarPreview').attr('src', event.target.result);
                $('#avatarPreview').addClass('show');
                $('#avatarPlaceholder').hide();
                $('#uploadAvatarSubmitBtn').show();
            };
            reader.readAsDataURL(compressedFile);

            $('#fileNameDisplay').text(file.name);
            $('#fileSizeDisplay').text(formatFileSize(compressedFile.size) + ' (compressed)');

            const img = new Image();
            img.onload = function () {
                $('#fileDimensionsDisplay').text(`${this.width} x ${this.height}px`);

                if (this.width < 200 || this.height < 200) {
                    alert('Minimum 200x200px required!');
                    resetAvatarPreview();
                }
            };
            img.src = URL.createObjectURL(compressedFile);
        });
    });

    function compressImage(file, callback) {
        const reader = new FileReader();

        reader.onload = function (event) {
            const img = new Image();

            img.onload = function () {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                let width = img.width;
                let height = img.height;
                const maxSize = 500;

                if (width > maxSize || height > maxSize) {
                    if (width > height) {
                        height = (height / width) * maxSize;
                        width = maxSize;
                    } else {
                        width = (width / height) * maxSize;
                        height = maxSize;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function (blob) {
                    const compressedFile = new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    callback(compressedFile);
                }, 'image/jpeg', 0.85);
            };

            img.src = event.target.result;
        };

        reader.readAsDataURL(file);
    }

    $('#uploadAvatarSubmitBtn').click(function () {
        if (!selectedAvatarFile) {
            alert('Please select an image!');
            return;
        }

        console.log('🚀 Starting upload...');

        const btn = $(this);
        const originalText = btn.html();

        showUploadProgress();

        btn.prop('disabled', true).html('⏳ Uploading...');

        const formData = new FormData();
        formData.append('avatar', selectedAvatarFile);

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                updateUploadProgress(percentComplete);
            }
        });

        xhr.addEventListener('load', function () {
            console.log('📥 Upload response received');

            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);

                if (response.success) {
                    console.log('✅ Upload successful!');

                    updateUploadProgress(100);

                    setTimeout(function () {
                        $('#uploadProgressBar').find('.progress-text').html('✅ Upload Complete!');
                        $('#progressFill').css('background', 'linear-gradient(90deg, #48bb78, #38a169)');

                        const newUrl = response.avatar_url + '?t=' + Date.now();
                        console.log('🖼️ Updating avatar:', newUrl);
                        $('.user-avatar, .dropdown-avatar, #userAvatar').attr('src', newUrl);

                        window.showNotification('Avatar uploaded successfully! ✅', 'success');

                        setTimeout(function () {
                            hideUploadProgress();
                            closeAvatarModal();
                            btn.prop('disabled', false).html(originalText);
                        }, 1000);

                    }, 300);

                } else {
                    console.error('❌ Upload failed:', response.message);
                    window.showNotification('Upload failed: ' + response.message, 'error');
                    hideUploadProgress();
                    btn.prop('disabled', false).html(originalText);
                }
            } else {
                console.error('❌ HTTP error:', xhr.status);
                window.showNotification('Upload failed! Server error.', 'error');
                hideUploadProgress();
                btn.prop('disabled', false).html(originalText);
            }
        });

        xhr.addEventListener('error', function () {
            console.error('❌ Network error');
            window.showNotification('Network error! Please check connection.', 'error');
            hideUploadProgress();
            btn.prop('disabled', false).html(originalText);
        });

        xhr.open('POST', '/profile/avatar');
        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        xhr.send(formData);
    });

    function showUploadProgress() {
        if ($('#uploadProgressBar').length === 0) {
            const progressHTML = `
                <div id="uploadProgressBar" style="
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 400px;
                    background: white;
                    border-radius: 15px;
                    padding: 25px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                    z-index: 10001;
                    animation: slideIn 0.3s ease;
                ">
                    <div class="progress-icon" style="text-align: center; font-size: 3rem; margin-bottom: 15px;">
                        📤
                    </div>
                    <div class="progress-text" style="font-weight: 600; margin-bottom: 15px; text-align: center; font-size: 1.1rem;">
                        Uploading Avatar...
                    </div>
                    <div style="
                        width: 100%;
                        height: 10px;
                        background: #e0e0e0;
                        border-radius: 5px;
                        overflow: hidden;
                        margin-bottom: 10px;
                    ">
                        <div id="progressFill" style="
                            width: 0%;
                            height: 100%;
                            background: linear-gradient(90deg, #667eea, #764ba2);
                            transition: width 0.3s ease;
                        "></div>
                    </div>
                    <div id="progressText" style="
                        text-align: center;
                        font-size: 0.9rem;
                        color: #666;
                        font-weight: 600;
                    ">0%</div>
                </div>
                
                <style>
                @keyframes slideIn {
                    from {
                        opacity: 0;
                        transform: translate(-50%, -60%);
                    }
                    to {
                        opacity: 1;
                        transform: translate(-50%, -50%);
                    }
                }
                
                @keyframes slideOut {
                    from {
                        opacity: 1;
                        transform: translate(-50%, -50%);
                    }
                    to {
                        opacity: 0;
                        transform: translate(-50%, -40%);
                    }
                }
                </style>
            `;
            $('body').append(progressHTML);
        }
    }

    function updateUploadProgress(percent) {
        const rounded = Math.round(percent);
        $('#progressFill').css('width', rounded + '%');
        $('#progressText').text(rounded + '%');
    }

    function hideUploadProgress() {
        const progressBar = $('#uploadProgressBar');
        progressBar.css('animation', 'slideOut 0.3s ease');
        setTimeout(function () {
            progressBar.remove();
        }, 300);
    }

    function closeAvatarModal() {
        console.log('🚪 Closing modal');
        $('#avatarPreviewModal').removeClass('show');
        resetAvatarPreview();
    }

    function resetAvatarPreview() {
        selectedAvatarFile = null;
        $('#avatarFileInput').val('');
        $('#avatarPreview').removeClass('show').attr('src', '');
        $('#avatarPlaceholder').html('<span class="placeholder-icon">📷</span><span class="placeholder-text">No image selected</span>').show();
        $('#uploadAvatarSubmitBtn').hide();
        $('#fileNameDisplay').text('-');
        $('#fileSizeDisplay').text('-');
        $('#fileDimensionsDisplay').text('-');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    $('#closeAvatarModal, #cancelAvatarBtn').click(function () {
        closeAvatarModal();
    });

    function updateOnlineUsersCount() {
        $.get('/online-users', function (users) {
            $('#onlineUsersCount').text(Array.isArray(users) ? users.length : 0);
        }).fail(function () { });
    }

    function updateNotificationsCount() {
        $.get('/notifications/unread-count', function (response) {
            if (response.count > 0) {
                $('#notificationBadge').text(response.count).show();
            } else {
                $('#notificationBadge').hide();
            }
        }).fail(function () { });
    }

    updateOnlineUsersCount();
    updateNotificationsCount();
    setInterval(updateOnlineUsersCount, 30000);
    setInterval(updateNotificationsCount, 10000);

    console.log('✅ Avatar upload system initialized with animations');
});

$('head').append(`
<style>
.processing-loader {
    color: #667eea;
    font-weight: 600;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
`);

// ========================================
// 🔥 NOTIFICATION SYSTEM CODE STARTS HERE
// ========================================

// ================= REAL-TIME NOTIFICATION SYSTEM =================
console.log('🔔 Notification system loading...');

let lastNotificationId = localStorage.getItem('lastNotificationId') || 0;

// ================= CHECK FOR NEW NOTIFICATIONS =================
function checkNewNotifications() {
    $.get('/notifications/unread-count', function (response) {
        if (response.success) {
            const count = response.count || 0;

            if (count > 0) {
                $('#notificationBadge').text(count).show();
            } else {
                $('#notificationBadge').hide();
            }

            if (response.latest && response.latest.id > lastNotificationId) {
                lastNotificationId = response.latest.id;
                localStorage.setItem('lastNotificationId', lastNotificationId);

                console.log('🆕 New notification:', response.latest);

                window.showNotification(
                    response.latest.icon + ' ' +
                    response.latest.title + ': ' +
                    response.latest.message,
                    'info'
                );

                if (typeof window.playNotificationSound === 'function') {
                    window.playNotificationSound();
                }

                if ($('#dashboardView').is(':visible')) {
                    console.log('🔄 Refreshing customer list...');
                    setTimeout(function () {
                        if (typeof window.loadCustomersFromServer === 'function') {
                            window.loadCustomersFromServer({}, true);
                        }
                    }, 1000);
                }
            }
        }
    }).fail(function (xhr) {
        console.warn('⚠️ Failed to check notifications:', xhr.status);
    });
}

setInterval(checkNewNotifications, 5000);
setTimeout(checkNewNotifications, 1000);

// ================= NOTIFICATION BELL CLICK =================
$(document).on('click', '#notificationsBell', function (e) {
    e.preventDefault(); // 🔥 CRITICAL
    e.stopPropagation(); // 🔥 CRITICAL
    
    console.log('🔔 Notification bell clicked');

    const dropdown = $('#notificationDropdown');

    if (dropdown.is(':visible')) {
        dropdown.hide();
    } else {
        loadNotificationsList();
        dropdown.show();
    }
});

function loadNotificationsList() {
    console.log('📋 Loading notifications list...');

    $.get('/notifications', function (response) {
        if (response.success) {
            console.log('✅ Loaded', response.data.length, 'notifications');
            renderNotificationsList(response.data);
        }
    }).fail(function () {
        console.error('❌ Failed to load notifications');
        $('#notificationList').html(`
            <div style="padding:30px; text-align:center; color:#999;">
                <div style="font-size:3rem; margin-bottom:10px;">❌</div>
                <div>Failed to load notifications</div>
            </div>
        `);
    });
}

// ================= RENDER NOTIFICATIONS LIST =================
function renderNotificationsList(notifications) {
    const $list = $('#notificationList');
    $list.empty();

    const unreadNotifications = notifications.filter(n => !n.is_read);

    if (unreadNotifications.length === 0) {
        $list.html(`
            <div style="padding:40px 20px; text-align:center;">
                <div style="font-size:4rem; margin-bottom:15px; opacity:0.3;">✅</div>
                <div style="font-size:1.1rem; font-weight:600; color:#4a5568; margin-bottom:8px;">
                    All caught up!
                </div>
                <div style="font-size:0.9rem; color:#a0aec0;">
                    No new notifications
                </div>
            </div>
        `);
        return;
    }

    unreadNotifications.forEach(function (n) {
        let customerId = null;
        if (n.data) {
            try {
                const data = typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
                customerId = data.customer_id;
            } catch (e) {
                console.warn('Failed to parse notification data');
            }
        }

        const item = $(`
            <div class="notification-item-wrapper" data-id="${n.id}" data-customer-id="${customerId || ''}" style="
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 12px 15px;
                border-bottom: 1px solid #eee;
                cursor: pointer;
                transition: background 0.2s;
            ">
                <div style="font-size:1.8rem; flex-shrink:0; line-height:1;">
                    ${n.icon || '📢'}
                </div>
                
                <div class="notification-item-content" style="flex: 1;">
                    <div style="font-weight:600; font-size:0.95rem; color:#2d3748; margin-bottom:4px;">
                        ${n.title}
                    </div>
                    <div style="font-size:0.85rem; color:#718096; line-height:1.4; margin-bottom:6px;">
                        ${n.message}
                    </div>
                    <div style="font-size:0.75rem; color:#cbd5e0;">
                        ${n.created_at_human || 'Just now'}
                    </div>
                </div>
                
                <button class="notification-item-delete" data-id="${n.id}" title="Dismiss" style="
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: transparent;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    transition: all 0.2s;
                    color: #999;
                    font-size: 1.1rem;
                ">
                    🗑️
                </button>
            </div>
        `);

        item.hover(
            function () { $(this).css('background', '#f8f9fa'); },
            function () { $(this).css('background', '#fff'); }
        );

        $list.append(item);
    });
}

// ================= CLICK NOTIFICATION CONTENT =================
$(document).on('click', '.notification-item-content', function (e) {
    e.preventDefault(); // 🔥 CRITICAL
    e.stopPropagation(); // 🔥 CRITICAL
    
    const $wrapper = $(this).closest('.notification-item-wrapper');
    const notifId = $wrapper.data('id');
    const customerId = $wrapper.data('customer-id');
    
    console.log('📌 Notification clicked:', notifId, 'Customer:', customerId);
    
    $.post('/notifications/' + notifId + '/read', function (response) {
        if (response.success) {
            console.log('✅ Notification marked as read');
            
            $wrapper.fadeOut(300, function () {
                $(this).remove();
                
                const remaining = $('#notificationList .notification-item-wrapper').length;
                if (remaining === 0) {
                    $('#notificationList').html(`
                        <div style="padding:40px 20px; text-align:center;">
                            <div style="font-size:4rem; margin-bottom:15px; opacity:0.3;">✅</div>
                            <div style="font-size:1.1rem; font-weight:600; color:#4a5568;">
                                All caught up!
                            </div>
                        </div>
                    `);
                }
            });
            
            checkNewNotifications();
            $('#notificationDropdown').hide();
            
            if (customerId && typeof navigateToCustomer === 'function') {
                console.log('🎯 Navigating to customer:', customerId);
                navigateToCustomer(customerId);
            }
        }
    }).fail(function () {
        console.error('❌ Failed to mark notification as read');
    });
});

// ================= NAVIGATE TO CUSTOMER =================
function navigateToCustomer(customerId) {
    console.log('🔄 Refreshing and scrolling to customer:', customerId);
    
    $('.sidebar-link').removeClass('active');
    $('#dashboardBtn').addClass('active');
    
    if (typeof window.showView === 'function') {
        window.showView('dashboardView');
    } else {
        $('.view-section').hide();
        $('#dashboardView').fadeIn(300);
    }
    
    window.showNotification('Loading customer... 🔄', 'info');
    
    if (typeof window.loadCustomersFromServer === 'function') {
        window.loadCustomersFromServer({}, true);
    }
    
    setTimeout(function () {
        const $customerRow = $(`#customer-${customerId}`);
        
        if ($customerRow.length > 0) {
            console.log('✅ Found customer row, scrolling...');
            
            $('html, body').animate({
                scrollTop: $customerRow.offset().top - 100
            }, 500);
            
            $customerRow.css({
                'background': '#fff3cd',
                'transition': 'background 2s ease'
            });
            
            setTimeout(function () {
                $customerRow.css('background', '');
            }, 2000);
            
            window.showNotification('Customer found! ✅', 'success');
        } else {
            console.warn('⚠️ Customer not found in list');
            window.showNotification('Customer not found in current view', 'error');
        }
    }, 1500);
}

// ================= DELETE SINGLE NOTIFICATION =================
$(document).on('click', '.notification-item-delete', function (e) {
    e.preventDefault(); // 🔥 CRITICAL
    e.stopPropagation(); // 🔥 CRITICAL

    const notifId = $(this).data('id');
    const $wrapper = $(this).closest('.notification-item-wrapper');

    console.log('🗑️ Deleting notification:', notifId);

    $wrapper.css({
        'opacity': '0.5',
        'pointer-events': 'none'
    });

    $.post('/notifications/' + notifId + '/read', function (response) {
        if (response.success) {
            console.log('✅ Notification dismissed');

            $wrapper.css({
                'transform': 'translateX(100%)',
                'opacity': '0',
                'transition': 'all 0.3s ease'
            });

            setTimeout(function () {
                $wrapper.slideUp(200, function () {
                    $(this).remove();

                    const remaining = $('#notificationList .notification-item-wrapper').length;
                    if (remaining === 0) {
                        $('#notificationList').html(`
                            <div style="padding:40px 20px; text-align:center;">
                                <div style="font-size:4rem; margin-bottom:15px; opacity:0.3;">✅</div>
                                <div style="font-size:1.1rem; font-weight:600; color:#4a5568; margin-bottom:8px;">
                                    All cleared!
                                </div>
                            </div>
                        `);
                    }
                });
            }, 300);

            checkNewNotifications();
        }
    }).fail(function (xhr) {
        console.error('❌ Failed to dismiss notification');
        
        $wrapper.css({
            'opacity': '1',
            'pointer-events': 'auto'
        });
        
        window.showNotification('Failed to dismiss notification ❌', 'error');
    });
});

// ================= DELETE ALL BUTTON - FIXED! =================
$(document).on('click', '#deleteAllBtn', function (e) {
    e.preventDefault(); // 🔥 CRITICAL - Stop form submission!
    e.stopPropagation(); // 🔥 CRITICAL - Stop event bubbling!
    
    console.log('🗑️ Delete All button clicked');
    
    const count = $('#notificationList .notification-item-wrapper').length;
    
    if (count === 0) {
        window.showNotification('No notifications to clear', 'info');
        return false; // 🔥 EXTRA SAFETY
    }
    
    if (confirm(`Clear all ${count} notifications?`)) {
        clearAllNotifications();
    }
    
    return false; // 🔥 EXTRA SAFETY - Prevent any form submission
});

// ================= CLEAR ALL NOTIFICATIONS =================
function clearAllNotifications() {
    console.log('🗑️ Clearing all notifications...');

    $.ajax({
        url: '/notifications/delete-all',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.success) {
                console.log('✅ All notifications cleared:', response.deleted_count);

                $('#notificationList').html(`
                    <div style="padding:40px 20px; text-align:center;">
                        <div style="font-size:4rem; margin-bottom:15px; opacity:0.3;">✅</div>
                        <div style="font-size:1.1rem; font-weight:600; color:#4a5568; margin-bottom:8px;">
                            All cleared!
                        </div>
                        <div style="font-size:0.9rem; color:#a0aec0;">
                            ${response.deleted_count} notifications deleted
                        </div>
                    </div>
                `);

                $('#notificationBadge').hide();
                
                window.showNotification(`${response.deleted_count} notifications cleared! ✅`, 'success');
            } else {
                console.error('❌ Failed to clear notifications');
                window.showNotification('Failed to clear notifications ❌', 'error');
            }
        },
        error: function (xhr) {
            console.error('❌ Request failed:', xhr.status, xhr.responseText);
            window.showNotification('Error clearing notifications ❌', 'error');
        }
    });
}

// ================= CLOSE DROPDOWN WHEN CLICKING OUTSIDE =================
$(document).on('click', function (e) {
    if (!$(e.target).closest('#notificationsBell').length && 
        !$(e.target).closest('#notificationDropdown').length) {
        $('#notificationDropdown').hide();
    }
});

// Prevent dropdown from closing when clicking inside
$(document).on('click', '#notificationDropdown', function (e) {
    e.stopPropagation();
});

console.log('✅ Notification system initialized - ALL NOTIFICATIONS WORKING!');