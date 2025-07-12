<?php
require_once '../includes/functions.php';
check_role('student');

$student_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $message = sanitize_input($_POST['message']);
        
        if (empty($message)) {
            $error = 'Please enter your feedback message';
        } elseif (strlen($message) < 10) {
            $error = 'Feedback message must be at least 10 characters long';
        } else {
            $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
            $stmt->bind_param("is", $student_id, $message);
            
            if ($stmt->execute()) {
                $success = 'Thank you for your feedback! We appreciate your input.';
                $message = ''; // Clear the form
            } else {
                $error = 'Failed to submit feedback. Please try again.';
            }
        }
    }
}

// Get student's previous feedback
$stmt = $conn->prepare("SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$previous_feedback = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback - Student Dashboard</title>
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
                            <a class="nav-link active" href="feedback.php">
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
                    <h1 class="h2">Submit Feedback</h1>
                    <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
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
                                <h5>Submit New Feedback</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Your Feedback</label>
                                        <textarea class="form-control" id="message" name="message" rows="6" placeholder="Please share your thoughts, suggestions, or report any issues you've encountered while using the examination system..." required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                        <div class="form-text">
                                            Your feedback helps us improve the system. Please be specific and constructive.
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Feedback Guidelines -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6>Feedback Guidelines</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2">✅ <strong>Be specific:</strong> Mention specific features or issues</li>
                                    <li class="mb-2">✅ <strong>Be constructive:</strong> Suggest improvements when possible</li>
                                    <li class="mb-2">✅ <strong>Be respectful:</strong> Use appropriate language</li>
                                    <li class="mb-2">✅ <strong>Include details:</strong> Describe what happened and when</li>
                                    <li class="mb-2">❌ <strong>Avoid:</strong> Personal attacks or inappropriate content</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Your Previous Feedback (<?php echo $previous_feedback->num_rows; ?> total)</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($previous_feedback->num_rows > 0): ?>
                                    <div class="list-group">
                                        <?php while ($feedback = $previous_feedback->fetch_assoc()): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
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
                                        <h6>No previous feedback!</h6>
                                        <p>This will be your first feedback submission. We look forward to hearing from you!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Feedback Statistics -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6>Feedback Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4><?php echo $previous_feedback->num_rows; ?></h4>
                                        <small class="text-muted">Total Submissions</small>
                                    </div>
                                    <div class="col-6">
                                        <h4>
                                            <?php
                                            $stmt = $conn->prepare("SELECT COUNT(*) as this_month FROM feedback WHERE user_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
                                            $stmt->bind_param("i", $student_id);
                                            $stmt->execute();
                                            echo $stmt->get_result()->fetch_assoc()['this_month'];
                                            ?>
                                        </h4>
                                        <small class="text-muted">This Month</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Feedback Templates -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6>Quick Feedback Templates</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="insertTemplate('I found the exam interface very user-friendly. The timer feature helped me manage my time effectively.')">
                                        Positive Experience
                                    </button>
                                </div>
                                <div class="mb-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="insertTemplate('I encountered some technical issues during the exam. The page froze once and I had to refresh.')">
                                        Technical Issue
                                    </button>
                                </div>
                                <div class="mb-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="insertTemplate('I would like to suggest adding a feature to review answers before final submission.')">
                                        Feature Request
                                    </button>
                                </div>
                                <div class="mb-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="insertTemplate('The exam questions were well-structured and covered the topics comprehensively.')">
                                        Content Feedback
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function insertTemplate(text) {
            document.getElementById('message').value = text;
            document.getElementById('message').focus();
        }

        // Character counter
        document.getElementById('message').addEventListener('input', function() {
            const maxLength = 1000;
            const currentLength = this.value.length;
            const remaining = maxLength - currentLength;
            
            // Update character count display
            let counter = document.getElementById('char-counter');
            if (!counter) {
                counter = document.createElement('div');
                counter.id = 'char-counter';
                counter.className = 'form-text';
                this.parentNode.appendChild(counter);
            }
            
            counter.textContent = `${currentLength} / ${maxLength} characters`;
            
            if (remaining < 0) {
                counter.className = 'form-text text-danger';
            } else if (remaining < 100) {
                counter.className = 'form-text text-warning';
            } else {
                counter.className = 'form-text';
            }
        });
    </script>
</body>
</html> 