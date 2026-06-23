<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection(); 

$error = "";
$success = "";
$message = ""; 

if (isset($_POST['book_table'])) {
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];
    $venue = $_POST['venue'];
    $res_date = $_POST['res_date'];
    $time_slot = $_POST['time_slot'];
    $guests = $_POST['guests'];
    $special_requests = $_POST['special_requests'] ?? '';

    $sql = "INSERT INTO table_reservations (full_name, phone_number, venue, reservation_date, time_slot, guests_count, special_requests) 
            VALUES (:full_name, :phone_number, :venue, :res_date, :time_slot, :guests, :special_requests)";

    $stmt = $db->prepare($sql);

    $execution_result = $stmt->execute([
        ':full_name' => $full_name,
        ':phone_number' => $phone_number,
        ':venue' => $venue,
        ':res_date' => $res_date,
        ':time_slot' => $time_slot,
        ':guests' => $guests,
        ':special_requests' => $special_requests
    ]);

    if ($execution_result) {
        $message = "Table reserved successfully!";
    } else {
        $errorInfo = $stmt->errorInfo();
        $message = "Error: " . $errorInfo[2];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuah Cafe - Tempahan Meja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fdfaf8; margin: 0; padding: 0; overflow-x: hidden; }
        
        /* --- HEADER NAVIGATION STYLE --- */
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
            width: 100%;
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

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .header-right { display: none; } 
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

    <div class="py-12 px-4 max-w-5xl mx-auto min-h-[60vh]">
        
        <div class="text-center space-y-2 mb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-[#5c3d1e] tracking-wider uppercase">Tempahan Meja</h1>
            <p class="text-sm md:text-base font-medium text-gray-500">Tempah meja untuk pengalaman bersantap yang terbaik</p>
        </div>

        <?php if(!empty($message)): ?>
            <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-6 py-4 rounded-[1.5rem] font-medium shadow-sm">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 p-8 md:p-14 space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wide text-gray-700 mb-2">Nama Penuh *</label>
                    <input type="text" name="full_name" placeholder="e.g. John Doe" required
                        class="w-full px-5 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#5c3d1e] focus:ring-2 focus:ring-[#5c3d1e]/10 text-gray-700 font-medium transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wide text-gray-700 mb-2">No Telefon *</label>
                    <input type="tel" name="phone_number" placeholder="e.g. 012-345 6789" required
                        class="w-full px-5 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#5c3d1e] focus:ring-2 focus:ring-[#5c3d1e]/10 text-gray-700 font-medium transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wide text-gray-700 mb-2">Pilih Acara *</label>
                    <select name="venue" required
                        class="w-full px-5 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#5c3d1e] focus:ring-2 focus:ring-[#5c3d1e]/10 text-gray-700 font-medium transition-all">
                        <option value="" disabled selected>Pilih Cawangan</option>
                        <option value="Tuah Cafe Eco Majestic">Tuah Cafe Eco Majestic</option>
                        <option value="Tuah Cafe Bangi">Tuah Cafe Bangi</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-wide text-gray-700 mb-2">Tarikh *</label>
                    <input type="date" name="res_date" required
                        class="w-full px-5 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#5c3d1e] focus:ring-2 focus:ring-[#5c3d1e]/10 text-gray-700 font-medium transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wide text-gray-700 mb-2">Masa *</label>
                    <select name="time_slot" required
                        class="w-full px-5 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#5c3d1e] focus:ring-2 focus:ring-[#5c3d1e]/10 text-gray-700 font-medium transition-all">
                        <option value="" disabled selected>Pilih slot masa</option>
                        <option value="12:00 PM - 02:00 PM">12:00 PM - 02:00 PM</option>
                        <option value="02:00 PM - 04:00 PM">02:00 PM - 04:00 PM</option>
                        <option value="06:00 PM - 08:00 PM">06:00 PM - 08:00 PM</option>
                        <option value="08:00 PM - 10:00 PM">08:00 PM - 10:00 PM</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase tracking-wide text-gray-700 mb-2">Tetamu</label>
                    <select name="guests" required
                        class="w-full px-5 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#5c3d1e] focus:ring-2 focus:ring-[#5c3d1e]/10 text-gray-700 font-medium transition-all">
                        <option value="" disabled selected>Jumlah orang?</option>
                        <option value="1-2 Pax">1-2 Pax</option>
                        <option value="3-4 Pax">3-4 Pax</option>
                        <option value="5-6 Pax">5-6 Pax</option>
                        <option value="7+ Pax">7+ Pax (Event Scale)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-wide text-gray-700 mb-2">Permintaan istimewa (Pilihan)</label>
                <textarea name="special_requests" rows="4" placeholder="Kerusi bayi..."
                    class="w-full px-5 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#5c3d1e] focus:ring-2 focus:ring-[#5c3d1e]/10 text-gray-700 font-medium resize-none transition-all"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" name="book_table" 
                    class="w-full py-4 bg-[#5c3d1e] hover:opacity-90 text-white font-bold rounded-xl transition-all tracking-wide shadow-lg flex items-center justify-center gap-2 text-sm uppercase">
                    <svg class="w-4 h-4 text-white/90" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                    Tempah Sekarang
                </button>
            </div>

        </form>

        <p class="text-center text-xs font-semibold text-gray-400 tracking-wide pt-6">
            &copy; 2026 - Semua maklumat adalah sulit dan peribadi
        </p>
    </div>

    <footer class="w-full bg-gradient-to-r from-[#6b3f2a] to-[#4b2e1e] text-[#eee] mt-12 pt-16 pb-8 px-6 md:px-12 border-t border-amber-900/20">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 pb-12 border-b border-white/10">
            <div class="space-y-4">
                <h2 class="text-2xl font-bold text-[#d4a24c] tracking-wide uppercase">Tuah Cafe</h2>
                <p class="text-sm leading-relaxed opacity-80">
                    Tempat di mana tradisi kulinari bertemu inovasi! Dari permulaan kecil hingga menjadi nama 
					yang dikenali dalam industri makanan dan minuman, kisah kami adalah tentang semangat, ketahanan, 
					dan keinginan untuk terus memberi yang terbaik kepada komuniti.
                </p>
            </div>
            
            <div>
                <h2 class="text-2xl font-bold text-[#d4a24c] tracking-wide uppercase">Pautan Pantas</h2>
                <ul>
                    <li><a href="Tentang_Kami.html">Tentang Kami</a></li>
                    <li><a href="Menu_fyp2.html">Menu</a></li>
                    <li><a href="Promotion.html">Promosi</a></li>
                    <li><a href="Keahlian.html">Keahlian</a></li>
                    <li><a href="Perkhidmatan.html">Perkhidmatan</a></li>
                    <li><a href="galeriacara.html">Galeri</a></li>
                    <li><a href="HubungiKami.html">Hubungi Kami</a></li>
                    <li><a href="Kerjaya.html">Kerjaya</a></li>
					</ul>
            </div>
            
            <div class="space-y-4">
                <h2 class="text-2xl font-bold text-[#d4a24c] tracking-wide uppercase">Maklumat Perhubungan</h2>
                <div class="space-y-3 text-sm opacity-85">
                    <p class="flex items-start gap-2"><span>📍</span><span>No.22 Jalan 7/1C, Seksyen 7 Bandar Baru Bangi, Selangor</span></p>
                    <p class="flex items-start gap-2"><span>📍</span><span>42, Jalan Eco Majestic 10/1D, Semenyih, Selangor</span></p>
                    <p class="flex items-center gap-2"><span>📞</span><span>03-8912 8798</span></p>
                    <p class="flex items-center gap-2"><span>📩</span><span>admin@tuahcafe.com</span></p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center pt-8 text-xs font-medium opacity-75 gap-4">
            <div>
                <p>© 2026 Tuah Cafe. Hak cipta terpelihara.</p>
            </div>
            <div class="flex gap-6 text-xl">
                <a href="https://www.instagram.com/tuahcafe" target="_blank" class="hover:text-[#d4a24c] transition-colors"><i class="fab fa-instagram"></i></a>
                <a href="https://www.facebook.com/tuahcafe" target="_blank" class="hover:text-[#d4a24c] transition-colors"><i class="fab fa-facebook"></i></a>
                <a href="https://www.tiktok.com/@tuahcafe" target="_blank" class="hover:text-[#d4a24c] transition-colors"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // Header Navigation Dropdown Toggle
        document.querySelector('.dropdown-trigger')?.addEventListener('click', function(e) {
            e.stopPropagation();
            this.parentElement.classList.toggle('active');
        });
        window.addEventListener('click', function() {
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('active'));
        });
    </script>
</body>
</html>