<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
$admin = $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Admin Dashboard...</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            overflow: hidden;
            position: relative;
        }

        /* Animated Grid Background */
        .grid-background {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(30, 58, 138, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(30, 58, 138, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Floating particles */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 50%;
            box-shadow: 0 0 15px rgba(30, 58, 138, 0.6);
        }

        .particle:nth-child(1) { left: 10%; top: 20%; animation: twinkle 2s infinite 0s; }
        .particle:nth-child(2) { left: 20%; top: 80%; animation: twinkle 2s infinite 0.4s; }
        .particle:nth-child(3) { left: 30%; top: 40%; animation: twinkle 2s infinite 0.8s; }
        .particle:nth-child(4) { left: 40%; top: 60%; animation: twinkle 2s infinite 1.2s; }
        .particle:nth-child(5) { left: 50%; top: 30%; animation: twinkle 2s infinite 1.6s; }
        .particle:nth-child(6) { left: 60%; top: 70%; animation: twinkle 2s infinite 0.2s; }
        .particle:nth-child(7) { left: 70%; top: 50%; animation: twinkle 2s infinite 0.6s; }
        .particle:nth-child(8) { left: 80%; top: 25%; animation: twinkle 2s infinite 1s; }
        .particle:nth-child(9) { left: 90%; top: 85%; animation: twinkle 2s infinite 1.4s; }
        .particle:nth-child(10) { left: 15%; top: 55%; animation: twinkle 2s infinite 0.3s; }

        @keyframes twinkle {
            0%, 100% { opacity: 0; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.5); }
        }

        /* Main container */
        .preloader-container {
            text-align: center;
            position: relative;
            z-index: 10;
            animation: fadeInScale 0.8s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Logo container with glow */
        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 40px;
        }

        .logo-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(30, 58, 138, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.8; }
        }

        .logo {
            position: relative;
            width: 140px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 70px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 30px;
            backdrop-filter: blur(20px);
            border: 2px solid rgba(30, 58, 138, 0.2);
            box-shadow: 
                0 20px 60px rgba(30, 58, 138, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        .brand-name {
            font-size: 56px;
            font-weight: 800;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            letter-spacing: 3px;
            text-shadow: none;
            animation: slideDown 0.8s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .subtitle {
            color: #64748b;
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 50px;
            letter-spacing: 1px;
        }

        /* Modern loader */
        .loader-wrapper {
            margin: 40px 0;
        }

        .loader {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            position: relative;
        }

        .loader-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid rgba(30, 58, 138, 0.15);
            border-radius: 50%;
        }

        .loader-ring:nth-child(1) {
            border-top-color: #1e3a8a;
            animation: spin 1.5s linear infinite;
        }

        .loader-ring:nth-child(2) {
            border-right-color: #3b82f6;
            animation: spin 2s linear infinite reverse;
        }

        .loader-ring:nth-child(3) {
            border-bottom-color: #06b6d4;
            animation: spin 2.5s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            color: #1e3a8a;
            font-size: 18px;
            margin-top: 30px;
            font-weight: 600;
            letter-spacing: 2px;
        }

        .loading-bar-container {
            width: 300px;
            height: 6px;
            background: rgba(30, 58, 138, 0.1);
            border-radius: 10px;
            margin: 25px auto;
            overflow: hidden;
            box-shadow: inset 0 2px 5px rgba(30, 58, 138, 0.05);
        }

        .loading-bar {
            height: 100%;
            background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 50%, #06b6d4 100%);
            border-radius: 10px;
            width: 0%;
            animation: loadingProgress 3.5s ease-in-out forwards;
            box-shadow: 0 0 15px rgba(30, 58, 138, 0.3);
        }

        @keyframes loadingProgress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        .percentage {
            color: #3b82f6;
            font-size: 14px;
            margin-top: 10px;
            font-weight: 600;
        }

        .status-text {
            color: #64748b;
            font-size: 14px;
            margin-top: 20px;
            font-style: italic;
        }

        /* Data cards animation */
        .data-cards {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .data-card {
            background: rgba(30, 58, 138, 0.05);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 12px;
            border: 1px solid rgba(30, 58, 138, 0.15);
            animation: cardPop 0.6s ease-out backwards;
        }

        .data-card:nth-child(1) { animation-delay: 1s; }
        .data-card:nth-child(2) { animation-delay: 1.5s; }
        .data-card:nth-child(3) { animation-delay: 2s; }

        @keyframes cardPop {
            0% {
                opacity: 0;
                transform: scale(0.5) translateY(20px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .data-card i {
            font-size: 24px;
            color: #3b82f6;
            margin-bottom: 8px;
        }

        .data-card p {
            color: #1e3a8a;
            font-size: 12px;
            margin: 0;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="grid-background"></div>
    
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="preloader-container">
        <div class="logo-container">
            <div class="logo-glow"></div>
            <div class="logo">🛡️</div>
        </div>
        
        <div class="brand-name">VACCIBOOK</div>
        <div class="subtitle">Admin Control Panel</div>
        
        <div class="loader-wrapper">
            <div class="loader">
                <div class="loader-ring"></div>
                <div class="loader-ring"></div>
                <div class="loader-ring"></div>
            </div>
        </div>
        
        <div class="loading-text">INITIALIZING DASHBOARD</div>
        
        <div class="loading-bar-container">
            <div class="loading-bar"></div>
        </div>
        
        <div class="percentage" id="percentage">0%</div>
        <div class="status-text" id="status">Loading system modules...</div>

        <div class="data-cards">
            <div class="data-card">
                <i class="fas fa-database"></i>
                <p>Database Connected</p>
            </div>
            <div class="data-card">
                <i class="fas fa-shield-alt"></i>
                <p>Security Verified</p>
            </div>
            <div class="data-card">
                <i class="fas fa-check-circle"></i>
                <p>System Ready</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Percentage counter
        let percent = 0;
        const percentElement = document.getElementById('percentage');
        const statusElement = document.getElementById('status');
        
        const statusMessages = [
            'Loading system modules...',
            'Connecting to database...',
            'Verifying credentials...',
            'Loading analytics...',
            'Preparing dashboard...',
            'Almost ready...'
        ];
        
        const interval = setInterval(() => {
            percent += 2;
            percentElement.textContent = percent + '%';
            
            // Update status message
            if (percent === 20) statusElement.textContent = statusMessages[1];
            if (percent === 40) statusElement.textContent = statusMessages[2];
            if (percent === 60) statusElement.textContent = statusMessages[3];
            if (percent === 80) statusElement.textContent = statusMessages[4];
            if (percent === 95) statusElement.textContent = statusMessages[5];
            
            if (percent >= 100) {
                clearInterval(interval);
                percentElement.textContent = '100%';
                statusElement.textContent = 'Redirecting...';
            }
        }, 60);
        
        // Redirect after 4 seconds
        setTimeout(() => {
            window.location.href = "dashboard.php";
        }, 4000);
    </script>
</body>
</html>
