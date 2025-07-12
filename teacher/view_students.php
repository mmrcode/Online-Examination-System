<?php
require_once '../includes/functions.php';
check_role('teacher');

$teacher_id = $_SESSION['user_id'];

// Get all students who have taken exams by this teacher
$stmt = $conn->prepare("
    SELECT DISTINCT u.*, 
           COUNT(r.id) as total_exams_taken,
           AVG(r.score) as avg_score,
           MAX(r.score) as best_score
    FROM users u
    JOIN results r ON u.id = r.user_id
    JOIN exams e ON r.exam_id = e.id
    WHERE e.created_by = ? AND u.role = 'student'
    GROUP BY u.id
    ORDER BY u.name
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$students = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - Teacher Dashboard</title>
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
                        <h5 class="text-white">Teacher Panel</h5>
                        <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_exam.php">
                                Create Exam
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_questions.php">
                                Add Questions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="view_students.php">
                                View Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ranking.php">
                                Rankings
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

                <!-- Students List -->
                <div class="card">
                    <div class="card-header">
                        <h5>Students Who Have Taken Your Exams (<?php echo $students->num_rows; ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>College</th>
                                        <th>Exams Taken</th>
                                        <th>Average Score</th>
                                        <th>Best Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($student = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
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
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewStudentDetails(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['name']); ?>')">
                                                View Details
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
                                <h5 class="card-title">Average Performance</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("
                                        SELECT AVG(r.score) as avg_performance 
                                        FROM results r 
                                        JOIN exams e ON r.exam_id = e.id 
                                        WHERE e.created_by = ?
                                    ");
                                    $stmt->bind_param("i", $teacher_id);
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
                                <h5 class="card-title">Pass Rate</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("
                                        SELECT (COUNT(CASE WHEN r.score >= 60 THEN 1 END) * 100.0 / COUNT(*)) as pass_rate 
                                        FROM results r 
                                        JOIN exams e ON r.exam_id = e.id 
                                        WHERE e.created_by = ?
                                    ");
                                    $stmt->bind_param("i", $teacher_id);
                                    $stmt->execute();
                                    $pass_rate = $stmt->get_result()->fetch_assoc()['pass_rate'];
                                    echo $pass_rate ? number_format($pass_rate, 1) : '0';
                                    ?>%
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
                                    $stmt = $conn->prepare("
                                        SELECT COUNT(DISTINCT r.user_id) as top_performers 
                                        FROM results r 
                                        JOIN exams e ON r.exam_id = e.id 
                                        WHERE e.created_by = ? AND r.score >= 80
                                    ");
                                    $stmt->bind_param("i", $teacher_id);
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

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Details: <span id="studentName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="studentDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewStudentDetails(studentId, studentName) {
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('studentDetailsContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Load student details via AJAX
            fetch(`get_student_details.php?student_id=${studentId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('studentDetailsContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('studentDetailsContent').innerHTML = '<div class="alert alert-danger">Error loading student details.</div>';
                });
            
            new bootstrap.Modal(document.getElementById('studentDetailsModal')).show();
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