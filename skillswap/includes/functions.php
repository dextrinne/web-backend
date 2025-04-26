<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function get_user_data($conn, $user_id) {
    $stmt = $conn->prepare("SELECT user_id, login, first_name, last_name, email, birthdate, gender FROM users_p WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function delete_user_skill($conn, $user_id, $skill_id) {
    $stmt = $conn->prepare("DELETE FROM user_skills WHERE user_id = ? AND skill_id = ?");
    return $stmt->execute([$user_id, $skill_id]);
}

function get_all_users_with_skills($conn) {
    $stmt = $conn->prepare("
        SELECT
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.gender,
            s.name AS skill_name,
            s.description AS skill_description
        FROM
            users_p u
        INNER JOIN
            user_skills us ON u.user_id = us.user_id
        INNER JOIN
            skills s ON us.skill_id = s.skills_id
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_user_skills($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT s.skills_id, s.name, s.description, us.added_from_user_id
        FROM user_skills us
        JOIN skills s ON us.skill_id = s.skills_id
        WHERE us.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_learning_skills($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT s.skills_id, s.name, s.description, uls.from_user_id,
               u.first_name, u.last_name, u.email
        FROM user_learning_skills uls
        JOIN skills s ON uls.skill_id = s.skills_id
        LEFT JOIN users_p u ON uls.from_user_id = u.user_id
        WHERE uls.user_id = ?
        ORDER BY uls.added_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_other_users_skills($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT s.skills_id, s.name, s.description, 
               u.user_id, u.first_name, u.last_name, u.email, u.gender,
               EXISTS(
                   SELECT 1 FROM user_skills us2 
                   WHERE us2.user_id = ? AND us2.skill_id = s.skills_id
               ) as already_has_skill,
               EXISTS(
                   SELECT 1 FROM user_learning_skills uls 
                   WHERE uls.user_id = ? AND uls.skill_id = s.skills_id
               ) as already_learning,
               (SELECT COUNT(*) FROM user_skills us3 WHERE us3.user_id = u.user_id) > 1 as has_multiple
        FROM user_skills us
        JOIN skills s ON us.skill_id = s.skills_id
        JOIN users_p u ON us.user_id = u.user_id
        WHERE us.user_id != ?
        ORDER BY u.last_name, u.first_name, s.name
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
