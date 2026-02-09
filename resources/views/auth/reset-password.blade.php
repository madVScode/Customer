<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - Customer Pro</title>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --success: #48bb78;
            --error: #f56565;
            --text-dark: #2d3748;
            --text-light: #718096;
            --border: #e2e8f0;
            --bg-light: #f7fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* ================= ANIMATED BACKGROUND ================= */
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
            backdrop-filter: blur(2px);
        }

        .bg-circle:nth-child(1) {
            width: 400px;
            height: 400px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .bg-circle:nth-child(2) {
            width: 300px;
            height: 300px;
            bottom: -100px;
            right: -100px;
            animation-delay: 4s;
        }

        .bg-circle:nth-child(3) {
            width: 200px;
            height: 200px;
            top: 50%;
            right: 10%;
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        /* ================= RESET CONTAINER ================= */
        .reset-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 10;
            animation: slideUp 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            overflow: hidden;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ================= HEADER ================= */
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 40px 40px;
            text-align: center;
            color: white;
            position: relative;
        }

        .reset-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0;
        }

        .reset-logo {
            font-size: 4rem;
            margin-bottom: 15px;
            animation: bounce 2s infinite;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        .reset-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
        }

        .reset-subtitle {
            font-size: 1rem;
            opacity: 0.95;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        /* ================= BODY ================= */
        .reset-body {
            padding: 50px 40px 40px;
        }

        /* ================= ALERTS ================= */
        .alert {
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            display: none;
            animation: slideDown 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #742a2a;
            border: 2px solid #fc8181;
        }

        .alert-success {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #22543d;
            border: 2px solid #68d391;
        }

        /* ================= FORM ================= */
        .form-group {
            margin-bottom: 28px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.95rem;
            letter-spacing: -0.2px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.3rem;
            color: #a0aec0;
            transition: all 0.3s;
            z-index: 1;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px 16px 58px;
            border: 2px solid var(--border);
            border-radius: 14px;
            font-size: 1rem;
            transition: all 0.3s;
            background: var(--bg-light);
            font-weight: 500;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-input:focus + .input-icon {
            color: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }

        /* ================= PASSWORD TOGGLE ================= */
        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.3rem;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s;
            z-index: 2;
        }

        .password-toggle:hover {
            background: var(--bg-light);
            transform: translateY(-50%) scale(1.1);
        }

        /* ================= PASSWORD STRENGTH ================= */
        .password-strength {
            margin-top: 12px;
            display: none;
        }

        .strength-bar {
            height: 6px;
            background: #e2e8f0;
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
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
        }

        .strength-weak .strength-fill {
            width: 33%;
            background: #f56565;
        }

        .strength-weak .strength-text {
            color: #f56565;
        }

        .strength-medium .strength-fill {
            width: 66%;
            background: #ed8936;
        }

        .strength-medium .strength-text {
            color: #ed8936;
        }

        .strength-strong .strength-fill {
            width: 100%;
            background: #48bb78;
        }

        .strength-strong .strength-text {
            color: #48bb78;
        }

        /* ================= BUTTONS ================= */
        .btn-reset {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.15rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .btn-reset:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-reset:active {
            transform: translateY(-1px);
        }

        .btn-reset:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-back {
            width: 100%;
            padding: 16px;
            background: var(--bg-light);
            color: var(--text-dark);
            border: 2px solid var(--border);
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 12px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-back:hover {
            background: white;
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .loading-spinner {
            display: inline-block;
            width: 22px;
            height: 22px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 12px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ================= INFO BOX ================= */
        .info-box {
            background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
            border: 2px solid #4299e1;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 25px;
            color: #2c5282;
        }

        .info-box strong {
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .info-box li {
            margin: 6px 0;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 600px) {
            .reset-container {
                max-width: 100%;
            }

            .reset-header {
                padding: 40px 30px 35px;
            }

            .reset-body {
                padding: 40px 30px 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Circles -->
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>

    <!-- Reset Container -->
    <div class="reset-container">
        <!-- Header -->
        <div class="reset-header">
            <div class="reset-logo">🔑</div>
            <div class="reset-title">Reset Password</div>
            <div class="reset-subtitle">Create a new strong password</div>
        </div>

        <!-- Body -->
        <div class="reset-body">
            <!-- Alert Messages -->
            <div id="alertError" class="alert alert-error"></div>
            <div id="alertSuccess" class="alert alert-success"></div>

            <!-- Info Box -->
            <div class="info-box">
                <strong>🔒 Password Requirements:</strong>
                <ul>
                    <li>Minimum 6 characters long</li>
                    <li>Mix of uppercase & lowercase letters</li>
                    <li>Include numbers and special characters</li>
                </ul>
            </div>

            <!-- Reset Form -->
            <form id="resetPasswordForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                
                <div class="form-group">
                    <label class="form-label">📧 Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">✉️</span>
                        <input 
                            type="email" 
                            class="form-input" 
                            value="{{ $email }}"
                            readonly
                            style="background: #edf2f7; cursor: not-allowed;"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">🔒 New Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔐</span>
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
                    <label class="form-label">🔒 Confirm Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔐</span>
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
                    <span id="btnText">RESET PASSWORD</span>
                </button>

                <a href="{{ route('login') }}" class="btn-back">
                    ← Back to Login
                </a>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            // ================= CSRF TOKEN SETUP =================
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ================= PASSWORD TOGGLE =================
            $('#togglePassword').click(function() {
                const passwordInput = $('#password');
                const eyeIcon = $('#eyeIcon1');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    eyeIcon.text('🙈');
                } else {
                    passwordInput.attr('type', 'password');
                    eyeIcon.text('👁️');
                }
            });

            $('#togglePasswordConfirm').click(function() {
                const passwordInput = $('#passwordConfirmation');
                const eyeIcon = $('#eyeIcon2');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    eyeIcon.text('🙈');
                } else {
                    passwordInput.attr('type', 'password');
                    eyeIcon.text('👁️');
                }
            });

            // ================= PASSWORD STRENGTH CHECKER =================
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
                
                // Length check
                if (password.length >= 6) strength++;
                if (password.length >= 10) strength++;
                
                // Uppercase check
                if (/[A-Z]/.test(password)) strength++;
                
                // Lowercase check
                if (/[a-z]/.test(password)) strength++;
                
                // Number check
                if (/[0-9]/.test(password)) strength++;
                
                // Special character check
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                if (strength <= 2) {
                    strengthDiv.addClass('strength-weak');
                    strengthDiv.find('.strength-text').text('Weak Password');
                } else if (strength <= 4) {
                    strengthDiv.addClass('strength-medium');
                    strengthDiv.find('.strength-text').text('Medium Password');
                } else {
                    strengthDiv.addClass('strength-strong');
                    strengthDiv.find('.strength-text').text('Strong Password');
                }
            });

            // ================= FORM SUBMIT =================
            $('#resetPasswordForm').submit(function(e) {
                e.preventDefault();

                const password = $('#password').val();
                const passwordConfirm = $('#passwordConfirmation').val();

                // Validate passwords match
                if (password !== passwordConfirm) {
                    $('#alertError').text('Passwords do not match!').fadeIn();
                    
                    // Shake animation
                    $('.reset-container').css('animation', 'shake 0.5s');
                    setTimeout(function() {
                        $('.reset-container').css('animation', '');
                    }, 500);
                    return;
                }

                const btn = $('#btnReset');
                const btnText = $('#btnText');

                // Disable button and show loading
                btn.prop('disabled', true);
                btnText.html('<span class="loading-spinner"></span>Resetting password...');

                // Hide alerts
                $('.alert').hide();

                // Get form data
                const formData = {
                    token: $('input[name="token"]').val(),
                    email: $('input[name="email"]').val(),
                    password: password,
                    password_confirmation: passwordConfirm
                };

                // Submit reset
                $.ajax({
                    url: '{{ route("password.update") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $('#alertSuccess').html(
                                '<strong>✅ Success!</strong><br>' + response.message
                            ).fadeIn();
                            
                            // Change button text
                            btnText.html('✅ Success! Redirecting...');

                            // Redirect after 2 seconds
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 2000);
                        }
                    },
                    error: function(xhr) {
                        // Re-enable button
                        btn.prop('disabled', false);
                        btnText.html('RESET PASSWORD');

                        // Show error message
                        let errorMsg = 'Failed to reset password. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        $('#alertError').html('<strong>❌ Error</strong><br>' + errorMsg).fadeIn();
                        
                        // Shake animation
                        $('.reset-container').css('animation', 'shake 0.5s');
                        setTimeout(function() {
                            $('.reset-container').css('animation', '');
                        }, 500);
                    }
                });
            });

            // ================= ENTER KEY TO SUBMIT =================
            $('#password, #passwordConfirmation').keypress(function(e) {
                if (e.which === 13) {
                    $('#resetPasswordForm').submit();
                }
            });

            // ================= AUTO-FOCUS =================
            $('#password').focus();

            // ================= SHAKE ANIMATION =================
            const style = document.createElement('style');
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
                    20%, 40%, 60%, 80% { transform: translateX(10px); }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>