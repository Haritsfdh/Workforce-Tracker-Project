<?php 
session_start();
require 'functions.php';

// Cek apakah sudah ada cookie
if (isset($_COOKIE['id']) && isset($_COOKIE['key'])){
    //panggil dulu
    $id = $_COOKIE['id'];
    $key = $_COOKIE['key'];

    // ambil username berdasarkan id
    $result = mysqli_query($conn, "SELECT username FROM user WHERE id = $id");
    $row = mysqli_fetch_assoc($result);

    // cek cookie dan username
    if ($key === hash('sha256', $row['username'] )){
        $_SESSION['login'] = true;
    }
}

if (isset($_SESSION["login"])) {
    header("Location: index.php");
    exit;
}

//cek username
if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Pengecekan apakah username dan password tidak kosong
    if (!empty($username) && !empty($password)) {
        $result = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");
        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);

            // cek password   
            if (password_verify($password, $row["password"])) {
                $_SESSION["login"] = true;

                // buat cookie
                if (isset($_POST["remember"])) {
                    setcookie('id', $row['id'], time() + (86400 * 30 * 3)); // set for 3 months
                    setcookie('key', hash('sha256', $row['username']), time() + (86400 * 30 * 3)); // set for 3 months
                }
                header("Location: index.php");
                exit;
            } else {
                // Pesan kesalahan jika password salah
                echo "<script>alert('Password yang kamu masukkan Salah!')</script>";
            }
        } else {
            // Pesan kesalahan jika username tidak ditemukan
            echo "<script>alert('Username tidak ditemukan')</script>";
        }
    } else {
        // Pesan kesalahan jika username atau password kosong
        echo "<script>alert('Username dan password harus diisi')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="registrasi.css"> <!-- Include your registrasi.css for shared styles -->
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
        <input type="username" name="username" id="username" placeholder="Username">
      </div>
      <div class="input-block">
        <label for="password" class="input-label">Password</label>
        <input type="password" name="password" id="password" placeholder="Password">
      </div>
        <div class="modal-buttons">
        <button class="input-button" type="submit" name="login">Login</button>
      </div>
        </form>
      <p class="sign-up">Belum punya akun? <a href="registrasi.php">Daftar Sekarang</a></p>
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

<script src="script.js"></script>
</body>
</html>
