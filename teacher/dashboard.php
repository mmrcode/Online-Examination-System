<?php
require_once '../includes/functions.php';
check_role('teacher');

$teacher_id = $_SESSION['user_id'];

// Get statistics for this teacher
$stmt = $conn->prepare("SELECT COUNT(*) as total_exams FROM exams WHERE created_by = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$exams_count = $stmt->get_result()->fetch_assoc()['total_exams'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as total_questions 
    FROM questions q 
    JOIN exams e ON q.exam_id = e.id 
    WHERE e.created_by = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$questions_count = $stmt->get_result()->fetch_assoc()['total_questions'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as total_results 
    FROM results r 
    JOIN exams e ON r.exam_id = e.id 
    WHERE e.created_by = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$results_count = $stmt->get_result()->fetch_assoc()['total_results'];

$stmt = $conn->prepare("
    SELECT AVG(r.score) as avg_score 
    FROM results r 
    JOIN exams e ON r.exam_id = e.id 
    WHERE e.created_by = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$avg_score = $stmt->get_result()->fetch_assoc()['avg_score'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Online Examination System</title>
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
                            <a class="nav-link active" href="dashboard.php">
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
                            <a class="nav-link" href="view_students.php">
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
                    <h1 class="h2">Teacher Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="create_exam.php" class="btn btn-sm btn-outline-primary">Create Exam</a>
                            <a href="add_questions.php" class="btn btn-sm btn-outline-success">Add Questions</a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $exams_count; ?></h3>
                            <p>Total Exams</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $questions_count; ?></h3>
                            <p>Total Questions</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo $results_count; ?></h3>
                            <p>Total Results</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stats">
                            <h3><?php echo number_format($avg_score, 1); ?></h3>
                            <p>Average Score</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="quick-actions">
                            <h5>Quick Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="create_exam.php" class="btn btn-primary">Create New Exam</a>
                                <a href="add_questions.php" class="btn btn-success">Add Questions to Exam</a>
                                <a href="view_students.php" class="btn btn-info">View Student Results</a>
                                <a href="ranking.php" class="btn btn-warning">View Rankings</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-actions">
                            <h5>Recent Exams</h5>
                            <div class="list-group list-group-flush">
                                <?php
                                // Get recent exams by this teacher
                                $stmt = $conn->prepare("
                                    SELECT e.*, COUNT(q.id) as question_count, COUNT(r.id) as result_count
                                    FROM exams e 
                                    LEFT JOIN questions q ON e.id = q.exam_id
                                    LEFT JOIN results r ON e.id = r.exam_id
                                    WHERE e.created_by = ?
                                    GROUP BY e.id
                                    ORDER BY e.created_at DESC 
                                    LIMIT 5
                                ");
                                $stmt->bind_param("i", $teacher_id);
                                $stmt->execute();
                                $recent_exams = $stmt->get_result();
                                
                                while ($exam = $recent_exams->fetch_assoc()) {
                                    echo "<div class='list-group-item'>";
                                    echo "<strong>" . htmlspecialchars($exam['title']) . "</strong>";
                                    echo "<br><small class='text-muted'>";
                                    echo "Duration: " . $exam['duration'] . " min | ";
                                    echo "Questions: " . $exam['question_count'] . " | ";
                                    echo "Results: " . $exam['result_count'];
                                    echo "</small>";
                                    echo "<br><small class='text-muted'>Created: " . date('M j, Y', strtotime($exam['created_at'])) . "</small>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Results -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Student Results</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Exam</th>
                                                <th>Score</th>
                                                <th>Correct</th>
                                                <th>Wrong</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $conn->prepare("
                                                SELECT r.*, u.name as student_name, e.title as exam_title
                                                FROM results r
                                                JOIN users u ON r.user_id = u.id
                                                JOIN exams e ON r.exam_id = e.id
                                                WHERE e.created_by = ?
                                                ORDER BY r.date DESC
                                                LIMIT 10
                                            ");
                                            $stmt->bind_param("i", $teacher_id);
                                            $stmt->execute();
                                            $recent_results = $stmt->get_result();
                                            
                                            while ($result = $recent_results->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($result['student_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($result['exam_title']) . "</td>";
                                                echo "<td><span class='badge bg-primary'>" . $result['score'] . "</span></td>";
                                                echo "<td>" . $result['correct_answers'] . "</td>";
                                                echo "<td>" . $result['wrong_answers'] . "</td>";
                                                echo "<td>" . date('M j, Y g:i A', strtotime($result['date'])) . "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
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