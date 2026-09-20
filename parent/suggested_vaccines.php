<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$parent = $_SESSION['parent'] ?? null;
$parentId = $parent['pid'] ?? 0;

// Get child ID from URL
$cid = $_GET['cid'] ?? 0;

// Get child details
$child = $dao->getData("*", "child", "cid=$cid AND pid=$parentId");

if (empty($child)) {
    header("Location: profile.php");
    exit;
}

$childData = $child[0];
$childName = $childData['cname'] ?? 'Child';
$dob = $childData['dob'] ?? '';

// Calculate age in days
function calculateAgeInDays($dob) {
    if (!$dob) return 0;
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $diff = $today->diff($dobDate);
    return $diff->days;
}

// Calculate age in weeks
function calculateAgeInWeeks($dob) {
    if (!$dob) return 0;
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $diff = $today->diff($dobDate);
    return floor($diff->days / 7);
}

// Calculate age in months
function calculateAgeInMonths($dob) {
    if (!$dob) return 0;
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $diff = $today->diff($dobDate);
    return ($diff->y * 12) + $diff->m;
}

// Calculate age display
function calculateAgeDisplay($dob) {
    if (!$dob) return "Not set";
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($dobDate);
    
    $parts = [];
    if ($age->y > 0) {
        $parts[] = $age->y . " year" . ($age->y > 1 ? "s" : "");
    }
    if ($age->m > 0) {
        $parts[] = $age->m . " month" . ($age->m > 1 ? "s" : "");
    }
    if ($age->d > 0 && $age->y == 0 && $age->m == 0) {
        $parts[] = $age->d . " day" . ($age->d > 1 ? "s" : "");
    }
    
    return !empty($parts) ? implode(", ", $parts) : "Less than 1 day";
}

$ageInDays = calculateAgeInDays($dob);
$ageInWeeks = calculateAgeInWeeks($dob);
$ageInMonths = calculateAgeInMonths($dob);
$ageDisplay = calculateAgeDisplay($dob);

// Get all vaccines
$vaccines = $dao->query("SELECT * FROM vaccine");

if (!is_array($vaccines)) {
    $vaccines = [];
}

// Function to parse period and match with age
function matchVaccineWithAge($period, $ageInDays, $ageInWeeks, $ageInMonths) {
    $period = strtolower(trim($period));
    
    // Parse the period string for days
    if (preg_match('/(\d+)\s*(day|days)/', $period, $matches)) {
        $days = intval($matches[1]);
        
        // Match if child is within 7 days of the recommended age
        if ($ageInDays >= $days - 3 && $ageInDays <= $days + 7) {
            return ['match' => true, 'status' => 'due', 'sortValue' => $days];
        } elseif ($ageInDays < $days - 3) {
            return ['match' => false, 'status' => 'upcoming', 'sortValue' => $days];
        }
    }
    // Parse the period string for weeks
    elseif (preg_match('/(\d+)\s*(week|weeks)/', $period, $matches)) {
        $weeks = intval($matches[1]);
        
        // Match if child is within 3 weeks before to 2 weeks after the recommended age
        // This allows parents to see and book appointments in advance
        if ($ageInWeeks >= $weeks - 3 && $ageInWeeks <= $weeks + 2) {
            return ['match' => true, 'status' => 'due', 'sortValue' => $weeks * 7];
        } elseif ($ageInWeeks < $weeks - 3) {
            return ['match' => false, 'status' => 'upcoming', 'sortValue' => $weeks * 7];
        }
    }
    // Parse the period string for months
    elseif (preg_match('/(\d+)\s*(month|months)/', $period, $matches)) {
        $months = intval($matches[1]);
        
        // Match if child is within 1 month of the recommended age
        if ($ageInMonths >= $months - 1 && $ageInMonths <= $months + 1) {
            return ['match' => true, 'status' => 'due', 'sortValue' => $months * 30];
        } elseif ($ageInMonths < $months - 1) {
            return ['match' => false, 'status' => 'upcoming', 'sortValue' => $months * 30];
        }
    }
    // Parse the period string for years
    elseif (preg_match('/(\d+)\s*(year|years)/', $period, $matches)) {
        $years = intval($matches[1]);
        $months = $years * 12;
        
        // Match if child is within 2 months of the recommended age
        if ($ageInMonths >= $months - 2 && $ageInMonths <= $months + 2) {
            return ['match' => true, 'status' => 'due', 'sortValue' => $months * 30];
        } elseif ($ageInMonths < $months - 2) {
            return ['match' => false, 'status' => 'upcoming', 'sortValue' => $months * 30];
        }
    }
    
    return ['match' => false, 'status' => 'na', 'sortValue' => 999999];
}

// Categorize vaccines
$suggestedVaccines = [];
$upcomingVaccines = [];

foreach ($vaccines as $vaccine) {
    $matchResult = matchVaccineWithAge($vaccine['period'], $ageInDays, $ageInWeeks, $ageInMonths);
    
    if ($matchResult['match'] && $matchResult['status'] == 'due') {
        $vaccine['matchInfo'] = $matchResult;
        $suggestedVaccines[] = $vaccine;
    } elseif ($matchResult['status'] == 'upcoming') {
        $vaccine['matchInfo'] = $matchResult;
        $upcomingVaccines[] = $vaccine;
    }
}

// Sort suggested vaccines by closeness to recommended age (sortValue)
usort($suggestedVaccines, function($a, $b) {
    return $a['matchInfo']['sortValue'] - $b['matchInfo']['sortValue'];
});

// Take only the 3 closest vaccines for "Recommended Now"
// Move the rest to "Upcoming"
if (count($suggestedVaccines) > 3) {
    $extraVaccines = array_slice($suggestedVaccines, 3);
    $suggestedVaccines = array_slice($suggestedVaccines, 0, 3);
    
    // Add extra vaccines to upcoming section
    foreach ($extraVaccines as $vaccine) {
        $upcomingVaccines[] = $vaccine;
    }
}

// Sort upcoming by age
usort($upcomingVaccines, function($a, $b) {
    return $a['matchInfo']['sortValue'] - $b['matchInfo']['sortValue'];
});

// Limit upcoming to next 5
$upcomingVaccines = array_slice($upcomingVaccines, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggested Vaccines - <?= htmlspecialchars($childName) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fb 0%, #e8eef5 100%);
            background-attachment: fixed;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px 60px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 1.1rem;
        }

        .child-info {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.08);
            border: 1px solid rgba(30, 58, 138, 0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            animation: fadeInUp 0.6s ease 0.2s backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .child-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            padding: 20px;
            border-radius: 20px;
            color: white;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.2);
        }

        .child-details h2 {
            margin: 0 0 10px 0;
            color: #1e3a8a;
            font-size: 1.8rem;
        }

        .child-details p {
            margin: 5px 0;
            color: #64748b;
        }

        .section {
            margin-bottom: 40px;
            animation: fadeInUp 0.6s ease backwards;
        }

        .section:nth-child(3) { animation-delay: 0.3s; }
        .section:nth-child(4) { animation-delay: 0.4s; }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .vaccines-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .vaccine-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.08);
            border: 2px solid rgba(30, 58, 138, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .vaccine-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .vaccine-card.upcoming::before {
            background: linear-gradient(90deg, #3b82f6, #1e3a8a);
        }

        .vaccine-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(30, 58, 138, 0.15);
            border-color: #3b82f6;
        }

        .vaccine-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
            transition: transform 0.4s ease;
        }

        .vaccine-card:hover .vaccine-image {
            transform: scale(1.05);
        }

        .vaccine-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 10px;
        }

        .vaccine-period {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-due {
            background: #dcfce7;
            color: #15803d;
        }

        .status-upcoming {
            background: #dbeafe;
            color: #1e40af;
        }

        .book-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
            box-shadow: 0 8px 20px rgba(30, 58, 138, 0.2);
        }

        .book-btn:hover {
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(30, 58, 138, 0.3);
        }

        .no-vaccines {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.08);
        }

        .no-vaccines i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .no-vaccines p {
            color: #64748b;
            font-size: 1.1rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: white;
            color: #1e3a8a;
            border: 2px solid #1e3a8a;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: #1e3a8a;
            color: white;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="profile.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>

        <div class="page-header">
            <h1 class="page-title">Suggested Vaccines</h1>
            <p class="page-subtitle">Age-based vaccination recommendations</p>
        </div>

        <div class="child-info">
            <div class="child-icon">
                <i class="fas fa-baby"></i>
            </div>
            <div class="child-details">
                <h2><?= htmlspecialchars($childName) ?></h2>
                <p><strong>Current Age:</strong> <?= $ageDisplay ?></p>
                <p><strong>Age Details:</strong> <?= $ageInDays ?> days | <?= $ageInWeeks ?> weeks | <?= $ageInMonths ?> months</p>
                <p><strong>Date of Birth:</strong> <?= htmlspecialchars($dob) ?></p>
            </div>
        </div>

        <!-- Suggested Vaccines (Due Now) -->
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-check-circle" style="color: #10b981;"></i>
                Recommended Now
            </h2>

            <?php if (!empty($suggestedVaccines)): ?>
                <div class="vaccines-grid">
                    <?php foreach ($suggestedVaccines as $vaccine): ?>
                        <div class="vaccine-card">
                            <img src="../uploads/<?= htmlspecialchars($vaccine['vimage']) ?>" 
                                 alt="<?= htmlspecialchars($vaccine['vname']) ?>" 
                                 class="vaccine-image">
                            <span class="status-badge status-due">✓ Due Now</span>
                            <div class="vaccine-name"><?= htmlspecialchars($vaccine['vname']) ?></div>
                            <div class="vaccine-period">
                                <i class="fas fa-calendar-alt"></i>
                                Recommended at: <?= htmlspecialchars($vaccine['period']) ?>
                            </div>
                            <a href="schedule.php?vid=<?= $vaccine['vid'] ?>" class="book-btn">
                                <i class="fas fa-calendar-check"></i> Book Appointment
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-vaccines">
                    <i class="fas fa-check-circle"></i>
                    <p>No vaccines are currently due based on <?= htmlspecialchars($childName) ?>'s age.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Vaccines -->
        <?php if (!empty($upcomingVaccines)): ?>
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-clock" style="color: #3b82f6;"></i>
                    Upcoming Vaccines
                </h2>
                <div class="vaccines-grid">
                    <?php foreach ($upcomingVaccines as $vaccine): ?>
                        <div class="vaccine-card upcoming">
                            <img src="../uploads/<?= htmlspecialchars($vaccine['vimage']) ?>" 
                                 alt="<?= htmlspecialchars($vaccine['vname']) ?>" 
                                 class="vaccine-image">
                            <span class="status-badge status-upcoming">⏰ Upcoming</span>
                            <div class="vaccine-name"><?= htmlspecialchars($vaccine['vname']) ?></div>
                            <div class="vaccine-period">
                                <i class="fas fa-calendar-alt"></i>
                                Recommended at: <?= htmlspecialchars($vaccine['period']) ?>
                            </div>
                            <a href="schedule.php?vid=<?= $vaccine['vid'] ?>" class="book-btn">
                                <i class="fas fa-info-circle"></i> View Schedules
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
