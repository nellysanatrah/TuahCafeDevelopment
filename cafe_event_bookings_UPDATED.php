<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_booking'])) {
    try {
        // Basic Info
        $full_name = $_POST['full_name'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $membership_status = $_POST['membership'] ?? '';
        $event_type = $_POST['event_type'] ?? '';
        $event_name = $_POST['event_name'] ?? '';
        $event_date = $_POST['event_date'] ?? '';

        $start_time = ($_POST['start_h'] ?? '00') . ":" . ($_POST['start_m'] ?? '00') . ":00";
        $end_time = ($_POST['end_h'] ?? '00') . ":" . ($_POST['end_m'] ?? '00') . ":00";

        $venue = $_POST['venue'] ?? '';
        $package_selected = $_POST['package'] ?? '';
        $pax_count = $_POST['pax'] ?? 0;

        $menu_items = "";
        if(isset($_POST['menu']) && is_array($_POST['menu'])) {
            foreach($_POST['menu'] as $category => $selection) {
                if(!empty($selection)) {
                    $menu_items .= "$category: $selection, ";
                }
            }
        }
        $menu_items = rtrim($menu_items, ", ");

        $addons_array = [];

        if(isset($_POST['dt_visible']) && $_POST['dt_visible'] == 'Yes') {
            $dessert_option = $_POST['dt_selection'] ?? 'None Selected';
            $addons_array[] = "Dessert Table: $dessert_option";
        }

        if(isset($_POST['qty']) && is_array($_POST['qty'])) {
            foreach($_POST['qty'] as $item => $q) {
                if($q > 0) {
                    $addons_array[] = "Pack: " . str_replace('_', ' ', $item) . " ($q)";
                }
            }
        }

        if(isset($_POST['qty_cat']) && is_array($_POST['qty_cat'])) {
            foreach($_POST['qty_cat'] as $item => $q) {
                if($q > 0) {
                    $addons_array[] = "Catering: " . str_replace('_', ' ', $item) . " ($q sets)";
                }
            }
        }

        if(isset($_POST['qty_drinks']) && is_array($_POST['qty_drinks'])) {
            foreach($_POST['qty_drinks'] as $item => $q) {
                if($q > 0) {
                    $addons_array[] = "Drink: " . str_replace('_', ' ', $item) . " ($q)";
                }
            }
        }

        if(isset($_POST['selected_cakes']) && is_array($_POST['selected_cakes'])) {
            $cakes = implode(', ', $_POST['selected_cakes']);
            $addons_array[] = "Cakes: $cakes";
        }

        if(!empty($_POST['cake_remark'])) {
            $addons_array[] = "Cake Writing: " . $_POST['cake_remark'];
        }

        $final_addons_summary = implode(" | ", $addons_array);
        
        $special_remarks = $_POST['special_remarks'] ?? '';

        $sql = "INSERT INTO event_bookings (
            full_name, phone_number, membership_status, event_type, 
            event_name, event_date, start_time, end_time, 
            venue, package_selected, pax_count, menu_details, 
            addons_details, special_remarks, created_at, is_read
        ) VALUES (
            :full_name, :phone_number, :membership_status, :event_type, 
            :event_name, :event_date, :start_time, :end_time, 
            :venue, :package_selected, :pax_count, :menu_details, 
            :addons_details, :special_remarks, NOW(), 0
        )";

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':membership_status', $membership_status);
        $stmt->bindParam(':event_type', $event_type);
        $stmt->bindParam(':event_name', $event_name);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':venue', $venue);
        $stmt->bindParam(':package_selected', $package_selected);
        $stmt->bindParam(':pax_count', $pax_count);
        $stmt->bindParam(':menu_details', $menu_items);
        $stmt->bindParam(':addons_details', $final_addons_summary);
        $stmt->bindParam(':special_remarks', $special_remarks);

        if($stmt->execute()) {
            $success = "Booking submitted successfully!";
        } else {
            $error = "Failed to submit booking.";
        }

    } catch(PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuah Cafe - Sewa Hall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fdfaf8; margin: 0; padding: 0; overflow-x: hidden; }
        .step-tab { border-bottom: 4px solid transparent; transition: 0.3s; color: #94a3b8; cursor: pointer; }
        .step-active { border-bottom: 4px solid #5c3d1e; color: #1a1a1a !important; font-weight: 800; }
        .form-input { width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem; background: #fff; outline: none; transition: all 0.2s; }
        .form-input:focus { border-color: #5c3d1e; box-shadow: 0 0 0 3px rgba(92, 61, 30, 0.1); }
        .hidden-step { display: none !important; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .pkg-content { background: #fefaf5; border-radius: 1rem; padding: 1.5rem; margin-top: 1rem; }

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
        .dropdown-content a { display: block; padding: 10px 16px; text-transform: none; }
        .dropdown.active .dropdown-content { display: block; }
		
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

        /* Responsif */
        @media (max-width: 768px) {
            .grid-container { grid-template-columns: 1fr; }
            .footer-container { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; gap: 20px; text-align: center; }
            .visit-container { flex-direction: column; }
            .visit-image { width: 100%; height: 300px; }
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
            <a href="Tentang_Kami.html">Tentang Kami</a>
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

    <div class="py-12 px-4">
        
        <div class="max-w-5xl mx-auto mb-6 bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <span class="text-sm font-bold text-gray-800">Proses Tempahan</span>
                <span id="step-counter-text" class="text-sm font-bold text-gray-500">Langkah 1/5</span>
            </div>
            <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                <div id="progress-fill" class="h-full bg-[#c59d5f] rounded-full" style="width: 20%;"></div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden">
            
            <div class="flex border-b text-center bg-gray-50/50">
                <div class="step-tab flex-1 py-6 text-[10px] font-bold uppercase tracking-widest step-active" data-step="1">1. Profil</div>
                <div class="step-tab flex-1 py-6 text-[10px] font-bold uppercase tracking-widest" data-step="2">2. Acara</div>
                <div class="step-tab flex-1 py-6 text-[10px] font-bold uppercase tracking-widest" data-step="3">3. Pakej Acara</div>
                <div class="step-tab flex-1 py-6 text-[10px] font-bold uppercase tracking-widest" data-step="4">4. Tambahan</div>
                <div class="step-tab flex-1 py-6 text-[10px] font-bold uppercase tracking-widest" data-step="5">5. Akhir</div>
            </div>

            <?php if($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl m-4">
                ✅ <?= $success ?>
            </div>
            <?php endif; ?>

            <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl m-4">
                ❌ <?= $error ?>
            </div>
            <?php endif; ?>

            <form id="bookingForm" method="POST" action="">
                <div class="p-8 md:p-12">
                   
                    <div class="form-step animate-fade-in" id="step-1">
                        <div class="max-w-3xl mx-auto">
                            <h3 class="text-xl font-extrabold text-[#5c3d1e] mb-6 uppercase border-b pb-2">Profil Pelanggan</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black uppercase mb-2">Nama Penuh*</label>
                                    <input type="text" name="full_name" required class="form-input" placeholder="Full Name">
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase mb-2">Telefon *</label>
                                    <div class="flex">
                                        <span class="bg-gray-100 px-4 flex items-center rounded-l-xl text-xs font-bold border border-r-0 text-gray-500">MY +60</span>
                                        <input type="tel" name="phone_number" pattern="[0-9]{9,11}" required class="form-input rounded-l-none" placeholder="123456789">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase mb-2">Ahli *</label>
                                    <select name="membership" required class="form-input">
                                        <option value="">Pilih Status</option>
                                        <option value="Not a Member">Bukan Ahli</option>
                                        <option value="Normal">Biasa</option>
                                        <option value="Bronze">Gangsa</option>
                                        <option value="Silver">Perak</option>
                                        <option value="Gold">Emas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-step hidden-step animate-fade-in space-y-6" id="step-2">
                        <div class="max-w-3xl mx-auto">
                            <h3 class="text-xl font-extrabold text-[#5c3d1e] mb-6 uppercase border-b pb-2">Informasi Acara</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black uppercase mb-2">Jenis Acara *</label>
                                    <select name="event_type" required class="form-input">
                                        <option value="">Pilih Jenis Acara</option>
                                        <option value="Birthday Party">Hari Lahir</option>
                                        <option value="Engagement Ceremony">Pertunangan</option>
                                        <option value="Gathering">Acara Berkumpul</option>
                                        <option value="Annual Dinner">Makan Malam Tahunan</option>
                                        <option value="Annual Grand Meeting">Mesyuarat Agung Tahunan</option>
                                        <option value="Award Day">Hari Anugerah</option>
                                        <option value="Seminars">Seminar</option>
                                        <option value="Meeting">Mesyuarat</option>
                                        <option value="Product Launch">Pelancaran Produk</option>
                                        <option value="Baby Shower">Majlis Kesyukuran Kelahiran Bayi</option>
                                        <option value="Aqiqah">Akikah</option>
                                        <option value="Others">Lain-lain</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase mb-2">Nama Acara *</label>
                                    <input type="text" name="event_name" required class="form-input" placeholder="e.g. Sarah's 21st Birthday">
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase mb-2">Tarikh Acara *</label>
                                    <input type="date" name="event_date" required class="form-input">
                                </div>
                                <div class="hidden md:block"></div>
                                <div>
                                    <label class="block text-xs font-bold mb-1">Masa Bermula *</label>
                                    <div class="flex gap-2">
                                        <select name="start_h" class="form-input text-xs">
                                            <?php for($i=9;$i<=20;$i++) echo "<option value='".sprintf("%02d",$i)."'>".sprintf("%02d",$i)."</option>"; ?>
                                        </select>
                                        <select name="start_m" class="form-input text-xs">
                                            <option value="00">00</option>
                                            <option value="30">30</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold mb-1">Masa Tamat *</label>
                                    <div class="flex gap-2">
                                        <select name="end_h" class="form-input text-xs">
                                            <?php for($i=12;$i<=23;$i++) echo "<option value='".sprintf("%02d",$i)."'>".sprintf("%02d",$i)."</option>"; ?>
                                        </select>
                                        <select name="end_m" class="form-input text-xs">
                                            <option value="00">00</option>
                                            <option value="30">30</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="md:col-span-2 p-4 bg-amber-50 rounded-xl text-[10px] font-bold text-amber-900 border border-amber-200 uppercase leading-relaxed">
                                    Tempoh acara adalah selama 3 jam. RM100/jam (Eco Majestic) | RM50/jam (Bangi).
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase mb-2">Lokasi Acara *</label>
                                    <select name="venue" required class="form-input">
                                        <option value="">Pilih Lokasi Acara</option>
                                        <option value="Tuah Cafe Bangi">Tuah Cafe Bangi</option>
                                        <option value="Tuah Cafe Eco Majestic">Tuah Cafe Eco Majestic</option>
                                        <option value="Offsite Catering">Katering di Luar Premis; Di lokasi anda</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase mb-2">Bilangan Tetamu *</label>
                                    <input type="number" name="pax" min="30" max="35" required class="form-input" placeholder="min 30, max 35">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-black uppercase mb-2">Pakej *</label>
                                    <select name="package" id="pkgSelect" required class="form-input font-bold border-2 border-[#5c3d1e]/20" onchange="updateMenuSection()">
                                        <option value="">Pilih Pakej</option>
                                        <option value="A">Pakej A - RM49.90</option>
                                        <option value="B">Pakej B - RM45.90</option>
                                        <option value="C">Pakej C - RM39.90</option>
                                        <option value="D">Pakej D - RM35.90</option>
                                        <option value="Space">Sewaan Ruang Sahaja</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-step hidden-step space-y-6" id="step-3">
                        <div class="max-w-3xl mx-auto">
                            <div id="pkg-A" class="pkg-content hidden">
                                <h3 class="font-black text-[#5c3d1e] border-b pb-2 uppercase">Pakej A - RM 49.90</h3>
                                <div class="space-y-4 mt-4">
                                    <p class="text-xs font-bold text-gray-500 italic">TERMASUK: Kambing Bakar dengan Sayur-sayuran Panggang</p>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Hidangan Utama (Spageti atau Nasi) *</label>
                                        <select name="menu[A_Main]" class="form-input">
                                            <option value="Spicy Beef Olio">Spicy Beef Olio</option>
                                            <option value="Bolognese">Bolognese</option>
                                            <option value="Carbonara">Carbonara</option>
                                            <option value="Pesto">Pesto</option>
                                            <option value="Butter Chicken Rice">Butter Chicken Rice</option>
                                            <option value="Chicken Balado Rice">Chicken Balado Rice</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Daging Pilihan Anda *</label>
                                        <select name="menu[A_Meat]" class="form-input">
                                            <option value="Grilled Chicken with Grilled Vegetables">Ayam Panggang dengan Sayur-sayuran Panggang</option>
                                            <option value="Chicken Chop">Chicken Chop</option>
                                            <option value="Fish & Chips">Fish & Chips</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Salad Pilihan Anda *</label>
                                        <select name="menu[A_Salad]" class="form-input">
                                            <option value="Coleslaw">Coleslaw</option>
                                            <option value="Garden Salad">Garden Salad</option>
                                            <option value="Fruit Salad">Fruit Salad</option>
                                        </select>
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                                        <p class="text-[10px] font-black uppercase text-gray-400">Pembuka Selera Termasuk:</p>
                                        <p class="text-xs font-bold text-gray-700">Mushroom Soup with Garlic Bread, Mini Crazy Nachos, Mashed Potato with Black Pepper Sauce</p>
                                        <p class="text-[10px] font-black uppercase text-gray-400 mt-2">Minuman Termasuk:</p>
                                        <p class="text-xs font-bold text-gray-700">Hot Thai Milk Tea, Sirap Pandan with Selasih</p>
                                        <p class="text-[10px] font-black uppercase text-gray-400 mt-2">Desserts Included:</p>
                                        <p class="text-xs font-bold text-gray-700">Fruit Platter, Ice Cream Sundae</p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Pencuci Mulut *</label>
                                        <select name="menu[A_Dessert]" class="form-input">
                                            <option value="Brownies">Brownies</option>
                                            <option value="Bread Pudding">Bread Pudding</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="pkg-B" class="pkg-content hidden">
                                <h3 class="font-black text-[#5c3d1e] border-b pb-2 uppercase">Pakej B - RM 45.90</h3>
                                <div class="space-y-4 mt-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Hidangan Utama (Spageti atau Nasi) *</label>
                                        <select name="menu[B_Main]" class="form-input">
                                            <option value="Spicy Beef Olio">Spicy Beef Olio</option>
                                            <option value="Bolognese">Bolognese</option>
                                            <option value="Carbonara">Carbonara</option>
                                            <option value="Pesto">Pesto</option>
                                            <option value="Butter Chicken Rice">Butter Chicken Rice</option>
                                            <option value="Chicken Balado Rice">Chicken Balado Rice</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Daging Pilihan Anda *</label>
                                        <select name="menu[B_Meat]" class="form-input">
                                            <option value="Grilled Chicken with Grilled Vegetables">Ayam Panggang dengan Sayur-sayuran Panggang</option>
                                            <option value="Chicken Chop">Chicken Chop</option>
                                            <option value="Fish & Chips">Fish & Chips</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Salad Pilihan Anda *</label>
                                        <select name="menu[B_Salad]" class="form-input">
                                            <option value="Coleslaw">Coleslaw</option>
                                            <option value="Garden Salad">Garden Salad</option>
                                            <option value="Fruit Salad">Fruit Salad</option>
                                        </select>
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                                        <p class="text-[10px] font-black uppercase text-gray-400">Pembuka Selera Termasuk:</p>
                                        <p class="text-xs font-bold text-gray-700">Mushroom Soup with Garlic Bread, Mini Crazy Nachos, Mashed Potato with Black Pepper Sauce</p>
                                        <p class="text-[10px] font-black uppercase text-gray-400 mt-2">Drinks Included:</p>
                                        <p class="text-xs font-bold text-gray-700">Hot Thai Milk Tea, Sirap Pandan with Selasih</p>
                                        <p class="text-[10px] font-black uppercase text-gray-400 mt-2">Desserts Included:</p>
                                        <p class="text-xs font-bold text-gray-700">Fruit Platter, Ice Cream Sundae</p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Pencuci Mulut *</label>
                                        <select name="menu[B_Dessert]" class="form-input">
                                            <option value="Brownies">Brownies</option>
                                            <option value="Bread Pudding">Bread Pudding</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="pkg-C" class="pkg-content hidden">
                                <h3 class="font-black text-[#5c3d1e] border-b pb-2 uppercase">Pakej C - RM 39.90</h3>
                                <div class="space-y-4 mt-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Hidangan Utama (Spageti atau Nasi) *</label>
                                        <select name="menu[C_Main]" class="form-input">
                                            <option value="Spicy Beef Olio">Spicy Beef Olio</option>
                                            <option value="Bolognese">Bolognese</option>
                                            <option value="Carbonara">Carbonara</option>
                                            <option value="Pesto">Pesto</option>
                                            <option value="Butter Chicken Rice">Butter Chicken Rice</option>
                                            <option value="Chicken Balado Rice">Chicken Balado Rice</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Salad Pilihan Anda *</label>
                                        <select name="menu[C_Salad]" class="form-input">
                                            <option value="Coleslaw">Coleslaw</option>
                                            <option value="Garden Salad">Garden Salad</option>
                                            <option value="Fruit Salad">Fruit Salad</option>
                                        </select>
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                                        <p class="text-[10px] font-black uppercase text-gray-400">Pembuka Selera Termasuk:</p>
                                        <p class="text-xs font-bold text-gray-700">BBQ Chicken Wings, Mushroom Soup with Garlic Bread, Mashed Potato with Black Pepper Sauce</p>
                                        <p class="text-[10px] font-black uppercase text-gray-400 mt-2">Drinks Included:</p>
                                        <p class="text-xs font-bold text-gray-700">Hot Thai Milk Tea, Sirap Pandan with Selasih</p>
                                        <p class="text-[10px] font-black uppercase text-gray-400 mt-2">Desserts Included:</p>
                                        <p class="text-xs font-bold text-gray-700">Fruit Platter, Ice Cream Sundae</p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Pembuka Selera Pilihan Anda *</label>
                                        <select name="menu[C_Appetizer]" class="form-input">
                                            <option value="Beef Meatball with Grilled Vegetables">Beef Meatball with Grilled Vegetables</option>
                                            <option value="Cheezy Chicken Popcorn">Cheezy Chicken Popcorn</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Pencuci Mulut *</label>
                                        <select name="menu[C_Dessert]" class="form-input">
                                            <option value="Brownies">Brownies</option>
                                            <option value="Bread Pudding">Bread Pudding</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="pkg-D" class="pkg-content hidden">
                                <h3 class="font-black text-[#5c3d1e] border-b pb-2 uppercase">Pakej D - RM 35.90</h3>
                                <div class="space-y-4 mt-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Hidangan Utama *</label>
                                        <select name="menu[D_Main]" class="form-input">
                                            <option value="Mac & Cheese">Mac & Cheese</option>
                                            <option value="Spicy Beef Olio">Spicy Beef Olio</option>
                                            <option value="Bolognese">Bolognese</option>
                                            <option value="Carbonara">Carbonara</option>
                                            <option value="Pesto">Pesto</option>
                                            <option value="Butter Chicken Rice">Butter Chicken Rice</option>
                                            <option value="Chicken Balado Rice">Chicken Balado Rice</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Salad Pilihan Anda *</label>
                                        <select name="menu[D_Salad]" class="form-input">
                                            <option value="Coleslaw">Coleslaw</option>
                                            <option value="Garden Salad">Garden Salad</option>
                                            <option value="Fruit Salad">Fruit Salad</option>
                                        </select>
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                                        <p class="text-[10px] font-black uppercase text-gray-400">Pembuka Selera Termasuk:</p>
                                        <p class="text-xs font-bold text-gray-700">Mushroom Soup with Garlic Bread, Mini Crazy Nachos, Mashed Potato with Black Pepper Sauce</p>
                                        <p class="text-[10px] font-black uppercase text-gray-400 mt-2">Drinks Included:</p>
                                        <p class="text-xs font-bold text-gray-700">Hot Thai Milk Tea, Sirap Pandan with Selasih</p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Pembuka Selera Pilihan Anda *</label>
                                        <select name="menu[D_Appetizer]" class="form-input">
                                            <option value="Beef Meatball with Grilled Vegetables">Beef Meatball with Grilled Vegetables</option>
                                            <option value="Cheezy Chicken Popcorn">Cheezy Chicken Popcorn</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Pencuci Mulut *</label>
                                        <select name="menu[D_Dessert]" class="form-input">
                                            <option value="Brownies">Brownies</option>
                                            <option value="Bread Pudding">Bread Pudding</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="pkg-Space" class="pkg-content hidden">
                                <h3 class="font-black text-[#5c3d1e] border-b pb-2 uppercase">Sewaan Ruang Sahaja</h3>
                                <div class="space-y-4 mt-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Adakah anda membawa makanan dari luar? *</label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="menu[Space_OutsideFood]" value="Yes" class="w-4 h-4 accent-[#5c3d1e]">
                                                <span class="text-xs font-bold text-gray-700">Ya</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="menu[Space_OutsideFood]" value="No" class="w-4 h-4 accent-[#5c3d1e]" checked>
                                                <span class="text-xs font-bold text-gray-700">Tidak</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Sewaan Peralatan *</label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="menu[Space_EquipRental]" value="Yes" class="w-4 h-4 accent-[#5c3d1e]">
                                                <span class="text-xs font-bold text-gray-700">Ya</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="menu[Space_EquipRental]" value="No" class="w-4 h-4 accent-[#5c3d1e]" checked>
                                                <span class="text-xs font-bold text-gray-700">Tidak</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="p-6 bg-gray-50 rounded-2xl space-y-4 border border-gray-100">
                                        <div class="space-y-2">
                                            <p class="text-[10px] font-black uppercase text-[#5c3d1e]">Caj Tambahan:</p>
                                            <ul class="text-[11px] font-bold text-gray-600 list-disc pl-4 space-y-1">
                                                <li>Masa Lebih: RM 100/jam (Eco Majestic) | RM 50/jam (Bangi)</li>
                                                <li>Barangan/peralatan tambahan akan dikenakan caj secara berasingan.</li>
                                                <li>Hiasan tambahan untuk acara akan dikenakan caj secara berasingan.</li>
                                            </ul>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-200">
                                            <div class="space-y-1">
                                                <p class="text-[10px] font-black uppercase text-gray-400">Tuah Cafe Bangi</p>
                                                <p class="text-[11px] font-bold text-gray-700">11:30 AM - 12:00 AM</p>
                                                <p class="text-[10px] font-bold text-red-500 uppercase">Tutup Khamis</p>
                                                <p class="text-[11px] font-bold text-gray-500">03-8912 8798</p>
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-[10px] font-black uppercase text-gray-400">Tuah Cafe Eco Majestic</p>
                                                <p class="text-[11px] font-bold text-gray-700">Hari Bekerja: 5:30 PM - 11:00 PM</p>
                                                <p class="text-[11px] font-bold text-gray-700">Sabtu-Ahad: 2:00 PM - 11:00 PM</p>
                                                <p class="text-[10px] font-bold text-red-500 uppercase">Tutup Khamis</p>
                                                <p class="text-[11px] font-bold text-gray-500">03-8724 0240</p>
                                            </div>
                                        </div>
                                        <div class="pt-2">
                                            <p class="text-[10px] italic text-gray-400">*Pengaturan vendor luar hanya dibenarkan semasa waktu operasi yang dinyatakan di atas.</p>
                                        </div>
                                    </div>
                                    <label class="flex items-start gap-3 cursor-pointer group">
                                        <input type="checkbox" required class="mt-1 w-4 h-4 accent-[#5c3d1e]" checked>
                                        <span class="text-[11px] font-bold text-gray-500 group-hover:text-gray-700 transition-colors">
                                            Saya telah membaca dan memahami semua peraturan dan syarat yang dinyatakan di atas.
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-step hidden-step" id="step-4">
                        <div class="p-8 md:p-12 space-y-10">
                            <h3 class="text-xl font-extrabold text-[#5c3d1e] border-b pb-2 uppercase text-center">Langkah 4: Pemilihan Tambahan</h3>
                            
                            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                                <h3 class="text-sm font-black uppercase text-[#5c3d1e] mb-6 tracking-widest border-l-4 border-[#5c3d1e] pl-4">Meja Pencuci Mulut Tambahan</h3>
                                <div class="flex gap-6 mb-8">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="dt_visible" value="Yes" class="w-5 h-5 accent-[#5c3d1e]" onclick="toggleDessertTable(true)">
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Ya</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="dt_visible" value="No" class="w-5 h-5 accent-[#5c3d1e]" onclick="toggleDessertTable(false)" checked>
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Tidak</span>
                                    </label>
                                </div>
                                
                                <div id="dessert_table_options" class="hidden space-y-6">
                                    <p class="text-[10px] font-black uppercase text-gray-400 mb-4 italic">Sila pilih satu pilihan:</p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <label class="relative cursor-pointer group">
                                            <input type="radio" name="dt_selection" value="Option A" class="hidden peer">
                                            <div class="overflow-hidden rounded-2xl border-2 border-gray-100 peer-checked:border-[#5c3d1e] peer-checked:ring-4 peer-checked:ring-[#5c3d1e]/10 transition-all">
                                                <img src="pakejA.png" alt="Dessert Table A" class="w-full h-48 object-cover">
                                                <div class="p-4 bg-white text-center">
                                                    <span class="block text-xs font-black uppercase tracking-tight"> Pilihan A</span>
                                                    <span class="text-sm font-bold text-[#5c3d1e]">RM 190.00</span>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="relative cursor-pointer group">
                                            <input type="radio" name="dt_selection" value="Option B" class="hidden peer">
                                            <div class="overflow-hidden rounded-2xl border-2 border-gray-100 peer-checked:border-[#5c3d1e] peer-checked:ring-4 peer-checked:ring-[#5c3d1e]/10 transition-all">
                                                <img src="pakejB.png" alt="Dessert Table B" class="w-full h-48 object-cover">
                                                <div class="p-4 bg-white text-center">
                                                    <span class="block text-xs font-black uppercase tracking-tight">Pilihan B</span>
                                                    <span class="text-sm font-bold text-[#5c3d1e]">RM 290.00</span>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="relative cursor-pointer group">
                                            <input type="radio" name="dt_selection" value="Option C" class="hidden peer">
                                            <div class="overflow-hidden rounded-2xl border-2 border-gray-100 peer-checked:border-[#5c3d1e] peer-checked:ring-4 peer-checked:ring-[#5c3d1e]/10 transition-all">
                                                <img src="pakejC.png" alt="Dessert Table C" class="w-full h-48 object-cover">
                                                <div class="p-4 bg-white text-center">
                                                    <span class="block text-xs font-black uppercase tracking-tight">Pilihan C</span>
                                                    <span class="text-sm font-bold text-[#5c3d1e]">RM 390.00</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                                <h3 class="text-sm font-black uppercase text-[#5c3d1e] mb-6 tracking-widest border-l-4 border-[#5c3d1e] pl-4">Pakej Pesta Tambahan</h3>
                                <div class="flex gap-6 mb-8">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="party_visible" value="Yes" class="w-5 h-5 accent-[#5c3d1e]" onclick="togglePartyPack(true)">
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Ya</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="party_visible" value="No" class="w-5 h-5 accent-[#5c3d1e]" onclick="togglePartyPack(false)" checked>
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Tidak</span>
                                    </label>
                                </div>
                                <div id="party_pack_options" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black uppercase">Pakej Standard</p>
                                            <p class="text-xs font-bold text-[#5c3d1e]">RM 5.00 / pakej</p>
                                        </div>
                                        <input type="number" name="qty[Standard_Pack]" min="0" value="0" class="w-20 form-input text-center font-bold">
                                    </div>
                                    <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black uppercase">Pakej Premium</p>
                                            <p class="text-xs font-bold text-[#5c3d1e]">RM 8.00 / pakej</p>
                                        </div>
                                        <input type="number" name="qty[Premium_Pack]" min="0" value="0" class="w-20 form-input text-center font-bold">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                                <h3 class="text-sm font-black uppercase text-[#5c3d1e] mb-6 tracking-widest border-l-4 border-[#5c3d1e] pl-4">Hidangan Katering Tambahan</h3>
                                <div class="flex gap-6 mb-8">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="catering_visible" value="Yes" class="w-5 h-5 accent-[#5c3d1e]" onclick="toggleCatering(true)">
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Ya</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="catering_visible" value="No" class="w-5 h-5 accent-[#5c3d1e]" onclick="toggleCatering(false)" checked>
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Tidak</span>
                                    </label>
                                </div>
                                <div id="catering_options" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black uppercase">Mushroom Soup + Garlic Bread</p>
                                            <p class="text-xs font-bold text-[#5c3d1e]">RM 150.00 / 30 sets</p>
                                        </div>
                                        <input type="number" name="qty_cat[Mushroom_Soup_Garlic_Bread]" min="0" value="0" class="w-20 form-input text-center font-bold">
                                    </div>
                                    <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black uppercase">Nasi Goreng Kampung</p>
                                            <p class="text-xs font-bold text-[#5c3d1e]">RM 210.00 / 30 sets</p>
                                        </div>
                                        <input type="number" name="qty_cat[Nasi_Goreng_Kampung]" min="0" value="0" class="w-20 form-input text-center font-bold">
                                    </div>
                                    <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black uppercase">Spaghetti Bolognese</p>
                                            <p class="text-xs font-bold text-[#5c3d1e]">RM 240.00 / 30 sets</p>
                                        </div>
                                        <input type="number" name="qty_cat[Spaghetti_Bolognese]" min="0" value="0" class="w-20 form-input text-center font-bold">
                                    </div>
                                    <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black uppercase">Spaghetti Carbonara</p>
                                            <p class="text-xs font-bold text-[#5c3d1e]">RM 270.00 / 30 sets</p>
                                        </div>
                                        <input type="number" name="qty_cat[Spaghetti_Carbonara]" min="0" value="0" class="w-20 form-input text-center font-bold">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                                <h3 class="text-sm font-black uppercase text-[#5c3d1e] mb-6 tracking-widest border-l-4 border-[#5c3d1e] pl-4">Kek Hari Jadi Tambahan</h3>
                                <div class="flex gap-6 mb-8">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="cake_visible" value="Yes" class="w-5 h-5 accent-[#5c3d1e]" onclick="toggleCakes(true)">
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Ya</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="cake_visible" value="No" class="w-5 h-5 accent-[#5c3d1e]" onclick="toggleCakes(false)" checked>
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Tidak</span>
                                    </label>
                                </div>
                                <div id="cake_options" class="hidden space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer">
                                            <input type="checkbox" name="selected_cakes[]" value="Chocolate Indulgence (RM120)" class="w-4 h-4 accent-[#5c3d1e]">
                                            <span class="text-xs font-bold text-gray-700">Chocolate Indulgence (RM 120.00)</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer">
                                            <input type="checkbox" name="selected_cakes[]" value="Red Velvet Cake (RM140)" class="w-4 h-4 accent-[#5c3d1e]">
                                            <span class="text-xs font-bold text-gray-700">Red Velvet Cake (RM 140.00)</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black uppercase text-gray-400 mb-2">Catatan Tulisan Kek</label>
                                        <textarea name="cake_remark" rows="2" class="form-input resize-none" placeholder="e.g. Happy Birthday Sarah!"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                                <h3 class="text-sm font-black uppercase text-[#5c3d1e] mb-6 tracking-widest border-l-4 border-[#5c3d1e] pl-4">Minuman Tambahan Segar</h3>
                                <div class="flex gap-6 mb-8">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="drinks_visible" value="Yes" class="w-5 h-5 accent-[#5c3d1e]" onclick="toggleDrinks(true)">
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Ya</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="drinks_visible" value="No" class="w-5 h-5 accent-[#5c3d1e]" onclick="toggleDrinks(false)" checked>
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-[#5c3d1e]">Tidak</span>
                                    </label>
                                </div>
                                <div id="drinks_options" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black uppercase">Thai Milk Tea (Dispenser)</p>
                                            <p class="text-xs font-bold text-[#5c3d1e]">RM 70.00 / dispenser</p>
                                        </div>
                                        <input type="number" name="qty_drinks[Thai_Milk_Tea_Dispenser]" min="0" value="0" class="w-20 form-input text-center font-bold">
                                    </div>
                                    <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 flex justify-between items-center">
                                        <div>
                                            <p class="text-xs font-black uppercase">Green Tea (Dispenser)</p>
                                            <p class="text-xs font-bold text-[#5c3d1e]">RM 70.00 / dispenser</p>
                                        </div>
                                        <input type="number" name="qty_drinks[Green_Tea_Dispenser]" min="0" value="0" class="w-20 form-input text-center font-bold">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="form-step hidden-step animate-fade-in space-y-8" id="step-5">
                        <div class="max-w-3xl mx-auto">
                            <h3 class="text-xl font-extrabold text-[#5c3d1e] mb-6 uppercase border-b pb-2 text-center">Semak Ringkasan Tempahan</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 space-y-3">
                                    <p class="text-xs font-black uppercase text-[#5c3d1e] border-b pb-1">Profil Pelanggan</p>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">Nama:</span><span id="preview_name">-</span></div>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">Telefon:</span><span id="preview_phone">-</span></div>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">Ahli:</span><span id="preview_member">-</span></div>
                                </div>

                                <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 space-y-3">
                                    <p class="text-xs font-black uppercase text-[#5c3d1e] border-b pb-1">Butiran Acara</p>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">Jenis:</span><span id="preview_type">-</span></div>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">Nama Acara:</span><span id="preview_event_name">-</span></div>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">Tarikh:</span><span id="preview_date">-</span></div>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">Masa:</span><span id="preview_time">-</span></div>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">lokasi Acara:</span><span id="preview_venue">-</span></div>
                                    <div class="flex justify-between text-xs font-bold"><span class="text-gray-400">Bilangan Tetamu:</span><span id="preview_pax">-</span></div>
                                </div>

                                <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 space-y-3 md:col-span-2">
                                    <p class="text-xs font-black uppercase text-[#5c3d1e] border-b pb-1">Pakej Menu Pilihan</p>
                                    <div class="text-xs font-bold text-gray-700 space-y-2" id="preview_menu">Tiada pakej dipilih.</div>
                                </div>

                                <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 space-y-3 md:col-span-2">
                                    <p class="text-xs font-black uppercase text-[#5c3d1e] border-b pb-1">Konfigurasi Tambahan Add-on</p>
                                    <div class="text-xs font-bold text-gray-700 space-y-2" id="preview_addons">Tiada tambahan dipilih.</div>
                                </div>
                            </div>

                            <div class="space-y-2 pt-4">
                                <label class="block text-xs font-black uppercase mb-2">Catatan Khas / Arahan (Pilihan)</label>
                                <textarea name="special_remarks" rows="3" class="form-input resize-none" placeholder="Any specific requirements regarding the space arrangement or timeline..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="p-6 bg-gray-50 border-t flex justify-between items-center gap-4">
                    <button type="button" id="prevBtn" onclick="nextStep(-1)" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl text-xs uppercase tracking-wider transition-colors hidden">
                        Sebelum
                    </button>
                    <div class="flex-1"></div>
                    <button type="button" id="nextBtn" onclick="nextStep(1)" class="px-8 py-3 bg-[#5c3d1e] hover:opacity-90 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-opacity shadow-sm">
                        Seterusnya
                    </button>
                    <button type="submit" name="submit_booking" id="submitBtn" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors shadow-md hidden">
                        Hantar Permintaan Tempahan
                    </button>
                </div>
            </form>

        </div>
    </div>

    <footer class="w-full bg-gradient-to-r from-[#6b3f2a] to-[#4b2e1e] text-[#eee] mt-12 pt-16 pb-8 px-6 md:px-12 border-t border-amber-900/20">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 pb-12 border-b border-white/10">
            <div class="space-y-4">
                <h2 class="text-2xl font-bold text-[#d4a24c] tracking-wide uppercase">Tuah Cafe</h2>
                <p class="text-sm leading-relaxed opacity-80">
                    Tempat di mana tradisi kulinari bertemu inovasi! Dari permulaan kecil hingga menjadi nama yang 
					dikenali dalam industri makanan dan minuman, kisah kami adalah tentang semangat, ketahanan, 
					dan keinginan untuk terus memberi yang terbaik kepada komuniti.
                </p>
            </div>
            <div class="space-y-4 md:pl-12">
                <h2 class="text-2xl font-bold text-[#d4a24c] tracking-wide uppercase">Pautan Pantas</h2>
                <ul>
                    <li><a href="Tentang_Kami.html">Tentang Kami</a></li>
                    <li><a href="Menu_fyp2.html">Menu</a></li>
                    <li><a href="Promotion.html">Promosi</a></li>
                    <li><a href="Keahlian.html">Keahlian</a></li>
                    <li><a href="Perkhidmatan.html">Perkhidmatan</a></li>
                    <li><a href="galeriacara.html">Galeri</a></li>
                    <li><a href="HubungiKami.html">Hubungi Kami</a></li>
                    <li><a href="Job_Form.php">Kerjaya</a></li>
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
            <div><p>© 2026 Tuah Cafe. Hak cipta terpelihara.</p></div>
            <div class="flex gap-6 text-xl">
                <a href="https://www.instagram.com/tuahcafe" target="_blank" class="hover:text-[#d4a24c] transition-colors"><i class="fab fa-instagram"></i></a>
                <a href="https://www.facebook.com/tuahcafe" target="_blank" class="hover:text-[#d4a24c] transition-colors"><i class="fab fa-facebook"></i></a>
                <a href="https://www.tiktok.com/@tuahcafe" target="_blank" class="hover:text-[#d4a24c] transition-colors"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </footer>

    <script>
        document.querySelector('.dropdown-trigger')?.addEventListener('click', function(e) {
            e.stopPropagation();
            this.parentElement.classList.toggle('active');
        });
        window.addEventListener('click', function() {
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('active'));
        });

        let currentStep = 1;
        const totalSteps = 5;

        function showStep(step) {
            document.querySelectorAll(".form-step").forEach((el, index) => {
                if(index === step - 1) el.classList.remove("hidden-step");
                else el.classList.add("hidden-step");
            });

            document.querySelectorAll(".step-tab").forEach((tab, index) => {
                if(index === step - 1) tab.classList.add("step-active");
                else tab.classList.remove("step-active");
            });

            const percentage = (step / totalSteps) * 100;
            const fill = document.getElementById("progress-fill");
            if(fill) fill.style.width = percentage + "%";
            
            const counterText = document.getElementById("step-counter-text");
            if(counterText) counterText.innerText = `Langkah ${step}/${totalSteps}`;

            const prev = document.getElementById("prevBtn");
            const next = document.getElementById("nextBtn");
            const submit = document.getElementById("submitBtn");

            if(step === 1) prev.classList.add("hidden");
            else prev.classList.remove("hidden");

            if(step === totalSteps) {
                next.classList.add("hidden");
                submit.classList.remove("hidden");
                updateUI();
            } else {
                next.classList.remove("hidden");
                submit.classList.add("hidden");
            }
        }

        function nextStep(dir) {
            if (dir === 1) {
                const currentFormStep = document.getElementById(`step-${currentStep}`);
                // Hanya memvalidasi elemen input yang saat ini TERLIHAT dan aktif di layar
                const visibleInputs = Array.from(currentFormStep.querySelectorAll("[required]")).filter(input => {
                    return input.offsetWidth > 0 && input.offsetHeight > 0;
                });

                let isValid = true;
                visibleInputs.forEach(input => {
                    if(!input.checkValidity()) {
                        input.reportValidity();
                        isValid = false;
                    }
                });
                if (!isValid) return false;
            }

            currentStep += dir;
            if(currentStep < 1) currentStep = 1;
            if(currentStep > totalSteps) currentStep = totalSteps;
            showStep(currentStep);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.querySelectorAll(".step-tab").forEach(tab => {
            tab.addEventListener("click", function() {
                const targetStep = parseInt(this.getAttribute("data-step"));
                if(targetStep < currentStep) {
                    currentStep = targetStep;
                    showStep(currentStep);
                } else if(targetStep > currentStep) {
                    while(currentStep < targetStep) {
                        const currentFormStep = document.getElementById(`step-${currentStep}`);
                        const visibleInputs = Array.from(currentFormStep.querySelectorAll("[required]")).filter(input => {
                            return input.offsetWidth > 0 && input.offsetHeight > 0;
                        });
                        let isValid = true;
                        visibleInputs.forEach(input => {
                            if(!input.checkValidity()) {
                                input.reportValidity();
                                isValid = false;
                            }
                        });
                        if (!isValid) break;
                        currentStep++;
                    }
                    showStep(currentStep);
                }
            });
        });

        function updateMenuSection() {
            const pkg = document.getElementById("pkgSelect").value;
            document.getElementById("pkg-A").classList.add("hidden");
            document.getElementById("pkg-B").classList.add("hidden");
            document.getElementById("pkg-C").classList.add("hidden");
            document.getElementById("pkg-D").classList.add("hidden");
            document.getElementById("pkg-Space").classList.add("hidden");

            if(pkg) {
                const activeDiv = document.getElementById(`pkg-${pkg}`);
                if(activeDiv) activeDiv.classList.remove("hidden");
            }
        }

        function toggleDessertTable(show) { 
            const container = document.getElementById('dessert_table_options'); 
            if (show) container.classList.remove('hidden'); 
            else { 
                container.classList.add('hidden'); 
                container.querySelectorAll('input[name="dt_selection"]').forEach(r => r.checked = false); 
            } 
        }
        function togglePartyPack(show) { const container = document.getElementById('party_pack_options'); if (show) container.classList.remove('hidden'); else { container.classList.add('hidden'); container.querySelectorAll('input[type="number"]').forEach(i => i.value = 0); } }
        function toggleCatering(show) { const container = document.getElementById('catering_options'); if (show) { container.classList.remove('hidden'); container.classList.add('grid'); } else { container.classList.add('hidden'); container.classList.remove('grid'); container.querySelectorAll('input[type="number"]').forEach(i => i.value = 0); } }
        function toggleCakes(show) { const container = document.getElementById('cake_options'); if (show) container.classList.remove('hidden'); else { container.classList.add('hidden'); container.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false); container.querySelector('textarea').value = ''; } }
        function toggleDrinks(show) { const container = document.getElementById('drinks_options'); if (show) container.classList.remove('hidden'); else { container.classList.add('hidden'); container.querySelectorAll('input[type="number"]').forEach(i => i.value = 0); } }

        function updateUI() {
            document.getElementById("preview_name").innerText = document.querySelector('[name="full_name"]').value || "-";
            document.getElementById("preview_phone").innerText = document.querySelector('[name="phone_number"]').value || "-";
            document.getElementById("preview_member").innerText = document.querySelector('[name="membership"]').value || "-";
            document.getElementById("preview_type").innerText = document.querySelector('[name="event_type"]').value || "-";
            document.getElementById("preview_event_name").innerText = document.querySelector('[name="event_name"]').value || "-";
            document.getElementById("preview_date").innerText = document.querySelector('[name="event_date"]').value || "-";
            
            const startH = document.querySelector('[name="start_h"]').value;
            const startM = document.querySelector('[name="start_m"]').value;
            const endH = document.querySelector('[name="end_h"]').value;
            const endM = document.querySelector('[name="end_m"]').value;
            document.getElementById("preview_time").innerText = `${startH}:${startM} till ${endH}:${endM}`;

            document.getElementById("preview_venue").innerText = document.querySelector('[name="venue"]').value || "-";
            document.getElementById("preview_pax").innerText = (document.querySelector('[name="pax"]').value || "0") + " Pax";

            const pkg = document.getElementById("pkgSelect").value;
            let menuHTML = "";
            if(pkg && pkg !== "Space") {
                const container = document.getElementById(`pkg-${pkg}`);
                if(container) {
                    container.querySelectorAll("select").forEach(select => {
                        const label = select.previousElementSibling ? select.previousElementSibling.innerText.replace(' *','') : "Item";
                        menuHTML += `<div class="flex justify-between"><span class="text-gray-400">${label}:</span><span>${select.value}</span></div>`;
                    });
                }
            } else if(pkg === "Space") {
                const outsideFood = document.querySelector('[name="menu[Space_OutsideFood]"]:checked')?.value || "No";
                const equip = document.querySelector('[name="menu[Space_EquipRental]"]:checked')?.value || "No";
                menuHTML += `<div class="flex justify-between"><span class="text-gray-400">Outside Food:</span><span>${outsideFood}</span></div>`;
                menuHTML += `<div class="flex justify-between"><span class="text-gray-400">Equipment Rental:</span><span>${equip}</span></div>`;
            }
            document.getElementById("preview_menu").innerHTML = menuHTML || "No package item details configured.";

            let addonsHTML = "";
            const dtVisible = document.querySelector('[name="dt_visible"]:checked')?.value;
            if (dtVisible === "Yes") {
                const dtOption = document.querySelector('[name="dt_selection"]:checked')?.value || "None Selected";
                addonsHTML += `<div class="border-b border-gray-100 pb-1">Dessert Table: ${dtOption}</div>`;
            }

            document.querySelectorAll('[name^="qty["]').forEach(item => {
                if (parseInt(item.value) > 0) {
                    addonsHTML += `<div class="border-b border-gray-100 pb-1">${item.name.replace('qty[','').replace(']','').replaceAll('_',' ')} (${item.value} packs)</div>`;
                }
            });

            document.querySelectorAll('[name^="qty_cat["]').forEach(item => {
                if (parseInt(item.value) > 0) {
                    addonsHTML += `<div class="border-b border-gray-100 pb-1">${item.name.replace('qty_cat[','').replace(']','').replaceAll('_',' ')} (${item.value} sets)</div>`;
                }
            });

            document.querySelectorAll('[name^="qty_drinks["]').forEach(item => {
                if (parseInt(item.value) > 0) {
                    addonsHTML += `<div class="border-b border-gray-100 pb-1">${item.name.replace('qty_drinks[','').replace(']','').replaceAll('_',' ')} (${item.value})</div>`;
                }
            });

            document.querySelectorAll('[name="selected_cakes[]"]:checked').forEach(item => {
                addonsHTML += `<div class="border-b border-gray-100 pb-1">Cake: ${item.value}</div>`;
            });

            const cakeRemark = document.querySelector('[name="cake_remark"]').value;
            if (cakeRemark.trim() !== "") {
                addonsHTML += `<div class="border-b border-gray-100 pb-1">Cake Remark: ${cakeRemark}</div>`;
            }

            document.getElementById("preview_addons").innerHTML = addonsHTML || "No additional extra add-ons selected.";
        }

        showStep(currentStep);
        updateMenuSection();
    </script>
</body>
</html>