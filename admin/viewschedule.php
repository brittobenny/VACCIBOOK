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
        margin: 20px 0;
        border-radius: 5px;
        border: 1px solid;
    }
</style>

<div class="container_gray_bg" id="home_feat_1">
    <?php if ($successMsg): ?>
        <div class="container">
            <div class="alert-success">
                ✓ <?php echo $successMsg; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <table border="1" class="table" style="margin-top:100px;">
                    <tr>

                        <th>SID</th>
                        <th>Vaccine</th>
                        <th>Health Centre</th>
                        <th>date</th>
                        <th>units</th>
                        <th>EDIT/DELETE</th>


                    </tr>
                    <?php

                    $actions = array(
                        'edit' => array('label' => 'Edit', 'link' => 'editschedule.php', 'params' => array('id' => 'sid'), 'attributes' => array('class' => 'btn btn-success')),

                        'delete' => array('label' => 'Delete', 'link' => 'editschedule.php', 'params' => array('id' => 'sid'), 'attributes' => array('class' => 'btn btn-success'))

                    );

                    $config = array(
                        'srno' => true,
                        'hiddenfields' => array('sid'),


                    );


                    $join = array(

                    );
                    $fields = array('sid', 'vid', 'hid', 'date', 'units');

                    $users = $dao->selectAsTable($fields, 'schedule as s', 1, $join, $actions, $config);

                    echo $users;




                    ?>

                </table>
            </div>





        </div><!-- End row -->
    </div><!-- End container -->
</div><!-- End container_gray_bg -->