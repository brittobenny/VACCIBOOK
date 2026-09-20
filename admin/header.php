<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Vaccibook Admin</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" />
  </head>
  <body>

  <!-- plugins:js -->
<script src="assets/vendors/js/vendor.bundle.base.js"></script>

<!-- inject:js -->
<script src="assets/js/off-canvas.js"></script>
<script src="assets/js/hoverable-collapse.js"></script>
<script src="assets/js/misc.js"></script>
<!-- endinject -->

    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 d-flex flex-row" style="position: sticky; top: 0; z-index: 1030;">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
<a class="navbar-brand brand-logo" href="index.php">
  <img src="assets/images/logo.png" alt="logo" style="height: 120px;width: 200px;" />
</a>          <a class="navbar-brand brand-logo-mini" href="index.html"><img src="assets/images/logomini.png"style="height: 50px;width: 50px;" alt="logo" /></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-stretch">
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
</button>
          <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item">
              <a class="nav-link" href="logout.php" style="background: #e53935; color: white; padding: 6px 14px; border-radius: 5px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.3s ease; font-size: 13px; line-height: 1;">
                <i class="mdi mdi-logout" style="font-size: 14px;"></i>
                Logout
              </a>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
       <!-- Sidebar Navigation Updated -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <!-- Profile Info -->
    <li class="nav-item nav-profile">
    <div class="nav-link">
        <div class="nav-profile-text d-flex flex-row align-items-center justify-content-center">
            <span class="font-weight-bold mb-2" style="font-size: 1.25rem;">ADMIN PANEL</span>
            <i class="mdi mdi-bookmark-check text-success nav-profile-badge" style="font-size: 1.5rem; margin-left: 10px;"></i>
        </div>
    </div>
</li>

    <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
        <span class="menu-title">Dashboard</span>
        <i class="mdi mdi-home menu-icon"></i>
      </a>
    </li>

    <!-- Manage Vaccine -->
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#vaccine-menu" aria-expanded="false" aria-controls="vaccine-menu">
        <span class="menu-title">Manage Vaccine</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-needle menu-icon"></i>
      </a>
      <div class="collapse" id="vaccine-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="vaccine.php">Add Vaccine</a></li>
          <li class="nav-item"> <a class="nav-link" href="viewvaccine.php">View Vaccines</a></li>
        </ul>
      </div>
    </li>

    <!-- Manage Health Centre -->
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#healthcentre-menu" aria-expanded="false" aria-controls="healthcentre-menu">
        <span class="menu-title">Manage Health Centre</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-hospital-building menu-icon"></i>
      </a>
      <div class="collapse" id="healthcentre-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="healthcentre.php">Add Health Centre</a></li>
          <li class="nav-item"> <a class="nav-link" href="viewhealthcentre.php">View Health Centres</a></li>
        </ul>
      </div>
    </li>

    <!-- Manage Schedule -->
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#schedule-menu" aria-expanded="false" aria-controls="schedule-menu">
        <span class="menu-title">Manage Schedule</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-calendar-clock menu-icon"></i>
      </a>
      <div class="collapse" id="schedule-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="schedule.php">Add Schedule</a></li>
          <li class="nav-item"> <a class="nav-link" href="viewschedule.php">View Schedules</a></li>
        </ul>
      </div>
    </li>

    <!-- Districts -->
    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#district-menu" aria-expanded="false" aria-controls="district-menu">
        <span class="menu-title">Districts</span>
        <i class="menu-arrow"></i>
        <i class="mdi mdi-map-marker menu-icon"></i>
      </a>
      <div class="collapse" id="district-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="district.php">Add District</a></li>
          <li class="nav-item"> <a class="nav-link" href="viewdistrict.php">View Districts</a></li>
        </ul>
      </div>
    </li>
  

<!-- Feedbacks -->
<li class="nav-item">
  <a class="nav-link" href="viewfeedback.php">
    <span class="menu-title">Feedbacks</span>
    <i class="mdi mdi-message-text-outline menu-icon"></i>
  </a>
</li>

<!-- View Users -->
<li class="nav-item">
  <a class="nav-link" href="viewusers.php">
    <span class="menu-title">View Users</span>
    <i class="mdi mdi-account-multiple menu-icon"></i>
  </a>
</li>

<!-- Reports -->
<li class="nav-item">
  <a class="nav-link" href="reptbookingdate.php">
    <span class="menu-title">Reports</span>
    <i class="mdi mdi-chart-bar menu-icon"></i>
  </a>
</li>
</ul>

</nav>

        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row" id="proBanner">
              <div class="col-12">
              
          <!-- partial:partials/_footer.html -->
         
  <?php ob_end_flush(); ?>
