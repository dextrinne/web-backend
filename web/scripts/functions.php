<?php
/**
 * @param string $filename 
 * @param array $data 
 */
function include_frontend_file(string $filename, array $data = []): void
{
    global $conf;

    $filepath = __DIR__ . '/../frontend/' . $filename;

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
    $base_url = conf('basedir');
    return 'frontend/' . $path;
}
