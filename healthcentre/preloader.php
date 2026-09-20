<?php
session_start();
if (!isset($_SESSION['healthcentre'])) {
    header("Location: login.php");
    exit;
}
$healthcentre = $_SESSION['healthcentre'];
$hname = $healthcentre['hname'] ?? 'Health Centre';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading...</title>
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
        
        .preloader-container {
            text-align: center;
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            z-index: 10;
        }
        
        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 30px;
        }

        .logo-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 170px;
            height: 170px;
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
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 70px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 25px;
            backdrop-filter: blur(20px);
            border: 2px solid rgba(30, 58, 138, 0.2);
            box-shadow: 0 20px 60px rgba(30, 58, 138, 0.2);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        .welcome-text {
            color: #1e3a8a;
            font-size: 1.9rem;
            font-weight: 600;
            margin-bottom: 10px;
            text-shadow: none;
        }
        
        .health-centre-name {
            color: #3b82f6;
            font-size: 2.3rem;
            font-weight: 700;
            margin-bottom: 40px;
            text-shadow: none;
        }
        
        .loader {
            width: 70px;
            height: 70px;
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
            animation: spin 1.2s linear infinite;
        }

        .loader-ring:nth-child(2) {
            border-right-color: #3b82f6;
            animation: spin 1.8s linear infinite reverse;
        }

        .loader-ring:nth-child(3) {
            border-bottom-color: #06b6d4;
            animation: spin 2.2s linear infinite;
        }
        
        .loading-text {
            color: #1e3a8a;
            font-size: 1.1rem;
            margin-top: 25px;
            font-weight: 600;
            letter-spacing: 1.5px;
        }

        .loading-bar-container {
            width: 280px;
            height: 5px;
            background: rgba(30, 58, 138, 0.1);
            border-radius: 10px;
            margin: 20px auto;
            overflow: hidden;
        }

        .loading-bar {
            height: 100%;
            background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 50%, #06b6d4 100%);
            border-radius: 10px;
            width: 0%;
            animation: loadingProgress 3.3s ease-in-out forwards;
            box-shadow: 0 0 10px rgba(30, 58, 138, 0.3);
        }

        @keyframes loadingProgress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        
        .dots {
            display: inline-block;
            width: 30px;
            text-align: left;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes bounceOld {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
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

        @keyframes twinkle {
            0%, 100% { opacity: 0; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.5); }
        }
        
        @keyframes floatOld {
            0%, 100% {
                transform: translateY(100vh);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px);
                opacity: 0;
            }
        }
    </style>
    <script>
        // Animated dots
        let dotCount = 0;
        setInterval(() => {
            dotCount = (dotCount + 1) % 4;
            document.getElementById('dots').textContent = '.'.repeat(dotCount);
        }, 500);
        
        // Redirect after 3.5 seconds
        setTimeout(() => {
            window.location.href = "dashboard.php";
        }, 3500);
    </script>
</head>
<body>
    <div class="particles">
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
            <div class="logo">🏥</div>
        </div>
        <div class="welcome-text">Welcome</div>
        <div class="health-centre-name"><?php echo htmlspecialchars($hname); ?></div>
        <div class="loader">
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
        </div>
        <div class="loading-text">LOADING DASHBOARD</div>
        <div class="loading-bar-container">
            <div class="loading-bar"></div>
        </div>
    </div>
</body>
</html>
