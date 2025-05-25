<?php
/**
 * @param string $filename 
 * @param array $data 
 */
function include_frontend_file(string $filename, array $data = []): void
{
    global $conf;

    $filepath = $_SERVER['DOCUMENT_ROOT'] . $conf['frontend_dir'] . '/' . $filename;

    if (file_exists($filepath)) {
        extract($data);
        include $filepath;
    } else {
        echo "<!-- Файл frontend не найден: " . htmlspecialchars($filename) . " -->";
    }
}

/**
 * @param string $path 
 * @return string 
 */
function frontend_url(string $path): string
{
    global $conf;
    return $conf['basedir'] . 'frontend/' . $path;
}



/**
 * @param string 
 * @param array 
 * @return string
 */
function theme($template, $variables = [])
{
    global $conf;
    extract($variables);
    ob_start();
    include($conf['theme'] . '/' . $template . '.tpl.php');
    return ob_get_clean();
}


