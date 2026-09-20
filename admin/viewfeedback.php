<?php
require('../config/autoload.php');
$dao = new DataAccess();

// Handle status update
if (isset($_POST['update_status'])) {
    $fid = intval($_POST['fid']);
    $status = $_POST['status'];
    
    $updateData = array('status' => $status);
    
    if ($dao->update($updateData, "feedback", "fid=$fid")) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
          icon: 'success',
          title: 'Updated!',
          text: 'Feedback status has been updated.',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          window.location.href = 'viewfeedback.php';
        });
        </script>
        ";
    }
}

// Get all feedbacks with related information
$feedbacks = $dao->query("SELECT f.*, 
                          p.pname, p.pemail as email, p.mob as phone,
                          h.hname, h.loc,
                          DATE_FORMAT(f.feedback_date, '%d %b %Y at %h:%i %p') as formatted_date
                          FROM feedback f
                          JOIN parent p ON f.pid = p.pid
                          LEFT JOIN healthcentre h ON f.hid = h.hid
                          ORDER BY f.feedback_date DESC");

// Ensure feedbacks is an array
if (!is_array($feedbacks)) {
    $feedbacks = [];
}

// Calculate statistics
$totalFeedbacks = count($feedbacks);
$statusCount = ['pending' => 0, 'reviewed' => 0, 'resolved' => 0];
$ratingDistribution = ['5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];
$averageRating = 0;

if ($feedbacks) {
    $totalRating = 0;
    foreach ($feedbacks as $feedback) {
        $statusCount[$feedback['status']]++;
        $totalRating += $feedback['rating'];
        $ratingDistribution[$feedback['rating']]++;
    }
    $averageRating = $totalFeedbacks > 0 ? round($totalRating / $totalFeedbacks, 1) : 0;
}
?>

<?php include('header.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .feedbacks-container {
        padding: 20px;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #1a237e;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #6c757d;
        font-size: 14px;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-icon.yellow {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1a237e;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #6c757d;
        font-size: 13px;
    }

    /* Filter & Search Section */
    .controls-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .controls-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 15px;
        align-items: center;
    }

    .search-box {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-select:focus {
        outline: none;
        border-color: #667eea;
    }

    /* Feedbacks Table */
    .feedbacks-table-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a237e;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .feedbacks-table {
        width: 100%;
        border-collapse: collapse;
    }

    .feedbacks-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .feedbacks-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .feedbacks-table tbody tr {
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .feedbacks-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
    }

    .feedbacks-table td {
        padding: 15px;
        font-size: 14px;
        color: #475569;
    }

    .parent-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .parent-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 16px;
    }

    .parent-details h5 {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 3px;
    }

    .parent-contact {
        font-size: 12px;
        color: #94a3b8;
    }

    .rating-display {
        display: flex;
        gap: 3px;
    }

    .rating-display i {
        color: #fbbf24;
        font-size: 14px;
    }

    .feedback-content {
        max-width: 300px;
    }

    .feedback-subject {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 5px;
    }

    .feedback-message {
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-reviewed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-resolved {
        background: #dcfce7;
        color: #15803d;
    }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .no-feedbacks {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }

    .no-feedbacks i {
        font-size: 64px;
        margin-bottom: 20px;
        color: #cbd5e1;
    }

    .no-feedbacks h3 {
        font-size: 20px;
        margin-bottom: 10px;
        color: #64748b;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 30px;
        border-radius: 16px;
        max-width: 600px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a237e;
    }

    .close-modal {
        font-size: 28px;
        font-weight: bold;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close-modal:hover {
        color: #1a237e;
        transform: rotate(90deg);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #475569;
        font-size: 14px;
    }

    .form-select {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .modal-btn {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .modal-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
</style>

<div class="feedbacks-container">
    <div class="page-header">
        <h1 class="page-title">Feedback Management</h1>
        <p class="page-subtitle">View and manage all parent feedbacks across the system</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?= $totalFeedbacks ?></div>
                    <div class="stat-label">Total Feedbacks</div>
                </div>
                <div class="stat-icon blue">
                    <i class="mdi mdi-comment-multiple"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?= $statusCount['pending'] ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-icon yellow">
                    <i class="mdi mdi-clock-outline"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?= $statusCount['reviewed'] ?></div>
                    <div class="stat-label">Reviewed</div>
                </div>
                <div class="stat-icon green">
                    <i class="mdi mdi-eye-check"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?= $averageRating ?> ★</div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-icon orange">
                    <i class="mdi mdi-star"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls Section -->
    <div class="controls-section">
        <div class="controls-grid">
            <div class="search-box">
                <i class="mdi mdi-magnify search-icon"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Search by parent name, subject, or message...">
            </div>
            <select class="filter-select" id="statusFilter" onchange="filterByStatus()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="reviewed">Reviewed</option>
                <option value="resolved">Resolved</option>
            </select>
            <select class="filter-select" id="ratingFilter" onchange="filterByRating()">
                <option value="all">All Ratings</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
            </select>
        </div>
    </div>

    <!-- Feedbacks Table -->
    <div class="feedbacks-table-section">
        <div class="table-header">
            <h2 class="table-title">
                <i class="mdi mdi-format-list-bulleted"></i>
                All Feedbacks
            </h2>
        </div>

        <?php if ($feedbacks && count($feedbacks) > 0): ?>
            <table class="feedbacks-table">
                <thead>
                    <tr>
                        <th>Parent</th>
                        <th>Rating</th>
                        <th>Feedback</th>
                        <th>Health Centre</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="feedbacksTableBody">
                    <?php foreach ($feedbacks as $feedback): ?>
                        <tr data-status="<?= $feedback['status'] ?>" data-rating="<?= $feedback['rating'] ?>">
                            <td>
                                <div class="parent-info">
                                    <div class="parent-avatar">
                                        <?= strtoupper(substr($feedback['pname'], 0, 1)) ?>
                                    </div>
                                    <div class="parent-details">
                                        <h5><?= htmlspecialchars($feedback['pname']) ?></h5>
                                        <div class="parent-contact">
                                            <?= htmlspecialchars($feedback['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="rating-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="mdi mdi-star" style="color: <?= $i <= $feedback['rating'] ? '#fbbf24' : '#e2e8f0' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <div class="feedback-content">
                                    <div class="feedback-subject"><?= htmlspecialchars($feedback['subject']) ?></div>
                                    <div class="feedback-message"><?= htmlspecialchars($feedback['message']) ?></div>
                                </div>
                            </td>
                            <td>
                                <?php if ($feedback['hname']): ?>
                                    <strong><?= htmlspecialchars($feedback['hname']) ?></strong><br>
                                    <small><?= htmlspecialchars($feedback['loc']) ?></small>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">General Feedback</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $feedback['formatted_date'] ?></td>
                            <td>
                                <span class="status-badge status-<?= $feedback['status'] ?>">
                                    <?= ucfirst($feedback['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn" onclick="openUpdateModal(<?= $feedback['fid'] ?>, '<?= $feedback['status'] ?>')">
                                    <i class="mdi mdi-pencil"></i> Update
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-feedbacks">
                <i class="mdi mdi-comment-off-outline"></i>
                <h3>No Feedbacks Yet</h3>
                <p>No feedbacks have been submitted yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Feedback Status</h3>
            <span class="close-modal" onclick="closeUpdateModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="fid" id="modal_fid">
            <div class="form-group">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" id="modal_status">
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="modal-btn">
                <i class="mdi mdi-check"></i> Update Status
            </button>
        </form>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#feedbacksTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filter by status
    function filterByStatus() {
        const status = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('#feedbacksTableBody tr');
        
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Filter by rating
    function filterByRating() {
        const rating = document.getElementById('ratingFilter').value;
        const rows = document.querySelectorAll('#feedbacksTableBody tr');
        
        rows.forEach(row => {
            if (rating === 'all' || row.dataset.rating === rating) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Open update modal
    function openUpdateModal(fid, currentStatus) {
        document.getElementById('modal_fid').value = fid;
        document.getElementById('modal_status').value = currentStatus;
        document.getElementById('updateModal').style.display = 'block';
    }

    // Close update modal
    function closeUpdateModal() {
        document.getElementById('updateModal').style.display = 'none';
    }

    // Close modal on outside click
    window.onclick = function(event) {
        const modal = document.getElementById('updateModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

</div>
</div>
</div>
</div>
