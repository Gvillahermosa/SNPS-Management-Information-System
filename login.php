<?php
session_start();

include "db/db_conn.php";

if (isset($_POST['username']) && isset($_POST['password'])) {

    function validate($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $uname = validate($_POST['username']);
    $pass = validate($_POST['password']);

    if (empty($uname)) {
        header("Location: login.php?error=Username is required");
        exit();
    } else if (empty($pass)) {
        header("Location: login.php?error=Password is required");
        exit();
    } else {
        // Check if username exists
        $sql_username_check = "SELECT * FROM users WHERE user_name='$uname'";
        $username_result = mysqli_query($conn, $sql_username_check);

        if (mysqli_num_rows($username_result) > 0) {
            // Username exists, now check the password
            $row = mysqli_fetch_assoc($username_result);
            if ($row['password'] === $pass) {
                // Correct username and password
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['name'] = $row['first_name'];
                $_SESSION['id'] = $row['id'];
                $_SESSION['role'] = $row['role'];

                // Check user role and redirect accordingly
                if ($row['role'] === 'admin' || $row['role'] == 'principal') {
                    header("Location: adminDashboard/admin_dashboard.php");
                } else if ($row['role'] === 'teacher') {
                    header("Location: teacherDashboard/teacher_dashboard.php");
                } else if ($row['role'] === 'subject_teacher') {
                    header("Location: subjectteacherDashboard/subject_teacher_dashboard.php");
                } else {
                    header("Location: index.php");
                }

                exit();
            } else {
                // Password is incorrect
                header("Location: index.php?error=Password is incorrect");
                exit();
            }
        } else {
            // Both username and password are incorrect
            header("Location: index.php?error=Incorrect Username and Password");
            exit();
        }
    }
} else {
    header("Location: index.php");
    exit();
}
?>
