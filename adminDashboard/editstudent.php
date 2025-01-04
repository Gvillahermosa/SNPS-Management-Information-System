<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
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


// Include your database connection file
require_once '../db/db_conn.php';

// Initialize variables for messages
$success_message = '';
$error_message = '';

// Check if the student ID is provided
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Fetch the student details
    $sql = "
        SELECT s.id, s.lrn, s.lname, s.fname, s.midname, s.suffix, s.gender,
               s.bdate, s.residence, s.studentpic, c.level, c.section,
               CONCAT(c.level, ' - ', c.section) AS class,
               s.mother_fname, s.mother_midname, s.mother_lname,
               s.father_fname, s.father_midname, s.father_lname,
               s.guardian_fname, s.guardian_midname, s.guardian_lname,
               s.guardian_contactnum, s.guardian_relationship
        FROM studentinfo s
        LEFT JOIN classes c ON s.class = c.id
        WHERE s.id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the student exists
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        $error_message = "Student not found.";
    }
    $stmt->close();

    // Fetch class options for the dropdown
    $class_sql = "SELECT * FROM classes";
    $classResult = $conn->query($class_sql);

    // Handle form submission for updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        // Get updated data from the form and sanitize inputs
        $lrn = $_POST['lrn'];
        $fname = $_POST['firstname'];
        $lname = $_POST['lastname'];
        $midname = $_POST['middlename'];
        $suffix = $_POST['suffix'];
        $gender = $_POST['gender'];
        $bdate = $_POST['birthdate'];
        $residence = $_POST['residence'];
        $mother_fname = $_POST['mother_fname'];
        $mother_midname = $_POST['mother_midname'];
        $mother_lname = $_POST['mother_lname'];
        $father_fname = $_POST['father_fname'];
        $father_midname = $_POST['father_midname'];
        $father_lname = $_POST['father_lname'];
        $guardian_fname = $_POST['guardian_fname'];
        $guardian_midname = $_POST['guardian_midname'];
        $guardian_lname = $_POST['guardian_lname'];
        $guardian_contactnum = $_POST['guardian_contactnum'];
        $guardian_relationship = $_POST['guardian_relationship'];
        $class_id = $_POST['class'];

        // Update student information
        $update_sql = "UPDATE studentinfo SET
                       lrn = ?,
                       fname = ?,
                       lname = ?,
                       midname = ?,
                       suffix = ?,
                       gender = ?,
                       bdate = ?,
                       residence = ?,
                       mother_fname = ?,
                       mother_midname = ?,
                       mother_lname = ?,
                       father_fname = ?,
                       father_midname = ?,
                       father_lname = ?,
                       guardian_fname = ?,
                       guardian_midname = ?,
                       guardian_lname = ?,
                       guardian_contactnum = ?,
                       guardian_relationship = ?,
                       class = ?
                       WHERE id = ?";

        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param(
            'isssssssssssssssssiii',
            $lrn,
            $fname,
            $lname,
            $midname,
            $suffix,
            $gender,
            $bdate,
            $residence,
            $mother_fname,
            $mother_midname,
            $mother_lname,
            $father_fname,
            $father_midname,
            $father_lname,
            $guardian_fname,
            $guardian_midname,
            $guardian_lname,
            $guardian_contactnum,
            $guardian_relationship,
            $class_id,
            $student_id
        );

        if ($stmt->execute()) {
            echo "<script>alert('Student information updated successfully.');</script>";

            // Refresh the student data after update
            $stmt->close();

            // Fetch the updated student details
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $student = $result->fetch_assoc();
                echo "<script>alert('Student profile updated successfully!');</script>";
            }

            $stmt->close();
        } else {
            $error_message = "Failed to update student information: " . $stmt->error;
        }
    }
} else {
    $error_message = "Student ID not specified.";
}

$conn->close();
?>

<!DOCTYPE html>

<html>

<head>
    <title>Admin</title>
    <link rel="stylesheet" type="text/css" href="../Assets/css/editstudent.css">
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
        <h1>Update Student</h1>
        <div class="bread-crumb">
            <p><span class="fa-solid--home"></span> Home / Student / Student Profile / Update Profile</p>
        </div>
        <div class="notifications">

        </div>
        <div class="dashboard">
            <p>Fill the Student Info</p>
            <div class="form-container">
                <form class="student-form" method="POST">
                    <div class="form-group">
                        <label for="lrn">LRN / ID No.</label>
                        <input type="text" id="lrn" name="lrn" class="lrn" required
                            value="<?= htmlspecialchars($student['lrn'] ?? ''); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First name</label>
                            <input type="text" id="firstname" name="firstname" class="form-control"
                                value="<?= htmlspecialchars($student['fname']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="middlename">Middle name (Dash "-" for none)</label>
                            <input type="text" id="middlename" name="middlename" class="form-control"
                                value="<?= htmlspecialchars($student['midname']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last name</label>
                            <input type="text" id="lastname" name="lastname" class="form-control"
                                value="<?= htmlspecialchars($student['lname']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <select id="suffix" name="suffix" class="form-control">
                                <option value="" <?= empty($student['suffix']) ? 'selected' : ''; ?>>None</option>
                                <option value="jr" <?= $student['suffix'] === 'jr' ? 'selected' : ''; ?>>Jr.</option>
                                <option value="sr" <?= $student['suffix'] === 'sr' ? 'selected' : ''; ?>>Sr.</option>
                                <option value="iii" <?= $student['suffix'] === 'iii' ? 'selected' : ''; ?>>III</option>
                                <option value="iv" <?= $student['suffix'] === 'iv' ? 'selected' : ''; ?>>IV</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <option value="" <?= empty($student['gender']) ? 'selected' : ''; ?>>Select Gender
                                </option>
                                <option value="male" <?= $student['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?= $student['gender'] === 'female' ? 'selected' : ''; ?>>Female
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="birthdate">Birth date</label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control"
                                placeholder="MM/DD/YYYY" value="<?= htmlspecialchars($student['bdate']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="residence">Residence</label>
                            <input type="text" id="residence" name="residence" class="form-control"
                                value="<?= htmlspecialchars($student['residence']); ?>">
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="class">Class</label>
                            <select id="class" name="class" class="form-control" required>
                                <?php while ($class = $classResult->fetch_assoc()): ?>
                                    <option value="<?= $class['id']; ?>" <?= ($class['id'] == $student['class']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($class['level'] . ' - ' . $class['section']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="credentials-section">
                        <h3>Mother's maiden name</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Last name</label>
                                <input type="text" id="mother_lname" name="mother_lname" class="form-control" required
                                    value="<?= htmlspecialchars($student['mother_lname']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="password">First name</label>
                                <div class="form-group">
                                    <input type="text" id="mother_fname" name="mother_fname" class="form-control"
                                        required value="<?= htmlspecialchars($student['mother_fname']); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Middle name</label>
                                <input type="text" id="mother_midname" name="mother_midname" class="form-control"
                                    required value="<?= htmlspecialchars($student['mother_midname']); ?>">
                            </div>
                        </div>
                    </div>
                    <!-- Father's Information -->
                    <div class="credentials-section">
                        <h3>Father's name</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Last name</label>
                                <input type="text" id="father_lname" name="father_lname" class="form-control"
                                    value="<?= htmlspecialchars($student['father_lname']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="password">First name</label>
                                <input type="text" id="father_fname" name="father_fname" class="form-control"
                                    value="<?= htmlspecialchars($student['father_fname']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Middle name</label>
                                <input type="text" id="father_midname" name="father_midname" class="form-control"
                                    value="<?= htmlspecialchars($student['father_midname']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Guardian's Information -->
                    <div class="credentials-section">
                        <h3>Guardian's Name</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Last name</label>
                                <input type="text" id="guardian_lname" name="guardian_lname" class="form-control"
                                    value="<?= htmlspecialchars($student['guardian_lname']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="password">First name</label>
                                <input type="text" id="guardian_fname" name="guardian_fname" class="form-control"
                                    value="<?= htmlspecialchars($student['guardian_fname']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Middle name</label>
                                <input type="text" id="guardian_midname" name="guardian_midname" class="form-control"
                                    value="<?= htmlspecialchars($student['guardian_midname']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="guardian_contactnum">Contact Number</label>
                                <input type="text" id="guardian_contactnum" name="guardian_contactnum"
                                    class="form-control-contact_num"
                                    value="<?= htmlspecialchars($student['guardian_contactnum'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="guardian_relationship" id="relationship_label">Relationship</label>
                                <select id="guardian_relationship" name="guardian_relationship"
                                    class="form-control-relationship" required>
                                    <?php if (!empty($student['guardian_relationship'])): ?>
                                        <!-- If guardian relationship exists in the database, show the existing value -->
                                        <option value="" <?= empty($student['guardian_relationship']) ? 'selected' : ''; ?>>
                                            None</option>
                                        <option value="jr" <?= $student['suffix'] === 'Parent' ? 'selected' : ''; ?>>Jr.
                                        </option>
                                        <option value="sr" <?= $student['suffix'] === 'Relative' ? 'selected' : ''; ?>>Sr.
                                        </option>
                                        <option value="iii" <?= $student['suffix'] === 'Non relative' ? 'selected' : ''; ?>>III
                                        </option>
                                        selected>
                                        <?= htmlspecialchars($student['guardian_relationship']); ?>
                                        </option>
                                    <?php else: ?>
                                        <!-- If guardian relationship is empty, show default options -->
                                        <option value="">Select Relationship</option>
                                        <option value="Parent">Parent</option>
                                        <option value="Relative">Relative</option>
                                        <option value="Non-relative">Non-relative</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update" id="save-btn"> Save </button>
                </form>
                <a href="student_profile.php?id=<?= $student['id']; ?>" class="btn btn-primary">
                    <button type="submit" id="back-btn"> Back to Student Profile </button></a>
                <div class="space">
                    Hello World!
                </div>
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
