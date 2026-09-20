<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php');
?>

<style>
  body {
    background: #ffffff;
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
  }

  /* Gradient heading style */
  .page-title {
    text-align: center;
    font-size: 2.25rem;
    font-weight: 700;
    margin: 40px 0 20px;
    letter-spacing: 0.05em;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .page-subtitle {
    text-align: center;
    font-size: 1rem;
    color: #475569;
    margin-bottom: 40px;
  }

  .district-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Force 3 cards per row */
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 40px 60px;
  }

  /* Gradient card style */
  .district-card {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    border-radius: 20px;
    overflow: hidden;
    text-align: center;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    padding: 20px;
    color: #fff;
  }

  .district-card:hover {
    transform: scale(1.05);
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
  }

  .card-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  /* Gradient text for district names */
  .card-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 15px;
    background: #fff;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  /* Softer button style */
  .card-content .book-btn {
    display: inline-block;
    padding: 12px 24px;
    background-color: #0f172a;
    color: #fff;
    font-weight: 400;
    text-decoration: none;
    border-radius: 50px;
    transition: background-color 0.3s ease;
    margin-top: 10px;
    font-size: 0.95rem;
  }

  .card-content .book-btn:hover {
    background-color: #1e3a8a;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .district-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
  }

  @media (max-width: 480px) {
    .district-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="page-title">Select District</div>
<div class="page-subtitle">Choose the district of the health center according to your convenience.</div>

<div class="district-grid">
  <?php
  $q = "SELECT * FROM district";
  $info = $dao->query($q);

  if ($info) {
    foreach ($info as $district) { ?>
      <div class="district-card">
        <div class="card-content">
          <h3><?php echo $district['dname']; ?></h3>
          <a href="displayhealth_centre.php?id=<?= $district['did'] ?>" class="book-btn">
            View Health Centres
          </a>
        </div>
      </div>
    <?php }
  } else { ?>
    <p style="text-align:center; font-size:1.2rem; color:#666;">No districts found.</p>
  <?php } ?>
</div>
