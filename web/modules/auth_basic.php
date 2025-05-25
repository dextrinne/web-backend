<?php
function auth(&$request, $r, $db) { // Accept $db as a parameter
    $user = null;

    if (empty($user) && !empty($_SERVER['PHP_AUTH_USER'])) {
        $admin_login = $_SERVER['PHP_AUTH_USER'];
        $admin_password = $_SERVER['PHP_AUTH_PW'];

        try {
            $stmt = $db->prepare("SELECT id, password FROM admin WHERE login = ?");
            $stmt->execute([$admin_login]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && hash('sha256', $admin_password) === $admin['password']) {
                $user = array(
                    'login' => $admin_login,
                    'pass' => $admin['password']
                );
                $request['user'] = $user;
            }
        } catch (PDOException $e) {
            error_log("Database error in auth(): " . $e->getMessage());
            return [
                'headers' => ['HTTP/1.1 500 Internal Server Error'],
                'entity' => 'Database error during authentication.'
            ];
        }
    }
    if (!isset($_SERVER['PHP_AUTH_USER']) || empty($user) || $_SERVER['PHP_AUTH_USER'] != $user['login']) {
        unset($user);
        $response = array(
            'headers' => array(
                sprintf('WWW-Authenticate: Basic realm="%s"', conf('sitename')),
                'HTTP/1.0 401 Unauthorized'
            ),
            'entity' => theme('401', $request),
        );
        return $response;
    }
}
