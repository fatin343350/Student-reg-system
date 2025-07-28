<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lecturer_id = $_POST['lecturer_id'];
    $unit_code = $_POST['unit_code'];

    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "student_system");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prevent duplicate assignment
    $check = $conn->prepare("SELECT * FROM lecturer_units WHERE lecturer_id = ? AND unit_code = ?");
    $check->bind_param("is", $lecturer_id, $unit_code);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        echo "⚠️ This unit is already assigned to the selected lecturer.";
    } else {
        $stmt = $conn->prepare("INSERT INTO lecturer_units (lecturer_id, unit_code) VALUES (?, ?)");
        $stmt->bind_param("is", $lecturer_id, $unit_code);
        if ($stmt->execute()) {
            echo "✅ Unit successfully assigned!";
        } else {
            echo "❌ Failed to assign unit: " . $conn->error;
        }
    }

    $conn->close();
}
?>
