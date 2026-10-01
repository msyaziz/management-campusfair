<?php
/**
 * Helper Autentikasi dan Manajemen Session
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login. Jika belum, redirect ke halaman login.
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /auth/login.php");
        exit;
    }
}

/**
 * Cek apakah user yang login memiliki role admin. Jika bukan, beri akses ditolak atau redirect.
 */
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die("Akses Ditolak: Fitur ini hanya dapat diakses oleh Admin.");
    }
}

/**
 * Mendapatkan data user yang sedang login dari session.
 */
function current_user() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'nama'     => $_SESSION['nama'],
        'username' => $_SESSION['username'],
        'role'     => $_SESSION['role']
    ];
}
