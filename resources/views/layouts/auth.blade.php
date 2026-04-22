{{-- ============================================================ --}}
{{-- resources/views/layouts/auth.blade.php                      --}}
{{-- Layout untuk halaman login / reset password                 --}}
{{-- ============================================================ --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2D8CFF">
    <title>@yield('title', 'Sign In - Customer Pro')</title>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>