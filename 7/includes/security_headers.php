<?php
// Запрет кэширования конфиденциальных страниц
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Защита от XSS
header("X-XSS-Protection: 1; mode=block");

// Защита от MIME-sniffing
header("X-Content-Type-Options: nosniff");

// Запрет фреймов (защита от clickjacking)
header("X-Frame-Options: DENY");

// Политика безопасности контента
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:");
?>