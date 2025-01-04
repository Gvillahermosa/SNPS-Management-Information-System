<?php
session_start();
include '../db/db_conn.php'; // Include your database connection file

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

    // Get the current year and month
    $currentYear = date("Y");
    $currentMonth = date("n"); // Numeric month (1 for January, 12 for December)

    // Determine the school year and semester
    if ($currentMonth < 6) { // Before June
        $startYear = $currentYear - 1;
        $endYear = $currentYear;
        $semester = "2nd semester";
    } else { // June or later
        $startYear = $currentYear;
        $endYear = $currentYear + 1;
        $semester = "1st semester";
    }

    // Combine into the school year format
    $schoolYear = $startYear . " - " . $endYear . " , " . $semester;

    // Query to count the number of students in the database
    $sql_students = "SELECT COUNT(*) as student_count FROM studentinfo";
    $result_students = mysqli_query($conn, $sql_students);
    $studentCount = 0;
    if ($result_students) {
        $row = mysqli_fetch_assoc($result_students);
        $studentCount = $row['student_count'];
    }

    // Query to count the number of Subjects in the database
    $sql_subjects = "SELECT COUNT(*) as subjects_count FROM subjects";
    $result_subjects = mysqli_query($conn, $sql_subjects);
    $subjectsCount = 0;
    if ($result_subjects) {
        $row = mysqli_fetch_assoc($result_subjects);
        $subjectsCount = $row['subjects_count'];
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
    $sql = "
SELECT
    c.id,
    c.level,
    c.section,
    c.teacher,
    SUM(CASE WHEN s.gender = 'Male' THEN 1 ELSE 0 END) AS male,
    SUM(CASE WHEN s.gender = 'Female' THEN 1 ELSE 0 END) AS female,
    COUNT(s.id) AS total
FROM
    classes c
LEFT JOIN
    studentinfo s ON c.id = s.class
GROUP BY
    c.id, c.level, c.section, c.teacher
";
    $result = mysqli_query($conn, $sql);

    // Prepare data for the chart
    $classes = [];
    $totals = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $classes[] = $row['level'] . ' ' . $row['section']; // Use class level and section as label
        $totals[] = $row['total']; // Use total students as data
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    </head>

    <body>
        <div class="header">
            <div class="header-left">
                <img src="../Assets/images/logo.png" alt="Logo">
                <p>SNPS | <?php echo $_SESSION['role']; ?></p>
            </div>
            <div class="navlink">
                <p>SY <?php echo $schoolYear; ?></p>
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
                        <li><a href="../adminDashboard/manage_subjects.php">Manage Subjects</a></li>
                        <li><a href="../adminDashboard/create_subject.php">Create Subject</a></li>
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
                    <a href="../adminDashboard/manage_subjects.php">
                        <div class="subject-list">
                            <span class="mdi--books"></span>
                            <p class="subjects_count" id="subjectslist" data-count="<?php echo $subjectsCount; ?>">0</p>
                            <p id="subjectlist">Subject Listed</p>
                        </div>
                    </a>
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
                <div class="dashboard-graph">
                    <canvas id="classesChart" width="400" height="350"></canvas>
                </div>

            </div>
        </div>



        <script src="../Assets/Javascript/main.js"></script>
        <script>
            var ctx = document.getElementById('classesChart').getContext('2d');
            var classesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($classes); ?>, // Class names
                    datasets: [{
                        label: 'Number of Students',
                        data: <?php echo json_encode($totals); ?>, // Number of students
                        backgroundColor: [
                            '#4D96FF', // Sky Blue
                            '#FF6B6B', // Soft Red
                            '#FFAC41',  // Bright Yellow-Orange
                            '#55D187', // Soft Green
                        ],
                        borderColor: [
                            '#277BCC', // Slightly darker Blue
                            '#E63946', // Slightly darker Red
                            '#E88E30',  // Slightly darker Orange
                            '#38A169', // Slightly darker Green
                        ],
                        borderWidth: 1,
                        borderRadius: 10 // Rounded bar corners
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: "Student's Enrolled in Each Class",
                            font: {
                                size: 20,
                                weight: 'bold'
                            },
                            color: '#333'
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.raw; // Show raw data value
                                    return label + ' students';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(200, 200, 200, 0.2)', // Subtle gridlines
                            },
                            ticks: {
                                autoSkip: false, // Prevent skipping of labels
                            },
                            categoryPercentage: 0.8, // Adjust bar width proportionally
                        },
                        y: {
                            grid: {
                                color: 'rgba(200, 200, 200, 0.2)',
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>

    </body>

    </html>

    <?php
} else {
    header("Location: ../index.php");
    exit();
}
?>
