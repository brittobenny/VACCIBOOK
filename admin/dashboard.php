<?php
require('../config/autoload.php');

$dao = new DataAccess();

$conn = mysqli_connect("localhost", "root", "", "vaccibook");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get basic counts
$parent_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM parent"))['total'];
$healthcentre_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM healthcentre WHERE status=1"))['total'];
$vaccine_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM vaccine"))['total'];
$schedule_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM schedule WHERE date >= CURDATE()"))['total'];

// Get additional statistics
$child_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM child"))['total'];
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM book"))['total'];
$pending_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM book WHERE status='pending'"))['total'];
$completed_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM book WHERE status='completed'"))['total'];

// Get monthly data for chart (last 6 months)
$monthlyData = [];
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('M Y', strtotime("-$i months"));
    $months[] = $monthName;
    
    // Count bookings made in this month
    $bookingQuery = "SELECT COUNT(*) AS count FROM book WHERE DATE_FORMAT(bookdate, '%Y-%m') = '$month'";
    $bookingResult = mysqli_fetch_assoc(mysqli_query($conn, $bookingQuery));
    $monthlyData['bookings'][] = $bookingResult['count'] ?? 0;
    
    // Count schedules in this month
    $scheduleQuery = "SELECT COUNT(*) AS count FROM schedule WHERE DATE_FORMAT(date, '%Y-%m') = '$month'";
    $scheduleResult = mysqli_fetch_assoc(mysqli_query($conn, $scheduleQuery));
    $monthlyData['schedules'][] = $scheduleResult['count'] ?? 0;
    
    // Count children registered in this month (assuming child table has a date column)
    $childQuery = "SELECT COUNT(*) AS count FROM child WHERE cid IN (
        SELECT cid FROM book WHERE DATE_FORMAT(bookdate, '%Y-%m') = '$month'
    )";
    $childResult = mysqli_fetch_assoc(mysqli_query($conn, $childQuery));
    $monthlyData['children'][] = $childResult['count'] ?? 0;
}

?>

<?php include('header.php'); ?>

<style>
/* Modern Dashboard Styles */
.dashboard-container {
    padding: 20px;
    background: #f8f9fa;
}

.page-header {
    margin-bottom: 30px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a237e;
    margin-bottom: 5px;
}

.page-subtitle {
    color: #6c757d;
    font-size: 14px;
}

/* Enhanced Card Styles */
.stat-card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stat-card .card-body {
    padding: 25px;
}

.stat-card .card-title {
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 15px;
    opacity: 0.9;
}

.stat-card h2 {
    font-size: 36px;
    font-weight: 700;
    margin: 0;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(30%, -30%);
}

/* Chart Card */
.chart-card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.chart-card .card-body {
    padding: 30px;
}

.chart-card .card-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a237e;
    margin-bottom: 20px;
}

/* Table Styles */
.table-card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.table-card .card-body {
    padding: 30px;
}

.table-card .card-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a237e;
    margin-bottom: 20px;
}

.table-hover tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    color: #495057;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
    padding: 15px;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
}

/* Badge Styles */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important;
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%) !important;
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%) !important;
}

.bg-gradient-dark {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
}

.bg-gradient-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* Animation */
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

.animate-fade-in {
    animation: fadeInUp 0.6s ease-out;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-card h2 {
        font-size: 28px;
    }
    .page-title {
        font-size: 24px;
    }
}
</style>

<div class="dashboard-container">
    <div class="page-header animate-fade-in">
        <h1 class="page-title">📊 Admin Dashboard</h1>
        <p class="page-subtitle">Welcome back! Here's what's happening with your vaccine booking system.</p>
    </div>

<div class="row animate-fade-in" style="margin-bottom: 20px;">
  <!-- First Row -->
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card stat-card bg-gradient-primary text-white">
      <div class="card-body">
        <h4 class="card-title">👥 Registered Parents</h4>
        <h2 class="text-center"><?php echo $parent_count; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3 grid-margin stretch-card">
    <div class="card stat-card bg-gradient-success text-white">
      <div class="card-body">
        <h4 class="card-title">👶 Registered Children</h4>
        <h2 class="text-center"><?php echo $child_count; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3 grid-margin stretch-card">
    <div class="card stat-card bg-gradient-info text-white">
      <div class="card-body">
        <h4 class="card-title">🏥 Health Centres</h4>
        <h2 class="text-center"><?php echo $healthcentre_count; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3 grid-margin stretch-card">
    <div class="card stat-card bg-gradient-warning text-white">
      <div class="card-body">
        <h4 class="card-title">💉 Available Vaccines</h4>
        <h2 class="text-center"><?php echo $vaccine_count; ?></h2>
      </div>
    </div>
  </div>
</div>

<!-- Second Row -->
<div class="row animate-fade-in" style="animation-delay: 0.2s; margin-bottom: 20px;">
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card stat-card bg-gradient-secondary text-white">
      <div class="card-body">
        <h4 class="card-title">📋 Total Bookings</h4>
        <h2 class="text-center"><?php echo $total_bookings; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3 grid-margin stretch-card">
    <div class="card stat-card bg-gradient-danger text-white">
      <div class="card-body">
        <h4 class="card-title">⏳ Pending Bookings</h4>
        <h2 class="text-center"><?php echo $pending_bookings; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3 grid-margin stretch-card">
    <div class="card stat-card bg-gradient-dark text-white">
      <div class="card-body">
        <h4 class="card-title">✅ Completed Bookings</h4>
        <h2 class="text-center"><?php echo $completed_bookings; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3 grid-margin stretch-card">
    <div class="card stat-card bg-gradient-purple text-white">
      <div class="card-body">
        <h4 class="card-title">📅 Upcoming Schedules</h4>
        <h2 class="text-center"><?php echo $schedule_count; ?></h2>
      </div>
    </div>
  </div>
</div>

<!-- Chart Section -->
<div class="row animate-fade-in" style="animation-delay: 0.4s; margin-bottom: 25px;">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card chart-card">
      <div class="card-body">
        <h4 class="card-title">📈 Monthly Statistics (Last 6 Months)</h4>
        <canvas id="statsChart" height="80"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Recent Bookings Table -->
<div class="row animate-fade-in" style="animation-delay: 0.6s; margin-bottom: 25px;">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card table-card">
      <div class="card-body">
        <h4 class="card-title">📋 Recent Bookings</h4>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Parent Name</th>
                <th>Child Name</th>
                <th>Vaccine</th>
                <th>Health Centre</th>
                <th>Schedule Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $recentBookings = mysqli_query($conn, "
                SELECT b.bid, p.pname as parent_name, c.cname as child_name, 
                       v.vname as vaccine_name, h.hname as health_centre,
                       s.date as schedule_date, b.status
                FROM book b
                JOIN parent p ON b.pid = p.pid
                JOIN child c ON b.cid = c.cid
                JOIN schedule s ON b.sid = s.sid
                JOIN vaccine v ON s.vid = v.vid
                JOIN healthcentre h ON s.hid = h.hid
                ORDER BY b.bookdate DESC
                LIMIT 10
              ");
              
              while ($row = mysqli_fetch_assoc($recentBookings)) {
                $statusClass = '';
                switch($row['status']) {
                  case 'pending': $statusClass = 'badge-warning'; break;
                  case 'completed': $statusClass = 'badge-success'; break;
                  case 'cancelled': $statusClass = 'badge-danger'; break;
                  default: $statusClass = 'badge-secondary';
                }
              ?>
                <tr>
                  <td><?php echo $row['bid']; ?></td>
                  <td><?php echo htmlspecialchars($row['parent_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['child_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['vaccine_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['health_centre']); ?></td>
                  <td><?php echo date('d M Y', strtotime($row['schedule_date'])); ?></td>
                  <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('statsChart').getContext('2d');

  const statsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($months); ?>,
      datasets: [
        {
          label: 'Bookings Made',
          data: <?php echo json_encode($monthlyData['bookings']); ?>,
          backgroundColor: 'rgba(255, 99, 132, 0.7)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 2
        },
        {
          label: 'Schedules Created',
          data: <?php echo json_encode($monthlyData['schedules']); ?>,
          backgroundColor: 'rgba(255, 206, 86, 0.7)',
          borderColor: 'rgba(255, 206, 86, 1)',
          borderWidth: 2
        },
        {
          label: 'Children with Bookings',
          data: <?php echo json_encode($monthlyData['children']); ?>,
          backgroundColor: 'rgba(75, 192, 192, 0.7)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 2
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      plugins: {
        legend: {
          display: true,
          position: 'top'
        },
        tooltip: {
          mode: 'index',
          intersect: false
        }
      }
    }
  });
</script>

</div> <!-- End dashboard-container -->
