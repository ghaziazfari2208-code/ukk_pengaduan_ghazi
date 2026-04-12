<?php
session_start();
include "db/koneksi.php";

// 1. Proteksi Halaman Siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    echo "<script>alert('Akses Ditolak! Silahkan Login.'); location='login.php';</script>";
    exit;
}

$nis_user = $_SESSION['nis'];

// 2. Logika Kirim Aspirasi
if (isset($_POST['kirim_aspirasi'])) {
    $id_kat = $_POST['id_kategori'];
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $ket = mysqli_real_escape_string($koneksi, $_POST['ket']);
    $tgl = date('Y-m-d H:i:s');

    $query = "INSERT INTO input_aspirasi (nis, id_kategori, lokasi, ket, tanggal_input) 
              VALUES ('$nis_user', '$id_kat', '$lokasi', '$ket', '$tgl')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Aspirasi berhasil terkirim!'); window.location='siswa_dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Dashboard | E-Sarana</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #3498db; --success: #2ecc71; --bg: #f8f9fa; }
        body { font-family: 'Quicksand', sans-serif; background-color: var(--bg); color: #2c3e50; }
        
        /* Navigasi Tengah */
        .nav-wrapper { display: flex; justify-content: center; margin: 30px 0; }
        .nav-pills-custom { background: #fff; padding: 8px; border-radius: 50px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .nav-pills-custom .nav-link { border-radius: 50px; padding: 10px 25px; color: #7f8c8d; font-weight: 600; transition: 0.3s; }
        .nav-pills-custom .nav-link.active { background: var(--primary); color: white !important; }

        .card-custom { border: none; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .form-control-pill { border-radius: 15px; padding: 12px 20px; background: #fdfdfd; }
        .btn-send { background: var(--primary); color: white; border-radius: 15px; padding: 12px; font-weight: 700; border: none; }
        
        .status-badge { padding: 5px 15px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; }
        .timeline-box { border-left: 3px solid #eee; padding-left: 20px; position: relative; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white px-4 shadow-sm">
    <a class="navbar-brand font-weight-bold text-primary" href="#"><i class="fas fa-graduation-cap mr-2"></i>E-SARANA SISWA</a>
    <div class="ml-auto d-flex align-items-center">
        <span class="small mr-3 text-muted">Siswa: <strong><?= $_SESSION['siswa']['nama'] ?></strong></span>
        <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</nav>

<div class="container py-4">
    <div class="nav-wrapper">
        <ul class="nav nav-pills nav-pills-custom shadow-sm" id="pills-tab">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#pills-form"><i class="fas fa-edit mr-2"></i>Buat Aspirasi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#pills-history"><i class="fas fa-tasks mr-2"></i>Status Aduan</a>
            </li>
        </ul>
    </div>

    <div class="tab-content mt-4">
        <div class="tab-pane fade show active" id="pills-form">
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card card-custom p-4">
                        <div class="text-center mb-4">
                            <h5 class="font-weight-bold">Sampaikan Keluhan Anda</h5>
                            <p class="small text-muted">Bantu kami meningkatkan fasilitas sekolah</p>
                        </div>
                        <form method="POST">
                            <div class="form-group">
                                <label class="small font-weight-bold">Kategori Fasilitas</label>
                                <select name="id_kategori" class="form-control form-control-pill" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php 
                                    $kat = mysqli_query($koneksi, "SELECT * FROM kategori");
                                    while($rk = mysqli_fetch_assoc($kat)) echo "<option value='".$rk['id_kategori']."'>".$rk['ket_kategori']."</option>";
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Lokasi Sarana (Misal: Kelas X-RPL)</label>
                                <input type="text" name="lokasi" class="form-control form-control-pill" placeholder="Contoh: Toilet Lantai 2" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Detail Kerusakan / Saran</label>
                                <textarea name="ket" class="form-control" rows="4" style="border-radius:15px;" placeholder="Jelaskan secara singkat..." required></textarea>
                            </div>
                            <button type="submit" name="kirim_aspirasi" class="btn btn-send btn-block shadow-sm">KIRIM SEKARANG</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pills-history">
            <h6 class="font-weight-bold mb-4">Riwayat Aspirasi Anda</h6>
            <div class="row">
                <?php
                $sql = "SELECT i.*, k.ket_kategori, a.status, a.feedback 
                        FROM input_aspirasi i
                        JOIN kategori k ON i.id_kategori = k.id_kategori
                        LEFT JOIN aspirasi a ON i.id_pelaporan = a.id_pelaporan
                        WHERE i.nis = '$nis_user' ORDER BY i.id_pelaporan DESC";
                $res = mysqli_query($koneksi, $sql);
                
                if(mysqli_num_rows($res) == 0) {
                    echo "<div class='col-12 text-center py-5'><p class='text-muted'>Belum ada aspirasi yang dikirim.</p></div>";
                }

                while($row = mysqli_fetch_assoc($res)) {
                    $st = $row['status'] ?? 'Menunggu';
                    $badge = ($st == 'Selesai') ? 'success' : (($st == 'Proses') ? 'warning' : 'secondary');
                ?>
                <div class="col-md-6 mb-3">
                    <div class="card card-custom p-3 border-left border-<?= $badge ?>" style="border-left-width: 5px !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge badge-light text-primary mb-2"><?= $row['ket_kategori'] ?></span>
                                <h6 class="font-weight-bold mb-1"><?= $row['lokasi'] ?></h6>
                                <p class="small text-muted mb-2"><?= $row['ket'] ?></p>
                            </div>
                            <span class="status-badge bg-<?= $badge ?> text-white text-uppercase"><?= $st ?></span>
                        </div>
                        <hr class="my-2">
                        <div class="small">
                            <span class="text-muted font-weight-bold">Umpan Balik Admin:</span><br>
                            <span class="<?= $row['feedback'] ? 'text-dark' : 'text-muted italic' ?>">
                                <?= $row['feedback'] ?: 'Belum ada tanggapan.' ?>
                            </span>
                        </div>
                        <div class="mt-2 text-right">
                            <small style="font-size: 10px;" class="text-muted"><?= $row['tanggal_input'] ?></small>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>