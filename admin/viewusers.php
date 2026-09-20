<?php require('../config/autoload.php'); ?>
<?php
$dao = new DataAccess();

// Handle success message
$successMsg = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMsg = "Operation completed successfully!";
}
?>
<?php include('header.php'); ?>

<style>
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
        padding: 12px 20px;
        margin-bottom: 20px;
        border-radius: 5px;
        border: 1px solid;
    }
    
    .table-responsive {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead {
        background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        color: white;
    }
    
    .table thead th {
        border: none;
        padding: 15px;
        font-weight: 600;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .table tbody td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    
    .user-id {
        font-weight: 600;
        color: #1e3a8a;
    }
    
    .page-header {
        margin-bottom: 30px;
    }
    
    .page-header h3 {
        color: #1e3a8a;
        font-weight: 700;
    }
    
    .badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-success {
        background: #dcfce7;
        color: #15803d;
    }
    
    .stats-card {
        background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        color: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2);
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stats-label {
        font-size: 1rem;
        opacity: 0.9;
    }
</style>

<div class="container" style="margin-top:50px;">
    <?php if ($successMsg): ?>
        <div class="alert-success">
            ✓ <?php echo $successMsg; ?>
        </div>
    <?php endif; ?>
    
    <div class="page-header">
        <h3><i class="mdi mdi-account-multiple"></i> Registered Users (Parents)</h3>
    </div>
    
    <?php
    // Get total user count
    $sql = "SELECT COUNT(*) as total FROM parent";
    $totalResult = $dao->query($sql);
    $totalUsers = $totalResult[0]['total'] ?? 0;
    ?>
    
    <div class="stats-card">
        <div class="stats-number"><?php echo $totalUsers; ?></div>
        <div class="stats-label">Total Registered Users</div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Children</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.pid, p.pname, p.mob, p.pemail, 
                        (SELECT COUNT(*) FROM child c WHERE c.pid = p.pid) as child_count
                        FROM parent p 
                        ORDER BY p.pid DESC";
                $result = $dao->query($sql);

                if ($result && count($result) > 0) {
                    foreach ($result as $user) {
                        ?>
                        <tr>
                            <td class="user-id">#<?php echo $user['pid']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['pname']); ?></strong>
                            </td>
                            <td>
                                <i class="mdi mdi-phone"></i> <?php echo htmlspecialchars($user['mob']); ?>
                            </td>
                            <td>
                                <i class="mdi mdi-email"></i> <?php echo htmlspecialchars($user['pemail']); ?>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    <i class="mdi mdi-check-circle"></i> Active
                                </span>
                            </td>
                            <td>
                                <i class="mdi mdi-human-child"></i> <?php echo $user['child_count']; ?> 
                                <?php echo $user['child_count'] == 1 ? 'Child' : 'Children'; ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6" class="text-center">No users found</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
