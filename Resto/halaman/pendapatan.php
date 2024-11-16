<?php
// Ambil data pendapatan untuk harian, mingguan, dan bulanan
$pendapatan_harian = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(pendapatan_harian) AS total_harian FROM pendapatan WHERE tanggal = CURDATE()"));
$pendapatan_mingguan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(pendapatan_mingguan) AS total_mingguan FROM pendapatan WHERE tanggal >= CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY"));
$pendapatan_bulanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(pendapatan_bulanan) AS total_bulanan FROM pendapatan WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())"));
?>

<!-- Menampilkan Pendapatan Harian, Mingguan, dan Bulanan -->
<!-- <table class="table table-bordered" style="margin-top: 100px;">
    <tr class="header-transaksi">      
        <th class="col-md-4">
            <h5>Pendapatan Harian</h5> -->
            <!-- <p>Rp. <?= isset($pendapatan_harian['total_harian']) ? number_format($pendapatan_harian['total_harian'], 0, ',', '.') : '0' ?></p>
        </th>
        <th class="col-md-4">
            <h5>Pendapatan Mingguan</h5>
            <p>Rp. <?= isset($pendapatan_mingguan['total_mingguan']) ? number_format($pendapatan_mingguan['total_mingguan'], 0, ',', '.') : '0' ?></p>
        </th>
        <th class="col-md-4">
            <h5>Pendapatan Bulanan</h5>
            <p>Rp. <?= isset($pendapatan_bulanan['total_bulanan']) ? number_format($pendapatan_bulanan['total_bulanan'], 0, ',', '.') : '0' ?></p>
        </th>
    </tr>
</table> -->

<!-- Tabel Pendapatan -->
<table class="table table-bordered" style="margin-top: 100px;">
    <thead>
        <tr class="header-transaksi">
            <th>No</th>
            <th>Pendapatan Harian</th>
            <th>Pendapatan Mingguan</th>
            <th>Pendapatan Bulanan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $i = 1;
        // Anda bisa menyesuaikan query ini sesuai kebutuhan untuk mendata pendapatan
        $pendapatan_list = ambil_data("SELECT * FROM pendapatan ORDER BY tanggal DESC"); 
        
        foreach ($pendapatan_list as $pendapatan) { 
            $total_harian = isset($pendapatan['pendapatan_harian']) ? $pendapatan['pendapatan_harian'] : 0;
            $total_mingguan = isset($pendapatan['pendapatan_mingguan']) ? $pendapatan['pendapatan_mingguan'] : 0;
            $total_bulanan = isset($pendapatan['pendapatan_bulanan']) ? $pendapatan['pendapatan_bulanan'] : 0;
        ?>
            <tr style="background-color: white;">
                <td><?= $i; ?></td>
                <td>Rp. <?= number_format($total_harian, 0, ',', '.') ?>
                <td>Rp. <?= number_format($total_mingguan, 0, ',', '.') ?></td>
                <td>Rp. <?= number_format($total_bulanan, 0, ',', '.') ?></td>
            </tr>
        <?php 
        $i++;
        }
        ?>
    </tbody>
</table>
