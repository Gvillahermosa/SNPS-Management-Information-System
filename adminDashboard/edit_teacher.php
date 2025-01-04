<?php
session_start();
include "../db/db_conn.php";

$messageType = "";
$messageText = "";

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
    if (isset($_GET['id'])) {
        $teacher_id = intval($_GET['id']);
        // Fetch teacher details
        $sql = "SELECT * FROM users u WHERE u.teacher_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $teacher = $result->fetch_assoc();
        } else {
            $messageType = "error";
            $messageText = "Teacher not found.";
        }
        $stmt->close(); // Close the statement, but not the connection
    } else {
        $messageType = "error";
        $messageText = "No teacher selected.";
    }
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $suffix = $_POST['suffix'] ?? null;
    $gender = $_POST['gender'] ?? '';
    $birthdate = $_POST['birthdate'] ?? null;
    $residence = $_POST['residence'] ?? '';
    $civil_status = $_POST['role'] ?? '';
    $contact_num = $_POST['phone_num'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $username = $_POST['username'] ?? '';

    // Check if password and confirm password match
    if (!empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            $update_sql = "UPDATE users SET
                            first_name = ?,
                            mid_name = ?,
                            last_name = ?,
                            suffix = ?,
                            gender = ?,
                            birthdate = ?,
                            residence = ?,
                            civil_status = ?,
                            contact_num = ?,
                            email_add = ?,
                            user_name = ?,
                            password =?
                           WHERE teacher_id = ?";

            if ($stmt = $conn->prepare($update_sql)) {
                $stmt->bind_param(
                    "ssssssssssssi",
                    $firstname,
                    $middlename,
                    $lastname,
                    $suffix,
                    $gender,
                    $birthdate,
                    $residence,
                    $civil_status,
                    $contact_num,
                    $email,
                    $username,
                    $password,
                    $teacher_id
                );

                if ($stmt->execute()) {
                    $messageType = "success";
                    $messageText = "Teacher information updated successfully.";

                    // Fetch updated data
                    $sql = "SELECT * FROM users WHERE teacher_id = ?";
                    if ($fetch_stmt = $conn->prepare($sql)) {
                        $fetch_stmt->bind_param("i", $teacher_id);
                        $fetch_stmt->execute();
                        $result = $fetch_stmt->get_result();
                        if ($result->num_rows > 0) {
                            $teacher = $result->fetch_assoc();
                        }
                        $fetch_stmt->close();
                    }
                } else {
                    $messageType = "error";
                    $messageText = "Failed to update teacher information: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $messageType = "error";
                $messageText = "Failed to prepare the statement: " . $conn->error;
            }
        } else {
            $messageType = "error";
            $messageText = "Passwords do not match. Please re-enter.";
        }
    } else {
        $messageType = "error";
        $messageText = "Password and Confirm Password fields cannot be empty.";
    }
}
?>

<!DOCTYPE html>

<html>

<head>
    <title>SNPS MIS</title>
    <link rel="stylesheet" type="text/css" href="../Assets/css/edit_teacher.css">
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
        <h1>Update Profile</h1>
        <?php if (!empty($error_message))
            echo "<p style='color:red;'>$error_message</p>"; ?>
        <div class="bread-crumb">
            <p><span class="fa-solid--home"></span>Home / Teacher / My Profile / Update Profile</p>
        </div>
        <div class="notifications">

        </div>
        <div class="dashboard">
            <h2>Update Teacher Info</h2>
            <div class="form-container">
                <form class="student-form" method="POST">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First name</label>
                            <input type="text" id="firstname" name="firstname" class="form-control"
                                value="<?= htmlspecialchars($teacher['first_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="middlename">Middle name (Dash "-" for none)</label>
                            <input type="text" id="middlename" name="middlename" class="form-control"
                                value="<?= htmlspecialchars($teacher['mid_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last name</label>
                            <input type="text" id="lastname" name="lastname" class="form-control"
                                value="<?= htmlspecialchars($teacher['last_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <select id="suffix" name="suffix" class="form-control">
                                <option value="" <?= empty($teacher['suffix']) ? 'selected' : ''; ?>>None</option>
                                <option value="JR" <?= $teacher['suffix'] === 'JR' ? 'selected' : ''; ?>>Jr.</option>
                                <option value="SR" <?= $teacher['suffix'] === 'SR' ? 'selected' : ''; ?>>Sr.</option>
                                <option value="III" <?= $teacher['suffix'] === 'III' ? 'selected' : ''; ?>>III</option>
                                <option value="IV" <?= $teacher['suffix'] === 'IV' ? 'selected' : ''; ?>>IV</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="" <?= empty($teacher['gender']) ? 'selected' : ''; ?>>Select Gender
                                </option>
                                <option value="Male" <?= $teacher['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?= $teacher['gender'] === 'Female' ? 'selected' : ''; ?>>Female
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="birthdate">Birth date</label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control"
                                value="<?= htmlspecialchars($teacher['birthdate']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="residence">Residence</label>
                            <input type="text" id="residence" name="residence" class="form-control"
                                value="<?= htmlspecialchars($teacher['residence']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="class">Civil Status</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="" <?= empty($teacher['civil_status']) ? 'selected' : ''; ?>>--select--
                                </option>
                                <option value="Married" <?= $teacher['civil_status'] === 'Married' ? 'selected' : ''; ?>>
                                    Married</option>
                                <option value="Separated" <?= $teacher['civil_status'] === 'Separated' ? 'selected' : ''; ?>>Separated
                                </option>
                                <option value="Single" <?= $teacher['civil_status'] === 'Single' ? 'Single' : ''; ?>>Single
                                </option>
                                <option value="Widowed" <?= $teacher['civil_status'] === 'Widowed' ? 'selected' : ''; ?>>
                                    Widowed
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone_num">Phone Number</label>
                            <input type="text" id="phone_num" name="phone_num" class="form-control-phone_number"
                                value="<?= htmlspecialchars($teacher['contact_num']); ?>">
                        </div>
                        <div class="form-group">
                            <label id="label-for-email" for="Email">Email</label>
                            <input type="email" id="email" name="email" class="form-control-email"
                                value="<?= htmlspecialchars($teacher['email_add']); ?>">
                        </div>
                    </div>
                    <div class="credentials-section">
                    <h3>Credentials</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control"
                            value="<?= htmlspecialchars($teacher['user_name']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control"
                            value="<?= htmlspecialchars($teacher['password']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                    </div>
                </div>
                    <button type="submit" class="save-btn" id="save-btn"> Update </button>
                </form>
                <a href="teacher_profile.php?id=<?= $teacher['id']; ?>" class="btn btn-primary">
                    <button type="submit" id="back-btn"> Back to Teacher Profile </button></a>
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
