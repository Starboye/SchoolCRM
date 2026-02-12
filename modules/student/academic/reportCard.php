<?php
session_start();

if (!isset($_SESSION["access"], $_SESSION["name"], $_SESSION["id"]) || $_SESSION["access"] != 0) {
  header("Location: backoffice/login.php");
  exit();
}

$username = $_SESSION["name"];
$id       = $_SESSION["id"];   // student id (e.g. 2017115611)

// DB connection (same as studentDashboard.php)
$servername  = "localhost";
$username_db = "root";
$password_db = "";
$dbname      = "asimos";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ---------- 1. Fetch student info ----------
$studentInfo = null;
$studentSql  = "SELECT * FROM student_info WHERE id = '$id' LIMIT 1";
$studentRes  = $conn->query($studentSql);

if ($studentRes && $studentRes->num_rows > 0) {
  $studentInfo = $studentRes->fetch_assoc();
} else {
  // Fallback to session name if no row found
  $studentInfo = [
    'name'     => $username,
    'standard' => null,
    'section'  => null,
  ];
}

// ---------- 2. Determine selected term ----------
$validTerms = ["Term 1", "Term 2", "Term 3"];
$selectedTerm = isset($_GET['term']) ? $_GET['term'] : "Term 1";
if (!in_array($selectedTerm, $validTerms, true)) {
  $selectedTerm = "Term 1";
}

// ---------- 3. Fetch student's marks for selected term from marks_new ----------
$subjects = ["english", "tamil", "maths", "science", "social"];
$subjectLabels = [
  "english" => "English",
  "tamil"   => "Tamil",
  "maths"   => "Maths",
  "science" => "Science",
  "social"  => "Social",
];

$studentMarks = [
  "english" => 0,
  "tamil"   => 0,
  "maths"   => 0,
  "science" => 0,
  "social"  => 0,
];

$hasStudentMarks = false;
$studentTotal    = 0;
$perSubjectMax   = 100; // assuming each subject is out of 100

$marksSql = "SELECT * FROM marks_new WHERE id = '$id' AND testName = '$selectedTerm' LIMIT 1";
$marksRes = $conn->query($marksSql);

if ($marksRes && $marksRes->num_rows > 0) {
  $row = $marksRes->fetch_assoc();
  foreach ($subjects as $sub) {
    $studentMarks[$sub] = isset($row[$sub]) ? (int)$row[$sub] : 0;
    $studentTotal      += $studentMarks[$sub];
  }
  // If totalMarks stored per subject maximum (same for all), use that:
  if (!empty($row['totalMarks'])) {
    $perSubjectMax = (int)$row['totalMarks'];
  }
  $hasStudentMarks = true;
}

// ---------- 4. Fetch class-level stats (average + topper + rank) ----------
$classAvg   = array_fill_keys($subjects, 0);
$classTop   = array_fill_keys($subjects, 0);
$classCount = 0;
$classTotals = [];  // [student_id => totalMarks]
$rank       = null;
$classSize  = 0;
$percentile = null;

// Compute over ALL students in marks_new for that term
$classSql = "
  SELECT id, english, tamil, maths, science, social
  FROM marks_new
  WHERE testName = '" . $conn->real_escape_string($selectedTerm) . "'
";

$classRes = $conn->query($classSql);

if ($classRes && $classRes->num_rows > 0) {
  $sums = array_fill_keys($subjects, 0);

  while ($row = $classRes->fetch_assoc()) {
    $classCount++;

    $stuId = $row['id'];
    $totalForStudent = 0;

    foreach ($subjects as $sub) {
      $val = isset($row[$sub]) ? (int)$row[$sub] : 0;
      $sums[$sub] += $val;

      if ($val > $classTop[$sub]) {
        $classTop[$sub] = $val;
      }

      $totalForStudent += $val;
    }

    $classTotals[$stuId] = $totalForStudent;
  }

  // averages
  if ($classCount > 0) {
    foreach ($subjects as $sub) {
      $classAvg[$sub] = round($sums[$sub] / $classCount, 1);
    }
  }

  // rank for current student
  arsort($classTotals); // high → low
  $classSize = count($classTotals);
  $pos = 1;
  foreach ($classTotals as $stuId => $total) {
    if ($stuId == $id) {
      $rank = $pos;
      break;
    }
    $pos++;
  }

  if ($rank !== null && $classSize > 0) {
    $percentile = round((($classSize - $rank + 1) / $classSize) * 100);
  }
}



// ---------- 4. Fetch class-level stats (average + topper + rank) ----------
// $classAvg   = array_fill_keys($subjects, 0);
// $classTop   = array_fill_keys($subjects, 0);
// $classCount = 0;
// $rank       = null;
// $classSize  = 0;
// $percentile = null;

// // Need standard & section from student_info to define the class
// $standard = isset($studentInfo['standard']) ? (int)$studentInfo['standard'] : null;
// $section  = isset($studentInfo['section'])  ? $studentInfo['section'] : null;

// $classTotals = []; // total marks per student in this class for ranking: [student_id => totalMarks]

// if ($standard !== null && $section !== null) {
//   $classSql = "
//     SELECT mn.*, si.id AS student_id
//     FROM marks_new mn
//     JOIN student_info si ON si.id = mn.id
//     WHERE si.standard = $standard
//       AND si.section = '" . $conn->real_escape_string($section) . "'
//       AND mn.testName = '" . $conn->real_escape_string($selectedTerm) . "'
//   ";

//   $classRes = $conn->query($classSql);

//   if ($classRes && $classRes->num_rows > 0) {
//     $sums = array_fill_keys($subjects, 0);

//     while ($row = $classRes->fetch_assoc()) {
//       $classCount++;
//       $stuId = $row['student_id'];

//       $totalForStudent = 0;
//       foreach ($subjects as $sub) {
//         $val = isset($row[$sub]) ? (int)$row[$sub] : 0;
//         $sums[$sub] += $val;
//         if ($val > $classTop[$sub]) {
//           $classTop[$sub] = $val;
//         }
//         $totalForStudent += $val;
//       }
//       $classTotals[$stuId] = $totalForStudent;
//     }

//     if ($classCount > 0) {
//       foreach ($subjects as $sub) {
//         $classAvg[$sub] = round($sums[$sub] / $classCount, 1);
//       }
//     }

//     // Compute rank for logged-in student based on total marks
//     arsort($classTotals); // high to low
//     $classSize = count($classTotals);
//     $position  = 1;
//     foreach ($classTotals as $stuId => $total) {
//       if ($stuId == $id) {
//         $rank = $position;
//         break;
//       }
//       $position++;
//     }

//     if ($rank !== null && $classSize > 0) {
//       // Percentile: higher rank = better percentile
//       $percentile = round((($classSize - $rank + 1) / $classSize) * 100);
//     }
//   }
// }

// ---------- 5. Prepare data for chart (You vs Avg vs Topper) ----------
$chartSubjects     = [];
$studentSeriesData = [];
$avgSeriesData     = [];
$topperSeriesData  = [];

foreach ($subjects as $sub) {
  $chartSubjects[]     = $subjectLabels[$sub];
  $studentSeriesData[] = $studentMarks[$sub];
  $avgSeriesData[]     = $classAvg[$sub];
  $topperSeriesData[]  = $classTop[$sub];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Asimos - Report Card</title>
  <meta content="Asimos Report Card" name="description">
  <meta content="Asimos Report Card" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,600,700" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

  <!-- You can reuse the same header as studentDashboard.php if you want -->
  <!-- ======= Header (simplified placeholder) ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="studentDashboard.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <!-- <span class="d-none d-lg-block">Asimos</span> -->
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlspecialchars($username); ?></span>
          </a>
        </li>
      </ul>
    </nav>
  </header><!-- End Header -->

      <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">
    <div id="sidebar-container"></div>
    <script src="assets/js/loadSidebar.js"></script>
  </aside>
  <!-- ======= Sidebar ======= -->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Report Card</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="studentDashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Report Card</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Student info + term selector -->
        <div class="col-lg-12">
          <div class="card info-card">
            <div class="card-body">
              <h5 class="card-title">Student Details <span>| Report</span></h5>

              <div class="row">
                <div class="col-md-8">
                  <p><strong>Name:</strong> <?php echo htmlspecialchars($studentInfo['name'] ?? $username); ?></p>
                  <p>
                    <strong>Class:</strong>
                    <?php echo isset($studentInfo['standard']) ? htmlspecialchars($studentInfo['standard']) : '-'; ?>
                    <?php echo isset($studentInfo['section']) ? ' - ' . htmlspecialchars($studentInfo['section']) : ''; ?>
                  </p>
                  <p><strong>Student ID:</strong> <?php echo htmlspecialchars($id); ?></p>
                </div>
                <div class="col-md-4">
                  <form method="get" class="d-flex flex-column align-items-end">
                    <label for="term" class="form-label">Select Term</label>
                    <select name="term" id="term" class="form-select w-auto mb-2" onchange="this.form.submit()">
                      <?php foreach ($validTerms as $term): ?>
                        <option value="<?php echo htmlspecialchars($term); ?>" <?php if ($term === $selectedTerm) echo 'selected'; ?>>
                          <?php echo htmlspecialchars($term); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <?php if ($hasStudentMarks): ?>
                      <p class="mb-0"><strong>Total for <?php echo htmlspecialchars($selectedTerm); ?>:</strong> <?php echo $studentTotal; ?> / <?php echo $perSubjectMax * count($subjects); ?></p>
                    <?php else: ?>
                      <p class="text-danger mb-0">No marks available for <?php echo htmlspecialchars($selectedTerm); ?>.</p>
                    <?php endif; ?>
                  </form>
                </div>
              </div>

              <?php if ($rank !== null): ?>
                <div class="mt-3">
                  <p class="mb-0">
                    <strong>Rank:</strong> <?php echo $rank; ?> / <?php echo $classSize; ?>
                    <?php if ($percentile !== null): ?>
                      <span class="badge bg-success ms-2">Top <?php echo $percentile; ?>%</span>
                    <?php endif; ?>
                  </p>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>

        <!-- Marks table -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <!-- <h5 class="card-title">Marks - <?php echo htmlspecialchars($selectedTerm); ?></h5>  -->
               <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        Marks - <?php echo htmlspecialchars($selectedTerm); ?>
                    </h5>

                    <!-- Download PDF Link -->
                    <a href="reportCard_pdf.php?term=<?php echo urlencode($selectedTerm); ?>"
                    style="font-size: 16px; font-weight: 600; color: #4154f1; text-decoration: none;">
                        Download PDF
                    </a>
                </div>

              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Subject</th>
                    <th>Marks Obtained</th>
                    <th>Out of</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($subjects as $sub): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($subjectLabels[$sub]); ?></td>
                      <td><?php echo $studentMarks[$sub]; ?></td>
                      <td><?php echo $perSubjectMax; ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <tr class="table-secondary">
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo $studentTotal; ?></strong></td>
                    <td><strong><?php echo $perSubjectMax * count($subjects); ?></strong></td>
                  </tr>
                </tbody>
              </table>

            </div>
          </div>
        </div>

        <!-- Comparison chart: You vs Class Avg vs Topper -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Comparison <span>| You vs Class</span></h5>
              <div id="subjectComparisonChart" style="min-height: 350px;" class="echart"></div>
            </div>
          </div>
        </div>

      </div>
    </section>

  </main><!-- End #main -->

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

  <!-- Chart initialization -->
  <script>
    const rcSubjects = <?php echo json_encode($chartSubjects); ?>;
    const rcStudent = <?php echo json_encode($studentSeriesData); ?>;
    const rcAvg     = <?php echo json_encode($avgSeriesData); ?>;
    const rcTopper  = <?php echo json_encode($topperSeriesData); ?>;

    document.addEventListener("DOMContentLoaded", function () {
      const el = document.querySelector("#subjectComparisonChart");
      if (!el || typeof echarts === "undefined") {
        console.error("ECharts or chart container missing");
        return;
      }

      const chart = echarts.init(el);
      chart.setOption({
        tooltip: { trigger: 'axis' },
        legend: { data: ['You', 'Class Avg', 'Topper'] },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: {
          type: 'category',
          data: rcSubjects
        },
        yAxis: {
          type: 'value',
          min: 0,
          max: 100
        },
        series: [
          {
            name: 'You',
            type: 'bar',
            data: rcStudent
          },
          {
            name: 'Class Avg',
            type: 'bar',
            data: rcAvg
          },
          {
            name: 'Topper',
            type: 'bar',
            data: rcTopper
          }
        ]
      });
    });
  </script>

</body>
</html>
