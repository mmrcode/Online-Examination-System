<?php
require_once '../includes/functions.php';
check_role('admin');

$success = '';
$error = '';

// Handle feedback deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $feedback_id = (int)$_POST['feedback_id'];
        if ($feedback_id > 0) {
            $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
            $stmt->bind_param("i", $feedback_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $success = 'Feedback deleted successfully!';
            } else {
                $error = 'Failed to delete feedback.';
            }
        }
    }
}

// Get all feedback with student details
$stmt = $conn->prepare("
    SELECT f.*, u.name as student_name, u.email as student_email
    FROM feedback f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC
");
$stmt->execute();
$feedbacks = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback - Admin Dashboard</title>
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
                            <a class="nav-link" href="view_results.php">
                                View Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="view_feedback.php">
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
                    <h1 class="h2">View Feedback</h1>
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

                <!-- Feedback List -->
                <div class="card">
                    <div class="card-header">
                        <h5>All Student Feedback (<?php echo $feedbacks->num_rows; ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($feedbacks->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($feedback['student_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($feedback['student_email']); ?></small>
                                                </div>
                                                <button class="btn btn-sm btn-danger" onclick="deleteFeedback(<?php echo $feedback['id']; ?>, '<?php echo htmlspecialchars($feedback['student_name']); ?>')">
                                                    Delete
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                                                <small class="text-muted">
                                                    Submitted: <?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <h6>No feedback available!</h6>
                                <p>Students haven't submitted any feedback yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Total Feedback</h5>
                                <p class="card-text display-6"><?php echo $feedbacks->num_rows; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">This Month</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(*) as this_month FROM feedback WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['this_month'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">This Week</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(*) as this_week FROM feedback WHERE YEARWEEK(created_at) = YEARWEEK(CURRENT_DATE())");
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['this_week'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Today</h5>
                                <p class="card-text display-6">
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(*) as today FROM feedback WHERE DATE(created_at) = CURRENT_DATE()");
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['today'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Trends -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Feedback Trends</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Recent Feedback</h6>
                                        <div class="list-group list-group-flush">
                                            <?php
                                            $stmt = $conn->prepare("
                                                SELECT f.*, u.name as student_name
                                                FROM feedback f
                                                JOIN users u ON f.user_id = u.id
                                                ORDER BY f.created_at DESC
                                                LIMIT 5
                                            ");
                                            $stmt->execute();
                                            $recent_feedback = $stmt->get_result();
                                            
                                            while ($recent = $recent_feedback->fetch_assoc()) {
                                                echo "<div class='list-group-item'>";
                                                echo "<strong>" . htmlspecialchars($recent['student_name']) . "</strong>";
                                                echo "<br><small class='text-muted'>" . substr(htmlspecialchars($recent['message']), 0, 100) . "...</small>";
                                                echo "<br><small class='text-muted'>" . date('M j, Y g:i A', strtotime($recent['created_at'])) . "</small>";
                                                echo "</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Feedback by Month</h6>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT 
                                                DATE_FORMAT(created_at, '%Y-%m') as month,
                                                COUNT(*) as count
                                            FROM feedback 
                                            GROUP BY month
                                            ORDER BY month DESC
                                            LIMIT 6
                                        ");
                                        $stmt->execute();
                                        $monthly_feedback = $stmt->get_result();
                                        
                                        while ($month = $monthly_feedback->fetch_assoc()) {
                                            echo "<div class='mb-2'>";
                                            echo "<div class='d-flex justify-content-between'>";
                                            echo "<span>" . date('M Y', strtotime($month['month'] . '-01')) . "</span>";
                                            echo "<span>" . $month['count'] . " feedback</span>";
                                            echo "</div>";
                                            echo "<div class='progress'>";
                                            echo "<div class='progress-bar' style='width: " . ($month['count'] * 10) . "%'></div>";
                                            echo "</div>";
                                            echo "</div>";
                                        }
                                        ?>
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
    <div class="modal fade" id="deleteFeedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete feedback from: <strong id="feedbackStudentName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="feedback_id" id="feedbackId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteFeedback(feedbackId, studentName) {
            document.getElementById('feedbackId').value = feedbackId;
            document.getElementById('feedbackStudentName').textContent = studentName;
            new bootstrap.Modal(document.getElementById('deleteFeedbackModal')).show();
        }

        function exportToCSV() {
            const feedbacks = <?php echo json_encode($feedbacks->fetch_all(MYSQLI_ASSOC)); ?>;
            let csv = ['Student Name,Student Email,Message,Date\n'];
            
            feedbacks.forEach(feedback => {
                const row = [
                    `"${feedback.student_name}"`,
                    `"${feedback.student_email}"`,
                    `"${feedback.message.replace(/"/g, '""')}"`,
                    `"${feedback.created_at}"`
                ];
                csv.push(row.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'student_feedback.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html> 