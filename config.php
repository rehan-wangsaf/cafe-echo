<?php
// config.php - Konfigurasi database dan session

session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'echo_cafe');

// Koneksi Database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Base URL
define('BASE_URL', 'http://localhost/echo-cafe/');

// Fungsi helper
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function generateOrderNumber() {
    return 'EC' . date('Ymd') . rand(1000, 9999);
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isKasir() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'kasir';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('dashboard.php');
    }
}

// Fungsi untuk sanitasi input
function cleanInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>