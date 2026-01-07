<?php

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../config/session.php';

$msg = '';

$token = $_GET['token'] ?? '';

if ($token === '')
{
    $msg = "Invalid activation link.";
}
else
{
    $stmt = $pdo->prepare("
        SELECT at.user_id
        FROM activation_tokens at
        WHERE at.token = ?
          AND at.expires > NOW()
          AND at.used = 0
    ");
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row)
    {
        $msg = "Activation link invalid or expired.";
    }
    else
    {
        $pdo->beginTransaction();

        try
        {
            $pdo->prepare("
                UPDATE users SET is_active = 1 WHERE id = ?
            ")->execute([$row['user_id']]);

            $pdo->prepare("
                UPDATE activation_tokens SET used = 1 WHERE token = ?
            ")->execute([$token]);

            $pdo->commit();

            $msg = "Account activated. You can now log in.";
        }
        catch (Exception $e)
        {
            $pdo->rollBack();
            $msg = "Activation failed.";
        }
    }
}
require __DIR__ . '/../includes/header.php';

?>

<h2>Account activation</h2>
<p><?= htmlspecialchars($msg) ?></p>

<?php require __DIR__ . '/../includes/footer.php'; ?>