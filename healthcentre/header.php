<?php
// Start session for login tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page name dynamically
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vaccibook Dashboard</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- Modern Theme CSS -->
  <link rel="stylesheet" href="assets/css/modern-theme.css" />

  <style>
    /* Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
      background-attachment: fixed;
      color: #0f172a;
    }

    /* Header Styles */
    header {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
      height: 85px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 50px;
      box-shadow: 0 4px 20px rgba(102, 126, 234, 0.08);
      position: sticky;
      top: 0;
      z-index: 1000;
      border-bottom: 1px solid rgba(102, 126, 234, 0.1);
      backdrop-filter: blur(10px);
    }

    /* Logo */
    .logo img {
      height: 55px;
      filter: drop-shadow(0 2px 8px rgba(30, 58, 138, 0.2));
      transition: transform 0.3s ease;
    }

    .logo img:hover {
      transform: scale(1.05);
    }

    /* Navbar */
    nav ul {
      list-style: none;
      display: flex;
      gap: 35px;
    }

    nav ul li {
      position: relative;
      opacity: 0;
      transform: translateY(-15px);
      animation: fadeInSlide 0.8s forwards;
    }

    nav ul li:nth-child(1) { animation-delay: 0.1s; }
    nav ul li:nth-child(2) { animation-delay: 0.2s; }
    nav ul li:nth-child(3) { animation-delay: 0.3s; }
    nav ul li:nth-child(4) { animation-delay: 0.4s; }
    nav ul li:nth-child(5) { animation-delay: 0.5s; }

    @keyframes fadeInSlide {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Navbar Links */
    nav ul li a {
      color: #475569;
      text-decoration: none;
      font-weight: 500;
      font-size: 15px;
      letter-spacing: 0.3px;
      padding: 10px 0;
      position: relative;
      transition: all 0.3s ease;
    }

    nav ul li a:hover {
      color: #3b82f6;
    }

    nav ul li.active a {
      color: #1e3a8a;
      font-weight: 600;
    }

    nav ul li a::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 0%;
      height: 3px;
      background: linear-gradient(90deg, #1e3a8a, #3b82f6);
      border-radius: 10px;
      transition: width 0.3s ease;
    }

    nav ul li a:hover::after,
    nav ul li.active a::after {
      width: 100%;
    }

    /* Dropdown Menu */
    nav ul li .dropdown {
      position: absolute;
      top: 120%;
      left: -10px;
      background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
      display: none;
      flex-direction: column;
      min-width: 190px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
      z-index: 1000;
      border: 1px solid rgba(102, 126, 234, 0.1);
      animation: dropdownSlide 0.3s ease;
    }

    @keyframes dropdownSlide {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    nav ul li .dropdown a {
      color: #475569;
      padding: 13px 18px;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid rgba(102, 126, 234, 0.05);
    }

    nav ul li .dropdown a:last-child {
      border-bottom: none;
    }

    nav ul li .dropdown a:hover {
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
      color: white;
      padding-left: 22px;
    }

    nav ul li .dropdown a i {
      transition: transform 0.3s ease;
    }

    nav ul li .dropdown a:hover i {
      transform: scale(1.2);
    }

    nav ul li:hover .dropdown {
      display: flex;
    }
  </style>
</head>
<body>
  <header>
    <!-- Logo -->
    <div class="logo">
      <img src="assets/images/log.png" alt="Logo" />
    </div>

    <!-- Navbar -->
    <nav>
      <ul>
        <li class="<?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>"><a href="dashboard.php">DASHBOARD</a></li>
        <li class="<?= ($currentPage == 'schedule.php') ? 'active' : '' ?>"><a href="schedule.php">SCHEDULES</a></li>
        <li class="<?= ($currentPage == 'bookings.php') ? 'active' : '' ?>"><a href="bookings.php">BOOKINGS</a></li>
        <li class="<?= ($currentPage == 'feedbacks.php') ? 'active' : '' ?>"><a href="feedbacks.php">FEEDBACKS</a></li>
        <li class="<?= ($currentPage == 'settings.php' || $currentPage == 'profile.php') ? 'active' : '' ?>">
          <a href="#">SETTINGS <i class="fa-solid fa-caret-down"></i></a>
          <div class="dropdown">
            <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
          </div>
        </li>
      </ul>
    </nav>
  </header>
</body>
</html>
