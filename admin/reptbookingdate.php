<?php 
require('../config/autoload.php'); 
$dao = new DataAccess();

// Set default dates if not provided
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01'); // First day of current month
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d'); // Today
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query based on filters
$whereConditions = [];
$whereConditions[] = "DATE(b.bookdate) BETWEEN '$fromDate' AND '$toDate'";

if ($statusFilter != 'all') {
    $whereConditions[] = "b.status = '$statusFilter'";
}

$whereClause = implode(' AND ', $whereConditions);

// Fetch bookings with detailed information
$sql = "SELECT b.bid, b.bookdate, b.status, 
               c.cname, c.gender, c.dob,
               p.pname as parent_name, p.mob as parent_mob,
               v.vname,
               h.hname,
               s.date as schedule_date
        FROM book b
        JOIN child c ON b.cid = c.cid
        JOIN parent p ON b.pid = p.pid
        JOIN schedule s ON b.sid = s.sid
        JOIN vaccine v ON s.vid = v.vid
        JOIN healthcentre h ON s.hid = h.hid
        WHERE $whereClause
        ORDER BY s.date DESC, b.bookdate DESC";

$bookings = $dao->query($sql);

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN b.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
    FROM book b
    JOIN schedule s ON b.sid = s.sid
    WHERE $whereClause";

$stats = $dao->query($statsSql);
$statistics = $stats[0];

// Include header after processing
include("header.php");
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .reports-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .page-header h3 {
            color: #1e3a8a;
            font-weight: 700;
            margin: 0;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #1e3a8a;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .btn-filter {
            padding: 10px 25px;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .btn-filter:hover {
            background: linear-gradient(90deg, #0d153a, #1e3a8a);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }
        
        .btn-reset {
            padding: 10px 25px;
            background: #64748b;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .btn-reset:hover {
            background: #475569;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }
        
        .stat-card.total {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        }
        
        .stat-card.pending {
            background: linear-gradient(135deg, #f59e0b, #f97316);
        }
        
        .stat-card.approved {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
        }
        
        .stat-card.completed {
            background: linear-gradient(135deg, #10b981, #34d399);
        }
        
        .stat-card.cancelled {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.95rem;
            opacity: 0.95;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table thead {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
        }
        
        .table thead th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border: none;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .booking-id {
            font-weight: 600;
            color: #1e3a8a;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background: #dbeafe;
            color: #1e3a8a;
        }
        
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-size: 1.1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn-print {
            padding: 10px 20px;
            background: #059669;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .btn-print:hover {
            background: #047857;
        }
        
        @media print {
            .filter-section, .action-buttons, .sidebar, .navbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container" style="margin-top:20px;">
    <div class="reports-container">
        <div class="page-header">
            <h3><i class="mdi mdi-chart-line"></i> Booking Reports</h3>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <label>From Date (Booking Date)</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $fromDate ?>" required>
                </div>
                
                <div class="form-group">
                    <label>To Date (Booking Date)</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $toDate ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="all" <?= $statusFilter == 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $statusFilter == 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="completed" <?= $statusFilter == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $statusFilter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-filter">
                        <i class="mdi mdi-filter"></i> Apply Filter
                    </button>
                </div>
                
                <div class="form-group">
                    <a href="reptbookingdate.php" class="btn-reset">
                        <i class="mdi mdi-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?= $statistics['total'] ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            
            <div class="stat-card pending">
                <div class="stat-number"><?= $statistics['pending_count'] ?></div>
                <div class="stat-label">Pending</div>
            </div>
            
            <div class="stat-card approved">
                <div class="stat-number"><?= $statistics['approved_count'] ?></div>
                <div class="stat-label">Approved</div>
            </div>
            
            <div class="stat-card completed">
                <div class="stat-number"><?= $statistics['completed_count'] ?></div>
                <div class="stat-label">Completed</div>
            </div>
            
            <div class="stat-card cancelled">
                <div class="stat-number"><?= $statistics['cancelled_count'] ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn-print">
                <i class="mdi mdi-printer"></i> Print Report
            </button>
        </div>
        
        <!-- Bookings Table -->
        <div class="table-responsive">
            <?php if ($bookings && count($bookings) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Booking Date</th>
                            <th>Schedule Date</th>
                            <th>Child Name</th>
                            <th>Parent Name</th>
                            <th>Contact</th>
                            <th>Vaccine</th>
                            <th>Health Centre</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): 
                            $statusClass = 'status-' . strtolower($booking['status']);
                            $gender = ($booking['gender'] == 'M' || $booking['gender'] == 'm') ? 'M' : 'F';
                        ?>
                            <tr>
                                <td class="booking-id">#<?= $booking['bid'] ?></td>
                                <td><?= date('d M Y', strtotime($booking['bookdate'])) ?></td>
                                <td><?= date('d M Y', strtotime($booking['schedule_date'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($booking['cname']) ?></strong>
                                    <small style="color:#64748b;"> (<?= $gender ?>)</small>
                                </td>
                                <td><?= htmlspecialchars($booking['parent_name']) ?></td>
                                <td><?= htmlspecialchars($booking['parent_mob']) ?></td>
                                <td><?= htmlspecialchars($booking['vname']) ?></td>
                                <td><?= htmlspecialchars($booking['hname']) ?></td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="mdi mdi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p>No bookings found for the selected date range and filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
