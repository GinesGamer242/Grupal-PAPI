<?php 
session_start();
require_once "Connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';


//ACTIVATION 
function SendMailConfirmation($destino, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'irenetest04@gmail.com';
        $mail->Password   = 'jaox zhlf ypff vdqn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('irenetest04@gmail.com', 'Confirmation acount');
        $mail->addAddress($destino);

        $mail->isHTML(true);
$mail->Subject = 'Activate your acount';
$url = 'http://localhost/PAPI/INDIVIDUALTASKII/Activation.php?token=' . rawurlencode($token);
$mail->Body = <<<HTML
<h2>Activate your acount</h2>
<p>Click here to activate your account:</p>
<a href="$url">Activate acount</a>
HTML;



        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}


//RECOVERATION PASSWORD 
function SendMailRecuperation($destino, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'irenetest04@gmail.com';
        $mail->Password   = 'jaox zhlf ypff vdqn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('irenetest04@gmail.com', 'Recover your password');
        $mail->addAddress($destino);

        $mail->isHTML(true);
$mail->Subject = 'Recover your password';
$url = 'http://localhost/PAPI/INDIVIDUALTASKII/ForgotPassword.php?token=' . rawurlencode($token);
$mail->Body = <<<HTML
<h2>Recover your password</h2>
<p>Click on the following link to change your password:</p>
<a href="$url">Reset password</a>
HTML;
;


        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}



//REGISTER 
if (isset($_POST['register'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $message = "The user already exists";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("INSERT INTO users (email, password, is_admin, is_active, activation_token) VALUES (?, ?, 0, 0, ?)");
        if ($stmt->execute([$email, $hashed, $token])) {
            SendMailConfirmation($email, $token);
            $message = "Registration successful. Check your email to activate your account.";
        } else {
            $message = "Error registering user.";
        }
    }
}


//LOGIN
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "User not found";
    } else if ($user['is_active'] == 0) {
        $message = "You must activate your account from your email.";
    } else if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];

        if ($user['is_admin'] == 1) {
            header("Location: Admin.php");
        } else {
            header("Location: Catalog.php");
        }
        exit;

    } else {
        $message = "Incorrect password";
    }
}


//FORGOT PASSWORD
if (isset($_POST['forgot'])) {

    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "There is no user with that email address.";
    } else {
        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("UPDATE users SET reset_token=?, reset_expiration=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email=?");
        $stmt->execute([$token, $email]);

        SendMailRecuperation($email, $token);
        $message = "A link to recover the password was sent.";
    }
}


//LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

?>

<h2>Authentication</h2>

<?php if (isset($message)) echo "<p>$message</p>"; ?>



<h3>Register</h3>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit" name="register">Register</button>
</form>


<h3>Login</h3>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit" name="login">Login</button>
</form>


<h3>Forgot your password?</h3>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    <button type="submit" name="forgot">Recover password</button>
</form>

<p><a href="CreateDataBase.php">GENERATE DATA (Database, all tables and some data)</a></p>


