<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$parent = $_SESSION['parent'] ?? null;
$parentId = $parent['pid'] ?? 0;

// Handle cancellation
if (isset($_GET['cancel']) && isset($_GET['bid'])) {
    $bid = intval($_GET['bid']);
    
    // Get booking details to restore units
    $booking = $dao->getData("*", "book", "bid=$bid AND pid=$parentId");
    
    if (!empty($booking)) {
        $sid = $booking[0]['sid'];
        
        // Delete booking
        if ($dao->delete("book", "bid=$bid")) {
            // Restore units
            $schedule = $dao->getData("units", "schedule", "sid=$sid");
            if (!empty($schedule)) {
                $newUnits = $schedule[0]['units'] + 1;
                $dao->update(array('units' => $newUnits), "schedule", "sid=$sid");
            }
            
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              icon: 'success',
              title: 'Booking Cancelled!',
              text: 'Your booking has been cancelled successfully.',
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              window.location.href = 'bookings.php';
            });
            </script>
            ";
        }
    }
}

// Fetch all bookings for this parent
$sql = "SELECT b.bid, b.bookdate, b.status, b.certificate,
               c.cname, c.gender, c.dob, 
               v.vname, h.hname, h.loc, s.date as schedule_date, s.sid
        FROM book b
        JOIN child c ON b.cid = c.cid
        JOIN schedule s ON b.sid = s.sid
        JOIN vaccine v ON s.vid = v.vid
        JOIN healthcentre h ON s.hid = h.hid
        WHERE b.pid = $parentId
        ORDER BY s.date DESC, b.bookdate DESC";

$bookings = $dao->query($sql);
?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffffff;
}

.page-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin: 50px 0 30px;
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

.bookings-container {
    max-width: 1200px;
    margin: 0 auto 60px;
    padding: 0 20px;
}

.bookings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.booking-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 8px 24px rgba(30, 58, 138, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
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

.booking-card:nth-child(1) { animation-delay: 0.1s; }
.booking-card:nth-child(2) { animation-delay: 0.2s; }
.booking-card:nth-child(3) { animation-delay: 0.3s; }
.booking-card:nth-child(4) { animation-delay: 0.4s; }
.booking-card:nth-child(5) { animation-delay: 0.5s; }
.booking-card:nth-child(6) { animation-delay: 0.6s; }

.booking-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(30, 58, 138, 0.2);
    border-color: #3b82f6;
}

.booking-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(180deg, #1e3a8a, #3b82f6);
}

.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.booking-id {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 600;
}

.booking-status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
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

.status-cancelled {
    background: #fee2e2;
    color: #b91c1c;
}

.status-missed {
    background: #f3f4f6;
    color: #6b7280;
}

.booking-info {
    margin-bottom: 20px;
}

.booking-info p {
    margin: 10px 0;
    font-size: 0.95rem;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
}

.booking-info strong {
    color: #1a237e;
    min-width: 120px;
}

.child-info {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 15px;
}

.child-info p {
    margin: 5px 0;
    font-size: 0.9rem;
}

.booking-actions {
    display: flex;
    gap: 10px;
}

.btn-cancel {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    text-decoration: none;
    display: block;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-cancel:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    color: #fff;
}

.btn-view {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    text-decoration: none;
    display: block;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    color: #fff;
}

.btn-certificate {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    text-decoration: none;
    display: block;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-certificate:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    color: #fff;
}

.no-bookings {
    text-align: center;
    padding: 80px 20px;
    color: #64748b;
}

.no-bookings img {
    width: 150px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-bookings h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #334155;
}

.no-bookings p {
    font-size: 1rem;
    margin-bottom: 25px;
}

.btn-book-now {
    display: inline-block;
    padding: 14px 36px;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
    color: #fff;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-book-now:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    transform: translateY(-2px);
    color: #fff;
}

.stats-bar {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-bottom: 40px;
    padding: 25px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(30, 58, 138, 0.1);
    border: 1px solid #e2e8f0;
    animation: slideInDown 0.6s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-item {
    text-align: center;
    padding: 15px 25px;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.stat-item:hover {
    background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
    transform: translateY(-3px);
}

.stat-number {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-label {
    font-size: 0.95rem;
    color: #475569;
    margin-top: 5px;
    font-weight: 500;
}
</style>

<div class="page-title">My Bookings</div>

<div class="bookings-container">
    <?php if (!empty($bookings)) { 
        // Calculate stats
        $totalBookings = count($bookings);
        $upcomingBookings = 0;
        $today = date('Y-m-d');
        
        foreach ($bookings as $b) {
            if ($b['schedule_date'] >= $today) {
                $upcomingBookings++;
            }
        }
    ?>
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number"><?= $totalBookings ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $upcomingBookings ?></div>
                <div class="stat-label">Upcoming</div>
            </div>
        </div>
        
        <div class="bookings-grid">
            <?php foreach ($bookings as $b) { 
                $statusClass = 'status-' . strtolower($b['status']);
                $statusText = ucfirst($b['status']);
                $gender = ($b['gender'] == 'M' || $b['gender'] == 'm') ? 'Male' : 'Female';
            ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-id">Booking #<?= $b['bid'] ?></div>
                        <div class="booking-status <?= $statusClass ?>"><?= $statusText ?></div>
                    </div>
                    
                    <div class="child-info">
                        <p><strong>👶 Child:</strong> <?= htmlspecialchars($b['cname']) ?> (<?= $gender ?>)</p>
                        <p><strong>📅 DOB:</strong> <?= htmlspecialchars($b['dob']) ?></p>
                    </div>
                    
                    <div class="booking-info">
                        <p><strong>💉 Vaccine:</strong> <?= htmlspecialchars($b['vname']) ?></p>
                        <p><strong>🏥 Health Centre:</strong> <?= htmlspecialchars($b['hname']) ?></p>
                        <p><strong>📍 Location:</strong> <?= htmlspecialchars($b['loc']) ?></p>
                        <p><strong>📆 Appointment:</strong> <?= htmlspecialchars($b['schedule_date']) ?></p>
                        <p><strong>🕐 Booked On:</strong> <?= date('d M Y', strtotime($b['bookdate'])) ?></p>
                    </div>
                    
                    <div class="booking-actions">
                        <?php if ($b['status'] == 'pending') { ?>
                            <a href="javascript:void(0)" 
                               onclick="confirmCancel(<?= $b['bid'] ?>)" 
                               class="btn-cancel">Cancel Booking</a>
                        <?php } ?>
                        
                        <?php if ($b['status'] == 'completed' && $b['certificate']) { ?>
                            <a href="view_certificate.php?bid=<?= $b['bid'] ?>&cert=<?= urlencode($b['certificate']) ?>" 
                               class="btn-certificate">📄 View Certificate</a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="no-bookings">
            <img src="https://cdn-icons-png.flaticon.com/512/2706/2706950.png" alt="No bookings">
            <h3>No Bookings Yet</h3>
            <p>You haven't booked any vaccines yet. Start booking to protect your children!</p>
            <a href="dashboard.php" class="btn-book-now">Book Vaccine Now</a>
        </div>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmCancel(bid) {
    Swal.fire({
        title: 'Cancel Booking?',
        text: "Are you sure you want to cancel this booking?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'bookings.php?cancel=1&bid=' + bid;
        }
    });
}
</script>
