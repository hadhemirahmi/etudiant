<?php
session_start();
session_destroy();
unset($_SESSION['user_id']);
unset($_SESSION['role']);
unset($_SESSION['name']);
session_regenerate_id(true);  

header("Location: login.php");
exit;
?>