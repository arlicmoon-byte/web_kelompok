<?php

session_start();

require_once "config/database.php";

// Jika sudah login, arahkan sesuai role
if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] === 'guru') {
        header("Location: guru.php");
    } else {
        header("Location: murid.php");
    }

    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    $query = $pdo->prepare(
        "SELECT * FROM users WHERE username = ?"
    );

    $query->execute([$username]);

    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'guru') {
            header("Location: guru.php");
        } else {
            header("Location: murid.php");
        }

        exit;

    } else {

        $error = "Username atau password salah!";

    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login GroupTask</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="auth-body">

<div class="auth-card">

    <h1>Group<span>Task.</span></h1>

    <p>
        Selamat datang! Silakan login untuk melanjutkan.
    </p>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Masukkan username"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Masukkan password"
            required
        >

        <button type="submit" class="btn">
            Login
        </button>

    </form>

    <a href="buat_akun.php">
        Belum punya akun? Daftar di sini
    </a>

    <small>
        GroupTask - Sistem Pembagian Tugas Kelompok
    </small>

</div>

</body>
</html>