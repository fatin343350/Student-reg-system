<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$base_url = "/registration_system/";
$conn = new mysqli("localhost", "root", "", "student_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_SESSION['student_id'];

// Get student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found");
}

$registration_status = ucfirst($student['status'] ?? 'Pending');

// Count registered units
$unit_query = $conn->prepare("SELECT COUNT(*) as total FROM student_units WHERE student_id = ?");
$unit_query->bind_param("i", $student_id);
$unit_query->execute();
$unit_result = $unit_query->get_result();
$unit_count = $unit_result->fetch_assoc()['total'];

// Check average grade
$avg_grade = "-";
if ($conn->query("SHOW TABLES LIKE 'grades'")->num_rows > 0) {
    $grade_stmt = $conn->prepare("SELECT AVG(grade) AS avg_grade FROM grades WHERE student_id = ?");
    $grade_stmt->bind_param("i", $student_id);
    $grade_stmt->execute();
    $grade_result = $grade_stmt->get_result();
    if ($grade_result->num_rows > 0) {
        $row = $grade_result->fetch_assoc();
        $avg_grade = $row['avg_grade'] ? round($row['avg_grade'], 2) : "-";
    }
}

// Get upcoming timetable events (limit to 3)
$timetable_query = $conn->prepare("
    SELECT unit, start_time, end_time, room, day 
    FROM timetable 
    WHERE unit IN (SELECT unit FROM student_units WHERE student_id = ?)
    ORDER BY day, start_time 
    LIMIT 3
");
$timetable_query->bind_param("i", $student_id);
$timetable_query->execute();
$timetable_result = $timetable_query->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .sidebar-header h2 {
            color: var(--primary);
            font-size: 1.5rem;
            margin-left: 0.5rem;
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
            cursor: pointer;
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

        .disabled-link {
            color: var(--gray-light) !important;
            pointer-events: none;
            cursor: default;
        }

        /* Main Content Styles */
        .main {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
            transition: all 0.3s ease;
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
            object-fit: cover;
        }

        .user-info h3 {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .user-info p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Cards Section */
        .cards-section {
            margin-bottom: 2rem;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .card .value {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
        }

        .status {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status.Approved { background-color: #d1fae5; color: #065f46; }
        .status.Pending { background-color: #fef3c7; color: #92400e; }
        .status.Rejected { background-color: #fee2e2; color: #991b1b; }

        /* Sections */
        .section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .section-header a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Timetable */
        .timetable-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .timetable-item:last-child {
            border-bottom: none;
        }

        .time-box {
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.75rem;
            border-radius: 8px;
            text-align: center;
            margin-right: 1rem;
            min-width: 80px;
        }

        .time-box .day {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        .time-box .time {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .timetable-details h3 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .timetable-details p {
            font-size: 0.85rem;
            color: var(--gray);
        }

        .timetable-details p i {
            margin-right: 0.5rem;
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
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
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">S</div>
            <h2>Student Portal</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a onclick="loadContent('dashboard.php')" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a onclick="loadContent('courses.php')"><i class="fas fa-book"></i> <span>My Courses</span></a></li>
            <li><a onclick="loadContent('grades.php')"><i class="fas fa-chart-bar"></i> <span>Grades</span></a></li>
            <li><a onclick="loadContent('timetable.php')"><i class="fas fa-calendar-alt"></i> <span>Timetable</span></a></li>
            <li><a onclick="loadContent('view-attendance.php')"><i class="fas fa-calendar-check"></i> <span>Attendance</span></a></li>
            <li><a href="<?php echo $base_url; ?>logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main" id="main-content">
        <div class="header">
            <h1>Welcome back, <?php echo htmlspecialchars($student['first_name']); ?>!</h1>
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student['first_name'].'+'.$student['last_name']); ?>&background=random" alt="Profile">
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($student['first_name'].' '.$student['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($student['course']); ?></p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="cards-section">
            <div class="cards">
                <div class="card">
                    <h3>Registered Units</h3>
                    <div class="value"><?php echo $unit_count; ?></div>
                </div>
                <div class="card">
                    <h3>Registration Status</h3>
                    <div class="value">
                        <span class="status <?php echo $registration_status; ?>"><?php echo $registration_status; ?></span>
                    </div>
                </div>
                <div class="card">
                    <h3>Average Grade</h3>
                    <div class="value"><?php echo $avg_grade; ?></div>
                </div>
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="section">
            <div class="section-header">
                <h2>Upcoming Classes</h2>
                <a onclick="loadContent('timetable.php')" style="cursor: pointer;">View All</a>
            </div>
            <?php if ($timetable_result->num_rows > 0): ?>
                <?php while ($class = $timetable_result->fetch_assoc()): ?>
                    <div class="timetable-item">
                        <div class="time-box">
                            <div class="day"><?php echo $class['day']; ?></div>
                            <div class="time">
                                <?php 
                                    echo date("g:i A", strtotime($class['start_time'])) . ' - ' . 
                                         date("g:i A", strtotime($class['end_time'])); 
                                ?>
                            </div>
                        </div>
                        <div class="timetable-details">
                            <h3><?php echo htmlspecialchars($class['unit']); ?></h3>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($class['room']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No upcoming classes found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Current active link
let activeLink = document.querySelector('.sidebar-menu a.active');

// Function to load content dynamically
function loadContent(pageUrl) {
    // Update active link in sidebar
    if (activeLink) {
        activeLink.classList.remove('active');
    }
    event.target.classList.add('active');
    activeLink = event.target;
    
    // Show loading state
    const mainContent = document.getElementById('main-content');
    mainContent.innerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; height: 80vh;">
            <div class="loading"></div>
        </div>
    `;
    
    // Load content via AJAX
    fetch(pageUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Extract just the main content from the response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMainContent = doc.querySelector('.main') ? doc.querySelector('.main').innerHTML : html;
            
            // Update main content area
            mainContent.innerHTML = newMainContent;
            
            // Update browser URL without reload
            history.pushState(null, '', pageUrl);
            
            // Update page title
            const newTitle = doc.querySelector('title') ? doc.querySelector('title').text : 'Student Portal';
            document.title = newTitle;
        })
        .catch(err => {
            console.error('Error loading page:', err);
            mainContent.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: var(--danger);">
                    <h2>Error loading content</h2>
                    <p>${err.message}</p>
                    <button onclick="loadContent('dashboard.php')" style="
                        background: var(--primary);
                        color: white;
                        border: none;
                        padding: 0.5rem 1rem;
                        border-radius: 4px;
                        cursor: pointer;
                        margin-top: 1rem;
                    ">Return to Dashboard</button>
                </div>
            `;
        });
}

// Handle browser back/forward
window.addEventListener('popstate', function() {
    loadContent(window.location.pathname);
});

// Make sure all internal links use our loadContent function
document.addEventListener('click', function(e) {
    if (e.target.closest('.main a') && e.target.href && 
        !e.target.href.startsWith('http') && 
        !e.target.href.startsWith('mailto:') && 
        !e.target.href.startsWith('tel:')) {
        e.preventDefault();
        loadContent(e.target.href);
    }
});
</script>
</body>
</html>