<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Select Your Vaccine</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      background: #ffffff;
      font-family: 'Poppins', sans-serif;
      color: #1a1a1a;
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }

    /* Page Title */
    .page-title {
      text-align: center;
      font-size: 2.5rem;
      font-weight: 700;
      margin: 50px 0 30px;
      letter-spacing: 0.05em;
      background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Filter Bar */
    .filter-container {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 40px;
      padding: 0 20px;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }

    .filter-container input[type="text"] {
      flex: 1 1 60%;
      padding: 14px 24px;
      font-size: 1rem;
      border-radius: 50px;
      border: 2px solid transparent;
      background-color: #fff;
      font-weight: 500;
      outline: none;
      box-shadow: 0 2px 6px rgba(6, 182, 212, 0.15);
      background-image: linear-gradient(white, white), linear-gradient(90deg, #1e3a8a, #3b82f6, #06b6d4);
      background-origin: border-box;
      background-clip: padding-box, border-box;
    }

    .filter-container input[type="text"]:focus {
      box-shadow: 0 0 6px rgba(6, 182, 212, 0.6);
    }

    .filter-container select {
      flex: 0 0 180px;
      padding: 12px 20px;
      font-size: 1rem;
      border-radius: 50px;
      border: 2px solid transparent;
      background-color: #fff;
      font-weight: 500;
      outline: none;
      box-shadow: 0 2px 6px rgba(6, 182, 212, 0.15);
      background-image: 
        linear-gradient(white, white),
        linear-gradient(90deg, #1e3a8a, #3b82f6, #06b6d4);
      background-origin: padding-box, border-box;
      background-clip: padding-box, border-box;
      transition: 0.3s;
    }

    .filter-container select:hover {
      box-shadow: 0 0 8px rgba(6, 182, 212, 0.5);
    }

    /* Vaccine Grid */
    .vaccine-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      padding: 0 20px 60px;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Vaccine Card */
    .vaccine-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 20px;
      text-align: center;
      color: #000;
      box-shadow: 0 6px 20px rgba(30, 58, 138, 0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid #e2e8f0;
      opacity: 0;
      animation: cardFadeIn 0.6s ease-out forwards;
    }
    
    @keyframes cardFadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .vaccine-card:nth-child(1) { animation-delay: 0.1s; }
    .vaccine-card:nth-child(2) { animation-delay: 0.2s; }
    .vaccine-card:nth-child(3) { animation-delay: 0.3s; }
    .vaccine-card:nth-child(4) { animation-delay: 0.4s; }
    .vaccine-card:nth-child(5) { animation-delay: 0.5s; }
    .vaccine-card:nth-child(6) { animation-delay: 0.6s; }
    .vaccine-card:nth-child(7) { animation-delay: 0.7s; }
    .vaccine-card:nth-child(8) { animation-delay: 0.8s; }

    .vaccine-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 12px 30px rgba(30, 58, 138, 0.2);
      border-color: #3b82f6;
    }

    .vaccine-image {
      width: 100%;
      height: 140px;
      object-fit: cover;
      border-radius: 15px;
      margin-bottom: 15px;
    }

    .vaccine-card h6 {
      font-size: 1.2rem;
      font-weight: 600;
      margin: 5px 0 8px;
      color: #000;
    }

    .vaccine-card p {
      font-size: 1rem;
      margin: 0 0 15px;
      font-weight: 500;
      color: #555;
    }

    /* Book Now button */
    .book-btn {
      background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
      color: #fff;
      padding: 10px 24px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 30px;
      text-decoration: none;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .book-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }

    /* No Results Message */
    #noResults {
      text-align: center;
      font-size: 1.3rem;
      color: #666;
      font-weight: 500;
      margin-top: 20px;
      display: none;
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .vaccine-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 600px) {
      .vaccine-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <div class="page-title">Select Your Vaccine</div>

  <!-- Search and Filter -->
  <div class="filter-container">
    <input type="text" id="search" placeholder="Search by name..." oninput="filterVaccines()" aria-label="Search vaccines by name" />
    <select id="periodFilter" onchange="filterVaccines()" aria-label="Filter vaccines by period">
      <option value="">Filter by period</option>
      <option value="6 weeks">6 weeks</option>
      <option value="10 weeks">10 weeks</option>
      <option value="14 weeks">14 weeks</option>
      <option value="9 months">9 months</option>
    </select>
  </div>

  <!-- Grid of Vaccines -->
  <div class="vaccine-grid" id="vaccineGrid">
    <?php
    $sql = "SELECT vid, vname, vimage, period FROM vaccine";
    $result = $dao->query($sql);

    if ($result) {
      foreach ($result as $vaccine) {
        $vname = htmlspecialchars($vaccine['vname']);
        $vimage = htmlspecialchars($vaccine['vimage']);
        $period = htmlspecialchars($vaccine['period']);
        $vid = urlencode($vaccine['vid']);
        echo "
          <div class='vaccine-card'>
            <img class='vaccine-image' src='../uploads/{$vimage}' alt='{$vname}'>
            <h6>{$vname}</h6>
            <p>{$period}</p>
            <a class='book-btn' href='schedule.php?vid={$vid}'>Book Now</a>
          </div>
        ";
      }
    } else {
      echo "<p style='text-align:center; color:#666; font-size:1.2rem;'>No vaccines found.</p>";
    }
    ?>
  </div>

  <div id="noResults">No available vaccines</div>

  <script>
    function filterVaccines() {
      const search = document.getElementById('search').value.toLowerCase();
      const period = document.getElementById('periodFilter').value.toLowerCase();
      const cards = document.querySelectorAll('.vaccine-card');
      const noResults = document.getElementById('noResults');

      let visibleCount = 0;

      cards.forEach(card => {
        const name = card.querySelector('h6').innerText.toLowerCase();
        const vaccinePeriod = card.querySelector('p').innerText.toLowerCase();
        const matchesName = name.includes(search);
        const matchesPeriod = period === "" || vaccinePeriod.includes(period);

        if (matchesName && matchesPeriod) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      // Show "No available vaccines" message if no cards match
      noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  </script>

</body>
</html>
<?php include 'fd.php'; ?>