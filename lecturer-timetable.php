<?php
session_start();
if (!isset($_SESSION['lecturer_id'])) {
    header("Location: lecturer-login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$lecturer_name = $_SESSION['lecturer_name'];
$timetables = $conn->query("SELECT * FROM timetable_files ORDER BY upload_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecturer Timetable</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: #f5f7fb;
            color: var(--dark);
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--gray-light);
            padding-bottom: 1rem;
        }
        .sidebar-logo {
            width: 40px;
            height: 40px;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: 600;
        }
        .sidebar-header h2 {
            margin-left: 0.5rem;
            font-size: 1.5rem;
            color: var(--primary);
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: var(--gray);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        .sidebar-menu a i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .main {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            font-size: 1.75rem;
            color: var(--dark);
            font-weight: 600;
        }
        .user-profile {
            display: flex;
            align-items: center;
        }
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.75rem;
        }
        .user-info h3 {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .user-info p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .section h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .notes {
            list-style: none;
            padding: 0;
        }
        .notes li {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
        }
        .notes li:last-child {
            border-bottom: none;
        }
        .notes a {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
        }
        .notes small {
            display: block;
            color: var(--gray);
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 1rem 0.5rem;
            }
            .sidebar-header h2, .sidebar-menu a span {
                display: none;
            }
            .sidebar-menu a {
                justify-content: center;
                padding: 0.75rem 0;
            }
            .sidebar-menu a i {
                margin-right: 0;
                font-size: 1.25rem;
            }
            .main {
                margin-left: 80px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">L</div>
            <h2>Lecturer Portal</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="lecturer-dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
            <li><a href="upload-notes.php"><i class="fas fa-file-upload"></i><span>Upload Notes</span></a></li>
            <li><a href="grade-students.php"><i class="fas fa-graduation-cap"></i><span>Grade Students</span></a></li>
            <li><a href="lecturer-timetable.php" class="active"><i class="fas fa-calendar-alt"></i><span>Timetable</span></a></li>
            <li><a href="mark-attendance.php"><i class="fas fa-clipboard-check"></i> <span>Mark Attendance</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </div>

    <div class="main">
        <div class="header">
            <h1>Uploaded Timetables</h1>
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($lecturer_name); ?>&background=random" alt="Profile">
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($lecturer_name); ?></h3>
                    <p>Lecturer</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Timetable Files</h2>
            <ul class="notes">
                <?php if ($timetables->num_rows > 0): ?>
                    <?php while ($row = $timetables->fetch_assoc()): ?>
                        <li>
                            <a href="uploads/timetables/<?php echo urlencode($row['file_name']); ?>" target="_blank">
                                <?php echo htmlspecialchars($row['file_name']); ?>
                            </a>
                            <small>Uploaded on <?php echo date("d M Y", strtotime($row['upload_date'])); ?></small>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No timetable uploaded yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
