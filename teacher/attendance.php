<?php
session_start();

// DB Connection
$db = mysqli_connect("localhost", "root", "", "asimos");
if(!$db){ die("DB failed"); }

$teacher_id = $_SESSION['id']; 
$date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
$session = isset($_GET['session']) ? $_GET['session'] : "";
?>

<?php include "includes/teacher_header.php"; ?>
  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">
    <div id="sidebar-container"></div>
    <script src="../teacher/includes/loadteacherSidebar.js"></script>
  </aside>
  <!-- ======= Sidebar ======= -->
<div class="container mt-4">

<h3>Realtime Attendance</h3>
<hr>

<!-- FILTER FORM -->
<form method="GET">

    <label>Date</label>
    <input type="date" name="date" class="form-control" 
    value="<?= $date; ?>" required>

    <label class="mt-3">Session</label>
    <select name="session" class="form-control" required>
        <option value="">-- choose --</option>
        <option value="morning"   <?= ($session=="morning")?"selected":""; ?>>Morning</option>
        <option value="afternoon" <?= ($session=="afternoon")?"selected":""; ?>>Afternoon</option>
        <option value="evening"   <?= ($session=="evening")?"selected":""; ?>>Evening</option>
    </select>

    <button class="btn btn-primary mt-3">Load Students</button>
</form>


<?php
if($session != "")
{
    $studentQuery = "
        SELECT DISTINCT id, name 
        FROM student_info 
        ORDER BY name;
    ";
    $studentResult = mysqli_query($db, $studentQuery);

    echo "<hr>";
?>

<table class="table table-bordered mt-3">

<thead>
<tr>
    <th>Student</th>
    <th style="width:150px;">Control</th>
    <th>Status</th>
</tr>
</thead>

<tbody>

<?php while($s = mysqli_fetch_assoc($studentResult)){ ?>

<tr id="row<?= $s['id']; ?>">

<td><?= $s['name']; ?></td>

<td>

<button class="btn btn-success btn-sm"
onclick="markAttendance('<?= $s['id']; ?>',1)">
P
</button>

<button class="btn btn-danger btn-sm"
onclick="markAttendance('<?= $s['id']; ?>',0)">
A
</button>

</td>

<td id="status<?= $s['id']; ?>"></td>

</tr>

<?php } ?>

</tbody>

</table>

<?php } ?>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

function markAttendance(student_id, status)
{
    // read live dropdown value
    let session = document.querySelector("select[name='session']").value;
    let date    = document.querySelector("input[name='date']").value;

    if(session === ""){
        alert("Select session first");
        return;
    }

    $.ajax({
        url: "../forms/updateAttendanceAjax.php",
        type: "POST",
        data: {
            student_id: student_id,
            status: status,
            session: session,
            date: date
        },
        success:function(response){

            if(status == 1){
                document.getElementById("status"+student_id).innerHTML =
                    "<span style='color:green;font-weight:bold;'>Present</span>";
            } else {
                document.getElementById("status"+student_id).innerHTML =
                    "<span style='color:red;font-weight:bold;'>Absent</span>";
            }

        }
    });

}

</script>


<?php include "includes/teacher_footer.php"; ?>
