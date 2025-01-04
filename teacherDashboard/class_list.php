<?php

session_start();

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

    // Database connection
    $conn = new mysqli("localhost", "root", "", "studentgradingsystem");

    // Check connection
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




    $teacher_id = $_SESSION['id']; // Assign teacher ID from session
    $query = "
    SELECT
        c.id AS class_id,
        c.level,
        c.section,
        s.subject_name,
        s.id AS subject_id
    FROM classes c
    JOIN subjects s
        ON c.level = s.level AND c.section = s.section
    WHERE c.teacher_id = ?
";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('i', $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    ?>

    <!DOCTYPE html>

    <html>

    <head>
        <title>SNPS MIS</title>
        <link rel="stylesheet" href="../Assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
        <link rel="stylesheet" type="text/css" href="../Assets/css/teacher_dashboard/manage_class.css">
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
                        <li><a href="../teacherDashboard/manage_student.php">Student List</a>
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
                        <li><a href="../teacherDashboard/class_list.php?id=<?= $teacher['teacher_id']; ?>">My Classes</a>
                        </li>
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
            <h1>My Classes</h1>
            <div class="bread-crumb">
                <p><span class="fa-solid--home"></span> Home / Classes / My Classes</p>
            </div>
            <div class="dashboard">
                <p>Submit Grades</p>
                <div class="datatable">
                    <table id="example" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Level</th>
                                <th>Section</th>
                                <th>Subject Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 1;
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $count++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['level']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['section']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
                                    echo "<td>  <button class='btn btn-primary btn-sm submitGradesButton'
                                   data-class-id='" . htmlspecialchars(string: $row['class_id']) . "'
                                   data-subject-id='" . htmlspecialchars($row['subject_id']) . "'
                                   data-subject-name='" . htmlspecialchars($row['subject_name']) . "'
                                   data-section='" . htmlspecialchars($row['section']) . "'
                                   data-bs-toggle='modal'
                                   data-bs-target='#gradeModal'>
                                   Submit Grades
                                </button>
                            </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<p>No data found for teacher ID: " . htmlspecialchars($teacher_id) . "</p>";
                            }
                            ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Level</th>
                                <th>Section</th>
                                <th>Subject Name</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
            <!-- Include the grade submission modal -->
            <div class="modal fade" id="gradeModal" tabindex="-1" aria-labelledby="gradeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="gradeModalLabel">Grade Input</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Form for Grade Submission -->
                            <form id="gradeForm">
                                <!-- Hidden Inputs for Class and Subject IDs -->
                                <input type="hidden" name="class_id" id="classID">
                                <input type="hidden" name="subject_id" id="subjectID">

                                <!-- Placeholder for Class and Section Info -->
                                <h6 id="classInfo"> </h6>
                                <!-- Grade Table -->
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Full Name</th>
                                            <th>Q1</th>
                                            <th>Q2</th>
                                            <th>Q3</th>
                                            <th>Q4</th>
                                            <th>Final</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studentList">
                                        <!-- Dynamic Rows Added Here -->
                                    </tbody>
                                </table>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary">Save Grades</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script src="../Assets/Javascript/main.js"></script>
        <script>
            document.addEventListener("click", function (event) {
                if (event.target.classList.contains("submitGradesButton")) {
                    const button = event.target;

                    const classID = button.getAttribute("data-class-id");
                    const subjectID = button.getAttribute("data-subject-id");
                    const subjectName = button.getAttribute("data-subject-name");
                    const section = button.getAttribute("data-section");

                    // Set the class info in the modal
                    const classInfo = `${subjectName} (Section: ${section})`;
                    document.getElementById("classInfo").innerText = classInfo;

                    // Set hidden inputs
                    document.getElementById("classID").value = classID;
                    document.getElementById("subjectID").value = subjectID;

                    // Fetch student data using AJAX
                    $.ajax({
                        url: "../teacherDashboard/fetch_students.php",
                        type: "POST",
                        data: {
                            class_id: classID,
                            subject_id: subjectID,
                        },
                        success: function (response) {
                            const students = JSON.parse(response);
                            const studentList = document.getElementById("studentList");
                            studentList.innerHTML = ""; // Clear the previous data

                            if (students.length > 0) {
                                students.forEach((student, index) => {
                                    const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${student.full_name}</td>
                                    <td><input type="number" name="q1[${student.student_id}]" value="${student.q1 || ''}" class="form-control"></td>
                                    <td><input type="number" name="q2[${student.student_id}]" value="${student.q2 || ''}" class="form-control"></td>
                                    <td><input type="number" name="q3[${student.student_id}]" value="${student.q3 || ''}" class="form-control"></td>
                                    <td><input type="number" name="q4[${student.student_id}]" value="${student.q4 || ''}" class="form-control"></td>
                                    <td><input type="number" name="final[${student.student_id}]" value="${student.final_grade || ''}" class="form-control" readonly></td>
                                    <td><input type="text" name="remarks[${student.student_id}]" value="${student.remarks || ''}" class="form-control"></td>
                                </tr>
                            `;
                                    studentList.insertAdjacentHTML("beforeend", row);
                                });
                            } else {
                                studentList.innerHTML = "<tr><td colspan='8' style='text-align: center;'>No students found for this class/subject</td></tr>";

                            }
                        },
                        error: function (error) {
                            console.error("Error fetching students:", error);
                        },
                    });
                }
            });
            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("gradeForm").addEventListener("submit", function (event) {
                    event.preventDefault(); // Prevent default form submission

                    const formData = new FormData(this);

                    $.ajax({
                        url: "../teacherDashboard/save_grades.php",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            const result = JSON.parse(response);
                            if (result.success) {
                                alert("Grades saved successfully!");
                                $("#gradeModal").modal("hide");
                            } else {
                                alert("Failed to save grades: " + result.error);
                            }
                        },
                        error: function (error) {
                            console.error("Error saving grades:", error);
                            alert("An error occurred while saving grades.");
                        },
                    });
                });
            });




        </script>


    </body>

    </html>

    <?php
    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>
