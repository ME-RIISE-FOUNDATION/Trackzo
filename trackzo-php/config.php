<?php
/**
 * Trackzo — Database configuration
 * -----------------------------------------------------------------
 * EDIT THE 4 VALUES BELOW with your Hostinger MySQL details.
 * In hPanel: Databases -> MySQL Databases -> create a database + user,
 * then copy the name / user / password here. On Hostinger the host is
 * almost always "localhost".
 * -----------------------------------------------------------------
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'trackzo');       // e.g. u123456789_trackzo
define('DB_USER', 'root');          // e.g. u123456789_admin
define('DB_PASS', '');              // your database password

// App settings
define('APP_NAME', 'Trackzo');
define('APP_CURRENCY', '₹');   // Indian Rupee

// --- Do not edit below this line ------------------------------------
date_default_timezone_set('UTC');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Return a shared PDO connection. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(
                '<div style="font-family:system-ui;max-width:640px;margin:80px auto;padding:24px;'
                . 'border:1px solid #fecaca;background:#fef2f2;border-radius:16px;color:#991b1b">'
                . '<h2 style="margin:0 0 8px">Database connection failed</h2>'
                . '<p style="margin:0 0 12px;color:#7f1d1d">' . htmlspecialchars($e->getMessage()) . '</p>'
                . '<p style="margin:0;color:#7f1d1d">Open <code>config.php</code> and check your DB_NAME, '
                . 'DB_USER and DB_PASS. On Hostinger create the database first under '
                . '<strong>hPanel &rarr; Databases &rarr; MySQL Databases</strong>.</p></div>'
            );
        }
    }
    return $pdo;
}
