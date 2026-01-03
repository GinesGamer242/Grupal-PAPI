<?php

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $stmt = $pdo->prepare("
        SELECT * FROM users
        WHERE email = ? AND is_active = 1
    ");
    $stmt->execute([$_POST['email']]);
    $u = $stmt->fetch();

    if ($u && password_verify($_POST['password'], $u['password_hash']))
    {
        $_SESSION['user_id'] = $u['id'];
        header("Location: index.php");
        exit;
    }
    else
    {
        $error = "Invalid credentials";
    }
}

?>

<h2>Login</h2>
<p style="color:red"><?= htmlspecialchars($error) ?></p>

<form method="post">
    <label>Email <input name="email" required></label><br>
    <label>Password <input type="password" name="password" required></label><br>
    <button>Login</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>