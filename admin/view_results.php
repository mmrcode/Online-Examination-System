<?php
require_once '../includes/functions.php';
check_role('admin');

$success = '';
$error = '';

// Handle result deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $result_id = (int)$_POST['result_id'];
        if ($result_id > 0) {
            $stmt = $conn->prepare("DELETE FROM results WHERE id = ?");
            $stmt->bind_param("i", $result_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $success = 'Result deleted successfully!';
            } else {
                $error = 'Failed to delete result.';
            }
        }
    }
}

// Get all results with student and exam details
$stmt = $conn->prepare("
    SELECT r.*, u.name as student_name, u.email as student_email, e.title as exam_title, t.name as teacher_name
    FROM results r
    JOIN users u ON r.user_id = u.id
    JOIN exams e ON r.exam_id = e.id
    JOIN users t ON e.created_by = t.id
    ORDER BY r.date DESC
");
$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - Admin Dashboard</title>
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
                            <a class="nav-link" href="view_students.php">
                                View Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="view_results.php">
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
                    <h1 class="h2">View Results</h1>
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

                <!-- Results List -->
                <div class="card">
                    <div class="card-header">
                        <h5>All Exam Results (<?php echo $results->num_rows; ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="resultsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Student</th>
                                        <th>Exam</th>
                                        <th>Teacher</th>
                                        <th>Score</th>
                                        <th>Correct</th>
                                        <th>Wrong</th>
                                        <th>Accuracy</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($result = $results->fetch_assoc()): ?>
                                        <?php 
                                        $accuracy = $result['total_questions'] > 0 ? 
                                            ($result['correct_answers'] / $result['total_questions']) * 100 : 0;
                                        ?>
                                    <tr>
                                        <td><?php echo $result['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($result['student_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($result['student_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($result['exam_title']); ?></td>
                                        <td><?php echo htmlspecialchars($result['teacher_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $result['score'] >= 80 ? 'success' : ($result['score'] >= 60 ? 'warning' : 'danger'); ?> fs-6">
                                                <?php echo $result['score']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success"><?php echo $result['correct_answers']; ?></span>
                                        </td>
                                        <td>
                                            <span class="text-danger"><?php echo $result['wrong_answers']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $accuracy >= 80 ? 'success' : ($accuracy >= 60 ? 'warning' : 'danger'); ?>">
                                                <?php echo number_format($accuracy, 1); ?>%
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($result['date'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="deleteResult(<?php echo $result['id']; ?>, '<?php echo htmlspecialchars($result['student_name']); ?>')">
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
                                <h5 class="card-title">Total Results</h5>
                                <p class="card-text display-6"><?php echo $results->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Average Score</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("SELECT AVG(score) as avg_score FROM results");
                                    $stmt->execute();
                                    $avg = $stmt->get_result()->fetch_assoc()['avg_score'];
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
                                    $stmt = $conn->prepare("SELECT 
                                        (COUNT(CASE WHEN score >= 60 THEN 1 END) * 100.0 / COUNT(*)) as pass_rate 
                                        FROM results");
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
                                <h5 class="card-title">Excellence Rate</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("SELECT 
                                        (COUNT(CASE WHEN score >= 80 THEN 1 END) * 100.0 / COUNT(*)) as excellence_rate 
                                        FROM results");
                                    $stmt->execute();
                                    $excellence_rate = $stmt->get_result()->fetch_assoc()['excellence_rate'];
                                    echo $excellence_rate ? number_format($excellence_rate, 1) : '0';
                                    ?>%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Score Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Score Ranges</h6>
                                        <?php
                                        $stmt = $conn->prepare("SELECT 
                                            CASE 
                                                WHEN score >= 90 THEN '90-100'
                                                WHEN score >= 80 THEN '80-89'
                                                WHEN score >= 70 THEN '70-79'
                                                WHEN score >= 60 THEN '60-69'
                                                ELSE 'Below 60'
                                            END as score_range,
                                            COUNT(*) as count
                                        FROM results 
                                        GROUP BY score_range
                                        ORDER BY MIN(score) DESC");
                                        $stmt->execute();
                                        $score_distribution = $stmt->get_result();
                                        
                                        while ($range = $score_distribution->fetch_assoc()) {
                                            $percentage = ($range['count'] / $results->num_rows) * 100;
                                            echo "<div class='mb-2'>";
                                            echo "<div class='d-flex justify-content-between'>";
                                            echo "<span>" . $range['score_range'] . "</span>";
                                            echo "<span>" . $range['count'] . " results</span>";
                                            echo "</div>";
                                            echo "<div class='progress'>";
                                            echo "<div class='progress-bar' style='width: " . $percentage . "%'></div>";
                                            echo "</div>";
                                            echo "</div>";
                                        }
                                        ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Recent Activity</h6>
                                        <div class="list-group list-group-flush">
                                            <?php
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
                                            
                                            while ($recent = $recent_results->fetch_assoc()) {
                                                echo "<div class='list-group-item'>";
                                                echo "<strong>" . htmlspecialchars($recent['student_name']) . "</strong> scored ";
                                                echo "<span class='badge bg-primary'>" . $recent['score'] . "</span> in ";
                                                echo "<strong>" . htmlspecialchars($recent['exam_title']) . "</strong>";
                                                echo "<br><small class='text-muted'>" . date('M j, Y g:i A', strtotime($recent['date'])) . "</small>";
                                                echo "</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteResultModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the result for: <strong id="resultStudentName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="result_id" id="resultId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteResult(resultId, studentName) {
            document.getElementById('resultId').value = resultId;
            document.getElementById('resultStudentName').textContent = studentName;
            new bootstrap.Modal(document.getElementById('deleteResultModal')).show();
        }

        function exportToCSV() {
            const table = document.getElementById('resultsTable');
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
            a.download = 'exam_results.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html> 