<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

// Connect to the database
include "../db/db_conn.php";

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

// Retrieve teacher details based on the session user ID
$user_id = $_GET['id'];

// Query for teacher's personal details
$sqlTeacher = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sqlTeacher);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$resultTeacher = $stmt->get_result();
$teacher = $resultTeacher->fetch_assoc(); // Fetch teacher details

$teacher_id = $teacher['teacher_id'];

$profilePic = '';
if (!empty($teacher['teacher_pic'])) {
    $profilePic = 'data:image/jpeg;base64,' . base64_encode($teacher['teacher_pic']);
} else {
    $profilePic = '../Assets/images/profile.png'; // Default placeholder image
}

$sqlBackground = "SELECT
                  t.id,
                  t.level,
                  t.degree,
                  t.major,
                  t.minor,
                  t.units
                  FROM teacher_background t
                  WHERE t.teacher_id = ?";
$stmt = $conn->prepare($sqlBackground);
$stmt->bind_param('i', $teacher['teacher_id']);
$stmt->execute();
$resultBackground = $stmt->get_result();

// Fetch all records for educational background
$teacherBackgrounds = [];
while ($background = $resultBackground->fetch_assoc()) {
    $teacherBackgrounds[] = $background;
}

// Fetch all records for Class load
$sqlClass = "
    SELECT
        c.id AS class_id,
        c.level,
        c.section,
        SUM(CASE WHEN s.gender = 'Male' THEN 1 ELSE 0 END) AS male,
        SUM(CASE WHEN s.gender = 'Female' THEN 1 ELSE 0 END) AS female,
        COUNT(s.id) AS total
    FROM
        classes c
    LEFT JOIN
        studentinfo s ON c.id = s.class
    WHERE c.teacher_id = ?
    GROUP BY c.level, c.section";

$stmt = $conn->prepare($sqlClass);
$stmt->bind_param("i", $teacher['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $level_input = $_POST['levelInput'] ?? '';
    $degree_input = $_POST['degreeInput'] ?? '';
    $major_input = $_POST['majorInput'] ?? '';
    $minor_input = $_POST['minorInput'] ?? '';
    $unit_input = $_POST['unitsInput'] ?? '';

    // Insert new record into `teacher_background`
    $insertSql = "INSERT INTO teacher_background (teacher_id, level, degree, major, minor, units)
                 VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($insertSql)) {
        $stmt->bind_param(
            "isssss",
            $teacher_id, // Use `teacher_id` for the insert
            $level_input,
            $degree_input,
            $major_input,
            $minor_input,
            $unit_input
        );
        if ($stmt->execute()) {
            // Close the statement
            $stmt->close();

            // Redirect to the same page to prevent resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $messageType = "error";
            $messageText = "Error: Could not save record. " . $conn->error;
        }
    } else {
        $messageType = "error";
        $messageText = "Error: Could not prepare the SQL statement.";
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin</title>
    <link rel="stylesheet" type="text/css" href="../Assets/css/teacher_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../Assets/css/bootstrap.min.css">
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
        <h1>Teacher Profile</h1>
        <div class="bread-crumb">
            <p><span class="fa-solid--home"></span> Home / Teacher / My Profile</p>
        </div>
        <div class="notifications">

        </div>

        <div class="dashboard">
            <h2>Teacher Profile</h2>
            <div class="button-container">
                <button class="buttons">
                    <a href="edit_teacher.php?id=<?= $teacher['teacher_id']; ?>">
                        <span class="iconamoon--edit-light"></span></a></button>
            </div>
            <div class="form-container">
                <form class="teacher-form" method="POST">
                    <div class="photo-upload">
                        <div class="photo-placeholder" id="photo-placeholder">
                            <div class="photo-placeholder" id="photo-placeholder">
                                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture"
                                    id="profile-img">
                            </div>
                        </div>

                        <div class="student-info">
                            <?php if (isset($teacher)): ?>
                                <p id="student_name"><?php echo htmlspecialchars($teacher['last_name']); ?>,
                                    <?php echo htmlspecialchars($teacher['first_name']); ?>
                                </p>
                                <p> <span class="mdi--id-card-outline"></span>
                                    <?php echo htmlspecialchars($teacher['teacher_id']); ?> </p>
                                <p> <span class="ion--home"></span> <?php echo htmlspecialchars($teacher['residence']); ?>
                                </p>
                            <?php elseif (isset($error_message)): ?>
                                <div class="alert alert-danger">
                                    <?php echo htmlspecialchars($error_message); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <nav>
                        <div class="nav nav-tabs" id="myTab" role="tablist">
                            <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home"
                                aria-selected="true">Teacher Information</button>
                            <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile"
                                aria-selected="false">Educational Background</button>
                            <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact"
                                aria-selected="false">Current Class Loads</button>

                        </div>
                    </nav>
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                            aria-labelledby="nav-home-tab" tabindex="0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="table-light">Personal Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>Field</th>
                                        <th>Details</th>
                                    </tr>
                                    <tr>
                                        <td>Teacher ID</td>
                                        <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>First Name</td>
                                        <td><?php echo htmlspecialchars($teacher['first_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Last Name</td>
                                        <td><?php echo htmlspecialchars($teacher['last_name']); ?></td>
                                    </tr>

                                    <tr>
                                        <td>Gender</td>
                                        <td><?php echo htmlspecialchars($teacher['gender']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Birthdate</td>
                                        <td>
                                            <?php
                                            // Assuming $student['bdate'] is in 'YYYY-MM-DD'
                                            $birthDate = new DateTime($teacher['birthdate']);
                                            $today = new DateTime(); // Current date
                                            $age = $today->diff($birthDate)->y; // Difference in years

                                            // Display formatted birthdate and age
                                            echo date("F d, Y", strtotime($teacher['birthdate'])) . " (" . $age . " years old)";
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Residence</td>
                                        <td><?php echo htmlspecialchars($teacher['residence']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Civil Status</td>
                                        <td><?php echo htmlspecialchars($teacher['civil_status']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Phone</td>
                                        <td><?php echo htmlspecialchars($teacher['contact_num']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td><?php echo htmlspecialchars($teacher['email_add']); ?></td>
                                    </tr>

                                </tbody>
                            </table>

                        </div>
                        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab"
                            tabindex="0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th colspan="5" class="table-light">
                                            Educational Background
                                        </th>
                                        <th class="table-light">
                                            <!-- Button trigger modal -->
                                            <button type="button" id="add-btn" class="btn btn-primary"
                                                data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                                <span class="mingcute--add-fill"></span>
                                            </button>

                                            <!-- Modal -->
                                            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static"
                                                data-bs-keyboard="false" tabindex="-1"
                                                aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Add
                                                                Entry</h1>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Add `action` and `method` attributes -->
                                                            <form id="entryForm" method="POST" action="">
                                                                <!-- Level Input -->
                                                                <div class="mb-3">
                                                                    <label for="levelInput"
                                                                        class="form-label">Level</label>
                                                                    <select class="form-select" id="levelInput"
                                                                        name="levelInput" required>
                                                                        <option value="" disabled selected>Select Level
                                                                        </option>
                                                                        <option value="Elementary">Elementary</option>
                                                                        <option value="Secondary">Secondary</option>
                                                                        <option value="Vocational">Vocational</option>
                                                                        <option value="Tertiary">Tertiary</option>
                                                                        <option value="Masteral">Masteral</option>
                                                                        <option value="Doctoral">Doctoral</option>
                                                                    </select>
                                                                </div>
                                                                <!-- Degree Input -->
                                                                <div class="mb-3">
                                                                    <label for="degreeInput"
                                                                        class="form-label">Degree</label>
                                                                    <input type="text" class="form-control"
                                                                        id="degreeInput" name="degreeInput"
                                                                        placeholder="Enter Degree" required>
                                                                </div>
                                                                <!-- Major Input -->
                                                                <div class="mb-3">
                                                                    <label for="majorInput"
                                                                        class="form-label">Major</label>
                                                                    <input type="text" class="form-control"
                                                                        id="majorInput" name="majorInput"
                                                                        placeholder="Enter Major">
                                                                </div>
                                                                <!-- Minor Input -->
                                                                <div class="mb-3">
                                                                    <label for="minorInput"
                                                                        class="form-label">Minor</label>
                                                                    <input type="text" class="form-control"
                                                                        id="minorInput" name="minorInput"
                                                                        placeholder="Enter Minor">
                                                                </div>
                                                                <!-- Units Input -->
                                                                <div class="mb-3">
                                                                    <label for="unitsInput"
                                                                        class="form-label">Units</label>
                                                                    <select class="form-select" id="unitsInput"
                                                                        name="unitsInput" required>
                                                                        <option value="" disabled selected>Select Units
                                                                        </option>
                                                                        <option value="Graduated">Graduated</option>
                                                                        <!-- Generate options dynamically for 3 units to 99 units -->
                                                                        <?php
                                                                        for ($i = 3; $i <= 99; $i++) {
                                                                            echo "<option value=\"$i\">$i units</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <!-- Change this button type to `submit` -->
                                                            <button type="submit" class="btn btn-primary"
                                                                id="saveEntryButton">Save Entry</button>
                                                        </div>
                </form>
            </div>
        </div>
    </div>

    </th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <th>level</th>
            <th>Degree</th>
            <th>Major</th>
            <th>Minor</th>
            <th>Units</th>
            <th>Action</th>
        </tr>
        <tr>
            <?php if (!empty($teacherBackgrounds)): ?>
                <?php foreach ($teacherBackgrounds as $background): ?>
                <tr>
                    <td><?php echo htmlspecialchars($background['level']); ?></td>
                    <td><?php echo htmlspecialchars($background['degree']); ?></td>
                    <td><?php echo htmlspecialchars($background['major']); ?></td>
                    <td><?php echo htmlspecialchars($background['minor']); ?></td>
                    <td><?php echo htmlspecialchars($background['units']); ?></td>
                    <td>
                        <button class="delete-btn btn btn-danger" data-id="<?php echo $background['id']; ?>">Delete</button>
                    </td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center; vertical-align: middle;">
                    No educational background records found.
                </td>
            </tr>
        <?php endif; ?>
        </tr>

    </tbody>
    </table>
    </div>

    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab" tabindex="0">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th colspan="6" class="table-light">Current Class Load</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Level</th>
                    <th>Section</th>
                    <th>Male</th>
                    <th>Female</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['level']); ?></td>
                            <td><?php echo htmlspecialchars($row['section']); ?></td>
                            <td><?php echo htmlspecialchars($row['male']); ?></td>
                            <td><?php echo htmlspecialchars($row['female']); ?></td>
                            <td><?php echo htmlspecialchars($row['total']); ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm submitGradesButton"
                                    data-level-id="<?php echo $row['level']; ?>"
                                    data-section-id="<?php echo $row['section']; ?>"
                                    data-class-id="<?php echo $row['class_id']; ?>" data-bs-toggle="modal"
                                    data-bs-target="#recordModal">
                                    View Class Record
                                </button>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; vertical-align: middle;">No class load records found.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
        <div class="modal fade" id="recordModal" tabindex="-1" aria-labelledby="recordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="recordModalLabel">Class Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6 id="classInfo"></h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="studentList">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <div class="tab-pane fade" id="nav-disabled" role="tabpanel" aria-labelledby="nav-disabled-tab" tabindex="0">...
    </div>
    </div>

    </form>
    </div>
    </div>

    </div>
    <script src="../Assets/Javascript/main.js"></script>
    <script src="../Assets/Javascript/notification.js"></script>
    <script src="../Assets/Javascript/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function () {
                const backgroundId = this.getAttribute('data-id');

                if (confirm('Are you sure you want to delete this record?')) {
                    fetch('delete_background.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `background_id=${backgroundId}`,
                    })
                        .then(response => response.text())
                        .then(data => {
                            alert(data);
                            // Refresh the page or remove the row from the table
                            if (data.includes('Record deleted successfully')) {
                                this.closest('tr').remove();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the record.');
                        });
                }
            });
        });

        document.querySelectorAll('.submitGradesButton').forEach(button => {
            button.addEventListener('click', function () {
                // Adjust the selector for the correct column containing `class_id`
                const classID = this.getAttribute('data-class-id');
                const classLevel = this.getAttribute('data-level-id');
                const classSection = this.getAttribute('data-section-id');
                // Debugging: Log the class ID
                console.log(`Class ID: ${classID}`);

                // Set class info in modal
                document.getElementById('classInfo').textContent = `${classLevel} - ${classSection}`;

                // Clear existing student list
                const studentList = document.getElementById('studentList');
                studentList.innerHTML = '';

                // Fetch student data via AJAX
                fetch('../teacherDashboard/fetch_studentList.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `class_id=${classID}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach((student, index) => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
        <td>${index + 1}</td>
        <td>${student.full_name}</td>
        <td>
            <!-- Link to reportcard.php with the student_id -->
            <a href="../teacherDashboard/reportcard.php?id=${student.student_id}" target="_blank">
                <button type="button" class="btn btn-primary btn-sm">Print Card</button>
            </a>
        </td>
    `;
                                studentList.appendChild(row);
                            });

                        } else {
                            const row = document.createElement('tr');
                            row.innerHTML = '<td colspan="4" style="text-align: center; vertical-align: middle;">No students enrolled in this class.</td>';
                            studentList.appendChild(row);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });




    </script>

</body>

</html>
