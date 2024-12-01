<?php

$host = 'localhost';
$dbname = 'HotelBooking';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}


$lastReservation = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['gender'];
    $nomor_identitas = $_POST['id'];
    $tipe_kamar = $_POST['tipe'];
    $tanggal_pesan = $_POST['tanggal'];
    $durasi = $_POST['durasi'];
    $breakfast = isset($_POST['breakfast']) ? 1 : 0;
    $total_bayar = $_POST['total'];

    $sql = "INSERT INTO reservations (nama_pemesan, jenis_kelamin, nomor_identitas, tipe_kamar, tanggal_pesan, durasi_menginap, termasuk_breakfast, total_bayar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiid", $nama, $jenis_kelamin, $nomor_identitas, $tipe_kamar, $tanggal_pesan, $durasi, $breakfast, $total_bayar);

    if ($stmt->execute()) {
      
        $lastId = $stmt->insert_id;
        $result = $conn->query("SELECT * FROM reservations WHERE id = $lastId");
        $lastReservation = $result->fetch_assoc();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    

    <?php if ($lastReservation): ?>
        <div class="detail-pemesanan">
            <h2>Detail Pesanan Anda</h2>
            <p><span>Nama Pemesan   :</span> <?php echo htmlspecialchars($lastReservation['nama_pemesan']); ?></p>
            <p><span>Nomor Identitas:</span> <?php echo htmlspecialchars($lastReservation['nomor_identitas']); ?></p>
            <p><span>Jenis Kelamin  :</span> <?php echo htmlspecialchars($lastReservation['jenis_kelamin']); ?></p>
            <p><span>Tipe Kamar     :</span> <?php echo htmlspecialchars($lastReservation['tipe_kamar']); ?></p>
            <p><span>Durasi Menginap:</span> <?php echo htmlspecialchars($lastReservation['durasi_menginap']); ?> Hari</p>
            <p><span>Discount       :</span> <?php echo $lastReservation['durasi_menginap'] > 2 ? '10%' : '0%'; ?></p>
            <p><span>Total Bayar    :</span> Rp <?php echo number_format($lastReservation['total_bayar'], 0, ',', '.'); ?></p>
            <?php if (!empty($lastReservation['termasuk_breakfast']) && $lastReservation['termasuk_breakfast'] == true): ?>
        <p><span>Fasilitas Tambahan : </span> Dengan Sarapan</p>
      
    <?php endif; ?>
            <button onclick="window.location.href='../index.html'">Kembali</button>
        </div>
    <?php else: ?>
        <form action="" method="POST">
            <label for="nama">Nama Pemesan</label>
            <input type="text" id="nama" name="nama" required>

            <label for="gender">Jenis Kelamin</label>
            <select id="gender" name="gender">
                <option value="Laki-Laki">Laki-Laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>

            <label for="id">Nomor Identitas</label>
<input type="text" id="id" name="id" maxlength="16" required pattern="\d{16}" title="Nomor identitas harus berisi 16 angka">


            <label for="tipe">Tipe Kamar</label>
            <select id="tipe" name="tipe">
                <option value="Standar">Standar</option>
                <option value="Deluxe">Deluxe</option>
                <option value="Family">Family</option>
            </select>

            <label for="tanggal">Tanggal Pesan</label>
<input type="date" id="tanggal" name="tanggal" required>

            <label for="durasi">Durasi Menginap (malam)</label>
            <input type="number" id="durasi" name="durasi" required>
            
            <label for="total">Total Bayar</label>
            <input type="text" id="total" name="total" readonly>
            <div class="checkbox-group">
        <input type="checkbox" id="breakfast" name="breakfast" />
        <label for="breakfast">Tambahkan Sarapan</label>
    </div>
            <button type="submit">Pesan</button>
            <button onclick="window.location.href='../index.html'">Kembali</button>
        </form>
    <?php endif; ?>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tanggalInput = document.getElementById('tanggal');
        const today = new Date().toISOString().split('T')[0];
        tanggalInput.setAttribute('min', today);

        const tipeInput = document.getElementById('tipe');
        const durasiInput = document.getElementById('durasi');
        const breakfastInput = document.getElementById('breakfast');
        const totalInput = document.getElementById('total');
        const idInput = document.getElementById('id');
        const form = document.querySelector('form');

        function hitungTotal() {
            const tipe = tipeInput.value;
            const durasi = parseInt(durasiInput.value, 10) || 0;
            const breakfast = breakfastInput.checked;

            let hargaPerMalam;
            if (tipe === 'Standar') hargaPerMalam = 500000;
            else if (tipe === 'Deluxe') hargaPerMalam = 750000;
            else if (tipe === 'Family') hargaPerMalam = 1000000;

            let total = hargaPerMalam * durasi;
            if (durasi > 2) total *= 0.9;
            if (breakfast) total += 80000;

            totalInput.value = total;
        }

        tipeInput.addEventListener('change', hitungTotal);
        durasiInput.addEventListener('input', hitungTotal);
        breakfastInput.addEventListener('change', hitungTotal);

        form.addEventListener('submit', function (e) {
            const idValue = idInput.value.trim();
            if (!/^\d{16}$/.test(idValue)) {
                alert("Nomor identitas harus terdiri dari 16 angka.");
                e.preventDefault();
            }
        });
    });
</script>

</html>
