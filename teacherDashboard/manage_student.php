<?php
session_start();


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





if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "studentgradingsystem");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


// Retrieve teacher details based on the session user ID
$user_id = $_SESSION['id'];
// Query for teacher's personal details
$sqlTeacher = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sqlTeacher);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$resultTeacher = $stmt->get_result();
$teacher = $resultTeacher->fetch_assoc(); // Fetch teacher details

$teacher_id = $teacher['teacher_id'];

    // Fetch data from the students table
    $sql = "
    SELECT s.id, s.lrn, CONCAT(s.fname, ' ', s.lname) AS name, s.gender,
           CONCAT(c.level, ' - ', c.section) AS class
    FROM studentinfo s
    JOIN classes c ON s.class = c.id
    ";

    $result = $conn->query($sql);
    ?>
    <!DOCTYPE html>

    <html>

    <head>
    <title>SNPS MIS</title>
        <link rel="stylesheet" href="../Assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
        <link rel="stylesheet" type="text/css" href="../Assets/css/teacher_dashboard/manage_students.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
            integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
        <script>
            $(document).ready(function () {
                $('#example').DataTable();
            });
        </script>
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

                <li><a href="#" class="student-btn">
                        <p class="fluent-mdl2--group"></p>Students
                        <span class="fas fa-caret-down second"></span>
                    </a>
                    <ul class="student-show">
                        <li><a href="../teacherDashboard/managestudents.php">Student List</a>
                        </li>
                        <li><a href="../teacherDashboard/student_admission.php">Student Admission</a></li>
                    </ul>
                </li>

                <li><a href="#" class="classes-btn">
                        <p class="fluent--class-20-regular"></p>Classes
                        <span class="fas fa-caret-down fourth"></span>
                    </a>
                    <ul class="classes-show">
                        <li><a href="../teacherDashboard/manage_class.php">Class List</a></li>
                        <li><a href="../teacherDashboard/class_list.php?id=<?= $teacher['teacher_id']; ?>">My Classes</a></li>
                    </ul>
                </li>
                <li><a href="#" class="teacher_add-btn">
                        <p class="ph--chalkboard-teacher-light"></p>Teachers
                        <span class="fas fa-caret-down fifth"></span>
                    </a>
                    <ul class="teacher_add-show">
                        <li><a href="../teacherDashboard/manage_teacher.php">Teacher List</a></li>
                        <li><a href="../teacherDashboard/teacher_dashboard.php">My Profile</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="body-contents">
            <h1>Student List</h1>
            <div class="bread-crumb">
                <p><span class="fa-solid--home"></span> Home / Student / Student List</p>
            </div>
            <div class="dashboard">
                <p>View Students Info</p>
                <div class="datatable">
                    <table id="example" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>LRN / ID No.</th>
                                <th>Learner</th>
                                <th>Gender</th>
                                <th>Current Class</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            if ($result->num_rows > 0) {
                                $index = 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $index++ . "</td>"; // Increment index for the first column
                                    echo "<td>" . $row['lrn'] . "</td>";
                                    echo "<td>" . $row['name'] . "</td>";
                                    echo "<td>" . $row['gender'] . "</td>";
                                    echo "<td>" . $row['class'] . "</td>";
                                    echo "<td><a href='student_profile.php?id=" . $row['id'] . "' class='btn btn-primary'>Profile</a></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>LRN / ID No.</th>
                                <th>Learner</th>
                                <th>Gender</th>
                                <th>Current Class</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
            <div class="space">
                Hello World!
            </div>
        </div>

        <script src="../Assets/Javascript/main.js"></script>

    </body>

    </html>

    <?php
    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>
