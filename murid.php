
<?php
require_once "config/auth.php";
require_once "config/database.php";

wajibRole("murid");

$nama = $_SESSION["nama"] ?? "Murid";

// ======================================
// 1. MENGAMBIL KELOMPOK DAN STATISTIKNYA
// ======================================

$stmt = $pdo->prepare(
    "SELECT
        k.*,

        COUNT(j.id) AS total_tugas,

        COALESCE(SUM(
            CASE
                WHEN j.status = 'Selesai' THEN 1
                ELSE 0
            END
        ), 0) AS tugas_selesai,

        COALESCE(SUM(
            CASE
                WHEN j.status = 'Sedang dikerjakan' THEN 1
                ELSE 0
            END
        ), 0) AS tugas_proses,

        COALESCE(SUM(
            CASE
                WHEN j.status = 'Belum dikerjakan'
                     OR j.status IS NULL THEN 1
                ELSE 0
            END
        ), 0) AS tugas_belum

    FROM kelompok k

    LEFT JOIN jobdesk j
        ON j.kelompok_id = k.id

    WHERE k.created_by = ?

    GROUP BY k.id

    ORDER BY k.id DESC"
);

$stmt->execute([$_SESSION["user_id"]]);

$kelompok = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ======================================
// 2. MENGHITUNG TOTAL KESELURUHAN
// ======================================

$jumlahKelompok = count($kelompok);

$jumlahTugas = 0;
$tugasSelesai = 0;
$tugasProses = 0;
$tugasBelum = 0;

foreach ($kelompok as $item) {
    $jumlahTugas += (int) $item["total_tugas"];
    $tugasSelesai += (int) $item["tugas_selesai"];
    $tugasProses += (int) $item["tugas_proses"];
    $tugasBelum += (int) $item["tugas_belum"];
}

$persentaseProgres = 0;

if ($jumlahTugas > 0) {
    $persentaseProgres = round(
        ($tugasSelesai / $jumlahTugas) * 100
    );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Murid - GroupTask</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="dashboard-body">

<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <h2 class="brand">
            GroupTask<span>.</span>
        </h2>

        <p class="sidebar-label">MENU UTAMA</p>

        <a href="murid.php" class="sidebar-link active">
            🏠 Dashboard
        </a>

        <a href="#daftar-kelompok" class="sidebar-link">
            👥 Kelompok Saya
        </a>

        <a href="#daftar-kelompok" class="sidebar-link">
            📋 Jobdesk
        </a>

        <div class="sidebar-bottom">
            <a href="logout.php" class="sidebar-link logout-link">
                ↪ Keluar
            </a>
        </div>

    </aside>

    <!-- KONTEN UTAMA -->
    <main class="dashboard-main">

        <!-- HEADER -->
        <header class="dashboard-header">

            <div>

                <p class="eyebrow">
                    DASHBOARD MURID
                </p>

                <h1>
                    Halo, <?= htmlspecialchars($nama) ?>! 👋
                </h1>

                <p class="welcome-text">
                    Selamat datang kembali di GroupTask.
                    Yuk, pantau perkembangan tugas kelompokmu!
                </p>

            </div>

            <div class="profile-avatar">
                <?= htmlspecialchars(strtoupper(substr($nama, 0, 1))) ?>
            </div>

        </header>

        <!-- STATISTIK KESELURUHAN -->
        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon purple">👥</div>

                <p>Total Kelompok</p>

                <h2><?= $jumlahKelompok ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-icon blue">📋</div>

                <p>Total Tugas</p>

                <h2><?= $jumlahTugas ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-icon orange">✅</div>

                <p>Tugas Selesai</p>

                <h2><?= $tugasSelesai ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-icon purple">🚀</div>

                <p>Progres Keseluruhan</p>

                <h2><?= $persentaseProgres ?>%</h2>

            </div>

        </section>

        <!-- RINGKASAN KESELURUHAN -->
        <section class="group-section">

            <div class="section-heading">

                <div>
                    <h2>Ringkasan Semua Tugas</h2>

                    <p>
                        Statistik dari seluruh kelompokmu.
                    </p>
                </div>

            </div>

            <div class="stat-card">

                <h2><?= $persentaseProgres ?>%</h2>

                <div style="
                    width: 100%;
                    height: 12px;
                    background: #e5e7eb;
                    border-radius: 20px;
                    overflow: hidden;
                    margin: 12px 0;
                ">

                    <div style="
                        width: <?= $persentaseProgres ?>%;
                        height: 100%;
                        background: #7c3aed;
                        border-radius: 20px;
                    "></div>

                </div>

                <p>
                    <?= $tugasSelesai ?> dari <?= $jumlahTugas ?>
                    tugas telah selesai.
                </p>

                <p>🟢 Selesai: <?= $tugasSelesai ?></p>
                <p>🟡 Sedang dikerjakan: <?= $tugasProses ?></p>
                <p>⚪ Belum dikerjakan: <?= $tugasBelum ?></p>

            </div>

        </section>

        <!-- DAFTAR KELOMPOK -->
        <section class="group-section" id="daftar-kelompok">

            <div class="section-heading">

                <div>
                    <h2>Kelompok Saya</h2>

                    <p>
                        Lihat statistik dan perkembangan setiap kelompok.
                    </p>
                </div>

                <a href="buat_kelompok.php" class="primary-button">
                    + Buat Kelompok
                </a>

            </div>

            <?php if (count($kelompok) > 0): ?>

                <div class="group-grid">

                    <?php foreach ($kelompok as $item): ?>

                        <?php
                        $total = (int) $item["total_tugas"];
                        $selesai = (int) $item["tugas_selesai"];
                        $proses = (int) $item["tugas_proses"];
                        $belum = (int) $item["tugas_belum"];

                        $progres = 0;

                        if ($total > 0) {
                            $progres = round(
                                ($selesai / $total) * 100
                            );
                        }
                        ?>

                        <div class="group-card">

                            <div class="group-icon">👥</div>

                            <h3>
                                <?= htmlspecialchars($item["nama_kelompok"]) ?>
                            </h3>

                            <p>Statistik tugas kelompok</p>

                            <div style="
                                margin-top: 18px;
                                text-align: left;
                            ">

                                <div style="
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                ">

                                    <strong>Progres</strong>

                                    <strong>
                                        <?= $progres ?>%
                                    </strong>

                                </div>

                                <div style="
                                    width: 100%;
                                    height: 10px;
                                    background: #e5e7eb;
                                    border-radius: 20px;
                                    overflow: hidden;
                                    margin: 10px 0 15px;
                                ">

                                    <div style="
                                        width: <?= $progres ?>%;
                                        height: 100%;
                                        background: #7c3aed;
                                        border-radius: 20px;
                                    "></div>

                                </div>

                                <p>
                                    📋 Total tugas: <?= $total ?>
                                </p>

                                <p>
                                    🟢 Selesai: <?= $selesai ?>
                                </p>

                                <p>
                                    🟡 Sedang dikerjakan: <?= $proses ?>
                                </p>

                                <p>
                                    ⚪ Belum dikerjakan: <?= $belum ?>
                                </p>

                            </div>

                            <a
                                href="detail_kelompok.php?id=<?= (int) $item["id"] ?>"
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
                        Kamu belum membuat kelompok.
                        Mulai dengan membuat kelompok pertamamu!
                    </p>

                    <a href="buat_kelompok.php" class="primary-button">
                        + Buat Kelompok Pertama
                    </a>

                </div>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>
