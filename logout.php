<?php
session_start();
session_unset();
session_destroy();
header("Location: /pjbl/login/Login.php");
exit;
?>