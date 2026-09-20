<?php
require('../config/autoload.php');
require('CertificateGenerator.php');
$dao = new DataAccess();
include('header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$healthCentre = $_SESSION['healthcentre'] ?? null;
$hid = $healthCentre['hid'] ?? 0;

// Handle status update
if (isset($_POST['update_status'])) {
    $bid = intval($_POST['bid']);
    $status = $_POST['status'];
    
    $updateData = array(
        'status' => $status
    );
    
    // If marking as completed, generate certificate
    if ($status == 'completed') {
        // Get booking details
        $bookingQuery = "SELECT b.*, c.cname, c.gender, c.dob, 
                               v.vname, h.hname, h.loc, s.date as schedule_date
                        FROM book b
                        JOIN child c ON b.cid = c.cid
                        JOIN schedule s ON b.sid = s.sid
                        JOIN vaccine v ON s.vid = v.vid
                        JOIN healthcentre h ON s.hid = h.hid
                        WHERE b.bid = $bid";
        
        $bookingData = $dao->query($bookingQuery);
        
        if ($bookingData) {
            $booking = $bookingData[0];
            
            // Generate certificate
            $certGen = new CertificateGenerator();
            $childData = array('cname' => $booking['cname']);
            $vaccineData = array('vname' => $booking['vname']);
            $healthCentreData = array('hname' => $booking['hname'], 'loc' => $booking['loc']);
            
            $certificateFilename = $certGen->generateCertificate($booking, $childData, $vaccineData, $healthCentreData);
            $updateData['certificate'] = $certificateFilename;
        }
    }
    
    if ($dao->update($updateData, "book", "bid=$bid")) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          icon: 'success',
          title: 'Status Updated!',
          text: 'Booking status has been updated successfully.',
          showConfirmButton: false,
          timer: 2000
        }).then(() => {
          window.location.href = 'bookings.php';
        });
        </script>
        ";
    }
}

// Fetch all bookings for this health centre
$sql = "SELECT b.bid, b.bookdate, b.status, b.certificate,
               c.cname, c.gender, c.dob, c.id_proof, c.cid,
               v.vname, s.date as schedule_date, s.sid,
               p.pname as parent_name, p.mob as parent_mob
        FROM book b
        JOIN child c ON b.cid = c.cid
        JOIN parent p ON b.pid = p.pid
        JOIN schedule s ON b.sid = s.sid
        JOIN vaccine v ON s.vid = v.vid
        WHERE s.hid = $hid
        ORDER BY s.date DESC, b.bookdate DESC";

$bookings = $dao->query($sql);
?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f9f9f9;
}

.page-title {
    text-align: center;
    font-size: 2.2rem;
    font-weight: 700;
    margin: 40px 0 30px;
    color: #1a237e;
}

.bookings-container {
    max-width: 1400px;
    margin: 0 auto 60px;
    padding: 0 20px;
}

.stats-bar {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.stat-card {
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    text-align: center;
    min-width: 150px;
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

.bookings-table {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: linear-gradient(90deg, #1a237e, #3f51b5);
    color: white;
}

thead th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
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

.btn-action {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    background: #1a237e;
    color: white;
    cursor: pointer;
    font-size: 0.85rem;
    transition: 0.3s;
}

.btn-action:hover {
    background: #0d153a;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 30px;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h3 {
    color: #1a237e;
}

.close {
    font-size: 28px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #1a237e;
}

.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
}

.btn-submit {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(90deg, #1a237e, #3f51b5);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.btn-submit:hover {
    background: linear-gradient(90deg, #0d153a, #1a237e);
}

.no-bookings {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
}
</style>

<div class="page-title">Vaccination Bookings</div>

<div class="bookings-container">
    <?php if (!empty($bookings)) { 
        // Calculate stats
        $total = count($bookings);
        $pending = 0;
        $completed = 0;
        $cancelled = 0;
        $missed = 0;
        
        foreach ($bookings as $b) {
            switch($b['status']) {
                case 'pending': $pending++; break;
                case 'completed': $completed++; break;
                case 'cancelled': $cancelled++; break;
                case 'missed': $missed++; break;
            }
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
            <div class="stat-card">
                <div class="stat-number"><?= $missed ?></div>
                <div class="stat-label">Missed</div>
            </div>
        </div>
        
        <div class="bookings-table">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Child Name</th>
                        <th>Parent Contact</th>
                        <th>Vaccine</th>
                        <th>Schedule Date</th>
                        <th>Status</th>
                        <th>ID Proof</th>
                        <th>Action</th>
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
                            <td>
                                <?= htmlspecialchars($b['parent_name']) ?><br>
                                <small style="color:#64748b;"><?= $b['parent_mob'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($b['vname']) ?></td>
                            <td><?= date('d M Y', strtotime($b['schedule_date'])) ?></td>
                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($b['id_proof'])): ?>
                                    <button class="btn-action" onclick="window.open('view_id_proof.php?cid=<?= $b['cid'] ?>', '_blank')" 
                                            style="background: #059669; font-size: 0.8rem;">
                                        👁️ View ID
                                    </button>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.8rem;">Not provided</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-action" onclick="openModal(<?= htmlspecialchars(json_encode($b)) ?>)">
                                    Update
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <div class="no-bookings">
            <h3>No Bookings Yet</h3>
            <p>No vaccination bookings have been made for your health centre.</p>
        </div>
    <?php } ?>
</div>

<!-- Update Status Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Booking Status</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="bid" id="modal_bid">
            
            <div class="form-group">
                <label>Child Name:</label>
                <input type="text" id="modal_child" readonly style="background:#f8f9fa; padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
            </div>
            
            <div class="form-group">
                <label>Vaccine:</label>
                <input type="text" id="modal_vaccine" readonly style="background:#f8f9fa; padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
            </div>
            
            <div class="form-group">
                <label>Status:</label>
                <select name="status" id="modal_status" required>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="missed">Missed</option>
                </select>
            </div>
            
            <button type="submit" name="update_status" class="btn-submit">Update Status</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openModal(booking) {
    document.getElementById('modal_bid').value = booking.bid;
    document.getElementById('modal_child').value = booking.cname;
    document.getElementById('modal_vaccine').value = booking.vname;
    document.getElementById('modal_status').value = booking.status;
    document.getElementById('updateModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('updateModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('updateModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>
