<?php
require_once '../includes/functions.php';
check_role('student');

$student_id = $_SESSION['user_id'];

// Get all student's exam results
$stmt = $conn->prepare("
    SELECT r.*, e.title as exam_title, e.total_marks, t.name as teacher_name, rnk.rank_position
    FROM results r
    JOIN exams e ON r.exam_id = e.id
    JOIN users t ON e.created_by = t.id
    LEFT JOIN ranking rnk ON r.user_id = rnk.user_id AND r.exam_id = rnk.exam_id
    WHERE r.user_id = ?
    ORDER BY r.date DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam History - Student Dashboard</title>
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
                        <h5 class="text-white">Student Panel</h5>
                        <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="take_exam.php">
                                Take Exam
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="history.php">
                                Exam History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="feedback.php">
                                Submit Feedback
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
                    <h1 class="h2">Exam History</h1>
                    <div>
                        <a href="take_exam.php" class="btn btn-outline-primary">Take New Exam</a>
                        <button onclick="window.print()" class="btn btn-outline-secondary">Print History</button>
                    </div>
                </div>

                <?php if ($results->num_rows > 0): ?>
                    <!-- Results Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5>All Exam Results (<?php echo $results->num_rows; ?> total)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Exam</th>
                                            <th>Teacher</th>
                                            <th>Score</th>
                                            <th>Percentage</th>
                                            <th>Rank</th>
                                            <th>Correct</th>
                                            <th>Wrong</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($result = $results->fetch_assoc()): ?>
                                            <?php $percentage = ($result['score'] / $result['total_marks']) * 100; ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($result['exam_title']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($result['teacher_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $result['score'] >= 80 ? 'success' : ($result['score'] >= 60 ? 'warning' : 'danger'); ?> fs-6">
                                                        <?php echo $result['score']; ?> / <?php echo $result['total_marks']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger'); ?>">
                                                        <?php echo number_format($percentage, 1); ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($result['rank_position']): ?>
                                                        <span class="badge bg-info"><?php echo $result['rank_position']; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="text-success"><?php echo $result['correct_answers']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-danger"><?php echo $result['wrong_answers']; ?></span>
                                                </td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($result['date'])); ?></td>
                                                <td>
                                                    <a href="result.php?exam_id=<?php echo $result['exam_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Summary -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Performance Summary</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Calculate performance statistics
                                    $stmt = $conn->prepare("
                                        SELECT 
                                            AVG(score) as avg_score,
                                            MAX(score) as best_score,
                                            MIN(score) as worst_score,
                                            COUNT(*) as total_exams
                                        FROM results 
                                        WHERE user_id = ?
                                    ");
                                    $stmt->bind_param("i", $student_id);
                                    $stmt->execute();
                                    $stats = $stmt->get_result()->fetch_assoc();
                                    ?>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Total Exams:</strong> <?php echo $stats['total_exams']; ?></p>
                                            <p><strong>Average Score:</strong> <?php echo number_format($stats['avg_score'], 1); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Best Score:</strong> <?php echo number_format($stats['best_score'], 1); ?></p>
                                            <p><strong>Worst Score:</strong> <?php echo number_format($stats['worst_score'], 1); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Score Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $stmt = $conn->prepare("
                                        SELECT 
                                            CASE 
                                                WHEN score >= 90 THEN 'A+ (90-100)'
                                                WHEN score >= 80 THEN 'A (80-89)'
                                                WHEN score >= 70 THEN 'B (70-79)'
                                                WHEN score >= 60 THEN 'C (60-69)'
                                                ELSE 'F (Below 60)'
                                            END as grade_range,
                                            COUNT(*) as count
                                        FROM results 
                                        WHERE user_id = ?
                                        GROUP BY grade_range
                                        ORDER BY MIN(score) DESC
                                    ");
                                    $stmt->bind_param("i", $student_id);
                                    $stmt->execute();
                                    $grade_distribution = $stmt->get_result();
                                    
                                    while ($grade = $grade_distribution->fetch_assoc()) {
                                        $percentage = ($grade['count'] / $stats['total_exams']) * 100;
                                        echo "<div class='mb-2'>";
                                        echo "<div class='d-flex justify-content-between'>";
                                        echo "<span>" . $grade['grade_range'] . "</span>";
                                        echo "<span>" . $grade['count'] . " exams</span>";
                                        echo "</div>";
                                        echo "<div class='progress'>";
                                        echo "<div class='progress-bar' style='width: " . $percentage . "%'></div>";
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Performance Trend -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Performance Trend</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT score, date, e.title as exam_title
                                            FROM results r
                                            JOIN exams e ON r.exam_id = e.id
                                            WHERE r.user_id = ?
                                            ORDER BY r.date DESC
                                            LIMIT 10
                                        ");
                                        $stmt->bind_param("i", $student_id);
                                        $stmt->execute();
                                        $recent_results = $stmt->get_result();
                                        ?>
                                        <div class="col-md-8">
                                            <h6>Last 10 Exams</h6>
                                            <div class="list-group">
                                                <?php while ($recent = $recent_results->fetch_assoc()): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($recent['exam_title']); ?></strong>
                                                            <br><small class="text-muted"><?php echo date('M j, Y', strtotime($recent['date'])); ?></small>
                                                        </div>
                                                        <span class="badge bg-<?php echo $recent['score'] >= 80 ? 'success' : ($recent['score'] >= 60 ? 'warning' : 'danger'); ?> fs-6">
                                                            <?php echo $recent['score']; ?>
                                                        </span>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Quick Stats</h6>
                                            <div class="list-group list-group-flush">
                                                <div class="list-group-item d-flex justify-content-between">
                                                    <span>Pass Rate</span>
                                                    <span>
                                                        <?php
                                                        $stmt = $conn->prepare("SELECT (COUNT(CASE WHEN score >= 60 THEN 1 END) * 100.0 / COUNT(*)) as pass_rate FROM results WHERE user_id = ?");
                                                        $stmt->bind_param("i", $student_id);
                                                        $stmt->execute();
                                                        $pass_rate = $stmt->get_result()->fetch_assoc()['pass_rate'];
                                                        echo number_format($pass_rate, 1) . '%';
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between">
                                                    <span>Excellence Rate</span>
                                                    <span>
                                                        <?php
                                                        $stmt = $conn->prepare("SELECT (COUNT(CASE WHEN score >= 80 THEN 1 END) * 100.0 / COUNT(*)) as excellence_rate FROM results WHERE user_id = ?");
                                                        $stmt->bind_param("i", $student_id);
                                                        $stmt->execute();
                                                        $excellence_rate = $stmt->get_result()->fetch_assoc()['excellence_rate'];
                                                        echo number_format($excellence_rate, 1) . '%';
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between">
                                                    <span>Total Questions</span>
                                                    <span>
                                                        <?php
                                                        $stmt = $conn->prepare("SELECT SUM(total_questions) as total_questions FROM results WHERE user_id = ?");
                                                        $stmt->bind_param("i", $student_id);
                                                        $stmt->execute();
                                                        echo $stmt->get_result()->fetch_assoc()['total_questions'];
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h6>No exam history found!</h6>
                        <p>You haven't taken any exams yet. <a href="take_exam.php">Start with your first exam</a>.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 