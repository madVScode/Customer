<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akaun Pelancong</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',sans-serif;
            background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            padding:1rem;
        }
        .card {
            background:white; border-radius:20px; padding:2.5rem;
            width:100%; max-width:440px;
            box-shadow:0 25px 60px rgba(0,0,0,0.4);
        }
        .brand {
            text-align:center; margin-bottom:2rem;
        }
        .brand h1 { font-size:1.8rem; font-weight:800; color:#0f2027; }
        .brand h1 span { color:#00d4aa; }
        .brand p { color:#718096; font-size:0.88rem; margin-top:0.25rem; }

        .form-group { margin-bottom:1.1rem; }
        label { display:block; font-size:0.82rem; font-weight:700; color:#4a5568; margin-bottom:0.4rem; }
        input {
            width:100%; padding:0.75rem 1rem;
            border:2px solid #e2e8f0; border-radius:10px;
            font-family:inherit; font-size:0.9rem; color:#2d3748;
            transition:border-color 0.2s;
            outline:none;
        }
        input:focus { border-color:#00d4aa; }
        input.is-invalid { border-color:#e53e3e; }

        .invalid-feedback { color:#e53e3e; font-size:0.78rem; margin-top:0.3rem; }

        .btn-submit {
            width:100%; padding:0.85rem;
            background:linear-gradient(135deg,#0f2027,#2c5364);
            color:white; border:none; border-radius:12px;
            font-family:inherit; font-size:1rem; font-weight:700;
            cursor:pointer; margin-top:0.5rem; transition:all 0.2s;
        }
        .btn-submit:hover { opacity:0.9; transform:translateY(-1px); }

        .divider { text-align:center; color:#a0aec0; font-size:0.85rem; margin:1.25rem 0; }

        .login-link {
            display:block; text-align:center;
            color:#718096; font-size:0.88rem;
        }
        .login-link a { color:#00d4aa; font-weight:700; text-decoration:none; }
        .login-link a:hover { text-decoration:underline; }

        .back-link-top {
            display:inline-flex; align-items:center; gap:0.35rem;
            color:#718096; font-size:0.82rem; font-weight:600;
            text-decoration:none; margin-bottom:1.25rem;
            padding:0.35rem 0.75rem; border-radius:8px;
            background:#f7fafc; border:1px solid #e2e8f0;
            transition:all 0.2s;
        }
        .back-link-top:hover { background:#edf2f7; color:#2d3748; transform:translateX(-2px); }

        .alert-error {
            background:#fff5f5; border:1px solid #feb2b2; border-radius:10px;
            padding:0.75rem 1rem; margin-bottom:1.25rem; color:#c53030; font-size:0.85rem;
        }

        .row-2 { display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; }

        @media(max-width:480px) {
            .card { padding:1.75rem; }
            .row-2 { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<div class="card">
    <a href="{{ route('tourist.index') }}" class="back-link-top">← Kembali ke Laman Utama</a>
    <div class="brand">
        <h1>✈️ Arson<span>Trip</span></h1>
        <p>Daftar akaun baharu untuk mula menempah trip</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>⚠️ {{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- ✅ FIX: Buang company_id — tourist daftar tanpa pilih syarikat --}}
    <form method="POST" action="{{ route('tourist.register.submit') }}">
        @csrf

        <div class="row-2">
            <div class="form-group">
                <label>Nama Penuh *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    placeholder="Nama anda"
                    class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                    required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>No. Telefon *</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    placeholder="0112585521"
                    class="{{ $errors->has('phone') ? 'is-invalid' : '' }}"
                    required>
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label>Alamat Emel *</label>
            <input type="email" name="email" value="{{ old('email') }}"
                placeholder="email@example.com"
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row-2">
            <div class="form-group">
                <label>Kata Laluan *</label>
                <input type="password" name="password"
                    placeholder="Min. 8 aksara"
                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Sahkan Kata Laluan *</label>
                <input type="password" name="password_confirmation"
                    placeholder="Ulang kata laluan"
                    required>
            </div>
        </div>

        <button type="submit" class="btn-submit">🚀 Daftar Sekarang</button>
    </form>

    <div class="divider">— atau —</div>

    <p class="login-link">
        Sudah ada akaun? <a href="{{ route('tourist.login') }}">Log masuk di sini</a>
    </p>
</div>

</body>
</html>