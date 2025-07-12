<?php
require_once '../includes/functions.php';
check_role('teacher');

$teacher_id = $_SESSION['user_id'];
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($student_id <= 0) {
    echo '<div class="alert alert-danger">Invalid student ID.</div>';
    exit();
}

// Get student details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found.</div>';
    exit();
}

// Get student's exam results for this teacher's exams
$stmt = $conn->prepare("
    SELECT r.*, e.title as exam_title, e.total_marks, rnk.rank_position
    FROM results r
    JOIN exams e ON r.exam_id = e.id
    LEFT JOIN ranking rnk ON r.user_id = rnk.user_id AND r.exam_id = rnk.exam_id
    WHERE r.user_id = ? AND e.created_by = ?
    ORDER BY r.date DESC
");
$stmt->bind_param("ii", $student_id, $teacher_id);
$stmt->execute();
$results = $stmt->get_result();
?>

<div class="row">
    <div class="col-md-6">
        <h6>Student Information</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Name:</strong></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
            </tr>
            <tr>
                <td><strong>Gender:</strong></td>
                <td><?php echo ucfirst($student['gender']); ?></td>
            </tr>
            <tr>
                <td><strong>Mobile:</strong></td>
                <td><?php echo htmlspecialchars($student['mobile']); ?></td>
            </tr>
            <tr>
                <td><strong>College:</strong></td>
                <td><?php echo htmlspecialchars($student['college']); ?></td>
            </tr>
            <tr>
                <td><strong>Registered:</strong></td>
                <td><?php echo date('M j, Y', strtotime($student['created_at'])); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6>Performance Summary</h6>
        <?php
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_exams,
                AVG(r.score) as avg_score,
                MAX(r.score) as best_score,
                MIN(r.score) as worst_score,
                SUM(r.correct_answers) as total_correct,
                SUM(r.wrong_answers) as total_wrong
            FROM results r
            JOIN exams e ON r.exam_id = e.id
            WHERE r.user_id = ? AND e.created_by = ?
        ");
        $stmt->bind_param("ii", $student_id, $teacher_id);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        ?>
        <div class="row text-center">
            <div class="col-6 mb-2">
                <div class="card">
                    <div class="card-body p-2">
                        <h6 class="mb-0"><?php echo $stats['total_exams']; ?></h6>
                        <small class="text-muted">Exams Taken</small>
                    </div>
                </div>
            </div>
            <div class="col-6 mb-2">
                <div class="card">
                    <div class="card-body p-2">
                        <h6 class="mb-0"><?php echo number_format($stats['avg_score'], 1); ?></h6>
                        <small class="text-muted">Avg Score</small>
                    </div>
                </div>
            </div>
            <div class="col-6 mb-2">
                <div class="card">
                    <div class="card-body p-2">
                        <h6 class="mb-0"><?php echo number_format($stats['best_score'], 1); ?></h6>
                        <small class="text-muted">Best Score</small>
                    </div>
                </div>
            </div>
            <div class="col-6 mb-2">
                <div class="card">
                    <div class="card-body p-2">
                        <h6 class="mb-0"><?php echo number_format($stats['worst_score'], 1); ?></h6>
                        <small class="text-muted">Worst Score</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <h6>Accuracy</h6>
            <?php
            $total_questions = $stats['total_correct'] + $stats['total_wrong'];
            $accuracy = $total_questions > 0 ? ($stats['total_correct'] / $total_questions) * 100 : 0;
            ?>
            <div class="progress mb-2">
                <div class="progress-bar bg-success" style="width: <?php echo $accuracy; ?>%">
                    <?php echo number_format($accuracy, 1); ?>%
                </div>
            </div>
            <small class="text-muted">
                <?php echo $stats['total_correct']; ?> correct / <?php echo $total_questions; ?> total questions
            </small>
        </div>
    </div>
</div>

<?php if ($results->num_rows > 0): ?>
    <div class="mt-4">
        <h6>Exam History</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Exam</th>
                        <th>Score</th>
                        <th>Rank</th>
                        <th>Correct</th>
                        <th>Wrong</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($result = $results->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['exam_title']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $result['score'] >= 80 ? 'success' : ($result['score'] >= 60 ? 'warning' : 'danger'); ?>">
                                    <?php echo $result['score']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($result['rank_position']): ?>
                                    <span class="badge bg-info"><?php echo $result['rank_position']; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-success"><?php echo $result['correct_answers']; ?></span></td>
                            <td><span class="text-danger"><?php echo $result['wrong_answers']; ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($result['date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="mt-4">
        <div class="alert alert-info">
            <h6>No exam history!</h6>
            <p>This student hasn't taken any of your exams yet.</p>
        </div>
    </div>
<?php endif; ?> 