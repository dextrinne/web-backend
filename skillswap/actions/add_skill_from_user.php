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

try {
    $stmt = $conn->prepare("SELECT * FROM skills WHERE skills_id = ?");
    $stmt->execute([$skill_id]);
    $skill = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$skill) {
        $_SESSION['error'] = "Навык не найден";
        header("Location: ../user.php");
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO user_learning_skills (user_id, skill_id, from_user_id)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE from_user_id = VALUES(from_user_id)
    ");
    $stmt->execute([$current_user_id, $skill_id, $from_user_id]);

    if ($add_all) {
        $query = "
            INSERT INTO user_learning_skills (user_id, skill_id, from_user_id)
            SELECT ?, us.skill_id, ?
            FROM user_skills us
            WHERE us.user_id = ?
            AND NOT EXISTS (
                SELECT 1 FROM user_learning_skills 
                WHERE user_id = ? AND skill_id = us.skill_id
            )
            ON DUPLICATE KEY UPDATE from_user_id = VALUES(from_user_id)
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([$current_user_id, $from_user_id, $from_user_id, $current_user_id]);
    }

    $_SESSION['success'] = "Навык добавлен в список для обучения";
    header("Location: ../user.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при добавлении навыка: " . $e->getMessage();
    header("Location: ../user.php");
    exit();
}
