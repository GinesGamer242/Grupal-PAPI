<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../config/conn.php';

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

session_start();

$err = $ok = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $pass = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');

    // Input validation
    if (!$email) $err = 'Invalid Email';
    elseif (strlen($pass) < 6) $err = 'Minimum password lenght of 6 characters';
    else
    {
        // Check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) $err = 'Email already registered';
        else
        {
            // Hash password before storing it
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $pdo->prepare(
                'INSERT INTO users (email,password_hash,name,is_active) VALUES (?,?,?,0)'
            )->execute([$email,$hash,$name]);

            // Generate activation token
            $uid = $pdo->lastInsertId();
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', time()+3600*24);

            $pdo->prepare(
                'INSERT INTO activation_tokens (user_id, token, expires) VALUES (?,?,?)'
            )->execute([$uid,$token,$expires]);

            // Build activation link and send email
            $link = (isset($_SERVER['HTTP_HOST']) ? 'http://'.$_SERVER['HTTP_HOST'] : '')
                . '/PAPI/camping_shop/public/activate.php?token='.$token;    

            SendMail($email, $link);
        }
    }
}

function SendMail(string $userEmail, string $sentLink)
{
    $mail = new PHPMailer(true);

    try
    {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ginesgonzalezbruscaspam@gmail.com';
        $mail->Password   = 'vsuy dhes ncrl sgdm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('campingEnteprise@gmail.com', 'Sender');
        $mail->addAddress($userEmail, 'User');

        $mail->isHTML(true);
        $mail->Subject = 'Account Verification Mail';
        $mail->Body    = "This is the last step before verifying your account! To verify it, click the next link : $sentLink";
        $mail->AltBody = "Couldn't show mail.";

        $mail->send();
    }
    catch (Exception $e)
    {
        echo "Error sending the mail: {$mail->ErrorInfo}";
    }
}

require __DIR__ . '/../includes/header.php';

?>

<h2>Register</h2>

<?php if($err) echo '<p style="color:red;">'.htmlspecialchars($err).'</p>'; ?>
<?php if($ok) echo '<p style="color:green;">'.htmlspecialchars($ok).'</p>'; ?>

<form method="post">
    <label>Name: <input name="name"></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button>Register</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>