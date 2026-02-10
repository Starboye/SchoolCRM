<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $username = $_POST['username'];
        $password = $_POST['password'];
        $usertype = $_POST['userType'];

        // UI supports only 0=student, 1=teacher, 2=admin
        if (!in_array((int)$usertype, [0, 1, 2], true)) {
            echo '<script>alert("Invalid user type selected."); window.location.href = "../index.php";</script>';
            exit;
        }

        $servername = "localhost";
        $username_db = "root";
        $password_db = "";
        $dbname = "asimos";

        echo $username;
        echo $password;
        echo $usertype;
    
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }


        $sql = "SELECT * FROM user_login WHERE name='$username' AND password='$password'";
        $result = $conn->query($sql);

        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $precedence = $row['access'];
            echo $precedence;
            echo $usertype;
            if($precedence == $usertype) {
                session_start();

                if($usertype == 0) {
                    header("Location: ../studentDashboard.php");
                    $_SESSION["access"] = "0";
                    $_SESSION["name"] = $username;
                    $_SESSION["id"] = $row['id'];
                }

                if($usertype == 1) {
                    header("Location: ../teacher/dashboard.php");
                    $_SESSION["access"] = "1";
                    $_SESSION["name"] = $username;
                    $_SESSION["id"] = $row['id'];
                }

                if($usertype == 2) {
                    $_SESSION["access"] = "2";
                    $_SESSION["name"] = $username;
                    $_SESSION["id"] = $row['id'];
                    header("Location: ../admin/dashboard.php");
                    exit;
                }
            }

            else {
                 echo '<script>alert("Un-Authorized ! Please choose the Correct Category."); window.location.href = "../index.php";</script>';
            }

        } else {
             echo '<script>alert("Incorrect Username / Password. Please enter the correct details."); window.location.href = "../index.php";</script>';
        
        }
        $conn->close();
}
?>
