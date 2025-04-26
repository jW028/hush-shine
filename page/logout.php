<?php 
require_once '../_base.php';

// Clear session data
session_destroy();

session_start();
$_SESSION['logout_success'] = true;
$_SESSION['logout_time'] = time(); 

header("Location: ../index.php");
exit;
?>