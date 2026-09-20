<?php 

 require('../config/autoload.php'); 

$file=new FileUpload();
$elements=array(
        "vname"=>"","vimage"=>"","period"=>"");


$form=new FormAssist($elements,$_POST);


$dao=new DataAccess();

$msg = ""; // Initialize message variable

$labels=array('vname'=>"Vaccine Name","vimage"=>"Vaccine Image",'period'=>"Vaccine period" );

$rules=array(
    "vname"=>array("required"=>true,"minlength"=>3,"maxlength"=>70),
"vimage"=> array('filerequired'=>true),
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
  
    if($dao->insert($data,"vaccine"))
    {
        header('location:viewvaccine.php?success=1');
        exit();
    }
    else
        {$msg="Registration failed";} ?>

<span style="color:red;"><?php echo $msg; ?></span>

<?php
    
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