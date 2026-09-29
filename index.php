<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

if ($_SESSION['role'] === 'admin') {
    header("Location: /pendaftar/index.php");
    exit;
} else {
    header("Location: /wawancara/index.php");
    exit;
}
