<?php
if (!function_exists('include_frontend_file')) {
    function include_frontend_file(string $filename, array $data = []): void {
        global $conf;
        $filepath = $_SERVER['DOCUMENT_ROOT'].$conf['frontend_dir'].'/'.$filename;
        
        if (file_exists($filepath)) {
            extract($data);
            include $filepath;
        } else {
            echo "<!-- File not found: ".htmlspecialchars($filename)." -->";
        }
    }
}

if (!function_exists('frontend_url')) {
    function frontend_url(string $path): string {
        global $conf;
        return $conf['basedir'].'frontend/'.$path;
    }
}