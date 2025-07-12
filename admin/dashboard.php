<?php
require_once '../includes/functions.php';
check_role('admin');

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_students FROM users WHERE role = 'student'");
$stmt->execute();
$students_count = $stmt->get_result()->fetch_assoc()['total_students'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_teachers FROM users WHERE role = 'teacher'");
$stmt->execute();
$teachers_count = $stmt->get_result()->fetch_assoc()['total_teachers'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_exams FROM exams");
$stmt->execute();
$exams_count = $stmt->get_result()->fetch_assoc()['total_exams'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_results FROM results");
$stmt->execute();
$results_count = $stmt->get_result()->fetch_assoc()['total_results'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_feedback FROM feedback");
$stmt->execute();
$feedback_count = $stmt->get_result()->fetch_assoc()['total_feedback'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Examination System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Admin Panel</h5>
                        <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_teachers.php">
                                Manage Teachers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_students.php">
                                View Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_results.php">
                                View Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_feedback.php">
                                View Feedback
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="manage_teachers.php" class="btn btn-sm btn-outline-primary">Manage Teachers</a>
                            <a href="view_students.php" class="btn btn-sm btn-outline-success">View Students</a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $students_count; ?></h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $teachers_count; ?></h3>
                            <p>Total Teachers</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $exams_count; ?></h3>
                            <p>Total Exams</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $results_count; ?></h3>
                            <p>Total Results</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="quick-actions">
                            <h5>Quick Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="manage_teachers.php" class="btn btn-primary">Add New Teacher</a>
                                <a href="view_students.php" class="btn btn-success">View All Students</a>
                                <a href="view_results.php" class="btn btn-info">View Exam Results</a>
                                <a href="view_feedback.php" class="btn btn-warning">View Feedback (<?php echo $feedback_count; ?>)</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-actions">
                            <h5>Recent Activity</h5>
                            <div class="list-group list-group-flush">
                                <?php
                                // Get recent results
                                $stmt = $conn->prepare("
                                    SELECT r.*, u.name as student_name, e.title as exam_title 
                                    FROM results r 
                                    JOIN users u ON r.user_id = u.id 
                                    JOIN exams e ON r.exam_id = e.id 
                                    ORDER BY r.date DESC 
                                    LIMIT 5
                                ");
                                $stmt->execute();
                                $recent_results = $stmt->get_result();
                                
                                while ($result = $recent_results->fetch_assoc()) {
                                    echo "<div class='list-group-item'>";
                                    echo "<strong>" . htmlspecialchars($result['student_name']) . "</strong> completed ";
                                    echo "<strong>" . htmlspecialchars($result['exam_title']) . "</strong> ";
                                    echo "with score: <span class='badge bg-primary'>" . $result['score'] . "</span>";
                                    echo "<br><small class='text-muted'>" . date('M j, Y g:i A', strtotime($result['date'])) . "</small>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>System Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                                        <p><strong>MySQL Version:</strong> <?php echo $conn->server_info; ?></p>
                                        <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Total Users:</strong> <?php echo $students_count + $teachers_count + 1; ?></p>
                                        <p><strong>Database Size:</strong> Calculating...</p>
                                        <p><strong>Last Backup:</strong> Not configured</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 