<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect current page accurately
$currentPage = strtolower(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

// Get parent name from session
$parentName = isset($_SESSION['parent']['name']) ? $_SESSION['parent']['name'] : 'Parent';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vaccibook Dashboard</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/modern-theme.css" />

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
      background-attachment: fixed;
    }

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

    /* Branding */
    .branding {
      position: relative;
      width: 200px;
      height: 55px;
      display: flex;
      justify-content: flex-start;
      align-items: center;
      overflow: hidden;
      font-weight: 600;
      color: #1a237e;
    }

    .branding span, .branding img {
      position: absolute;
      opacity: 0;
      transform: translateY(100%);
      transition: all 0.7s ease-in-out;
    }

    .branding span {
      font-size: 18px;
      font-weight: 600;
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .branding img {
      height: 50px;
      object-fit: contain;
      filter: drop-shadow(0 2px 8px rgba(30, 58, 138, 0.2));
    }

    .branding span.active, .branding img.active {
      opacity: 1;
      transform: translateY(0);
    }

   nav {
  flex: 1;
  display: flex;
  justify-content: flex-end;
  margin-right: 20px;
}



    nav ul {
      list-style: none;
      display: flex;
      gap: 40px;
    }

    nav ul li {
      position: relative;
    }

    nav ul li a {
      position: relative;
      color: #475569;
      text-decoration: none;
      font-weight: 500;
      font-size: 16px;
      padding: 10px 0;
      transition: all 0.3s ease;
      letter-spacing: 0.3px;
    }

    nav ul li a:hover {
      color: #3b82f6;
    }

    /* Underline animation */
    nav ul li a::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -2px;
      width: 0%;
      height: 3px;
      background: linear-gradient(90deg, #1e3a8a, #3b82f6);
      border-radius: 10px;
      transition: width 0.3s ease;
    }

    nav ul li a:hover::after {
      width: 100%;
    }

    /* Active underline */
    nav ul li.active a::after {
      width: 100%;
      background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    }

    nav ul li.active a {
      font-weight: 600;
      color: #1e3a8a;
    }

    /* Logout button */
    .logout-btn {
      padding: 11px 24px;
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
      color: #fff;
      font-weight: 600;
      border-radius: 30px;
      text-decoration: none;
      box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .logout-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
      z-index: -1;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .logout-btn:hover::before {
      opacity: 1;
    }

    .logout-btn:hover {
      box-shadow: 0 6px 25px rgba(30, 58, 138, 0.5);
      transform: translateY(-2px);
    }
  </style>
</head>
<body>
  <header>
    <!-- Greeting Animation -->
    <div class="branding">
      <span class="greet active">Hi <?= htmlspecialchars($parentName) ?>!</span>
      <img src="assets/images/log.png" alt="Logo" class="logo">
    </div>

    <!-- Centered Navigation -->
    <nav>
      <ul>
        <li class="<?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>"><a href="dashboard.php">HOME</a></li>
        <li class="<?= ($currentPage === 'healthcentres.php') ? 'active' : '' ?>"><a href="healthcentres.php">HEALTH CENTRES</a></li>
        <li class="<?= ($currentPage === 'bookings.php') ? 'active' : '' ?>"><a href="bookings.php">BOOKINGS</a></li>
        <li class="<?= ($currentPage === 'feedback.php') ? 'active' : '' ?>"><a href="feedback.php">FEEDBACKS</a></li>
        <li class="<?= ($currentPage === 'profile.php') ? 'active' : '' ?>"><a href="profile.php">PROFILE</a></li>
        <li class="<?= ($currentPage === 'contact.php') ? 'active' : '' ?>"><a href="contact.php">CONTACT</a></li>
      </ul>
    </nav>

    <!-- Logout Button -->
    <a href="logout.php" class="logout-btn">Logout</a>
  </header>

  <!-- Greeting animation script -->
  <script>
    const greet = document.querySelector('.branding .greet');
    const logo = document.querySelector('.branding .logo');
    let showGreet = true;

    setInterval(() => {
      if (showGreet) {
        greet.classList.remove('active');
        logo.classList.add('active');
      } else {
        logo.classList.remove('active');
        greet.classList.add('active');
      }
      showGreet = !showGreet;
    }, 3000);
  </script>
</body>
</html>
