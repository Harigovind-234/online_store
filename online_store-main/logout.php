<?php
session_start();
session_destroy(); // destroys all session variables
header("Location: login.php");
exit;
?>
