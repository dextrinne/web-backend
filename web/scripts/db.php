<?php
try {
    $db = new PDO(
        'mysql:host=localhost;dbname=u68595;charset=utf8',
        'u68595',
        '6788124',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

function db_row($stmt)
{
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function db_error()
{
    global $db;
    return $db->errorInfo();
}

function db_query($query)
{
    global $db;
    $q = $db->prepare($query);
    $args = func_get_args();
    array_shift($args);
    $res = $q->execute($args);
    if ($res) {
        while ($row = db_row($res)) {
            if (isset($row['id']) && !isset($r[$row['id']])) {
                $r[$row['id']] = $row;
            } else {
                $r[] = $row;
            }
        }
    }
    return $r;
}

function db_result($query)
{
    global $db;
    $q = $db->prepare($query);
    $args = func_get_args();
    array_shift($args);
    $res = $q->execute($args);
    if ($res) {
        if ($row = db_row($res)) {
            return $row[0];
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

function db_command($query)
{
    global $db;
    $q = $db->prepare($query);
    $args = func_get_args();
    array_shift($args);
    return $res = $q->execute($args);
}

function db_insert_id()
{
    global $db;
    return $db->lastInsertId();
}

function db_get($name, $default = FALSE)
{
    if (strlen($name) == 0) {
        return $default;
    }
    $value = db_result("SELECT value FROM variable WHERE name = ?", $name);
    if ($value === FALSE) {
        return $default;
    } else {
        return $value;
    }
}

function db_set($name, $value)
{
    if (strlen($name) == 0) {
        return;
    }

    $v = db_get($name);
    if ($v === FALSE) {
        $q = "INSERT INTO variable VALUES (?, ?)";
        return db_command($q, $name, $value) > 0;
    } else {
        $q = "UPDATE variable SET value = ? WHERE name = ?";
        return db_command($q, $value, $name) > 0;
    }
}

function db_array()
{
    global $db; 
    $args = func_get_args();
    $key = array_shift($args);
    $query = array_shift($args);
    $q = $db->prepare($query);
        $res = $q->execute($args);
    $r = array();
    if ($res) {
        while ($row = db_row($res)) {
            if (!empty($key) && isset($row[$key]) && !isset($r[$row[$key]])) {
                $r[$row[$key]] = $row;
            } else {
                $r[] = $row;
            }
        }
    }
    return $r;
}