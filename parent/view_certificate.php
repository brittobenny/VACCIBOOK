<?php
require('../config/autoload.php');
$dao = new DataAccess();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$parent = $_SESSION['parent'] ?? null;
$parentId = $parent['pid'] ?? 0;

// Get certificate filename from URL
$certificateFile = isset($_GET['cert']) ? $_GET['cert'] : '';
$bid = isset($_GET['bid']) ? intval($_GET['bid']) : 0;

if (empty($certificateFile) || $bid == 0) {
    echo "<p style='text-align:center; margin-top:50px;'>Invalid certificate.</p>";
    exit;
}

// Verify this certificate belongs to this parent's booking
$booking = $dao->getData("*", "book", "bid=$bid AND pid=$parentId AND certificate='$certificateFile'");

if (empty($booking)) {
    echo "<p style='text-align:center; margin-top:50px;'>Certificate not found or access denied.</p>";
    exit;
}

$certificatePath = "../uploads/certificates/" . $certificateFile;

if (!file_exists($certificatePath)) {
    echo "<p style='text-align:center; margin-top:50px;'>Certificate file not found.</p>";
    exit;
}

// Read certificate content
$certificateContent = file_get_contents($certificatePath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Certificate</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fb;
            padding: 20px;
        }
        
        .header-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .back-btn {
            padding: 10px 20px;
            background: #64748b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
            display: inline-block;
        }
        
        .back-btn:hover {
            background: #475569;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-download {
            padding: 12px 24px;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }
        
        .btn-download:hover {
            background: #059669;
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        
        .btn-print {
            padding: 12px 24px;
            background: #1a237e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }
        
        .btn-print:hover {
            background: #0d153a;
            box-shadow: 0 4px 12px rgba(26,35,126,0.3);
        }
        
        .certificate-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .certificate-frame {
            width: 100%;
            min-height: 800px;
            border: none;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .header-bar {
                display: none;
            }
            
            .certificate-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <a href="bookings.php" class="back-btn">← Back to Bookings</a>
        <div class="action-buttons">
            <button onclick="printCertificate()" class="btn-print">
                🖨️ Print Certificate
            </button>
            <a href="../uploads/certificates/<?= htmlspecialchars($certificateFile) ?>" 
               download="vaccination_certificate_<?= $bid ?>.html" 
               class="btn-download">
                📥 Download Certificate
            </a>
        </div>
    </div>
    
    <div class="certificate-container">
        <iframe class="certificate-frame" srcdoc="<?= htmlspecialchars($certificateContent) ?>"></iframe>
    </div>
    
    <script>
        function printCertificate() {
            window.print();
        }
        
        // Auto-resize iframe to content
        window.addEventListener('load', function() {
            const iframe = document.querySelector('.certificate-frame');
            iframe.onload = function() {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    const height = iframeDoc.body.scrollHeight;
                    iframe.style.height = height + 'px';
                } catch(e) {
                    console.log('Could not resize iframe');
                }
            };
        });
    </script>
</body>
</html>
