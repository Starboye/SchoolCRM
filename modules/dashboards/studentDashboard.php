<?php
require_once __DIR__ . '/../includes/portal.php';
require_login(0);

$username = $_SESSION['name'];
$id = $_SESSION['id'];
$access = $_SESSION['access'];
$row = '';

$conn = db_mysqli();

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

// Notification Systems 
$ntnFrm = [];
$ntnrow = [];

$student_id = $_SESSION['id'];

$res = $conn->query("
    SELECT *
    FROM student_info
    WHERE id = '$student_id'
    LIMIT 1
");


$row = $res->fetch_assoc();
$student = $res->fetch_assoc();


$std = $row['standard'];
$sec = $row['section'];

$_SESSION['standard'] = $std;
$_SESSION['section'] = $sec;

$classKey = strtoupper("CLASS_{$std}_{$sec}");

$notificationSql = "
    SELECT * FROM notification
    WHERE TRIM(id) = '$student_id'
       OR TRIM(id) = 'ALL'
       OR TRIM(id) = '$classKey'
    ORDER BY date DESC, time DESC
";


$notificationRes = $conn->query($notificationSql);

if ($notificationRes && $notificationRes->num_rows > 0) {
    while ($nrow = $notificationRes->fetch_assoc()) {
        $ntnrow[] = $nrow['notification'];
        $ntnFrm[] = $nrow['sentBy'];
    }
} else {
    // keep arrays in sync
    $ntnFrm[]  = 'System';
    $ntnrow[]  = 'No Notification';
}

/* FINAL SAFETY CHECK */
if (!empty($ntnFrm) && !empty($ntnrow) && count($ntnFrm) === count($ntnrow)) {
    $notification = array_combine($ntnFrm, $ntnrow);
} else {
    $notification = [];
}

  
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
    $assess0 = [];
    $assess1 = [];
    $assess2 = [];
    $assess3 = [];
    $assess4 = [];
    $assessmentSql = "SELECT * FROM assessments WHERE id='$id'";
    $assessmentRes = $conn->query($assessmentSql);

    if ($assessmentRes && $assessmentRes->num_rows > 0) {
      while ($assRow = $assessmentRes->fetch_assoc()) {
        $assess0[] = $assRow['date'];
        $assess1[] = $assRow['test'];
        $assess2[] = $assRow['subjectName'];
        $assess3[] = $assRow['marks'];
        $assess4[] = $assRow['Result'];
      }
    }


?>
<?php
$pageTitle = 'Dashboard';
$withCharts = true;
include dirname(__DIR__, 2) . '/partials/student_head.php';
include dirname(__DIR__, 2) . '/partials/header.php';
?>
<?php include dirname(__DIR__, 2) . '/partials/sidebar.php'; ?>

<main id="main" class="main dashboard">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="studentDashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div>

    <?php if (!empty($_GET['search_error'])): ?>
      <div class="alert alert-warning">
        No page matched "<?= e((string)($_GET['q'] ?? '')) ?>". Try: timetable, homework, report card, profile, fees, announcements.
      </div>
    <?php endif; ?>

    <section class="section dashboard">
      <div class="row">

        <!-- Left  echo $attendanceTodayResult['status'] side columns -->
        <div class="col-lg-8">
          <div class="row">

            <!-- Sales Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Attendance <span>| Today </span></h5>
                   
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
                  <h5 class="card-title">Rank <span>| Latest</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6>12</h6>
                      <span class="text-danger small pt-1 fw-bold">55</span> <span class="text-muted small pt-2 ps-1">Increased by 5 Ranks</span>

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
                        <?php if (empty($assess0)): ?>
                          <tr>
                            <td colspan="5" class="text-muted text-center">No class test records yet.</td>
                          </tr>
                        <?php else: ?>
                          <?php for ($i = 0, $n = count($assess0); $i < $n; $i++): ?>
                          <tr>
                            <th scope="row"><a href="#"><?php echo e((string)$assess0[$i]); ?></a></th>
                            <td><?php echo e((string)$assess1[$i]); ?></td>
                            <td><a href="#" class="text-primary"><?php echo e((string)$assess2[$i]); ?></a></td>
                            <td><?php echo e((string)$assess3[$i]); ?></td>
                            <?php if (($assess4[$i] ?? '') === 'FAIL'): ?>
                                <td><span class="badge bg-danger"><?php echo e((string)$assess4[$i]); ?></span></td>
                            <?php else: ?>
                                <td><span class="badge bg-success"><?php echo e((string)($assess4[$i] ?? 'PASS')); ?></span></td>
                            <?php endif; ?>
                          </tr>
                          <?php endfor; ?>
                        <?php endif; ?>
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

              <div class="activity portal-notifications">
                <?php if (empty($notification)): ?>
                  <p class="text-muted mb-0">No notifications.</p>
                <?php else: ?>
                  <?php
                    $badgeColors = ['text-danger', 'text-success', 'text-primary', 'text-warning', 'text-info'];
                    $ni = 0;
                    foreach ($notification as $sender => $message):
                      $badgeClass = $badgeColors[$ni % count($badgeColors)];
                      $ni++;
                  ?>
                  <div class="activity-item d-flex">
                    <div class="activite-label"><?= e((string)$sender) ?></div>
                    <i class="bi bi-circle-fill activity-badge <?= e($badgeClass) ?> align-self-start"></i>
                    <div class="activity-content"><?= e((string)$message) ?></div>
                  </div>
                  <?php endforeach; ?>
                <?php endif; ?>
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
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" id="termDropdown">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>

                <li><a class="dropdown-item" href="#" data-term="| Term 1">Term 1</a></li>
                <li><a class="dropdown-item" href="#" data-term="| Term 2">Term 2</a></li>
                <li><a class="dropdown-item" href="#" data-term="| Term 3">Term 3</a></li>
              </ul>
            </div>
                        
          
            <script>
              document.querySelectorAll('#termDropdown .dropdown-item').forEach(item => {
                item.addEventListener('click', function (e) {
                  e.preventDefault();
                  const selected = this.getAttribute('data-term');

                  // Update UI
                  document.getElementById('selectedTerm').innerText = selected;

                  // Send to this same PHP file
                  fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'selected_term=' + encodeURIComponent(selected)
                  })
                  .then(res => res.text())
                  .then(data => {
                    console.log('Server response:', data);
                  });
                   window.location.reload();
                });
              });
            </script>
            <?php
            
              // Capture POST request from fetch() and store in session
              if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_term'])) {
                  $_SESSION['selected_term'] = $_POST['selected_term'];
                  echo "Term Saved: " . $_SESSION['selected_term'];
                  exit; // Stop further HTML rendering during fetch call
              }

              // Get saved term if exists
              $selectedTerm = $_SESSION['selected_term'] ?? 'Term 1';
            ?>
            
            <div class="card-body pb-0">
              <h5 class="card-title">Marks <span id="selectedTerm"> | <?php echo htmlspecialchars($selectedTerm); ?> </span></h5>

              <div id="trafficChart" style="min-height: 400px;" class="echart"></div>

              <script>
                document.addEventListener('DOMContentLoaded', function() {
                  try {
                    // 1) ensure ECharts is available
                    if (typeof echarts === 'undefined') {
                      console.error('ECharts not found. Make sure assets/vendor/echarts/echarts.min.js is loaded before this script.');
                      return;
                    }

                    // 2) ensure container exists and has height
                    const container = document.querySelector('#trafficChart');
                    if (!container) {
                      console.error('#trafficChart element not found in DOM.');
                      return;
                    }
                    // if container has no height (collapsed), give it a default height
                    if (!container.clientHeight || container.clientHeight === 0) {
                      container.style.minHeight = '360px';
                    }

                    // 3) Prepare term and marks. Use existing JS variables if present,
                    //    otherwise fallback to PHP values (safe json-encoded).
                    const selectedTerm = (typeof term !== 'undefined') ? term : <?php echo json_encode($selectedTerm ?? 'Term 1'); ?>;

                    // Attempt to reuse termMarks if defined, else build from PHP variables
                    let termMarksObj = (typeof termMarks !== 'undefined') ? termMarks :
                      {
                        "Term 1": {
                          english: <?php echo json_encode($term1_marks[0] ?? 0); ?>,
                          tamil:   <?php echo json_encode($term1_marks[1] ?? 0); ?>,
                          maths:   <?php echo json_encode($term1_marks[2] ?? 0); ?>,
                          science: <?php echo json_encode($term1_marks[3] ?? 0); ?>,
                          social:  <?php echo json_encode($term1_marks[4] ?? 0); ?>
                        },
                        "Term 2": {
                          english: <?php echo json_encode($term2_marks[0] ?? 0); ?>,
                          tamil:   <?php echo json_encode($term2_marks[1] ?? 0); ?>,
                          maths:   <?php echo json_encode($term2_marks[2] ?? 0); ?>,
                          science: <?php echo json_encode($term2_marks[3] ?? 0); ?>,
                          social:  <?php echo json_encode($term2_marks[4] ?? 0); ?>
                        },
                        "Term 3": {
                          english: <?php echo json_encode($term3_marks[0] ?? 0); ?>,
                          tamil:   <?php echo json_encode($term3_marks[1] ?? 0); ?>,
                          maths:   <?php echo json_encode($term3_marks[2] ?? 0); ?>,
                          science: <?php echo json_encode($term3_marks[3] ?? 0); ?>,
                          social:  <?php echo json_encode($term3_marks[4] ?? 0); ?>
                        }
                      };

                    // 4) pick marks for selected term, guard against missing keys
                    let marks = termMarksObj[selectedTerm];
                    if (!marks) {
                      // try trimmed key fallback
                      marks = termMarksObj[selectedTerm && selectedTerm.trim()] || Object.values(termMarksObj)[0];
                      console.warn('Selected term not found in data; using fallback marks for', selectedTerm);
                    }

                    // Ensure numeric values
                    marks = {
                      english: Number(marks.english) || 0,
                      tamil:   Number(marks.tamil) || 0,
                      maths:   Number(marks.maths) || 0,
                      science: Number(marks.science) || 0,
                      social:  Number(marks.social) || 0
                    };

                    // compute total
                    const totalMarks = marks.english + marks.tamil + marks.maths + marks.science + marks.social;

                    // 5) init chart
                    const chart = echarts.init(container);

                    const baseOption = {
                      tooltip: { trigger: 'item' },
                      legend: { top: '5%', left: 'center' },
                      title: {
                        text: selectedTerm + "\nTotal: " + totalMarks,
                        left: 'center', top: '45%',
                        textStyle: { fontSize: 16, fontWeight: 'bold', lineHeight: 22 }
                      },
                      series: [{
                        name: 'Marks',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        label: { show: false, position: 'center' },
                        labelLine: { show: false },
                        emphasis: { label: { show: false, fontSize: '18', fontWeight: 'bold' } },
                        data: [
                          { value: marks.english, name: 'English' },
                          { value: marks.tamil,   name: 'Tamil' },
                          { value: marks.maths,   name: 'Maths' },
                          { value: marks.science, name: 'Science' },
                          { value: marks.social,  name: 'Social' }
                        ]
                      }]
                    };

                    chart.setOption(baseOption);

                    // 6) hover behavior: show slice in center on mouseover, restore on mouseout
                    chart.on('mouseover', function(params) {
                      if (params && params.componentType === 'series' && params.seriesType === 'pie') {
                        const name = params.name || '';
                        const val = typeof params.value !== 'undefined' ? params.value : (params.data && params.data.value);
                        chart.setOption({ title: { text: name + "\n" + val } });
                      }
                    });

                    // restore on leaving chart area
                    chart.getDom().addEventListener('mouseleave', function() {
                      chart.setOption({ title: { text: selectedTerm + "\nTotal: " + totalMarks } });
                    });

                    // small safety: if container changes size later, resize chart
                    window.addEventListener('resize', function(){ chart.resize(); });

                    // debug info
                    console.info('Marks pie chart initialized for', selectedTerm, 'total:', totalMarks);

                  } catch (err) {
                    console.error('Pie chart init error:', err);
                  }
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
          </div>
          <!-- End News & Updates -->

        </div><!-- End Right side columns -->

      </div>
    </section>

  </main><!-- End #main -->

<?php include dirname(__DIR__, 2) . '/partials/student_footer.php'; ?>
