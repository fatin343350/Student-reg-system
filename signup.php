<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $course     = $_POST['course'];
    $level      = $_POST['level'];
    $gender     = $_POST['gender'];
    $phone      = $_POST['phone'];
    $country    = $_POST['country'];

    // Connect to MySQL
    $conn = new mysqli("localhost", "root", "", "student_system");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ✅ Insert student with 'pending' status
    $sql = "INSERT INTO students 
        (first_name, last_name, email, password, course, level, gender, phone_number, country, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $password, $course, $level, $gender, $phone, $country);

    if ($stmt->execute()) {
        $student_id = $stmt->insert_id; // Get the ID of the newly inserted student

        // ✅ Fetch all unit codes for the selected course
        $unit_query = $conn->prepare("SELECT unit_code FROM units WHERE course = ?");
        $unit_query->bind_param("s", $course);
        $unit_query->execute();
        $unit_result = $unit_query->get_result();

        // ✅ Assign each unit to student
        $insert_unit = $conn->prepare("INSERT INTO student_units (student_id, unit_code) VALUES (?, ?)");
        while ($row = $unit_result->fetch_assoc()) {
            $unit_code = $row['unit_code'];
            $insert_unit->bind_param("is", $student_id, $unit_code);
            $insert_unit->execute();
        }

        // ✅ Success message
        echo "
        <div style='text-align: center; margin-top: 50px; font-family: Arial;'>
            <h2 style='color: green;'>✅ Registration successful!</h2>
            <p>Your account is pending approval. Please wait for the admin to activate your account.</p>
            <a href='login.html'>
                <button style='padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>Go to Login</button>
            </a>
        </div>";
    } else {
        echo "<p style='color:red; text-align:center;'>❌ Error: " . $stmt->error . "</p>";
    }

    $conn->close();
}
?>
