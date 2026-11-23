
<?php

// ==========================================
// AUTHENTICATION SYSTEM - GROUPTASK
// ==========================================

// Memulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================
// FUNGSI CEK LOGIN
// ==========================================

function wajibLogin() {

    // Periksa apakah pengguna sudah login
    if (
        !isset($_SESSION['user_id']) ||
        !isset($_SESSION['role'])
    ) {
        header("Location: login.php");
        exit;
    }

    // Pastikan role valid
    if (!in_array($_SESSION['role'], ['murid', 'guru'], true)) {
        session_unset();
        session_destroy();

        header("Location: login.php");
        exit;
    }
}


// ==========================================
// FUNGSI CEK ROLE
// ==========================================

function wajibRole($role) {

    // Pastikan pengguna sudah login
    wajibLogin();

    // Pastikan role yang diminta valid
    if (!in_array($role, ['murid', 'guru'], true)) {
        http_response_code(403);
        exit("Role tidak valid.");
    }

    // Jika role tidak sesuai
    if ($_SESSION['role'] !== $role) {

        // Jika pengguna adalah guru
        if ($_SESSION['role'] === 'guru') {
            header("Location: guru.php");
            exit;
        }

        // Jika pengguna adalah murid
        if ($_SESSION['role'] === 'murid') {
            header("Location: murid.php");
            exit;
        }
    }
}


// ==========================================
// FUNGSI CEK APAKAH PENGGUNA ADALAH GURU
// ==========================================

function isGuru() {

    return isset($_SESSION['role']) &&
           $_SESSION['role'] === 'guru';

}


// ==========================================
// FUNGSI CEK APAKAH PENGGUNA ADALAH MURID
// ==========================================

function isMurid() {

    return isset($_SESSION['role']) &&
           $_SESSION['role'] === 'murid';

}

?>
