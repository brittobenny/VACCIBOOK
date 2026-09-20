<?php 

 require('../config/autoload.php'); 
$dao=new DataAccess();
$info=$dao->getData('*','district','did='.$_GET['id']);
$did = $_GET['id'];

$file=new FileUpload();
$elements=array(
        "dname"=>$info[0]['dname']);


$form=new FormAssist($elements,$_POST);


$dao=new DataAccess();

$msg = ""; // Initialize message variable

$labels=array('dname'=>"District Name" );

$rules=array(
    "dname"=>array("required"=>true,"minlength"=>2,"maxlength"=>25,"alphaonly"=>true)
);
    
    
$validator = new FormValidator($rules,$labels);

if(isset($_POST["btn_insert"]))
{

if($validator->validate($_POST))
{
    $data=array(
        'dname'=>$_POST['dname']
    );
  
    if($dao->update($data,"district","did=$did"))
    {
        header('location:viewdistrict.php?success=1');
        exit();
    }
    else
        {$msg="Update failed";}
}

}

// Include header AFTER form processing to prevent output before redirect
include("header.php");
?>
<html>
<head>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f5f7fb;
    }
    
    .form-container {
        max-width: 600px;
        margin: 50px auto;
        background: white;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    
    .form-container h2 {
        color: #1e3a8a;
        font-weight: 700;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .row {
        margin-bottom: 20px;
    }
    
    label {
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
        color: #1e3a8a;
    }
    
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 14px;
    }
    
    button {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(90deg, #1e3a8a, #3b82f6);
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }
    
    button:hover {
        background: linear-gradient(90deg, #0d153a, #1e3a8a);
        box-shadow: 0 0 10px rgba(30, 58, 138, 0.4);
    }
    
    .error {
        color: red;
        font-size: 13px;
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
        color: white;
    }
</style>
</head>
<body>

<div class="container" style="margin-top:50px;">
    <a href="viewdistrict.php" class="back-btn">
        <i class="mdi mdi-arrow-left"></i> Back to Districts
    </a>
    
    <div class="form-container">
        <h2><i class="mdi mdi-map-marker-edit"></i> Edit District</h2>

        <?php if($msg): ?>
        <div class="alert alert-danger"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
        
            <div class="row">
                <label>District Name:</label>
                <?= $form->textBox('dname',array('class'=>'form-control')); ?>
                <?= $validator->error('dname'); ?>
            </div>

            <button type="submit" name="btn_insert">Update District</button>
        </form>
    </div>
</div>

</body>

</html>
