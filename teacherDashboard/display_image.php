<?php
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);
    $conn = new mysqli("localhost", "root", "", "studentgradingsystem");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT studentpic FROM studentinfo WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($studentPic);
        $stmt->fetch();

        // Set the content type header
        header("Content-Type: image/jpeg"); // Change to the correct image type
        echo $studentPic;
    } else {
        echo "Image not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
