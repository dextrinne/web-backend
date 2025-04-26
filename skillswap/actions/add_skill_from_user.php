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
$from_user_id = $_POST['from_user_id'];
$add_all = isset($_POST['add_all']) ? $_POST['add_all'] : 0;

// Проверяем, что навык существует
$stmt = $conn->prepare("SELECT * FROM skills WHERE skills_id = ?");
$stmt->bind_param("i", $skill_id);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();

if (!$skill) {
    $_SESSION['error'] = "Навык не найден";
    header("Location: ../user.php");
    exit();
}

// Добавляем навык текущему пользователю
$stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id, added_from_user_id) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $current_user_id, $skill_id, $from_user_id);
$stmt->execute();

if ($add_all) {
    // Добавляем все навыки пользователя
    $query = "
        INSERT INTO user_skills (user_id, skill_id, added_from_user_id)
        SELECT ?, skill_id, ?
        FROM user_skills
        WHERE user_id = ?
        AND NOT EXISTS (
            SELECT 1 FROM user_skills 
            WHERE user_id = ? AND skill_id = user_skills.skill_id
        )
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $current_user_id, $from_user_id, $from_user_id, $current_user_id);
    $stmt->execute();
}

$_SESSION['success'] = "Навык успешно добавлен";
header("Location: ../user.php");
exit();
?>
