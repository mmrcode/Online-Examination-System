<?php
require_once '../includes/functions.php';
check_role('teacher');

$teacher_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get teacher's exams
$stmt = $conn->prepare("SELECT * FROM exams WHERE created_by = ? ORDER BY title");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$exams = $stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $exam_id = (int)$_POST['exam_id'];
        $question_text = sanitize_input($_POST['question_text']);
        $option_a = sanitize_input($_POST['option_a']);
        $option_b = sanitize_input($_POST['option_b']);
        $option_c = sanitize_input($_POST['option_c']);
        $option_d = sanitize_input($_POST['option_d']);
        $correct_option = sanitize_input($_POST['correct_option']);
        
        // Validate exam ownership
        $stmt = $conn->prepare("SELECT id FROM exams WHERE id = ? AND created_by = ?");
        $stmt->bind_param("ii", $exam_id, $teacher_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $error = 'Invalid exam selected';
        } elseif (empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
            $error = 'All fields are required';
        } elseif (!in_array($correct_option, ['a', 'b', 'c', 'd'])) {
            $error = 'Please select a valid correct option';
        } else {
            $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $exam_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option);
            
            if ($stmt->execute()) {
                $success = 'Question added successfully!';
            } else {
                $error = 'Failed to add question. Please try again.';
            }
        }
    }
}

// Get selected exam questions (if exam is selected)
$selected_exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$questions = [];
if ($selected_exam_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY id");
    $stmt->bind_param("i", $selected_exam_id);
    $stmt->execute();
    $questions = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions - Teacher Dashboard</title>
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
                            <a class="nav-link active" href="add_questions.php">
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
                    <h1 class="h2">Add Questions</h1>
                    <a href="create_exam.php" class="btn btn-outline-primary">Create New Exam</a>
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
                                <h5>Add New Question</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    
                                    <div class="mb-3">
                                        <label for="exam_id" class="form-label">Select Exam</label>
                                        <select class="form-select" id="exam_id" name="exam_id" required>
                                            <option value="">Choose an exam...</option>
                                            <?php while ($exam = $exams->fetch_assoc()): ?>
                                                <option value="<?php echo $exam['id']; ?>" <?php echo $selected_exam_id == $exam['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($exam['title']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="question_text" class="form-label">Question</label>
                                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="option_a" class="form-label">Option A</label>
                                        <input type="text" class="form-control" id="option_a" name="option_a" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="option_b" class="form-label">Option B</label>
                                        <input type="text" class="form-control" id="option_b" name="option_b" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="option_c" class="form-label">Option C</label>
                                        <input type="text" class="form-control" id="option_c" name="option_c" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="option_d" class="form-label">Option D</label>
                                        <input type="text" class="form-control" id="option_d" name="option_d" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="correct_option" class="form-label">Correct Option</label>
                                        <select class="form-select" id="correct_option" name="correct_option" required>
                                            <option value="">Select correct option...</option>
                                            <option value="a">A</option>
                                            <option value="b">B</option>
                                            <option value="c">C</option>
                                            <option value="d">D</option>
                                        </select>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Add Question</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Exam Questions</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($selected_exam_id > 0 && $questions->num_rows > 0): ?>
                                    <div class="list-group">
                                        <?php while ($question = $questions->fetch_assoc()): ?>
                                            <div class="list-group-item">
                                                <h6>Question <?php echo $question['id']; ?></h6>
                                                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <small class="text-muted">
                                                            A: <?php echo htmlspecialchars($question['option_a']); ?><br>
                                                            B: <?php echo htmlspecialchars($question['option_b']); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted">
                                                            C: <?php echo htmlspecialchars($question['option_c']); ?><br>
                                                            D: <?php echo htmlspecialchars($question['option_d']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="badge bg-success">Correct: <?php echo strtoupper($question['correct_option']); ?></span>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php elseif ($selected_exam_id > 0): ?>
                                    <div class="alert alert-info">
                                        No questions added to this exam yet. Add your first question using the form.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        Select an exam from the dropdown to view its questions.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit form when exam is selected to show questions
        document.getElementById('exam_id').addEventListener('change', function() {
            if (this.value) {
                window.location.href = 'add_questions.php?exam_id=' + this.value;
            }
        });
    </script>
</body>
</html> 