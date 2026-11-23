<?php

require_once "config/database.php";

$pesan = "";
$berhasil = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST['nama'] ?? "");
    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($nama === "" || $username === "" || strlen($password) < 8) {

        $pesan = "Lengkapi data dan gunakan password minimal 8 karakter.";

    } else {

        $cek = $pdo->prepare(
            "SELECT id FROM users WHERE username = ?"
        );

        $cek->execute([$username]);

        if ($cek->fetch()) {

            $pesan = "Username sudah digunakan!";

        } else {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $simpan = $pdo->prepare(
                "INSERT INTO users (nama, username, password, role)
                 VALUES (?, ?, ?, 'murid')"
            );

            $simpan->execute([
                $nama,
                $username,
                $passwordHash
            ]);

            $berhasil = true;
            $pesan = "Pendaftaran berhasil! Silakan login.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar GroupTask</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="auth-body">

<div class="auth-card">

    <h1>Buat Akun</h1>

    <p>Daftar sebagai murid untuk menggunakan GroupTask.</p>

    <?php if ($pesan): ?>
        <div class="<?= $berhasil ? 'tag done' : 'error' ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Nama lengkap</label>
        <input
            type="text"
            name="nama"
            placeholder="Nama lengkap"
            maxlength="100"
            required
        >

        <label>Username</label>
        <input
            type="text"
            name="username"
            placeholder="Buat username"
            maxlength="50"
            required
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Minimal 8 karakter"
            minlength="8"
            required
        >

        <button type="submit" class="btn">
            Daftar Sekarang
        </button>

    </form>

    <a href="login.php">Sudah punya akun? Login</a>

</div>

</body>
</html>