<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['user_name'])) {
    header("Location: ../index.php");
    exit();
}



// Get the current year and month
$currentYear = date("Y");
$currentMonth = date("n"); // Numeric month (1 for January, 12 for December)

// Determine the school year and semester
if ($currentMonth < 6) { // Before June
    $startYear = $currentYear - 1;
    $endYear = $currentYear;

} else { // June or later
    $startYear = $currentYear;
    $endYear = $currentYear + 1;

}

// Combine into the school year format
$schoolYear = $startYear . " - " . $endYear;





$grades = []; // Initialize grades to avoid undefined variable issues
$error_message = "";

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']); // Sanitize input to prevent SQL injection

    // Database connection
    $conn = new mysqli("localhost", "root", "", "studentgradingsystem");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sqlDetails = "
    SELECT
        CONCAT(st.Fname, ' ', st.lname) AS studentName,
        st.gender,
        st.bdate,
        cl.level,
        cl.section
    FROM studentinfo st
    JOIN classes cl ON cl.id = st.class
    WHERE st.id = ?
";

    $stmDetails = $conn->prepare($sqlDetails); // Corrected variable name
    $stmDetails->bind_param("i", $student_id); // Use the same variable
    $stmDetails->execute();
    $resultDetails = $stmDetails->get_result(); // Corrected variable for `get_result()`

    $sqlGetPrincipal = "SELECT CONCAT(first_name, ' ', last_name) AS principalName
    FROM users
    WHERE role = 'Principal'";

    $stmt = $conn->prepare($sqlGetPrincipal);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $principalName = $row['principalName'];
    } else {
    echo "No principal found.";
    }

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

    if ($resultDetails->num_rows > 0) {
        $studentDetails = $resultDetails->fetch_assoc();
        $studentName = htmlspecialchars($studentDetails['studentName']);
        $gender = htmlspecialchars($studentDetails['gender']);
        $birthdate = $studentDetails['bdate'];
        $level = htmlspecialchars($studentDetails['level']);
        $section = htmlspecialchars($studentDetails['section']);
    } else {
        $studentName = "No Name Found";
        $gender = "N/A";
        $birthdate = null;
        $level = "N/A";
        $section = "N/A";
    }

    if ($birthdate) {
        $birthdateObj = new DateTime($birthdate);
        $today = new DateTime();
        $age = $today->diff($birthdateObj)->y;
    }


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
    $conn->close(); // Close the connection properly
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

$teacherName = "";
foreach ($grades as $grade) {
    $teacherName = htmlspecialchars($grade['teacher_name']); // Store teacher name
    break; // Exit loop if you just need the first occurrence
}




?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="../Assets/css/teacher_dashboard/report_card.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report</title>

    <style>
        /* A4 size landscape */
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
        }

        .report-card {
            width: 100%;
            max-width: calc(1122px - 20mm);
            /* A4 width minus margins */
            max-height: calc(794px - 20mm);
            /* A4 height minus margins */
            padding: 10px;
            box-sizing: border-box;
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 20px;
        }

        .report-details {
            box-sizing: border-box;
        }

        .report-details h3 {
            text-align: center;
            font-size: 20px;
            /* Increased font size */
            font-weight: bold;
            margin-bottom: 10px;
        }

        .report-details table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 16px;
            /* Increased font size */
        }

        .parent-signature {
            margin-top: 22px;
            font-weight: bold;
            text-align: center;
            font-size: 18px;
            /* Increased font size */
        }

        .signature-lines p {
            margin: 8px 0;
            font-size: 16px;
            /* Increased font size */
        }

        .report-header {
            margin-bottom: 10px;
            text-align: center;
        }

        .report-header h2 {
            margin: 0;
            font-size: 22px;
            /* Increased font size */
            font-weight: bold;
        }

        .report-header h3 {
            margin: 5px 0;
            font-size: 18px;
            /* Increased font size */
        }

        .details {
            margin-top: 10px;
        }

        .details table {
            margin-top: 10px;
        }

        .remarks {
            margin-top: 15px;
        }

        .remarks p {
            margin: 12px 0;
            line-height: 1.6;
            text-align: justify;
            font-size: 16px;
            /* Increased font size */
        }

        .footer {
            margin-top: 15px;
        }

        .footer p {
            margin: 8px 0;
            font-size: 16px;
            /* Increased font size */
        }

        .footer .signature {
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="report-card">
        <!-- Attendance Record -->
        <div class="attendance-record">

            <div class="parent-signature">
                <p>PARENT/GUARDIAN'S SIGNATURE</p>
            </div>
            <div class="signature-lines">
                <p style="margin-top: 25px;">1st Quarter: ______________________________________</p>
                <p style="margin-top: 25px;">2nd Quarter: ______________________________________</p>
                <p style="margin-top: 25px;">3rd Quarter: ______________________________________</p>
                <p style="margin-top: 25px;">4th Quarter: ______________________________________</p>
            </div>
        </div>
        <!-- Report Details -->
        <div class="report-details">
            <div class="details">
                <h3>LEARNER'S PROGRESS REPORT CARD</h3>
                <p style="font-size: 18px; margin-left: 140px;">
                    School Year: <span
                        style="text-decoration: underline;"><?php echo htmlspecialchars($schoolYear); ?></span>
                </p>

                <p style="display: inline;">Name:</p> <input type="text"
                    style="width: 470px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"
                    disabled value="<?php echo htmlspecialchars($studentName); ?>">

                <p>Age:<input type="text" style="width: 200px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;" disabled
                        value="<?php echo htmlspecialchars($age); ?>">

                    Sex: <input type="text" style="width: 240px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;" disabled
                    value="<?php echo htmlspecialchars($gender); ?>">
                </p>
                Grade:<input type="text" style="width: 200px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;" disabled
                value="<?php echo htmlspecialchars($level); ?>">
                Section: <input type="text" style="width: 200px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;" disabled
                value="<?php echo htmlspecialchars($section); ?>">


            </div>
            <div class="remarks">
                <p>Dear Parent,</p>
                <p style="text-indent: 50px;">
                    This report card shows the ability and progress your child has made in the different learning areas
                    as well as his/her progress in core values.
                </p>
                <p style="text-indent: 50px;">The school welcomes you should you desire to know more about your child's
                    progress.</p>
                <div style="text-align: right; margin-right: 20px; margin-top: -15px;">
                    <p
                        style="display: inline-block; font-size: 16px; text-decoration: underline; word-wrap: break-word; max-width: 100%; padding-bottom: 5px; text-align: center;">
                        <?php echo htmlspecialchars($teacherName); ?>
                    </p>
                    <p style="font-weight: bold; margin: 0; text-align: right; margin-right: 75px; margin-top:-15px">
                        Teacher</p>
                </div>


                <p style=" margin-top: -40px;"><input type="text" style="width: 180px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;" disabled
                value="<?php echo htmlspecialchars($principalName); ?>"></p>
                <p style="margin-left: 5px; margin-top: -10px; font-weight: bold;">Head Teacher/Principal</p>
            </div>
            <div class="footer">
                <p
                    style="margin-left: 160px; font-weight: bold; font-size: 20px; margin-top:-5px; margin-bottom: 20px;">
                    Certificate of Transfer</p>

                <p>Admitted to Grade <input type="text" style="width: 100px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"> Section <input type="text" style="width: 90px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"> Room <input type="text" style="width: 75px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"></p>
                <p>Eligible for Admission to Grade <input type="text" style="width: 300px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"></p>
                <p>Approved:</p>
                <p><input type="text" style="width: 180px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"></p>
                <p style="margin-left: 2px; font-weight: bold;">Head Teacher/Principal</p>
                <p style="position: absolute; margin-top: -53px; margin-left: 356px;"><input type="text" style="width: 180px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"></p>
                <p style="position: absolute; margin-top: -27px; margin-left: 420px; font-weight: bold;">Teacher</p>

                <div class="signature">
                    <p>Admitted in <input type="text" style="width: 445px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"></p>
                    <p>Date: <input type="text" style="width: 180px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"></p>
                    <p style="margin-left: 352px; margin-top: -10px;"><input type="text" style="width: 180px; border: none; border-bottom: 1px solid; background-color: transparent; color: inherit; cursor: default;"></p>
                    <p style="margin-left: 420px; font-weight: bold;">Principal</p>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
        <div class="section">
            <div class="table-container">
                <div class="header">ACADEMIC GROWTH</div>
                <table>
                    <thead>
                        <tr>
                            <th>SUBJECTS</th>
                            <th colspan="4">PERIODIC RATING</th>
                            <th>FINAL RATING</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th>1st</th>
                            <th>2nd</th>
                            <th>3rd</th>
                            <th>4th</th>
                            <th></th>
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
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No grades available for this student.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="header">ATTENDANCE</div>
                <table id="attendance">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="vertical">August</th>
                            <th class="vertical">September</th>
                            <th class="vertical">October</th>
                            <th class="vertical">November</th>
                            <th class="vertical">December</th>
                            <th class="vertical">January</th>
                            <th class="vertical">February</th>
                            <th class="vertical">March</th>
                            <th class="vertical">April</th>
                            <th class="vertical">May</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>No. of School Days</td>
                            <td>25</td>
                            <td>23</td>
                            <td>24</td>
                            <td>22</td>
                            <td>13</td>
                            <td>22</td>
                            <td>22</td>
                            <td>20</td>
                            <td>25</td>
                            <td>10</td>
                            <td>206</td>
                        </tr>
                        <tr>
                            <td>Days Present</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Days Absent</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-container">
                <div class="header">GROWTH IN HABITS & ATTITUDES</div>
                <table class="habits-table">
                    <thead>
                        <tr>
                            <th>SUBJECTS</th>
                            <th colspan="4">GRADING PERIOD</th>
                            <th>FINAL RATING</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th>1st</th>
                            <th>2nd</th>
                            <th>3rd</th>
                            <th>4th</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Says prayer with reverence</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Behaves courteously & politely</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Is neat and clean</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Respect others thier rights & property</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Friendly and cheerful</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Works independently & finishes his/her work on time</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Listens & active in class participation</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Follows directed activities</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Honest & displays "give & take spirit"</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>eats nutritious foods & practices good health habits</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Obeyys school & classroom rules</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Cleans & cares for mother earth</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>
                        <tr>
                            <td>Patriotism</td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                            <td><input type="text"></td>
                        </tr>

                    </tbody>
                </table>
                <div class="footer evaluation">
                    <strong>EVALUATION CODE:</strong><br>
                    96% - 100% = A+ Suggest Highly Advanced Development (SHAD)<br>
                    90% - 95% = A Suggest Slight Advanced Development (SSAD)<br>
                    80% - 89% = B Average Development (AD)<br>
                    75% - 79% = C Suggest Slight Delay Overall Development (SDOD)<br>
                    74% & below = D Suggest Significant Delay in Overall Development (SSDOD)
                </div>
            </div>
        </div>
    </div>
</body>

</html>
