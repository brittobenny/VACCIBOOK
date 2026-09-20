<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php'); 

// Get vid or hid from query string
$vid = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
$hid = isset($_GET['hid']) ? intval($_GET['hid']) : 0;

// Prepare SQL and heading
$heading = "";
if ($vid > 0) {
    $sql = "SELECT s.sid, s.date, s.units, v.vname, h.hname, h.loc 
            FROM schedule s
            JOIN vaccine v ON s.vid = v.vid
            JOIN healthcentre h ON s.hid = h.hid
            WHERE s.vid = $vid";
    $info = $dao->query("SELECT vname FROM vaccine WHERE vid=$vid");
    $heading = "Available Schedules for " . ($info ? htmlspecialchars($info[0]['vname']) : "Vaccine");
} elseif ($hid > 0) {
    $sql = "SELECT s.sid, s.date, s.units, v.vname, h.hname, h.loc 
            FROM schedule s
            JOIN vaccine v ON s.vid = v.vid
            JOIN healthcentre h ON s.hid = h.hid
            WHERE s.hid = $hid";
    $info = $dao->query("SELECT hname FROM healthcentre WHERE hid=$hid");
    $heading = "Available Schedules at " . ($info ? htmlspecialchars($info[0]['hname']) : "Health Centre");
} else {
    echo "<p>No vaccine or health centre selected.</p>";
    exit;
}

// Fetch data
$schedules = $dao->query($sql);
?>

<style>
.page-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    margin: 40px 0 30px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.schedule-container {
    max-width: 1100px;
    margin: 0 auto 60px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    padding: 0 20px;
}

.schedule-card {
    background: #fff;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}
.schedule-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

.schedule-info p {
    margin: 10px 0;
    font-size: 0.95rem;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
}
.schedule-info strong {
    color: #1e3a8a;
}

/* Date styling */
.schedule-info .date {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e3a8a;
}

/* Units badge */
.units {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 5px;
}
.units.available { background: #dcfce7; color: #15803d; }
.units.low { background: #fef9c3; color: #b45309; }
.units.none { background: #fee2e2; color: #b91c1c; }

/* Book button */
.book-btn {
    display: block;
    width: 100%;
    text-align: center;
    padding: 12px;
    border-radius: 12px;
    background: linear-gradient(90deg, #1a237e, #3f51b5);
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    margin-top: 15px;
    transition: 0.3s;
}
.book-btn:hover {
    background: linear-gradient(90deg, #0d153a, #1a237e);
    transform: scale(1.02);
}
</style>

<div class="page-title"><?= $heading ?></div>

<div class="schedule-container">
    <?php if (!empty($schedules)) { 
        foreach ($schedules as $s) { 
            $units = intval($s['units']);
            $unitClass = $units == 0 ? "none" : ($units < 5 ? "low" : "available");
        ?>
            <div class="schedule-card">
                <div class="schedule-info">
                    <p class="date">📅 <?= htmlspecialchars($s['date']) ?></p>
                    <p>💉 <strong>Vaccine:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong> <?= htmlspecialchars($s['vname']) ?></p>
                    <p>🏥 <strong>Health Centre:</strong> <?= htmlspecialchars($s['hname']) ?> (<?= htmlspecialchars($s['loc']) ?>)</p>
                    <p class="units <?= $unitClass ?>">Units Available: <?= $units ?></p>
                </div>
                <a class="book-btn" href="bookvaccine.php?sid=<?= $s['sid'] ?>">Book Now</a>
            </div>
    <?php } 
     } else { ?>
    <div style="text-align:center; grid-column: 1 / -1; padding:40px; color:#64748b;">
        <img src="https://cdn-icons-png.flaticon.com/512/25/25231.png" width="100" alt="No schedules">
        <p style="margin-top:15px; font-size:1.1rem; font-weight:500;">No available schedules found</p>
    </div>
<?php } ?>

</div>
