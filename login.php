<?php
session_start();
require_once 'db/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password';
        } else {
            // Check user credentials
            $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirect based on role
                    switch ($user['role']) {
                        case 'admin':
                            header("Location: admin/dashboard.php");
                            break;
                        case 'teacher':
                            header("Location: teacher/dashboard.php");
                            break;
                        case 'student':
                            header("Location: student/dashboard.php");
                            break;
                        default:
                            header("Location: index.php");
                    }
                    exit();
                } else {
                    $error = 'Invalid password';
                }
            } else {
                $error = 'User not found';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Examination System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Modern Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>
                Online Exam System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="about.php">
                    <i class="fas fa-info-circle me-1"></i>About Us
                </a>
                <a class="nav-link" href="contact.php">
                    <i class="fas fa-envelope me-1"></i>Contact Us
                </a>
                <a class="nav-link active" href="login.php">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <a class="nav-link" href="register.php">
                    <i class="fas fa-user-plus me-1"></i>Register
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card fade-in">
                    <div class="card-header text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-3x text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-0">Welcome Back</h4>
                        <p class="text-muted mb-0">Sign in to your account</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger bounce-in">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       placeholder="Enter your email" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mb-4">
                            <p class="mb-0">Don't have an account? 
                                <a href="register.php" class="text-primary fw-semibold">Register here</a>
                            </p>
                        </div>
                        
                        <!-- Demo Accounts Section -->
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2 text-info"></i>Demo Accounts
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-white rounded">
                                            <i class="fas fa-user-shield fa-2x text-warning mb-2"></i>
                                            <h6 class="fw-bold mb-1">Admin</h6>
                                            <small class="text-muted d-block">admin@example.com</small>
                                            <small class="text-muted d-block">admin123</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-white rounded">
                                            <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2"></i>
                                            <h6 class="fw-bold mb-1">Teacher</h6>
                                            <small class="text-muted d-block">teacher@example.com</small>
                                            <small class="text-muted d-block">teacher123</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-white rounded">
                                            <i class="fas fa-user-graduate fa-2x text-primary mb-2"></i>
                                            <h6 class="fw-bold mb-1">Student</h6>
                                            <small class="text-muted d-block">student@example.com</small>
                                            <small class="text-muted d-block">student123</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Add animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html> 