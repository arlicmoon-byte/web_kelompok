<?php
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/database.php";

wajibRole("murid");

$kelompokId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$kelompokId) {
    header("Location: murid.php");
    exit;
}

// Periksa kepemilikan kelompok
$stmt = $pdo->prepare(
    "SELECT id, nama_kelompok
     FROM kelompok
     WHERE id = ? AND created_by = ?"
);

$stmt->execute([$kelompokId, $_SESSION["user_id"]]);
$kelompok = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kelompok) {
    exit("Kelompok tidak ditemukan atau akses ditolak.");
}

// Ambil anggota kelompok
$stmt = $pdo->prepare(
    "SELECT id, nama_anggota
     FROM anggota
     WHERE kelompok_id = ?
     ORDER BY nama_anggota ASC"
);

$stmt->execute([$kelompokId]);
$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$pesan = $_SESSION["pesan"] ?? "";
unset($_SESSION["pesan"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Jobdesk - GroupTask</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="dashboard-body">

<main class="dashboard-main">

    <div class="group-form-card">

        <a href="detail_kelompok.php?id=<?= (int)$kelompokId ?>"
           class="detail-link">
            ← Kembali ke Kelompok
        </a>

        <h1>Tambah Jobdesk</h1>

        <p class="welcome-text">
            Kelompok: <?= htmlspecialchars($kelompok["nama_kelompok"]) ?>
        </p>

        <?php if ($pesan): ?>
            <p class="form-notice">
                <?= htmlspecialchars($pesan) ?>
            </p>
        <?php endif; ?>

        <?php if (count($anggota) > 0): ?>

        <form action="aksi.php?aksi=tambah_jobdesk" method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

            <input type="hidden" name="kelompok_id"
                   value="<?= (int)$kelompokId ?>">

            <label>Judul Pekerjaan</label>
            <input
                type="text"
                name="judul_tugas"
                placeholder="Contoh: Membuat desain website"
                maxlength="150"
                required
            >

            <label>Penanggung Jawab</label>
            <select name="anggota_id" required>
                <option value="">Pilih anggota</option>

                <?php foreach ($anggota as $orang): ?>
                    <option value="<?= (int)$orang["id"] ?>">
                        <?= htmlspecialchars($orang["nama_anggota"]) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <label>Deskripsi Pekerjaan</label>
            <textarea
                name="deskripsi"
                rows="5"
                placeholder="Jelaskan pekerjaan yang harus dilakukan..."
            ></textarea>

            <label>Deadline</label>
            <input
                type="date"
                name="deadline"
                min="<?= date('Y-m-d') ?>"
            >

            <button type="submit" class="primary-button">
                Simpan Jobdesk
            </button>

        </form>

        <?php else: ?>

            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>Belum Ada Anggota</h3>
                <p>Tambahkan anggota kelompok terlebih dahulu.</p>
                <a href="detail_kelompok.php?id=<?= (int)$kelompokId ?>"
                   class="primary-button">
                    Tambah Anggota
                </a>
            </div>

        <?php endif; ?>

    </div>

</main>

</body>
</html>