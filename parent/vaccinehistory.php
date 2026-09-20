<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$parent = $_SESSION['parent'] ?? null;
$parentId = $parent['pid'] ?? 0;

// Get child ID from URL
$cid = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($cid == 0) {
    echo "<p style='text-align:center; margin-top:50px;'>Invalid child ID.</p>";
    exit;
}

// Verify child belongs to this parent
$child = $dao->getData("*", "child", "cid=$cid AND pid=$parentId");

if (empty($child)) {
    echo "<p style='text-align:center; margin-top:50px;'>Child not found or access denied.</p>";
    exit;
}

$childData = $child[0];
$gender = ($childData['gender'] == 'M' || $childData['gender'] == 'm') ? 'Male' : 'Female';

// Calculate age
function calculateAge($dob) {
    if (!$dob) return "Not set";
    $dobDate = new DateTime($dob);
    $today   = new DateTime();
    $age     = $today->diff($dobDate);
    return $age->y . " years, " . $age->m . " months";
}

// Fetch vaccination history for this child
$sql = "SELECT b.bid, b.bookdate, b.status, b.certificate,
               v.vname, h.hname, h.loc, s.date as schedule_date
        FROM book b
        JOIN schedule s ON b.sid = s.sid
        JOIN vaccine v ON s.vid = v.vid
        JOIN healthcentre h ON s.hid = h.hid
        WHERE b.cid = $cid
        ORDER BY s.date DESC";

$vaccinations = $dao->query($sql);
?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f9f9f9;
}

.page-header {
    max-width: 1200px;
    margin: 40px auto 30px;
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

.child-header {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 30px;
}

.child-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: white;
}

.child-details h2 {
    color: #1a237e;
    margin-bottom: 10px;
}

.child-details p {
    margin: 5px 0;
    color: #64748b;
}

.history-container {
    max-width: 1200px;
    margin: 30px auto 60px;
    padding: 0 20px;
}

.section-title {
    font-size: 1.8rem;
    color: #1a237e;
    margin-bottom: 20px;
}

.stats-bar {
    display: flex;
    gap: 20px;
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

.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #e0e0e0;
}

.timeline-item {
    position: relative;
    background: white;
    padding: 25px;
    border-radius: 16px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.timeline-item:hover {
    transform: translateX(5px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -30px;
    top: 30px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: white;
    border: 3px solid #1a237e;
}

.timeline-item.completed::before {
    background: #10b981;
    border-color: #10b981;
}

.timeline-item.pending::before {
    background: #f59e0b;
    border-color: #f59e0b;
}

.timeline-item.cancelled::before {
    background: #ef4444;
    border-color: #ef4444;
}

.timeline-item.missed::before {
    background: #9ca3af;
    border-color: #9ca3af;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.vaccine-name {
    font-size: 1.3rem;
    color: #1a237e;
    font-weight: 600;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-completed {
    background: #dcfce7;
    color: #15803d;
}

.status-pending {
    background: #fef9c3;
    color: #b45309;
}

.status-cancelled {
    background: #fee2e2;
    color: #b91c1c;
}

.status-missed {
    background: #f3f4f6;
    color: #6b7280;
}

.timeline-content p {
    margin: 8px 0;
    color: #64748b;
    font-size: 0.95rem;
}

.timeline-content strong {
    color: #334155;
}

.btn-certificate {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 20px;
    background: linear-gradient(90deg, #1a237e, #3f51b5);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: 0.3s;
}

.btn-certificate:hover {
    background: linear-gradient(90deg, #0d153a, #1a237e);
    box-shadow: 0 4px 12px rgba(26,35,126,0.3);
}

.no-history {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    color: #64748b;
}

.no-history img {
    width: 120px;
    margin-bottom: 20px;
    opacity: 0.5;
}
</style>

<div class="page-header">
    <a href="profile.php" class="back-btn">← Back to Profile</a>
    
    <div class="child-header">
        <div class="child-avatar">
            <?= $gender == 'Male' ? '👦' : '👧' ?>
        </div>
        <div class="child-details">
            <h2><?= htmlspecialchars($childData['cname']) ?></h2>
            <p><strong>Gender:</strong> <?= $gender ?></p>
            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($childData['dob']) ?></p>
            <p><strong>Age:</strong> <?= calculateAge($childData['dob']) ?></p>
        </div>
    </div>
</div>

<div class="history-container">
    <h3 class="section-title">Vaccination History</h3>
    
    <?php if (!empty($vaccinations)) { 
        // Calculate stats
        $total = count($vaccinations);
        $completed = 0;
        $pending = 0;
        
        foreach ($vaccinations as $v) {
            if ($v['status'] == 'completed') $completed++;
            if ($v['status'] == 'pending') $pending++;
        }
    ?>
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">Total Vaccinations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $completed ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $pending ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        
        <div class="timeline">
            <?php foreach ($vaccinations as $v) { 
                $statusClass = strtolower($v['status']);
            ?>
                <div class="timeline-item <?= $statusClass ?>">
                    <div class="timeline-header">
                        <div class="vaccine-name">💉 <?= htmlspecialchars($v['vname']) ?></div>
                        <span class="status-badge status-<?= $statusClass ?>">
                            <?= ucfirst($v['status']) ?>
                        </span>
                    </div>
                    
                    <div class="timeline-content">
                        <p><strong>Health Centre:</strong> <?= htmlspecialchars($v['hname']) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($v['loc']) ?></p>
                        <p><strong>Appointment Date:</strong> <?= date('d F Y', strtotime($v['schedule_date'])) ?></p>
                        <p><strong>Booked On:</strong> <?= date('d F Y', strtotime($v['bookdate'])) ?></p>
                        
                        <?php if ($v['status'] == 'completed' && $v['certificate']) { ?>
                            <a href="view_certificate.php?bid=<?= $v['bid'] ?>&cert=<?= urlencode($v['certificate']) ?>" 
                               class="btn-certificate">
                                📄 View Certificate
                            </a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="no-history">
            <img src="https://cdn-icons-png.flaticon.com/512/3209/3209076.png" alt="No history">
            <h3>No Vaccination History</h3>
            <p>No vaccinations have been booked for this child yet.</p>
            <a href="dashboard.php" class="btn-certificate" style="margin-top:20px;">Book Vaccine Now</a>
        </div>
    <?php } ?>
</div>
