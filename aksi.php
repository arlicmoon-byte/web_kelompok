
<?php

require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/database.php";

wajibRole("murid");

$aksi = $_GET["aksi"] ?? "";

// Semua aksi harus menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: murid.php");
    exit;
}

// ======================================
// FUNGSI REDIRECT DAN NOTIFIKASI
// ======================================

function redirectKe($url)
{
    header("Location: " . $url);
    exit;
}

function pesanError($pesan, $url)
{
    $_SESSION["pesan_error"] = $pesan;
    redirectKe($url);
}

function pesanBerhasil($pesan, $url)
{
    $_SESSION["pesan"] = $pesan;
    redirectKe($url);
}

// ======================================
// VALIDASI CSRF
// ======================================

function validasiCsrf()
{
    $token = $_POST["csrf_token"] ?? "";
    $sessionToken = $_SESSION["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !is_string($sessionToken) ||
        $token === "" ||
        $sessionToken === "" ||
        !hash_equals($sessionToken, $token)
    ) {
        http_response_code(403);
        exit("Permintaan tidak valid. Silakan muat ulang halaman.");
    }
}

validasiCsrf();

// ======================================
// 1. SIMPAN KELOMPOK
// ======================================

if ($aksi === "simpan_kelompok") {

    $namaKelompok = trim($_POST["nama_kelompok"] ?? "");

    if ($namaKelompok === "" || mb_strlen($namaKelompok) > 100) {
        pesanError(
            "Nama kelompok wajib diisi dan maksimal 100 karakter.",
            "buat_kelompok.php"
        );
    }

    try {

        $stmt = $pdo->prepare(
            "INSERT INTO kelompok (nama_kelompok, created_by)
             VALUES (?, ?)"
        );

        $stmt->execute([
            $namaKelompok,
            $_SESSION["user_id"]
        ]);

        pesanBerhasil("Kelompok berhasil dibuat!", "murid.php");

    } catch (PDOException $e) {

        error_log($e->getMessage());

        pesanError(
            "Gagal membuat kelompok.",
            "buat_kelompok.php"
        );
    }
}

// ======================================
// 2. TAMBAH ANGGOTA
// ======================================

if ($aksi === "tambah_anggota") {

    $kelompokId = filter_var(
        $_POST["kelompok_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $nama = trim($_POST["nama_anggota"] ?? "");

    if (!$kelompokId) {
        exit("ID kelompok tidak valid.");
    }

    if ($nama === "" || mb_strlen($nama) > 100) {
        pesanError(
            "Nama anggota wajib diisi dan maksimal 100 karakter.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );
    }

    try {

        $cek = $pdo->prepare(
            "SELECT id FROM kelompok
             WHERE id = ? AND created_by = ?"
        );

        $cek->execute([
            $kelompokId,
            $_SESSION["user_id"]
        ]);

        if (!$cek->fetchColumn()) {
            http_response_code(403);
            exit("Tidak memiliki akses ke kelompok ini.");
        }

        $stmt = $pdo->prepare(
            "INSERT INTO anggota (kelompok_id, nama_anggota)
             VALUES (?, ?)"
        );

        $stmt->execute([$kelompokId, $nama]);

        pesanBerhasil(
            "Anggota berhasil ditambahkan!",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );

    } catch (PDOException $e) {

        error_log($e->getMessage());

        pesanError(
            "Gagal menambahkan anggota.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );
    }
}

// ======================================
// 3. EDIT ANGGOTA
// ======================================

if ($aksi === "edit_anggota") {

    $anggotaId = filter_var(
        $_POST["anggota_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $kelompokId = filter_var(
        $_POST["kelompok_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $nama = trim($_POST["nama_anggota"] ?? "");

    if (!$anggotaId || !$kelompokId) {
        exit("ID tidak valid.");
    }

    if ($nama === "" || mb_strlen($nama) > 100) {
        pesanError(
            "Nama anggota wajib diisi dan maksimal 100 karakter.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );
    }

    try {

        $stmt = $pdo->prepare(
            "UPDATE anggota a
             INNER JOIN kelompok k ON a.kelompok_id = k.id
             SET a.nama_anggota = ?
             WHERE a.id = ?
             AND a.kelompok_id = ?
             AND k.created_by = ?"
        );

        $stmt->execute([
            $nama,
            $anggotaId,
            $kelompokId,
            $_SESSION["user_id"]
        ]);

        $cek = $pdo->prepare(
            "SELECT a.id
             FROM anggota a
             INNER JOIN kelompok k ON a.kelompok_id = k.id
             WHERE a.id = ?
             AND a.kelompok_id = ?
             AND k.created_by = ?"
        );

        $cek->execute([
            $anggotaId,
            $kelompokId,
            $_SESSION["user_id"]
        ]);

        if (!$cek->fetchColumn()) {
            pesanError(
                "Anggota tidak ditemukan.",
                "detail_kelompok.php?id=" . (int)$kelompokId
            );
        }

        pesanBerhasil(
            "Nama anggota berhasil diperbarui!",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );

    } catch (PDOException $e) {

        error_log($e->getMessage());

        pesanError(
            "Gagal memperbarui anggota.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );
    }
}

// ======================================
// 4. HAPUS ANGGOTA
// ======================================

if ($aksi === "hapus_anggota") {

    $anggotaId = filter_var(
        $_POST["anggota_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $kelompokId = filter_var(
        $_POST["kelompok_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if (!$anggotaId || !$kelompokId) {
        exit("ID tidak valid.");
    }

    try {

        $stmt = $pdo->prepare(
            "DELETE a FROM anggota a
             INNER JOIN kelompok k ON a.kelompok_id = k.id
             WHERE a.id = ?
             AND a.kelompok_id = ?
             AND k.created_by = ?"
        );

        $stmt->execute([
            $anggotaId,
            $kelompokId,
            $_SESSION["user_id"]
        ]);

        if ($stmt->rowCount() > 0) {
            pesanBerhasil(
                "Anggota berhasil dihapus!",
                "detail_kelompok.php?id=" . (int)$kelompokId
            );
        }

        pesanError(
            "Anggota tidak ditemukan atau tidak dapat dihapus.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );

    } catch (PDOException $e) {

        error_log($e->getMessage());

        pesanError(
            "Gagal menghapus anggota. Periksa relasi jobdesk.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );
    }
}

// ======================================
// 5. TAMBAH JOBDESK
// ======================================

if ($aksi === "tambah_jobdesk") {

    $kelompokId = filter_var(
        $_POST["kelompok_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $anggotaId = filter_var(
        $_POST["anggota_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $namaTugas = trim(
        $_POST["nama_tugas"] ??
        $_POST["judul_tugas"] ??
        ""
    );

    $deskripsi = trim($_POST["deskripsi"] ?? "");
    $deadline = trim($_POST["deadline"] ?? "");

    if (!$kelompokId || !$anggotaId) {
        exit("Kelompok atau anggota tidak valid.");
    }

    if ($namaTugas === "" || mb_strlen($namaTugas) > 150) {
        pesanError(
            "Nama tugas wajib diisi dan maksimal 150 karakter.",
            "buat_jobdesk.php?id=" . (int)$kelompokId
        );
    }

    if ($deadline !== "") {

        $tanggal = DateTime::createFromFormat("!Y-m-d", $deadline);

        if (
            !$tanggal ||
            $tanggal->format("Y-m-d") !== $deadline ||
            $deadline < date("Y-m-d")
        ) {
            pesanError(
                "Tanggal deadline tidak valid.",
                "buat_jobdesk.php?id=" . (int)$kelompokId
            );
        }

    } else {
        $deadline = null;
    }

    try {

        $cekKelompok = $pdo->prepare(
            "SELECT id FROM kelompok
             WHERE id = ? AND created_by = ?"
        );

        $cekKelompok->execute([
            $kelompokId,
            $_SESSION["user_id"]
        ]);

        if (!$cekKelompok->fetchColumn()) {
            http_response_code(403);
            exit("Tidak memiliki akses ke kelompok ini.");
        }

        $cekAnggota = $pdo->prepare(
            "SELECT id FROM anggota
             WHERE id = ? AND kelompok_id = ?"
        );

        $cekAnggota->execute([
            $anggotaId,
            $kelompokId
        ]);

        if (!$cekAnggota->fetchColumn()) {
            exit("Anggota tidak ditemukan.");
        }

        $stmt = $pdo->prepare(
            "INSERT INTO jobdesk
            (kelompok_id, anggota_id, nama_tugas, deskripsi, deadline)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $kelompokId,
            $anggotaId,
            $namaTugas,
            $deskripsi,
            $deadline
        ]);

        pesanBerhasil(
            "Jobdesk berhasil ditambahkan!",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );

    } catch (PDOException $e) {

        error_log($e->getMessage());

        pesanError(
            "Gagal menyimpan jobdesk. Periksa struktur tabel database.",
            "buat_jobdesk.php?id=" . (int)$kelompokId
        );
    }
}

// ======================================
// 6. EDIT JOBDESK
// ======================================

if ($aksi === "edit_jobdesk") {

    $jobdeskId = filter_var(
        $_POST["jobdesk_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $kelompokId = filter_var(
        $_POST["kelompok_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $anggotaId = filter_var(
        $_POST["anggota_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $namaTugas = trim($_POST["nama_tugas"] ?? "");
    $deskripsi = trim($_POST["deskripsi"] ?? "");
    $deadline = trim($_POST["deadline"] ?? "");

    if (!$jobdeskId || !$kelompokId || !$anggotaId) {
        exit("Data jobdesk tidak valid.");
    }

    $urlKembali = "edit_jobdesk.php?id=" . (int)$jobdeskId;

    if ($namaTugas === "" || mb_strlen($namaTugas) > 150) {
        pesanError(
            "Nama tugas wajib diisi dan maksimal 150 karakter.",
            $urlKembali
        );
    }

    if ($deadline !== "") {

        $tanggal = DateTime::createFromFormat("!Y-m-d", $deadline);

        if (
            !$tanggal ||
            $tanggal->format("Y-m-d") !== $deadline
        ) {
            pesanError(
                "Tanggal deadline tidak valid.",
                $urlKembali
            );
        }

    } else {
        $deadline = null;
    }

    try {

        // PERIKSA KEPEMILIKAN JOBDESK
        $cekJobdesk = $pdo->prepare(
            "SELECT j.id
             FROM jobdesk j
             INNER JOIN kelompok k ON j.kelompok_id = k.id
             WHERE j.id = ?
             AND j.kelompok_id = ?
             AND k.created_by = ?"
        );

        $cekJobdesk->execute([
            $jobdeskId,
            $kelompokId,
            $_SESSION["user_id"]
        ]);

        if (!$cekJobdesk->fetchColumn()) {
            http_response_code(403);
            exit("Jobdesk tidak ditemukan atau tidak memiliki akses.");
        }

        // PERIKSA ANGGOTA
        $cekAnggota = $pdo->prepare(
            "SELECT id
             FROM anggota
             WHERE id = ? AND kelompok_id = ?"
        );

        $cekAnggota->execute([
            $anggotaId,
            $kelompokId
        ]);

        if (!$cekAnggota->fetchColumn()) {
            pesanError(
                "Anggota tidak ditemukan dalam kelompok ini.",
                $urlKembali
            );
        }

        // PERBARUI JOBDESK
        $stmt = $pdo->prepare(
            "UPDATE jobdesk
             SET anggota_id = ?,
                 nama_tugas = ?,
                 deskripsi = ?,
                 deadline = ?
             WHERE id = ? AND kelompok_id = ?"
        );

        $stmt->execute([
            $anggotaId,
            $namaTugas,
            $deskripsi,
            $deadline,
            $jobdeskId,
            $kelompokId
        ]);

        pesanBerhasil(
            "Jobdesk berhasil diperbarui!",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );

    } catch (PDOException $e) {

        error_log($e->getMessage());

        pesanError(
            "Gagal memperbarui jobdesk.",
            $urlKembali
        );
    }
}

if ($aksi === "hapus_jobdesk") {
    $jobdeskId = filter_var(
        $_POST["jobdesk_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $kelompokId = filter_var(
        $_POST["kelompok_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if (!$jobdeskId || !$kelompokId) {
        pesanError("Data jobdesk tidak valid.");
        header("Location: murid.php");
        exit;
    }

    // Pastikan kelompok dimiliki oleh pengguna yang sedang login
    $stmt = $pdo->prepare(
        "SELECT j.id
         FROM jobdesk j
         INNER JOIN kelompok k ON j.kelompok_id = k.id
         WHERE j.id = ?
           AND j.kelompok_id = ?
           AND k.created_by = ?"
    );

    $stmt->execute([
        $jobdeskId,
        $kelompokId,
        $_SESSION["user_id"]
    ]);

    $jobdesk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jobdesk) {
        pesanError("Jobdesk tidak ditemukan atau kamu tidak memiliki akses.");
        header("Location: murid.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            "DELETE FROM jobdesk
             WHERE id = ? AND kelompok_id = ?"
        );

        $stmt->execute([$jobdeskId, $kelompokId]);

        pesanBerhasil(
    "Jobdesk berhasil dihapus!",
    "detail_kelompok.php?id=" . (int)$kelompokId
);
        header("Location: detail_kelompok.php?id=" . $kelompokId);
        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());

        pesanError("Jobdesk gagal dihapus.","detail_kelompok.php?id=" . (int)$kelompokId
);
        header("Location: detail_kelompok.php?id=" . $kelompokId);
        exit;
    }
}
// ======================================
// 7. UPDATE STATUS JOBDESK
// ======================================

if ($aksi === "update_status_jobdesk") {

    $jobdeskId = filter_var(
        $_POST["jobdesk_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $kelompokId = filter_var(
        $_POST["kelompok_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $status = $_POST["status"] ?? "";

    $daftarStatus = [
        "Belum dikerjakan",
        "Sedang dikerjakan",
        "Selesai"
    ];

    if (!$jobdeskId || !$kelompokId) {
        exit("ID jobdesk tidak valid.");
    }

    if (!in_array($status, $daftarStatus, true)) {
        pesanError(
            "Status tidak valid.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );
    }

    try {

        $stmt = $pdo->prepare(
            "UPDATE jobdesk j
             INNER JOIN kelompok k
             ON j.kelompok_id = k.id
             SET j.status = ?
             WHERE j.id = ?
             AND j.kelompok_id = ?
             AND k.created_by = ?"
        );

        $stmt->execute([
            $status,
            $jobdeskId,
            $kelompokId,
            $_SESSION["user_id"]
        ]);

        if ($stmt->rowCount() > 0) {
            pesanBerhasil(
                "Status jobdesk berhasil diperbarui!",
                "detail_kelompok.php?id=" . (int)$kelompokId
            );
        }

        pesanError(
            "Jobdesk tidak ditemukan atau status tidak berubah.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );

    } catch (PDOException $e) {

        error_log($e->getMessage());

        pesanError(
            "Gagal memperbarui status jobdesk.",
            "detail_kelompok.php?id=" . (int)$kelompokId
        );
    }
}
// ======================================
// AKSI TIDAK DIKENAL
// ======================================

http_response_code(404);
exit("Aksi tidak ditemukan.");

