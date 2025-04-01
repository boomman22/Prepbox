<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'user_auth');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', '');     // Change to your MySQL password

// SMTP configuration for password reset emails
define('SMTP_HOST', 'smtp.example.com'); // Your SMTP server
define('SMTP_USER', 'your@email.com');   // SMTP username
define('SMTP_PASS', 'yourpassword');     // SMTP password
define('SMTP_PORT', 587);                // Typically 587 for TLS
define('SMTP_FROM', 'no-reply@yourdomain.com');
define('SMTP_FROM_NAME', 'Your Site Name');

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to redirect with messages
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION[$type] = $message;
    }
    header("Location: $url");
    exit();
}
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'documents.hemishvora@gmail.com'); // Your full Gmail address
define('SMTP_PASS', 'qjth pdgp nzqo khfd'); // The generated app password
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_FROM', 'no-reply@yourdomain.com');
define('SMTP_FROM_NAME', 'PrepBOX');
// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enable if using HTTPS
ini_set('session.use_strict_mode', 1);
session_set_cookie_params([
    'lifetime' => 86400, // 1 day
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,    // Enable if using HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
?>