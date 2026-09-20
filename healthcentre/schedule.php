<?php
require('../config/autoload.php');
$dao = new DataAccess();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['healthcentre'])) {
    header("Location: login.php");
    exit;
}

// Get health centre data from session
$healthcentre = $_SESSION['healthcentre'];
$hid = $healthcentre['hid'] ?? 0;
$centreName = $healthcentre['hname'] ?? 'Health Centre';

// Fetch schedules for this healthcentre
$schedules = [];
if ($hid > 0) {
    $schedules = $dao->getData('*', 'schedule', "hid = $hid");
}
?>

<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedules</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0px;
            background: #f5f7fa;
        }

        h1 {
            text-align: center;
            color: #1e3a8a;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .schedule-container {
            max-width: 1000px;
            margin: 30px auto; /* Added spacing below header */
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .filter-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
            gap: 10px;
        }

        .filter-container input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .filter-container button {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }

        .filter-container button:hover {
            background: #162d6e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #1e3a8a;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .view-btn {
            padding: 6px 12px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .view-btn:hover {
            background: #2563eb;
        }

        .no-data {
            text-align: center;
            color: #555;
            font-size: 16px;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="schedule-container">
    <h1><i class="fa-solid fa-calendar-days"></i> My Vaccine Schedules</h1>
    
    <div style="background: #e8eaf6; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; color: #1a237e;">
        <i class="fa-solid fa-info-circle"></i> <strong>Note:</strong> Schedules are managed by the admin. Contact admin to add or modify schedules.
    </div>

    <!-- Date Filter -->
    <div class="filter-container">
        <input type="date" id="filterDate">
        <button onclick="filterByDate()">Filter</button>
    </div>

    <?php if (!empty($schedules)): ?>
        <table id="scheduleTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vaccine</th>
                    <th>Date</th>
                    <th>Units</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 1;
                foreach ($schedules as $schedule): 
                    // Get vaccine name
                    $vaccine = $dao->getData('vname', 'vaccine', "vid = {$schedule['vid']}");
                    $vaccineName = $vaccine[0]['vname'] ?? 'Unknown';
                ?>
                    <tr>
                        <td><?= $count++; ?></td>
                        <td><?= htmlspecialchars($vaccineName); ?></td>
                        <td><?= htmlspecialchars($schedule['date']); ?></td>
                        <td><?= htmlspecialchars($schedule['units']); ?></td>
                        <td>
                            <a href="viewbookings.php?id=<?= $schedule['sid']; ?>">
                                <button class="view-btn"><i class="fa-solid fa-eye"></i> View Bookings</button>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <i class="fa-solid fa-circle-exclamation"></i> No schedules available for your health centre.
        </div>
    <?php endif; ?>
</div>

<script>
function filterByDate() {
    const filterDate = document.getElementById('filterDate').value;
    const rows = document.querySelectorAll("#scheduleTable tbody tr");
    let matchFound = false;

    rows.forEach(row => {
        const rowDate = row.cells[2].innerText;
        if (filterDate === "" || rowDate === filterDate) {
            row.style.display = "";
            if (filterDate !== "") matchFound = true;
        } else {
            row.style.display = "none";
        }
    });

    // Remove old "no results" message if exists
    const oldMessage = document.getElementById('noResultsMessage');
    if (oldMessage) oldMessage.remove();

    // Show message if no match found
    if (filterDate !== "" && !matchFound) {
        const message = document.createElement('div');
        message.id = 'noResultsMessage';
        message.style.textAlign = 'center';
        message.style.color = '#555';
        message.style.fontSize = '16px';
        message.style.padding = '15px';
        message.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> No schedules found for the selected date.`;
        document.querySelector('.schedule-container').appendChild(message);
    }
}
</script>

</body>
</html>
