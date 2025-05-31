<?php
session_start();
session_destroy();
header("Location: /web-backend/web/index.php"); 
exit;
?>