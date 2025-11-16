<?php
session_start();

// Cek apakah user sudah login dan role siswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: index.php");
    exit();
}

require 'koneksi.php';

// Ambil user dan user_id dengan aman
$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

if (!$user_id) {
    die("User ID tidak ditemukan. Silakan login ulang.");
}

// Cek apakah siswa sudah punya data lengkap
$checkDataStmt = $koneksi->prepare("SELECT id FROM data_siswa WHERE user_id = ?");
$checkDataStmt->bind_param("i", $user_id);
$checkDataStmt->execute();
$dataSiswa = $checkDataStmt->get_result()->fetch_assoc();
$checkDataStmt->close();

// Jika sudah lengkap, redirect ke home
if ($dataSiswa) {
    header("Location: home.php");
    exit();
}

// Ambil data ekstrakurikuler
$ekstraStmt = $koneksi->query("SELECT * FROM ekstra ORDER BY nama_ekstra ASC");
$ekstrakurikuler = $ekstraStmt->fetch_all(MYSQLI_ASSOC);

// Proses pemilihan ekstrakurikuler
if (isset($_POST['pilih_ekstra'])) {
    if (empty($_POST['ekstra'])) {
        $error = "Pilih minimal 1 ekstrakurikuler!";
    } else {
        // Simpan pilihan ekstra ke session
        $_SESSION['pilihan_ekstra'] = $_POST['ekstra'];
        header("Location: lengkapi_data.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Ekstrakurikuler</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 15px;
        }

        .content {
            padding: 40px;
        }

        .welcome-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .welcome-box h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .welcome-box p {
            color: #666;
            line-height: 1.6;
        }

        .info-box {
            background: #e8f4f8;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .info-box ul {
            margin: 10px 0 0 20px;
            color: #555;
        }

        .info-box ul li {
            margin: 5px 0;
            font-size: 14px;
        }

        .ekstra-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .ekstra-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .ekstra-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
        }

        .ekstra-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }

        .ekstra-card input[type="checkbox"] {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .ekstra-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: white;
            font-size: 24px;
        }

        .ekstra-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .ekstra-detail {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }

        .ekstra-detail i {
            width: 18px;
            color: #667eea;
        }

        .error-message {
            background: #fee;
            border-left: 4px solid #f44336;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .btn-container {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            .ekstra-grid {
                grid-template-columns: 1fr;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Pilih Ekstrakurikuler</h1>
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</p>
        </div>

        <div class="content">
            <div class="welcome-box">
                <h3>Hai, Siswa Baru!</h3>
                <p>Selamat datang di Sistem Ekstrakurikuler SMK Salafiyah. Silakan pilih ekstrakurikuler yang ingin Anda ikuti. Anda dapat memilih lebih dari satu ekstrakurikuler sesuai minat Anda.</p>
            </div>

            <div class="info-box">
                <strong><i class="fas fa-info-circle"></i> Informasi Penting:</strong>
                <ul>
                    <li>Pilih minimal 1 ekstrakurikuler</li>
                    <li>Anda dapat memilih lebih dari 1 ekstrakurikuler</li>
                    <li>Perhatikan jadwal dan lokasi agar tidak bentrok</li>
                    <li>Setelah memilih, Anda akan diminta melengkapi data pribadi</li>
                </ul>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="ekstra-grid">
                    <?php foreach ($ekstrakurikuler as $index => $ekstra): ?>
                        <div class="ekstra-card" onclick="toggleCard(this)">
                            <input type="checkbox" name="ekstra[]" value="<?php echo $ekstra['id']; ?>" onclick="event.stopPropagation();">
                            <div class="ekstra-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="ekstra-name"><?php echo htmlspecialchars($ekstra['nama_ekstra']); ?></div>
                            <div class="ekstra-detail">
                                <i class="fas fa-user"></i> Pembina: <?php echo htmlspecialchars($ekstra['pembina'] ?? 'Belum ditentukan'); ?>
                            </div>
                            <div class="ekstra-detail">
                                <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($ekstra['hari'] ?? 'Akan diinformasikan'); ?>
                            </div>
                            <div class="ekstra-detail">
                                <i class="fas fa-clock"></i> <?php echo htmlspecialchars($ekstra['waktu'] ?? '-'); ?>
                            </div>
                            <div class="ekstra-detail">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ekstra['lokasi'] ?? 'Akan diinformasikan'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="btn-container">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='logout.php'">
                        <i class="fas fa-times"></i> Logout
                    </button>
                    <button type="submit" name="pilih_ekstra" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCard(card) {
            const checkbox = card.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        }

        // Update visual saat checkbox diubah
        document.querySelectorAll('.ekstra-card input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    this.closest('.ekstra-card').classList.add('selected');
                } else {
                    this.closest('.ekstra-card').classList.remove('selected');
                }
            });
        });
    </script>
</body>
</html>