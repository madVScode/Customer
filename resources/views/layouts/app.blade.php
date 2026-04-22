{{-- ============================================================ --}}
{{-- resources/views/layouts/app.blade.php                       --}}
{{-- Layout utama untuk semua halaman admin                      --}}
{{-- Version : 2.7.1                                             --}}
{{-- ============================================================ --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token"                   content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable"       content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color"                  content="#667eea">
    
    <title>@yield('title', 'Customer Management System')</title>

    <script>
    window.currentUserRole = "{{ auth()->user()->role ?? 'guest' }}";
    </script>

    {{-- ── Core Libraries ── --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

    {{-- ── Tom Select ── --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/css/tom-select.default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/js/tom-select.complete.min.js"></script>

    {{-- ── Cropper & Fonts ── --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- ── App Stylesheets ── --}}
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/live_search.css') }}">

    {{-- ── Shared Inline Styles ── --}}
    @include('partials.styles.app-styles')
    @include('partials.styles.tom-select-theme')
    {{-- ── Page-specific head content ── --}}
    @stack('styles')
    {{-- ⚠️ Critical CSS MESTI PALING LAST sekali --}}
    <style>
        .modal-overlay,
        #avatarPreviewModal { display: none !important; }
        .modal-overlay.show,
        #avatarPreviewModal.show { display: flex !important; }
    </style>
</head>
<body>

{{-- Navbar --}}
@include('partials.navbar')

{{-- Notification container --}}
<div class="notification-container" id="notificationContainer"></div>

{{-- Sidebar --}}
@include('partials.sidebar')

{{-- ── Main Content ── --}}
<div class="main-content">
    @yield('content')
</div>

{{-- ── Modals ── --}}
@include('partials.modals.customer-modal')
@include('partials.modals.company-modal')
@include('partials.modals.trip-detail-modal')
@include('partials.modals.edit-booking-modal')
@include('partials.modals.receipt-modal')
@include('partials.modals.edit-trip-modal')

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- JS — Load order PENTING, jangan ubah susunan               --}}
{{-- ════════════════════════════════════════════════════════════ --}}

{{-- 1. Entry point DAHULU — mesti sebelum semua script lain --}}
<script src="{{ asset('js/app.js') }}"></script>

{{-- 2. Utility scripts — selepas app.js --}}
<script src="{{ asset('js/responsive.js') }}"></script>
<script src="{{ asset('js/theme-toggle.js') }}"></script>
<script src="{{ asset('js/image-cropper.js') }}"></script>
<script src="{{ asset('js/company-modal-tabs.js') }}"></script>

{{-- ✅ 3. Notification system — MESTI selepas app.js             --}}
{{--    (app.js define showNotification, notifications.js guna ia) --}}
<script src="{{ asset('js/core/notifications.js') }}"></script>

{{-- 4. Audio system --}}
<script src="{{ asset('js/core/audio.js') }}"></script>

{{-- 5. Customer CRUD --}}
<script src="{{ asset('js/core/customers.js') }}"></script>

{{-- 6. Feature modules --}}
<script src="{{ asset('js/modules/companies.js') }}"></script>
<script src="{{ asset('js/modules/trips.js') }}"></script>
<script src="{{ asset('js/core/receipts.js') }}"></script>
<script src="{{ asset('js/modules/bookings.js') }}"></script>

{{-- 7. Settings, heartbeat, avatar --}}
<script src="{{ asset('js/core/settings.js') }}"></script>
<script src="{{ asset('js/core/heartbeat.js') }}"></script>
<script src="{{ asset('js/core/avatar.js') }}"></script>

{{-- 8. Dashboard — sidebar guard, mobile menu, online users --}}
<script src="{{ asset('js/core/dashboard.js') }}"></script>

{{-- ── TomSelect Init ── --}}
@include('partials.scripts.tom-select-init')

{{-- ✅ Tutorial — hanya untuk role tester --}}
@if(auth()->user()->role === 'tester')
<script src="{{ asset('js/core/tutorial.js') }}"></script>
@endif

{{-- ── Page-specific scripts ── --}}
@stack('scripts')

</body>
</html>