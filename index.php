<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Examination System</title>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="about.php">
                        <i class="fas fa-info-circle me-1"></i>About Us
                    </a>
                    <a class="nav-link" href="contact.php">
                        <i class="fas fa-envelope me-1"></i>Contact Us
                    </a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto text-center fade-in">
                <h1 class="display-4 mb-4 text-white fw-bold">
                    <i class="fas fa-laptop-code me-3"></i>
                    Welcome to Online Examination System
                </h1>
                <p class="lead mb-5 text-white-50 fs-5">
                    A comprehensive platform for conducting online examinations with real-time evaluation, 
                    instant results, and advanced analytics.
                </p>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="alert alert-success bounce-in">
                        <i class="fas fa-user-check me-2"></i>
                        Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>!
                        <a href="<?php echo $_SESSION['role']; ?>/dashboard.php" class="btn btn-light ms-3">
                            <i class="fas fa-tachometer-alt me-1"></i>Go to Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Feature Cards -->
                    <div class="row g-4 mt-4">
                        <div class="col-md-4 slide-in">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-user-graduate fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">For Students</h5>
                                    <p class="card-text text-muted">
                                        Take exams, view results, track progress, and submit feedback with our intuitive interface.
                                    </p>
                                    <a href="register.php" class="btn btn-primary w-100">
                                        <i class="fas fa-user-plus me-1"></i>Register as Student
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 slide-in" style="animation-delay: 0.2s;">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-chalkboard-teacher fa-3x text-success"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">For Teachers</h5>
                                    <p class="card-text text-muted">
                                        Create exams, add questions, monitor results, and analyze student performance.
                                    </p>
                                    <a href="login.php" class="btn btn-success w-100">
                                        <i class="fas fa-sign-in-alt me-1"></i>Teacher Login
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 slide-in" style="animation-delay: 0.4s;">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-user-shield fa-3x text-warning"></i>
                                    </div>
                                    <h5 class="card-title fw-bold">For Administrators</h5>
                                    <p class="card-text text-muted">
                                        Manage users, view reports, monitor system performance, and handle feedback.
                                    </p>
                                    <a href="login.php" class="btn btn-warning w-100">
                                        <i class="fas fa-sign-in-alt me-1"></i>Admin Login
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features Section -->
                    <div class="row mt-5 pt-5">
                        <div class="col-12 text-center mb-5">
                            <h2 class="text-white fw-bold mb-3">Why Choose Our Platform?</h2>
                            <p class="text-white-50">Discover the features that make our examination system stand out</p>
                        </div>
                        
                        <div class="col-md-3 text-center fade-in">
                            <div class="mb-3">
                                <i class="fas fa-clock fa-2x text-info"></i>
                            </div>
                            <h6 class="text-white fw-bold">Real-time Timer</h6>
                            <p class="text-white-50 small">Accurate countdown with auto-submission</p>
                        </div>
                        
                        <div class="col-md-3 text-center fade-in" style="animation-delay: 0.1s;">
                            <div class="mb-3">
                                <i class="fas fa-chart-line fa-2x text-success"></i>
                            </div>
                            <h6 class="text-white fw-bold">Instant Results</h6>
                            <p class="text-white-50 small">Get scores and rankings immediately</p>
                        </div>
                        
                        <div class="col-md-3 text-center fade-in" style="animation-delay: 0.2s;">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt fa-2x text-warning"></i>
                            </div>
                            <h6 class="text-white fw-bold">Secure Platform</h6>
                            <p class="text-white-50 small">Advanced security and anti-cheating measures</p>
                        </div>
                        
                        <div class="col-md-3 text-center fade-in" style="animation-delay: 0.3s;">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                            </div>
                            <h6 class="text-white fw-bold">Mobile Friendly</h6>
                            <p class="text-white-50 small">Responsive design for all devices</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 pt-5 pb-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-white-50 mb-2">
                        <i class="fas fa-heart text-danger"></i> 
                        Built with modern technologies for the best user experience
                    </p>
                    <div class="text-white-50">
                        <p class="mb-1">
                            <strong>Created by:</strong> 
                            <a href="https://github.com/mmrcode" target="_blank" class="text-white text-decoration-none">
                                <i class="fab fa-github me-1"></i>Mohammad Muqsit Raja
                            </a>
                        </p>
                        <small class="text-white-50">
                            <i class="fas fa-code me-1"></i>Full Stack Developer
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling and enhance animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add intersection observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all cards and sections
            document.querySelectorAll('.card, .fade-in, .slide-in').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html> 