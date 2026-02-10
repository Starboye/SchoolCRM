<?php
session_start();

/* ===============================
   DB CONNECTION
   =============================== */
$db = mysqli_connect("localhost", "root", "", "asimos");
if(!$db){ die("DB failed"); }

/* =================================================
   AJAX HANDLER — MUST BE FIRST
   ================================================= */
if (isset($_POST['ajax'])) {

    $student_id = mysqli_real_escape_string($db, $_POST['student_id']);
    $tab = $_POST['tab'];

    /* -------- PERSONAL -------- */
    if ($tab === "personal") {
        $s = mysqli_fetch_assoc(
            mysqli_query($db,"SELECT * FROM student_info WHERE id='$student_id'")
        );
        ?>
        <div class="card">
            <div class="card-body">
                <h4><?= $s['name']; ?> (<?= $s['standard'].$s['section']; ?>)</h4>
                <hr>
                <p><b>DOB:</b> <?= $s['dateOfBirth']; ?></p>
                <p><b>Gender:</b> <?= $s['gender']; ?></p>
                <p><b>Blood Group:</b> <?= $s['bloodGroup']; ?></p>
                <p><b>Phone:</b> <?= $s['phone']; ?></p>
                <p><b>Email:</b> <?= $s['emailID']; ?></p>
                <p><b>Address:</b> <?= $s['address']; ?></p>
            </div>
        </div>
        <?php
        exit;
    }

    /* -------- ACADEMICS -------- */
    if ($tab === "academic") {
        $q = mysqli_query($db,"
            SELECT e.exam_name, sub.subject_name,
                   md.marks_obtained, md.total_marks
            FROM marks_master mm
            JOIN exams e ON e.exam_id = mm.exam_id
            JOIN marks_details md ON md.mark_id = mm.mark_id
            JOIN subjects sub ON sub.id = md.subject_id
            WHERE mm.student_id='$student_id'
        ");
        ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Exam</th>
                <th>Subject</th>
                <th>Marks</th>
            </tr>
            </thead>
            <tbody>
            <?php while($r=mysqli_fetch_assoc($q)){ ?>
                <tr>
                    <td><?= $r['exam_name']; ?></td>
                    <td><?= $r['subject_name']; ?></td>
                    <td><?= $r['marks_obtained']; ?>/<?= $r['total_marks']; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php
        exit;
    }

    /* -------- ATTENDANCE -------- */
    if ($tab === "attendance") {
        $q = mysqli_query($db,"
            SELECT date, morning, afternoon, evening
            FROM attendance
            WHERE id='$student_id'
            ORDER BY date DESC
            LIMIT 30
        ");
        ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Date</th>
                <th>Morning</th>
                <th>Afternoon</th>
                <th>Evening</th>
            </tr>
            </thead>
            <tbody>
            <?php while($r=mysqli_fetch_assoc($q)){ ?>
                <tr>
                    <td><?= $r['date']; ?></td>
                    <td><?= $r['morning']?'Present':'Absent'; ?></td>
                    <td><?= $r['afternoon']?'Present':'Absent'; ?></td>
                    <td><?= $r['evening']?'Present':'Absent'; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php
        exit;
    }

    /* -------- HOMEWORK -------- */
    if ($tab === "homework") {
        $cls = mysqli_fetch_assoc(
            mysqli_query($db,"SELECT standard, section FROM student_info WHERE id='$student_id'")
        );
        $q = mysqli_query($db,"
            SELECT subject_name, title, description, date
            FROM homeworks
            WHERE standard='{$cls['standard']}'
              AND section='{$cls['section']}'
            ORDER BY date DESC
        ");
        while($r=mysqli_fetch_assoc($q)){
            ?>
            <div class="card mb-2">
                <div class="card-body">
                    <b><?= $r['subject_name']; ?></b> (<?= $r['date']; ?>)
                    <p><b><?= $r['title']; ?></b></p>
                    <p><?= $r['description']; ?></p>
                </div>
            </div>
            <?php
        }
        exit;
    }
}
?>

<?php include "includes/teacher_header.php"; ?>

<aside id="sidebar" class="sidebar">
  <div id="sidebar-container"></div>
  <script src="../teacher/includes/loadteacherSidebar.js"></script>
</aside>

<body>
<main id="main" class="main">
<div class="container mt-4">

<h3>Student Profile</h3>
<hr>

<label>Select Student</label>
<select id="student_id" class="form-control" style="max-width:350px;">
    <option value="">-- Select Student --</option>
    <?php
    $q = mysqli_query($db,"SELECT id,name,standard,section FROM student_info ORDER BY name");
    while($s=mysqli_fetch_assoc($q)){
        echo "<option value='{$s['id']}'>{$s['name']} ({$s['standard']}{$s['section']})</option>";
    }
    ?>
</select>

<ul class="nav nav-tabs mt-3" id="tabs" style="display:none;">
    <li class="nav-item"><a class="nav-link active" data-tab="personal">Personal</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="academic">Academics</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="attendance">Attendance</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="homework">Homework</a></li>
</ul>

<div id="content" class="mt-3"></div>

</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$("#student_id").change(function(){
    if(!this.value) return;
    $("#tabs").show();
    loadTab("personal");
});

$(".nav-link").click(function(){
    $(".nav-link").removeClass("active");
    $(this).addClass("active");
    loadTab($(this).data("tab"));
});

function loadTab(tab){
    $.post("view_studentData.php",{
        ajax:1,
        tab:tab,
        student_id:$("#student_id").val()
    },function(res){
        $("#content").html(res);
    });
}
</script>

<?php include "includes/teacher_footer.php"; ?>
