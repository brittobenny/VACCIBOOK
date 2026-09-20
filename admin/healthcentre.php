<?php 

 require('../config/autoload.php'); 

$file=new FileUpload();
$elements=array(
        "hname"=>"","did"=>"","himage"=>"","loc"=>"","email"=>"","password"=>"");


$form=new FormAssist($elements,$_POST);


$dao=new DataAccess();

$msg = ""; // Initialize message variable

$labels=array('hname'=>"Health Centre Name","did"=>"District name","himage"=>"Health Centre Image","loc"=>"Location","email"=>"E-mail","password"=>"Password" );

$rules=array(
    "hname"=>array("required"=>true,"minlength"=>3,"maxlength"=>80,"alphaspaceonly"=>true),
    "did"=>array("required"=>true),
    "himage"=> array('filerequired'=>true),
    "loc"=>array("required"=>true,"minlength"=>3,"maxlength"=>80,"alphaspaceonly"=>true),
    "email"=>array("required"=>true,"minlength"=>3,"maxlength"=>80),
    "password"=>array("required"=>true,"minlength"=>6,"maxlength"=>15),


     
);
    
    
$validator = new FormValidator($rules,$labels);

if(isset($_POST["btn_insert"]))
{

if($validator->validate($_POST))
{
	
if($fileName=$file->doUploadRandom($_FILES['himage'],array('.jpg','.png','.jpeg'),100000,5,'../uploads'))
		{

$data=array(

        'hname'=>$_POST['hname'],
	'did'=>$_POST['did'],
        'himage'=>$fileName,
        'loc'=>$_POST['loc'],
        'email'=>$_POST['email'],
        'password'=>$_POST['password'],
    );
  
    if($dao->insert($data,"healthcentre"))
    {
        header('location:viewhealthcentre.php?success=1');
        exit();
    }
    else
        {$msg="Registration failed";} 
    
}
else
echo $file->errors();
}

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
Name:

<?= $form->textBox('hname',array('class'=>'form-control')); ?>
<?= $validator->error('hname'); ?>

</div>
</div>


<div class="row">
                    <div class="col-md-6">
District:

<?php
                    $options = $dao->createOptions('dname','did',"district");
                    echo $form->dropDownList('did',array('class'=>'form-control'),$options); ?>
<?= $validator->error('did'); ?>

</div>
</div>

<div class="row">
                    <div class="col-md-6">
IMAGE:

<?= $form->fileField('himage',array('class'=>'form-control')); ?>
<span style="color:red;"><?= $validator->error('himage'); ?></span>

</div>
</div>
<div class="row">
                    <div class="col-md-6">
Location:

<?= $form->textBox('loc',array('class'=>'form-control')); ?>
<?= $validator->error('loc'); ?>

</div>
</div>
<div class="row">
                    <div class="col-md-6">
E-mail:

<?= $form->textBox('email',array('class'=>'form-control')); ?>
<?= $validator->error('email'); ?>

</div>
</div>
<div class="row">
                    <div class="col-md-6">
password:

<?= $form->textBox('password',array('class'=>'form-control')); ?>
<?= $validator->error('password'); ?>

</div>
</div>






<button type="submit" name="btn_insert"  >Submit</button>
</form>


</body>

</html>