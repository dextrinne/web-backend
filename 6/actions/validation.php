<?php
function validateFormData($db, &$errors, &$values, $abilities) {
    $errors = FALSE;

    if (empty($_POST['fio'])) {
        setcookie('fio_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        if (strlen($_POST['fio']) > 150) {
            setcookie('fio_error', '2', time() + 24 * 60 * 60);
            $errors = TRUE;
        } elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]+$/u", $_POST['fio'])) {
            setcookie('fio_error', '3', time() + 24 * 60 * 60);
            $errors = TRUE;
        }
    }

    if (empty($_POST['tel']) || !preg_match('/^\+7\d{10}$/', $_POST['tel'])) {
        setcookie('tel_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if (empty($_POST["email"]) || !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    $fav_languages = $_POST["abilities"] ?? [];
    if (empty($fav_languages)) {
        setcookie('abilities_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        foreach ($fav_languages as $ability) {
            if (empty($abilities[$ability])) {
                setcookie('abilities_error', '1', time() + 24 * 60 * 60);
                $errors = TRUE;
            }
        }
    }

    if (empty($_POST['bdate']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['bdate'])) {
        setcookie('bdate_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if (empty($_POST['radio'])) {
        setcookie('radio_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if (empty($_POST['bio'])) {
        setcookie('bio_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } elseif(strlen($_POST['bio']) > 512) {
        setcookie('bio_error', '2', time() + 24 * 60 * 60);
        $errors = TRUE;
    } elseif(preg_match('/[<>{}\[\]]|<script|<\?php/i', $_POST['bio'])) {
        setcookie('bio_error', '3', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if (empty($_POST['ccheck'])) {
        setcookie('ccheck_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }


    return !$errors;
}
?>
