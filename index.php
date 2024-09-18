<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Workforce Tracker</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,300;0,400;0,700;1,700&display=swap"
    rel="stylesheet">

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>

  <!-- My Style -->
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <!-- Navbar start -->
  <nav class="navbar">
    <a href="#" class="navbar-logo">Workforce<span>Tracker</span>.</a>

    <div class="navbar-nav">
      <a href="#home">Home</a>
      <a href="#about">Tentang Kami</a>
      <a href="maps.php">Maps</a>
      <a href="#products">Produk</a>
      <a href="logout.php" >Logout</a>
    </div>

    <!-- Search Form start -->
    <div class="search-form">
      <input type="search" id="search-box" placeholder="search here...">
      <label for="search-box"><i data-feather="search"></i></label>
    </div>
    <!-- Search Form end -->

  </nav>
  <!-- Navbar end -->

  <!-- Hero Section start -->
  <section class="hero" id="home">
    <div class="mask-container">
      <main class="content">
        <h1>Cara Terbaik Untuk<span>Cek Lokasi Karyawan</span></h1>
      </main>
    </div>
  </section>
  <!-- Hero Section end -->

  <!-- About Section start -->
<section id="about" class="about">
  <h2><span>Tentang</span> Kami</h2>

  <div class="row">
    <!-- First group photo container -->
    <div class="about-img">
      <img src="img/products/fotoharits.jpg" alt="Group Photo 1">
      <div class="member-info">
        <h4>Muhammad Harits Fadhila</h4>
        <p>Ketua</p>
      </div>
    </div>

    <!-- Second group photo container -->
    <div class="about-img">
      <img src="img/products/fotoatan.png" alt="Group Photo 2">
      <div class="member-info">
        <h4>Nur Dua Fathansyah</h4>
        <p>Anggota</p>
      </div>
    </div>

    <!-- Third group photo container -->
    <div class="about-img">
      <img src="img/products/syahda.jpeg" alt="Group Photo 3">
      <div class="member-info">
        <h4>Prabowo</h4>
        <p>Anggota</p>
      </div>
    </div>
  </div>
</section>
<!-- About Section end -->


  <!-- Products Section start -->
  <section class="products" id="products">
    <h2><span>Produk Unggulan</span> Kami</h2>
    <p>Ini adalah produk Tugas Akhir yang kami buat dengan 2 Subsistem</p>

    <div class="row">
      <div class="product-card">
        <div class="product-icons">
        </div>
        <div class="product-image">
          <img src="img/products/Navigation Symbol PNG (2).jpg" alt="Product 1">
        </div>
        <div class="product-content">
          <h3>GPS Geofencing Tracker</h3>
        </div>
      </div>
      <div class="product-card">
        <div class="product-icons">
        </div>
        <div class="product-image">
          <img src="img/products/yolov5.png" alt="Product 1">
        </div>
        <div class="product-content">
          <h3>Deteksi Yolo V5</h3>
        </div>
      </div>
  </section>
  <!-- Products Section end -->

  <!-- Footer start -->
  <footer>
    <div class="socials">
      <a href="#"><i data-feather="instagram"></i></a>
      <a href="#"><i data-feather="twitter"></i></a>
      <a href="#"><i data-feather="facebook"></i></a>
    </div>

    <div class="links">
      <a href="#home">Home</a>
      <a href="#about">Tentang Kami</a>
    </div>

    <div class="credit">
      <p>Created by <a href="">Muhammad Harits Fadhila</a>. | &copy; 2024.</p>
    </div>
  </footer>
  <!-- Footer end -->

  <!-- Modal Box Item Detail start -->
  <div class="modal" id="item-detail-modal">
    <div class="modal-container">
      <a href="#" class="close-icon"><i data-feather="x"></i></a>
      <div class="modal-content">
        <img src="img/products/1.jpg" alt="Product 1">
        <div class="product-content">
          <h3>Product 1</h3>
          <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Provident, tenetur cupiditate facilis obcaecati
            ullam maiores minima quos perspiciatis similique itaque, esse rerum eius repellendus voluptatibus!</p>
          <div class="product-stars">
            <i data-feather="star" class="star-full"></i>
            <i data-feather="star" class="star-full"></i>
            <i data-feather="star" class="star-full"></i>
            <i data-feather="star" class="star-full"></i>
            <i data-feather="star"></i>
          </div>
          <div class="product-price">IDR 30K <span>IDR 55K</span></div>
          <a href="#"><i data-feather="shopping-cart"></i> <span>add to cart</span></a>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Box Item Detail end -->

  <!-- Feather Icons -->
  <script>
    feather.replace()
  </script>

  <!-- My Javascript -->
  <script src="js/script.js"></script>
</body>

</html>