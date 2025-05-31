<?php
session_start();

// Очищаем данные редактирования
unset($_SESSION['edit_user_id']);

$return_to_admin = isset($_GET['return_to_admin']);
session_destroy();

if ($return_to_admin) {
    header("Location: /web-backend/web/modules/admin_panel.php");
} else {
    header("Location: /web-backend/web/#form-anchor");
}
exit;
?>