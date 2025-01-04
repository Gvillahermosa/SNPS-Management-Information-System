<?php
session_start();
include "../db/db_conn.php";

$messageType = "";
$messageText = "";

$classSql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, role FROM users WHERE role='teacher'";
$classResult = $conn->query($classSql);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $level = $_POST['level'];
    $section = $_POST['section'];


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


    // Split the teacher data into ID and name
    list($teacher_id, $teacher) = explode('|', $_POST['teacher_data']);

    $checkSql = "SELECT id FROM classes WHERE level = ? AND section = ?";
    if ($stmt = $conn->prepare($checkSql)) {
        $stmt->bind_param("ss", $level, $section);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $messageType = "error";
            $messageText = "Class already exists.";
        } else {
            $stmt->close();

            $insertSql = "INSERT INTO classes (section, level, teacher, teacher_id) VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($insertSql)) {
                $stmt->bind_param("sssi", $section, $level, $teacher, $teacher_id);

                if ($stmt->execute()) {
                    $messageType = "success";
                    $messageText = "Class created successfully!";
                } else {
                    $messageType = "error";
                    $messageText = "Error: Could not create class. " . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

?>
<!DOCTYPE html>

<html>

<head>
    <title>Admin</title>
    <link rel="stylesheet" type="text/css" href="../Assets/css/createclass.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <h1>Create Class</h1>
        <div class="bread-crumb">
            <p><span class="fa-solid--home"></span> Home / Student / Create Class</p>
        </div>
        <div class="notifications">

        </div>
        <div class="dashboard">
            <p>Fill the Class Info</p>
            <div class="form-container">
                <form class="student-form" method="POST">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="level">Level</label>
                            <input type="text" id="level" name="level" class="form-control" required>
                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label for="section">Section</label>
                            <input type="text" id="section" name="section" class="form-control" required>
                            </select>
                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label for="teacher_name">Teacher</label>
                            <select id="teacher_name" name="teacher_data" class="form-control" required>
                                <option value="">Select Teacher for this class</option>
                                <?php
                                if ($classResult->num_rows > 0) {
                                    while ($class = $classResult->fetch_assoc()) {
                                        echo "<option value='" . $class['id'] . "|" . $class['name'] . "'>" . $class['name'] . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>No Teachers available</option>";
                                }
                                ?>
                            </select>

                        </div>

                    </div>
                    <button type="submit"> Save </button>
                </form>
            </div>
        </div>

    </div>



    <script>
        const messageType = "<?php echo $messageType; ?>";
        const messageText = "<?php echo $messageText; ?>";
    </script>

    <script src="../Assets/Javascript/main.js"></script>
    <script src="../Assets/Javascript/notification.js"></script>
</body>

</html>
