<?php
require 'includes/config.php';
require 'includes/functions.php';

// Verify token
$token = $_GET['token'] ?? '';
$email = '';

if ($token) {
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $result = $stmt->fetch();
    
    if (!$result) {
        redirect('login-register.html', 'Invalid or expired reset link', 'error');
    }
    
    $email = $result['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate
    if (empty($password) || empty($confirm_password)) {
        redirect("reset-password.php?token=$token", 'Both password fields are required', 'error');
    }
    
    if ($password !== $confirm_password) {
        redirect("reset-password.php?token=$token", 'Passwords do not match', 'error');
    }
    
    if (strlen($password) < 8) {
        redirect("reset-password.php?token=$token", 'Password must be at least 8 characters', 'error');
    }
    
    // Verify token again
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $result = $stmt->fetch();
    
    if (!$result) {
        redirect('login-register.html', 'Invalid or expired reset link', 'error');
    }
    
    $email = $result['email'];
    
    // Update password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashedPassword, $email]);
    
    // Delete the reset entry
    $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
    
    redirect('login-register.html', 'Password updated successfully. You can now login.', 'success');
}

// Show reset form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Include your CSS files here -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-body">
                        <h2 class="card-title text-center">Reset Password</h2>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>