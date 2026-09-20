<?php
require('../config/autoload.php');
$dao = new DataAccess();



$msg = "";
$msgClass = "";

// Login processing
if (isset($_POST['login'])) {
    $email = trim($_POST['aemail']) ?? '';
    $pass = trim($_POST['password']) ?? '';

    // Check admin credentials in DB
    $admin = $dao->getData("*", "admin", "aemail='$email' AND password='$pass'");
    
    if (!empty($admin)) {
        // Save admin session
        $_SESSION['admin'] = [
            'email' => $admin[0]['aemail']
        ];
        header("Location: preloader.php");
        exit;
    } else {
        $msg = "Invalid Email or Password";
        $msgClass = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Vaccibook</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .bg-animation span {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            animation: animate 25s linear infinite;
            bottom: -150px;
        }

        .bg-animation span:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
        .bg-animation span:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
        .bg-animation span:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
        .bg-animation span:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
        .bg-animation span:nth-child(5) { left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
        .bg-animation span:nth-child(6) { left: 75%; width: 110px; height: 110px; animation-delay: 3s; }
        .bg-animation span:nth-child(7) { left: 35%; width: 150px; height: 150px; animation-delay: 7s; }
        .bg-animation span:nth-child(8) { left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 45s; }
        .bg-animation span:nth-child(9) { left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 35s; }
        .bg-animation span:nth-child(10) { left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s; }

        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1400px;
            margin: auto;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 80px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
            z-index: 1;
            min-height: 600px;
        }

        .left-section {
            flex: 1;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3));
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .admin-icon {
            font-size: 120px;
            color: white;
            margin-bottom: 30px;
            filter: drop-shadow(0 10px 30px rgba(255, 255, 255, 0.3));
            animation: float 3s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .left-section h1 {
            font-size: 48px;
            font-weight: 800;
            color: white;
            margin-bottom: 20px;
            text-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }

        .left-section p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }

        .right-section {
            flex: 1;
            background: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #64748b;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 14px 20px 14px 50px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #1e3a8a;
            background: white;
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
        }

        .form-input:focus ~ .input-icon {
            color: #1e3a8a;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
            margin-top: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #ef4444;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #1e3a8a;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }

        @media (max-width: 968px) {
            .container {
                flex-direction: column;
            }

            .left-section {
                padding: 40px 20px;
            }

            .left-section h1 {
                font-size: 32px;
            }

            .admin-icon {
                font-size: 80px;
            }

            .right-section {
                padding: 40px 20px;
            }
        }

        /* Loading animation */
        .login-btn.loading {
            pointer-events: none;
            position: relative;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .features {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            position: relative;
            z-index: 1;
        }

        .feature {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
        }

        .feature i {
            color: #fbbf24;
            font-size: 24px;
        }

        .feature span {
            color: white;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="container">
        <div class="left-section">
            <div class="admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Admin Portal</h1>
            <p>Access the complete system dashboard to manage health centres, vaccines, schedules, and monitor overall operations.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics & Insights</span>
                </div>
                <div class="feature">
                    <i class="fas fa-users-cog"></i>
                    <span>User Management</span>
                </div>
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure Access</span>
                </div>
                <div class="feature">
                    <i class="fas fa-database"></i>
                    <span>Data Control</span>
                </div>
            </div>
        </div>

        <div class="right-section">
            <div class="login-header">
                <h2>Admin Login</h2>
                <p>Enter your credentials to access the admin panel</p>
            </div>

            <?php if ($msg): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $msg ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            name="aemail" 
                            class="form-input" 
                            placeholder="admin@vaccibook.com"
                            required
                            autocomplete="email"
                        >
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" name="login" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                </button>
            </form>

            <div class="back-link">
                <a href="../index/index.php">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Add loading animation on form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = document.querySelector('.login-btn');
            btn.classList.add('loading');
            btn.innerHTML = '';
        });
    </script>
</body>
</html>
