<table class="table table-bordered" style="margin-top: 100px;">

    <tr class="header-transaksi">

        <th>No</th>

        <th>Kode Pesanan</th>

        <th>Nama Pelanggan</th>

        <th> Jenis Pesanan </th>

        <th> No Meja </th>

        <th>Kode Menu</th>

        <th>Jumlah</th>

    </tr>

    <?php $i = 1; foreach ($menu as $m) { ?>

        <tr style="background-color: white;">

            <td><?= $i; ?></td>

            <td><?= $m["kode_pesanan"]; ?></td>

            <td><?= $m["nama_pelanggan"]; ?></td>

            <td><?= $m["jenis_pesanan"]; ?></td>

            <td><?= $m["meja"]; ?></td>

            <td><?= $m["kode_menu"]; ?></td>

            <td><?= $m["qty"]; ?></td>

        </tr>

    <?php $i++; } ?>

</table>