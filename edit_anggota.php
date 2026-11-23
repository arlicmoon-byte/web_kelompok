<?php
require_once "config/auth.php";
require_once "config/database.php";

wajibRole("murid");

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: murid.php");
    exit;
}

// Ambil anggota sekaligus periksa kepemilikan kelompok
$stmt = $pdo->prepare(
    "SELECT a.id, a.nama_anggota, a.kelompok_id
     FROM anggota a
     INNER JOIN kelompok k ON a.kelompok_id = k.id
     WHERE a.id = ? AND k.created_by = ?"
);

$stmt->execute([$id, $_SESSION["user_id"]]);
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anggota) {
    exit("Anggota tidak ditemukan.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Anggota - GroupTask</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="dashboard-body">

<main class="dashboard-main">
    <div class="group-form-card">

        <a href="detail_kelompok.php?id=<?= (int)$anggota["kelompok_id"] ?>"
           class="detail-link">
            ← Kembali
        </a>

        <h1>Edit Nama Anggota</h1>

        <form action="aksi.php?aksi=edit_anggota" method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

            <input type="hidden" name="anggota_id"
                   value="<?= (int)$anggota["id"] ?>">

            <input type="hidden" name="kelompok_id"
                   value="<?= (int)$anggota["kelompok_id"] ?>">

            <label>Nama Anggota</label>

            <input
                type="text"
                name="nama_anggota"
                value="<?= htmlspecialchars($anggota["nama_anggota"]) ?>"
                maxlength="100"
                required
            >

            <button type="submit" class="primary-button">
                Simpan Perubahan
            </button>

        </form>

    </div>
</main>

</body>
</html>