<?php
session_start();
session_destroy();
header("Location: /web-backend/web/#form-anchor"); 
exit;
?>