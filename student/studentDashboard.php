<?php
session_start();
if($_SESSION["access"] !=null && $_SESSION["name"]!=null && $_SESSION["id"]!=null && $_SESSION["access"]==0) { 
  
  $username = $_SESSION["name"];
  $id = $_SESSION["id"];
  $access = $_SESSION["access"];

  $servername = "localhost";
  $username_db = "root";
  $password_db = "";
  $dbname = "asimos";
  $row = "";

  $conn = new mysqli($servername, $username_db, $password_db, $dbname);

  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

// 
  $sql = "SELECT * FROM student_info WHERE name='$username' AND id='$id'";
  $result = $conn->query($sql);

//   if ($result->num_rows > 0) {
//       $row = $result->fetch_assoc();
//   }

  if ($result === false) {
      // Check for errors in query execution
      die("Error: " . $conn->error);
  }

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      // Process $row data
  } else {
      // No matching rows found
      echo "No results found.";
  }

// 
  $notificationSql = "SELECT * FROM notification WHERE id='$id'";
  $notification = $conn->query($notificationSql);

  if($notification->num_rows > 0) {
    while ($nrow = $notification->fetch_assoc()) {
      $ntnrow[] = $nrow['notification'];
      $ntnFrm[] = $nrow['sentBy'];
    }
  } else {
    $ntnrow[] = "No Notification";
  }

  $notification = array_combine($ntnFrm , $ntnrow);


  
//
  $date = date("Y-m-d");
  $attendanceTodaySql = "SELECT status FROM attendance WHERE name='$username' AND id='$id' AND date= '$date'";
  $attendanceTodayResult1 = $conn->query($attendanceTodaySql);
  $attendanceTodayResult = $attendanceTodayResult1->fetch_assoc();

  if ($attendanceTodayResult < 0 || $attendanceTodayResult==0) {
    $attendanceTodayResult['status'] = "1";
  }
  

// 
  $attendanceCountSql = "SELECT count(*) as count FROM attendance WHERE name='$username' AND id='$id' AND status='0'";
  $resultSql1 = $conn->query($attendanceCountSql);
  $attendanceCount = $resultSql1->fetch_assoc();



// Term 1 Marks
  echo $id;
  $marks = "SELECT * FROM marks_new WHERE id='$id' and testName ='Term 1'";
  $marks_result1 = $conn->query($marks);

  if($marks_result1->num_rows > 0) {
      while ($mks = $marks_result1->fetch_assoc()) {
        $term1_marks[0] = $mks['english'];
        $term1_marks[1] = $mks['tamil'];
        $term1_marks[2] = $mks['maths'];
        $term1_marks[3] = $mks['science'];
        $term1_marks[4] = $mks['social'];
      }
    } else {
      $term1_marks[0] = "No Marks Data Available.";
    }

// Term 2 Marks
  echo $id;
  $marks2 = "SELECT * FROM marks_new WHERE id='$id' and testName ='Term 2'";
  $marks_result2 = $conn->query($marks2);

  if($marks_result2->num_rows > 0) {
      while ($mks2 = $marks_result2->fetch_assoc()) {
        $term2_marks[0] = $mks2['english'];
        $term2_marks[1] = $mks2['tamil'];
        $term2_marks[2] = $mks2['maths'];
        $term2_marks[3] = $mks2['science'];
        $term2_marks[4] = $mks2['social'];
      }
    } else {
      $term2_marks[0] = "No Marks Data Available.";
    }

// Term 3 Marks
  echo $id;
  $marks3 = "SELECT * FROM marks_new WHERE id='$id' and testName ='Term 3'";
  $marks_result3 = $conn->query($marks3);

  if($marks_result3->num_rows > 0) {
      while ($mks3 = $marks_result3->fetch_assoc()) {
        $term3_marks[0] = $mks3['english'];
        $term3_marks[1] = $mks3['tamil'];
        $term3_marks[2] = $mks3['maths'];
        $term3_marks[3] = $mks3['science'];
        $term3_marks[4] = $mks3['social'];
      }
    } else {
      $term3_marks[0] = "No Marks Data Available.";
    }

// Assessment
    echo $id;
    $assessment = "SELECT * FROM assessments WHERE id='$id'";
    $assessment1 = $conn->query($assessment);

    if($assessment1->num_rows > 0) {
      while ($ass1 = $assessment1->fetch_assoc()) {
        $assess0[] = $ass1['date'];
        $assess1[] = $ass1['test'];
        $assess2[] = $ass1['subjectName'];
        $assess3[] = $ass1['marks'];
        $assess4[] = $ass1['Result'];
      }
    } else {
      $assess[0] = "No Marks Data Available.";
    }


  $conn->close();

  
  
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Asimos - Dashboard</title>
  <meta content="Asimos Dashboard" name="description">
  <meta content="Asimos Dashboard" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="assets/css/style.css" rel="stylesheet">

</head>

<body>

  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <!-- <span class="d-none d-lg-block"></span> -->
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number"><?php echo count($notification) ?></span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              You have <?php echo count($notification) ?> new notifications
              <!-- <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a> -->
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <?php  
              foreach ($notification as $key => $value) {?>
                <li class="notification-item">
                  <i class="bi bi-info-circle text-primary"></i>
                    <div>
                      <h4><?php echo $key; ?></h4>
                      <p>
                        <?php
                          echo $value; "<br>";
                        ?>
                      </p>
                      <p>30 min. ago</p>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                    </div>
                  <?php
              } 
              ?>
          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-text"></i>
            <span class="badge bg-success badge-number">3</span>
          </a><!-- End Messages Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
            <li class="dropdown-header">
              You have 3 new messages
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-1.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Maria Hudson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>4 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-2.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Anna Nelson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>6 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-3.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>David Muldon</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>8 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="dropdown-footer">
              <a href="#">Show all messages</a>
            </li>

          </ul><!-- End Messages Dropdown Items -->

        </li><!-- End Messages Nav -->

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
           <img src= <?php echo $row['locOfProfilePic']; ?> alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $username; ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo $username; ?>  </h6>
              <span>student</span><br>
              <span><?php echo $row['standard'];echo " - "; echo $row['section']; ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="faq.html">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="./backoffice/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#dash-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <br>
       <li class="nav-item">
         <a class="nav-link collapsed" data-bs-target="#dash-nav" data-bs-toggle="collapse" href="#">
           <i class="bx bx-money"></i>
           <span>Fee Status</span>
         </a>
       </li>
       <br>
       <li class="nav-item">
         <a class="nav-link collapsed" data-bs-target="#dash-nav" data-bs-toggle="collapse" href="#">
            <i class="bi bi-layout-text-window-reverse"></i>
              <span>Time Table</span>
         </a>
       </li>
       <br>
       <li class="nav-item">
         <a class="nav-link collapsed" data-bs-target="#dash-nav" data-bs-toggle="collapse" href="#">
           <i class="bi bi-journal-text"></i>
           <span>Report Card</span>
         </a>
       </li>
       <br>
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#dash-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i>
          <span>Test Results</span>
        </a>
      </li>
      <br>
        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#dash-nav" data-bs-toggle="collapse" href="#">
            <i class="bi bi-card-list"></i>
            <span>Home Work</span>
          </a>
        </li>
        <br>
        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#dash-nav" data-bs-toggle="collapse" href="#">
            <i class="bx bx-cog"></i>
            <span>Settings</span>
          </a>
        </li>

      <!-- End Dashboard Nav -->

<!--
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Components</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="components-alerts.html">
              <i class="bi bi-circle"></i><span>Alerts</span>
            </a>
          </li>
          <li>
            <a href="components-accordion.html">
              <i class="bi bi-circle"></i><span>Accordion</span>
            </a>
          </li>
          <li>
            <a href="components-badges.html">
              <i class="bi bi-circle"></i><span>Badges</span>
            </a>
          </li>
          <li>
            <a href="components-breadcrumbs.html">
              <i class="bi bi-circle"></i><span>Breadcrumbs</span>
            </a>
          </li>
          <li>
            <a href="components-buttons.html">
              <i class="bi bi-circle"></i><span>Buttons</span>
            </a>
          </li>
          <li>
            <a href="components-cards.html">
              <i class="bi bi-circle"></i><span>Cards</span>
            </a>
          </li>
          <li>
            <a href="components-carousel.html">
              <i class="bi bi-circle"></i><span>Carousel</span>
            </a>
          </li>
          <li>
            <a href="components-list-group.html">
              <i class="bi bi-circle"></i><span>List group</span>
            </a>
          </li>
          <li>
            <a href="components-modal.html">
              <i class="bi bi-circle"></i><span>Modal</span>
            </a>
          </li>
          <li>
            <a href="components-tabs.html">
              <i class="bi bi-circle"></i><span>Tabs</span>
            </a>
          </li>
          <li>
            <a href="components-pagination.html">
              <i class="bi bi-circle"></i><span>Pagination</span>
            </a>
          </li>
          <li>
            <a href="components-progress.html">
              <i class="bi bi-circle"></i><span>Progress</span>
            </a>
          </li>
          <li>
            <a href="components-spinners.html">
              <i class="bi bi-circle"></i><span>Spinners</span>
            </a>
          </li>
          <li>
            <a href="components-tooltips.html">
              <i class="bi bi-circle"></i><span>Tooltips</span>
            </a>
          </li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-journal-text"></i><span>Forms</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="forms-elements.html">
              <i class="bi bi-circle"></i><span>Form Elements</span>
            </a>
          </li>
          <li>
            <a href="forms-layouts.html">
              <i class="bi bi-circle"></i><span>Form Layouts</span>
            </a>
          </li>
          <li>
            <a href="forms-editors.html">
              <i class="bi bi-circle"></i><span>Form Editors</span>
            </a>
          </li>
          <li>
            <a href="forms-validation.html">
              <i class="bi bi-circle"></i><span>Form Validation</span>
            </a>
          </li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-layout-text-window-reverse"></i><span>Tables</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="tables-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="tables-general.html">
              <i class="bi bi-circle"></i><span>General Tables</span>
            </a>
          </li>
          <li>
            <a href="tables-data.html">
              <i class="bi bi-circle"></i><span>Data Tables</span>
            </a>
          </li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i><span>Charts</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="charts-chartjs.html">
              <i class="bi bi-circle"></i><span>Chart.js</span>
            </a>
          </li>
          <li>
            <a href="charts-apexcharts.html">
              <i class="bi bi-circle"></i><span>ApexCharts</span>
            </a>
          </li>
          <li>
            <a href="charts-echarts.html">
              <i class="bi bi-circle"></i><span>ECharts</span>
            </a>
          </li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-gem"></i><span>Icons</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="icons-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="icons-bootstrap.html">
              <i class="bi bi-circle"></i><span>Bootstrap Icons</span>
            </a>
          </li>
          <li>
            <a href="icons-remix.html">
              <i class="bi bi-circle"></i><span>Remix Icons</span>
            </a>
          </li>
          <li>
            <a href="icons-boxicons.html">
              <i class="bi bi-circle"></i><span>Boxicons</span>
            </a>
          </li>
        </ul>
      </li>

      <li class="nav-heading">Pages</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="users-profile.html">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="faq.html">
          <i class="bi bi-question-circle"></i>
          <span>F.A.Q</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="contact.html">
          <i class="bi bi-envelope"></i>
          <span>Contact</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="register.html">
          <i class="bi bi-card-list"></i>
          <span>Register</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="login.html">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Login</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="error-404.html">
          <i class="bi bi-dash-circle"></i>
          <span>Error 404</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="blank.html">
          <i class="bi bi-file-earmark"></i>
          <span>Blank</span>
        </a>
      </li>
-->
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row">

            <!-- Sales Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Attendance <span>| Today <?php echo $attendanceTodayResult['status']; ?></span></h5>

                  <div class="d-flex align-items-center">

                    <?php 
                      if($attendanceTodayResult['status']!=0) {  ?>
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background:#6bff7d;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6>Present</h6>
                      <span class="text-success small pt-1 fw-bold"><?php echo date("Y-m-d"); ?></span> <span class="text-muted small pt-2 ps-1">Have a great day.</span>



                    <?php } else { ?>
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background:#ff283de0;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6>Absent</h6>
                      <span class="text-success small pt-1 fw-bold"><?php echo date("Y-m-d"); ?></span> <span class="text-muted small pt-2 ps-1"><br> </span>

                      <?php }
                    ?>
                      
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Sales Card -->

            <!-- Revenue Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">

              

                <div class="card-body">
                  <h5 class="card-title">Number of days Absent <span>| Year</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $attendanceCount['count']; echo "/"; echo "180";?></h6>
                      <span class="text-success small pt-1 fw-bold"><?php echo number_format(100-($attendanceCount['count']/180)*100, 2) . " % Attendance";?></span> <span class="text-muted small pt-2 ps-1"></span>

                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Revenue Card -->

            <!-- Customers Card -->
            <div class="col-xxl-4 col-xl-12">

              <div class="card info-card customers-card">

                <!-- <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div> -->

                <div class="card-body">
                  <h5 class="card-title">Customers <span>| This Year</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6>1244</h6>
                      <span class="text-danger small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">decrease</span>

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->

            <!-- Reports -->
            <div class="col-12">
              <div class="card">
              

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>
                    
                    <li><a class="dropdown-item" href="#"><?php echo $username; ?>'s Score</a></li>
                    <li><a class="dropdown-item" href="#">Class Average Score</a></li>
                    <li><a class="dropdown-item" href="#">Class Topper Score</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Marks <span></span></h5>
                  
                  <!-- Line Chart -->
                  <div id="reportsChart"></div>

                  <?php

                    if ($marks_result1->num_rows > 0) {
                      while ($row1 = $marks_result1->fetch_assoc()) {
                          // Print or use the data as needed
                          
                          
                          $term1_marks = array(
                            $row1['subjectName'] => $row1['marksObtained']
                          );
                          
                          // foreach ($term1_marks as $key => $value) {
                          //     echo "Key: $key, Value: $value<br>";
                          // }

                          // echo $row['subjectName'];
                          // echo strval($term1_marks['English']); 
                          // echo "<br>";
                      }
                  } else {
                      echo "No rows found for id: $id";
                  }
                ?>


                <script>
                  document.addEventListener("DOMContentLoaded", () => {
                    // PHP variables containing the data
                    <?php


                    // Example PHP data arrays
                    $englishData = json_encode([$term1_marks[0] ,$term1_marks[0], $term2_marks[0], $term3_marks[0], round(($term1_marks[0] + $term2_marks[0] + $term3_marks[0])/3)]);
                    $tamilData = json_encode([$term1_marks[1] ,$term1_marks[1], $term2_marks[1], $term3_marks[1], round(($term1_marks[1] + $term2_marks[1] + $term3_marks[1])/3)]);
                    $mathsData = json_encode([$term1_marks[2] ,$term1_marks[2], $term2_marks[2], $term3_marks[2], round(($term1_marks[2] + $term2_marks[2] + $term3_marks[2])/3)]);
                    $scienceData = json_encode([$term1_marks[3] ,$term1_marks[3], $term2_marks[3], $term3_marks[3], round(($term1_marks[3] + $term2_marks[3] + $term3_marks[3])/3)]);
                    $socialData = json_encode([$term1_marks[4] ,$term1_marks[4], $term2_marks[4], $term3_marks[4], round(($term1_marks[4] + $term2_marks[4] + $term3_marks[4])/3)]);
                    $categories = json_encode(["", "Term 1", "Term 2", "Term 3", "Annual"]);
                    ?>

                    // JavaScript variables with data echoed from PHP
                    const englishData = <?php echo $englishData; ?>;
                    const tamilData = <?php echo $tamilData; ?>;
                    const mathsData = <?php echo $mathsData; ?>;
                    const scienceData = <?php echo $scienceData; ?>;
                    const socialData = <?php echo $socialData; ?>;
                    const categories = <?php echo $categories; ?>;

                    // Function to update the chart with new data
                    function updateChart() {
                      new ApexCharts(document.querySelector("#reportsChart"), {
                        series: [{
                          name: 'English',
                          data: englishData
                        }, {
                          name: 'Tamil',
                          data: tamilData
                        }, {
                          name: 'Maths',
                          data: mathsData
                        }, {
                          name: 'Science',
                          data: scienceData
                        }, {
                          name: 'Social',
                          data: socialData
                        }],
                        chart: {
                          height: 350,
                          type: 'area',
                          toolbar: {
                            show: false
                          },
                        },
                        markers: {
                          size: 4
                        },
                        colors: ['#4154f1', '#2eca6a', '#ff771d', '#f00524', '#ffe605'],
                        fill: {
                          type: "gradient",
                          gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.3,
                            opacityTo: 0.4,
                            stops: [0, 90, 100]
                          }
                        },
                        dataLabels: {
                          enabled: false
                        },
                        stroke: {
                          curve: 'smooth',
                          width: 2
                        },
                        xaxis: {
                          type: 'categories',
                          categories: categories
                        },
                        tooltip: {
                          x: {
                            format: 'dd/MM/yy HH:mm'
                          },
                        }
                      }).render();
                    }

                    // Call the updateChart function to render the chart
                    updateChart();
                  });
                </script>
                  <!-- End Line Chart -->

                </div>

              </div>
            </div><!-- End Reports -->

            <!-- Recent Sales -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Assessment <span>| Class Test</span></h5>

                  <table class="table table-borderless datatable">
                    <thead>
                      <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Test</th>
                        <th scope="col">Subject Name</th>
                        <th scope="col">Marks</th>
                        <th scope="col">Result</th>
                      </tr>
                    </thead>
                    <tbody>
                        <?php
                          for($i = 0; $i < count($assess0); $i++) { ?>
                          <tr>
                            <th scope="row"><a href="#"><?php echo $assess0[$i]; ?></a></th>
                            <td><?php echo $assess1[$i]; ?></td>
                            <td><a href="#" class="text-primary"><?php echo $assess2[$i]; ?></a></td>
                            <td><?php echo $assess3[$i]; ?></td>
                            <?php
                                if($assess4[$i] == "FAIL") {
                            ?>
                                <td><span class="badge bg-danger"><?php echo $assess4[$i]; ?></span></td>
                                <?php } else { ?>
                                <td><span class="badge bg-success"><?php echo $assess4[$i]; ?></span></td>
                                <?php } ?>
                          </tr>
                        <?php }?>
                    </tbody>
                  </table>

                </div>

              </div>
            </div><!-- End Recent Sales -->

            <!--<div class="col-12">
              <div class="card top-selling overflow-auto">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                      <h6>Filter</h6>
                    </li>

                    <li><a class="dropdown-item" href="#">Today</a></li>
                    <li><a class="dropdown-item" href="#">This Month</a></li>
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                  </ul>
                </div>

                <div class="card-body pb-0">
                  <h5 class="card-title">Top Selling <span>| Today</span></h5>

                  <table class="table table-borderless">
                    <thead>
                      <tr>
                        <th scope="col">Preview</th>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col">Sold</th>
                        <th scope="col">Revenue</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row"><a href="#"><img src="assets/img/product-1.jpg" alt=""></a></th>
                        <td><a href="#" class="text-primary fw-bold">Ut inventore ipsa voluptas nulla</a></td>
                        <td>$64</td>
                        <td class="fw-bold">124</td>
                        <td>$5,828</td>
                      </tr>
                      <tr>
                        <th scope="row"><a href="#"><img src="assets/img/product-2.jpg" alt=""></a></th>
                        <td><a href="#" class="text-primary fw-bold">Exercitationem similique doloremque</a></td>
                        <td>$46</td>
                        <td class="fw-bold">98</td>
                        <td>$4,508</td>
                      </tr>
                      <tr>
                        <th scope="row"><a href="#"><img src="assets/img/product-3.jpg" alt=""></a></th>
                        <td><a href="#" class="text-primary fw-bold">Doloribus nisi exercitationem</a></td>
                        <td>$59</td>
                        <td class="fw-bold">74</td>
                        <td>$4,366</td>
                      </tr>
                      <tr>
                        <th scope="row"><a href="#"><img src="assets/img/product-4.jpg" alt=""></a></th>
                        <td><a href="#" class="text-primary fw-bold">Officiis quaerat sint rerum error</a></td>
                        <td>$32</td>
                        <td class="fw-bold">63</td>
                        <td>$2,016</td>
                      </tr>
                      <tr>
                        <th scope="row"><a href="#"><img src="assets/img/product-5.jpg" alt=""></a></th>
                        <td><a href="#" class="text-primary fw-bold">Sit unde debitis delectus repellendus</a></td>
                        <td>$79</td>
                        <td class="fw-bold">41</td>
                        <td>$3,239</td>
                      </tr>
                    </tbody>
                  </table>

                </div>

              </div>
            </div> -->

          </div>
        </div><!-- End Left side columns -->

        <!-- Right side columns -->
        <div class="col-lg-4">

          <!-- Recent Activity -->
          <!-- <div class="card"> 
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>

                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>

            <div class="card-body">
              <h5 class="card-title">Recent Activity <span>| Today</span></h5>

              <div class="activity">

                <div class="activity-item d-flex">
                  <div class="activite-label">32 min</div>
                  <i class='bi bi-circle-fill activity-badge text-success align-self-start'></i>
                  <div class="activity-content">
                    Quia quae rerum <a href="#" class="fw-bold text-dark">explicabo officiis</a> beatae
                  </div>
                </div>

                <div class="activity-item d-flex">
                  <div class="activite-label">56 min</div>
                  <i class='bi bi-circle-fill activity-badge text-danger align-self-start'></i>
                  <div class="activity-content">
                    Voluptatem blanditiis blanditiis eveniet
                  </div>
                </div>

                <div class="activity-item d-flex">
                  <div class="activite-label">2 hrs</div>
                  <i class='bi bi-circle-fill activity-badge text-primary align-self-start'></i>
                  <div class="activity-content">
                    Voluptates corrupti molestias voluptatem
                  </div>
                </div>

                <div class="activity-item d-flex">
                  <div class="activite-label">1 day</div>
                  <i class='bi bi-circle-fill activity-badge text-info align-self-start'></i>
                  <div class="activity-content">
                    Tempore autem saepe <a href="#" class="fw-bold text-dark">occaecati voluptatem</a> tempore
                  </div>
                </div>

                <div class="activity-item d-flex">
                  <div class="activite-label">2 days</div>
                  <i class='bi bi-circle-fill activity-badge text-warning align-self-start'></i>
                  <div class="activity-content">
                    Est sit eum reiciendis exercitationem
                  </div>
                </div>

                <div class="activity-item d-flex">
                  <div class="activite-label">4 weeks</div>
                  <i class='bi bi-circle-fill activity-badge text-muted align-self-start'></i>
                  <div class="activity-content">
                    Dicta dolorem harum nulla eius. Ut quidem quidem sit quas
                  </div>
                </div>

              </div>

            </div>
          </div> -->
          <!-- End Recent Activity -->

          <!-- Recent Activity -->
          <div class="card">
            <!-- <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>

                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>  -->

            <div class="card-body">
              <h5 class="card-title">Notifications <span></span></h5>

              <div class="activity">
                
                <?php  
                  foreach ($notification as $key => $value) {?>
                  <div class="activity-item d-flex">
                  <div class="activite-label">
                        <?php echo $key; ?>
                  </div>
                  <i class='bi bi-circle-fill activity-badge text-danger align-self-start'></i>
                    <div class="activity-content">
                          <?php
                            echo $value; "<br>";
                          ?>
                    </div>
                </div>
                <?php
                } 
                ?>
              </div>

            </div>
          </div><!-- End Recent Activity -->
          

          <!-- Budget Report -->
          <!-- <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>

                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>

            <div class="card-body pb-0">
              <h5 class="card-title">Budget Report <span>| This Month</span></h5>

              <div id="budgetChart" style="min-height: 400px;" class="echart"></div>

              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  var budgetChart = echarts.init(document.querySelector("#budgetChart")).setOption({
                    legend: {
                      data: ['Allocated Budget', 'Actual Spending']
                    },
                    radar: {
                      // shape: 'circle',
                      indicator: [{
                          name: 'Sales',
                          max: 6500
                        },
                        {
                          name: 'Administration',
                          max: 16000
                        },
                        {
                          name: 'Information Technology',
                          max: 30000
                        },
                        {
                          name: 'Customer Support',
                          max: 38000
                        },
                        {
                          name: 'Development',
                          max: 52000
                        },
                        {
                          name: 'Marketing',
                          max: 25000
                        }
                      ]
                    },
                    series: [{
                      name: 'Budget vs spending',
                      type: 'radar',
                      data: [{
                          value: [4200, 3000, 20000, 35000, 50000, 18000],
                          name: 'Allocated Budget'
                        },
                        {
                          value: [5000, 14000, 28000, 26000, 42000, 21000],
                          name: 'Actual Spending'
                        }
                      ]
                    }]
                  });
                });
              </script>

            </div>
          </div> -->
          <!-- End Budget Report -->

            <div class = "card">
              <div class="tab-content pt-2">
              
                  <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                      <li class="dropdown-header text-start">
                        <h6>Filter</h6>
                      </li>

                      <li><a class="dropdown-item" href="#">Today</a></li>
                      <li><a class="dropdown-item" href="#">This Month</a></li>
                      <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                  </div>
          

                <div class="card-body pb-0">
                  <a href = "users-profile.php"><h5 class="card-title">Profile Details <span>| Full Details</span></a></h5>


                  <div class="tab-pane fade show active profile-overview" id="profile-overview">
                    <!-- <h5 class="card-title">About</h5>
                    <p class="small fst-italic">Sunt est soluta temporibus accusantium neque nam maiores cumque temporibus. Tempora libero non est unde veniam est qui dolor. Ut sunt iure rerum quae quisquam autem eveniet perspiciatis odit. Fuga sequi sed ea saepe at unde.</p> -->


                    <div class="row">
                      <div class="col-lg-5 col-md-3">Full Name</div>
                      <div class="col-lg-7 col-md-3">: <?php echo $username; ?></div>
                    </div>

                    <div class="row">
                      <div class="col-lg-5 col-md-3">Standard</div>
                      <div class="col-lg-7 col-md-3">: <?php echo $row['standard']; ?></div>
                    </div>

                    <div class="row">
                      <div class="col-lg-5 col-md-3">Section</div>
                      <div class="col-lg-7 col-md-3">: <?php echo $row['section']; ?></div>
                    </div>

                    <div class="row">
                      <div class="col-lg-5 col-md-3">Gender</div>
                      <div class="col-lg-7 col-md-3">: <?php echo $row['gender']; ?></div>
                    </div>

                    <div class="row">
                      <div class="col-lg-5 col-md-3">Date of Birth</div>
                      <div class="col-lg-7 col-md-3">: <?php echo $row['dateOfBirth']; ?></div>
                    </div>

                    <div class="row">
                      <div class="col-lg-5 col-md-3">Blood Group</div>
                      <div class="col-lg-7 col-md-3">: <?php echo $row['bloodGroup']; ?></div>
                    </div>

                    <div class="row">
                      <div class="col-lg-5 col-md-3">Phone</div>
                      <div class="col-lg-7 col-md-3">: <?php echo $row['fatherPhone']; ?></div>
                    </div>

                    <div class="row">
                      <div class="col-lg-5 col-md-3">Email</div>
                      <div class="col-lg-7 col-md-3">: <?php echo $row['emailID']; ?></div>
                    </div>
                    <br>

                  </div>
                </div>
              </div>
            </div>

          <!-- Website Traffic -->
          <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>

                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>

            <div class="card-body pb-0">
              <h5 class="card-title">Marks <span>| Today</span></h5>

              <div id="trafficChart" style="min-height: 400px;" class="echart"></div>

              <script>

                function getTodayAttendance() {
                  fetch('attendanceToday.php')
                  .then(response => response.json())
                  .then(data => {
                    document.getElementById('attendanceToday').innerHTML = data.result;
                  })
                  .catch(error => console.error('Error: ',error));
                }

                
                document.addEventListener("DOMContentLoaded", () => {
                  echarts.init(document.querySelector("#trafficChart")).setOption({
                    tooltip: {
                      trigger: 'item'
                    },
                    legend: {
                      top: '5%',
                      left: 'center'
                    },
                    series: [{
                      name: 'Access From',
                      type: 'pie',
                      radius: ['40%', '70%'],
                      avoidLabelOverlap: false,
                      label: {
                        show: false,
                        position: 'center'
                      },
                      emphasis: {
                        label: {
                          show: true,
                          fontSize: '18',
                          fontWeight: 'bold'
                        }
                      },
                      labelLine: {
                        show: false
                      },
                      data: [{
                          value: 1048,
                          name: 'Search Engine'
                        },
                        {
                          value: 735,
                          name: 'Direct'
                        },
                        {
                          value: 580,
                          name: 'Email'
                        },
                        {
                          value: 484,
                          name: 'Union Ads'
                        },
                        {
                          value: 300,
                          name: 'Video Ads'
                        }
                      ]
                    }]
                  });
                });
              </script>

            </div>
          </div><!-- End Website Traffic -->

          <!-- News & Updates Traffic -->
          <!--<div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>

                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>

            <div class="card-body pb-0">
              <h5 class="card-title">News &amp; Updates <span>| Today</span></h5>

              <div class="news">
                <div class="post-item clearfix">
                  <img src="assets/img/news-1.jpg" alt="">
                  <h4><a href="#">Nihil blanditiis at in nihil autem</a></h4>
                  <p>Sit recusandae non aspernatur laboriosam. Quia enim eligendi sed ut harum...</p>
                </div>

                <div class="post-item clearfix">
                  <img src="assets/img/news-2.jpg" alt="">
                  <h4><a href="#">Quidem autem et impedit</a></h4>
                  <p>Illo nemo neque maiores vitae officiis cum eum turos elan dries werona nande...</p>
                </div>

                <div class="post-item clearfix">
                  <img src="assets/img/news-3.jpg" alt="">
                  <h4><a href="#">Id quia et et ut maxime similique occaecati ut</a></h4>
                  <p>Fugiat voluptas vero eaque accusantium eos. Consequuntur sed ipsam et totam...</p>
                </div>

                <div class="post-item clearfix">
                  <img src="assets/img/news-4.jpg" alt="">
                  <h4><a href="#">Laborum corporis quo dara net para</a></h4>
                  <p>Qui enim quia optio. Eligendi aut asperiores enim repellendusvel rerum cuder...</p>
                </div>

                <div class="post-item clearfix">
                  <img src="assets/img/news-5.jpg" alt="">
                  <h4><a href="#">Et dolores corrupti quae illo quod dolor</a></h4>
                  <p>Odit ut eveniet modi reiciendis. Atque cupiditate libero beatae dignissimos eius...</p>
                </div>

              </div><!-- End sidebar recent posts-->

            </div>
          </div>-->
          <!-- End News & Updates -->

        </div><!-- End Right side columns -->

      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; <strong><span>Asimos</span></strong><bR>All Rights Reserved
    </div>
    <!--<div class="credits">

      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
    </div>-->
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
<?php

      }
      else
      {
        // echo $_SESSION["access"] + $_SESSION["name"] + $_SESSION["id"];
        header("Location: error-404.html");
      }
?>