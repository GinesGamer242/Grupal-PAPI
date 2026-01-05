<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//DB
$pdo = new PDO(
    "mysql:host=localhost;dbname=ecommerce;charset=utf8",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

//MAIL 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lucixsg.ordenador@gmail.com';
        $mail->Password   = 'dmtq izlf lofb yhzw'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('lucixsg.ordenador@gmail.com', 'E-commerce Platform');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
    } catch (Exception $e) {
        // Para el trabajo: no rompemos la ejecución
        error_log($mail->ErrorInfo);
    }
}

// ACTION
$action = $_GET["action"] ?? "login";

//REGISTER
if ($action === "register" && $_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if (!$email || empty($password)) {
        die("Invalid email or password too short");
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("Email already registered");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));

    $stmt = $pdo->prepare(
        "INSERT INTO users (email, password_hash, activation_token)
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$email, $hash, $token]);

    $link = "http://localhost/papi/LuciaSoria_ecommerce/index.php?action=activate&token=$token";

    sendMail(
        $email,
        "Activate your account",
        "Click the following link to activate your account:<br>
         <a href='$link'>$link</a>"
    );

    echo "Registration successful. Check your email to activate the account.";
    exit;
}

// ACTIVATE
if ($action === "activate") {
    $token = $_GET["token"] ?? "";

    $stmt = $pdo->prepare(
        "UPDATE users
         SET is_active = 1, activation_token = NULL
         WHERE activation_token = ?"
    );
    $stmt->execute([$token]);

    if ($stmt->rowCount() === 0) {
        die("Invalid or expired activation token");
    }

    echo "Account activated. <a href='index.php'>Login</a>";
    exit;
}

//LOGIN 
if ($action === "login" && $_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    $stmt = $pdo->prepare(
        "SELECT id, password_hash, is_admin, is_active
         FROM users WHERE email = ?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (
        !$user ||
        !$user["is_active"] ||
        !password_verify($password, $user["password_hash"])
    ) {
        die("Invalid credentials or inactive account");
    }

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["is_admin"] = $user["is_admin"];

    if ($user['is_admin']) {
        header("Location: index.php?action=dashboard");
    } else {
        header("Location: index.php?action=shop");
    }
    exit;

}

//LOGOUT
if ($action === "logout") {
    session_destroy();
    header("Location: index.php?action=login");
    exit;
}

//FORGOT PASSWORD
if ($action === "forgot" && $_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"] ?? "";
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare(
            "UPDATE users SET reset_token = ? WHERE email = ?"
        );
        $stmt->execute([$token, $email]);

        $link = "http://localhost/papi/LuciaSoria_ecommerce/index.php?action=reset&token=$token";

        sendMail(
            $email,
            "Password recovery",
            "Reset your password using the following link:<br>
             <a href='$link'>$link</a>"
        );
    }

    echo "If the email exists, a recovery link has been sent.";
    exit;
}

//RESET PASSWORD
if ($action === "reset" && $_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["token"] ?? "";
    $password = $_POST["password"] ?? "";

    if (empty($password)) {
        die("Password too short");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "UPDATE users
         SET password_hash = ?, reset_token = NULL
         WHERE reset_token = ?"
    );
    $stmt->execute([$hash, $token]);

    if ($stmt->rowCount() === 0) {
        die("Invalid or expired token");
    }

    echo "Password updated. <a href='index.php'>Login</a>";
    exit;
}
?>

<!-- ================= VIEWS ================= -->

<?php if ($action === "login"): ?>
<h2>Login</h2>
<form method="POST">
    Email: <input name="email"><br>
    Password: <input type="password" name="password"><br>
    <button>Login</button>
</form>
<a href="index.php?action=register_form">Register</a><br>
<a href="index.php?action=forgot_form">Forgot password</a>
<?php endif; ?>

<?php if ($action === "register_form"): ?>
<h2>Register</h2>
<form method="POST" action="index.php?action=register">
    Email: <input name="email"><br>
    Password: <input type="password" name="password"><br>
    <button>Register</button>
</form>
<a href="index.php">Back to login</a>
<?php endif; ?>

<?php if ($action === "forgot_form"): ?>
<h2>Recover password</h2>
<form method="POST" action="index.php?action=forgot">
    Email: <input name="email"><br>
    <button>Send recovery email</button>
</form>
<a href="index.php">Back to login</a>
<?php endif; ?>

<?php if ($action === "reset" && $_SERVER["REQUEST_METHOD"] === "GET"): ?>
<h2>Reset password</h2>
<form method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET["token"]) ?>">
    New password: <input type="password" name="password"><br>
    <button>Reset</button>
</form>
<?php endif; ?>
