<?php
session_start();
require_once "Connection.php"; 

// Check if a token is provided
if (isset($_GET['token'])) {
    $token = rawurldecode(trim($_GET['token']));

    if ($token === '' || strlen($token) < 20) {
        $message = "Invalid token";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE activation_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
           if ($user['is_active'] == 1) {
                $message = "Account already activated";
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE users 
                    SET is_active = 1, activation_token = NULL 
                    WHERE id = ?"
                );
    $stmt->execute([$user['id']]);

    $message = "Account successfully activated. You can now log in";
}

        } else {
            $message = "Invalid token or account already activated";
        }
    }
} else {
    $message = "Token not provided";
}


?>

<h2>Activación de Cuenta</h2>
<p><?php echo $message; ?></p>
<a href="index.php">Ir a login</a>
