<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Customer Pro</title>
    
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

        .bg-circle:nth-child(4) {
            width: 150px;
            height: 150px;
            bottom: 30%;
            left: 15%;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        /* ================= LOGIN CONTAINER ================= */
        .login-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 480px;
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
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 40px 40px;
            text-align: center;
            color: white;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0;
        }

        .login-logo {
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

        .login-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            font-size: 1rem;
            opacity: 0.95;
            font-weight: 500;
        }

        .role-badges {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 25px;
            position: relative;
            z-index: 1;
        }

        .role-badge {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s;
        }

        .role-badge:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-2px);
        }

        /* ================= BODY ================= */
        .login-body {
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

        .alert-info {
            background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
            color: #2c5282;
            border: 2px solid #4299e1;
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

        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }

        /* ================= REMEMBER & FORGOT ================= */
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: var(--text-dark);
            font-weight: 600;
            user-select: none;
        }

        .checkbox-label input {
            margin-right: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            position: relative;
        }

        .forgot-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }

        .forgot-link:hover::after {
            width: 100%;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
        }

        /* ================= BUTTONS ================= */
        .btn-login {
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
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
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

        /* ================= FOOTER ================= */
        .login-footer {
            text-align: center;
            padding: 30px 40px;
            background: linear-gradient(to bottom, var(--bg-light), #edf2f7);
            color: var(--text-light);
            font-size: 0.875rem;
            line-height: 1.8;
        }

        .demo-accounts {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .demo-accounts strong {
            color: var(--text-dark);
            display: block;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .demo-account {
            background: var(--bg-light);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 0.85rem;
            border: 1px solid var(--border);
            transition: all 0.3s;
        }

        .demo-account:hover {
            background: white;
            border-color: var(--primary);
            transform: translateX(5px);
        }

        .demo-account:last-child {
            margin-bottom: 0;
        }

        /* ================= FORGOT PASSWORD MODAL ================= */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.show {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideUp 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
        }

        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 20px 20px 0 0;
            color: white;
            text-align: center;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .modal-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .modal-body {
            padding: 40px 30px;
        }

        .btn-reset {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-reset:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-back {
            width: 100%;
            padding: 14px;
            background: var(--bg-light);
            color: var(--text-dark);
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-back:hover {
            background: white;
            border-color: var(--primary);
            color: var(--primary);
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 600px) {
            .login-container {
                max-width: 100%;
            }

            .login-header {
                padding: 40px 30px 35px;
            }

            .login-body {
                padding: 40px 30px 30px;
            }

            .login-footer {
                padding: 25px 30px;
            }

            .modal-body {
                padding: 30px 25px;
            }

            .role-badges {
                flex-direction: column;
            }

            .role-badge {
                width: 100%;
                justify-content: center;
            }
        }

        /* ================= UTILITIES ================= */
        .text-center {
            text-align: center;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mt-10 {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Animated Background Circles -->
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>

    <!-- Login Container -->
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="login-logo">👥</div>
            <div class="login-title">Customer Pro</div>
            <div class="login-subtitle">Admin Management System</div>
            
            <div class="role-badges">
                <div class="role-badge">
                    <span>🔧</span>
                    <span>Admin Manage</span>
                </div>
                <div class="role-badge">
                    <span>📊</span>
                    <span>Admin Record</span>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="login-body">
            <!-- Alert Messages -->
            <div id="alertError" class="alert alert-error"></div>
            <div id="alertSuccess" class="alert alert-success"></div>
            <div id="alertInfo" class="alert alert-info"></div>

            <!-- Login Form -->
            <form id="loginForm">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">📧 Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">✉️</span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-input" 
                            placeholder="admin@gmail.com"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">🔒 Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔐</span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-input" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" id="togglePassword">
                            <span id="eyeIcon">👁️</span>
                        </button>
                    </div>
                </div>

                <div class="remember-forgot">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link" id="forgotPasswordLink">Forgot password?</a>
                </div>

                <button type="submit" id="btnLogin" class="btn-login">
                    <span id="btnText">SIGN IN</span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <div class="demo-accounts">
                <strong>🎯 Demo Accounts</strong>
                <div class="demo-account">
                    <strong>Admin Manage:</strong> adminmanagedata@gmail.com / admin123
                </div>
                <div class="demo-account">
                    <strong>Admin Record:</strong> adminrecordata@gmail.com / record123
                </div>
            </div>
            <div style="font-size: 0.8rem; color: #a0aec0;">
                © 2026 Customer Pro Management. All rights reserved.
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal-overlay" id="forgotPasswordModal">
        <div class="modal-content">
            <div class="modal-header">
                <button class="modal-close" id="closeForgotModal">✖</button>
                <div class="modal-icon">🔑</div>
                <div class="modal-title">Forgot Password?</div>
                <div class="modal-subtitle">No worries, we'll send you reset instructions</div>
            </div>
            
            <div class="modal-body">
                <div id="alertModalError" class="alert alert-error"></div>
                <div id="alertModalSuccess" class="alert alert-success"></div>
                
                <form id="forgotPasswordForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">📧 Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">✉️</span>
                            <input 
                                type="email" 
                                name="reset_email" 
                                id="resetEmail" 
                                class="form-input" 
                                placeholder="Enter your email"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" id="btnReset" class="btn-reset">
                        <span id="btnResetText">Send Reset Link</span>
                    </button>
                    
                    <button type="button" class="btn-back" id="backToLogin">
                        ← Back to Login
                    </button>
                </form>
            </div>
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
                const eyeIcon = $('#eyeIcon');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    eyeIcon.text('🙈'); // Eye closed
                } else {
                    passwordInput.attr('type', 'password');
                    eyeIcon.text('👁️'); // Eye open
                }
            });

            // ================= FORGOT PASSWORD MODAL =================
            $('#forgotPasswordLink').click(function(e) {
                e.preventDefault();
                $('#forgotPasswordModal').addClass('show');
                $('#resetEmail').focus();
                $('.alert').hide();
            });

            $('#closeForgotModal, .modal-overlay').click(function(e) {
                if (e.target === this) {
                    $('#forgotPasswordModal').removeClass('show');
                    $('#forgotPasswordForm')[0].reset();
                    $('.alert').hide();
                }
            });

            $('#backToLogin').click(function() {
                $('#forgotPasswordModal').removeClass('show');
                $('#forgotPasswordForm')[0].reset();
                $('.alert').hide();
            });

            // ================= LOGIN FORM SUBMIT =================
            $('#loginForm').submit(function(e) {
                e.preventDefault();

                const btn = $('#btnLogin');
                const btnText = $('#btnText');

                // Disable button and show loading
                btn.prop('disabled', true);
                btnText.html('<span class="loading-spinner"></span>Signing in...');

                // Hide alerts
                $('.alert').hide();

                // Get form data
                const formData = {
                    email: $('#email').val(),
                    password: $('#password').val(),
                    remember: $('#remember').is(':checked')
                };

                // Submit login
                $.ajax({
                    url: '{{ route("login.post") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $('#alertSuccess').text(response.message).fadeIn();
                            
                            // Change button text
                            btnText.html('✅ Success! Redirecting...');

                            // Redirect after 1 second
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        // Re-enable button
                        btn.prop('disabled', false);
                        btnText.html('SIGN IN');

                        // Show error message
                        let errorMsg = 'Login failed. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        $('#alertError').text(errorMsg).fadeIn();
                        
                        // Shake animation on error
                        $('.login-container').css('animation', 'shake 0.5s');
                        setTimeout(function() {
                            $('.login-container').css('animation', '');
                        }, 500);
                    }
                });
            });

            // ================= FORGOT PASSWORD FORM SUBMIT =================
            $('#forgotPasswordForm').submit(function(e) {
                e.preventDefault();

                const btn = $('#btnReset');
                const btnText = $('#btnResetText');

                // Disable button and show loading
                btn.prop('disabled', true);
                btnText.html('<span class="loading-spinner"></span>Sending...');

                // Hide alerts
                $('#alertModalError, #alertModalSuccess').hide();

                // Get email
                const email = $('#resetEmail').val();

                // Submit reset request
                $.ajax({
                    url: '{{ route("password.forgot") }}',
                    type: 'POST',
                    data: { email: email },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $('#alertModalSuccess').html(
                                '<strong>✅ Email Sent!</strong><br>' + response.message
                            ).fadeIn();
                            
                            // Change button text
                            btnText.html('✅ Email Sent!');

                            // Reset form after 3 seconds
                            setTimeout(function() {
                                $('#forgotPasswordModal').removeClass('show');
                                $('#forgotPasswordForm')[0].reset();
                                $('#alertModalSuccess').hide();
                                btn.prop('disabled', false);
                                btnText.html('Send Reset Link');
                                
                                // Show info on login page
                                $('#alertInfo').html(
                                    '<strong>📧 Check Your Email</strong><br>' +
                                    'We\'ve sent password reset instructions to <strong>' + email + '</strong>'
                                ).fadeIn();
                            }, 3000);
                        }
                    },
                    error: function(xhr) {
                        // Re-enable button
                        btn.prop('disabled', false);
                        btnText.html('Send Reset Link');

                        // Show error message
                        let errorMsg = 'Failed to send reset email. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        $('#alertModalError').html('<strong>❌ Error</strong><br>' + errorMsg).fadeIn();
                    }
                });
            });

            // ================= ENTER KEY TO SUBMIT =================
            $('#email, #password').keypress(function(e) {
                if (e.which === 13) {
                    $('#loginForm').submit();
                }
            });

            $('#resetEmail').keypress(function(e) {
                if (e.which === 13) {
                    $('#forgotPasswordForm').submit();
                }
            });

            // ================= AUTO-FOCUS =================
            $('#email').focus();

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