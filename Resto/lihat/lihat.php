<?php 
session_start();
require_once 'dompdf/autoload.inc.php';
if (!isset($_SESSION["akun-admin"])) {
    if (isset($_SESSION["akun-user"])) {
        echo "<script>
            alert('Lihat data hanya berlaku untuk admin!');
            location.href = '../index.php';
        </script>";
        exit;
    } else {
        header("Location: ../login.php");
        exit;
    }
}

require_once "../function.php";

// Mengambil data pesanan berdasarkan kode_pesanan
$kode = $_GET["kode_pesanan"];
$menu = ambil_data("SELECT DISTINCT * FROM pesanan 
                    JOIN transaksi ON (pesanan.kode_pesanan = transaksi.kode_pesanan) 
                    JOIN menu ON (menu.kode_menu = pesanan.kode_menu) 
                    WHERE transaksi.kode_pesanan = '$kode'
");

if (empty($menu)) {
    echo "Data pesanan tidak ditemukan!";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $menu[0]["nama_pelanggan"]; ?></title>
    <style>
    .daftar-pesanan, .daftar-pesanan tr > td, .daftar-pesanan tr > th {
        border: 1px solid black;
        border-collapse: collapse;
        width: auto;
    }

    .daftar-pesanan th, .daftar-pesanan td {
        padding: 5px;  
    }

    .daftar-pesanan th {
        text-align: left;  
    }

  
    .daftar-pesanan td:nth-child(1), .daftar-pesanan th:nth-child(1) {
        width: 25%;  
    }

    .daftar-pesanan td:nth-child(2), .daftar-pesanan th:nth-child(2) {
        width: 10%;  
    }

    .daftar-pesanan td:nth-child(3), .daftar-pesanan th:nth-child(3) {
        width: 15%;  
    }

    .daftar-pesanan td:nth-child(4), .daftar-pesanan th:nth-child(4) {
        width: 20%;  
    }

    .daftar-pesanan td:nth-child(5), .daftar-pesanan th:nth-child(5) {
        width: 10%;  
    }

    .daftar-pesanan td:nth-child(6), .daftar-pesanan th:nth-child(6) {
        width: 20%;  
    }

    .pembayaran tr > th {
        width: auto;
        text-align: start;
    }

    .pembayaran {
        width: auto;
        
    }
</style>

</head>
<body>
    <a href="../index.php?transaksi" style="color: black; text-decoration: none;">
    <h1 align="center">Pesanan</h1>
</a>
    <table class="data-pelanggan">
        <tr>
            <td>Atas Nama</td>
            <td>:</td>
            <td><?= $menu[0]["nama_pelanggan"]; ?></td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td><?= $menu[0]["waktu"]; ?></td>
        </tr>
        <tr>
            <td>Jenis Pesanan</td>
            <td>:</td>
            <td><?= $menu[0]["jenis_pesanan"]; ?></td>
        </tr>
        <tr>
            <td>No Meja</td>
            <td>:</td>
            <td><?= $menu[0]["meja"]; ?></td>
        </tr>
    </table><br>

    <table class="daftar-pesanan" cellpadding="5">
        <tr>
            <th>Daftar Menu</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>No Meja</th>
            <th>Total</th>
        </tr>
        <?php 
        $total_semuanya = 0;
        foreach ($menu as $m) { ?>
            <tr>
                <td><?= $m["nama"]; ?></td>
                <td><?= $m["qty"]; ?></td>
                <td>Rp. <?= number_format($m["harga"], 0, ',', '.'); ?></td>
                <td><?= $m["meja"]; ?></td>
                <td>Rp. <?= number_format($m["harga"] * $m["qty"], 0, ',', '.'); ?></td>
            </tr>
        <?php $total_semuanya += $m["harga"] * $m["qty"];  
        } ?>
    </table><br>

    <table class="pembayaran" cellpadding="3">
        <tr>
            <th>Total Semuanya</th>
            <th>:</th>
            <th>Rp. <?= number_format($total_semuanya, 0, ',', '.'); ?></th>
        </tr>
    </table>
    <br>
 <!-- Tombol untuk mencetak PDF
 <a href="../cetak/cetak.php?kode_pesanan=<?= $kode; ?>&pembayaran=<?= $total_semuanya; ?>" class="btn btn-success">Cetak PDF</a> -->

    </body>
</html>
