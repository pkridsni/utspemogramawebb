<?php 
session_start();
require_once 'dompdf/autoload.inc.php';  // Pastikan jalur ini sesuai

if (!isset($_SESSION["akun-admin"])) {
    if (isset($_SESSION["akun-user"])) {
        echo "<script>
            alert('Cetak data hanya berlaku untuk admin!');
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
                    WHERE transaksi.kode_pesanan = '$kode'");

if (empty($menu)) {
    echo "Data pesanan tidak ditemukan!";
    exit;
}

// Membuat konten HTML untuk PDF
ob_start();
include "page.php";  // Memuat halaman dengan konten yang ingin di-cetak
$html = ob_get_clean();

use Dompdf\Dompdf;
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();

// Output PDF langsung ke browser tanpa diunduh
$dompdf->stream('pesan.pdf', array('Attachment' => 0));

echo "<script>
    window.print();  // Menampilkan dialog print otomatis setelah PDF ditampilkan
    window.onafterprint = function() { window.close(); };  // Menutup tab setelah selesai print
</script>";
?>
