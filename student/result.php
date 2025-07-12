<?php
require_once '../includes/functions.php';
check_role('student');

$student_id = $_SESSION['user_id'];
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

if ($exam_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Get student's result for this exam
$stmt = $conn->prepare("
    SELECT r.*, e.title as exam_title, e.total_marks, e.positive_marks, e.negative_marks
    FROM results r
    JOIN exams e ON r.exam_id = e.id
    WHERE r.user_id = ? AND r.exam_id = ?
");
$stmt->bind_param("ii", $student_id, $exam_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    header("Location: dashboard.php");
    exit();
}

// Get student's ranking
$ranking = get_user_ranking($student_id, $exam_id);

// Get top 10 rankings for this exam
$stmt = $conn->prepare("
    SELECT rnk.*, u.name as student_name
    FROM ranking rnk
    JOIN users u ON rnk.user_id = u.id
    WHERE rnk.exam_id = ?
    ORDER BY rnk.rank_position
    LIMIT 10
");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$top_rankings = $stmt->get_result();

// Calculate percentage
$percentage = ($result['score'] / $result['total_marks']) * 100;
$grade = '';
if ($percentage >= 90) $grade = 'A+';
elseif ($percentage >= 80) $grade = 'A';
elseif ($percentage >= 70) $grade = 'B';
elseif ($percentage >= 60) $grade = 'C';
elseif ($percentage >= 50) $grade = 'D';
else $grade = 'F';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result - Online Examination System</title>
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
                    <h1 class="h2">Exam Result</h1>
                    <div>
                        <a href="take_exam.php" class="btn btn-outline-primary">Take Another Exam</a>
                        <a href="history.php" class="btn btn-outline-secondary">View History</a>
                    </div>
                </div>

                <!-- Result Summary -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo htmlspecialchars($result['exam_title']); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Your Performance</h6>
                                        <div class="mb-3">
                                            <strong>Score:</strong> 
                                            <span class="badge bg-<?php echo $result['score'] >= 80 ? 'success' : ($result['score'] >= 60 ? 'warning' : 'danger'); ?> fs-6">
                                                <?php echo $result['score']; ?> / <?php echo $result['total_marks']; ?>
                                            </span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Percentage:</strong> <?php echo number_format($percentage, 1); ?>%
                                        </div>
                                        <div class="mb-3">
                                            <strong>Grade:</strong> 
                                            <span class="badge bg-<?php echo $grade == 'A+' || $grade == 'A' ? 'success' : ($grade == 'B' || $grade == 'C' ? 'warning' : 'danger'); ?>">
                                                <?php echo $grade; ?>
                                            </span>
                                        </div>
                                        <?php if ($ranking): ?>
                                        <div class="mb-3">
                                            <strong>Rank:</strong> 
                                            <span class="badge bg-info"><?php echo $ranking; ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Detailed Breakdown</h6>
                                        <div class="mb-3">
                                            <strong>Total Questions:</strong> <?php echo $result['total_questions']; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Correct Answers:</strong> 
                                            <span class="text-success"><?php echo $result['correct_answers']; ?></span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Wrong Answers:</strong> 
                                            <span class="text-danger"><?php echo $result['wrong_answers']; ?></span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Unanswered:</strong> 
                                            <span class="text-muted"><?php echo $result['total_questions'] - $result['correct_answers'] - $result['wrong_answers']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h6>Marking Scheme</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <strong>Positive Marks:</strong> <?php echo $result['positive_marks']; ?> per correct answer
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <strong>Negative Marks:</strong> <?php echo $result['negative_marks']; ?> per wrong answer
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($result['date'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Top 10 Rankings</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($top_rankings->num_rows > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php while ($rank = $top_rankings->fetch_assoc()): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <?php if ($rank['rank_position'] <= 3): ?>
                                                        <span class="badge bg-warning me-2"><?php echo $rank['rank_position']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary me-2"><?php echo $rank['rank_position']; ?></span>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($rank['student_name']); ?>
                                                </div>
                                                <span class="badge bg-primary"><?php echo $rank['score']; ?></span>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No rankings available yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5>Performance Analysis</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Accuracy Rate</label>
                                    <div class="progress">
                                        <?php $accuracy = $result['total_questions'] > 0 ? ($result['correct_answers'] / $result['total_questions']) * 100 : 0; ?>
                                        <div class="progress-bar bg-success" style="width: <?php echo $accuracy; ?>%">
                                            <?php echo number_format($accuracy, 1); ?>%
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Score Achievement</label>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%">
                                            <?php echo number_format($percentage, 1); ?>%
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <?php if ($percentage >= 80): ?>
                                        <div class="alert alert-success">
                                            <strong>Excellent!</strong> Great job on this exam!
                                        </div>
                                    <?php elseif ($percentage >= 60): ?>
                                        <div class="alert alert-warning">
                                            <strong>Good!</strong> Keep up the good work!
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-danger">
                                            <strong>Need Improvement</strong> Review the material and try again!
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <a href="take_exam.php" class="btn btn-primary">Take Another Exam</a>
                        <a href="history.php" class="btn btn-secondary">View All Results</a>
                        <a href="feedback.php" class="btn btn-outline-info">Submit Feedback</a>
                        <button onclick="window.print()" class="btn btn-outline-dark">Print Result</button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 