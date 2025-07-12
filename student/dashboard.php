<?php
require_once '../includes/functions.php';
check_role('student');

$student_id = $_SESSION['user_id'];

// Get student statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_exams_taken FROM results WHERE user_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$exams_taken = $stmt->get_result()->fetch_assoc()['total_exams_taken'];

$stmt = $conn->prepare("SELECT AVG(score) as avg_score FROM results WHERE user_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$avg_score = $stmt->get_result()->fetch_assoc()['avg_score'];

$stmt = $conn->prepare("SELECT MAX(score) as best_score FROM results WHERE user_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$best_score = $stmt->get_result()->fetch_assoc()['best_score'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_rankings FROM ranking WHERE user_id = ? AND rank_position = 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$first_place = $stmt->get_result()->fetch_assoc()['total_rankings'];

// Get available exams (exams not taken by this student)
$stmt = $conn->prepare("
    SELECT e.*, COUNT(q.id) as question_count 
    FROM exams e 
    LEFT JOIN questions q ON e.id = q.exam_id
    WHERE e.id NOT IN (SELECT exam_id FROM results WHERE user_id = ?)
    GROUP BY e.id
    HAVING question_count > 0
    ORDER BY e.created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$available_exams = $stmt->get_result();

// Get recent results
$stmt = $conn->prepare("
    SELECT r.*, e.title as exam_title, rnk.rank_position
    FROM results r
    JOIN exams e ON r.exam_id = e.id
    LEFT JOIN ranking rnk ON r.user_id = rnk.user_id AND r.exam_id = rnk.exam_id
    WHERE r.user_id = ?
    ORDER BY r.date DESC
    LIMIT 5
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$recent_results = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Online Examination System</title>
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
                            <a class="nav-link active" href="dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="take_exam.php">
                                Take Exam
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="history.php">
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
                    <h1 class="h2">Student Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="take_exam.php" class="btn btn-sm btn-outline-primary">Take Exam</a>
                            <a href="history.php" class="btn btn-sm btn-outline-success">View History</a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $exams_taken; ?></h3>
                            <p>Exams Taken</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo number_format($avg_score, 1); ?></h3>
                            <p>Average Score</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo number_format($best_score, 1); ?></h3>
                            <p>Best Score</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $first_place; ?></h3>
                            <p>First Place</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Available Exams -->
                    <div class="col-md-6">
                        <div class="quick-actions">
                            <h5>Available Exams</h5>
                            <?php if ($available_exams->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($exam = $available_exams->fetch_assoc()): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($exam['title']); ?></strong>
                                                    <br><small class="text-muted">
                                                        Duration: <?php echo $exam['duration']; ?> min | 
                                                        Questions: <?php echo $exam['question_count']; ?> |
                                                        Total Marks: <?php echo $exam['total_marks']; ?>
                                                    </small>
                                                </div>
                                                <a href="start_exam.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-primary">
                                                    Start Exam
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    No exams available at the moment. Check back later!
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Results -->
                    <div class="col-md-6">
                        <div class="quick-actions">
                            <h5>Recent Results</h5>
                            <?php if ($recent_results->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($result = $recent_results->fetch_assoc()): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($result['exam_title']); ?></strong>
                                                    <br><small class="text-muted">
                                                        Score: <span class="badge bg-primary"><?php echo $result['score']; ?></span> |
                                                        Correct: <?php echo $result['correct_answers']; ?> |
                                                        Wrong: <?php echo $result['wrong_answers']; ?>
                                                    </small>
                                                    <?php if ($result['rank_position']): ?>
                                                        <br><small class="text-success">
                                                            Rank: <?php echo $result['rank_position']; ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y', strtotime($result['date'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="history.php" class="btn btn-outline-primary">View All Results</a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    You haven't taken any exams yet. Start with an available exam!
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <?php if ($exams_taken > 0): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Performance Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Score Distribution</h6>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT 
                                                CASE 
                                                    WHEN score >= 90 THEN '90-100'
                                                    WHEN score >= 80 THEN '80-89'
                                                    WHEN score >= 70 THEN '70-79'
                                                    WHEN score >= 60 THEN '60-69'
                                                    ELSE 'Below 60'
                                                END as score_range,
                                                COUNT(*) as count
                                            FROM results 
                                            WHERE user_id = ?
                                            GROUP BY score_range
                                            ORDER BY MIN(score) DESC
                                        ");
                                        $stmt->bind_param("i", $student_id);
                                        $stmt->execute();
                                        $score_distribution = $stmt->get_result();
                                        
                                        while ($range = $score_distribution->fetch_assoc()) {
                                            $percentage = ($range['count'] / $exams_taken) * 100;
                                            echo "<div class='mb-2'>";
                                            echo "<div class='d-flex justify-content-between'>";
                                            echo "<span>" . $range['score_range'] . "</span>";
                                            echo "<span>" . $range['count'] . " exams</span>";
                                            echo "</div>";
                                            echo "<div class='progress'>";
                                            echo "<div class='progress-bar' style='width: " . $percentage . "%'></div>";
                                            echo "</div>";
                                            echo "</div>";
                                        }
                                        ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Recent Performance Trend</h6>
                                        <p class="text-muted">Your performance over the last 5 exams:</p>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT score, date 
                                            FROM results 
                                            WHERE user_id = ? 
                                            ORDER BY date DESC 
                                            LIMIT 5
                                        ");
                                        $stmt->bind_param("i", $student_id);
                                        $stmt->execute();
                                        $recent_scores = $stmt->get_result();
                                        
                                        while ($score = $recent_scores->fetch_assoc()) {
                                            $color = $score['score'] >= 80 ? 'success' : ($score['score'] >= 60 ? 'warning' : 'danger');
                                            echo "<div class='d-flex justify-content-between align-items-center mb-2'>";
                                            echo "<span>" . date('M j', strtotime($score['date'])) . "</span>";
                                            echo "<span class='badge bg-" . $color . "'>" . $score['score'] . "</span>";
                                            echo "</div>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 