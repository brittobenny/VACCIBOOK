<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['healthcentre'])) {
    header("Location: login.php");
    exit;
}

// Get child ID from URL
$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;

if ($cid == 0) {
    echo "<p style='text-align:center; margin-top:50px; color:red;'>Invalid child ID.</p>";
    exit;
}

// Get child details including id_proof
$child = $dao->getData("*", "child", "cid=$cid");

if (empty($child)) {
    echo "<p style='text-align:center; margin-top:50px; color:red;'>Child not found.</p>";
    exit;
}

$childData = $child[0];
$childName = $childData['cname'] ?? 'Unknown';
$dob = $childData['dob'] ?? 'N/A';
$gender = ($childData['gender'] == 'M' || $childData['gender'] == 'm') ? 'Male' : 'Female';
$idProof = $childData['id_proof'] ?? '';

// Check if ID proof exists
if (empty($idProof)) {
    echo "<p style='text-align:center; margin-top:50px; color:red;'>No ID proof uploaded for this child.</p>";
    exit;
}

// Get file extension to determine type
$fileExt = strtolower(pathinfo($idProof, PATHINFO_EXTENSION));
$isPDF = ($fileExt == 'pdf');
$filePath = '../uploads/' . $idProof;

// Check if file exists
if (!file_exists($filePath)) {
    echo "<p style='text-align:center; margin-top:50px; color:red;'>ID proof file not found on server.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View ID Proof - <?= htmlspecialchars($childName) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fb 0%, #e8eef5 100%);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(90deg, #1a237e, #3f51b5);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .close-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .close-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .child-info {
            padding: 25px 30px;
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a237e;
        }

        .document-viewer {
            padding: 30px;
            text-align: center;
            min-height: 500px;
        }

        .document-viewer img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .document-viewer iframe {
            width: 100%;
            height: 700px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .download-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: linear-gradient(90deg, #1a237e, #3f51b5);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
        }

        .download-btn:hover {
            background: linear-gradient(90deg, #0d153a, #1a237e);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .verification-section {
            padding: 20px 30px;
            background: #e8f5e9;
            border-top: 2px solid #4caf50;
            text-align: center;
        }

        .verification-section p {
            margin: 0;
            color: #2e7d32;
            font-weight: 600;
        }

        .verification-section i {
            font-size: 1.5rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-id-card"></i> Child ID Proof Verification</h2>
            <button class="close-btn" onclick="window.close()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>

        <div class="child-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Child Name</span>
                    <span class="info-value"><?= htmlspecialchars($childName) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Gender</span>
                    <span class="info-value"><?= htmlspecialchars($gender) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date of Birth</span>
                    <span class="info-value"><?= htmlspecialchars($dob) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Document Type</span>
                    <span class="info-value"><?= strtoupper($fileExt) ?></span>
                </div>
            </div>
        </div>

        <div class="document-viewer">
            <?php if ($isPDF): ?>
                <iframe src="<?= htmlspecialchars($filePath) ?>#toolbar=1"></iframe>
            <?php else: ?>
                <img src="<?= htmlspecialchars($filePath) ?>" alt="Child ID Proof">
            <?php endif; ?>
            
            <br>
            <a href="<?= htmlspecialchars($filePath) ?>" download class="download-btn">
                <i class="fas fa-download"></i> Download Document
            </a>
        </div>

        <div class="verification-section">
            <p>
                <i class="fas fa-shield-alt"></i>
                This document is confidential and should only be used for verification purposes.
            </p>
        </div>
    </div>
</body>
</html>
