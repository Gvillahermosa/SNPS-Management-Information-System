<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['user_name'])) {
    header("Location: ../index.php");
    exit();
}

// Get the current year and month
$currentYear = date("Y");
$currentMonth = date("n");

// Determine the school year and semester
if ($currentMonth < 6) {
    $startYear = $currentYear - 1;
    $endYear = $currentYear;
    $semester = "2nd semester";
} else {
    $startYear = $currentYear;
    $endYear = $currentYear + 1;
    $semester = "1st semester";
}

$schoolYear = $startYear . " - " . $endYear . " , " . $semester;


// Check if 'id' is set in the query string
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']); // Sanitize input to prevent SQL injection

    // Database connection
    $conn = new mysqli("localhost", "root", "", "studentgradingsystem");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve teacher details based on session user ID
    $user_id = $_SESSION['id'];
    $sqlTeacher = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sqlTeacher);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $resultTeacher = $stmt->get_result();
    $teacher = $resultTeacher->fetch_assoc();
    $teacher_id = $teacher['teacher_id'];
    $stmt->close();

    // Fetch student details
    $sql = "
        SELECT s.id, s.lrn, s.lname, s.fname, s.midname, s.suffix,
               CONCAT(s.lname, ', ', s.fname) AS name, s.gender,
               CONCAT(s.mother_fname, ' ', s.mother_lname) AS mothers_name,
               CONCAT(s.father_fname, ' ', s.father_lname) AS fathers_name,
               CONCAT(s.guardian_fname, ' ', s.guardian_lname) AS guardians_name,
               s.bdate, s.residence, s.studentpic, c.level, s.guardian_contactnum,
               c.section, CONCAT(c.level, ' - ', c.section) AS class
        FROM studentinfo s
        JOIN classes c ON s.class = c.id
        WHERE s.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        $error_message = "Student not found.";
    }
    $stmt->close();

    // Fetch grades using the student's id
    $sqlGrades = "
     SELECT
         sub.subject_name AS subject,
         sg.q1, sg.q2, sg.q3, sg.q4, sg.final_grade,
        CONCAT(u.first_name, ' ', u.last_name) AS teacher_name
     FROM student_grades sg
     JOIN subjects sub ON sg.subject_id = sub.id
     JOIN classes c ON sg.class_id = c.id
     JOIN users u ON c.teacher_id = u.id
     WHERE sg.student_id = ?
     ORDER BY sub.subject_name
 ";
    $stmtGrades = $conn->prepare($sqlGrades);
    $stmtGrades->bind_param("i", $student_id);
    $stmtGrades->execute();
    $resultGrades = $stmtGrades->get_result();

    $grades = [];
    while ($row = $resultGrades->fetch_assoc()) {
        // Add letter grade calculation for each quarter
        $row['q1_letter'] = getLetterGrade($row['q1']);
        $row['q2_letter'] = getLetterGrade($row['q2']);
        $row['q3_letter'] = getLetterGrade($row['q3']);
        $row['q4_letter'] = getLetterGrade($row['q4']);
        $row['final_letter'] = getLetterGrade($row['final_grade']);
        $grades[] = $row;
    }
    $stmtGrades->close();

    // Debugging: Check if grades are empty
    if (empty($grades)) {
        $error_message = "No grades found for this student.";
    }
} else {
    $error_message = "No student selected.";
}

// Function to determine the letter grade
function getLetterGrade($grade)
{
    if ($grade >= 96 && $grade <= 100) {
        return 'A+';
    } elseif ($grade >= 90 && $grade <= 95) {
        return 'A';
    } elseif ($grade >= 80 && $grade <= 89) {
        return 'B';
    } elseif ($grade >= 75 && $grade <= 79) {
        return 'C';
    } else {
        return 'D';
    }
}


// Default profile picture path
$defaultProfilePic = '../Assets/images/profile.png'; // Ensure this path is correct

// Prepare the profile picture variable
if (!empty($student['studentpic'])) {
    $profilePic = 'data:image/jpeg;base64,' . base64_encode($student['studentpic']);
} else {
    $profilePic = $defaultProfilePic; // Use default if no custom pic
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileBinary = file_get_contents($fileTmpPath);

        // Validate file type
        $fileType = mime_content_type($fileTmpPath);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Invalid file type. Only JPG, PNG, and GIF are allowed.');</script>";
            exit();
        }

        // Database update
        $sql = "UPDATE studentinfo SET studentpic = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $fileBinary, $student_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "<script>alert('Profile picture updated successfully!');</script>";
                $profilePic = 'data:image/jpeg;base64,' . base64_encode($fileBinary);
            } else {
                echo "<script>alert('No changes were made.');</script>";
            }
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please select a valid file.');</script>";
    }
}

// Close the connection after all operations
$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin</title>
    <link rel="stylesheet" type="text/css" href="../Assets/css/student_profile.css">
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
        <h1>Student Profile</h1>
        <div class="bread-crumb">
            <p><span class="fa-solid--home"></span> Home / Student / Student Profile</p>
        </div>
        <div class="notifications">

        </div>

        <div class="dashboard">
            <h2>Student Profile</h2>
            <div class="button-container">
                <form method="POST" enctype="multipart/form-data">
                    <button class="buttons" name="upload">

                        <div class="upload-btn">
                            <input type="file" id="profile-picture" name="profile_picture" accept="image/*" required>
                            <label for="profile-picture"><span class="icon-park--upload-picture"></span></label>
                        </div>

                    </button>
                </form>
                <button class="buttons" onclick="printID()">
                    <span class="stash--user-id"></span> </button>
                <button class="buttons">
                    <a href="editstudent.php?id=<?= $student['id']; ?>">
                        <span class="iconamoon--edit-light"></span></a></button>

            </div>
            <div class="form-container">
                <form class="student-form" method="POST">
                    <div class="photo-upload">

                        <div class="photo-placeholder" id="photo-placeholder">
                            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture"
                                id="profile-img">

                        </div>

                        <div class="student-info">
                            <?php if (isset($student)): ?>
                                <p id="student_name"> <?php echo htmlspecialchars($student['name']); ?> </p>
                                <p> <span class="mdi--id-card-outline"></span>
                                    <?php echo htmlspecialchars($student['lrn']); ?> </p>
                                <p> <span class="ion--home"></span> <?php echo htmlspecialchars($student['residence']); ?>
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
                                aria-selected="true">Student Information</button>
                            <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile"
                                aria-selected="false">Performance</button>
                            <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact"
                                aria-selected="false">Class Enrolled</button>

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
                                        <th>Fields</th>
                                        <th>Details</th>
                                    </tr>
                                    <tr>
                                        <td>LRN</td>
                                        <td><?php echo htmlspecialchars($student['lrn']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Last name</td>
                                        <td><?php echo htmlspecialchars($student['lname']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>First name</td>
                                        <td><?php echo htmlspecialchars($student['fname']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Middle name</td>
                                        <td><?php echo htmlspecialchars($student['midname']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Ext. name</td>
                                        <td><?php echo htmlspecialchars($student['suffix']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Gender</td>
                                        <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Date of Birth</td>
                                        <td>
                                            <?php
                                            // Assuming $student['bdate'] is in 'YYYY-MM-DD'
                                            $birthDate = new DateTime($student['bdate']);
                                            $today = new DateTime(); // Current date
                                            $age = $today->diff($birthDate)->y; // Difference in years

                                            // Display formatted birthdate and age
                                            echo date("F d, Y", strtotime($student['bdate'])) . " (" . $age . " years old)";
                                            ?>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Current residence</td>
                                        <td><?php echo htmlspecialchars($student['residence']); ?></td>
                                    </tr>
                                </tbody>
                            </table>



                            <table class="table table-striped table-hover" , style="margin-top: 40px">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="table-light">Contact Information</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th style="width: 36.5%;">Fields</th>
                                        <th>Details</th>
                                    </tr>
                                    <tr>
                                        <td>Mother's maiden name</td>
                                        <td><?php echo htmlspecialchars($student['mothers_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Father's name</td>
                                        <td><?php echo htmlspecialchars($student['fathers_name']); ?></td>
                                    </tr>
                                    <tr style="height: 35px;">
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Guardian's name</td>
                                        <td><?php echo htmlspecialchars($student['guardians_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Guardian's contact number</td>
                                        <td><?php echo htmlspecialchars($student['guardian_contactnum']); ?></td>
                                    </tr>

                                </tbody>
                            </table>

                        </div>
                        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab"
                            tabindex="0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th colspan="7" class="table-light">
                                            <?php echo htmlspecialchars($student['class']); ?> |
                                            <?php echo $schoolYear; ?>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>Subject</th>
                                        <th style="width: 100px;">1st</th>
                                        <th style="width: 100px;">2nd</th>
                                        <th>3rd</th>
                                        <th>4th</th>
                                        <th>Final</th>
                                        <th>Teacher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($grades)): ?>
                                        <?php foreach ($grades as $grade): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($grade['subject']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['q1_letter']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['q2_letter']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['q3_letter']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['q4_letter']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['final_letter']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['teacher_name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No grades available for this student.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab"
                            tabindex="0">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th colspan="3" class="table-light">Enrollment History</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>School year</th>
                                        <th>level</th>
                                        <th>Section</th>
                                    </tr>
                                    <tr>
                                        <td><?php echo $schoolYear; ?></td>
                                        <td><?php echo htmlspecialchars($student['level']); ?></td>
                                        <td><?php echo htmlspecialchars($student['section']); ?></td>
                                    </tr>

                                </tbody>
                            </table>


                        </div>
                        <div class="tab-pane fade" id="nav-disabled" role="tabpanel" aria-labelledby="nav-disabled-tab"
                            tabindex="0">...</div>
                    </div>

                </form>
            </div>
        </div>

    </div>
    <script src="../Assets/Javascript/main.js"></script>
    <script src="../Assets/Javascript/notification.js"></script>
    <script src="../Assets/Javascript/bootstrap.bundle.min.js"></script>
</body>

</html>
