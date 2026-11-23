<?php

require_once "config/auth.php";
require_once "config/database.php";

wajibRole("guru");

$nama = $_SESSION["nama"] ?? "Guru";

// =====================================
// MENGAMBIL STATISTIK SETIAP KELOMPOK
// =====================================

$stmt = $pdo->query(
    "SELECT
        k.id,
        k.nama_kelompok,

        (SELECT COUNT(*)
         FROM anggota a
         WHERE a.kelompok_id = k.id) AS jumlah_anggota,

        (SELECT COUNT(*)
         FROM jobdesk j
         WHERE j.kelompok_id = k.id) AS jumlah_tugas,

        (SELECT COUNT(*)
         FROM jobdesk j
         WHERE j.kelompok_id = k.id
         AND j.status = 'Belum dikerjakan') AS tugas_belum,

        (SELECT COUNT(*)
         FROM jobdesk j
         WHERE j.kelompok_id = k.id
         AND j.status = 'Sedang dikerjakan') AS tugas_proses,

        (SELECT COUNT(*)
         FROM jobdesk j
         WHERE j.kelompok_id = k.id
         AND j.status = 'Selesai') AS tugas_selesai

     FROM kelompok k
     ORDER BY k.id DESC"
);

$kelompok = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================
// STATISTIK KESELURUHAN
// =====================================

$totalKelompok = count($kelompok);
$totalAnggota = 0;
$totalTugas = 0;
$totalBelum = 0;
$totalProses = 0;
$totalSelesai = 0;

foreach ($kelompok as $item) {
    $totalAnggota += (int)$item["jumlah_anggota"];
    $totalTugas += (int)$item["jumlah_tugas"];
    $totalBelum += (int)$item["tugas_belum"];
    $totalProses += (int)$item["tugas_proses"];
    $totalSelesai += (int)$item["tugas_selesai"];
}

$persentaseKeseluruhan = $totalTugas > 0
    ? round(($totalSelesai / $totalTugas) * 100)
    : 0;

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Guru - GroupTask</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="dashboard-body">

<div class="dashboard-layout">

    <!-- SIDEBAR GURU -->

    <aside class="sidebar teacher-sidebar">

        <h2 class="brand">GroupTask<span>.</span></h2>

        <p class="sidebar-label">MENU GURU</p>

        <a href="guru.php" class="sidebar-link active">
            🏠 Dashboard
        </a>

        <a href="#daftar-kelompok" class="sidebar-link">
            👥 Semua Kelompok
        </a>

        <a href="#statistik" class="sidebar-link">
            📊 Statistik
        </a>

        <div class="sidebar-bottom">

            <a href="logout.php" class="sidebar-link logout-link">
                ↪ Keluar
            </a>

        </div>

    </aside>

    <!-- KONTEN UTAMA -->

    <main class="dashboard-main teacher-main">

        <!-- HEADER -->

        <header class="dashboard-header">

            <div>

                <p class="eyebrow">DASHBOARD GURU</p>

                <h1>
                    Halo, <?= htmlspecialchars(
                        $nama,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>! 👋
                </h1>

                <p class="welcome-text">
                    Pantau perkembangan seluruh kelompok murid
                    melalui GroupTask.
                </p>

            </div>

            <div class="profile-avatar">
                <?= htmlspecialchars(
                    strtoupper(substr($nama, 0, 1)),
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </div>

        </header>

        <!-- STATISTIK UTAMA -->

        <section class="stats-grid" id="statistik">

            <div class="stat-card">

                <div class="stat-icon purple">👥</div>

                <p>Total Kelompok</p>

                <h2><?= $totalKelompok ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-icon blue">🧑‍🎓</div>

                <p>Total Anggota</p>

                <h2><?= $totalAnggota ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-icon orange">📋</div>

                <p>Total Jobdesk</p>

                <h2><?= $totalTugas ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-icon purple">✅</div>

                <p>Jobdesk Selesai</p>

                <h2><?= $totalSelesai ?></h2>

            </div>

        </section>

        <!-- RINGKASAN PROGRES -->

        <section class="group-section teacher-overview-section">

            <div class="stat-card guru-overview">

                <h2>Ringkasan Progres Seluruh Kelompok</h2>

                <p class="welcome-text">
                    Persentase penyelesaian seluruh jobdesk murid.
                </p>

                <div class="guru-overview-number">
                    <?= $persentaseKeseluruhan ?>%
                </div>

                <div class="guru-progress-track">

                    <div
                        class="guru-progress-fill"
                        style="width: <?= $persentaseKeseluruhan ?>%"
                    ></div>

                </div>

                <div class="guru-status-summary">

                    <span>
                        🟠 Belum dikerjakan: <?= $totalBelum ?>
                    </span>

                    <span>
                        🔵 Sedang dikerjakan: <?= $totalProses ?>
                    </span>

                    <span>
                        🟢 Selesai: <?= $totalSelesai ?>
                    </span>

                </div>

            </div>

        </section>

        <!-- DAFTAR KELOMPOK -->

        <section class="group-section" id="daftar-kelompok">

            <div class="section-heading">

                <div>

                    <h2>Seluruh Kelompok</h2>

                    <p>
                        Pantau statistik dan perkembangan setiap kelompok.
                    </p>

                </div>

            </div>

            <?php if (!empty($kelompok)): ?>

                <div class="group-grid">

                    <?php foreach ($kelompok as $item): ?>

                        <?php

                        $jumlahTugas = (int)$item["jumlah_tugas"];
                        $jumlahSelesai = (int)$item["tugas_selesai"];

                        $progress = $jumlahTugas > 0
                            ? round(($jumlahSelesai / $jumlahTugas) * 100)
                            : 0;

                        ?>

                        <div class="group-card guru-group-card">

                            <div class="group-icon">👥</div>

                            <h3>
                                <?= htmlspecialchars(
                                    $item["nama_kelompok"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </h3>

                            <!-- INFORMASI KELOMPOK -->

                            <div class="guru-group-info">

                                <span>
                                    👤 <?= (int)$item["jumlah_anggota"] ?>
                                    Anggota
                                </span>

                                <span>
                                    📋 <?= $jumlahTugas ?>
                                    Jobdesk
                                </span>

                            </div>

                            <!-- PROGRES KELOMPOK -->

                            <div class="guru-group-progress">

                                <div class="guru-progress-label">

                                    <span>Progres Kelompok</span>

                                    <strong><?= $progress ?>%</strong>

                                </div>

                                <div class="guru-progress-track">

                                    <div
                                        class="guru-progress-fill"
                                        style="width: <?= $progress ?>%"
                                    ></div>

                                </div>

                            </div>

                            <!-- STATUS JOBDESK -->

                            <div class="guru-mini-status">

                                <span class="mini-belum">
                                    <?= (int)$item["tugas_belum"] ?> Belum
                                </span>

                                <span class="mini-proses">
                                    <?= (int)$item["tugas_proses"] ?> Proses
                                </span>

                                <span class="mini-selesai">
                                    <?= $jumlahSelesai ?> Selesai
                                </span>

                            </div>

                            <!-- LIHAT DETAIL -->

                            <a
                                href="guru_detail_kelompok.php?id=<?= (int)$item["id"] ?>"
                                class="detail-link"
                            >
                                Lihat Detail →
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-icon">📂</div>

                    <h3>Belum Ada Kelompok</h3>

                    <p>
                        Belum ada kelompok yang dibuat oleh murid.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>