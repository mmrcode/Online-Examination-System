<?php
require_once '../includes/functions.php';
check_role('student');

$student_id = $_SESSION['user_id'];
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

if ($exam_id <= 0) {
    header("Location: take_exam.php");
    exit();
}

// Check if student has already taken this exam
$stmt = $conn->prepare("SELECT id FROM results WHERE user_id = ? AND exam_id = ?");
$stmt->bind_param("ii", $student_id, $exam_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: take_exam.php");
    exit();
}

// Get exam details
$exam = get_exam_data($exam_id);
if (!$exam) {
    header("Location: take_exam.php");
    exit();
}

// Get questions for this exam
$questions = get_exam_questions($exam_id);
if (empty($questions)) {
    header("Location: take_exam.php");
    exit();
}

// Handle exam submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $user_answers = $_POST['answers'] ?? [];
        
        // Calculate score
        $score_data = calculate_score($exam_id, $user_answers);
        
        // Save result
        if (save_exam_result($student_id, $exam_id, $score_data)) {
            header("Location: result.php?exam_id=$exam_id");
            exit();
        } else {
            $error = 'Failed to submit exam. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exam['title']); ?> - Online Examination System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Timer -->
    <div class="exam-timer" id="timer">
        Time Remaining: <span id="time-display"><?php echo format_time($exam['duration']); ?></span>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo htmlspecialchars($exam['title']); ?></h1>
                    <div>
                        <span class="badge bg-primary"><?php echo count($questions); ?> Questions</span>
                        <span class="badge bg-success"><?php echo $exam['duration']; ?> Minutes</span>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="" id="exam-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-container">
                            <h5 class="question-text">
                                Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question_text']); ?>
                            </h5>
                            
                            <div class="option-container">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="a" id="q<?php echo $question['id']; ?>_a">
                                <label for="q<?php echo $question['id']; ?>_a">
                                    <strong>A:</strong> <?php echo htmlspecialchars($question['option_a']); ?>
                                </label>
                            </div>
                            
                            <div class="option-container">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="b" id="q<?php echo $question['id']; ?>_b">
                                <label for="q<?php echo $question['id']; ?>_b">
                                    <strong>B:</strong> <?php echo htmlspecialchars($question['option_b']); ?>
                                </label>
                            </div>
                            
                            <div class="option-container">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="c" id="q<?php echo $question['id']; ?>_c">
                                <label for="q<?php echo $question['id']; ?>_c">
                                    <strong>C:</strong> <?php echo htmlspecialchars($question['option_c']); ?>
                                </label>
                            </div>
                            
                            <div class="option-container">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="d" id="q<?php echo $question['id']; ?>_d">
                                <label for="q<?php echo $question['id']; ?>_d">
                                    <strong>D:</strong> <?php echo htmlspecialchars($question['option_d']); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-4 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg" onclick="return confirmSubmit()">
                            Submit Exam
                        </button>
                        <a href="take_exam.php" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Timer functionality
        let timeLeft = <?php echo $exam['duration'] * 60; ?>; // Convert to seconds
        const timerDisplay = document.getElementById('time-display');
        const examForm = document.getElementById('exam-form');
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                alert('Time is up! Your exam will be submitted automatically.');
                examForm.submit();
                return;
            }
            
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }
        
        function confirmSubmit() {
            return confirm('Are you sure you want to submit your exam? You cannot change your answers after submission.');
        }
        
        // Start timer
        updateTimer();
        
        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // Warn before leaving page
        window.onbeforeunload = function() {
            return "Are you sure you want to leave? Your progress will be lost.";
        };
        
        // Remove warning when submitting form
        examForm.onsubmit = function() {
            window.onbeforeunload = null;
        };
    </script>
</body>
</html> 