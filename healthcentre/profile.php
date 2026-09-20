<?php
require('../config/autoload.php');
$dao = new DataAccess();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['healthcentre'])) {
    header("Location: login.php");
    exit;
}

// Get health centre data from session
$healthcentre = $_SESSION['healthcentre'];
$user = $healthcentre; // Already an array with all data

// Resolve profile image (from healthcentre.himage)
$imagePath = "../uploads/" . ($user['himage'] ?? '');
if (empty($user['himage']) || !file_exists($imagePath)) {
    $imagePath = "assets/images/profile.png";
}

include('header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Health Centre Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  :root{
    --ink:#0f172a;
    --brand:#1e3a8a;
    --brand-2:#2563eb;
    --bg:#eef5ff;
    --card:#ffffff;
    --ring: rgba(37,99,235,.25);
  }

  body{
    font-family:'Poppins',sans-serif;
    background: linear-gradient(180deg,#f2f7ff 0%, #eef5ff 100%);
    color:var(--ink);
  }

  /* ---- layout wrapper under the header ---- */
  .profile-wrap{
    max-width: 1100px;
    margin: 28px auto 72px;   /* small gap under header */
    padding: 0 16px;
  }

  .profile-grid{
    display:flex;
    justify-content:center;
    align-items:stretch;
    gap: 28px;
    animation: fadeIn .45s ease;
  }

  .card{
    background:var(--card);
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(16,24,40,.08);
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .card:hover{
    transform: translateY(-3px);
    box-shadow: 0 16px 36px rgba(16,24,40,.12);
  }

  /* left: photo card */
  .photo-card{
    width: 370px;
    padding: 28px 28px 34px;
    text-align:center;
  }

  .photo-title{
    font-weight:600;
    color:var(--brand);
    margin-bottom: 14px;
    letter-spacing:.3px;
  }

  .photo-wrap{
    position:relative;
    width: 240px;
    height: 240px;
    margin: 10px auto 18px;
    border-radius:50%;
    overflow:hidden;
    border:6px solid var(--card);
    box-shadow: 0 0 0 4px var(--ring);
  }
  .photo-wrap img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
    transition: transform .35s ease;
  }
  .photo-wrap:hover img{ transform: scale(1.04); }

  .photo-overlay{
    position:absolute;
    inset:auto 0 0 0;
    background: linear-gradient(180deg,transparent 0%, rgba(0,0,0,.65) 100%);
    color:#fff;
    padding:10px 0;
    font-size:14px;
    opacity:0;
    transition:opacity .25s ease;
  }
  .photo-wrap:hover .photo-overlay{ opacity:1; }

  .photo-actions{
    display:flex;
    justify-content:center;
    gap:12px;
    margin-top:8px;
  }
  .btn{
    border:none;
    border-radius: 12px;
    padding: 10px 16px;
    font-size:14px;
    cursor:pointer;
    transition: background .2s ease, transform .2s ease;
  }
  .btn-primary{
    color:#fff;
    background: linear-gradient(45deg, var(--brand), var(--brand-2));
  }
  .btn-primary:hover{ transform:translateY(-1px); }
  .btn-ghost{
    background: #f3f6ff;
    color: var(--brand);
  }
  .btn-ghost:hover{ background:#e7eeff; }

  /* right: details card */
  .details-card{
    flex:1 1 520px;
    min-width: 460px;
    padding: 30px 36px;
  }
  .name{
    font-size: 28px;
    line-height:1.2;
    color:var(--brand);
    font-weight:700;
    margin-bottom: 12px;
  }
  .muted{
    color:#677089;
    font-size:13px;
    letter-spacing:.2px;
  }
  .info-list{
    margin-top:18px;
    display:grid;
    row-gap: 12px;
  }
  .info-item{
    display:flex;
    align-items:center;
    gap:12px;
    padding: 12px 14px;
    border-radius: 12px;
    background: #f8faff;
  }
  .info-item i{ color:var(--brand-2); width:20px; text-align:center; }

  .actions{
    margin-top: 18px;
    display:flex;
    gap:12px;
  }

  @media (max-width: 980px){
    .profile-grid{ flex-direction:column; }
    .photo-card{ width:100%; }
    .details-card{ min-width: unset; }
  }

  @keyframes fadeIn{
    from{opacity:0; transform: translateY(10px);}
    to{opacity:1; transform: translateY(0);}
  }
</style>
</head>
<body>
  <main class="profile-wrap">
    <div class="profile-grid">
      <!-- Photo Card -->
      <section class="card photo-card">
        <div class="photo-title">Profile Photo</div>

        <div class="photo-wrap">
          <img src="<?= htmlspecialchars($imagePath) ?>" alt="Profile Picture">
          <div class="photo-overlay"><i class="fa-solid fa-camera"></i> Change photo</div>
        </div>

        <div class="photo-actions">
          <a class="btn btn-primary" href="edit_profile.php"><i class="fa-regular fa-pen-to-square"></i> Edit Profile</a>
          <a class="btn btn-ghost" href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        </div>
      </section>

      <!-- Details Card -->
      <section class="card details-card">
        <div class="name"><?= htmlspecialchars($user['hname'] ?? 'Health Centre') ?></div>
        <div class="muted">Your official health centre profile</div>

        <div class="info-list">
          <div class="info-item">
            <i class="fa-solid fa-envelope"></i>
            <span><?= htmlspecialchars($user['email'] ?? 'No email available') ?></span>
          </div>
          <div class="info-item">
            <i class="fa-solid fa-phone"></i>
            <span><?= htmlspecialchars($user['phone'] ?? 'No phone number') ?></span>
          </div>
          <div class="info-item">
            <i class="fa-solid fa-location-dot"></i>
            <span><?= htmlspecialchars($user['address'] ?? 'No address provided') ?></span>
          </div>
          <div class="info-item">
            <i class="fa-solid fa-calendar-day"></i>
            <span>Registered on <?= htmlspecialchars($user['created_at'] ?? 'N/A') ?></span>
          </div>
        </div>

        <div class="actions">
          <a class="btn btn-primary" href="edit_profile.php"><i class="fa-regular fa-pen-to-square"></i> Edit Details</a>
          <a class="btn btn-ghost" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
      </section>
    </div>
  </main>
</body>
</html>
