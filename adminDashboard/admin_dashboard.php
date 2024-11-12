<?php
session_start();
include '../db/db_conn.php'; // Include your database connection file

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

    // Query to count the number of students in the database
    $sql_students = "SELECT COUNT(*) as student_count FROM studentinfo";
    $result_students = mysqli_query($conn, $sql_students);
    $studentCount = 0;
    if ($result_students) {
        $row = mysqli_fetch_assoc($result_students);
        $studentCount = $row['student_count'];
    }

    // Query to count the number of classes in the database
    $sql_classes = "SELECT COUNT(*) as class_count FROM classes";
    $result_classes = mysqli_query($conn, $sql_classes);
    $classCount = 0;
    if ($result_classes) {
        $row = mysqli_fetch_assoc($result_classes);
        $classCount = $row['class_count'];
    }

    // Query to count the number of teacher in the database
    $sql_teachers = "SELECT COUNT(*) as teacher_count FROM users WHERE role='teacher' OR role='principal'";
    $result_teachers = mysqli_query($conn, $sql_teachers);
    $teacherCount = 0;
    if ($result_teachers) {
        $row = mysqli_fetch_assoc($result_teachers);
        $teacherCount = $row['teacher_count'];
    }
    ?>
    <!DOCTYPE html>

    <html>

    <head>
        <title>Admin</title>
        <link rel="stylesheet" type="text/css" href="../Assets/css/admindashboard.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
            integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>

    <body>
        <div class="header">
            <div class="header-left">
                <img src="../Assets/images/logo.png" alt="Logo">
                <p>SNPS | Admin</p>
            </div>
            <div class="navlink">
                <p>SY 2024-2025, 1st Semester</p>
                <p>|</p>
                <p class="user-name"><?php echo $_SESSION['name']; ?></p>
                <p>|</p>
                <a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            </div>
        </div>
        <div class="sidenav">
            <ul>
                <li><a href="../adminDashboard/admin_dashboard.php" class="dashboardbtn">
                        <p class="stash--dashboard"></p>Dashboard
                    </a></li>
                <li><a href="#" class="student-btn">
                        <p class="fluent-mdl2--group"></p>Students
                        <span class="fas fa-caret-down second"></span>
                    </a>
                    <ul class="student-show">
                        <li><a href="../adminDashboard/managestudents.php">Manage Students</a>
                        </li>
                        <li><a href="../adminDashboard/studentadmission.php">Student Admission</a></li>
                    </ul>
                </li>
                <li><a href="#" class="jobplace-btn">
                        <p class="ph--books-thin"></p>Subjects
                        <span class="fas fa-caret-down third"></span>
                    </a>
                    <ul class="jobplace-show">
                        <li><a href="">Manage Subjects</a></li>
                        <li><a href="">Create Subject</a></li>
                    </ul>
                </li>
                <li><a href="#" class="classes-btn">
                        <p class="fluent--class-20-regular"></p>Classes
                        <span class="fas fa-caret-down fourth"></span>
                    </a>
                    <ul class="classes-show">
                        <li><a href="../adminDashboard/manageclass.php">Manage Classes</a></li>
                        <li><a href="../adminDashboard/createclass.php">Create Class</a></li>
                    </ul>
                </li>
                <li><a href="#" class="teacher_add-btn">
                        <p class="ph--chalkboard-teacher-light"></p>Teachers
                        <span class="fas fa-caret-down fifth"></span>
                    </a>
                    <ul class="teacher_add-show">
                        <li><a href="../adminDashboard/manageteacher.php">Manage Teacher</a></li>
                        <li><a href="../adminDashboard/teacherregistration.php">Teacher Registration</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="body-contents">
            <h1>Dashboard</h1>
            <div class="dashboard">
                <div class="dashboard-stat">
                    <a href="../adminDashboard/managestudents.php">
                        <div class="student-list">
                            <span class="fa--group"></span>
                            <p class="student_count" id="studentlist" data-count="<?php echo $studentCount; ?>">0</p>
                            <p id="student-list">Students Listed</p>
                        </div>
                    </a>
                    <div class="subject-list">
                        <span class="mdi--books"></span>
                        <p id="subjectlist">Subject Listed</p>
                    </div>
                    <a href="../adminDashboard/manageclass.php">
                        <div class="class-list">
                            <span class="f7--building-columns-fill"></span>
                            <p class="class_count" id="classlist" data-count="<?php echo $classCount; ?>">0</p>
                            <p id="class-list">Total Class Listed</p>
                        </div>
                    </a>
                    <a href="../adminDashboard/manageteacher.php">
                    <div class="teacher-list">
                        <span class="game-icons--teacher"></span>
                        <p class="teacher_count" id="teacherlist" data-count="<?php echo $teacherCount; ?>">0</p>
                        <p id="teacher-list">Teacher Listed</p>
                    </div>
                    </a>
                </div>
            </div>
        </div>



        <script src="../Assets/Javascript/main.js"></script>
    </body>

    </html>

    <?php
} else {
    header("Location: ../index.php");
    exit();
}
?>
