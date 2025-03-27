<?php 
require_once '../_base.php';

// Clear session data
session_destroy();

header("Location: ../index.php");
exit;
?>