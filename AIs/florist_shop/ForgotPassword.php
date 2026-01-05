<?php
session_start();
require_once "Connection.php";

$message = "";

if (!isset($_GET['token'])) {
    die("Token not provided.");
}

$token = $_GET['token'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expiration > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Invalid or expired token.");
}

if (isset($_POST['reset_password'])) {

    $newPassword = $_POST['new_password'];

    
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_expiration = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$hashed, $user['id']]);

        $message = "Password successfully reset. You can now log in.";
    
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>

<h2>Reset Password</h2>

<?php if ($message): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<?php if (!$message): ?>
<form method="POST">
    New password: <br>
    <input type="password" name="new_password" required><br><br>

    <button type="submit" name="reset_password">Save new password</button>
</form>
<?php endif; ?>

<br>
<a href="index.php">Return to login</a>

</body>
</html>
