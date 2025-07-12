<?php
require_once '../includes/functions.php';
check_role('admin');

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $name = sanitize_input($_POST['name']);
                    $email = sanitize_input($_POST['email']);
                    $password = $_POST['password'];
                    $gender = sanitize_input($_POST['gender']);
                    $mobile = sanitize_input($_POST['mobile']);
                    $college = sanitize_input($_POST['college']);
                    
                    if (empty($name) || empty($email) || empty($password) || empty($gender) || empty($mobile) || empty($college)) {
                        $error = 'All fields are required';
                    } elseif (!validate_email($email)) {
                        $error = 'Please enter a valid email address';
                    } elseif (!validate_mobile($mobile)) {
                        $error = 'Please enter a valid 10-digit mobile number';
                    } elseif (strlen($password) < 6) {
                        $error = 'Password must be at least 6 characters long';
                    } else {
                        // Check if email already exists
                        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $error = 'Email already registered';
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, gender, mobile, college) VALUES (?, ?, ?, 'teacher', ?, ?, ?)");
                            $stmt->bind_param("ssssss", $name, $email, $hashed_password, $gender, $mobile, $college);
                            
                            if ($stmt->execute()) {
                                $success = 'Teacher added successfully!';
                            } else {
                                $error = 'Failed to add teacher. Please try again.';
                            }
                        }
                    }
                    break;
                    
                case 'delete':
                    $teacher_id = (int)$_POST['teacher_id'];
                    if ($teacher_id > 0) {
                        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'");
                        $stmt->bind_param("i", $teacher_id);
                        
                        if ($stmt->execute() && $stmt->affected_rows > 0) {
                            $success = 'Teacher deleted successfully!';
                        } else {
                            $error = 'Failed to delete teacher.';
                        }
                    }
                    break;
            }
        }
    }
}

// Get all teachers
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'teacher' ORDER BY name");
$stmt->execute();
$teachers = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - Admin Dashboard</title>
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
                        <h5 class="text-white">Admin Panel</h5>
                        <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_teachers.php">
                                Manage Teachers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_students.php">
                                View Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_results.php">
                                View Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_feedback.php">
                                View Feedback
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
                    <h1 class="h2">Manage Teachers</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                        Add New Teacher
                    </button>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Teachers List -->
                <div class="card">
                    <div class="card-header">
                        <h5>All Teachers</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Gender</th>
                                        <th>Mobile</th>
                                        <th>College</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $teacher['id']; ?></td>
                                        <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                        <td><?php echo ucfirst($teacher['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['mobile']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['college']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($teacher['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="deleteTeacher(<?php echo $teacher['id']; ?>, '<?php echo htmlspecialchars($teacher['name']); ?>')">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Teacher Modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="mobile" name="mobile" maxlength="10" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="college" class="form-label">College/Institution</label>
                            <input type="text" class="form-control" id="college" name="college" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Teacher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTeacherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete teacher: <strong id="teacherName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="teacher_id" id="teacherId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteTeacher(teacherId, teacherName) {
            document.getElementById('teacherId').value = teacherId;
            document.getElementById('teacherName').textContent = teacherName;
            new bootstrap.Modal(document.getElementById('deleteTeacherModal')).show();
        }
    </script>
</body>
</html> 