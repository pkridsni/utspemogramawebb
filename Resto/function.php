<?php

$koneksi = mysqli_connect("localhost", "root", "", "senayan");

// Funtion Register
function register_akun() {
    global $koneksi;
    $username = htmlspecialchars($_POST["username"]);
    $password = md5(htmlspecialchars($_POST["password"]));
    $konfirmasi_password = md5(htmlspecialchars($_POST["konfirmasi-password"]));
    $cek_username = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM `user` WHERE username = '$username'"));

    if ($cek_username != null) {
        echo "<script>alert('Username sudah ada!');</script>";
        return -1;
    } else if ($password != $konfirmasi_password) {
        echo "<script>alert('Password Tidak Sesuai!');</script>";
        return -1;
    }

    mysqli_query($koneksi, "INSERT INTO `user` VALUES ('', '$username', '$password')");
    return mysqli_affected_rows($koneksi);
}

// Function Login
function login_akun() {
    global $koneksi;
    $username = htmlspecialchars($_POST["username"]);
    $password = md5(htmlspecialchars($_POST["password"]));
    $cek_akun_admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM `admin` WHERE username = '$username' AND `password` = '$password'"));
    $cek_akun_user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM `user` WHERE username = '$username' AND `password` = '$password'"));

    if ($cek_akun_admin == null && $cek_akun_user == null) return false;
    if ($cek_akun_user != null) {
        $_SESSION["akun-user"] = ["username" => $username, "password" => $password];
    }
    if ($cek_akun_admin != null) {
        $_SESSION["akun-admin"] = ["username" => $username, "password" => $password];
    }

    header("Location: index.php");
    exit;
}

// Function Select Data
function ambil_data($query) {
    global $koneksi;
    $db = [];
    $sql_query = mysqli_query($koneksi, $query);
    while ($q = mysqli_fetch_assoc($sql_query)) {
        array_push($db, $q);
    }
    return $db;
}

// Function Tambah Data
function tambah_data_menu() {
    global $koneksi;
    $nama = htmlspecialchars($_POST["nama"]);
    $harga = (int) htmlspecialchars($_POST["harga"]);
    $gambar = htmlspecialchars($_FILES["gambar"]["name"]);
    $kategori = htmlspecialchars($_POST["kategori"]);
    $status = htmlspecialchars($_POST["status"]);


    // Generate Kode Menu
    $kode_menu = "MN" . ambil_data("SELECT MAX(SUBSTR(kode_menu, 3)) AS kode FROM menu")[0]["kode"] + 1;

    // cek format gambar
    $format_gambar = ["jpg", "jpeg", "png", "gif"];
    $cek_gambar = explode(".", $gambar);
    $cek_gambar = strtolower(end($cek_gambar));
    if (!in_array($cek_gambar, $format_gambar)) {
        echo "<script>alert('File yang diupload bukan merupakan image!');</script>";
        return -1;
    }

    // upload file
    $nama_gambar = uniqid() . ".$cek_gambar";
    move_uploaded_file($_FILES["gambar"]["tmp_name"], "src/img/$nama_gambar");

    // eksekusi query insert
    $id_menu = ambil_data("SELECT MAX(SUBSTR(kode_menu, 3)) AS kode FROM menu")[0]["kode"] + 1;
    mysqli_query($koneksi, "INSERT INTO menu VALUES ($id_menu, '$kode_menu', '$nama', $harga, '$nama_gambar', '$kategori', '$status')");
    return mysqli_affected_rows($koneksi);
}

// Function Edit Data Menu
function edit_data_menu() {
    global $koneksi;
    $id_menu = $_POST["id_menu"];
    $nama = htmlspecialchars($_POST["nama"]);
    $harga = (int) htmlspecialchars($_POST["harga"]);
    $gambar = htmlspecialchars($_FILES["gambar"]["name"]);
    $kategori = htmlspecialchars($_POST["kategori"]);
    $status = htmlspecialchars($_POST["status"]);
    $kode_menu = htmlspecialchars($_POST["kode_menu"]);

    // cek format gambar
    $format_gambar = ["jpg", "jpeg", "png", "gif"];
    $cek_gambar = explode(".", $gambar);
    $cek_gambar = strtolower(end($cek_gambar));
    if (!in_array($cek_gambar, $format_gambar) && strlen($gambar) != 0) {
        echo "<script>alert('File yang diupload bukan merupakan image!');</script>";
        return -1;
    }

    // cek jika admin mengupload gambar yang baru
    $gambar_lama = $_POST["gambar-lama"];
    if (strlen($gambar) == 0) {
        $gambar = $gambar_lama;
    } else if ($gambar != $gambar_lama && strlen($gambar) != 0) {
        move_uploaded_file($_FILES["gambar"]["tmp_name"], "src/img/$gambar");
        unlink("src/img/$gambar_lama");
    }

    // eksekusi query update
    mysqli_query($koneksi, "UPDATE menu SET kode_menu = '$kode_menu', nama = '$nama', harga = $harga, gambar = '$gambar', kategori = '$kategori', `status` = '$status' WHERE id_menu = $id_menu");
    return mysqli_affected_rows($koneksi);
}

// Function Hapus Data Menu
function hapus_data_menu() {
    global $koneksi;
    $id_menu = $_GET["id_menu"];

    // hapus file gambar
    $file_gambar = ambil_data("SELECT * FROM menu WHERE id_menu = $id_menu")[0]["gambar"];
    if (file_exists("src/img/$file_gambar")) unlink("src/img/$file_gambar");

    // eksekusi query delete
    mysqli_query($koneksi, "DELETE FROM menu WHERE id_menu = $id_menu");
    return mysqli_affected_rows($koneksi);
}

// Tambah Data Pesanan & Transaksi
function tambah_data_pesanan() {
    global $koneksi;

    // Nama Pelanggan dan Detail Pemesanan
    $pelanggan = htmlspecialchars($_POST["pelanggan"]);
    $jenis_pesanan = $_POST["jenis_pesanan"];
    $meja = $_POST["meja"];

    // Generate Kode Pesanan
    $kode_pesanan = uniqid();

    // Mengambil Data Qty dan Kode Menu
    $list_pesanan = [];
    $max_menu = count(ambil_data("SELECT * FROM menu"));

    // Variabel untuk menghitung total pendapatan
    $total_pendapatan = 0;

    // Loop untuk menghitung total harga pesanan
    for ($i = 1; $i <= $max_menu; $i++) {
        // Cek apakah qty ada dan bukan nol
        if (isset($_POST["qty$i"]) && (int) $_POST["qty$i"] > 0) {
            // Ambil kode menu dan qty yang dipesan
            $kode_menu = $_POST["kode_menu$i"];
            $qty = (int) $_POST["qty$i"];

            // Ambil harga menu
            $menu_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT harga FROM menu WHERE kode_menu = '$kode_menu'"));
            $harga_menu = $menu_data['harga'];

            // Hitung total pendapatan untuk item ini
            $total_pendapatan += $harga_menu * $qty;

            // Masukkan pesanan ke tabel pesanan
            if (!mysqli_query($koneksi, "INSERT INTO pesanan (kode_pesanan, kode_menu, qty, jenis_pesanan, meja) VALUES ('$kode_pesanan', '$kode_menu', $qty, '$jenis_pesanan', '$meja')")) {
                echo "<script>alert('Gagal menyimpan pesanan: " . mysqli_error($koneksi) . "');</script>";
                return -1;
            }
        }
    }

    // Cek jika tidak ada pesanan yang valid
    if ($total_pendapatan == 0) {
        echo "<script>alert('Anda belum memesan menu!');</script>";
        return -1;
    }

    // Masukkan data transaksi ke tabel transaksi
    if (!mysqli_query($koneksi, "INSERT INTO transaksi (kode_pesanan, nama_pelanggan, jenis_pesanan, meja) VALUES ('$kode_pesanan', '$pelanggan', '$jenis_pesanan', '$meja')")) {
        echo "<script>alert('Gagal menyimpan transaksi: " . mysqli_error($koneksi) . "');</script>";
        return -1;
    }

    // Insert atau Update Pendapatan (harian, mingguan, bulanan)
    $tanggal_sekarang = date('Y-m-d');
    $pendapatan_harian = $total_pendapatan; // Pendapatan hari ini
    $pendapatan_mingguan = $total_pendapatan; // Pendapatan minggu ini
    $pendapatan_bulanan = $total_pendapatan; // Pendapatan bulan ini

    // Periksa apakah sudah ada data pendapatan harian
    $cek_pendapatan_harian = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pendapatan WHERE tanggal = '$tanggal_sekarang'"));
    if ($cek_pendapatan_harian) {
        // Update jika sudah ada data pendapatan harian
        $pendapatan_harian += $cek_pendapatan_harian['pendapatan_harian'];
        mysqli_query($koneksi, "UPDATE pendapatan SET pendapatan_harian = $pendapatan_harian WHERE tanggal = '$tanggal_sekarang'");
    } else {
        // Insert jika data pendapatan harian belum ada
        mysqli_query($koneksi, "INSERT INTO pendapatan (tanggal, pendapatan_harian) VALUES ('$tanggal_sekarang', $pendapatan_harian)");
    }

    // Periksa apakah sudah ada data pendapatan mingguan
    $cek_pendapatan_mingguan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pendapatan WHERE WEEK(tanggal, 1) = WEEK('$tanggal_sekarang', 1)"));
    if ($cek_pendapatan_mingguan) {
        // Update jika sudah ada data pendapatan mingguan
        $pendapatan_mingguan += $cek_pendapatan_mingguan['pendapatan_mingguan'];
        mysqli_query($koneksi, "UPDATE pendapatan SET pendapatan_mingguan = $pendapatan_mingguan WHERE WEEK(tanggal, 1) = WEEK('$tanggal_sekarang', 1)");
    } else {
        // Insert jika data pendapatan mingguan belum ada
        mysqli_query($koneksi, "INSERT INTO pendapatan (tanggal, pendapatan_mingguan) VALUES ('$tanggal_sekarang', $pendapatan_mingguan)");
    }

    // Periksa apakah sudah ada data pendapatan bulanan
    $cek_pendapatan_bulanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pendapatan WHERE MONTH(tanggal) = MONTH('$tanggal_sekarang') AND YEAR(tanggal) = YEAR('$tanggal_sekarang')"));
    if ($cek_pendapatan_bulanan) {
        // Update jika sudah ada data pendapatan bulanan
        $pendapatan_bulanan += $cek_pendapatan_bulanan['pendapatan_bulanan'];
        mysqli_query($koneksi, "UPDATE pendapatan SET pendapatan_bulanan = $pendapatan_bulanan WHERE MONTH(tanggal) = MONTH('$tanggal_sekarang') AND YEAR(tanggal) = YEAR('$tanggal_sekarang')");
    } else {
        // Insert jika data pendapatan bulanan belum ada
        mysqli_query($koneksi, "INSERT INTO pendapatan (tanggal, pendapatan_bulanan) VALUES ('$tanggal_sekarang', $pendapatan_bulanan)");
    }

    return mysqli_affected_rows($koneksi);
}



// Hapus Data Pesanan & Transaksi
function hapus_data_pesanan() {
    global $koneksi;
    $kode_pesanan = $_GET["kode_pesanan"];
    
    // eksekusi query delete
    mysqli_query($koneksi, "DELETE FROM transaksi WHERE kode_pesanan = '$kode_pesanan'");
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE kode_pesanan = '$kode_pesanan'");
    
    return mysqli_affected_rows($koneksi);
}
