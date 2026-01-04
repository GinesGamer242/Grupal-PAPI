<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../config/conn.php';

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$ok = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Retrieve and sanitize email
    $email = trim($_POST['email'] ?? '');

    // Check if email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if (!$u)
    {
        $err = "Written mail doesn't exist";
    }
    else
    {
        // Generate secure reset token
        $token = bin2hex(random_bytes(24));
        $expires = date('Y-m-d H:i:s', time()+3600);

        // Store token in database
        $pdo->prepare('INSERT INTO password_resets (user_id, token, expires) VALUES (?,?,?)')
            ->execute([$u['id'],$token,$expires]);

        // Build reset link
        $link = (isset($_SERVER['HTTP_HOST'])? 'http://'.$_SERVER['HTTP_HOST'] : '') .
                '/PAPI/camping_shop/public/reset_password.php?token='.$token;

        // Send reset email
        SendMail($email, $link);
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
        $mail->Subject = 'Password Reset Mail';
        $mail->Body    = "To reset your account's password, click the next link: $sentLink";
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

<h2>Password recover</h2>
<?php if($err) echo '<p style="color:red;">'.htmlspecialchars($err).'</p>'; ?>
<?php if($ok) echo '<p style="color:green;">'.htmlspecialchars($ok).'</p>'; ?>

<form method="post">
    <label>Email: <input type="email" name="email" required></label><br>
    <button>Send Link</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>