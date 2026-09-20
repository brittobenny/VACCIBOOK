
<?php require('../config/autoload.php'); ?>

<?php
$dao=new DataAccess();

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
                <table  border="1" class="table" style="margin-top:100px;">
                    <tr>
                        
                        <th>HId</th>
                        <th>Health Centre Name</th>
                        <th>Health Centre Image</th>
                        <th>Locatoion</th>
                        <th>E-mail</th>
                        <th>Password</th>
                      
                        <th>EDIT/DELETE</th>
                     
                      
                    </tr>
<?php
    
    $actions=array(
    'edit'=>array('label'=>'Edit','link'=>'edithealthcentre.php','params'=>array('id'=>'hid'),'attributes'=>array('class'=>'btn btn-success')),
    
    'delete'=>array('label'=>'Delete','link'=>'edithealthcentre.php','params'=>array('id'=>'hid'),'attributes'=>array('class'=>'btn btn-success'))
    
    );

    $config=array(
        'srno'=>true,
        'hiddenfields'=>array('hid'),
        'images' => array(
        array(
            'field' => 'himage',
            'path' => '../uploads/', 
            'attributes' => array('style'=>'width:100px;')
        )
    )
        
        
    );

   
   $join=array(
     
    );  $fields=array('hid','hname','himage','loc','email','password');

    $users=$dao->selectAsTable($fields,'healthcentre as s',1,$join,$actions,$config);
    
    echo $users;
                    
                    
                   
    
?>
             
                </table>
            </div>    

            
            
            
            
        </div><!-- End row -->
    </div><!-- End container -->
    </div><!-- End container_gray_bg -->
    
    
