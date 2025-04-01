<?php
require 'includes/config.php';
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Registration
    if (isset($_POST['user-name']) && isset($_POST['user-password']) && isset($_POST['user-email'])) {
        $username = sanitize($_POST['user-name']);
        $email = sanitize($_POST['user-email']);
        $password = $_POST['user-password'];
        
        // Validate inputs
        $errors = [];
        
        if (empty($username)) {
            $errors[] = "Username is required";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $errors[] = "Username must be 3-20 characters (letters, numbers, underscores)";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        
        if (!empty($errors)) {
            redirect('login-register.html', implode('<br>', $errors), 'error');
        }
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            redirect('login-register.html', 'Username or email already exists', 'error');
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);
        
        redirect('login-register.html', 'Registration successful! Please login.', 'success');
    }
    
    // Handle Login
    elseif (isset($_POST['user-name']) && isset($_POST['user-password'])) {
        $username = sanitize($_POST['user-name']);
        $password = $_POST['user-password'];
        
        // Find user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ];
            
            // Set remember me cookie if checked
            if (isset($_POST['remember'])) {
                $token = generateToken();
                $expiry = time() + 60 * 60 * 24 * 30; // 30 days
                
                setcookie('remember_token', $token, $expiry, '/');
                
                // Store in database
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);
            }
            
            redirect('index.html', 'Login successful!');
        } else {
            redirect('login-register.html', 'Invalid username or password', 'error');
        }
    }
}

// If not a POST request, redirect
redirect('login-register.html');
?>