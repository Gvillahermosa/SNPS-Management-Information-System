<?php
session_start();
include "../db/db_conn.php";

$messageType = "";
$messageText = "";

$classSql = "SELECT id, level, section FROM classes";
$classResult = $conn->query($classSql);

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Capture form data
        $lrn = $_POST['lrn'];
        $firstname = $_POST['firstname'];
        $middlename = $_POST['middlename'];
        $lastname = $_POST['lastname'];
        $suffix = $_POST['suffix'];
        $gender = $_POST['gender'];
        $birthdate = $_POST['birthdate'];
        $residence = $_POST['residence'];
        $class = $_POST['class'];

        // Check if the LRN already exists in the database
        $checkSql = "SELECT id FROM StudentInfo WHERE LRN = ?";
        if ($stmt = $conn->prepare($checkSql)) {
            $stmt->bind_param("i", $lrn);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Record exists, set error message
                $messageType = "error";
                $messageText = "This LRN already exists in the database. Record not saved.";
            } else {
                // LRN does not exist, insert new record
                $stmt->close(); // Close previous statement

                $insertSql = "INSERT INTO StudentInfo (LRN, Fname, Midname, Lname, Suffix, Gender, bdate, Residence, Class)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = $conn->prepare($insertSql)) {
                    $stmt->bind_param("issssssss", $lrn, $firstname, $middlename, $lastname, $suffix, $gender, $birthdate, $residence, $class);

                    if ($stmt->execute()) {
                        $messageType = "success";
                        $messageText = "Student record saved successfully!";
                    } else {
                        $messageType = "error";
                        $messageText = "Error: Could not save record. " . $conn->error;
                    }
                    $stmt->close();
                }
            }
        } else {
            $messageType = "error";
            $messageText = "Error: Could not prepare check query. " . $conn->error;
        }

        $conn->close();
    }
    ?>
    <!DOCTYPE html>

    <html>

    <head>
        <title>Admin</title>
        <link rel="stylesheet" type="text/css" href="../Assets/css/studentadmission.css">
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
            <h1>Student Admission</h1>
            <div class="bread-crumb">
                <p><span class="fa-solid--home"></span> Home / Student / Student Admission</p>
            </div>
            <div class="notifications">

            </div>
            <div class="dashboard">
                <p>Fill the Student Info</p>
                <div class="form-container">
                    <form class="student-form" method="POST">
                        <div class="form-group">
                            <label for="lrn">LRN / ID No.</label>
                            <input type="text" id="lrn" name="lrn" class="lrn" required>
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
                                <label for="class">Class</label>
                                <select id="class" name="class" class="form-control" required>
                                    <option value="">Select Class</option>
                                    <?php
                                    // Loop through the classes and create an option for each
                                    if ($classResult->num_rows > 0) {
                                        while ($class = $classResult->fetch_assoc()) {
                                            echo "<option value='" . $class['id'] . "'>" . $class['level'] . " - " . $class['section'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No classes available</option>";
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

    <?php
} else {
    header("Location: ../index.php");
    exit();
}
?>
