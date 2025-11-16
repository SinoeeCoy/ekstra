<?php
session_start();

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header("Location: home.php");
    exit();
}

require 'koneksi.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username_login']);
    $password = $_POST['password_login'];
    
    if (empty($username) || empty($password)) {
        echo '<script>alert("Error: Username dan password harus diisi.");</script>';
    } else {
        $loginStmt = $koneksi->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
        $loginStmt->bind_param("s", $username);

        $loginStmt->execute();
        $result = $loginStmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            
            if ($data['status'] != 'aktif') {
                echo '<script>alert("Error: Akun Anda tidak aktif. Hubungi administrator.");</script>';
            } else {
                if (md5($password) === $data['password']) {
                    session_regenerate_id(true);
                    
                    $_SESSION['user'] = [
                        'id' => $data['id'],
                        'username' => $data['username'],
                        'password' => $data['password'],
                        'role' => $data['role']
                    ];
                    
                    // Cek jika siswa, apakah sudah lengkap datanya
                    if ($data['role'] == 'siswa') {
                        $checkSiswa = $koneksi->prepare("SELECT id FROM data_siswa WHERE user_id = ?");
                        $checkSiswa->bind_param("i", $data['id']);
                        $checkSiswa->execute();
                        $siswaData = $checkSiswa->get_result()->fetch_assoc();
                        
                        if (!$siswaData) {
                            echo '<script>alert("Selamat datang, '.htmlspecialchars($data['nama_siswa']).'! Silakan pilih ekstrakurikuler dan lengkapi data Anda."); location.href="pilih_ekstra.php";</script>';
                        } else {
                            echo '<script>alert("Selamat datang Siswa, '.htmlspecialchars($data['nama_siswa']).'"); location.href="home.php";</script>';
                        }
                        $checkSiswa->close();
                    } elseif ($data['role'] == 'pembina') {
                        echo '<script>alert("Selamat datang Pembina, '.htmlspecialchars($data['username']).'"); location.href="home.php";</script>';
                    } elseif ($data['role'] == 'admin') {
                        echo '<script>alert("Selamat datang Admin, '.htmlspecialchars($data['username']).'"); location.href="home.php";</script>';
                    } else {
                        echo '<script>alert("Selamat datang, '.htmlspecialchars($data['nama_siswa']).'"); location.href="home.php";</script>';
                    }
                } else {
                    echo '<script>alert("Error: Password tidak sesuai.");</script>';
                }
            }
        } else {
            echo '<script>alert("Error: Username tidak ditemukan.");</script>';
        }
        $loginStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMK Salafiyah</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            min-height: 550px;
        }

        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #89b5f5 0%, #6b9ce8 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            position: relative;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            top: -30px;
            left: -30px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .illustration {
            width: 250px;
            height: auto;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .left-panel h2 {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .left-panel p {
            font-size: 15px;
            line-height: 1.6;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .right-panel {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h3 {
            font-size: 26px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .forgot-password {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 25px;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #764ba2;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #999;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            padding: 0 15px;
        }

        .register-link {
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #764ba2;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left-panel {
                padding: 40px 30px;
            }

            .illustration {
                width: 180px;
            }

            .left-panel h2 {
                font-size: 22px;
            }

            .right-panel {
                padding: 40px 30px;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            animation: fadeInUp 0.6s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <svg class="illustration" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                <!-- Character -->
                <ellipse cx="250" cy="450" rx="80" ry="15" fill="rgba(0,0,0,0.1)"/>
                
                <!-- Body -->
                <rect x="200" y="280" width="100" height="140" rx="10" fill="#4a5568"/>
                
                <!-- Head -->
                <circle cx="250" cy="200" r="60" fill="#fbbf77"/>
                
                <!-- Hair -->
                <path d="M 190 190 Q 190 150 220 140 Q 250 130 280 140 Q 310 150 310 190 Z" fill="#d4764f"/>
                
                <!-- Glasses -->
                <circle cx="230" cy="200" r="15" fill="none" stroke="#333" stroke-width="3"/>
                <circle cx="270" cy="200" r="15" fill="none" stroke="#333" stroke-width="3"/>
                <line x1="245" y1="200" x2="255" y2="200" stroke="#333" stroke-width="3"/>
                
                <!-- Eyes -->
                <circle cx="230" cy="200" r="5" fill="#333"/>
                <circle cx="270" cy="200" r="5" fill="#333"/>
                
                <!-- Smile -->
                <path d="M 230 220 Q 250 230 270 220" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round"/>
                
                <!-- Arms -->
                <rect x="170" y="300" width="30" height="80" rx="15" fill="#fbbf77"/>
                <rect x="300" y="300" width="30" height="80" rx="15" fill="#fbbf77"/>
                
                <!-- Legs -->
                <rect x="210" y="420" width="35" height="80" rx="10" fill="#2d3748"/>
                <rect x="255" y="420" width="35" height="80" rx="10" fill="#2d3748"/>
                
                <!-- Clipboard -->
                <rect x="150" y="350" width="70" height="90" rx="5" fill="#fff" stroke="#333" stroke-width="2"/>
                <rect x="160" y="360" width="50" height="3" fill="#667eea"/>
                <rect x="160" y="370" width="50" height="3" fill="#e0e0e0"/>
                <rect x="160" y="380" width="35" height="3" fill="#e0e0e0"/>
                <circle cx="185" cy="335" r="8" fill="#667eea"/>
            </svg>
            
            <h2>Selamat Datang!</h2>
            <p>Sistem Informasi Ekstrakurikuler<br>SMK Salafiyah</p>
        </div>

        <div class="right-panel">
            <div class="login-header">
                <h3>Login ke Akun Anda</h3>
                <p>Masukkan kredensial Anda untuk mengakses sistem</p>
            </div>

            <form method="POST" action="">
                <div class="input-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username_login" placeholder="Masukkan username" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password_login" placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="forgot-password">
                    <a href="#">Lupa Password?</a>
                </div>

                <button type="submit" name="login" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
            </form>

            <div class="divider">
                <span>atau</span>
            </div>

            <div class="register-link">
                Belum punya akun? <a href="register.php">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</body>
</html>