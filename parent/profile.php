<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Parent info from session
$parent = $_SESSION['parent'] ?? null;
$parentName  = $parent['name']  ?? "Parent";
$parentEmail = $parent['email'] ?? "";
$parentPhone = $parent['mob']   ?? "";
$parentId    = $parent['pid']   ?? 0;

// Get children for this parent
$children = $dao->getData("*", "child", "pid=$parentId");

// Check if child was just added
$showSuccess = false;
if (isset($_SESSION['child_added'])) {
    $showSuccess = true;
    unset($_SESSION['child_added']);
}

// Helper: calculate age
function calculateAge($dob) {
    if (!$dob) return "Not set";
    $dobDate = new DateTime($dob);
    $today   = new DateTime();
    $age     = $today->diff($dobDate);
    return $age->y . " years, " . $age->m . " months";
}
?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #ffffff;
    margin: 0;
    padding: 0;
    color: #333;
}

/* Layout */
.profile-container {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
    max-width: 1200px;
    margin: 50px auto;
    padding: 0 20px;
}

/* Profile Card */
.profile-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 6px 20px rgba(30, 58, 138, 0.12);
    text-align: left;
    border: 1px solid #e2e8f0;
    animation: slideInLeft 0.6s ease-out;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.profile-card h3 {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
    font-weight: 700;
}
.profile-card p {
    margin: 10px 0;
    font-size: 15px;
    color: #475569;
}
.profile-card p strong {
    color: #1e3a8a;
}
.profile-card button {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 30px;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.profile-card button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    color: #fff;
}

/* Fix profile card height */
.fixed-card {
    min-height: 260px;
    max-height: 320px;
    overflow-y: auto;
}

/* Action Card (4 buttons) */
.ad-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 6px 20px rgba(30, 58, 138, 0.12);
    border: 1px solid #e2e8f0;
    animation: slideInLeft 0.6s ease-out;
    animation-delay: 0.2s;
    opacity: 0;
    animation-fill-mode: forwards;
}
.action-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}
.action-buttons .btn {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
    color: #fff;
    border-radius: 16px;
    padding: 22px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-buttons .btn i {
    font-size: 24px;
    margin-bottom: 10px;
    transition: transform 0.3s ease;
}

.action-buttons .btn:hover i {
    transform: scale(1.15);
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    color: #fff;
}

/* Children Section */
.children-section {
    margin-top: 20px;
}

.children-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

.child-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 26px;
    text-align: left;
    box-shadow: 0 6px 20px rgba(30, 58, 138, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    opacity: 0;
    animation: cardFadeIn 0.6s ease-out forwards;
}

@keyframes cardFadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.child-card:nth-child(1) { animation-delay: 0.1s; }
.child-card:nth-child(2) { animation-delay: 0.2s; }
.child-card:nth-child(3) { animation-delay: 0.3s; }
.child-card:nth-child(4) { animation-delay: 0.4s; }
.child-card:nth-child(5) { animation-delay: 0.5s; }
.child-card:nth-child(6) { animation-delay: 0.6s; }

.child-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(30, 58, 138, 0.2);
    border-color: #3b82f6;
}

.child-card h4 {
    margin-bottom: 12px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 19px;
    font-weight: 700;
}

.child-card p {
    font-size: 14px;
    color: #475569;
    margin: 6px 0;
}

.child-card p strong {
    color: #1e3a8a;
}

.child-card button {
    margin-top: 18px;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6, #06b6d4);
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.child-card button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    color: #fff;
}

/* Add Child Card */
.add-child {
    border: 2px dashed #3b82f6;
    text-align: center;
    justify-content: center;
    align-items: center;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 180px;
    background: #ffffff;
}
.add-child:hover {
    background: #f0f9ff;
    border-color: #1e3a8a;
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(30, 58, 138, 0.15);
}
.add-child i {
    font-size: 36px;
    color: #fff;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    padding: 16px;
    border-radius: 50%;
    box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
    transition: transform 0.3s ease;
}
.add-child:hover i {
    transform: rotate(90deg) scale(1.1);
}
.add-child span {
    color: #1e3a8a;
    font-weight: 600;
    font-size: 16px;
}
</style>

<div class="profile-container">
    <!-- Left Column (Profile + Action Buttons) -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Profile Card -->
        <div class="profile-card fixed-card">
            <h3>My Profile</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($parentName) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($parentEmail) ?></p>
            <p><strong>Phone:</strong> <?= $parentPhone ? htmlspecialchars($parentPhone) : "Not provided" ?></p>
            <br>
            <button onclick="window.location.href='editprofile.php'">Edit Profile</button>
        </div>

        <!-- Action Buttons Card -->
        <div class="ad-card">
            <div class="action-buttons">
                <div class="btn" onclick="window.location.href='dashboard.php'">
                    <i class="fas fa-calendar-check"></i>
                    Book Now
                </div>
                <div class="btn" onclick="window.location.href='healthcentres.php'">
                    <i class="fas fa-hospital"></i>
                    Find Centres
                </div>
                <div class="btn" onclick="window.location.href='feedback.php'">
                    <i class="fas fa-comment-dots"></i>
                    Feedback
                </div>
                <div class="btn" onclick="window.location.href='logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </div>
            </div>
        </div>
    </div>

    <!-- Children Section -->
    <div class="children-section">
        <h3>My Children</h3>
        <br>
        <div class="children-cards">
            <?php if (!empty($children)) { 
                foreach ($children as $child) { 
                    $childId = $child['cid'] ?? $child['id'];
                    $gender = ($child['gender'] == 'M') ? 'Male' : 'Female';
            ?>
                <div class="child-card">
                    <h4><?= htmlspecialchars($child['cname']) ?></h4>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($gender) ?></p>
                    <p><strong>DOB:</strong> <?= htmlspecialchars($child['dob']) ?></p>
                    <p><strong>Age:</strong> <?= calculateAge($child['dob']) ?></p>
                    <button onclick="window.location.href='vaccinehistory.php?id=<?= urlencode($childId) ?>'">
                        View Vaccination History
                    </button>
                    <button onclick="window.location.href='suggested_vaccines.php?cid=<?= urlencode($childId) ?>'" 
                            style="background: linear-gradient(135deg, #10b981, #059669); margin-top: 10px;">
                        💉 Suggested Vaccines
                    </button>
                </div>
            <?php } 
            } ?>
            
            <!-- Always show Add Child card -->
            <div class="child-card add-child" onclick="window.location.href='addchild.php'">
                <i class="fas fa-plus"></i>
                <span>Add a Child</span>
            </div>
        </div>
    </div>
</div>

<?php if ($showSuccess): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  icon: 'success',
  title: 'Child Added!',
  text: 'The child was added successfully.',
  showConfirmButton: false,
  timer: 2000
});
</script>
<?php endif; ?>
