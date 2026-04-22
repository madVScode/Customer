<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2D8CFF">
    <title>Reset Password - Customer Pro</title>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --zoom-blue: #2D8CFF;
            --zoom-blue-hover: #0B5CFF;
            --zoom-dark: #1C1C1E;
            --zoom-gray: #747487;
            --zoom-light-gray: #F5F5F7;
            --zoom-border: #E5E5EA;
            --success: #34C759;
            --error: #FF3B30;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
        }

        .reset-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .reset-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 480px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reset-header {
            text-align: center;
            padding: 48px 40px 32px;
        }

        .reset-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, var(--zoom-blue) 0%, #0B5CFF 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            box-shadow: 0 8px 24px rgba(45, 140, 255, 0.3);
        }

        .reset-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--zoom-dark);
            margin-bottom: 8px;
        }

        .reset-subtitle {
            font-size: 15px;
            color: var(--zoom-gray);
            font-weight: 500;
        }

        .reset-body {
            padding: 0 40px 40px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: none;
            font-size: 14px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background: #FFE5E5;
            color: #D32F2F;
            border: 1px solid #FFCDD2;
        }

        .alert-success {
            background: #E8F5E9;
            color: #388E3C;
            border: 1px solid #C8E6C9;
        }

        .info-box {
            background: #E3F2FD;
            border: 1px solid #BBDEFB;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            color: #1976D2;
            font-size: 13px;
            line-height: 1.6;
        }

        .info-box strong {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .info-box li {
            margin: 4px 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--zoom-dark);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--zoom-border);
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            color: var(--zoom-dark);
            transition: all 0.2s;
            background: var(--zoom-light-gray);
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--zoom-blue);
            background: white;
            box-shadow: 0 0 0 3px rgba(45, 140, 255, 0.1);
        }

        .form-input:read-only {
            background: #F0F0F0;
            cursor: not-allowed;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            padding: 6px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .password-toggle:hover {
            background: var(--zoom-light-gray);
        }

        .password-strength {
            margin-top: 12px;
            display: none;
        }

        .strength-bar {
            height: 6px;
            background: var(--zoom-border);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 3px;
        }

        .strength-text {
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        .strength-weak .strength-fill {
            width: 33%;
            background: var(--error);
        }

        .strength-weak .strength-text {
            color: var(--error);
        }

        .strength-medium .strength-fill {
            width: 66%;
            background: #FF9500;
        }

        .strength-medium .strength-text {
            color: #FF9500;
        }

        .strength-strong .strength-fill {
            width: 100%;
            background: var(--success);
        }

        .strength-strong .strength-text {
            color: var(--success);
        }

        .btn-reset {
            width: 100%;
            padding: 16px;
            background: var(--zoom-blue);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 12px rgba(45, 140, 255, 0.3);
            margin-top: 8px;
        }

        .btn-reset:hover {
            background: var(--zoom-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(45, 140, 255, 0.4);
        }

        .btn-reset:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-back {
            width: 100%;
            padding: 14px;
            background: var(--zoom-light-gray);
            color: var(--zoom-dark);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 8px;
        }

        .btn-back:hover {
            background: var(--zoom-border);
        }

        .loading-spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 768px) {
            .reset-wrapper {
                padding: 15px;
            }

            .reset-card {
                max-width: 100%;
            }

            .reset-header {
                padding: 36px 28px 24px;
            }

            .reset-body {
                padding: 0 28px 32px;
            }

            .reset-icon {
                width: 70px;
                height: 70px;
                font-size: 2.5rem;
            }

            .reset-title {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .reset-header {
                padding: 32px 24px 20px;
            }

            .reset-body {
                padding: 0 24px 28px;
            }

            .reset-title {
                font-size: 22px;
            }

            .form-input {
                padding: 12px 14px;
                font-size: 14px;
            }
        }

        @media (max-height: 600px) and (orientation: landscape) {
            .reset-wrapper {
                padding: 10px;
            }

            .reset-header {
                padding: 24px 32px 16px;
            }

            .reset-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
                margin-bottom: 16px;
            }

            .reset-title {
                font-size: 20px;
            }

            .reset-body {
                padding: 0 32px 24px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            .info-box {
                padding: 12px;
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-wrapper">
        <div class="reset-card">
            <div class="reset-header">
                <div class="reset-icon">🔑</div>
                <h1 class="reset-title">Reset Password</h1>
                <p class="reset-subtitle">Create a strong new password</p>
            </div>

            <div class="reset-body">
                <div id="alertError" class="alert alert-error"></div>
                <div id="alertSuccess" class="alert alert-success"></div>

                <div class="info-box">
                    <strong>🔒 Password Requirements:</strong>
                    <ul>
                        <li>Minimum 6 characters</li>
                        <li>Mix of letters and numbers recommended</li>
                        <li>Avoid common passwords</li>
                    </ul>
                </div>

                <form id="resetPasswordForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input 
                            type="email" 
                            class="form-input" 
                            value="{{ $email }}"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-input" 
                                placeholder="Enter new password"
                                required
                                minlength="6"
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <span id="eyeIcon1">👁️</span>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-fill"></div>
                            </div>
                            <div class="strength-text"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                id="passwordConfirmation" 
                                class="form-input" 
                                placeholder="Confirm new password"
                                required
                                minlength="6"
                            >
                            <button type="button" class="password-toggle" id="togglePasswordConfirm">
                                <span id="eyeIcon2">👁️</span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="btnReset" class="btn-reset">
                        <span id="btnText">Reset Password</span>
                    </button>

                    <a href="{{ route('login') }}" class="btn-back">
                        ← Back to Sign In
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#togglePassword').click(function() {
                const input = $('#password');
                const icon = $('#eyeIcon1');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.text('🙈');
                } else {
                    input.attr('type', 'password');
                    icon.text('👁️');
                }
            });

            $('#togglePasswordConfirm').click(function() {
                const input = $('#passwordConfirmation');
                const icon = $('#eyeIcon2');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.text('🙈');
                } else {
                    input.attr('type', 'password');
                    icon.text('👁️');
                }
            });

            $('#password').on('input', function() {
                const password = $(this).val();
                const strengthDiv = $('#passwordStrength');
                
                if (password.length === 0) {
                    strengthDiv.hide();
                    return;
                }

                strengthDiv.show();
                strengthDiv.removeClass('strength-weak strength-medium strength-strong');

                let strength = 0;
                
                if (password.length >= 6) strength++;
                if (password.length >= 10) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                if (strength <= 2) {
                    strengthDiv.addClass('strength-weak');
                    strengthDiv.find('.strength-text').text('Weak');
                } else if (strength <= 4) {
                    strengthDiv.addClass('strength-medium');
                    strengthDiv.find('.strength-text').text('Medium');
                } else {
                    strengthDiv.addClass('strength-strong');
                    strengthDiv.find('.strength-text').text('Strong');
                }
            });

            $('#resetPasswordForm').submit(function(e) {
                e.preventDefault();

                const password = $('#password').val();
                const passwordConfirm = $('#passwordConfirmation').val();

                if (password !== passwordConfirm) {
                    $('#alertError').text('Passwords do not match!').fadeIn();
                    return;
                }

                const btn = $('#btnReset');
                const btnText = $('#btnText');

                btn.prop('disabled', true);
                btnText.html('<span class="loading-spinner"></span>Resetting...');
                $('.alert').hide();

                const formData = {
                    token: $('input[name="token"]').val(),
                    email: $('input[name="email"]').val(),
                    password: password,
                    password_confirmation: passwordConfirm
                };

                $.ajax({
                    url: '{{ route("password.update") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#alertSuccess').text(response.message).fadeIn();
                            btnText.html('✓ Success! Redirecting...');

                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 2000);
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        btnText.html('Reset Password');

                        let errorMsg = 'Failed to reset password.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        $('#alertError').text(errorMsg).fadeIn();
                    }
                });
            });

            $('#password').focus();
        });
    </script>
</body>
</html>