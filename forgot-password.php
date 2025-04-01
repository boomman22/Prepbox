<?php
require 'includes/config.php';
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    
    // Validate email
    if (empty($email)) {
        redirect('login-register.html', 'Email is required', 'error');
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 0) {
        // Don't reveal if email exists for security
        redirect('login-register.html', 'If your email exists, you will receive a reset link', 'success');
    }
    
    // Generate token and expiry
    $token = generateToken();
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration
    
    // Store in database
    $stmt = $pdo->prepare("REPLACE INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires]);
    
    // Create reset link
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=$token";
    
    // Email content
    $subject = "Password Reset Request";
    $body = "
        <h2>Password Reset</h2>
        <p>You requested a password reset. Click the link below to reset your password:</p>
        <p><a href='$resetLink'>Reset Password</a></p>
        <p>This link will expire in 1 hour.</p>
        <p>If you didn't request this, please ignore this email.</p>
    ";
    
    // Send email
    if (sendEmail($email, $subject, $body)) {
        redirect('login-register.html', 'Password reset link sent to your email', 'success');
    } else {
        redirect('login-register.html', 'Failed to send reset email. Please try again.', 'error');
    }
}

// If not POST request, show form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- Include your CSS files here -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-body">
                        <h2 class="card-title text-center">Forgot Password</h2>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="login-register.html">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>