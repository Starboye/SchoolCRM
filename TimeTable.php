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
    <div id="sidebar-container"></div>
    <script src="assets/js/loadSidebar.js"></script>
  </aside>
  <!-- End Sidebar-->

  <!-- End Dashboard Nav -->







      <main id="main" class="main">
<div class="row">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Numbered Table</h5>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>Period</th>
              <th>Monday</th>
              <th>Tuesday</th>
              <th>Wednesday</th>
              <th>Thursday</th>
              <th>Friday</th>
       
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>English</td>
              <td>Tamil</td>
              <td>Maths</td>
              <td>Science</td>
              <td>Social</td>
              <!-- <td><button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button></td> -->
            </tr>
            <tr>
              <td>2</td>
              <td>Tamil</td>
              <td>Physics</td>
              <td>Chemistry</td>
              <td>Biology</td>
              <td>P.Edu</td>
              <!-- <td><button type="button" class="btn btn-danger"><i class="bi bi-exclamation-octagon"></i></button></td> -->
            </tr>
            <tr>
              <td>3</td>
              <td>Maths</td>
              <td>Chem Lab</td>
              <td>Chem Lab</td>
              <td>English</td>
              <td>Science</td>
              <!-- <td><button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button></td> -->
            </tr>
            <tr>
              <td>4</td>
              <td>Biology</td>
              <td>Bio Lab</td>
              <td>Bio Lab</td>
              <td>Maths</td>
              <td>Physics</td>
              <!-- <td><button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button></td> -->
            </tr>
             <tr>
              <td class="text-center">L</td>
              <td class="text-center" >U</td>
              <td class="text-center" >N</td>
              <td class="text-center" >C</td>
              <td class="text-center" >H</td>
              <!-- td>Physics</td> -->
              <!-- <td><button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button></td> -->
            </tr>
            <tr>
              <td>5</td>
              <td>Computer</td>
              <td>Tamil</td>
              <td>Accountancy</td>
              <td>History</td>
              <td>Geography</td>
              <!-- <td><button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button></td> -->
            </tr>
            <tr>
              <td>6</td>
              <td>Tamil</td>
              <td>Physics</td>
              <td>Maths</td>
              <td>Biology</td>
              <td>Chemistry</td>
              <!-- <td><button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button></td> -->
            </tr>
            <tr>
              <td>7</td>
              <td>Tamil</td>
              <td>Physics</td>
              <td>Chemistry</td>
              <td>Biology</td>
              <td>P.Edu</td>
              <!-- <td><button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button></td> -->
            </tr>
            <tr>
              <td>8</td>
              <td>English</td>
              <td>Science</td>
              <td>Computer</td>
              <td>Tamil</td>
              <td>Accountancy</td>
              <!-- <td><button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button></td> -->
            </tr>

            <!--<tr>
              <td>4</td>
              <td>Lisa Ray</td>
              <td>Social Studies</td>
            </tr> -->
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>










<?php

      }
      else
      {
        // echo $_SESSION["access"] + $_SESSION["name"] + $_SESSION["id"];
        header("Location: error-404.html");
      }
?>