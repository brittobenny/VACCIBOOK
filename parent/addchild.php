<?php 
require('../config/autoload.php'); 

// Start session FIRST before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$parent = $_SESSION['parent'] ?? null;
$parentId = $parent['pid'] ?? 0;

$elements = array(
    "cname" => "",
    "gender" => "",
    "dob" => "",
    "id_proof" => ""
);

$form = new FormAssist($elements, $_POST);

$dao = new DataAccess();

$labels = array(
    'cname'  => "Child Name",
    'gender' => "Gender",
    'dob'    => "Date of Birth",
    'id_proof' => "ID Proof"
);

$rules = array(
    "cname"  => array("required" => true, "minlength" => 2, "maxlength" => 80, "alphaspaceonly" => true),
    "gender" => array("required" => true),
    "dob"    => array("required" => true)
);

$validator = new FormValidator($rules, $labels);

$msg = "";
$uploadError = "";

if (isset($_POST["btn_insert"])) {
    // Validate file upload first
    if (!isset($_FILES['id_proof']) || $_FILES['id_proof']['error'] == 4) {
        $uploadError = "Please upload a child ID proof document.";
    } elseif ($_FILES['id_proof']['error'] != 0) {
        $uploadError = "Error uploading file. Please try again.";
    } elseif ($_FILES['id_proof']['size'] > 5242880) { // 5MB limit
        $uploadError = "File size must be less than 5MB.";
    } else {
        $allowed = array('jpg', 'jpeg', 'png', 'pdf');
        $filename = $_FILES['id_proof']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $uploadError = "Only JPG, JPEG, PNG, and PDF files are allowed.";
        }
    }
    
    if (empty($uploadError) && $validator->validate($_POST)) {
        // Validate child age (must be under 16 years)
        $dob = $_POST['dob'];
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
        
        // Check if DOB is in the future
        if ($dobDate > $today) {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              icon: 'error',
              title: 'Invalid Date!',
              text: 'Date of birth cannot be in the future.'
            });
            </script>
            ";
        }
        // Check if child is 16 years or older
        elseif ($age >= 16) {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              icon: 'error',
              title: 'Age Restriction!',
              text: 'Only children under 16 years can be added.'
            });
            </script>
            ";
        }
        else {
            // Handle file upload (validation already done above)
            $allowed = array('jpg', 'jpeg', 'png', 'pdf');
            $filename = $_FILES['id_proof']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            $newFilename = 'child_id_' . time() . '_' . uniqid() . '.' . $ext;
            $uploadPath = '../uploads/' . $newFilename;
            
            if (move_uploaded_file($_FILES['id_proof']['tmp_name'], $uploadPath)) {
                $data = array(
                    'cname'  => $_POST['cname'],
                    'gender' => $_POST['gender'],
                    'dob'    => $_POST['dob'],
                    'pid'    => $parentId,
                    'id_proof' => $newFilename
                );

                if($dao->insert($data,"child")) {
                    $_SESSION['child_added'] = true;
                    header('Location: profile.php');
                    exit;
                } else {
                    $msg = "Failed to add child to database. Please try again.";
                }
            } else {
                $msg = "Failed to upload file. Please check folder permissions.";
            }
        }
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
            background: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1a237e;
        }
        .row {
            margin-bottom: 20px;
        }
        label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
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
            background: linear-gradient(90deg, #1a237e, #3f51b5);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: linear-gradient(90deg, #0d153a, #1a237e);
            box-shadow: 0 0 10px rgba(26,35,126,0.4);
        }
        .error {
            color: red;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add Child</h2>

    <?php if ($msg): ?>
        <p style="color:red; text-align:center;"><?= $msg ?></p>
    <?php endif; ?>
    
    <?php if ($uploadError): ?>
        <p style="color:red; text-align:center;"><strong><?= $uploadError ?></strong></p>
    <?php endif; ?>
    
    <?php if (isset($_POST["btn_insert"]) && !$validator->validate($_POST) && empty($uploadError)): ?>
        <div style="background: #fee2e2; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <p style="color: #dc2626; margin: 0; font-weight: 600;">Please fix the following errors:</p>
            <ul style="color: #dc2626; margin: 10px 0 0 20px;">
                <?php foreach($validator->getErrors() as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <label>Child Name:</label>
            <?= $form->textBox('cname', array('class' => 'form-control')); ?>
            <?= $validator->error('cname'); ?>
        </div>

        <div class="row">
            <label>Gender:</label>
            <?php
           // This logic is taken from the first code block to change gender input type.
           $options=array('Male'=>"m","Female"=>"f");
           echo $form->radioGroup('gender',array(),$options); ?>
            <?= $validator->error('gender'); ?>
        </div>

        <div class="row">
            <label>Date of Birth:</label>
            <?php
           // Set max date to today to prevent future dates
           $maxDate = date('Y-m-d');
           echo $form->inputBox('dob',array('class'=>'form-control', 'max'=>$maxDate),"date") 
           ?>
            <span style="color:red;"><?= $validator->error('dob'); ?></span>
            <small style="color: #666; display: block; margin-top: 5px;">* Only children under 16 years can be added</small>
        </div>

        <div class="row">
            <label>Child ID Proof: <span style="color:red;">*</span></label>
            <input type="file" name="id_proof" class="form-control" accept="image/*,.pdf" required>
            <small style="color: #666; display: block; margin-top: 5px;">Accepted formats: JPG, JPEG, PNG, PDF (Max 5MB)</small>
        </div>

        <button type="submit" name="btn_insert">Add Child</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
