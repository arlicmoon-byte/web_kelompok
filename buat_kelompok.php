<?php
require_once "config/auth.php";
require_once "config/database.php";

wajibRole("murid");

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$pesan = $_SESSION["pesan_error"] ?? "";
unset($_SESSION["pesan_error"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kelompok - GroupTask</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-body">

<main class="dashboard-main">
    <div class="group-form-card">
        <a href="murid.php" class="detail-link">← Kembali ke Dashboard</a>

        <h1>Buat Kelompok Baru</h1>
        <p class="welcome-text">
            Buat kelompok belajar agar tugasmu lebih mudah diatur.
        </p>

        <?php if ($pesan !== ""): ?>
            <p class="form-error">
                <?= htmlspecialchars($pesan) ?>
            </p>
        <?php endif; ?>

        <form action="aksi.php?aksi=simpan_kelompok" method="POST">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
            >

            <label for="nama_kelompok">Nama Kelompok</label>

            <input
                type="text"
                id="nama_kelompok"
                name="nama_kelompok"
                placeholder="Contoh: Kelompok 1"
                maxlength="100"
                required
            >

            <button type="submit" class="primary-button">
                Simpan Kelompok
            </button>
        </form>
    </div>
</main>

</body>
</html>