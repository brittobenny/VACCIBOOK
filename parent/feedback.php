<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$parent = $_SESSION['parent'] ?? null;
$parentId = $parent['pid'] ?? 0;

// Get health centres for dropdown
$healthCentres = $dao->getData("*", "healthcentre", "status=1");

// Get parent's bookings for dropdown (only completed bookings)
$bookings = $dao->query("SELECT b.bid, v.vname, h.hname, DATE_FORMAT(s.date, '%d-%m-%Y') as booking_date
                         FROM book b
                         JOIN schedule s ON b.sid = s.sid
                         JOIN vaccine v ON s.vid = v.vid
                         JOIN healthcentre h ON s.hid = h.hid
                         WHERE b.pid = $parentId AND b.status = 'completed'
                         ORDER BY s.date DESC");

// Handle form submission
if (isset($_POST['btn_submit_feedback'])) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $hid = isset($_POST['hid']) && $_POST['hid'] != '' ? intval($_POST['hid']) : null;
    $bid = isset($_POST['bid']) && $_POST['bid'] != '' ? intval($_POST['bid']) : null;
    
    if ($rating > 0 && $subject != '' && $message != '') {
        $feedbackData = array(
            'pid' => $parentId,
            'rating' => $rating,
            'subject' => $subject,
            'message' => $message
        );
        
        // Add optional fields if provided
        if ($hid !== null) {
            $feedbackData['hid'] = $hid;
        }
        if ($bid !== null) {
            $feedbackData['bid'] = $bid;
        }
        
        if ($dao->insert($feedbackData, "feedback")) {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              icon: 'success',
              title: 'Thank You!',
              text: 'Your feedback has been submitted successfully.',
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              window.location.href = 'feedback.php';
            });
            </script>
            ";
        } else {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Failed to submit feedback. Please try again.',
              confirmButtonText: 'OK'
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
          title: 'Missing Information!',
          text: 'Please fill in all required fields.',
          confirmButtonText: 'OK'
        });
        </script>
        ";
    }
}

// Get parent's previous feedbacks
$myFeedbacks = $dao->query("SELECT f.*, h.hname, DATE_FORMAT(f.feedback_date, '%d %b %Y at %h:%i %p') as formatted_date
                            FROM feedback f
                            LEFT JOIN healthcentre h ON f.hid = h.hid
                            WHERE f.pid = $parentId
                            ORDER BY f.feedback_date DESC");

// Ensure myFeedbacks is an array
if (!is_array($myFeedbacks)) {
    $myFeedbacks = [];
}

// Get all approved feedbacks from other users (reviewed or resolved)
$approvedFeedbacks = $dao->query("SELECT f.*, p.pname, h.hname, 
                                  DATE_FORMAT(f.feedback_date, '%d %b %Y at %h:%i %p') as formatted_date
                                  FROM feedback f
                                  JOIN parent p ON f.pid = p.pid
                                  LEFT JOIN healthcentre h ON f.hid = h.hid
                                  WHERE f.status IN ('reviewed', 'resolved') AND f.pid != $parentId
                                  ORDER BY f.feedback_date DESC
                                  LIMIT 50");

// Ensure approvedFeedbacks is an array
if (!is_array($approvedFeedbacks)) {
    $approvedFeedbacks = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Vaccibook</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
            background-attachment: fixed;
        }

        .feedback-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease;
        }

        .page-title {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 16px;
        }

        .feedback-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .approved-section {
            grid-column: 1 / -1;
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease 0.2s backwards;
        }

        @media (max-width: 968px) {
            .feedback-grid {
                grid-template-columns: 1fr;
            }
        }

        .feedback-form-card, .feedback-history-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease;
        }

        .card-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .card-title {
            font-size: 22px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: #3b82f6;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #475569;
            font-size: 14px;
        }

        .required {
            color: #ef4444;
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* Star Rating */
        .star-rating {
            display: flex;
            gap: 8px;
            font-size: 32px;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            color: #cbd5e1;
            transition: all 0.3s ease;
        }

        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #fbbf24;
            transform: scale(1.1);
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Feedback History */
        .feedback-list {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .feedback-list::-webkit-scrollbar {
            width: 6px;
        }

        .feedback-list::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .feedback-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .feedback-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .feedback-item {
            padding: 20px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-radius: 12px;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s ease;
        }

        .feedback-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .feedback-rating {
            display: flex;
            gap: 3px;
        }

        .feedback-rating i {
            color: #fbbf24;
            font-size: 14px;
        }

        .feedback-date {
            font-size: 12px;
            color: #94a3b8;
        }

        .feedback-subject {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .feedback-message {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .feedback-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #94a3b8;
        }

        .feedback-meta i {
            margin-right: 4px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-reviewed {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-resolved {
            background: #dcfce7;
            color: #15803d;
        }

        .no-feedback {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .no-feedback i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #cbd5e1;
        }

        .no-feedback p {
            font-size: 16px;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .help-text {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 5px;
        }

        /* Approved Feedbacks Grid */
        .approved-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            max-height: 600px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .approved-grid::-webkit-scrollbar {
            width: 6px;
        }

        .approved-grid::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .approved-grid::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .approved-card {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #10b981;
            transition: all 0.3s ease;
        }

        .approved-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .parent-name {
            font-size: 14px;
            font-weight: 600;
            color: #3b82f6;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .parent-name i {
            font-size: 12px;
        }

        .verified-badge {
            background: #dcfce7;
            color: #15803d;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <div class="feedback-container">
        <div class="page-header">
            <h1 class="page-title">Share Your Feedback</h1>
            <p class="page-subtitle">We value your opinion and strive to improve our services</p>
        </div>

        <div class="feedback-grid">
            <!-- Feedback Form -->
            <div class="feedback-form-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-pen-to-square"></i>
                        Submit Feedback
                    </h2>
                </div>

                <form method="POST" action="">
                    <!-- Rating -->
                    <div class="form-group">
                        <label class="form-label">Rating <span class="required">*</span></label>
                        <div class="star-rating">
                            <input type="radio" name="rating" id="star5" value="5" required>
                            <label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star4" value="4">
                            <label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star3" value="3">
                            <label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star2" value="2">
                            <label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="star1" value="1">
                            <label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                        <p class="help-text">Click on the stars to rate your experience</p>
                    </div>

                    <!-- Health Centre (Optional) -->
                    <div class="form-group">
                        <label class="form-label" for="hid">Health Centre (Optional)</label>
                        <select class="form-select" name="hid" id="hid">
                            <option value="">-- Select Health Centre --</option>
                            <?php
                            if ($healthCentres) {
                                foreach ($healthCentres as $hc) {
                                    echo "<option value='{$hc['hid']}'>{$hc['hname']} - {$hc['loc']}</option>";
                                }
                            }
                            ?>
                        </select>
                        <p class="help-text">Select if feedback is about a specific health centre</p>
                    </div>

                    <!-- Booking (Optional) -->
                    <div class="form-group">
                        <label class="form-label" for="bid">Related Booking (Optional)</label>
                        <select class="form-select" name="bid" id="bid">
                            <option value="">-- Select Booking --</option>
                            <?php
                            if ($bookings) {
                                foreach ($bookings as $booking) {
                                    echo "<option value='{$booking['bid']}'>{$booking['vname']} - {$booking['hname']} ({$booking['booking_date']})</option>";
                                }
                            }
                            ?>
                        </select>
                        <p class="help-text">Select if feedback is about a specific vaccination</p>
                    </div>

                    <!-- Subject -->
                    <div class="form-group">
                        <label class="form-label" for="subject">Subject <span class="required">*</span></label>
                        <input type="text" class="form-input" name="subject" id="subject" 
                               placeholder="Brief title of your feedback" required maxlength="200">
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label class="form-label" for="message">Your Feedback <span class="required">*</span></label>
                        <textarea class="form-textarea" name="message" id="message" 
                                  placeholder="Share your experience, suggestions, or concerns..." required></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="btn_submit_feedback" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Submit Feedback
                    </button>
                </form>
            </div>

            <!-- Feedback History -->
            <div class="feedback-history-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-clock-rotate-left"></i>
                        Your Feedback History
                    </h2>
                </div>

                <div class="feedback-list">
                    <?php if ($myFeedbacks && count($myFeedbacks) > 0): ?>
                        <?php foreach ($myFeedbacks as $feedback): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <div class="feedback-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star" style="color: <?= $i <= $feedback['rating'] ? '#fbbf24' : '#e2e8f0' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="status-badge status-<?= $feedback['status'] ?>">
                                        <?= ucfirst($feedback['status']) ?>
                                    </span>
                                </div>
                                <div class="feedback-subject"><?= htmlspecialchars($feedback['subject']) ?></div>
                                <div class="feedback-message"><?= htmlspecialchars($feedback['message']) ?></div>
                                <div class="feedback-meta">
                                    <span><i class="far fa-calendar"></i><?= $feedback['formatted_date'] ?></span>
                                    <?php if ($feedback['hname']): ?>
                                        <span><i class="fas fa-hospital"></i><?= htmlspecialchars($feedback['hname']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-feedback">
                            <i class="far fa-comment-dots"></i>
                            <p>No feedback submitted yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Approved Feedbacks from Other Parents -->
            <div class="approved-section">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-star"></i>
                        Community Feedbacks - Approved Reviews
                    </h2>
                </div>

                <div class="approved-grid">
                    <?php if ($approvedFeedbacks && count($approvedFeedbacks) > 0): ?>
                        <?php foreach ($approvedFeedbacks as $feedback): ?>
                            <div class="approved-card">
                                <div class="parent-name">
                                    <i class="fas fa-user-circle"></i>
                                    <?= htmlspecialchars($feedback['pname']) ?>
                                    <span class="verified-badge">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </span>
                                </div>
                                <div class="feedback-header">
                                    <div class="feedback-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star" style="color: <?= $i <= $feedback['rating'] ? '#fbbf24' : '#e2e8f0' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="feedback-subject"><?= htmlspecialchars($feedback['subject']) ?></div>
                                <div class="feedback-message"><?= htmlspecialchars($feedback['message']) ?></div>
                                <div class="feedback-meta">
                                    <span><i class="far fa-calendar"></i><?= $feedback['formatted_date'] ?></span>
                                    <?php if ($feedback['hname']): ?>
                                        <span><i class="fas fa-hospital"></i><?= htmlspecialchars($feedback['hname']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-feedback">
                            <i class="far fa-comment-dots"></i>
                            <p>No approved feedbacks available yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
