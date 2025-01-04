<?php
session_start();

$messageType = "";
$messageText = "";

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {
    // Enable error reporting for debugging (remove in production)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Database connection
    $conn = new mysqli("localhost", "root", "", "studentgradingsystem");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

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





    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $levelSection = isset($_POST['level']) ? trim($_POST['level']) : null;
        $subjects = isset($_POST['subject']) ? $_POST['subject'] : [];

        if (!empty($levelSection) && strpos($levelSection, ' - ') !== false) {
            list($level, $section) = explode(' - ', $levelSection);

            foreach ($subjects as $subject) {
                if (!empty(trim($subject))) {
                    $sqlInsert = "INSERT INTO subjects (level, section, subject_name) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sqlInsert);

                    if (!$stmt) {
                        echo "Error preparing statement: " . $conn->error;
                        continue;
                    }

                    $stmt->bind_param("sss", $level, $section, $subject);

                    if (!$stmt->execute()) {

                        echo "Error executing statement: " . $stmt->error . "<br>";
                    } else {
                        $messageType = "success";
                        $messageText = "Subjects added successfully!";

                    }

                    $stmt->close();
                }
            }
        } else {
            echo "<script>alert('Please select a valid level and section!');</script>";
        }
    }

    // Fetch classes for the form
    $sql = "SELECT id, level, section FROM classes";
    $result = $conn->query($sql);

    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" type="text/css" href="../Assets/css/create_subject.css">
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
        <h1>Create Subject</h1>
        <div class="bread-crumb">
            <p><span class="fa-solid--home"></span> Home / Student / Create Subject</p>
        </div>

        <div class="notifications">

        </div>

        <div class="dashboard">
            <div class="form-container">
                <h3>Create Subject</h3>


                <form id="subjectForm" method="POST" action="">
                    <div class="form-group">
                        <label for="level">Level</label>
                        <select id="level" name="level" required>
                            <option value="">--Select Level--</option>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($class = $result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($class['level'] . ' - ' . $class['section'], ENT_QUOTES, 'UTF-8') . "'>"
                                        . htmlspecialchars($class['level'], ENT_QUOTES, 'UTF-8') . " - "
                                        . htmlspecialchars($class['section'], ENT_QUOTES, 'UTF-8') . "</option>";
                                }
                            } else {
                                echo "<option value=''>No classes available</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <div id="subjectList">
                            <input type="text" name="subject[]" placeholder="Enter subject" required>
                        </div>
                        <button type="button" class="add-button" onclick="addSubjectField()">+</button>
                    </div>

                    <button type="submit" id="save-btn">Save</button>
                </form>
            </div>

        </div>
    </div>
    <script>
        const messageType = "<?php echo $messageType; ?>";
        const messageText = "<?php echo $messageText; ?>";
    </script>
    <script>
        // Function to dynamically add a new subject input field
        function addSubjectField() {
            // Create a new input field
            const newInput = document.createElement("input");
            newInput.type = "text";
            newInput.name = "subject[]";
            newInput.placeholder = "Enter subject";
            newInput.required = true;

            // Add the input to the subject container
            const subjectList = document.getElementById("subjectList");
            subjectList.appendChild(newInput);
        }
    </script>
    <script src="../Assets/Javascript/notification.js"></script>
    <script src="../Assets/Javascript/main.js"></script>


</body>

</html>
