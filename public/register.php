<?php

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch())
    {
        $msg = "Email already registered";
    }
    else
    {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $pdo->prepare("
            INSERT INTO users (email, password_hash, is_active)
            VALUES (?, ?, 0)
        ")->execute([$email, $hash]);

        $uid = $pdo->lastInsertId();

        $pdo->prepare("
            INSERT INTO activation_tokens (user_id, token, expires)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY))
        ")->execute([$uid, $token]);

        $msg = "Account created. Activation required.";
        // aquí iría el mail real
    }
}

?>

<h2>Register</h2>
<p><?= htmlspecialchars($msg) ?></p>

<form method="post">
    <label>Email <input name="email" required></label><br>
    <label>Password <input type="password" name="password" required></label><br>
    <button>Create account</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>