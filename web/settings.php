<?php
// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'u68595');
define('DB_USER', 'u68595');
define('DB_PASS', '6788124');

// Настройки безопасности
define('CSRF_TOKEN_LIFETIME', 3600);

// Настройки пути
define('FRONTEND_DIR', __DIR__ . '/frontend');
define('THEME_DIR', __DIR__ . '/theme');

// Другие настройки
define('SITE_NAME', 'Форма регистрации');
define('DISPLAY_ERRORS', true);

define('INCLUDE_PATH', './scripts' . PATH_SEPARATOR . './modules');

$conf = array(
  'sitename' => 'Blablabla',
  'theme' => './theme',
  'charset' => 'UTF-8',
  'clean_urls' => TRUE,
  'display_errors' => 1,
  'date_format' => 'Y.m.d',
  'date_format_2' => 'Y.m.d H:i',
  'date_format_3' => 'd.m.Y',
  'basedir' => '/web-backend/web/',
  'login' => 'admin',
  'password' => '123',
  'admin_mail' => 'sin@kubsu.ru',
  'frontend_dir' => '/web-backend/web/frontend',
  'db_host' => 'localhost',
  'db_name' => 'u68595',
  'db_user' => 'u68595',
  'db_psw' => '6788124',
  'form_module' => 'form_reg',
  'admin_module' => 'admin_panel',
);

$urlconf = array(
  '' => array('module' => 'front'),
  '/^admin$/' => array('module' => 'admin_panel', 'auth' => 'auth_basic'),
  /*'/^edit_user\/(\d+)$/' => array('module' => 'edit_user', 'auth' => null ),*/
  //'/^edit_user\/(\d+)$/' => array('module' => 'edit_user'),
  '/^form$/' => array('module' => 'form'),
  '/^success$/' => array('module' => 'success'),
  //'/^register$/' => array('module' => 'form_reg'),
  '/^login$/' => array('module' => 'login'),
  '/^logout$/' => array('module' => 'logout'),
  
  '/^edit_user(\/\d+)?$/' => array('module' => 'edit_user'),
  '/^register$/' => array('module' => 'form_reg'),
);


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=' . $conf['charset']);