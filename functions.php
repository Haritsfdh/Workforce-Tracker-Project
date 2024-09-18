<?php 

// koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "workforce_tracker");

function query($query){
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)){
        $rows[]= $row;
    }
    return $rows;
}

function tambah($data){
    global $conn;

    // ambil data nya dengan metode post
    $nama = htmlspecialchars($data["nama"]);
    $alamat = htmlspecialchars($data["alamat"]);
    $nomorTelepon = htmlspecialchars($data["nomortelepon"]);
    $email = htmlspecialchars($data["email"]);


    // query tambah / insert data kedalam database 
    $query = "INSERT INTO karyawan VALUES
            ('', '$nama', '$alamat', '$nomorTelepon', '$email')";
    mysqli_query($conn, $query);
    // nilai default nya berupa angka /int sehingga bisa dimanfaatkan untuk pengkondisian
    // jika nilainya > 0 atau berhasil maka tambah 
    return mysqli_affected_rows($conn);
}

function hapus($id){
    global $conn;
    // query hapus data pada tabel
    mysqli_query($conn, "DELETE FROM karyawan WHERE id = $id");
    return mysqli_affected_rows($conn);
}

function ubah($data){

    global $conn;

    // ambil data nya dengan metode post
    $id = $data["id"];
    $nama = htmlspecialchars($data["nama"]);
    $alamat = htmlspecialchars($data["alamat"]);
    $nomorTelepon = htmlspecialchars($data["nomortelepon"]);
    $email = htmlspecialchars($data["email"]);

    // query UPDATE data kedalam database 
    // SET / ganti dengan data apa 
    $query = "UPDATE karyawan SET
            nama = '$nama', 
            alamat = '$alamat', 
            nomortelepon = '$nomorTelepon', 
            email = '$email'
            WHERE id = $id";
    mysqli_query($conn, $query);
    // nilai default nya berupa angka /int sehingga bisa dimanfaatkan untuk pengkondisian
    // jika nilainya > 0 atau berhasil maka tambah 
    return mysqli_affected_rows($conn);
}

function cari($keyword){
    $query = "SELECT * FROM mahasiswa 
            WHERE nama LIKE '%$keyword%'OR
            npm LIKE '%$keyword%'OR
            jurusan LIKE '%$keyword%'OR
            email LIKE '%$keyword%'
            ";
    return query($query);
}

// function upload(){

//     // panggil data yang di submit
//     $namaFile = $_FILES['gambar']['name'];
//     $ukuranFile = $_FILES['gambar']['size'];
//     $error = $_FILES['gambar']['error'];
//     $tmpName = $_FILES['gambar']['tmp_name'];

//     // cek apakah tidak ada gambar yang diupload
//     if($error === 4) {
//         echo "<script>
//             alert('tolong upload file terlebih dahulu!');
//         </script>"; 
//         return false;
//     }

//     // cek apakah yang diupload adalah gambar

//     $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
//     // ubah string jadi array dengan explode , '.' adalah delimiter

//     $ekstensiGambar = explode('.', $namaFile);

//     // ambil ekstensi file nya saja

//     $ekstensiGambar = strtolower(end($ekstensiGambar));

//     // cek apakah ada sebuah string didalam array dengan in_array

//     if( !in_array($ekstensiGambar, $ekstensiGambarValid) ){

//         echo "<script>
//             alert('yang kamu upload bukan gambar!');
//         </script>";

//         return false;

//     }

//     // tentukan ukuran gambar , jika lebih dari 1MB tampilkan pesan gambar terlalu besar

//     if( $ukuranFile > 1000000 ){
//         echo "<script>
//             alert('ukuran gambar terlalu besar!');
//         </script>";

//         return false;
//     }

//     // cegah nama file sama yang menyebabkan gambar tertimpa

//     $namaFileBaru = uniqid();
//     $namaFileBaru .= '.';
//     $namaFileBaru .= $ekstensiGambar;

//     // lolos pengecekan , gambar siap di upload 
//     move_uploaded_file($tmpName, 'img/' . $namaFileBaru);

//     return $namaFileBaru;

    
// }

function registrasi($data){

    global $conn;

    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);

    //cek apakah suatu username sudah terdaftar atau belum
    $stmt = mysqli_prepare($conn, "SELECT username FROM user WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "<script>
            alert('Username sudah ada');
        </script>";
        return false;
    }
        

    //cek konfirmasi password
    if( $password2 !== $password ){
        echo "<script>
            alert('konfirmasi password tidak sesuai');
        </script>";

        return false;
    }

    // enksripsi password agar aman, 
    //ada dua parameter (yang ingin dienskripsi, metode enkripsinya)

    $password = password_hash($password, PASSWORD_DEFAULT);

    // masukan user baru kedalam database
    $query = "INSERT INTO user VALUES
            ('', '$username', '$password')";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);

}
    
















?>