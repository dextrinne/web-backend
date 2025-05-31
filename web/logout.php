<?php
session_start();
$return_to_admin = isset($_GET['return_to_admin']);
session_destroy();

if ($return_to_admin) {
    header("Location: /web-backend/web/admin_panel.php");
} else {
    header("Location: /web-backend/web/#form-anchor");
}
exit;
?>