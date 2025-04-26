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
   function get_other_users_skills($conn, $user_id) {
       $sql = "SELECT 
                   u.user_id,
                   u.first_name,
                   u.last_name,
                   u.email,
                   u.gender,
                   s.skills_id,
                   s.name, 
                   s.description,
                   (SELECT COUNT(*) FROM user_skills WHERE user_id = u.user_id) > 1 AS has_multiple
               FROM users_p u
               INNER JOIN user_skills us ON u.user_id = us.user_id
               INNER JOIN skills s ON us.skill_id = s.skills_id
               WHERE u.user_id != ?";

       $stmt = $conn->prepare($sql);
       $stmt->execute([$user_id]); // Pass $user_id as an array

       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
?>
