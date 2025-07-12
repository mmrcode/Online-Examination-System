<?php
require_once '../includes/functions.php';
check_role('teacher');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $title = sanitize_input($_POST['title']);
        $duration = (int)$_POST['duration'];
        $total_marks = (int)$_POST['total_marks'];
        $positive_marks = (float)$_POST['positive_marks'];
        $negative_marks = (float)$_POST['negative_marks'];
        
        if (empty($title) || $duration <= 0 || $total_marks <= 0) {
            $error = 'Please fill all required fields with valid values';
        } elseif ($positive_marks < 0 || $negative_marks < 0) {
            $error = 'Marks cannot be negative';
        } else {
            $teacher_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("INSERT INTO exams (title, duration, total_marks, positive_marks, negative_marks, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiddi", $title, $duration, $total_marks, $positive_marks, $negative_marks, $teacher_id);
            
            if ($stmt->execute()) {
                $exam_id = $conn->insert_id;
                $success = "Exam created successfully! Exam ID: $exam_id. You can now add questions to this exam.";
            } else {
                $error = 'Failed to create exam. Please try again.';
            }
        }
    }
}

// Get teacher's exams
$teacher_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM exams WHERE created_by = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$exams = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam - Teacher Dashboard</title>
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
                            <a class="nav-link active" href="create_exam.php">
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
                    <h1 class="h2">Create Exam</h1>
                    <a href="add_questions.php" class="btn btn-success">Add Questions</a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Create New Exam</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Exam Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Duration (minutes)</label>
                                        <input type="number" class="form-control" id="duration" name="duration" min="1" max="480" value="60" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="total_marks" class="form-label">Total Marks</label>
                                        <input type="number" class="form-control" id="total_marks" name="total_marks" min="1" value="100" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="positive_marks" class="form-label">Positive Marks per Question</label>
                                                <input type="number" class="form-control" id="positive_marks" name="positive_marks" min="0" step="0.01" value="1.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="negative_marks" class="form-label">Negative Marks per Question</label>
                                                <input type="number" class="form-control" id="negative_marks" name="negative_marks" min="0" step="0.01" value="0.25" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Create Exam</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Your Exams</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Duration</th>
                                                <th>Questions</th>
                                                <th>Results</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($exam = $exams->fetch_assoc()): ?>
                                                <?php
                                                // Get question count for this exam
                                                $stmt2 = $conn->prepare("SELECT COUNT(*) as question_count FROM questions WHERE exam_id = ?");
                                                $stmt2->bind_param("i", $exam['id']);
                                                $stmt2->execute();
                                                $question_count = $stmt2->get_result()->fetch_assoc()['question_count'];
                                                
                                                // Get result count for this exam
                                                $stmt3 = $conn->prepare("SELECT COUNT(*) as result_count FROM results WHERE exam_id = ?");
                                                $stmt3->bind_param("i", $exam['id']);
                                                $stmt3->execute();
                                                $result_count = $stmt3->get_result()->fetch_assoc()['result_count'];
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($exam['title']); ?></td>
                                                    <td><?php echo $exam['duration']; ?> min</td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $question_count > 0 ? 'success' : 'warning'; ?>">
                                                            <?php echo $question_count; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $result_count; ?></span>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($exam['created_at'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
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