<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get Health Centre data from Session
$healthcentre = $_SESSION['healthcentre'] ?? null;
$centreName = $healthcentre['hname'] ?? "Health Centre";
$hid = $healthcentre['hid'] ?? 0;

// Fetch real statistics
$totalSchedules = 0;
$totalBookings = 0;
$completedVaccines = 0;
$pendingVaccines = 0;
$todaySchedules = 0;

if ($hid) {
    // Total schedules
    $result = $dao->query("SELECT COUNT(*) as total FROM schedule WHERE hid=$hid");
    $totalSchedules = $result[0]['total'] ?? 0;
    
    // Total bookings
    $bookingResult = $dao->query("SELECT COUNT(*) as total FROM book b 
                                   JOIN schedule s ON b.sid = s.sid 
                                   WHERE s.hid=$hid");
    $totalBookings = $bookingResult[0]['total'] ?? 0;
    
    // Completed vaccines
    $completedResult = $dao->query("SELECT COUNT(*) as total FROM book b 
                                    JOIN schedule s ON b.sid = s.sid 
                                    WHERE s.hid=$hid AND b.status='completed'");
    $completedVaccines = $completedResult[0]['total'] ?? 0;
    
    // Pending vaccines
    $pendingResult = $dao->query("SELECT COUNT(*) as total FROM book b 
                                  JOIN schedule s ON b.sid = s.sid 
                                  WHERE s.hid=$hid AND b.status='pending'");
    $pendingVaccines = $pendingResult[0]['total'] ?? 0;
    
    // Today's schedules
    $today = date('Y-m-d');
    $todayResult = $dao->query("SELECT COUNT(*) as total FROM schedule 
                                WHERE hid=$hid AND date='$today'");
    $todaySchedules = $todayResult[0]['total'] ?? 0;
}

// Recent bookings
$recentBookings = [];
if ($hid) {
    $recentBookings = $dao->query("SELECT b.bid, b.bookdate, b.status, 
                                          c.cname, v.vname, s.date as schedule_date,
                                          p.pname as parent_name
                                   FROM book b
                                   JOIN child c ON b.cid = c.cid
                                   JOIN parent p ON b.pid = p.pid
                                   JOIN schedule s ON b.sid = s.sid
                                   JOIN vaccine v ON s.vid = v.vid
                                   WHERE s.hid=$hid
                                   ORDER BY b.bookdate DESC
                                   LIMIT 5");
}

// Upcoming schedules
$upcomingSchedules = [];
if ($hid) {
    $upcomingSchedules = $dao->query("SELECT s.sid, s.date, s.units, v.vname
                                      FROM schedule s
                                      JOIN vaccine v ON s.vid = v.vid
                                      WHERE s.hid=$hid AND s.date >= CURDATE()
                                      ORDER BY s.date ASC
                                      LIMIT 5");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $centreName; ?> Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fb 0%, #e8eef5 100%);
            background-attachment: fixed;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px 60px;
        }
        
        .page-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin: 40px 0 15px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInDown 0.6s ease;
        }
        
        .page-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 40px;
            animation: fadeInUp 0.6s ease 0.2s backwards;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(30, 58, 138, 0.1);
            animation: cardFadeIn 0.6s ease backwards;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        
        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #06b6d4);
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(30, 58, 138, 0.15);
            border-color: #3b82f6;
        }
        
        .stat-card:hover::after {
            opacity: 1;
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: inline-block;
            padding: 15px;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.1), rgba(59, 130, 246, 0.1));
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .stat-number {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 12px 0;
        }
        
        .stat-label {
            font-size: 0.95rem;
            color: #64748b;
            font-weight: 500;
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Section Card */
        .section-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.08);
            border: 1px solid rgba(30, 58, 138, 0.1);
            animation: fadeInUp 0.6s ease 0.4s backwards;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(30, 58, 138, 0.1);
        }
        
        /* Recent Bookings */
        .booking-item {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.3s;
        }
        
        .booking-item:last-child {
            border-bottom: none;
        }
        
        .booking-item:hover {
            background: #f8f9fa;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .booking-child {
            font-weight: 600;
            color: #1a237e;
        }
        
        .booking-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fef9c3;
            color: #b45309;
        }
        
        .status-completed {
            background: #dcfce7;
            color: #15803d;
        }
        
        .booking-details {
            font-size: 0.85rem;
            color: #64748b;
        }
        
        /* Upcoming Schedules */
        .schedule-item {
            padding: 15px;
            border-left: 4px solid #1a237e;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 12px;
            transition: 0.3s;
        }
        
        .schedule-item:hover {
            background: #e8eaf6;
            transform: translateX(5px);
        }
        
        .schedule-date {
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 5px;
        }
        
        .schedule-vaccine {
            font-size: 0.9rem;
            color: #334155;
        }
        
        .schedule-units {
            font-size: 0.85rem;
            color: #64748b;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }
        
        .empty-state img {
            width: 80px;
            opacity: 0.5;
            margin-bottom: 15px;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            padding: 18px 25px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            text-decoration: none;
            border-radius: 15px;
            text-align: center;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .action-btn:hover::before {
            left: 100%;
        }
        
        .action-btn:hover {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 35px rgba(30,58,138,0.4);
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        /* Vaccines Grid */
        .vaccines-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .vaccine-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 20px;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 2px solid rgba(30, 58, 138, 0.1);
            position: relative;
        }
        
        .vaccine-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.05), rgba(59, 130, 246, 0.05));
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .vaccine-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 20px 40px rgba(30, 58, 138, 0.15);
            border-color: #3b82f6;
        }
        
        .vaccine-card:hover::before {
            opacity: 1;
        }
        
        .vaccine-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
            transition: transform 0.4s ease;
            position: relative;
            z-index: 1;
        }
        
        .vaccine-card:hover .vaccine-image {
            transform: scale(1.05);
        }
        
        .vaccine-card h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 12px 0;
            color: #1e3a8a;
        }
        
        .vaccine-period {
            font-size: 0.9rem;
            color: #64748b;
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1 class="page-title">Welcome to <?= htmlspecialchars($centreName) ?></h1>
        <p class="page-subtitle">Manage your schedules, bookings, and vaccine operations</p>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-number"><?= $totalSchedules ?></div>
                <div class="stat-label">Total Schedules</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?= $totalBookings ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?= $completedVaccines ?></div>
                <div class="stat-label">Completed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?= $pendingVaccines ?></div>
                <div class="stat-label">Pending</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📆</div>
                <div class="stat-number"><?= $todaySchedules ?></div>
                <div class="stat-label">Today's Schedules</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section-card" style="margin-bottom: 30px;">
            <h3 class="section-title">⚡ Quick Actions</h3>
            <div class="quick-actions">
                <a href="schedule.php" class="action-btn">
                    📅 Manage Schedules
                </a>
                <a href="bookings.php" class="action-btn">
                    📋 View Bookings
                </a>
                <a href="profile.php" class="action-btn">
                    ⚙️ Settings
                </a>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Bookings -->
            <div class="section-card">
                <h3 class="section-title">📋 Recent Bookings</h3>
                <?php if (!empty($recentBookings)) { ?>
                    <?php foreach ($recentBookings as $booking) { 
                        $statusClass = 'status-' . strtolower($booking['status']);
                    ?>
                        <div class="booking-item">
                            <div class="booking-header">
                                <span class="booking-child"><?= htmlspecialchars($booking['cname']) ?></span>
                                <span class="booking-status <?= $statusClass ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </div>
                            <div class="booking-details">
                                <div>💉 <?= htmlspecialchars($booking['vname']) ?></div>
                                <div>👤 Parent: <?= htmlspecialchars($booking['parent_name']) ?></div>
                                <div>📅 Schedule: <?= date('d M Y', strtotime($booking['schedule_date'])) ?></div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="no-data">No recent bookings</div>
                <?php } ?>
            </div>

            <!-- Upcoming Schedules -->
            <div class="section-card">
                <h3 class="section-title">📆 Upcoming Schedules</h3>
                <?php if (!empty($upcomingSchedules)) { ?>
                    <?php foreach ($upcomingSchedules as $schedule) { ?>
                        <div class="schedule-item">
                            <div class="schedule-date">
                                📅 <?= date('d F Y', strtotime($schedule['date'])) ?>
                            </div>
                            <div class="schedule-vaccine">
                                💉 <?= htmlspecialchars($schedule['vname']) ?>
                            </div>
                            <div class="schedule-units">
                                📦 <?= $schedule['units'] ?> units available
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="no-data">No upcoming schedules</div>
                <?php } ?>
            </div>
        </div>

        <!-- Available Vaccines Section -->
        <div class="section-card" style="margin-top: 30px;">
            <h3 class="section-title">💉 Available Vaccines</h3>
            <div class="vaccines-grid">
                <?php
                $vaccines = $dao->query("SELECT vid, vname, vimage, period FROM vaccine LIMIT 8");
                
                if (!empty($vaccines)) {
                    foreach ($vaccines as $vaccine) { ?>
                        <div class="vaccine-card">
                            <img src="../uploads/<?= htmlspecialchars($vaccine['vimage']) ?>" 
                                 alt="<?= htmlspecialchars($vaccine['vname']) ?>" 
                                 class="vaccine-image" />
                            <h4><?= htmlspecialchars($vaccine['vname']) ?></h4>
                            <p class="vaccine-period">📅 <?= htmlspecialchars($vaccine['period']) ?></p>
                        </div>
                    <?php }
                } else { ?>
                    <div class="no-data" style="grid-column: 1 / -1;">No vaccines available</div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
