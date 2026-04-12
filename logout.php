<?php
session_start();

// 1. Hapus semua data yang ada di dalam $_SESSION
$_SESSION = array();

// 2. Hancurkan session dari server
session_destroy();

// 3. Hapus cookie session jika ada (opsional tapi lebih bersih)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Arahkan kembali ke halaman login
echo "<script>alert('Anda telah berhasil keluar.'); location='login.php';</script>";
exit;
?>