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

    $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id, added_from_user_id, is_learning, status) 
                           VALUES (?, ?, ?, TRUE, 'LEARNING')
                           ON DUPLICATE KEY UPDATE is_learning = TRUE, status = 'LEARNING'");
    $stmt->execute([$current_user_id, $skill_id, $from_user_id]);

    if ($add_all) {
        $query = "
            INSERT INTO user_skills (user_id, skill_id, added_from_user_id, is_learning, status)
            SELECT ?, us.skill_id, ?, TRUE, 'LEARNING'
            FROM user_skills us
            WHERE us.user_id = ? AND (us.is_learning = FALSE OR us.is_learning IS NULL)
            AND NOT EXISTS (
                SELECT 1 FROM user_skills 
                WHERE user_id = ? AND skill_id = us.skill_id AND status = 'LEARNING'
            )
            ON DUPLICATE KEY UPDATE is_learning = TRUE, status = 'LEARNING'
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
