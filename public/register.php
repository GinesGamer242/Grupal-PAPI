<?php

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../config/session.php';

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

        $pdo->beginTransaction();

        try {
            $pdo->prepare("
                INSERT INTO users (email, password_hash, is_active)
                VALUES (?, ?, 0)
            ")->execute([$email, $hash]);

            $uid = $pdo->lastInsertId();

            $pdo->prepare("
                INSERT INTO activation_tokens (user_id, token, expires)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY))
            ")->execute([$uid, $token]);

            $pdo->commit();

            //$activationLink = "http://localhost/PAPI/GA_GinesLuciaIrene/Grupal-PAPI/public/activate.php?token=$token";
              $activationLink = "http://localhost/PAPI/Grupal-PAPI/public/activate.php?token=$token";

            $msg = "Account created. Activate via email:<br><a href='$activationLink'>$activationLink</a>";

            // aquí luego irá mail()
        }
        catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Registration failed.";
        }
    }
}
require __DIR__ . '/../includes/header.php';

?>

<h2>Register</h2>

<p><?= $msg ?></p>
<form method="post">
    <label>Email <input name="email" required></label><br>
    <label>Password <input type="password" name="password" required></label><br>
    <button>Create account</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>