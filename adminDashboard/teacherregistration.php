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
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Capture form data
        $teacher_id = $_POST['teacher_id'] ?? '';
        $first_name = $_POST['firstname'] ?? '';
        $mid_name = $_POST['middlename'] ?? '';
        $last_name = $_POST['lastname'] ?? '';
        $suffix = $_POST['suffix'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';
        $residence = $_POST['residence'] ?? '';
        $role = $_POST['role'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $teacherpic = $_FILES['profile_picture']['tmp_name'] ?? null; // Get the temporary file path

        if ($teacherpic && file_exists($teacherpic)) {
            // Read the file as binary
            $teacherPicData = file_get_contents($teacherpic);
        } else {
            $teacherPicData = null; // No file uploaded
        }

        // Password validation
        if (strlen($password) < 8) {
            $messageType = "error";
            $messageText = "Password must be at least 8 characters long.";
        } elseif (ctype_digit($password)) {
            $messageType = "error";
            $messageText = "Password must not be entirely numeric.";
        } elseif ($password === "1234567890") {
            $messageType = "error";
            $messageText = "Password must not be '1234567890'.";
        } elseif ($password !== $confirm_password) {
            $messageType = "error";
            $messageText = "Passwords do not match. Please re-enter.";
        } else {
            // Check for existing teacher_id
            $checkSql = "SELECT id FROM users WHERE teacher_id = ?";
            if ($stmt = $conn->prepare($checkSql)) {
                $stmt->bind_param("i", $teacher_id);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $messageType = "error";
                    $messageText = "This ID already exists in the database. Record not saved.";
                } else {
                    $stmt->close();

                    // Hash the password for security
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new record
                    $insertSql = "INSERT INTO users (teacher_id, first_name, mid_name, last_name, suffix, gender, birthdate, residence, role, user_name, password, teacher_pic)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    if ($stmt = $conn->prepare($insertSql)) {
                        $stmt->bind_param(
                            "isssssssssss",
                            $teacher_id,
                            $first_name,
                            $mid_name,
                            $last_name,
                            $suffix,
                            $gender,
                            $birthdate,
                            $residence,
                            $role,
                            $username,
                            $hashedPassword,
                            $teacherPicData
                        );

                        if ($stmt->execute()) {
                            $messageType = "success";
                            $messageText = "Teacher record saved successfully!";
                        } else {
                            $messageType = "error";
                            $messageText = "Error: Could not save record. " . $conn->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>

<html>

<head>
    <title>Admin</title>
    <link rel="stylesheet" type="text/css" href="../Assets/css/teacherregistration.css">
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
        <h1>Teacher Registration</h1>
        <div class="bread-crumb">
            <p><span class="fa-solid--home"></span> Home / Student / Teacher Registration</p>
        </div>
        <div class="notifications">

        </div>
        <div class="dashboard">
            <h2>Fill in the Teacher Info</h2>
            <div class="form-container">
                <form class="student-form" method="POST" enctype="multipart/form-data">
                    <div class="photo-upload">
                        <div class="photo-placeholder" id="photo-placeholder">
                            <?php if (!empty($profilePicPath)): ?>
                                <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" id="profile-img">
                            <?php else: ?>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='%23666' d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E"
                                    alt="Profile placeholder" id="profile-img">
                            <?php endif; ?>
                        </div>
                        <div class="upload-btn">
                            <input type="file" id="profile-picture" name="profile_picture" accept="image/*" required>
                            <label for="profile-picture">Upload Picture</label>
                        </div>
                        <div class="photo-requirements">
                            <p>✓ Plain white or light blue background</p>
                            <p>✓ Face centered and clearly visible</p>
                            <p>✓ Professional attire (collar/blazer)</p>
                            <p>✓ High resolution, recent photo</p>
                            <p>✓ 2×3 inches for upload</p>
                            <p class="error">✗ No accessories (hat, heavy jewelry)</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="teacher_id">ID No.</label>
                        <input type="text" id="teacher_id" name="teacher_id" class="lrn" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First name</label>
                            <input type="text" id="firstname" name="firstname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="middlename">Middle name (Dash "-" for none)</label>
                            <input type="text" id="middlename" name="middlename" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last name</label>
                            <input type="text" id="lastname" name="lastname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <select id="suffix" name="suffix" class="form-control">
                                <option value="">--select--</option>
                                <option value="jr">Jr.</option>
                                <option value="sr">Sr.</option>
                                <option value="iii">III</option>
                                <option value="iv">IV</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="">--select--</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="birthdate">Birth date</label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control"
                                placeholder="MM/DD/YYYY" required>
                        </div>
                        <div class="form-group">
                            <label for="residence">Residence</label>
                            <input type="text" id="residence" name="residence" class="form-control" required>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="class">Role</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="Principal">Principal</option>
                                <option value="Teacher">Teacher</option>
                            </select>
                        </div>
                    </div>
                    <div class="credentials-section">
                        <h3>Credentials</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>

                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                    class="form-control" required>
                                <!-- Error message will be added here dynamically -->
                            </div>

                        </div>
                    </div>
                    <button type="submit" class="save-btn"> Save </button>
                </form>
            </div>
        </div>

    </div>



    <script>
        const messageType = "<?php echo $messageType; ?>";
        const messageText = "<?php echo $messageText; ?>";
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("password");
            const confirmPasswordInput = document.getElementById("confirm_password");

            const passwordError = document.createElement("p");
            const confirmError = document.createElement("p");

            // Styling for error messages
            [passwordError, confirmError].forEach(error => {
                error.style.color = "red";
                error.style.fontSize = "12px";
                error.style.fontWeight = "bold";
                error.style.marginTop = "5px";
            });

            // Add the error messages below respective input fields
            passwordInput.parentNode.appendChild(passwordError);
            confirmPasswordInput.parentNode.appendChild(confirmError);

            // Validate password
            passwordInput.addEventListener("input", function () {
                let errors = [];

                // Check password length
                if (passwordInput.value.length < 8 && passwordInput.value.length > 0) {
                    errors.push("Password must be at least 8 characters long.");
                }

                // Check if password is all numeric or matches a forbidden password
                if (/^\d+$/.test(passwordInput.value)) {
                    errors.push("Password must not be all numeric.");
                }

                if (passwordInput.value === "1234567890") {
                    errors.push("Password must not be '1234567890'.");
                }

                // Display errors
                passwordError.textContent = errors.join(" ");
            });

            // Validate password confirmation
            confirmPasswordInput.addEventListener("input", function () {
                if (confirmPasswordInput.value === passwordInput.value) {
                    confirmError.textContent = ""; // Clear error if passwords match
                } else {
                    confirmError.textContent = "Passwords do not match!";
                }
            });
        });
    </script>



    <script src="../Assets/Javascript/main.js"></script>
    <script src="../Assets/Javascript/notification.js"></script>
</body>

</html>
