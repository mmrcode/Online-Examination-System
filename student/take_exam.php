<?php
require_once '../includes/functions.php';
check_role('student');

$student_id = $_SESSION['user_id'];

// Get available exams (exams not taken by this student)
$stmt = $conn->prepare("
    SELECT e.*, COUNT(q.id) as question_count, u.name as teacher_name
    FROM exams e 
    LEFT JOIN questions q ON e.id = q.exam_id
    JOIN users u ON e.created_by = u.id
    WHERE e.id NOT IN (SELECT exam_id FROM results WHERE user_id = ?)
    GROUP BY e.id
    HAVING question_count > 0
    ORDER BY e.created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$available_exams = $stmt->get_result();

// Get completed exams
$stmt = $conn->prepare("
    SELECT e.*, r.score, r.date, rnk.rank_position
    FROM results r
    JOIN exams e ON r.exam_id = e.id
    LEFT JOIN ranking rnk ON r.user_id = rnk.user_id AND r.exam_id = rnk.exam_id
    WHERE r.user_id = ?
    ORDER BY r.date DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$completed_exams = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam - Student Dashboard</title>
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
                            <a class="nav-link active" href="take_exam.php">
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
                    <h1 class="h2">Take Exam</h1>
                    <a href="history.php" class="btn btn-outline-secondary">View History</a>
                </div>

                <!-- Available Exams -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Available Exams</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($available_exams->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($exam = $available_exams->fetch_assoc()): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($exam['title']); ?></h6>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <strong>Teacher:</strong> <?php echo htmlspecialchars($exam['teacher_name']); ?><br>
                                                        <strong>Duration:</strong> <?php echo $exam['duration']; ?> minutes<br>
                                                        <strong>Questions:</strong> <?php echo $exam['question_count']; ?><br>
                                                        <strong>Total Marks:</strong> <?php echo $exam['total_marks']; ?><br>
                                                        <strong>Positive Marks:</strong> <?php echo $exam['positive_marks']; ?><br>
                                                        <strong>Negative Marks:</strong> <?php echo $exam['negative_marks']; ?>
                                                    </small>
                                                </p>
                                            </div>
                                            <div class="card-footer">
                                                <a href="start_exam.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-primary btn-sm w-100">
                                                    Start Exam
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <h6>No exams available!</h6>
                                <p>There are currently no exams available for you to take. Please check back later or contact your teacher.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Completed Exams -->
                <div class="card">
                    <div class="card-header">
                        <h5>Recently Completed Exams</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($completed_exams->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Exam Title</th>
                                            <th>Score</th>
                                            <th>Rank</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($exam = $completed_exams->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($exam['title']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $exam['score'] >= 80 ? 'success' : ($exam['score'] >= 60 ? 'warning' : 'danger'); ?>">
                                                        <?php echo $exam['score']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($exam['rank_position']): ?>
                                                        <span class="badge bg-info"><?php echo $exam['rank_position']; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($exam['date'])); ?></td>
                                                <td>
                                                    <a href="result.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        View Result
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h6>No completed exams yet!</h6>
                                <p>You haven't completed any exams yet. Start with an available exam above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 