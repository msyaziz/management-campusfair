<?php
/**
 * Konfigurasi Koneksi Database MariaDB/MySQL menggunakan PDO
 */

$host     = '127.0.0.1';
$dbname   = 'campusfair';
$username = 'admin_db';
$password = 'password1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
