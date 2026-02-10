<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["id"], $_SESSION["name"])) {

    $username        = $_SESSION["name"];
    $sessionId       = $_SESSION["id"];   // from session
    $postedId        = $_POST["id"] ?? ""; // hidden field, just for extra safety
    $currentPassword = $_POST["currentPassword"] ?? "";
    $newPassword     = $_POST["newPassword"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    // Basic validation
    if ($newPassword === "" || $currentPassword === "" || $confirmPassword === "") {
        echo '<script>alert("All fields are required."); window.location.href = "../users-profile.php";</script>';
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo '<script>alert("New password and confirm password do not match."); window.location.href = "../users-profile.php";</script>';
        exit;
    }

    if (strlen($newPassword) < 6) {
        echo '<script>alert("New password should be at least 6 characters long."); window.location.href = "../users-profile.php";</script>';
        exit;
    }

    // Connect DB
    $conn = mysqli_connect("localhost", "root", "", "asimos");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Use session ID as the source of truth
    $id = mysqli_real_escape_string($conn, $sessionId);
    $usernameEsc = mysqli_real_escape_string($conn, $username);
    $currentEsc  = mysqli_real_escape_string($conn, $currentPassword);
    $newEsc      = mysqli_real_escape_string($conn, $newPassword);

    // Check old password
    $checkSql = "SELECT password FROM user_login WHERE id = '$id' AND name = '$usernameEsc' LIMIT 1";
    $result   = mysqli_query($conn, $checkSql);

    if ($result && mysqli_num_rows($result) === 1) {
        $row         = mysqli_fetch_assoc($result);
        $oldPassword = $row["password"];

        // NOTE: passwords are stored in plain text right now
        if ($oldPassword !== $currentEsc) {
            echo '<script>alert("Current password is incorrect."); window.location.href = "../users-profile.php";</script>';
        } else {
            // Update password
            $updateSql = "UPDATE user_login SET password = '$newEsc' WHERE id = '$id' AND name = '$usernameEsc'";
            if (mysqli_query($conn, $updateSql)) {
                echo '<script>alert("Password updated successfully."); window.location.href = "../users-profile.php";</script>';
            } else {
                echo '<script>alert("Error updating password. Please try again."); window.location.href = "../users-profile.php";</script>';
            }
        }
    } else {
        echo '<script>alert("User not found or session mismatch."); window.location.href = "../users-profile.php";</script>';
    }

    mysqli_close($conn);

} else {
    // Invalid access
    header("Location: ../error-404.html");
    exit;
}
?>
