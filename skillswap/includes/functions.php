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

function get_user_skills($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT s.skills_id, s.name, s.description
        FROM skills s
        INNER JOIN user_skills us ON s.skills_id = us.skill_id
        WHERE us.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function delete_user_skill($conn, $user_id, $skill_id) {
    $stmt = $conn->prepare("DELETE FROM user_skills WHERE user_id = ? AND skill_id = ?");
    return $stmt->execute([$user_id, $skill_id]);
}

function sanitize_input($data) {
       $data = trim($data);
       $data = stripslashes($data);
       $data = htmlspecialchars($data);
       return $data;
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

function get_other_users_skills($conn, $current_user_id) {
    $query = "
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.gender,
            u.email,
            s.skills_id,
            s.name,
            s.description,
            COUNT(s2.skills_id) > 1 as has_multiple
        FROM 
            users_p u
        JOIN 
            user_skills us ON u.user_id = us.user_id
        JOIN 
            skills s ON us.skill_id = s.skills_id
        LEFT JOIN
            user_skills us2 ON u.user_id = us2.user_id
        LEFT JOIN
            skills s2 ON us2.skill_id = s2.skills_id
        WHERE 
            u.user_id != ?
            AND NOT EXISTS (
                SELECT 1 FROM user_skills 
                WHERE user_id = ? AND skill_id = s.skills_id
            )
        GROUP BY
            u.user_id, s.skills_id
        ORDER BY 
            u.last_name, u.first_name, s.name
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $current_user_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
