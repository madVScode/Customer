<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk — Portal Pelancong</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',sans-serif;
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem 1rem;
        }
        .card { background:white; border-radius:20px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 25px 60px rgba(0,0,0,0.3); }
        .brand { text-align:center; margin-bottom:2rem; }
        .brand-icon { font-size:3rem; display:block; margin-bottom:0.5rem; }
        .brand h1 { font-size:1.6rem; font-weight:800; color:#2d3748; }
        .brand p { color:#718096; font-size:0.9rem; }
        .form-group { margin-bottom:1.25rem; }
        .form-label { display:block; font-size:0.85rem; font-weight:600; color:#4a5568; margin-bottom:0.5rem; }
        .form-input { width:100%; padding:0.75rem 1rem; border:2px solid #e2e8f0; border-radius:10px; font-family:inherit; font-size:0.95rem; transition:border-color 0.2s; }
        .form-input:focus { outline:none; border-color:#00d4aa; }
        .btn-submit { width:100%; padding:0.9rem; background:linear-gradient(135deg,#0f2027,#2c5364); color:white; border:none; border-radius:10px; font-family:inherit; font-size:1rem; font-weight:700; cursor:pointer; transition:all 0.2s; }
        .btn-submit:hover { opacity:0.9; transform:translateY(-1px); }
        .alert { background:#fff5f5; border:1px solid #fc8181; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.25rem; color:#c53030; font-size:0.85rem; }
        .link-register { text-align:center; font-size:0.9rem; color:#718096; margin-top:1.25rem; }
        .link-register a { color:#00d4aa; font-weight:600; text-decoration:none; }
        .divider { text-align:center; color:#a0aec0; font-size:0.85rem; margin:1rem 0; position:relative; }
        .divider::before, .divider::after { content:''; position:absolute; top:50%; width:43%; height:1px; background:#e2e8f0; }
        .divider::before { left:0; } .divider::after { right:0; }
        .back-link { display:inline-flex; align-items:center; gap:0.4rem; color:#718096; text-decoration:none; font-size:0.85rem; margin-bottom:1.5rem; }
        .back-link:hover { color:#2d3748; }
    </style>
</head>
<body>
<div class="card">
    <a href="{{ route('tourist.index') }}" class="back-link">← Lihat Trip</a>

    <div class="brand">
        <span class="brand-icon">✈️</span>
        <h1>Log Masuk</h1>
        <p>Portal Pelancong Arson Trip</p>
    </div>

    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #6ee7b7;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;color:#065f46;font-size:0.85rem;">
            ✅ {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tourist.login.submit') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Alamat Emel</label>
            <input type="email" name="email" class="form-input" placeholder="emel@contoh.com" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">Kata Laluan</label>
            <input type="password" name="password" class="form-input" placeholder="Kata laluan anda" required>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1.25rem;">
            <input type="checkbox" name="remember" id="remember" style="width:16px;height:16px;accent-color:#00d4aa;">
            <label for="remember" style="font-size:0.85rem;color:#718096;cursor:pointer;">Ingat saya</label>
        </div>
        <button type="submit" class="btn-submit">🔑 Log Masuk</button>
    </form>

    <div class="divider">atau</div>
    <div class="link-register">
        Belum ada akaun? <a href="{{ route('tourist.register') }}">Daftar percuma</a>
    </div>
</div>
</body>
</html>