<?php
session_start();
session_destroy();
header('Location: /DONGIV%20S2/auth/Login.php');
exit();
?>
