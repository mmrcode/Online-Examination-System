<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Online Examination System</title>
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
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                    <a class="nav-link active" href="about.php">
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
            <div class="col-12 text-center fade-in">
                <h1 class="display-4 mb-4 text-white fw-bold">
                    <i class="fas fa-info-circle me-3"></i>
                    About Our Platform
                </h1>
                <p class="lead mb-5 text-white-50 fs-5">
                    Discover the story behind our innovative online examination system
                </p>
            </div>
        </div>
    </div>

    <!-- About Content -->
    <div class="container mt-5">
        <div class="row g-5">
            <!-- Mission Section -->
            <div class="col-lg-6 slide-in">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-bullseye fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title fw-bold text-center mb-4">Our Mission</h3>
                        <p class="card-text">
                            We are dedicated to revolutionizing the way educational institutions conduct examinations. 
                            Our platform provides a secure, efficient, and user-friendly solution for online assessments, 
                            making education more accessible and effective in the digital age.
                        </p>
                        <p class="card-text">
                            Our mission is to bridge the gap between traditional examination methods and modern technology, 
                            ensuring that students, teachers, and administrators have access to the best tools for 
                            educational assessment and evaluation.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Vision Section -->
            <div class="col-lg-6 slide-in" style="animation-delay: 0.2s;">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-eye fa-3x text-success"></i>
                        </div>
                        <h3 class="card-title fw-bold text-center mb-4">Our Vision</h3>
                        <p class="card-text">
                            To become the leading platform for online examinations worldwide, setting new standards 
                            for digital assessment technology. We envision a future where every educational institution 
                            can seamlessly transition to online examinations without compromising on quality or security.
                        </p>
                        <p class="card-text">
                            We strive to create an ecosystem that supports continuous learning and improvement, 
                            providing insights and analytics that help educators make data-driven decisions 
                            to enhance student performance and learning outcomes.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-star me-2"></i>
                            Why Choose Our Platform?
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4 text-center fade-in">
                                <div class="mb-3">
                                    <i class="fas fa-shield-alt fa-2x text-warning"></i>
                                </div>
                                <h5 class="fw-bold">Secure & Reliable</h5>
                                <p class="text-muted">Advanced security measures ensure the integrity of every examination.</p>
                            </div>
                            <div class="col-md-4 text-center fade-in" style="animation-delay: 0.1s;">
                                <div class="mb-3">
                                    <i class="fas fa-clock fa-2x text-info"></i>
                                </div>
                                <h5 class="fw-bold">Real-time Processing</h5>
                                <p class="text-muted">Instant results and immediate feedback for better learning outcomes.</p>
                            </div>
                            <div class="col-md-4 text-center fade-in" style="animation-delay: 0.2s;">
                                <div class="mb-3">
                                    <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                                </div>
                                <h5 class="fw-bold">Mobile Friendly</h5>
                                <p class="text-muted">Access exams from any device, anywhere, anytime.</p>
                            </div>
                            <div class="col-md-4 text-center fade-in" style="animation-delay: 0.3s;">
                                <div class="mb-3">
                                    <i class="fas fa-chart-line fa-2x text-success"></i>
                                </div>
                                <h5 class="fw-bold">Analytics & Insights</h5>
                                <p class="text-muted">Comprehensive reports and performance analytics.</p>
                            </div>
                            <div class="col-md-4 text-center fade-in" style="animation-delay: 0.4s;">
                                <div class="mb-3">
                                    <i class="fas fa-users fa-2x text-secondary"></i>
                                </div>
                                <h5 class="fw-bold">Multi-role Support</h5>
                                <p class="text-muted">Designed for students, teachers, and administrators.</p>
                            </div>
                            <div class="col-md-4 text-center fade-in" style="animation-delay: 0.5s;">
                                <div class="mb-3">
                                    <i class="fas fa-cog fa-2x text-danger"></i>
                                </div>
                                <h5 class="fw-bold">Easy Management</h5>
                                <p class="text-muted">Intuitive interface for seamless exam management.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Section -->
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-users me-2"></i>
                            Our Technology Stack
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-3 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fab fa-php fa-2x text-primary mb-2"></i>
                                    <h6 class="fw-bold">PHP</h6>
                                    <small class="text-muted">Backend Development</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-database fa-2x text-success mb-2"></i>
                                    <h6 class="fw-bold">MySQL</h6>
                                    <small class="text-muted">Database Management</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fab fa-js-square fa-2x text-warning mb-2"></i>
                                    <h6 class="fw-bold">JavaScript</h6>
                                    <small class="text-muted">Frontend Interactivity</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fab fa-bootstrap fa-2x text-info mb-2"></i>
                                    <h6 class="fw-bold">Bootstrap</h6>
                                    <small class="text-muted">Responsive Design</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Developer Section -->
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-code me-2"></i>
                            Developer Information
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="p-4">
                                    <div class="mb-4">
                                        <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                                        <h4 class="fw-bold">Mohammad Muqsit Raja</h4>
                                        <p class="text-muted mb-3">Full Stack Developer</p>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <a href="https://github.com/mmrcode" target="_blank" class="btn btn-outline-dark btn-lg w-100">
                                                <i class="fab fa-github me-2"></i>GitHub Profile
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="mailto:contact@mmrcode.com" class="btn btn-outline-primary btn-lg w-100">
                                                <i class="fas fa-envelope me-2"></i>Contact Developer
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <p class="text-muted">
                                            <i class="fas fa-star text-warning me-1"></i>
                                            This online examination system was developed with modern web technologies 
                                            and best practices to provide an excellent user experience.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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