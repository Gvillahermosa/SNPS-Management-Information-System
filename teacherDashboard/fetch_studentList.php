<?php
session_start();

// Check if session is valid
if (isset($_SESSION['id']) && isset($_POST['class_id'])) {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "studentgradingsystem");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $class_id = $_POST['class_id'];
    // Query to fetch students for a specific class and subject
    $query = "
        SELECT
            s.id AS student_id,
            CONCAT(s.Fname, ' ', s.Lname) AS full_name
        FROM studentinfo s
        WHERE s.class = ?;
    ";

    $stmt = $conn->prepare(query: $query);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    // Return the students as JSON
    echo json_encode($students);

    $conn->close();
} else {
    echo json_encode(["error" => "Invalid request."]);
}
?>
