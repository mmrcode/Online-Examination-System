<?php
require_once '../includes/functions.php';
check_role('admin');

$success = '';
$error = '';

// Handle student deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $student_id = (int)$_POST['student_id'];
        if ($student_id > 0) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
            $stmt->bind_param("i", $student_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $success = 'Student deleted successfully!';
            } else {
                $error = 'Failed to delete student.';
            }
        }
    }
}

// Get all students with their statistics
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(r.id) as total_exams_taken,
           AVG(r.score) as avg_score,
           MAX(r.score) as best_score
    FROM users u
    LEFT JOIN results r ON u.id = r.user_id
    WHERE u.role = 'student'
    GROUP BY u.id
    ORDER BY u.name
");
$stmt->execute();
$students = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - Admin Dashboard</title>
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
                            <a class="nav-link" href="dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_teachers.php">
                                Manage Teachers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="view_students.php">
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
                    <h1 class="h2">View Students</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportToCSV()">Export CSV</button>
                        </div>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Students List -->
                <div class="card">
                    <div class="card-header">
                        <h5>All Students (<?php echo $students->num_rows; ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Gender</th>
                                        <th>Mobile</th>
                                        <th>College</th>
                                        <th>Exams Taken</th>
                                        <th>Avg Score</th>
                                        <th>Best Score</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $student['id']; ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo ucfirst($student['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                                        <td><?php echo htmlspecialchars($student['college']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $student['total_exams_taken']; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($student['avg_score']): ?>
                                                <span class="badge bg-<?php echo $student['avg_score'] >= 80 ? 'success' : ($student['avg_score'] >= 60 ? 'warning' : 'danger'); ?>">
                                                    <?php echo number_format($student['avg_score'], 1); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['best_score']): ?>
                                                <span class="badge bg-primary"><?php echo number_format($student['best_score'], 1); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($student['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['name']); ?>')">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Total Students</h5>
                                <p class="card-text display-6"><?php echo $students->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Active Students</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as active_students FROM results");
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['active_students'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Avg Performance</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("SELECT AVG(score) as avg_performance FROM results");
                                    $stmt->execute();
                                    $avg = $stmt->get_result()->fetch_assoc()['avg_performance'];
                                    echo $avg ? number_format($avg, 1) : '0';
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Top Performers</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as top_performers FROM results WHERE score >= 80");
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['top_performers'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete student: <strong id="studentName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will also delete all their exam results.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="student_id" id="studentId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteStudent(studentId, studentName) {
            document.getElementById('studentId').value = studentId;
            document.getElementById('studentName').textContent = studentName;
            new bootstrap.Modal(document.getElementById('deleteStudentModal')).show();
        }

        function exportToCSV() {
            const table = document.getElementById('studentsTable');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                
                for (let j = 0; j < cols.length - 1; j++) { // Exclude Actions column
                    rowData.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                }
                
                csv.push(rowData.join(','));
            }
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'students_data.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html> 