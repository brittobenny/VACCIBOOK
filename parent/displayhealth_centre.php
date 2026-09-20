<?php 
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php'); 

// Validate district ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<script>alert('Invalid district ID!'); window.location.href='index.php';</script>");
}

$did = intval($_GET['id']);
$_SESSION['did'] = $did;

// Fetch district name
$dquery = "SELECT dname FROM district WHERE did = $did";
$dresult = $dao->query($dquery);
$district_name = $dresult && isset($dresult[0]['dname']) ? htmlspecialchars($dresult[0]['dname']) : "Unknown District";

// Optional: capture email if logged in
if (isset($_SESSION['email'])) {
    $name = $_SESSION['email'];
}
?>

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #f9fafb;
    color: #1e293b;
}

/* Page Title Section */
.page-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    margin: 40px 0 10px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.page-subtitle {
    text-align: center;
    font-size: 1rem;
    color: #64748b;
    margin-bottom: 40px;
}

/* Grid Layout for Cards */
.container {
    width: 90%;
    max-width: 1200px;
    margin: auto;
}
.district-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 25px;
    padding-bottom: 60px;
}

/* Modern Card Styling */
.health-card {
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    text-align: center;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    padding: 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 2px solid transparent;
}
.health-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 22px rgba(30, 58, 138, 0.2);
    border-color: #1e3a8a;
}

.health-card h3 {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 12px 0;
    color: #1e3a8a;
}

.card-image {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 14px;
    margin-bottom: 12px;
}

/* Button Style */
.btn-select {
    display: inline-block;
    padding: 12px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    margin-top: 10px;
}
.btn-select:hover {
    background: linear-gradient(90deg, #0f172a, #1e3a8a);
    box-shadow: 0 4px 10px rgba(30,58,138,0.3);
}

/* Empty State */
.no-centre {
    text-align: center; 
    font-size: 1.2rem; 
    color: #64748b;
    margin-top: 40px;
}
</style>

<!-- Page Title -->
<div class="page-title">Health Centres in <?php echo $district_name; ?></div>
<div class="page-subtitle">Choose the most convenient health centre for you</div>

<!-- Healthcentre Cards -->
<div class="container">
    <div class="district-grid">
        <?php
        $q = "SELECT * FROM healthcentre WHERE did = $did";
        $info = $dao->query($q);

        if (empty($info)) {
            echo "<p class='no-centre'>No Health Centre Available</p>";
        } else {
            foreach ($info as $centre) { ?>
                <div class="health-card">
                    <img src="<?php echo BASE_URL.'uploads/'.$centre['himage']; ?>" 
                         alt="Health Centre" 
                         class="card-image" />
                    <h3><?php echo htmlspecialchars($centre['hname']); ?></h3>
                    <a href="schedule.php?hid=<?php echo $centre['hid']; ?>" 
                       class="btn-select">View Schedules</a>
                </div>
            <?php }
        }
        ?>
    </div>
</div>
