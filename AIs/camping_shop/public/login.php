<?php

require __DIR__ . '/../config/conn.php';
session_start();

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Retrieve user by email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    // Validate credentials
    if (!$u || !password_verify($pass, $u['password_hash']))
    {
        $err = 'Credenciales inválidas';
    }
    else
    {
        // Check if account is active
        if (!$u['is_active'])
        {
            $err = 'Cuenta no activada. Revisa tu correo.';
        }
        else
        {
            // Store user session without password hash
            unset($u['password_hash']);
            $_SESSION['user'] = $u;

            // Redirect based on role
            if ($_SESSION['user']['is_admin'])
            {
                header('Location: ../admin/index.php');
            }
            else
            {
                header ('Location: index.php');
            }
            exit;
        }
    }
}

require __DIR__ . '/../includes/header.php';

?>

<h2>Login</h2>
<?php if($err) echo '<p style="color:red;">'.htmlspecialchars($err).'</p>'; ?>

<form method="post">
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button>Enter</button>
</form>

<p><a href="forgot.php">Forgot password?</a></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>