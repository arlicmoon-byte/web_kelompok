
<?php

require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/database.php";

wajibRole("murid");

// ==========================================
// TOKEN CSRF
// ==========================================

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// ==========================================
// FUNGSI ESCAPE HTML
// ==========================================

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

// ==========================================
// MENGAMBIL ID KELOMPOK
// ==========================================

$kelompokId = filter_var(
    $_GET["id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if (!$kelompokId) {
    header("Location: murid.php");
    exit;
}

// ==========================================
// MEMERIKSA KEPEMILIKAN KELOMPOK
// ==========================================

$stmt = $pdo->prepare(
    "SELECT id, nama_kelompok
     FROM kelompok
     WHERE id = ? AND created_by = ?"
);

$stmt->execute([
    $kelompokId,
    $_SESSION["user_id"]
]);

$kelompok = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kelompok) {
    http_response_code(404);
    exit("Kelompok tidak ditemukan atau tidak memiliki akses.");
}

// ==========================================
// MENGAMBIL DATA ANGGOTA
// ==========================================

$stmt = $pdo->prepare(
    "SELECT id, nama_anggota
     FROM anggota
     WHERE kelompok_id = ?
     ORDER BY id ASC"
);

$stmt->execute([$kelompokId]);

$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// MENGAMBIL DATA JOBDESK
// ==========================================

$stmt = $pdo->prepare(
    "SELECT
        j.id,
        j.nama_tugas,
        j.deskripsi,
        j.deadline,
        j.status,
        a.nama_anggota
     FROM jobdesk j
     INNER JOIN anggota a ON j.anggota_id = a.id
     WHERE j.kelompok_id = ?
     ORDER BY j.id DESC"
);

$stmt->execute([$kelompokId]);

$jobdesk = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// PESAN NOTIFIKASI
// ==========================================

$pesan = $_SESSION["pesan"] ?? "";
$pesanError = $_SESSION["pesan_error"] ?? "";

unset($_SESSION["pesan"]);
unset($_SESSION["pesan_error"]);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Detail Kelompok - GroupTask</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body class="dashboard-body">

<main class="dashboard-main">

    <div class="group-form-card">

        <a href="murid.php" class="detail-link">
            ← Kembali ke Dashboard
        </a>

        <h1><?= e($kelompok["nama_kelompok"]) ?></h1>

        <p class="welcome-text">
            Kelola anggota dan pembagian pekerjaan kelompokmu.
        </p>

        <!-- NOTIFIKASI -->

        <?php if ($pesan !== ""): ?>

            <p class="form-notice" role="status">
                <?= e($pesan) ?>
            </p>

        <?php endif; ?>

        <?php if ($pesanError !== ""): ?>

            <p class="form-notice" role="alert">
                <?= e($pesanError) ?>
            </p>

        <?php endif; ?>

        <!-- TAMBAH ANGGOTA -->

        <h2>Tambah Anggota</h2>

        <form action="aksi.php?aksi=tambah_anggota"
              method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION["csrf_token"]) ?>"
            >

            <input
                type="hidden"
                name="kelompok_id"
                value="<?= (int)$kelompokId ?>"
            >

            <label for="nama_anggota">
                Nama Anggota
            </label>

            <input
                type="text"
                id="nama_anggota"
                name="nama_anggota"
                placeholder="Masukkan nama anggota"
                maxlength="100"
                required
            >

            <button type="submit" class="primary-button">
                + Tambahkan Anggota
            </button>

        </form>

        <hr>

        <!-- DAFTAR ANGGOTA -->

        <h2>Daftar Anggota</h2>

        <p class="welcome-text">
            Jumlah anggota: <?= count($anggota) ?> orang
        </p>

        <?php if (!empty($anggota)): ?>

            <div class="member-list">

                <?php foreach ($anggota as $index => $orang): ?>

                    <div class="member-item">

                        <div class="member-avatar">
                            <?= $index + 1 ?>
                        </div>

                        <span class="member-name">
                            <?= e($orang["nama_anggota"]) ?>
                        </span>

                        <div class="member-actions">

                            <a
                                href="edit_anggota.php?id=<?= (int)$orang["id"] ?>"
                                class="edit-button"
                            >
                                Edit
                            </a>

                            <form
                                action="aksi.php?aksi=hapus_anggota"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus anggota ini?');"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($_SESSION["csrf_token"]) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="anggota_id"
                                    value="<?= (int)$orang["id"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="kelompok_id"
                                    value="<?= (int)$kelompokId ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete-button"
                                >
                                    Hapus
                                </button>

                            </form>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <div class="empty-icon">👥</div>

                <h3>Belum Ada Anggota</h3>

                <p>Tambahkan anggota pertama kelompokmu.</p>

            </div>

        <?php endif; ?>

        <hr>

        <!-- DAFTAR JOBDESK -->

        <div class="jobdesk-heading">

            <div>

                <h2>Daftar Jobdesk</h2>

                <p class="welcome-text">
                    Pembagian pekerjaan setiap anggota kelompok.
                </p>

            </div>

            <a
                href="buat_jobdesk.php?id=<?= (int)$kelompokId ?>"
                class="primary-button"
            >
                + Tambah Jobdesk
            </a>

        </div>

        <?php if (!empty($jobdesk)): ?>

            <div class="jobdesk-list">

                <?php foreach ($jobdesk as $tugas): ?>

                    <div class="jobdesk-card">

                        <div class="jobdesk-info">

                            <h3>
                                <?= e($tugas["nama_tugas"]) ?>
                            </h3>

                            <p>
                                <?= nl2br(e($tugas["deskripsi"] ?? "")) ?>
                            </p>

                            <div class="jobdesk-meta">

                                <span>
                                    👤 <?= e($tugas["nama_anggota"]) ?>
                                </span>

                                <span>
                                    📅

                                    <?php if (!empty($tugas["deadline"])): ?>

                                        <?= e(date(
                                            "d-m-Y",
                                            strtotime($tugas["deadline"])
                                        )) ?>

                                    <?php else: ?>

                                        Belum ditentukan

                                    <?php endif; ?>

                                </span>

                            </div>

                        </div>

                    <div class="jobdesk-actions">
    <form action="aksi.php?aksi=update_status_jobdesk"
      method="POST">

    <input type="hidden"
           name="csrf_token"
           value="<?= e($_SESSION["csrf_token"]) ?>">

    <input type="hidden"
           name="jobdesk_id"
           value="<?= (int)$tugas["id"] ?>">

    <input type="hidden"
           name="kelompok_id"
           value="<?= (int)$kelompokId ?>">

    <label>Status Pekerjaan</label>

    <select name="status" onchange="this.form.submit()">

        <?php
        $statusSekarang = $tugas["status"] ?? "Belum dikerjakan";

        $pilihanStatus = [
            "Belum dikerjakan",
            "Sedang dikerjakan",
            "Selesai"
        ];
        ?>

        <?php foreach ($pilihanStatus as $status): ?>

            <option value="<?= e($status) ?>"
                <?= $statusSekarang === $status ? "selected" : "" ?>>
                <?= e($status) ?>
            </option>

        <?php endforeach; ?>

    </select>

</form>

    <a href="edit_jobdesk.php?id=<?= (int)$tugas["id"] ?>" class="edit-button">
        Edit
    </a>

    <form action="aksi.php?aksi=hapus_jobdesk" method="POST"
          onsubmit="return confirm('Yakin ingin menghapus jobdesk ini?');">
        <input type="hidden" name="csrf_token"
               value="<?= e($_SESSION["csrf_token"]) ?>">
        <input type="hidden" name="jobdesk_id"
               value="<?= (int)$tugas["id"] ?>">
        <input type="hidden" name="kelompok_id"
               value="<?= (int)$kelompok["id"] ?>">

        <button type="submit" class="delete-button">
            Hapus
        </button>
    </form>
</div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <div class="empty-icon">📋</div>

                <h3>Belum Ada Jobdesk</h3>

                <p>
                    Mulai bagikan pekerjaan kepada anggota kelompok.
                </p>

            </div>

        <?php endif; ?>

    </div>

</main>

</body>
</html>
