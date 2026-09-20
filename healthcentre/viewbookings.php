<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['healthcentre'])) {
    header("Location: login.php");
    exit;
}

// Get health centre data
$healthcentre = $_SESSION['healthcentre'];
$hid = $healthcentre['hid'] ?? 0;

// Get schedule ID from URL
$sid = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($sid == 0) {
    echo "<p style='text-align:center; margin-top:50px;'>Invalid schedule ID.</p>";
    exit;
}

// Verify this schedule belongs to this health centre
$scheduleCheck = $dao->getData("*", "schedule", "sid=$sid AND hid=$hid");

if (empty($scheduleCheck)) {
    echo "<p style='text-align:center; margin-top:50px;'>Schedule not found or access denied.</p>";
    exit;
}

$schedule = $scheduleCheck[0];

// Get vaccine name
$vaccine = $dao->getData("vname", "vaccine", "vid=" . $schedule['vid']);
$vaccineName = $vaccine[0]['vname'] ?? 'Unknown';

// Fetch all bookings for this schedule
$sql = "SELECT b.bid, b.bookdate, b.status, b.certificate,
               c.cname, c.gender, c.dob, c.id_proof, c.cid,
               p.pname as parent_name, p.mob as parent_mob
        FROM book b
        JOIN child c ON b.cid = c.cid
        JOIN parent p ON b.pid = p.pid
        WHERE b.sid = $sid
        ORDER BY b.bookdate DESC";

$bookings = $dao->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Bookings</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #64748b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        
        .back-btn:hover {
            background: #475569;
        }
        
        .schedule-info {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .schedule-info h2 {
            color: #1a237e;
            margin-bottom: 15px;
        }
        
        .schedule-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .detail-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a237e;
        }
        
        .bookings-table {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .table-header {
            padding: 20px 25px;
            background: linear-gradient(90deg, #1a237e, #3f51b5);
            color: white;
        }
        
        .table-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        thead th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #1a237e;
            font-size: 0.9rem;
        }
        
        tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.3s;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        tbody td {
            padding: 15px;
            font-size: 0.9rem;
            color: #334155;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #fef9c3;
            color: #b45309;
        }
        
        .status-completed {
            background: #dcfce7;
            color: #15803d;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .status-missed {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        .no-bookings {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .no-bookings img {
            width: 100px;
            opacity: 0.5;
            margin-bottom: 20px;
        }
        
        .stats-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1a237e;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="schedule.php" class="back-btn">← Back to Schedules</a>
        
        <div class="schedule-info">
            <h2>Schedule Details</h2>
            <div class="schedule-details">
                <div class="detail-item">
                    <div class="detail-label">Vaccine</div>
                    <div class="detail-value">💉 <?= htmlspecialchars($vaccineName) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">📅 <?= date('d F Y', strtotime($schedule['date'])) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Units Available</div>
                    <div class="detail-value">📦 <?= $schedule['units'] ?></div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($bookings)) { 
            // Calculate stats
            $total = count($bookings);
            $pending = 0;
            $completed = 0;
            
            foreach ($bookings as $b) {
                if ($b['status'] == 'pending') $pending++;
                if ($b['status'] == 'completed') $completed++;
            }
        ?>
            <div class="stats-bar">
                <div class="stat-card">
                    <div class="stat-number"><?= $total ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $pending ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $completed ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            
            <div class="bookings-table">
                <div class="table-header">
                    <h3>Bookings for this Schedule</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Child Name</th>
                            <th>Parent</th>
                            <th>Contact</th>
                            <th>Booked On</th>
                            <th>Status</th>
                            <th>ID Proof</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b) { 
                            $gender = ($b['gender'] == 'M' || $b['gender'] == 'm') ? 'M' : 'F';
                            $statusClass = 'status-' . strtolower($b['status']);
                        ?>
                            <tr>
                                <td><strong>#<?= $b['bid'] ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($b['cname']) ?> (<?= $gender ?>)<br>
                                    <small style="color:#64748b;">DOB: <?= $b['dob'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($b['parent_name']) ?></td>
                                <td><?= htmlspecialchars($b['parent_mob']) ?></td>
                                <td><?= date('d M Y', strtotime($b['bookdate'])) ?></td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= ucfirst($b['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($b['id_proof'])): ?>
                                        <a href="view_id_proof.php?cid=<?= $b['cid'] ?>" target="_blank" 
                                           style="padding: 5px 10px; background: #059669; color: white; 
                                                  border-radius: 6px; text-decoration: none; font-size: 0.8rem;
                                                  display: inline-block;">
                                            👁️ View ID
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 0.8rem;">Not provided</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="bookings-table">
                <div class="table-header">
                    <h3>Bookings for this Schedule</h3>
                </div>
                <div class="no-bookings">
                    <img src="https://cdn-icons-png.flaticon.com/512/2706/2706950.png" alt="No bookings">
                    <h3>No Bookings Yet</h3>
                    <p>No one has booked this schedule yet.</p>
                </div>
            </div>
        <?php } ?>
    </div>
</body>
</html>
