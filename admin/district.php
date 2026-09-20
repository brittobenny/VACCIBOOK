<?php 
require('../config/autoload.php'); 

$file = new FileUpload();
$elements = array(
    "dname" => ""
);

$form = new FormAssist($elements, $_POST);

$dao = new DataAccess();

$labels = array('dname' => "District name");

$rules = array(
    "dname" => array("required" => true, "minlength" => 2, "maxlength" => 25, "alphaonly" => true),
);

$validator = new FormValidator($rules, $labels);

$successMsg = "";
$loginLink = false;

if (isset($_POST["btn_insert"])) {
    if ($validator->validate($_POST)) {
        $data = array(
            'dname' => $_POST['dname'],
        );

        if ($dao->insert($data, "district")) {
            $successMsg = "New record created successfully!";
            $loginLink = true;
            // Clear form values by redirecting with success message in URL
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();
        } else {
            $msg = "Registration failed";
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMsg = "New record created successfully!";
    $loginLink = true;
}

// Include header AFTER form processing to prevent output before redirect
include("header.php");
?>
<html>
<head>
</head>
<body>

<?php if (!empty($successMsg)): ?>
    <p style="color:green;"><?= $successMsg ?></p>
    <?php if ($loginLink): ?>
        <a href="viewdistrict.php" style="color:blue; text-decoration:underline;">View Districts</a>
    <?php endif; ?>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            District Name:
            <?= $form->textBox('dname', array('class' => 'form-control')); ?>
            <?= $validator->error('dname'); ?>
        </div>
    </div>
    <button type="submit" name="btn_insert">Submit</button>
</form>

</body>
</html>
