<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $title = "Log In";
    include(VIEW_PATH . 'layouts/partials/title-meta.php');
    ?>
    <meta charset="utf-8" />
    <title><?= $title ?> | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Admin Dashboard Login" name="description" />
    <meta content="Admin" name="author" />

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom Premium Design Styles -->
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .auth-bg {
            background: #800020;
            position: relative;
            overflow: hidden;
        }
        
        .auth-bg::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: conic-gradient(from 0deg at 50% 50%, 
                #800020 0deg, 
                #a00028 60deg, 
                #5c0016 120deg, 
                #800020 180deg, 
                #a00028 240deg, 
                #5c0016 300deg, 
                #800020 360deg);
            animation: rotate 20s linear infinite;
        }
        
        .auth-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 107%, rgba(255,255,255,0.05) 0%, transparent 50%),
                        radial-gradient(circle at 85% 0%, rgba(255,255,255,0.05) 0%, transparent 50%),
                        radial-gradient(circle at 50% 50%, transparent 0%, rgba(128,0,32,0.4) 100%);
        }
        
        @keyframes rotate {
            100% {
                transform: rotate(360deg);
            }
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            right: 20%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            left: 80%;
            bottom: 10%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.5;
            }
            50% {
                transform: translateY(-100px) rotate(180deg);
                opacity: 0.8;
            }
        }
        
        .auth-container {
            position: relative;
            z-index: 10;
        }
        
        .auth-brand {
            display: flex;
            justify-content: center;
            margin-bottom: 2.5rem;
            position: relative;
            animation: logoEntry 1s ease-out;
        }
        
        .auth-brand img {
            height: 60px !important;
            width: auto;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            filter: brightness(0) invert(1);
            drop-shadow: 0 4px 20px rgba(255, 255, 255, 0.3));
        }
        
        .auth-brand .logo-light {
            display: block;
        }
        
        .auth-brand .logo-dark {
            display: none;
        }
        
        .auth-brand:hover img {
            transform: scale(1.1) translateY(-3px);
            filter: brightness(0) invert(1) drop-shadow(0 6px 25px rgba(255, 255, 255, 0.4));
        }
        
        @keyframes logoEntry {
            0% {
                opacity: 0;
                transform: translateY(-50px) scale(0.8);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 3.5rem 3rem !important;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4),
                        0 0 100px rgba(128, 0, 32, 0.2),
                        inset 0 0 0 1px rgba(255, 255, 255, 0.5);
            animation: cardEntry 1s ease-out 0.2s both;
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #800020, transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        @keyframes cardEntry {
            0% {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        h4 {
            color: #800020;
            font-size: 2rem !important;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 2.5rem !important;
            position: relative;
            animation: fadeIn 1s ease-out 0.4s both;
        }
        
        h4::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #800020, transparent);
            border-radius: 2px;
        }
        
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-label {
            color: #2d3748;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            display: block;
            transition: color 0.3s ease;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
            color: #2d3748;
        }
        
        .form-control:focus {
            border-color: #800020;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(128, 0, 32, 0.1);
            transform: translateY(-2px);
        }
        
        .form-control::placeholder {
            color: #a0aec0;
            font-weight: 400;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .btn {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #718096;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .input-group .btn:hover {
            background: rgba(128, 0, 32, 0.1);
            color: #800020;
        }
        
        .input-group .btn:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid #cbd5e0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .form-check-input:checked {
            background-color: #800020;
            border-color: #800020;
            box-shadow: 0 2px 8px rgba(128, 0, 32, 0.3);
        }
        
        .form-check-label {
            color: #4a5568;
            margin-left: 0.5rem;
            cursor: pointer;
            user-select: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #800020 0%, #a00028 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #ffffff;
            box-shadow: 0 10px 30px rgba(128, 0, 32, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
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
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(128, 0, 32, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
            box-shadow: 0 5px 20px rgba(128, 0, 32, 0.3);
        }
        
        /* Alert Styles */
        .alert {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            color: #c53030;
            border-left: 4px solid #f56565;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
            color: #2f855a;
            border-left: 4px solid #48bb78;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
            color: #2c5282;
            border-left: 4px solid #4299e1;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        .alert li {
            margin: 0.25rem 0;
        }
        
        /* Enhanced spacing */
        .mb-3 {
            margin-bottom: 1.75rem !important;
        }
        
        .mb-4 {
            margin-bottom: 2.5rem !important;
        }
        
        /* Loading state */
        .btn-primary.loading {
            pointer-events: none;
            color: transparent;
        }
        
        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            top: 50%;
            left: 50%;
            margin-left: -12px;
            margin-top: -12px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.8s linear infinite;
        }
        
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card {
                padding: 2.5rem 2rem !important;
                margin: 1rem;
            }
            
            h4 {
                font-size: 1.75rem !important;
            }
            
            .auth-brand img {
                height: 50px !important;
            }
            
            .btn-primary {
                padding: 1rem 2rem;
            }
        }
    </style>
</head>

<body>

    <div class="auth-bg d-flex min-vh-100">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        
        <div class="auth-container row g-0 justify-content-center align-items-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xxl-4 col-lg-5 col-md-7 col-sm-10">
                <a href="index.php" class="auth-brand d-flex justify-content-center mb-4">
                    <img src="<?php echo BASE_URL;?>/assets/images/logo-dark.png" alt="dark logo" height="26" class="logo-dark">
                    <img src="<?php echo BASE_URL;?>/assets/images/logo.png" alt="logo light" height="26" class="logo-light">
                </a>

                <div class="card overflow-hidden text-center">
                    <h4 class="fw-bold">Log in to your account</h4>
                    
                    <?php
                        // Flash message with proper error handling
                        $flash = SessionManager::getFlash('login');
                        if (!empty($flash) && is_array($flash)) {
                            $flashClass = isset($flash['class']) ? $flash['class'] : 'alert alert-info';
                            $flashMessage = isset($flash['message']) ? $flash['message'] : '';
                            if (!empty($flashMessage)) {
                                echo '<div class="' . htmlspecialchars($flashClass) . ' text-center mb-3">' . htmlspecialchars($flashMessage) . '</div>';
                            }
                        }

                        // Validation errors from controller with proper checking
                        if (isset($errors) && !empty($errors) && is_array($errors)) {
                            echo '<div class="alert alert-danger text-center">';
                            foreach ($errors as $err) {
                                echo '<div class="error-item">' . htmlspecialchars($err) . '</div>';  // No bullet!
                            }
                            echo '</div>';
                        }
                    ?>

                    <form action="<?php echo APP_URL;?>auth/index" method="post" class="text-start">
                        <input type="hidden" name="csrf_token" value="<?= isset($csrf_token) ? htmlspecialchars($csrf_token) : '' ?>">
                        
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control"
                                   value="<?= isset($username) ? htmlspecialchars($username) : '' ?>"
                                   placeholder="Enter your username" 
                                   required 
                                   autocomplete="username">
                        </div>

                        <!-- Password field with eye toggle -->
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control"
                                       placeholder="Enter your password" 
                                       required 
                                       autocomplete="current-password">
                                <button type="button" class="btn" id="togglePassword" tabindex="-1">
                                    <i class="ri-eye-off-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                <label class="form-check-label" for="checkbox-signin">Remember me</label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary" type="submit" id="loginBtn">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include(VIEW_PATH . 'layouts/partials/footer-scripts.php'); ?>

    <!-- Enhanced JavaScript -->
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
        });
        
        // Add ripple effect on button click
        document.getElementById('loginBtn').addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
        
        // Input animation on focus
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
        
        // Parallax effect on mouse move
        document.addEventListener('mousemove', (e) => {
            const shapes = document.querySelectorAll('.shape');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 10;
                shape.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });
    </script>

</body>
</html>