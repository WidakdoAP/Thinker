<?php
session_start();
session_unset();
session_destroy();
header("Location: /pjbl/landing/Landing.php");
exit;
?>