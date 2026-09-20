<?php 
require('../config/autoload.php'); 

$file = new FileUpload();
$elements = array(
    "vid"   => "",
    "hid"   => "",
    "date"  => "",
    "units" => ""
);

$form = new FormAssist($elements, $_POST);

$dao = new DataAccess();

$labels = array(
    'vid'   => "Vaccine",
    'hid'   => "Health Centre",
    'date'  => "DATE",
    'units' => "Units"
);

$rules = array(
    "vid"   => array("required" => true),
    "hid"   => array("required" => true),
    "date"  => array("required" => true,'date'=>array('from'=>'today','to'=>'+30 days 12 am')),
    "units" => array("required" => true)
);

$validator = new FormValidator($rules, $labels);

$msg = ""; // always initialize

// ✅ Handle insert BEFORE including header.php
if (isset($_POST["btn_insert"])) {
    if ($validator->validate($_POST)) {
        $data = array(
            'vid'   => $_POST['vid'],
            'hid'   => $_POST['hid'],
            'date'  => $_POST['date'],
            'units' => $_POST['units'],
        );
  
        if ($dao->insert($data, "schedule")) {
            // redirect safely before HTML output
            header('Location: viewschedule.php');
            exit;
        } else {
            $msg = "Registration failed";
        }
    } else {
        echo $file->errors();
    }
}

// ✅ include header only AFTER redirect/insert logic
include("header.php");
?>
<html>
<head>
</head>
<body>

<form action="" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            Vaccine:
            <?php
            $options = $dao->createOptions('vname','vid',"vaccine");
            echo $form->dropDownList('vid', array('class'=>'form-control'), $options);
            ?>
            <?= $validator->error('vid'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            Health Centre:
            <?php
            $options = $dao->createOptions('hname','hid',"healthcentre");
            echo $form->dropDownList('hid', array('class'=>'form-control'), $options);
            ?>
            <?= $validator->error('hid'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            Date:
            <?= $form->inputBox('date', array('class'=>'form-control'), "date") ?>
            <span style="color:red;"><?= $validator->error('date'); ?></span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            Units:
            <?= $form->textBox('units', array('class'=>'form-control')); ?>
            <?= $validator->error('units'); ?>
        </div>
    </div>

    <button type="submit" name="btn_insert">Submit</button>
</form>

<?php if (!empty($msg)): ?>
    <span style="color:red;"><?= $msg; ?></span>
<?php endif; ?>

</body>
</html>
