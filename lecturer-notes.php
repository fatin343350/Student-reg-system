<?php
session_start();
if (!isset($_SESSION['lecturer_id'])) {
    header("Location: lecturer-login.html");
    exit();
}

$lecturer_id = $_SESSION['lecturer_id'];
$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch notes uploaded by this lecturer
$sql = "SELECT title, file_name, unit_code, upload_date FROM notes WHERE lecturer_id = ? ORDER BY upload_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Uploaded Notes</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>L DASH</h2>
        <ul>
            <li><a href="lecturer-dashboard.php">🏠 Dashboard</a></li>
            <li><a href="upload-notes.php">📂 Upload Notes</a></li>
            <li><a href="lecturer-notes.php">📄 My Notes</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <h2>📄 Your Uploaded Notes / Assignments</h2>
        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>Title</th>
                    <th>Unit Code</th>
                    <th>File</th>
                    <th>Uploaded On</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['unit_code']); ?></td>
                        <td>
                            <a href="uploads/<?php echo urlencode($row['file_name']); ?>" target="_blank">📥 View</a>
                        </td>
                        <td><?php echo date("d M Y", strtotime($row['upload_date'])); ?></td>
                        <td>
                            <a href="grade-assignment.php?unit_code=<?php echo urlencode($row['unit_code']); ?>&title=<?php echo urlencode($row['title']); ?>">📝 Grade</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No notes uploaded yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
