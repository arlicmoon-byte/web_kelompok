
<?php

require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/database.php";

wajibRole("murid");

// TOKEN CSRF
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// FUNGSI ESCAPE
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// AMBIL ID JOBDESK
$jobdeskId = filter_var(
    $_GET["id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if (!$jobdeskId) {
    header("Location: murid.php");
    exit;
}

// AMBIL DATA JOBDESK DAN PERIKSA KEPEMILIKAN
$stmt = $pdo->prepare(
    "SELECT
        j.id,
        j.kelompok_id,
        j.anggota_id,
        j.nama_tugas,
        j.deskripsi,
        j.deadline,
        j.status,
        k.nama_kelompok
     FROM jobdesk j
     INNER JOIN kelompok k ON j.kelompok_id = k.id
     WHERE j.id = ? AND k.created_by = ?"
);

$stmt->execute([
    $jobdeskId,
    $_SESSION["user_id"]
]);

$jobdesk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jobdesk) {
    http_response_code(404);
    exit("Jobdesk tidak ditemukan atau tidak memiliki akses.");
}

$kelompokId = (int)$jobdesk["kelompok_id"];

// AMBIL ANGGOTA KELOMPOK
$stmt = $pdo->prepare(
    "SELECT id, nama_anggota
     FROM anggota
     WHERE kelompok_id = ?
     ORDER BY id ASC"
);

$stmt->execute([$kelompokId]);

$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);

// NOTIFIKASI
$pesanError = $_SESSION["pesan_error"] ?? "";
unset($_SESSION["pesan_error"]);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jobdesk - GroupTask</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="dashboard-body">

<main class="dashboard-main">

    <div class="group-form-card">

        <a
            href="detail_kelompok.php?id=<?= $kelompokId ?>"
            class="detail-link"
        >
            ← Kembali ke Detail Kelompok
        </a>

        <h1>Edit Jobdesk</h1>

        <p class="welcome-text">
            Kelompok: <?= e($jobdesk["nama_kelompok"]) ?>
        </p>

        <?php if ($pesanError !== ""): ?>
            <p class="form-notice" role="alert">
                <?= e($pesanError) ?>
            </p>
        <?php endif; ?>

        <form action="aksi.php?aksi=edit_jobdesk" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION["csrf_token"]) ?>"
            >

            <input
                type="hidden"
                name="jobdesk_id"
                value="<?= (int)$jobdesk["id"] ?>"
            >

            <input
                type="hidden"
                name="kelompok_id"
                value="<?= $kelompokId ?>"
            >

            <label for="nama_tugas">Nama Jobdesk</label>

            <input
                type="text"
                id="nama_tugas"
                name="nama_tugas"
                maxlength="150"
                value="<?= e($jobdesk["nama_tugas"]) ?>"
                required
            >

            <label for="anggota_id">Penanggung Jawab</label>

            <select id="anggota_id" name="anggota_id" required>

                <?php foreach ($anggota as $orang): ?>

                    <option
                        value="<?= (int)$orang["id"] ?>"
                        <?= (int)$orang["id"] === (int)$jobdesk["anggota_id"] ? "selected" : "" ?>
                    >
                        <?= e($orang["nama_anggota"]) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <label for="deskripsi">Deskripsi</label>

            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="5"
            ><?= e($jobdesk["deskripsi"] ?? "") ?></textarea>

            <label for="deadline">Deadline</label>

            <input
                type="date"
                id="deadline"
                name="deadline"
                value="<?= e($jobdesk["deadline"] ?? "") ?>"
            >

            <button type="submit" class="primary-button">
                Simpan Perubahan
            </button>

        </form>

    </div>

</main>

</body>
</html>
