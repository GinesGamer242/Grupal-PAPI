<?php

require __DIR__ . '/../config/session.php';

session_destroy();
header("Location: login.php");

exit;

?>