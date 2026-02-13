<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $title = "Log In";
    include(VIEW_PATH . 'layouts/partials/title-meta.php');
    ?>
    <meta charset="utf-8" />
    <title><?= $title ?> | Stock Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Hardware & Furniture Stock Management System" name="description" />
    <meta content="Stock Admin" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <script src="<?php echo BASE_URL;?>/assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="<?php echo BASE_URL;?>/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="<?php echo BASE_URL;?>/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="<?php echo BASE_URL;?>/assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- AdminLTE Style with Blue Theme -->
    <style>
        * {
            font-family: 'Source Sans Pro', sans-serif;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-box {
            width: 100%;
            max-width: 400px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 25px;
            font-size: 35px;
            font-weight: 300;
        }
        
        .login-logo a {
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s ease;
        }
        
        .login-logo a:hover {
            transform: translateY(-2px);
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .logo-icon i {
            font-size: 28px;
            color: #3498db;
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .logo-text .main-title {
            font-size: 28px;
            font-weight: 600;
            line-height: 1;
            margin-bottom: 2px;
        }
        
        .logo-text .sub-title {
            font-size: 14px;
            font-weight: 300;
            opacity: 0.9;
        }
        
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: #ffffff;
            padding: 20px 25px;
            border-bottom: none;
            text-align: center;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        
        .card-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 30px 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }
        
        .form-control {
            height: 45px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.15);
            background: #ffffff;
            outline: none;
        }
        
        .form-control::placeholder {
            color: #95a5a6;
        }
        
        .input-group {
            position: relative;
            display: flex;
        }
        
        .input-group .form-control {
            flex: 1;
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .input-group .btn {
            border: 2px solid #e0e6ed;
            border-left: none;
            background: #f8f9fa;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            padding: 0 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .input-group .btn:hover {
            background: #e9ecef;
        }
        
        .input-group .btn i {
            color: #7f8c8d;
            font-size: 18px;
        }
        
        .form-check {
            padding-left: 0;
            margin-bottom: 20px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            margin-right: 8px;
            border: 2px solid #bdc3c7;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .form-check-input:checked {
            background-color: #3498db;
            border-color: #3498db;
        }
        
        .form-check-label {
            color: #7f8c8d;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }
        
        .btn-primary {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary span {
            position: relative;
            z-index: 1;
        }
        
        .btn-primary.loading {
            pointer-events: none;
            background: #95a5a6;
        }
        
        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.8s linear infinite;
        }
        
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
        
        .alert {
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: none;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #fee;
            color: #c0392b;
            border-left: 4px solid #e74c3c;
        }
        
        .alert-info {
            background-color: #e8f4fd;
            color: #2471a3;
            border-left: 4px solid #3498db;
        }
        
        .alert-success {
            background-color: #eafaf1;
            color: #1e8449;
            border-left: 4px solid #27ae60;
        }
        
        .error-item {
            padding: 3px 0;
        }
        
        .card-footer {
            background: #f8f9fa;
            padding: 15px 25px;
            text-align: center;
            border-top: 1px solid #e0e6ed;
        }
        
        .card-footer p {
            margin: 0;
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .card-footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .card-footer a:hover {
            color: #2980b9;
        }
        
        /* Icon prefix for inputs */
        .input-icon {
            position: relative;
        }
        
        .input-icon .form-control {
            padding-left: 45px;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
            z-index: 1;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-logo {
                font-size: 28px;
            }
            
            .logo-icon {
                width: 45px;
                height: 45px;
            }
            
            .logo-icon i {
                font-size: 24px;
            }
            
            .logo-text .main-title {
                font-size: 24px;
            }
            
            .logo-text .sub-title {
                font-size: 12px;
            }
            
            .card-body {
                padding: 25px 20px;
            }
            
            .card-header h3 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-page">
        <div class="login-box">
            <!-- Logo -->
            <div class="login-logo">
                <a href="index.php">
                    <div class="logo-icon">
                        <i class="ri-stack-line"></i>
                    </div>
                    <div class="logo-text">
                        <span class="main-title">StockPro</span>
                        <span class="sub-title">Inventory Management</span>
                    </div>
                </a>
            </div>

            <!-- Login Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Sign In</h3>
                    <p>Enter your credentials to access your account</p>
                </div>

                <div class="card-body">
                    <?php
                        // Flash message with proper error handling
                        $flash = SessionManager::getFlash('login');
                        if (!empty($flash) && is_array($flash)) {
                            $flashClass = isset($flash['class']) ? $flash['class'] : 'alert alert-info';
                            $flashMessage = isset($flash['message']) ? $flash['message'] : '';
                            if (!empty($flashMessage)) {
                                echo '<div class="' . htmlspecialchars($flashClass) . '">' . htmlspecialchars($flashMessage) . '</div>';
                            }
                        }

                        // Validation errors from controller with proper checking
                        if (isset($errors) && !empty($errors) && is_array($errors)) {
                            echo '<div class="alert alert-danger">';
                            foreach ($errors as $err) {
                                echo '<div class="error-item">' . htmlspecialchars($err) . '</div>';
                            }
                            echo '</div>';
                        }
                    ?>

                    <form action="<?php echo APP_URL;?>auth/index" method="post">
                        <input type="hidden" name="csrf_token" value="<?= isset($csrf_token) ? htmlspecialchars($csrf_token) : '' ?>">
                        
                        <!-- Username -->
                        <div class="form-group">
                            <label class="form-label" for="username">
                                <i class="ri-user-line" style="margin-right: 5px;"></i>Username
                            </label>
                            <div class="input-icon">
                                <i class="ri-user-3-line"></i>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       class="form-control"
                                       value="<?= isset($username) ? htmlspecialchars($username) : '' ?>"
                                       placeholder="Enter your username" 
                                       required 
                                       autocomplete="username">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password">
                                <i class="ri-lock-line" style="margin-right: 5px;"></i>Password
                            </label>
                            <div class="input-icon">
                                <i class="ri-lock-password-line"></i>
                                <div class="input-group">
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           class="form-control"
                                           placeholder="Enter your password" 
                                           required 
                                           autocomplete="current-password"
                                           style="padding-left: 45px;">
                                    <button type="button" class="btn" id="togglePassword" tabindex="-1">
                                        <i class="ri-eye-off-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="checkbox-signin">
                            <label class="form-check-label" for="checkbox-signin">
                                Remember me on this device
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button class="btn btn-primary" type="submit" id="loginBtn">
                            <span>Sign In</span>
                        </button>
                    </form>
                </div>

                <div class="card-footer">
                    <p>Department of Computer Engineering</p><p>Government Polytechnic College Nedumkandam</p>
                    <p style="margin-top: 8px; font-size: 12px;">© 2026 StockPro. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <?php include(VIEW_PATH . 'layouts/partials/footer-scripts.php'); ?>

    <!-- JavaScript -->
    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function () {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            } else {
                password.type = 'password';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            }
        });
        
        // Add loading state on form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.disabled = true;
            btn.querySelector('span').style.opacity = '0';
        });
        
        // Input focus effect
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-group').style.transform = 'translateX(3px)';
            });
            
            input.addEventListener('blur', function() {
                this.closest('.form-group').style.transform = 'translateX(0)';
            });
        });
        
        // Enter key on remember me checkbox
        document.getElementById('checkbox-signin').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.checked = !this.checked;
            }
        });
    </script>

</body>
</html>