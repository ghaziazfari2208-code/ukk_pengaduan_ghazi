<?php
session_start();
include "db/koneksi.php";

// 1. Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses Ditolak! Silahkan Login.'); location='login.php';</script>";
    exit;
}

// 2. Logika Update Status & Feedback (Tanggapan Admin)
if (isset($_POST['update_status'])) {
    $id_pelaporan = $_POST['id_pelaporan'];
    $status = $_POST['status'];
    $feedback = mysqli_real_escape_string($koneksi, $_POST['feedback']);

    // Cek apakah data sudah ada di tabel aspirasi
    $cek = mysqli_query($koneksi, "SELECT * FROM aspirasi WHERE id_pelaporan='$id_pelaporan'");
    if (mysqli_num_rows($cek) > 0) {
        $query = "UPDATE aspirasi SET status='$status', feedback='$feedback' WHERE id_pelaporan='$id_pelaporan'";
    } else {
        $query = "INSERT INTO aspirasi (id_pelaporan, status, feedback) VALUES ('$id_pelaporan', '$status', '$feedback')";
    }
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Berhasil menanggapi aspirasi!'); window.location='admin_dashboard.php';</script>";
    }
}

// 3. Logika Filter Dinamis
$where = "WHERE 1=1";
if (isset($_GET['tgl']) && $_GET['tgl'] != '') {
    $tgl = $_GET['tgl'];
    $where .= " AND DATE(i.tanggal_input) = '$tgl'";
}
if (isset($_GET['bulan']) && $_GET['bulan'] != '') {
    $bln = $_GET['bulan'];
    $where .= " AND MONTH(i.tanggal_input) = '$bln'";
}
if (isset($_GET['search']) && $_GET['search'] != '') {
    $s = $_GET['search'];
    $where .= " AND (s.nama LIKE '%$s%' OR k.ket_kategori LIKE '%$s%')";
}

// 4. Statistik Ringkas
$stat_masuk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM input_aspirasi"))['total'];
$stat_selesai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aspirasi WHERE status='Selesai'"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | E-Sarana</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #3498db; --dark: #2c3e50; --bg: #f4f7f6; }
        body { font-family: 'Quicksand', sans-serif; background-color: var(--bg); color: var(--dark); }
        
        /* Navigasi Tengah Modern */
        .nav-wrapper { display: flex; justify-content: center; margin: 30px 0; }
        .nav-pills-custom { background: #fff; padding: 8px; border-radius: 50px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .nav-pills-custom .nav-link { border-radius: 50px; padding: 10px 25px; color: #7f8c8d; font-weight: 600; transition: 0.3s; }
        .nav-pills-custom .nav-link.active { background: var(--primary); color: white !important; }

        /* Card & Table */
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); }
        .table thead th { border: none; background: #fafafa; text-transform: uppercase; font-size: 0.7rem; color: #95a5a6; }
        .badge-status { padding: 5px 12px; border-radius: 50px; font-weight: 700; font-size: 0.7rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white px-4 shadow-sm">
    <a class="navbar-brand font-weight-bold text-primary" href="#"><i class="fas fa-tools mr-2"></i>E-SARANA ADMIN</a>
    <div class="ml-auto d-flex align-items-center">
        <span class="small mr-3 text-muted">Halo, <strong><?= $_SESSION['admin']['nama_lengkap'] ?></strong></span>
        <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill"><i class="fas fa-power-off"></i></a>
    </div>
</nav>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card card-custom bg-primary text-white p-3">
                <div class="card-body">
                    <h6>Total Aspirasi Masuk</h6>
                    <h2 class="font-weight-bold"><?= $stat_masuk ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card card-custom bg-success text-white p-3">
                <div class="card-body">
                    <h6>Aspirasi Selesai</h6>
                    <h2 class="font-weight-bold"><?= $stat_selesai ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="nav-wrapper">
        <ul class="nav nav-pills nav-pills-custom shadow-sm" id="pills-tab">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#pills-semua"><i class="fas fa-list mr-2"></i>List Aspirasi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#pills-filter"><i class="fas fa-filter mr-2"></i>Filter Data</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#pills-history"><i class="fas fa-history mr-2"></i>History Selesai</a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade" id="pills-filter">
            <div class="card card-custom mb-4 p-4">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-3 form-group">
                        <label class="small font-weight-bold">Per Tanggal</label>
                        <input type="date" name="tgl" class="form-control rounded-pill">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="small font-weight-bold">Per Bulan</label>
                        <select name="bulan" class="form-control rounded-pill">
                            <option value="">Semua Bulan</option>
                            <?php 
                            $m = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                            foreach($m as $k => $v) echo "<option value='".($k+1)."'>$v</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold">Siswa / Kategori</label>
                        <input type="text" name="search" class="form-control rounded-pill" placeholder="Cari nama atau kategori...">
                    </div>
                    <div class="col-md-2 form-group">
                        <button type="submit" class="btn btn-primary btn-block rounded-pill">Cari</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="tab-pane fade show active" id="pills-semua">
            <div class="card card-custom">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Siswa</th>
                                    <th>Aspirasi</th>
                                    <th>Status</th>
                                    <th>Feedback</th>
                                    <th class="pr-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT i.*, s.nama, k.ket_kategori, a.status, a.feedback 
                                        FROM input_aspirasi i
                                        JOIN siswa s ON i.nis = s.nis
                                        JOIN kategori k ON i.id_kategori = k.id_kategori
                                        LEFT JOIN aspirasi a ON i.id_pelaporan = a.id_pelaporan
                                        $where ORDER BY i.id_pelaporan DESC";
                                $res = mysqli_query($koneksi, $sql);
                                while($row = mysqli_fetch_assoc($res)) {
                                    $st = $row['status'] ?? 'Menunggu';
                                    $color = ($st == 'Selesai') ? 'success' : (($st == 'Proses') ? 'warning' : 'secondary');
                                ?>
                                <tr>
                                    <td class="pl-4"><strong><?= $row['nama'] ?></strong><br><small class="text-muted"><?= $row['nis'] ?></small></td>
                                    <td>
                                        <span class="badge badge-light text-primary"><?= $row['ket_kategori'] ?></span><br>
                                        <small><?= $row['lokasi'] ?> - <?= $row['ket'] ?></small>
                                    </td>
                                    <td><span class="badge-status bg-<?= $color ?> text-white"><?= $st ?></span></td>
                                    <td><small class="text-muted"><?= $row['feedback'] ?: '-' ?></small></td>
                                    <td class="pr-4">
                                        <button class="btn btn-sm btn-light border rounded-pill" data-toggle="modal" data-target="#modal<?= $row['id_pelaporan'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modal<?= $row['id_pelaporan'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                                            <form method="POST">
                                                <div class="modal-body p-4">
                                                    <h5 class="font-weight-bold mb-4">Tanggapi Aspirasi</h5>
                                                    <input type="hidden" name="id_pelaporan" value="<?= $row['id_pelaporan'] ?>">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Status</label>
                                                        <select name="status" class="form-control rounded-pill">
                                                            <option value="Menunggu" <?= $st=='Menunggu'?'selected':'' ?>>Menunggu</option>
                                                            <option value="Proses" <?= $st=='Proses'?'selected':'' ?>>Proses</option>
                                                            <option value="Selesai" <?= $st=='Selesai'?'selected':'' ?>>Selesai</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Umpan Balik</label>
                                                        <textarea name="feedback" class="form-control" rows="3" style="border-radius: 15px;"><?= $row['feedback'] ?></textarea>
                                                    </div>
                                                    <button type="submit" name="update_status" class="btn btn-primary btn-block rounded-pill mt-4">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pills-history">
            <div class="card card-custom p-5 text-center text-muted">
                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                <h5>Histori Penyelesaian</h5>
                <p>Silahkan gunakan fitur filter di atas untuk mencari data histori perbaikan sarana yang sudah berstatus <b>Selesai</b>.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>