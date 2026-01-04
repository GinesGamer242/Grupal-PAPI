<?php

require __DIR__ . '/../config/conn.php';

// Retrieve token from URL
$token = $_GET['token'] ?? '';

if (!$token) die('Token inválido');

// Validate reset token
$stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ?');
$stmt->execute([$token]);
$row = $stmt->fetch();

// Check token expiration
if (!$row || strtotime($row['expires']) < time()) die('Invalid token.');

$err = $ok = '';

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $p1 = $_POST['password'] ?? '';
    $p2 = $_POST['confirm'] ?? '';

    if ($p1 !== $p2)
    {
        $err = "Passwords don't match.";
    }
    else
    {
        // Update password and remove reset token
        $hash = password_hash($p1, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([$hash, $row['user_id']]);

        $pdo->prepare('DELETE FROM password_resets WHERE id = ?')
            ->execute([$row['id']]);

        $ok = 'Password updated.';
    }
}

require __DIR__ . '/../includes/header.php';

?>

<h2>Reset password</h2>
<?php if($err) echo '<p style="color:red;">'.htmlspecialchars($err).'</p>'; ?>
<?php if($ok) echo '<p style="color:green;">'.htmlspecialchars($ok).'</p>'; ?>

<form method="post">
    <label>New password: <input type="password" name="password" required></label><br>
    <label>Confirm: <input type="password" name="confirm" required></label><br>
    <button>Reset</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>