<?php 

 require('../config/autoload.php'); 
$dao=new DataAccess();
$info=$dao->getData('*','vaccine','vid='.$_GET['id']);
$vid = $_GET['id'];

$file=new FileUpload();
$elements=array(
        "vname"=>$info[0]['vname'],"vimage"=>"","period"=>$info[0]['period']);


$form=new FormAssist($elements,$_POST);


$dao=new DataAccess();

$msg = ""; // Initialize message variable

$labels=array('vname'=>"Vaccine Name","vimage"=>"Vaccine Image",'period'=>"Vaccine period" );

$rules=array(
    "vname"=>array("required"=>true,"minlength"=>3,"maxlength"=>70),
"vimage"=> array('filerequired'=>false),
"period"=>array("required"=>true,"minlength"=>3,"maxlength"=>70)
     
);
    
    
$validator = new FormValidator($rules,$labels);

if(isset($_POST["btn_insert"]))
{

if($validator->validate($_POST))
{
	
if($fileName=$file->doUploadRandom($_FILES['vimage'],array('.jpg','.png','.jpeg'),100000,5,'../uploads'))
		{

$data=array(

        'vname'=>$_POST['vname'],
          'vimage'=>$fileName,
          'period'=>$_POST['period'],
    );
  
    if($dao->update($data,"vaccine","vid=$vid"))
    {
        header('location:viewvaccine.php?success=1');
        exit();
    }
    else
        {$msg="Update failed";}
}
else
    echo $file->errors();
}
else
{
    // If no new image uploaded, update without image
    $data=array(
        'vname'=>$_POST['vname'],
        'period'=>$_POST['period'],
    );
    
    if($dao->update($data,"vaccine","vid=$vid"))
    {
        header('location:viewvaccine.php?success=1');
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
</head>
<body>

<?php if($msg): ?>
<div class="alert alert-danger"><?php echo $msg; ?></div>
<?php endif; ?>

 <form action="" method="POST" enctype="multipart/form-data">
 
<div class="row">
                    <div class="col-md-6">
Name:

<?= $form->textBox('vname',array('class'=>'form-control')); ?>
<?= $validator->error('vname'); ?>

</div>
</div>


<div class="row">
                    <div class="col-md-6">
IMAGE:

<?= $form->fileField('vimage',array('class'=>'form-control')); ?>
<span style="color:red;"><?= $validator->error('vimage'); ?></span>

</div>
</div>
<div class="row">
                    <div class="col-md-6">
Period:

<?= $form->textBox('period',array('class'=>'form-control')); ?>
<?= $validator->error('period'); ?>

</div>
</div>






<button type="submit" name="btn_insert"  >Submit</button>
</form>


</body>

</html>