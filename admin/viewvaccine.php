<?php require('../config/autoload.php'); ?>
<?php
$dao = new DataAccess();

// Handle success message
$successMsg = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMsg = "New record created successfully!";
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
</style>
<style>
    .btn-edit {
        background-color: #28a745;
        border: none;
        color: #fff;
        font-size: 14px;
        padding: 5px 15px;   /* smaller button */
    }
    .btn-edit:hover {
        background-color: #218838;
    }
    .btn-delete {
        background-color: #dc3545;
        border: none;
        color: #fff;
        font-size: 14px;
        padding: 5px 15px;   /* smaller button */
    }
    .btn-delete:hover {
        background-color: #c82333;
    }
    .card {
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 10px;
        overflow: hidden;
        height: 350px; /* reduced height */
    }
    .card-img-top {
        height: 150px; /* smaller image */
        object-fit: cover;
    }
    .card-body {
        padding: 12px;
    }
    .btn-group-custom {
        display: flex;
        justify-content: center;
        gap: 8px; /* clean gap between buttons */
    }
</style>

<div class="container" style="margin-top:50px;">
    <?php if ($successMsg): ?>
        <div class="alert-success">
            ✓ <?php echo $successMsg; ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <?php
        $sql = "SELECT vid, vname, vimage, period FROM vaccine";
        $result = $dao->query($sql);

        if ($result) {
            foreach ($result as $vaccine) {
                ?>
                <div class="col-md-3"> <!-- 4 per row -->
                    <div class="card text-center">
                        <img src="../uploads/<?php echo $vaccine['vimage']; ?>" class="card-img-top">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $vaccine['vname']; ?></h6>
                            <p class="card-text"><strong>Vaccine Period:</strong> <?php echo $vaccine['period']; ?></p>
                            <div class="btn-group-custom">
                                <a href="editvaccine.php?id=<?php echo $vaccine['vid']; ?>" class="btn btn-edit">Edit</a>
                                <a href="deletevaccine.php?id=<?php echo $vaccine['vid']; ?>" class="btn btn-delete">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>
