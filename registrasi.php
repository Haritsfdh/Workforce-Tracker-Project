<?php
session_start();
require 'functions.php';

$registrationSuccess = false;

if (isset($_POST["register"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $password2 = $_POST["password2"];

    // Pengecekan apakah username dan password tidak kosong
    if (!empty($username) && !empty($password) && !empty($password2)) {
        if ($password === $password2) {
            // Pengecekan apakah username sudah ada di database
            $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo "<script>alert('Registrasi gagal! Username sudah digunakan.');</script>";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Gunakan prepared statement untuk menghindari SQL Injection
                $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $hashedPassword);

                if ($stmt->execute()) {
                    $registrationSuccess = true;
                } else {
                    echo "<script>alert('Registrasi gagal! Terjadi kesalahan pada database.');</script>";
                }
            }
        } else {
            echo "<script>alert('Konfirmasi password tidak sesuai.');</script>";
        }
    } else {
        echo "<script>alert('Username dan password harus diisi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="registrasi.css">
</head>
<body>

<div class="modal">
  <div class="modal-container">
    <div class="modal-left">
      <h1 class="modal-title">Daftar Akun Baru</h1>
      <p class="modal-desc">Workforce Tracker website</p>
      
      <form action="" method="post">
      <div class="input-block">
        <label for="username" class="input-label">Username</label>
        <input type="text" name="username" id="username" placeholder="Username" style="width: 100%; padding: 10px; border: 1px solid #444; border-radius: 5px; background-color: #555; color: white;">

      </div>
      <div class="input-block">
        <label for="password" class="input-label">Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required>
      </div>
      <div class="input-block">
        <label for="password2" class="input-label">Konfirmasi Password</label>
        <input type="password" name="password2" id="password2" placeholder="Konfirmasi Password" required>
      </div>
        <div class="modal-buttons">
        <button class="input-button" type="submit" name="register">Daftar</button>
      </div>
        </form>
      <p class="sign-up">Sudah punya akun? <a href="login.php">Login Sekarang</a></p>
    </div>
    <div class="modal-right">
      <img src="https://images.unsplash.com/photo-1512486130939-2c4f79935e4f?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=dfd2ec5a01006fd8c4d7592a381d3776&auto=format&fit=crop&w=1000&q=80" alt="">
    </div>
    <button class="icon-button close-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
    <path d="M 25 3 C 12.86158 3 3 12.86158 3 25 C 3 37.13842 12.86158 47 25 47 C 37.13842 47 47 37.13842 47 25 C 47 12.86158 37.13842 3 25 3 z M 25 5 C 36.05754 5 45 13.94246 45 25 C 45 36.05754 36.05754 45 25 45 C 13.94246 45 5 36.05754 5 25 C 5 13.94246 13.94246 5 25 5 z M 16.990234 15.990234 A 1.0001 1.0001 0 0 0 16.292969 17.707031 L 23.585938 25 L 16.292969 32.292969 A 1.0001 1.0001 0 1 0 17.707031 33.707031 L 25 26.414062 L 32.292969 33.707031 A 1.0001 1.0001 0 1 0 33.707031 32.292969 L 26.414062 25 L 33.707031 17.707031 A 1.0001 1.0001 0 0 0 32.980469 15.990234 A 1.0001 1.0001 0 0 0 32.292969 16.292969 L 25 23.585938 L 17.707031 16.292969 A 1.0001 1.0001 0 0 0 16.990234 15.990234 z"></path>
</svg>
      </button>
  </div>
</div>

<?php if ($registrationSuccess): ?>
<div id="success-popup" class="popup">
    <div class="popup-content">
        <h2>Registrasi Berhasil!</h2>
        <p>Akun Anda telah berhasil didaftarkan.</p>
        <div class="popup-buttons">
            <a href="login.php" class="popup-button">Login</a>
            <button class="popup-button" onclick="closePopup()">Nanti</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function closePopup() {
    document.getElementById('success-popup').style.display = 'none';
}
</script>
</body>
</html>

