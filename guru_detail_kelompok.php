
<?php

require_once "config/auth.php";
require_once "config/database.php";

wajibRole("guru");

// =====================================
// FUNGSI ESCAPE HTML
// =====================================

function eGuru($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

// =====================================
// VALIDASI ID KELOMPOK
// =====================================

$kelompokId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if (!$kelompokId) {
    header("Location: guru.php");
    exit;
}

// =====================================
// AMBIL DATA KELOMPOK
// =====================================

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

// =====================================
// AMBIL DATA ANGGOTA
// =====================================

$stmt = $pdo->prepare(
    "SELECT id, nama_anggota
     FROM anggota
     WHERE kelompok_id = ?
     ORDER BY id ASC"
);

$stmt->execute([$kelompokId]);

$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================
// AMBIL DATA JOBDESK
// =====================================

$stmt = $pdo->prepare(
    "SELECT
        j.id,
        j.nama_tugas,
        j.deskripsi,
        j.deadline,
        j.status,
        a.nama_anggota

     FROM jobdesk j

     INNER JOIN anggota a
     ON j.anggota_id = a.id

     WHERE j.kelompok_id = ?

     ORDER BY j.id DESC"
);

$stmt->execute([$kelompokId]);

$jobdesk = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================
// STATISTIK JOBDESK
// =====================================

$totalTugas = count($jobdesk);
$belumDikerjakan = 0;
$sedangDikerjakan = 0;
$selesai = 0;

foreach ($jobdesk as $tugas) {

    $status = $tugas["status"] ?? "Belum dikerjakan";

    if ($status === "Belum dikerjakan") {
        $belumDikerjakan++;
    } elseif ($status === "Sedang dikerjakan") {
        $sedangDikerjakan++;
    } elseif ($status === "Selesai") {
        $selesai++;
    }
}

$persentaseSelesai = $totalTugas > 0
    ? round(($selesai / $totalTugas) * 100)
    : 0;

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Detail Kelompok - Guru GroupTask</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body class="dashboard-body">

<div class="dashboard-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2 class="brand">GroupTask<span>.</span></h2>

        <p class="sidebar-label">MENU GURU</p>

        <a href="guru.php" class="sidebar-link">
            🏠 Dashboard
        </a>

        <a href="guru.php#daftar-kelompok"
           class="sidebar-link active">
            👥 Semua Kelompok
        </a>

        <a href="guru.php#statistik"
           class="sidebar-link">
            📊 Statistik
        </a>

        <div class="sidebar-bottom">

            <a href="logout.php"
               class="sidebar-link logout-link">
                ↪ Keluar
            </a>

        </div>

    </aside>

    <!-- KONTEN UTAMA -->

    <main class="dashboard-main">

        <header class="dashboard-header">

            <div>

                <a href="guru.php"
                   class="detail-link">
                    ← Kembali ke Dashboard
                </a>

                <p class="eyebrow">
                    MONITORING KELOMPOK
                </p>

                <h1>
                    <?= eGuru($kelompok["nama_kelompok"]) ?>
                </h1>

                <p class="welcome-text">
                    Halaman pemantauan aktivitas dan perkembangan kelompok.
                </p>

            </div>

            <div class="profile-avatar">
                G
            </div>

        </header>

        <!-- STATISTIK KELOMPOK -->

        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon purple">👥</div>

                <p>Total Anggota</p>

                <h2><?= count($anggota) ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-icon blue">📋</div>

                <p>Total Jobdesk</p>

                <h2><?= $totalTugas ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-icon orange">✅</div>

                <p>Tugas Selesai</p>

                <h2><?= $selesai ?></h2>

            </div>

        </section>

        <!-- PROGRES KELOMPOK -->

        <section class="group-section">

            <div class="stat-card">

                <h2>Progres Pekerjaan</h2>

                <p class="welcome-text">
                    Persentase jobdesk yang sudah diselesaikan.
                </p>

                <h2>
                    <?= $persentaseSelesai ?>%
                </h2>

                <div class="guru-progress-track">

                    <div class="guru-progress-fill"
                         style="width: <?= $persentaseSelesai ?>%">
                    </div>

                </div>

                <div class="guru-status-summary">

                    <span>
                        Belum dikerjakan: <?= $belumDikerjakan ?>
                    </span>

                    <span>
                        Sedang dikerjakan: <?= $sedangDikerjakan ?>
                    </span>

                    <span>
                        Selesai: <?= $selesai ?>
                    </span>

                </div>

            </div>

        </section>

        <!-- DAFTAR ANGGOTA -->

        <section class="group-section">

            <div class="section-heading">

                <div>

                    <h2>Daftar Anggota</h2>

                    <p>
                        Seluruh anggota yang tergabung dalam kelompok.
                    </p>

                </div>

            </div>

            <?php if (!empty($anggota)): ?>

                <div class="member-list">

                    <?php foreach ($anggota as $index => $orang): ?>

                        <div class="member-item">

                            <div class="member-avatar">
                                <?= $index + 1 ?>
                            </div>

                            <span class="member-name">
                                <?= eGuru($orang["nama_anggota"]) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-icon">👥</div>

                    <h3>Belum Ada Anggota</h3>

                    <p>
                        Kelompok ini belum memiliki anggota.
                    </p>

                </div>

            <?php endif; ?>

        </section>

        <!-- DAFTAR JOBDESK -->

        <section class="group-section">

            <div class="section-heading">

                <div>

                    <h2>Daftar Jobdesk</h2>

                    <p>
                        Pemantauan pembagian dan status pekerjaan anggota.
                    </p>

                </div>

            </div>

            <?php if (!empty($jobdesk)): ?>

                <div class="guru-jobdesk-list">

                    <?php foreach ($jobdesk as $tugas): ?>

                        <div class="jobdesk-card">

                            <div class="jobdesk-info">

                                <h3>
                                    <?= eGuru($tugas["nama_tugas"]) ?>
                                </h3>

                                <p>
                                    <?= nl2br(eGuru(
                                        $tugas["deskripsi"] ?? ""
                                    )) ?>
                                </p>

                                <div class="jobdesk-meta">

                                    <span>
                                        👤
                                        <?= eGuru($tugas["nama_anggota"]) ?>
                                    </span>

                                    <span>
                                        📅

                                        <?php if (!empty($tugas["deadline"])): ?>

                                            <?= eGuru(date(
                                                "d-m-Y",
                                                strtotime($tugas["deadline"])
                                            )) ?>

                                        <?php else: ?>

                                            Belum ditentukan

                                        <?php endif; ?>

                                    </span>

                                </div>

                            </div>

                            <?php
                            $status = $tugas["status"]
                                ?? "Belum dikerjakan";

                            $classStatus = "status-belum";

                            if ($status === "Sedang dikerjakan") {
                                $classStatus = "status-proses";
                            } elseif ($status === "Selesai") {
                                $classStatus = "status-selesai";
                            }
                            ?>

                            <span class="guru-status <?= $classStatus ?>">
                                <?= eGuru($status) ?>
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

        </section>

    </main>

</div>

</body>
</html>
