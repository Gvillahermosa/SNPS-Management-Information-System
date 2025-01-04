<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403); // Forbidden
    echo "Access denied";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['background_id'])) {
    include "../db/db_conn.php";

    $background_id = intval($_POST['background_id']);
    $teacher_id = $_SESSION['id'];

    // Check if the background record belongs to the logged-in teacher
    $sqlCheck = "SELECT * FROM teacher_background WHERE id = ?";
    $stmt = $conn->prepare($sqlCheck);
    $stmt->bind_param('i', $background_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete the background record
        $sqlDelete = "DELETE FROM teacher_background WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param('i', $background_id);
        if ($stmtDelete->execute()) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record";
        }
        $stmtDelete->close();
    } else {
        echo "Invalid record or permission denied";
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400); // Bad request
    echo "Invalid request";
}
?>
