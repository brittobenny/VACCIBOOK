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
    
    .btn-edit {
        background-color: #28a745;
        border: none;
        color: #fff;
        font-size: 14px;
        padding: 5px 15px;
    }
    .btn-edit:hover {
        background-color: #218838;
    }
    .btn-delete {
        background-color: #dc3545;
        border: none;
        color: #fff;
        font-size: 14px;
        padding: 5px 15px;
    }
    .btn-delete:hover {
        background-color: #c82333;
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
    
    .district-id {
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
</style>

<div class="container" style="margin-top:50px;">
    <?php if ($successMsg): ?>
        <div class="alert-success">
            ✓ <?php echo $successMsg; ?>
        </div>
    <?php endif; ?>
    
    <div class="page-header">
        <h3><i class="mdi mdi-map-marker"></i> All Districts</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>District ID</th>
                    <th>District Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT did, dname FROM district ORDER BY dname ASC";
                $result = $dao->query($sql);

                if ($result && count($result) > 0) {
                    foreach ($result as $district) {
                        ?>
                        <tr>
                            <td class="district-id"><?php echo $district['did']; ?></td>
                            <td><?php echo htmlspecialchars($district['dname']); ?></td>
                            <td>
                                <a href="editdistrict.php?id=<?php echo $district['did']; ?>" class="btn btn-edit">
                                    <i class="mdi mdi-pencil"></i> Edit
                                </a>
                                <a href="deletedistrict.php?id=<?php echo $district['did']; ?>" 
                                   class="btn btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this district?');">
                                    <i class="mdi mdi-delete"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="3" class="text-center">No districts found</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
