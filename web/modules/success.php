<?php
function success_get($request) {
    // Очищаем учетные данные из сессии, чтобы они не отображались снова
    $login = isset($_SESSION['new_login']) ? $_SESSION['new_login'] : '';
    $password = isset($_SESSION['new_password']) ? $_SESSION['new_password'] : '';

    unset($_SESSION['new_login']);
    unset($_SESSION['new_password']);
    unset($_SESSION['show_credentials']);
    unset($_SESSION['save']);

    return theme('success_form', ['login' => $login, 'password' => $password]);
}
?>