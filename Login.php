<?php
session_start();
include "db/koneksi.php"; 

$error = "";

// --- BAGIAN PENGECEKAN SESSION DI SINI SUDAH DIHAPUS ---
// Jadi setiap buka file ini, form login akan selalu muncul.

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // 1. Cek di tabel Admin
    $queryAdmin = $koneksi->query("SELECT * FROM admin WHERE username='$username' AND password='$password'");
    
    if ($queryAdmin->num_rows > 0) {
        $akun = $queryAdmin->fetch_assoc();
        $_SESSION['admin'] = $akun;
        $_SESSION['role'] = 'admin';
        echo "<script>alert('Login Admin Berhasil!'); location='admin_dashboard.php';</script>";
        exit;
    } 
    
    // 2. Cek di tabel Siswa
    $querySiswa = $koneksi->query("SELECT * FROM siswa WHERE nis='$username' AND password='$password'");
    
    if ($querySiswa->num_rows > 0) {
        $akun = $querySiswa->fetch_assoc();
        $_SESSION['siswa'] = $akun;
        $_SESSION['role'] = 'siswa';
        $_SESSION['nis'] = $akun['nis']; 
        
        echo "<script>alert('Login Siswa Berhasil!'); location='siswa_dashboard.php';</script>"; 
        exit;
    } else {
        $error = "Username/NIS atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | E-Sarana Sekolah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .btn-login { background: #3498db; border: none; border-radius: 10px; padding: 12px; font-weight: 600; color: white; width: 100%; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <i class="fas fa-school fa-3x text-primary mb-3"></i>
        <h3>E-Sarana</h3>
        <p class="small text-muted">Silahkan Login Terlebih Dahulu</p>
    </div>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger small"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="small font-weight-bold">Username / NIS</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="small font-weight-bold">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn-login mt-3">LOGIN SEKARANG</button>
    </form>
</div>

</body>
</html>