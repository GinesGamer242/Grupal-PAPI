<?php

require __DIR__ . '/../config/conn.php';

// Retrieve activation token from URL-
$token = $_GET['token'] ?? '';

// Validate token existence
if (!$token) die('Invalid token');

// Check activation token in database
$stmt = $pdo->prepare('SELECT * FROM activation_tokens WHERE token = ?');
$stmt->execute([$token]);
$row = $stmt->fetch();

// If token is invalid or expired, stop execution
if (!$row) die('Invalid or expired token');

// Activate user account and remove token
$pdo->prepare('UPDATE users SET is_active = 1 WHERE id = ?')->execute([$row['user_id']]);
$pdo->prepare('DELETE FROM activation_tokens WHERE id = ?')->execute([$row['id']]);

require __DIR__ . '/../includes/header.php';

?>

<h2>Account activated</h2>
<p>Your account has been activated. <a href="login.php">Log in</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>