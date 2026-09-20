<?php
require('../config/autoload.php');
$dao = new DataAccess();
include('header.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$healthCentre = $_SESSION['healthcentre'] ?? null;
$hid = $healthCentre['hid'] ?? 0;

// Get all feedbacks for this health centre
$feedbacks = $dao->query("SELECT f.*, p.pname, p.pemail as email, p.mob as phone,
                          DATE_FORMAT(f.feedback_date, '%d %b %Y at %h:%i %p') as formatted_date
                          FROM feedback f
                          JOIN parent p ON f.pid = p.pid
                          WHERE f.hid = $hid
                          ORDER BY f.feedback_date DESC");

// Ensure feedbacks is an array
if (!is_array($feedbacks)) {
    $feedbacks = [];
}

// Calculate statistics
$totalFeedbacks = count($feedbacks);
$averageRating = 0;
$ratingDistribution = ['5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];

if ($feedbacks) {
    $totalRating = 0;
    foreach ($feedbacks as $feedback) {
        $totalRating += $feedback['rating'];
        $ratingDistribution[$feedback['rating']]++;
    }
    $averageRating = $totalFeedbacks > 0 ? round($totalRating / $totalFeedbacks, 1) : 0;
}

// Get recent feedbacks (last 5)
$recentFeedbacks = array_slice($feedbacks, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedbacks - Vaccibook</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

        .feedbacks-container {
            max-width: 1400px;
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

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .stat-icon.yellow {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #15803d;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
        }

        /* Rating Distribution */
        .rating-distribution {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease 0.2s backwards;
        }

        .rating-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .rating-bars {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .rating-bar-row {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .rating-stars {
            display: flex;
            gap: 3px;
            min-width: 100px;
        }

        .rating-stars i {
            color: #fbbf24;
            font-size: 14px;
        }

        .bar-container {
            flex: 1;
            height: 24px;
            background: #f1f5f9;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
            border-radius: 12px;
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 11px;
            font-weight: 600;
        }

        .rating-count {
            min-width: 40px;
            text-align: right;
            font-weight: 500;
            color: #64748b;
        }

        /* Feedbacks List */
        .feedbacks-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease 0.3s backwards;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #3b82f6;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #64748b;
        }

        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            color: white;
            border-color: #3b82f6;
        }

        .feedback-card {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s ease;
        }

        .feedback-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .parent-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .parent-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 20px;
        }

        .parent-details h4 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 3px;
        }

        .parent-contact {
            font-size: 12px;
            color: #94a3b8;
            display: flex;
            gap: 12px;
        }

        .parent-contact i {
            margin-right: 4px;
        }

        .feedback-rating {
            display: flex;
            gap: 4px;
        }

        .feedback-rating i {
            color: #fbbf24;
            font-size: 16px;
        }

        .feedback-subject {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .feedback-message {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 15px;
        }

        .feedback-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .feedback-date {
            font-size: 13px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
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

        .no-feedbacks {
            text-align: center;
            padding: 80px 20px;
            color: #94a3b8;
        }

        .no-feedbacks i {
            font-size: 80px;
            margin-bottom: 25px;
            color: #cbd5e1;
        }

        .no-feedbacks h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #64748b;
        }

        .no-feedbacks p {
            font-size: 15px;
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

        .search-box {
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 15px center;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body>
    <div class="feedbacks-container">
        <div class="page-header">
            <h1 class="page-title">Parent Feedbacks</h1>
            <p class="page-subtitle">Monitor and respond to parent feedback about your services</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= $totalFeedbacks ?></div>
                        <div class="stat-label">Total Feedbacks</div>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= $averageRating ?> <i class="fas fa-star" style="font-size: 24px; color: #fbbf24;"></i></div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                    <div class="stat-icon yellow">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= $ratingDistribution['5'] + $ratingDistribution['4'] ?></div>
                        <div class="stat-label">Positive Reviews</div>
                    </div>
                    <div class="stat-icon green">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating Distribution -->
        <div class="rating-distribution">
            <h3 class="rating-title">
                <i class="fas fa-chart-bar"></i>
                Rating Distribution
            </h3>
            <div class="rating-bars">
                <?php for ($star = 5; $star >= 1; $star--): ?>
                    <?php 
                    $count = $ratingDistribution[$star];
                    $percentage = $totalFeedbacks > 0 ? ($count / $totalFeedbacks) * 100 : 0;
                    ?>
                    <div class="rating-bar-row">
                        <div class="rating-stars">
                            <?php for ($i = 0; $i < $star; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?= $percentage ?>%">
                                <?php if ($percentage > 10): ?>
                                    <?= round($percentage, 1) ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="rating-count"><?= $count ?></div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Feedbacks List -->
        <div class="feedbacks-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-inbox"></i>
                    All Feedbacks
                </h2>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterFeedbacks('all')">All</button>
                    <button class="filter-btn" onclick="filterFeedbacks('pending')">Pending</button>
                    <button class="filter-btn" onclick="filterFeedbacks('reviewed')">Reviewed</button>
                    <button class="filter-btn" onclick="filterFeedbacks('resolved')">Resolved</button>
                </div>
            </div>

            <div class="search-box">
                <input type="text" class="search-input" id="searchInput" placeholder="Search feedbacks by name, subject, or message...">
            </div>

            <div id="feedbacksList">
                <?php if ($feedbacks && count($feedbacks) > 0): ?>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div class="feedback-card" data-status="<?= $feedback['status'] ?>">
                            <div class="feedback-header">
                                <div class="parent-info">
                                    <div class="parent-avatar">
                                        <?= strtoupper(substr($feedback['pname'], 0, 1)) ?>
                                    </div>
                                    <div class="parent-details">
                                        <h4><?= htmlspecialchars($feedback['pname']) ?></h4>
                                        <div class="parent-contact">
                                            <span><i class="fas fa-envelope"></i><?= htmlspecialchars($feedback['email']) ?></span>
                                            <span><i class="fas fa-phone"></i><?= htmlspecialchars($feedback['phone']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="feedback-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star" style="color: <?= $i <= $feedback['rating'] ? '#fbbf24' : '#e2e8f0' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="feedback-subject"><?= htmlspecialchars($feedback['subject']) ?></div>
                            <div class="feedback-message"><?= htmlspecialchars($feedback['message']) ?></div>
                            
                            <div class="feedback-footer">
                                <div class="feedback-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= $feedback['formatted_date'] ?>
                                </div>
                                <span class="status-badge status-<?= $feedback['status'] ?>">
                                    <?= ucfirst($feedback['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-feedbacks">
                        <i class="far fa-comment-dots"></i>
                        <h3>No Feedbacks Yet</h3>
                        <p>You haven't received any feedback from parents yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Filter feedbacks by status
        function filterFeedbacks(status) {
            const cards = document.querySelectorAll('.feedback-card');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter cards
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Search feedbacks
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.feedback-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Animate bars on load
        window.addEventListener('load', function() {
            const bars = document.querySelectorAll('.bar-fill');
            bars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>
