<?php 

 require('../config/autoload.php'); 
$dao=new DataAccess();
$info=$dao->getData('*','schedule','sid='.$_GET['id']);
$sid = $_GET['id'];

$file=new FileUpload();
$elements=array(
        "vid"=>$info[0]['vid'],"hid"=>$info[0]['hid'],"date"=>$info[0]['date'],"units"=>$info[0]['units']);


$form=new FormAssist($elements,$_POST);


$dao=new DataAccess();

$msg = ""; // Initialize message variable

$labels=array('vid'=>"Vaccine","hid"=>"Health Centre","date"=>"DATE","units"=>"Units" );

$rules=array(
    "vid"=>array("required"=>true),
    "hid"=>array("required"=>true),
    "date"=>array("required"=>true),
    "units"=>array("required"=>true)
     
);
    
    
$validator = new FormValidator($rules,$labels);

if(isset($_POST["btn_insert"]))
{

if($validator->validate($_POST))
{
		

$data=array(

        'vid'=>$_POST['vid'],
	'hid'=>$_POST['hid'],
        'date'=>$_POST['date'],
        'units'=>$_POST['units'],
    );
  
    if($dao->update($data,"schedule","sid=$sid"))
    {
        header('location:viewschedule.php?success=1');
        exit();
    }
    else
        {$msg="Update failed";}
}
else
    echo $file->errors();
}

// Include header AFTER form processing to prevent output before redirect
include("header.php");
?>
<html>
<head>
</head>
<body>

<?php if($msg): ?>
<div class="alert alert-danger"><?php echo $msg; ?></div>
<?php endif; ?>

 <form action="" method="POST" enctype="multipart/form-data">
 <div class="row">
                    <div class="col-md-6">
 
Vaccine:

<?php
                    $options = $dao->createOptions('vname','vid',"vaccine");
                    echo $form->dropDownList('vid',array('class'=>'form-control'),$options); ?>
<?= $validator->error('vid'); ?>

</div>
</div>
<div class="row">
                    <div class="col-md-6">

Health Centre:

<?php
                    $options = $dao->createOptions('hname','hid',"healthcentre");
                    echo $form->dropDownList('hid',array('class'=>'form-control'),$options); ?>
<?= $validator->error('hid'); ?>

</div>
</div>
<div class="row">
                    <div class="col-md-6">

Date:

<?= $form->inputBox('date',array('class'=>'form-control'),'date'); ?>
<?= $validator->error('date'); ?>

</div>
</div>
<div class="row">
                    <div class="col-md-6">

Units:

<?= $form->textBox('units',array('class'=>'form-control')); ?>
<?= $validator->error('units'); ?>

</div>
</div>






<button type="submit" name="btn_insert"  >Submit</button>
</form>


</body>

</html>