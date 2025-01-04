<?php
session_start();

if (isset($_SESSION['id'])) {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "studentgradingsystem");

    if ($conn->connect_error) {
        die(json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]));
    }

    // Get class_id and subject_id
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];

    // Iterate through grades for each student
    foreach ($_POST['q1'] as $student_id => $q1) {
        // Use NULL for missing values
        $q2 = isset($_POST['q2'][$student_id]) ? $_POST['q2'][$student_id] : NULL;
        $q3 = isset($_POST['q3'][$student_id]) ? $_POST['q3'][$student_id] : NULL;
        $q4 = isset($_POST['q4'][$student_id]) ? $_POST['q4'][$student_id] : NULL;
        $remarks = isset($_POST['remarks'][$student_id]) ? $_POST['remarks'][$student_id] : NULL;

        // Check if all grades are provided (none should be NULL)
        if ($q1 !== NULL && $q2 !== NULL && $q3 !== NULL && $q4 !== NULL) {
            // Calculate the final grade based on the provided grades
            $grades = array($q1, $q2, $q3, $q4);
            $final = array_sum($grades) / count($grades);  // Calculate average

            // Determine if the student has passed (e.g., passing grade is 60)
            $status = ($final >= 75) ? 'Passed' : 'Not Passed';
        } else {
            // If any grade is missing, we set the status as 'Pending' or NULL
            $final = NULL;
            $status = 'Pending'; // or 'NULL' if you prefer
        }

        // Insert or update grades
        $query = "
            INSERT INTO student_grades (student_id, class_id, subject_id, q1, q2, q3, q4, final_grade, status, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                q1 = COALESCE(VALUES(q1), q1),
                q2 = COALESCE(VALUES(q2), q2),
                q3 = COALESCE(VALUES(q3), q3),
                q4 = COALESCE(VALUES(q4), q4),
                final_grade = VALUES(final_grade),
                status = VALUES(status),
                remarks = COALESCE(VALUES(remarks), remarks)
        ";

        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param(
                "iiiidddsss",
                $student_id,
                $class_id,
                $subject_id,
                $q1,
                $q2,
                $q3,
                $q4,
                $final,
                $status,
                $remarks
            );
            $stmt->execute();
        } else {
            echo json_encode(["success" => false, "error" => "Query preparation failed: " . $conn->error]);
            exit();
        }
    }

    echo json_encode(["success" => true]); // Success response
    $conn->close();
} else {
    echo json_encode(["success" => false, "error" => "Unauthorized request."]);
}
?>
