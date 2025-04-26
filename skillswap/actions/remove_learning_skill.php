<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$skill_id = $_POST['skill_id'];

try {
    $stmt = $conn->prepare("
        DELETE FROM user_learning_skills 
        WHERE user_id = ? AND skill_id = ?
    ");
    $stmt->execute([$current_user_id, $skill_id]);

    $_SESSION['success'] = "Навык удалён из списка для обучения";
    header("Location: ../user.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при удалении навыка: " . $e->getMessage();
    header("Location: ../user.php");
    exit();
}
