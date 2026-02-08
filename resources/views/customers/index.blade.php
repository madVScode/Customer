<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Management System</title>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- External CSS -->
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/live_search.css') }}">
</head>
<body>

<!-- ================= Notification Container ================= -->
@include('partials.navbar')
<div class="notification-container" id="notificationContainer"></div>

<!-- ================= Sidebar ================= -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-title">Customer Management</div>
    </div>
    <div class="sidebar-menu">
        <a href="#" class="sidebar-link active" id="dashboardBtn">
            <span class="sidebar-icon">📊</span>
            <span>Dashboard</span>
        </a>
        <a href="#" class="sidebar-link" id="customersBtn">
            <span class="sidebar-icon">➕</span>
            <span>Add Customer</span>
        </a>
        <a href="#" class="sidebar-link" id="createReceiptBtn">
            <span class="sidebar-icon">🧾</span>
            <span>Create Receipt</span>
        </a>
        <a href="#" class="sidebar-link" id="viewReceiptsBtn">
            <span class="sidebar-icon">📄</span>
            <span>View Receipts</span>
        </a>
        <a href="#" class="sidebar-link" id="settingsBtn">
            <span class="sidebar-icon">⚙️</span>
            <span>Settings</span>
        </a>
    </div>
</div>
<div class="sidebar-menu">
    <!-- Existing menu items -->
    <a href="#" class="sidebar-link active" id="dashboardBtn">...</a>
    <!-- ... -->
    
    <!-- ADD THIS AT THE BOTTOM -->
    <div style="border-top: 1px solid var(--border-color); margin-top: auto; padding-top: 1rem;">
        <form id="logoutForm" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-link" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer;">
                <span class="sidebar-icon">🚪</span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>

<!-- ================= Main Content ================= -->
<div class="main-content">

    <!-- ================= DASHBOARD VIEW ================= -->
    <div id="dashboardView" class="view-section">
        <h1 class="page-title">📊 Dashboard - Search Results</h1>
        <div class="content-card" id="onlineUsersWidget">
    <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">
        🟢 Online Admins
    </h2>
    <div id="onlineUsersList">
        <!-- Will be populated by JavaScript -->
    </div>
</div>

        <!-- ================= Search / Filter Section ================= -->
        <div class="content-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin: 0;">🔍 Search & Filter</h2>
                <span class="live-search-badge">Live Search</span>
            </div>
            
            <div class="instant-search-label">
                <strong>Instant Search Active:</strong> Results update automatically as you type
            </div>
            
            <div class="search-grid">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input id="searchName" type="text" placeholder="e.g., Ahmad bin Abdullah" class="form-input">
                    <div class="search-tips">Type to search instantly...</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">IC Number</label>
                    <input id="searchIC" type="text" placeholder="e.g., 950101-01-1234" class="form-input">
                    <div class="search-tips">Auto-search as you type</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input id="searchEmail" type="email" placeholder="e.g., customer@email.com" class="form-input">
                    <div class="search-tips">Instant results</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select id="searchGender" class="form-select">
                        <option value="">All Genders</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Min Age</label>
                    <input id="searchAgeMin" type="number" placeholder="e.g., 18" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Max Age</label>
                    <input id="searchAgeMax" type="number" placeholder="e.g., 65" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">State</label>
                    <select id="searchState" class="form-select">
                        <option value="">All States</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button id="btnSearch" class="btn btn-primary" style="width: 100%;">
                        🔍 Search Now
                    </button>
                </div>
            </div>
            
            <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: var(--input-readonly-bg); border-radius: 8px; font-size: 0.875rem; color: var(--text-muted); border-left: 3px solid var(--success-color);">
                💡 <strong>Tip:</strong> Press <kbd style="padding: 0.2rem 0.5rem; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 3px;">Enter</kbd> for instant search
            </div>
        </div>

        <!-- ================= Customer Table ================= -->
        <div class="content-card">
            <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem;">👥 Search Results</h2>
            
            <div id="searchResultsInfo" style="margin-bottom: 1rem; padding: 1rem; background: var(--input-readonly-bg); border-radius: 8px; color: var(--text-secondary); border: 1px solid var(--border-color);">
                <strong>Total Results:</strong> <span id="resultsCount">0</span> customer(s) found
            </div>
            
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>IC Number</th>
                            <th>Customer Information</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>State</th>
                            <th>Address</th>
                            <th>Postcode</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= ADD CUSTOMER VIEW ================= -->
<div id="addCustomerView" class="view-section" style="display: none;">
    <h1 class="page-title">➕ Add New Customer</h1>

    <div class="content-card">
        <form id="customerFormMain">
            @csrf

            <div class="form-grid">
                <!-- IC -->
                <div class="form-group">
                    <label class="form-label">IC Number *</label>
                    <input id="icNumberMain" name="ic" type="text"
                           placeholder="e.g., 950101-01-1234"
                           class="form-input"
                           maxlength="14" required>
                    <small class="text-muted">Format: YYMMDD-PB-XXXX</small>
                </div>

                <!-- AGE (DISPLAY ONLY) -->
                <div class="form-group">
                    <label class="form-label">Age</label>
                    <input id="ageMain" type="text"
                           placeholder="Auto-calculated from IC"
                           class="form-input" readonly>
                </div>

                <!-- GENDER -->
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <input id="genderMain" name="gender" type="text"
                           placeholder="Auto-detected from IC"
                           class="form-input" readonly>
                </div>

                <!-- HIDDEN DOB -->
                <input type="hidden" id="dobMain" name="dob">

                <!-- NAME -->
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input name="name" type="text"
                           class="form-input"
                           required>
                </div>

                <!-- EMAIL -->
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input name="email" type="email"
                           class="form-input" required>
                </div>

                <!-- PHONE -->
                <div class="form-group">
                    <label class="form-label">Phone *</label>
                    <input name="phone" type="tel"
                           class="form-input" required>
                </div>

                <!-- STATE -->
<div class="form-group">
    <label class="form-label">State *</label>
    <select id="selectStateMain" name="state_id" class="form-select" required>
        <option value="">Select State</option>
        @foreach($states as $state)
            <option value="{{ $state->id }}"
                {{ (old('state_id', $customer->state_id ?? '') == $state->id) ? 'selected' : '' }}>
                {{ $state->name }}
            </option>
        @endforeach
    </select>
</div>


                <!-- ADDRESS -->
                <div class="form-group">
                    <label class="form-label">Address *</label>
                    <input name="address_line" type="text"
                           class="form-input" required>
                </div>

                <!-- POSTCODE -->
                <div class="form-group">
                    <label class="form-label">Postcode *</label>
                    <input name="postcode" type="text"
                           maxlength="5"
                           class="form-input" required>
                </div>
            </div>

            <div class="text-center mt-8">
                <button type="submit" class="btn btn-success btn-lg">
                    💾 Save Customer
                </button>
                <button type="reset" class="btn btn-warning btn-lg ml-4">
                    🔄 Reset
                </button>
            </div>
        </form>
    </div>
</div>


    <!-- ================= CREATE RECEIPT VIEW ================= -->
    <div id="createReceiptView" class="view-section" style="display: none;">
        <h1 class="page-title">🧾 Create Receipt</h1>

        <div class="content-card">
            <form id="createReceiptForm">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Select Customer *</label>
                        <select id="receiptCustomerSelect" class="form-select" required>
                            <option value="">-- Select Customer --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Receipt Number</label>
                        <input id="receiptNumber" type="text" class="form-input" readonly>
                        <small style="color: #718096; font-size: 0.75rem;">Auto-generated</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Receipt Date *</label>
                        <input id="receiptDate" type="date" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Amount Paid (RM) *</label>
                        <input id="receiptAmount" type="number" step="0.01" min="0" placeholder="e.g., 150.00" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment Method *</label>
                        <select id="receiptPaymentMethod" class="form-select" required>
                            <option value="">-- Select Payment Method --</option>
                            <option value="cash">💵 Cash</option>
                            <option value="online_banking">🏦 Online Banking</option>
                            <option value="e-wallet">📱 E-Wallet (Touch'n Go, GrabPay, etc)</option>
                            <option value="credit_card">💳 Credit Card</option>
                            <option value="debit_card">💳 Debit Card</option>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Description / Notes</label>
                        <textarea id="receiptDescription" rows="3" placeholder="e.g., Payment for service, product purchase, etc." class="form-input"></textarea>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="submit" class="btn btn-success btn-lg">
                        💾 Create Receipt
                    </button>
                    <button type="reset" class="btn btn-warning btn-lg" style="margin-left: 1rem;">
                        🔄 Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= VIEW RECEIPTS VIEW (UPDATED WITH SEARCH/FILTER) ================= -->
    <div id="viewReceiptsView" class="view-section" style="display: none;">
        <h1 class="page-title">📄 View Receipts</h1>

        <!-- ================= Search / Filter Section ================= -->
        <div class="content-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin: 0;">🔍 Search & Filter Receipts</h2>
                <span class="live-search-badge">Live Search</span>
            </div>
            
            <div class="instant-search-label">
                <strong>Instant Search Active:</strong> Results update automatically as you type or change filters
            </div>
            
            <div class="search-grid">
                <div class="form-group">
                    <label class="form-label">Receipt Number</label>
                    <input id="searchReceiptNo" type="text" placeholder="e.g., RCP-12345678-001" class="form-input">
                    <div class="search-tips">Type to search instantly...</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Customer Name</label>
                    <input id="searchReceiptCustomer" type="text" placeholder="e.g., Ahmad bin Abdullah" class="form-input">
                    <div class="search-tips">Auto-search as you type</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date From</label>
                    <input id="searchDateFrom" type="date" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date To</label>
                    <input id="searchDateTo" type="date" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select id="searchPaymentMethod" class="form-select">
                        <option value="">All Methods</option>
                        <option value="cash">💵 Cash</option>
                        <option value="online_banking">🏦 Online Banking</option>
                        <option value="e-wallet">📱 E-Wallet</option>
                        <option value="credit_card">💳 Credit Card</option>
                        <option value="debit_card">💳 Debit Card</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Min Amount (RM)</label>
                    <input id="searchAmountMin" type="number" step="0.01" placeholder="e.g., 0.00" class="form-input">
                    <div class="search-tips">Instant filter</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Max Amount (RM)</label>
                    <input id="searchAmountMax" type="number" step="0.01" placeholder="e.g., 10000.00" class="form-input">
                    <div class="search-tips">Instant filter</div>
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end; gap: 0.5rem;">
                    <button id="btnSearchReceipts" class="btn btn-primary" style="flex: 1;">
                        🔍 Search
                    </button>
                    <button id="btnResetReceiptSearch" class="btn btn-secondary" style="flex: 1;">
                        🔄 Reset
                    </button>
                </div>
            </div>
            
            <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: var(--input-readonly-bg); border-radius: 8px; font-size: 0.875rem; color: var(--text-muted); border-left: 3px solid var(--success-color);">
                💡 <strong>Tip:</strong> All filters work instantly - just type or select and watch the results update!
            </div>
        </div>

        <!-- ================= Receipts Table ================= -->
        <div class="content-card">
            <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem;">📋 All Receipts</h2>
            
            <div id="receiptsInfo" style="margin-bottom: 1rem; padding: 1rem; background: var(--input-readonly-bg); border-radius: 8px; color: var(--text-secondary); border: 1px solid var(--border-color);">
                <strong>Total Receipts:</strong> <span id="totalReceiptsCount">0</span>
            </div>
            
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Amount (RM)</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="receiptsTableBody">
                        <!-- Data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= SETTINGS VIEW ================= -->
    <div id="settingsView" class="view-section" style="display: none;">
        <h1 class="page-title">⚙️ Settings</h1>

        <div class="settings-container">
            <!-- Appearance Settings -->
            <div class="settings-section">
                <h3>🎨 Appearance</h3>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-title">
                            <span id="themeIcon">🌙</span>
                            <span id="themeText">Dark Mode</span>
                        </div>
                        <div class="setting-description">
                            Switch between light and dark theme for comfortable viewing
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Sound & Notifications -->
            <div class="settings-section">
                <h3>🔔 Sound & Notifications</h3>
                
                <!-- Upload Custom Sound (MOVED TO TOP) -->
                <div class="setting-item" style="flex-direction: column; align-items: flex-start; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
                    <div class="setting-title" style="margin-bottom: 0.5rem; color: white; font-size: 1.1rem;">
                        📤 Upload Notification Sound
                    </div>
                    <div class="setting-description" style="margin-bottom: 1rem; color: rgba(255,255,255,0.9);">
                        Upload your own MP3 or WAV files to use as notification sounds (max 5MB per file)
                    </div>
                    <button id="uploadSoundBtn" class="btn btn-primary" style="background: white; color: #667eea; font-weight: 600;">
                        📤 Choose Sound File
                    </button>
                    <input type="file" id="soundFileInput" accept="audio/*" style="display: none;">
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-title">
                            🎵 Active Notification Ringtone
                        </div>
                        <div class="setting-description">
                            Select which uploaded sound to use for notifications
                        </div>
                    </div>
                    <div class="ringtone-controls">
                        <select id="ringtoneSelect" class="ringtone-select">
                            <option value="">No ringtones uploaded yet</option>
                        </select>
                        <button id="testRingtoneBtn" class="test-ringtone-btn">
                            ▶️ Test
                        </button>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-title">
                            🔊 Notification Volume
                        </div>
                        <div class="setting-description">
                            Adjust the volume level for notification sounds
                        </div>
                    </div>
                    <div class="volume-control">
                        <span class="volume-icon">🔇</span>
                        <div class="volume-slider-container">
                            <input type="range" min="0" max="100" value="50" class="volume-slider" id="volumeSlider">
                        </div>
                        <span class="volume-icon">🔊</span>
                        <span class="volume-value" id="volumeValue">50%</span>
                    </div>
                </div>

                <!-- Uploaded Sounds List -->
                <div class="setting-item" style="flex-direction: column; align-items: flex-start;">
                    <div class="setting-title" style="margin-bottom: 1rem;">
                        📋 My Uploaded Sounds
                    </div>
                    
                    <div id="customSoundsList" style="width: 100%;">
                        <!-- Custom sounds will be rendered here -->
                    </div>
                </div>
            </div>

                

            <!-- System Information -->
            <div class="settings-section">
                <h3>ℹ️ System Information</h3>
                
                <div class="setting-item" style="flex-direction: column; align-items: flex-start;">
                    <div class="setting-title" style="margin-bottom: 1rem;">
                        📊 Application Details
                    </div>
                    <div style="width: 100%; color: var(--text-secondary); line-height: 1.8;">
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span><strong>Version:</strong></span>
                            <span>2.1.0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span><strong>Total Customers:</strong></span>
                            <span id="totalCustomersCount">0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span><strong>Last Updated:</strong></span>
                            <span id="lastUpdated">Just now</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <span><strong>Current Theme:</strong></span>
                            <span id="currentTheme">Light Mode</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About -->
            <div class="settings-section">
                <h3>📖 About</h3>
                
                <div class="setting-item" style="flex-direction: column; align-items: flex-start;">
                    <div class="setting-title" style="margin-bottom: 1rem;">
                        🏢 Customer Pro Management System
                    </div>
                    <div style="color: var(--text-secondary); line-height: 1.8;">
                        <p style="margin-bottom: 0.75rem;">
                            A modern, professional customer management system designed for efficient data handling and beautiful user experience.
                        </p>
                        <p style="margin-bottom: 0.75rem;">
                            <strong>Features:</strong>
                        </p>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1rem;">
                            <li>Advanced search and filtering</li>
                            <li>Auto IC validation and data extraction</li>
                            <li>Professional receipt generation with PDF export</li>
                            <li>Dark/Light theme support</li>
                            <li>Customizable notification sounds with upload feature</li>
                            <li>Responsive and modern design</li>
                        </ul>
                        <p style="color: var(--text-muted); font-size: 0.875rem;">
                            © 2026 Customer Pro.Daniel All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ================= Add/Edit Customer Modal ================= -->
<div class="modal-overlay" id="customerModal">
    <div class="modal-content">
        <span class="modal-close" id="closeCustomerModal">✖</span>
        <h2 class="modal-header" id="modalTitle">Add New Customer</h2>

        <form id="customerForm">
            <input type="hidden" id="formMethod" value="POST">
            <input type="hidden" id="customerId">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">IC Number *</label>
                    <input id="icNumber" name="ic" type="text" placeholder="e.g., 950101-01-1234" class="form-input" maxlength="14" required>
                    <small style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Format: YYMMDD-PB-XXXX</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Age</label>
                    <input id="age" name="age" type="text" placeholder="Auto-calculated from IC" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <input id="gender" name="gender" type="text" placeholder="Auto-detected from IC" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input name="name" type="text" placeholder="e.g., Ahmad bin Abdullah" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input name="email" type="email" placeholder="e.g., ahmad@email.com" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input name="phone" type="tel" placeholder="e.g., 012-3456789" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">State *</label>
                    <select id="selectState" name="state_id" class="form-select" required>
                        <option value="">Select State</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Address Line *</label>
                    <input name="address_line" type="text" placeholder="e.g., No. 123, Jalan Merdeka" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Postcode *</label>
                    <input name="postcode" type="text" placeholder="e.g., 50000" class="form-input" maxlength="5" required>
                </div>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn btn-success btn-lg">
                    💾 Save Customer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= Receipt Preview Modal ================= -->
<div class="modal-overlay" id="receiptModal">
    <div class="modal-content" style="max-width: 800px;">
        <span class="modal-close" id="closeReceiptModal">✖</span>
        
        <div class="receipt-container" id="receiptContent">
            <div class="receipt-header">
                <div class="receipt-title">PAYMENT RECEIPT</div>
                <div class="receipt-subtitle">Customer Pro Management System</div>
                <div style="margin-top: 0.5rem; color: #a0aec0; font-size: 0.85rem;" id="receiptDateDisplay"></div>
            </div>

            <div id="receiptBody">
                <!-- Receipt content will be populated here -->
            </div>

            <div class="receipt-footer">
                <p>Thank you for your payment!</p>
                <p style="margin-top: 0.5rem;">This is a computer-generated receipt</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <button id="printReceiptBtn" class="btn btn-primary btn-lg">
                📄 Download PDF Receipt
            </button>
        </div>
    </div>
</div>

<!-- ================= Load app.js ================= -->
<script src="{{ asset('js/app.js') }}"></script>
@auth
    @if(auth()->user()->isAdminManage())
        <!-- Show Add, Edit, Delete buttons -->
        <button class="btn btn-success add-customer-btn">Add Customer</button>
    @else
        <!-- Hide CRUD buttons for Admin Record -->
        <button class="btn btn-primary export-btn">Export Data</button>
    @endif
@endauth
</body>
</html>