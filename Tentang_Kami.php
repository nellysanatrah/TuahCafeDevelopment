<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$success_message = '';
$error_message = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_STRING);
    $proposal = filter_var($_POST['proposal'] ?? '', FILTER_SANITIZE_STRING);

    if (!empty($name) && !empty($phone) && !empty($proposal)) {
        try {
            $query = "INSERT INTO komuniti_events (name, phone, proposal) VALUES (:name, :phone, :proposal)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':proposal' => $proposal
            ]);
            $success_message = "Terima kasih! Permohonan anda telah dihantar.";
        } catch(PDOException $e) {
            $error_message = "Ralat sistem. Sila cuba lagi.";
        }
    } else {
        $error_message = "Sila isi semua ruangan yang bertanda *";
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuah Cafe - Tentang Kami</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <style>
        /* --- RESET & BASIC --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f7f1e3;
            color: #4e342e;
            overflow-x: hidden;
        }

        /* --- HEADER --- */
        .main-header {
            background-color: #4A372E;
            color: white;
            padding: 10px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-section { display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; }
        .logo-circle {
            width: 45px;
            height: 45px;
            background-color: #1a1a1a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .brand-text h1 { font-size: 22px; font-weight: 700; margin: 0; }
        .brand-text p { font-size: 10px; opacity: 0.8; margin: 0; }

        .nav-links { display: flex; align-items: center; gap: 25px; }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            transition: 0.3s;
        }
        .nav-links a:hover { color: #C5A059; }

        /* --- HEADER RIGHT (CART & PROFILE) --- */
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon {
            color: white;
            font-size: 20px;
            text-decoration: none;
            transition: 0.3s;
            display: flex;
            align-items: center;
        }

        .header-icon:hover {
            color: #C5A059;
        }

        /* --- DROPDOWN --- */
        .dropdown { position: relative; }
        .dropdown-trigger { display: flex; align-items: center; gap: 5px; }
        .dropdown-toggle { cursor: pointer; color: white; transition: 0.3s; font-size: 14px; }
        .dropdown-toggle:hover { color: #C5A059; }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #4A372E;
            min-width: 180px;
            border-radius: 6px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            padding: 8px 0;
        }
        .dropdown-content a {
            display: block;
            padding: 10px 16px;
            text-transform: none;
        }
        .dropdown.active .dropdown-content { display: block; }

        /* --- HERO --- */
        .hero {
            height: 80vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('background.jpeg') center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }
        .hero h1 { font-size: 45px; margin-bottom: 10px; }

        /* --- INFO SECTION --- */
        .info-section { background: #d8cfc3; padding: 60px; }
        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1200px;
            margin: 0 auto;
        }
        .image-box { height: 400px; overflow: hidden; }
        .image-box img { width: 100%; height: 100%; object-fit: cover; }
        .text-box {
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .text-box h2 { font-size: 28px; color: #4b2e1e; margin-bottom: 15px; }
        .text-box p { font-size: 14px; line-height: 1.6; margin-bottom: 20px; text-align: justify; }

        /* --- TIMELINE --- */
        .timeline-section {
            padding: 100px 20px;
            background-color: #f7f1e3;
            width: 100%;
        }
        .section-title {
            text-align: center;
            font-size: 36px;
            margin-bottom: 30px;
            color: #4b2e1e;
            position: relative;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: #C5A059;
            margin: 10px auto;
        }

        .timeline-container {
            position: relative;
            max-width: 1100px;
            margin: 0 auto;
        }

        .timeline-line {
            position: absolute;
            width: 3px;
            background-color: #5d4037;
            top: 0;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .timeline-item {
            padding: 20px 0;
            position: relative;
            width: 50%;
            display: flex;
            justify-content: flex-end;
        }

        .timeline-item.right {
            left: 50%;
            justify-content: flex-start;
        }

        .timeline-dot {
            position: absolute;
            width: 16px;
            height: 16px;
            background-color: #5d4037;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;
        }

        .timeline-item.left .timeline-dot { right: -8px; }
        .timeline-item.right .timeline-dot { left: -8px; }

        .timeline-content {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 85%;
        }

        .timeline-item.left { padding-right: 50px; }
        .timeline-item.right { padding-left: 50px; }

        .year { font-weight: 800; font-size: 14px; color: #8d6e63; display: block; margin-bottom: 5px; }

        /* --- NILAI KAMI --- */
        .nilai-kami {
            display: flex;
            flex-wrap: wrap;
            background-color: #f7f1e3;
        }
        .nilai-text { flex: 1; padding: 60px; min-width: 300px; }
        .nilai-text h2 { font-size: 32px; color: #4b2e1e; margin-bottom: 30px; }
        .nilai-item { margin-bottom: 25px; }
        .nilai-item h4 { color: #4b2e1e; font-size: 18px; margin-bottom: 8px; }
        .nilai-item p { font-size: 14px; line-height: 1.6; color: #5d4037; text-align: justify; }
        .nilai-image { flex: 1; background: url('komuniti.jpg') center/cover; min-height: 400px; }

        /* --- COMMUNITY SLIDER --- */
        .community-text-section {
            max-width: 900px;
            margin: 0 auto 50px;
            text-align: center;
            padding: 0 20px;
        }
        .community-text-section h2 { color: #4b2e1e; margin-bottom: 20px; }
        .community-text-section p { line-height: 1.8; margin-bottom: 15px; color: #5d4037; }

        .menu-container {
            max-width: 1200px;
            margin: 0 auto 100px;
            position: relative;
            padding: 0 60px;
        }
        
        .swiper { width: 100%; height: auto; }

        .menu-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            margin: 10px;
        }
        .menu-img {
            height: 220px;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
        }
        .menu-info { padding: 25px; }
        .menu-info h4 { margin-bottom: 10px; font-size: 18px; color: #4b2e1e; }
        .menu-info p { color: #666; font-size: 14px; line-height: 1.5; }

        .swiper-button-next, .swiper-button-prev {
            background-color: #5b3a29;
            width: 40px !important;
            height: 40px !important;
            border-radius: 50%;
            color: white !important;
        }
        .swiper-button-next:after, .swiper-button-prev:after { font-size: 16px !important; font-weight: bold; }

        .center-btn { text-align: center; margin-bottom: 60px; }
        .btn-outline-brown {
            padding: 12px 35px;
            border: 2px solid #8d6e63;
            border-radius: 25px;
            text-decoration: none;
            color: #5d4037;
            font-weight: bold;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-outline-brown:hover { background: #8d6e63; color: white; }

        /* --- SECTION PENAJA (WORKING PHP FORM) --- */
        .sponsorship-section {
            background: linear-gradient(to right, #8b5e3c, #5d4037);
            padding: 80px 20px;
            text-align: center;
            color: white;
            border-radius: 40px 40px 0 0;
        }
        .sponsorship-section h2 { font-size: 32px; margin-bottom: 10px; }
        .sponsorship-section p { font-size: 14px; opacity: 0.9; max-width: 700px; margin: 0 auto 40px; line-height: 1.6; }
        
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; max-width: 800px; margin-left: auto; margin-right: auto; }
        .alert-success { background: rgba(40, 167, 69, 0.4); border: 1px solid #28a745; }
        .alert-error { background: rgba(220, 53, 69, 0.4); border: 1px solid #dc3545; }

        .sponsor-form-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .sponsor-form input, .sponsor-form textarea {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            color: white;
            font-size: 14px;
        }
        
        .sponsor-form input::placeholder, .sponsor-form textarea::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .sponsor-form textarea { height: 150px; resize: none; grid-column: span 2; }
        
        .btn-hantar {
            width: 100%;
            background: white;
            color: #5d4037;
            padding: 15px;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            text-transform: uppercase;
        }
        .btn-hantar:hover { background: #f7f1e3; transform: scale(1.02); }

        /* --- FOOTER --- */
        footer {
            background: linear-gradient(to right, #6b3f2a, #4b2e1e);
            color: #eee;
            padding: 80px 100px 40px;
        }
        .footer-container { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 60px; 
            margin-bottom: 40px;
        }
        .footer-container h2 { font-size: 24px; color: #d4a24c; margin-bottom: 20px; }
        .footer-container p { font-size: 14px; line-height: 1.6; opacity: 0.8; }
        .footer-container ul { list-style: none; }
        .footer-container ul li { margin-bottom: 10px; }
        .footer-container ul li a { text-decoration: none; color: #eee; font-size: 14px; }
        
        .footer-bottom {
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }
        .footer-icons {
            display: flex;
            gap: 20px;
        }
        .footer-icons a {
            color: white;
            font-size: 20px;
            transition: 0.3s;
        }
        .footer-icons a:hover {
            color: #d4a24c;
        }

        @media (max-width: 768px) {
            .grid-container, .footer-container { grid-template-columns: 1fr; }
            .nav-links, .lang-switch { display: none; }
            .timeline-line { left: 20px; }
            .timeline-item { width: 100%; justify-content: flex-start; padding-left: 45px !important; }
            .timeline-item.right { left: 0; }
            .timeline-dot { left: 12px !important; }
            .menu-container { padding: 0 40px; }
            .form-row { grid-template-columns: 1fr; }
            .sponsor-form textarea { grid-column: span 1; }
            footer { padding: 40px 20px; }
        }
    </style>
</head>

<body>

    <header class="main-header">
        <a href="Home_Page.html" class="logo-section">
            <div class="logo-circle">
                <img src="logo.png" alt="Logo" style="width:46px;">
            </div>
            <div class="brand-text">
                <h1>Tuah Cafe</h1>
                <p>Best Makan Spot Event Space di Bangi</p>
            </div>
        </a>

        <nav class="nav-links">
            <a href="Tentang_Kami.php">Tentang Kami</a>
            <a href="Menu_fyp2.html">Menu</a>
            <a href="Promotion.html">Promosi</a>
            <a href="Keahlian.html">Ahli</a>
            
            <div class="dropdown">
                <div class="dropdown-trigger">
                    <a href="Perkhidmatan.html">Perkhidmatan</a>
                    <i class="fas fa-caret-down dropdown-toggle"></i>
                </div>
                <div class="dropdown-content">
                    <a href="cafe_event_bookings.php">Sewa Hall</a>
                    <a href="Delivery.html">Tempahan dalam talian</a>
                    <a href="Katering.html">Katering</a>
                </div>
            </div>
                
            <a href="galeriacara.html">Galeri</a>
            <a href="HubungiKami.html">Hubungi Kami</a>
            <a href="Job_Form.php">Kerjaya</a>
        </nav>

        <div class="header-right">
            <a href="checkout.html" class="header-icon">
                <i class="fas fa-shopping-cart"></i>
            </a>
            <a href="http://localhost/fyp2_renew/admin/login.php" class="header-icon">
                <i class="fas fa-user-circle"></i>
            </a>
        </div>
    </header>

    <section class="hero">
        <div>
            <h1>TENTANG KAMI</h1>
            <p>KISAH DISEBALIK CITA RASA</p>
        </div>
    </section>

    <section class="info-section">
        <div class="grid-container">
            <div class="image-box">
                <img src="background.jpeg" alt="Perjalanan">
            </div>
            <div class="text-box">
                <h2>Kisah Kami</h2>
                <p>Dari permulaan kecil hingga menjadi nama yang dikenali dalam industri makanan dan minuman. Kisah kami adalah tentang semangat, ketahanan, dan keinginan untuk terus memberi yang terbaik kepada komuniti.</p>
            </div>
        </div>
    </section>

    <section class="timeline-section">
        <h2 class="section-title">Perjalanan Kami</h2>
        <div class="timeline-container">
            <div class="timeline-line"></div>
            <div class="timeline-item left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="year">2014</span>
                    <h4>Penubuhan</h4>
                    <p>Tuah Cafe diasaskan oleh Muhammad Nazeem di Bandar Baru Bangi dengan modal permulaan RM60,000.</p>
                </div>
            </div>
            <div class="timeline-item right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="year">2014</span>
                    <h4>Permulaan</h4>
                    <p>Bermula sebagai kafe shisha yang dibangunkan sendiri termasuk mengecat kedai dan memasang perabot.</p>
                </div>
            </div>
            <div class="timeline-item left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="year">2014</span>
                    <h4>Menu Awal</h4>
                    <p>Memperkenalkan Chicken Set berharga RM5 yang menjadi pilihan pelanggan.</p>
                </div>
            </div>
            <div class="timeline-item right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="year">2017</span>
                    <h4>Langkah Berani</h4>
                    <p>Menghentikan jualan shisha selepas perubahan trend pasaran dan fenomena vape.</p>
                </div>
            </div>
            <div class="timeline-item left">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="year">2017</span>
                    <h4>Perubahan Konsep</h4>
                    <p>Berubah kepada konsep kafe keluarga walaupun berdepan kritikan dan penurunan jualan sementara.</p>
                </div>
            </div>
            <div class="timeline-item right">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="year">Kini</span>
                    <h4>Pilihan Komuniti</h4>
                    <p>Kini menjadi tempat kegemaran keluarga dan komuniti untuk menikmati hidangan bersama.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="nilai-kami">
        <div class="nilai-text">
            <h2>Nilai Kami</h2>
            <div class="nilai-item">
                <h4>Falsafah Kami</h4>
                <p>Tuah Cafe bukan sekadar tempat makan — ia adalah institusi yang mahu memberi kembali kepada masyarakat. Kami percaya pada kualiti, kebersihan, dan pengalaman pelanggan yang terbaik.</p>
            </div>
            <div class="nilai-item">
                <h4>Masa Depan Kami</h4>
                <p>Kami bercadang untuk buka satu cawangan baru setiap tahun. Misi kami ialah jadikan Tuah Cafe destinasi untuk mencipta memori.</p>
            </div>
            <div class="nilai-item">
                <h4>Apa Yang Kami 'Offer' ?</h4>
                <p>Kini, Tuah Cafe ada dua cawangan di Bandar Baru Bangi dan Eco Majestic, Semenyih. Kami hidangkan menu yang menggabungkan citarasa tempatan dan global.</p>
            </div>
        </div>
        <div class="nilai-image"></div>
    </section>

    <section style="padding: 80px 0; background: #f7f1e3;">
        <div class="community-text-section">
            <h2 class="section-title">Bersama Komuniti</h2>
            <h2 class="section-title">Bersatu Teguh, Bercerai Roboh</h2>
            <p>Asalnya bermula dari bawah, kami sentiasa bersama komuniti untuk membina perpaduan yang lebih utuh.</p>
            <p>Melalui program amal dan kolaborasi strategik, kami bertekad untuk mencipta impak positif yang berkekalan.</p>
        </div>

        <div class="menu-container">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="menu-card">
                            <img src="k2.jpg" alt="Program Infaq" class="menu-img" style="width: 100%; height: 220px; object-fit: cover;">
                            <div class="menu-info"><h4>Program Infaq</h4><p>Sumbangan makanan kepada golongan memerlukan setiap Jumaat.</p></div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="menu-card">
                            <img src="sukan.png" alt="Sukan Komuniti" class="menu-img" style="width: 100%; height: 220px; object-fit: cover;">
                            <div class="menu-info"><h4>Sukan Komuniti</h4><p>Menaja aktiviti sukan belia di sekitar Bangi & Semenyih.</p></div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="menu-card">
                            <img src="latihankerjaya.png" alt="Latihan Kerjaya" class="menu-img" style="width: 100%; height: 220px; object-fit: cover;">
                            <div class="menu-info"><h4>Latihan Kerjaya</h4><p>Memberi peluang pekerjaan dan latihan kemahiran kepada lepasan sekolah.</p></div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="menu-card">
                            <img src="k4.jpg" alt="Majlis Akrab" class="menu-img" style="width: 100%; height: 220px; object-fit: cover;">
                            <div class="menu-info"><h4>Majlis Akrab</h4><p>Menyediakan ruang acara untuk keraian komuniti setempat.</p></div>
                        </div>
                    </div>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
        <div class="center-btn">
            <a href="galeriacara.html" class="btn-outline-brown">LIHAT GALERI</a>
        </div>
    </section>

    <!-- WORKING SPONSORSHIP FORM SECTION (from Komuniti.php) -->
    <section id="form-top" class="sponsorship-section">
        <h2>Kesempatan Menjadi Penaja Tuah Cafe</h2>
        <p>Kongsikan idea anda, dan mari cipta sesuatu yang luar biasa bersama!</p>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="sponsor-form-container">
            <form method="POST" action="#form-top" class="sponsor-form">
                <div class="form-row">
                    <input type="text" name="name" placeholder="Nama anda*" required>
                    <input type="text" name="phone" placeholder="No. Telefon*" required>
                </div>
                <div class="form-row" style="grid-template-columns: 1fr;">
                    <textarea name="proposal" placeholder="Perkenalkan diri anda, organisasi anda dan program anda secara ringkas*" required></textarea>
                </div>
                <button type="submit" class="btn-hantar">HANTAR PERMOHONAN</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <div>
                <h2>Tuah Cafe</h2>
                <p>Tempat di mana tradisi kulinari bertemu inovasi! Dari permulaan kecil hingga menjadi 
                nama yang dikenali dalam industri makanan dan minuman, kisah kami adalah tentang semangat,
                ketahanan, dan keinginan untuk terus memberi yang terbaik kepada komuniti.</p>
            </div>
            <div>
                <h2>Pautan Pantas</h2>
                <ul>
                    <li><a href="Tentang_Kami.php">Tentang Kami</a></li>
                    <li><a href="Menu_fyp2.html">Menu</a></li>
                    <li><a href="Promotion.html">Promosi</a></li>
                    <li><a href="Keahlian.html">Keahlian</a></li>
                    <li><a href="Perkhidmatan.html">Perkhidmatan</a></li>
                    <li><a href="galeriacara.html">Galeri</a></li>
                    <li><a href="HubungiKami.html">Hubungi Kami</a></li>
                    <li><a href="Job_Form.php">Kerjaya</a></li>
                </ul>
            </div>
            <div>
                <h2>Maklumat Perhubungan</h2>
                <p>📍 No.22 Jalan 7/1C, Seksyen 7 Bandar Baru Bangi, Selangor</p>
                <p>📍 42, Jalan Eco Majestic 10/1D, Semenyih, Selangor</p>
                <p>📞 03-8912 8798</p>
                <p>📩 admin@tuahcafe.com</p>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                <p>© 2026 Tuah Cafe. Hak cipta terpelihara.</p>
            </div>
            <div class="footer-icons">
                <a href="https://www.instagram.com/tuahcafe" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.facebook.com/tuahcafe" target="_blank"><i class="fab fa-facebook"></i></a>
                <a href="https://www.tiktok.com/@tuahcafe" target="_blank"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Dropdown Logic
        document.querySelectorAll(".dropdown-toggle").forEach(function(icon) {
            icon.addEventListener("click", function(e) {
                e.preventDefault();
                let parent = this.closest(".dropdown");
                parent.classList.toggle("active");
            });
        });

        // Swiper Logic
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                768: { slidesPerView: 2 },
                1024: { slidesPerView: 3 },
            },
        });
    </script>
</body>
</html>