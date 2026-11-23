
<?php

require_once "config/auth.php";
require_once "config/database.php";

wajibRole("guru");

function eDetailGuru($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Validasi ID kelompok
$kelompokId = filter_var(
    $_GET["id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if (!$kelompokId) {
    header("Location: guru.php");
    exit;
}

// Ambil kelompok
$stmt = $pdo->prepare(
    "SELECT id, nama_kelompok
     FROM kelompok
     WHERE id = ?"
);

$stmt->execute([$kelompokId]);

$kelompok = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kelompok) {
    http_response_code(404);
    exit("Kelompok tidak ditemukan.");
}

// Ambil anggota
$stmt = $pdo->prepare(
    "SELECT id, nama_anggota
     FROM anggota
     WHERE kelompok_id = ?
     ORDER BY id ASC"
);

$stmt->execute([$kelompokId]);

$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil jobdesk
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

// Statistik kelompok
$jumlahTugas = count($jobdesk);

$selesai = 0;
$proses = 0;
$belum = 0;

foreach ($jobdesk as $tugas) {

    if ($tugas["status"] === "Selesai") {
        $selesai++;
    } elseif ($tugas["status"] === "Sedang dikerjakan") {
        $proses++;
    } else {
        $belum++;
    }
}

$progres = $jumlahTugas > 0
    ? round(($selesai / $jumlahTugas) * 100)
    : 0;

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pemantauan Kelompok - GroupTask</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body class="dashboard-body">

<main class="dashboard-main">

    <div class="group-form-card teacher-detail-card">

        <a href="guru.php" class="detail-link">
            ← Kembali ke Dashboard Guru
        </a>

        <p class="eyebrow">
            PEMANTAUAN KELOMPOK
        </p>

        <h1>
            <?= eDetailGuru($kelompok["nama_kelompok"]) ?>
        </h1>

        <p class="welcome-text">
            Berikut adalah informasi anggota dan perkembangan jobdesk kelompok ini.
        </p>

        <div class="teacher-detail-stats">

            <div>
                <span>Anggota</span>
                <strong><?= count($anggota) ?></strong>
            </div>

            <div>
                <span>Total Jobdesk</span>
                <strong><?= $jumlahTugas ?></strong>
            </div>

            <div>
                <span>Selesai</span>
                <strong><?= $selesai ?></strong>
            </div>

        </div>

        <div class="teacher-progress-card">

            <div class="teacher-progress-info">
                <span>Progres kelompok</span>
                <strong><?= $progres ?>%</strong>
            </div>

            <div class="teacher-progress-track">
                <div
                    class="teacher-progress-fill"
                    style="width: <?= $progres ?>%;"
                ></div>
            </div>

            <p>
                <?= $selesai ?> selesai,
                <?= $proses ?> sedang dikerjakan,
                <?= $belum ?> belum dikerjakan.
            </p>

        </div>

        <hr>

        <!-- DAFTAR ANGGOTA -->

        <h2>Daftar Anggota</h2>

        <?php if (!empty($anggota)): ?>

            <div class="member-list">

                <?php foreach ($anggota as $index => $orang): ?>

                    <div class="member-item">

                        <div class="member-avatar">
                            <?= $index + 1 ?>
                        </div>

                        <span class="member-name">
                            <?= eDetailGuru($orang["nama_anggota"]) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>Belum Ada Anggota</h3>
            </div>

        <?php endif; ?>

        <hr>

        <!-- DAFTAR JOBDESK -->

        <h2>Daftar Jobdesk</h2>

        <p class="welcome-text">
            Guru dapat melihat pembagian dan status pekerjaan anggota.
        </p>

        <?php if (!empty($jobdesk)): ?>

            <div class="teacher-jobdesk-list">

                <?php foreach ($jobdesk as $tugas): ?>

                    <div class="teacher-jobdesk-card">

                        <div>

                            <h3>
                                <?= eDetailGuru($tugas["nama_tugas"]) ?>
                            </h3>

                            <p>
                                <?= nl2br(eDetailGuru($tugas["deskripsi"] ?? "")) ?>
                            </p>

                            <div class="jobdesk-meta">

                                <span>
                                    👤 <?= eDetailGuru($tugas["nama_anggota"]) ?>
                                </span>

                                <span>
                                    📅

                                    <?php if (!empty($tugas["deadline"])): ?>

                                        <?= eDetailGuru(
                                            date(
                                                "d-m-Y",
                                                strtotime($tugas["deadline"])
                                            )
                                        ) ?>

                                    <?php else: ?>

                                        Belum ditentukan

                                    <?php endif; ?>

                                </span>

                            </div>

                        </div>

                        <span class="teacher-status">

                            <?= eDetailGuru(
                                $tugas["status"] ?? "Belum dikerjakan"
                            ) ?>

                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <div class="empty-icon">📋</div>

                <h3>Belum Ada Jobdesk</h3>

                <p>
                    Kelompok ini belum memiliki pembagian pekerjaan.
                </p>

            </div>

        <?php endif; ?>

        <div class="teacher-readonly-note">
            🔒 Halaman ini hanya untuk melihat data.
            Seluruh perubahan data dilakukan melalui akun murid.
        </div>

    </div>

</main>

</body>
</html>
