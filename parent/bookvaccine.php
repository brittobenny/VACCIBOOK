<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$parent = $_SESSION['parent'] ?? null;
$parentId = $parent['pid'] ?? 0;

// Function to calculate age
function calculateAge($dob) {
    if (!$dob) return "Not set";
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($dobDate);
    
    if ($age->y > 0) {
        return $age->y . " year" . ($age->y > 1 ? "s" : "");
    } elseif ($age->m > 0) {
        return $age->m . " month" . ($age->m > 1 ? "s" : "");
    } else {
        return $age->d . " day" . ($age->d > 1 ? "s" : "");
    }
}

// Function to validate if vaccine period matches child's age
function validateVaccineAge($period, $dob) {
    if (!$dob) return ['valid' => false, 'message' => 'Child date of birth not set'];
    
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($dobDate);
    
    // Calculate age in different units
    $ageInDays = $age->days;
    $ageInWeeks = floor($ageInDays / 7);
    $ageInMonths = ($age->y * 12) + $age->m;
    $ageInYears = $age->y;
    
    $period = strtolower(trim($period));
    
    // Parse the period string for days
    if (preg_match('/(\d+)\s*(day|days)/', $period, $matches)) {
        $requiredDays = intval($matches[1]);
        // Allow booking from birth to 14 days after recommended period for days
        if ($ageInDays >= 0 && $ageInDays <= ($requiredDays + 14)) {
            return ['valid' => true, 'message' => ''];
        }
        return ['valid' => false, 'message' => "This vaccine is for {$requiredDays} day old babies. Your child is too old for this vaccine."];
    }
    // Parse the period string for weeks
    elseif (preg_match('/(\d+)\s*(week|weeks)/', $period, $matches)) {
        $requiredWeeks = intval($matches[1]);
        // Allow booking from 4 weeks before to 6 weeks after recommended period
        // This allows 2 month old (8-9 weeks) to book 12 weeks vaccine
        if ($ageInWeeks >= ($requiredWeeks - 4) && $ageInWeeks <= ($requiredWeeks + 6)) {
            return ['valid' => true, 'message' => ''];
        }
        if ($ageInWeeks < ($requiredWeeks - 4)) {
            return ['valid' => false, 'message' => "This vaccine is for {$requiredWeeks} week old babies. Your child is too young. Recommended age not yet reached."];
        }
        return ['valid' => false, 'message' => "This vaccine is for {$requiredWeeks} week old babies. Your child is too old for this vaccine."];
    }
    // Parse the period string for months
    elseif (preg_match('/(\d+)\s*(month|months)/', $period, $matches)) {
        $requiredMonths = intval($matches[1]);
        // Allow booking from 1 month before to 3 months after recommended period
        if ($ageInMonths >= ($requiredMonths - 1) && $ageInMonths <= ($requiredMonths + 3)) {
            return ['valid' => true, 'message' => ''];
        }
        if ($ageInMonths < ($requiredMonths - 1)) {
            return ['valid' => false, 'message' => "This vaccine is for {$requiredMonths} month old children. Your child is too young. Recommended age not yet reached."];
        }
        return ['valid' => false, 'message' => "This vaccine is for {$requiredMonths} month old children. Your child is too old for this vaccine."];
    }
    // Parse the period string for years
    elseif (preg_match('/(\d+)\s*(year|years)/', $period, $matches)) {
        $requiredYears = intval($matches[1]);
        $requiredMonthsForYear = $requiredYears * 12;
        // Allow booking from 2 months before to 6 months after recommended period
        if ($ageInMonths >= ($requiredMonthsForYear - 2) && $ageInMonths <= ($requiredMonthsForYear + 6)) {
            return ['valid' => true, 'message' => ''];
        }
        if ($ageInMonths < ($requiredMonthsForYear - 2)) {
            return ['valid' => false, 'message' => "This vaccine is for {$requiredYears} year old children. Your child is too young. Recommended age not yet reached."];
        }
        return ['valid' => false, 'message' => "This vaccine is for {$requiredYears} year old children. Your child is too old for this vaccine."];
    }
    
    // If period format is not recognized, allow booking but warn
    return ['valid' => true, 'message' => ''];
}

// Check if booking a specific schedule (sid parameter)
$sid = isset($_GET['sid']) ? intval($_GET['sid']) : 0;

if ($sid > 0) {
    // Show booking form for this schedule
    $scheduleInfo = $dao->query("SELECT s.sid, s.date, s.units, v.vname, v.vid, v.period, h.hname, h.loc 
                                  FROM schedule s
                                  JOIN vaccine v ON s.vid = v.vid
                                  JOIN healthcentre h ON s.hid = h.hid
                                  WHERE s.sid = $sid");
    
    if (!$scheduleInfo) {
        echo "<p style='text-align:center; margin-top:50px;'>Schedule not found.</p>";
        exit;
    }
    
    $schedule = $scheduleInfo[0];
    
    // Get children for this parent
    $children = $dao->getData("*", "child", "pid=$parentId");
    
    // Handle booking submission
    if (isset($_POST['btn_book'])) {
        $cid = isset($_POST['cid']) ? intval($_POST['cid']) : 0;
        
        if ($cid > 0) {
            // Get child's date of birth for age validation
            $childInfo = $dao->getData("*", "child", "cid=$cid");
            
            if (!empty($childInfo)) {
                $childDob = $childInfo[0]['dob'];
                $childName = $childInfo[0]['cname'];
                
                // Validate vaccine age appropriateness
                $ageValidation = validateVaccineAge($schedule['period'], $childDob);
                
                if (!$ageValidation['valid']) {
                    echo "
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                    Swal.fire({
                      icon: 'error',
                      title: 'Age Mismatch!',
                      text: '" . addslashes($ageValidation['message']) . "',
                      confirmButtonText: 'OK'
                    });
                    </script>
                    ";
                } else {
                    // Check if already booked
                    $existingBooking = $dao->getData("*", "book", "pid=$parentId AND sid=$sid AND cid=$cid");
                    
                    if (!empty($existingBooking)) {
                        echo "
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                        <script>
                        Swal.fire({
                          icon: 'warning',
                          title: 'Already Booked!',
                          text: 'This vaccine is already booked for the selected child.',
                          confirmButtonText: 'OK'
                        }).then(() => {
                          window.location.href = 'bookings.php';
                        });
                        </script>
                        ";
                    } else {
                // Check if units are available
                if ($schedule['units'] > 0) {
                    $bookingData = array(
                        'pid' => $parentId,
                        'sid' => $sid,
                        'cid' => $cid
                    );
                    
                    if ($dao->insert($bookingData, "book")) {
                        // Decrease units by 1
                        $newUnits = $schedule['units'] - 1;
                        $dao->update(array('units' => $newUnits), "schedule", "sid=$sid");
                        
                        echo "
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                        <script>
                        Swal.fire({
                          icon: 'success',
                          title: 'Booking Successful!',
                          text: 'Your vaccine appointment has been booked.',
                          showConfirmButton: false,
                          timer: 2000
                        }).then(() => {
                          window.location.href = 'bookings.php';
                        });
                        </script>
                        ";
                    } else {
                        echo "
                        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                        <script>
                        Swal.fire({
                          icon: 'error',
                          title: 'Booking Failed!',
                          text: 'Something went wrong. Please try again.'
                        });
                        </script>
                        ";
                    }
                } else {
                    echo "
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                    Swal.fire({
                      icon: 'error',
                      title: 'No Units Available!',
                      text: 'This vaccine schedule is fully booked.'
                    });
                    </script>
                    ";
                }
            }
                }
            } else {
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                Swal.fire({
                  icon: 'error',
                  title: 'Child Not Found!',
                  text: 'Unable to retrieve child information.'
                });
                </script>
                ";
            }
        } else {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              icon: 'warning',
              title: 'Select a Child!',
              text: 'Please select a child to book the vaccine.'
            });
            </script>
            ";
        }
    }
    
    // Display booking form
    ?>
    <style>
    .booking-form-container {
        max-width: 700px;
        margin: 50px auto;
        background: #ffffff;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(30, 58, 138, 0.1);
        border: 1px solid #e2e8f0;
        animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .booking-form-container h2 {
        text-align: center;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 30px;
        font-size: 2rem;
        font-weight: 700;
    }
    .schedule-details {
        background: #f0f9ff;
        padding: 25px;
        border-radius: 16px;
        margin-bottom: 30px;
        border: 1px solid #bfdbfe;
    }
    .schedule-details p {
        margin: 12px 0;
        font-size: 1rem;
        color: #334155;
    }
    .schedule-details strong {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 600;
    }
    .form-group {
        margin-bottom: 25px;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 10px;
        color: #1a237e;
    }
    .child-selection {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    .child-option {
        position: relative;
    }
    .child-option input[type="radio"] {
        display: none;
    }
    .child-label {
        display: block;
        padding: 20px;
        border: 2px solid rgba(102, 126, 234, 0.2);
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        background: white;
    }
    .child-label:hover {
        border-color: #3b82f6;
        background: #f0f9ff;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(30, 58, 138, 0.15);
    }
    .child-option input[type="radio"]:checked + .child-label {
        border: 2px solid #1e3a8a;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        font-weight: 600;
        color: white;
        box-shadow: 0 8px 24px rgba(30, 58, 138, 0.3);
    }
    
    .child-option input[type="radio"]:checked + .child-label .child-name,
    .child-option input[type="radio"]:checked + .child-label .child-info {
        color: white !important;
    }
    .child-label .child-name {
        font-size: 1.1rem;
        color: #1a237e;
        margin-bottom: 5px;
    }
    .child-label .child-info {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    .child-option.disabled .child-label {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    
    .child-option.disabled .child-label:hover {
        transform: none;
        box-shadow: none;
        border-color: #cbd5e1;
        background: #f1f5f9;
    }
    
    .child-option.disabled input[type="radio"]:disabled + .child-label {
        border: 2px solid #cbd5e1;
        background: #f1f5f9;
        color: #94a3b8;
    }
    
    .age-warning {
        margin-top: 8px;
        padding: 4px 8px;
        background: #fef2f2;
        color: #dc2626;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .age-eligible {
        margin-top: 8px;
        padding: 4px 8px;
        background: #f0fdf4;
        color: #16a34a;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .child-option input[type="radio"]:checked + .child-label .age-eligible {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }
    
    .btn-submit {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
        color: #fff;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        color: #fff;
    }
    
    .btn-back {
        display: inline-block;
        margin-bottom: 20px;
        padding: 11px 22px;
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        color: #fff;
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(100, 116, 139, 0.2);
    }
    .btn-back:hover {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(100, 116, 139, 0.3);
    }
    .no-children {
        text-align: center;
        padding: 40px;
        color: #64748b;
    }
    .no-children a {
        color: #1a237e;
        font-weight: 600;
        text-decoration: underline;
    }
    </style>
    
    <div class="booking-form-container">
        <a href="dashboard.php" class="btn-back">← Back to Schedules</a>
        <h2>Book Vaccine Appointment</h2>
        
        <div class="schedule-details">
            <p><strong>Vaccine:</strong> <?= htmlspecialchars($schedule['vname']) ?></p>
            <p><strong>Recommended Age:</strong> <?= htmlspecialchars($schedule['period']) ?></p>
            <p><strong>Health Centre:</strong> <?= htmlspecialchars($schedule['hname']) ?> (<?= htmlspecialchars($schedule['loc']) ?>)</p>
            <p><strong>Date:</strong> <?= htmlspecialchars($schedule['date']) ?></p>
            <p><strong>Units Available:</strong> <?= intval($schedule['units']) ?></p>
        </div>
        
        <?php if (!empty($children)) { 
            // Check if any child is eligible
            $eligibleCount = 0;
            foreach ($children as $child) {
                $ageCheck = validateVaccineAge($schedule['period'], $child['dob']);
                if ($ageCheck['valid']) {
                    $eligibleCount++;
                }
            }
        ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Select Child:</label>
                    <?php if ($eligibleCount == 0) { ?>
                        <div class="no-children" style="padding: 20px; margin-bottom: 20px;">
                            <p style="color: #dc2626; font-weight: 600;">⚠️ None of your children are eligible for this vaccine</p>
                            <p style="font-size: 0.9rem;">This vaccine is recommended for <strong><?= htmlspecialchars($schedule['period']) ?></strong> old children. Your children are either too young or too old for this vaccine.</p>
                        </div>
                    <?php } ?>
                    <div class="child-selection">
                        <?php foreach ($children as $child) { 
                            $gender = ($child['gender'] == 'M' || $child['gender'] == 'm') ? 'Male' : 'Female';
                            $age = calculateAge($child['dob']);
                            $ageCheck = validateVaccineAge($schedule['period'], $child['dob']);
                            $isEligible = $ageCheck['valid'];
                            $disabledAttr = $isEligible ? '' : 'disabled';
                            $disabledClass = $isEligible ? '' : 'disabled';
                        ?>
                            <div class="child-option <?= $disabledClass ?>">
                                <input type="radio" name="cid" id="child_<?= $child['cid'] ?>" value="<?= $child['cid'] ?>" <?= $disabledAttr ?> <?= ($isEligible && $eligibleCount > 0) ? 'required' : '' ?>>
                                <label for="child_<?= $child['cid'] ?>" class="child-label <?= $disabledClass ?>">
                                    <div class="child-name"><?= htmlspecialchars($child['cname']) ?></div>
                                    <div class="child-info"><?= $gender ?></div>
                                    <div class="child-info">Age: <?= $age ?></div>
                                    <?php if (!$isEligible) { ?>
                                        <div class="age-warning">⚠️ Not eligible</div>
                                    <?php } else { ?>
                                        <div class="age-eligible">✓ Eligible</div>
                                    <?php } ?>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                
                <?php if ($eligibleCount > 0) { ?>
                    <button type="submit" name="btn_book" class="btn-submit">Confirm Booking</button>
                <?php } else { ?>
                    <a href="dashboard.php" class="btn-submit" style="display: block; text-align: center; text-decoration: none; line-height: 16px;">Back to Vaccines</a>
                <?php } ?>
            </form>
        <?php } else { ?>
            <div class="no-children">
                <p>You don't have any children registered yet.</p>
                <p><a href="addchild.php">Add a child</a> to book vaccines.</p>
            </div>
        <?php } ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    exit;
}

// Otherwise, show schedule list
// Get vid or hid from query string
$vid = isset($_GET['vid']) ? intval($_GET['vid']) : 0;
$hid = isset($_GET['hid']) ? intval($_GET['hid']) : 0;

// Prepare SQL and heading
$heading = "";
if ($vid > 0) {
    $sql = "SELECT s.sid, s.date, s.units, v.vname, h.hname, h.loc 
            FROM schedule s
            JOIN vaccine v ON s.vid = v.vid
            JOIN healthcentre h ON s.hid = h.hid
            WHERE s.vid = $vid";
    $info = $dao->query("SELECT vname FROM vaccine WHERE vid=$vid");
    $heading = "Available Schedules for " . ($info ? htmlspecialchars($info[0]['vname']) : "Vaccine");
} elseif ($hid > 0) {
    $sql = "SELECT s.sid, s.date, s.units, v.vname, h.hname, h.loc 
            FROM schedule s
            JOIN vaccine v ON s.vid = v.vid
            JOIN healthcentre h ON s.hid = h.hid
            WHERE s.hid = $hid";
    $info = $dao->query("SELECT hname FROM healthcentre WHERE hid=$hid");
    $heading = "Available Schedules at " . ($info ? htmlspecialchars($info[0]['hname']) : "Health Centre");
} else {
    echo "<p>No vaccine or health centre selected.</p>";
    exit;
}

// Fetch data
$schedules = $dao->query($sql);
?>

<style>
.page-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    margin: 40px 0 30px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.schedule-container {
    max-width: 1100px;
    margin: 0 auto 60px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    padding: 0 20px;
}

.schedule-card {
    background: #fff;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}
.schedule-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

.schedule-info p {
    margin: 10px 0;
    font-size: 0.95rem;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
}
.schedule-info strong {
    color: #1e3a8a;
}

/* Date styling */
.schedule-info .date {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e3a8a;
}

/* Units badge */
.units {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 5px;
}
.units.available { background: #dcfce7; color: #15803d; }
.units.low { background: #fef9c3; color: #b45309; }
.units.none { background: #fee2e2; color: #b91c1c; }

/* Book button */
.book-btn {
    display: block;
    width: 100%;
    text-align: center;
    padding: 12px;
    border-radius: 12px;
    background: linear-gradient(90deg, #1a237e, #3f51b5);
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    margin-top: 15px;
    transition: 0.3s;
}
.book-btn:hover {
    background: linear-gradient(90deg, #0d153a, #1a237e);
    transform: scale(1.02);
}
</style>

<div class="page-title"><?= $heading ?></div>

<div class="schedule-container">
    <?php if (!empty($schedules)) { 
        foreach ($schedules as $s) { 
            $units = intval($s['units']);
            $unitClass = $units == 0 ? "none" : ($units < 5 ? "low" : "available");
        ?>
            <div class="schedule-card">
                <div class="schedule-info">
                    <p class="date">📅 <?= htmlspecialchars($s['date']) ?></p>
                    <p>💉 <strong>Vaccine:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong> <?= htmlspecialchars($s['vname']) ?></p>
                    <p>🏥 <strong>Health Centre:</strong> <?= htmlspecialchars($s['hname']) ?> (<?= htmlspecialchars($s['loc']) ?>)</p>
                    <p class="units <?= $unitClass ?>">Units Available: <?= $units ?></p>
                </div>
                <a class="book-btn" href="bookvaccine.php?sid=<?= $s['sid'] ?>">Book Now</a>
            </div>
    <?php } 
     } else { ?>
    <div style="text-align:center; grid-column: 1 / -1; padding:40px; color:#64748b;">
        <img src="https://cdn-icons-png.flaticon.com/512/25/25231.png" width="100" alt="No schedules">
        <p style="margin-top:15px; font-size:1.1rem; font-weight:500;">No available schedules found</p>
    </div>
<?php } ?>

</div>
