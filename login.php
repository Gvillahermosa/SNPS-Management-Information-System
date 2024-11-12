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
	}else if(empty($pass)){
        header("Location: login.php?error=Password is required");
	    exit();
	}else{
        $sql = "SELECT * FROM users WHERE user_name='$uname' AND password='$pass'";
		$result = mysqli_query($conn, $sql);

		if (mysqli_num_rows($result)===1) {

			 $row = mysqli_fetch_assoc($result);
            if ($row['user_name'] === $uname && $row['password'] === $pass) {
            	$_SESSION['user_name'] = $row['user_name'];
            	$_SESSION['name'] = $row['first_name'];
            	$_SESSION['id'] = $row['id'];
            	header("Location: home.php");
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
        }
        else{
			header("Location: index.php?error=Incorect Username or password");
	        exit();
		}
    }else
        {
			header("Location: index.php?error=Incorect Username or password");
	        exit();
         }
}
}else {
    header("Location: index.php");
    exit();
}
