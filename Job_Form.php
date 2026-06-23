<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect all form data
    $formData = $_POST;
    
    // Handle file uploads
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Process resume file (compulsory)
    if (isset($_FILES['resume_document']) && $_FILES['resume_document']['error'] === UPLOAD_ERR_OK) {
        $resume_file = $upload_dir . time() . '_' . basename($_FILES['resume_document']['name']);
        if (move_uploaded_file($_FILES['resume_document']['tmp_name'], $resume_file)) {
            $formData['resume_document'] = $resume_file;
        }
    }
    
    // Process supporting document (optional)
    if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] === UPLOAD_ERR_OK) {
        $supporting_file = $upload_dir . time() . '_support_' . basename($_FILES['supporting_document']['name']);
        if (move_uploaded_file($_FILES['supporting_document']['tmp_name'], $supporting_file)) {
            $formData['supporting_document'] = $supporting_file;
        }
    }
    
    try {
        // Build the query dynamically based on what's submitted
        $fields = [];
        $placeholders = [];
        $values = [];
        
        foreach ($formData as $key => $value) {
            if (!empty($value) && $key !== 'submit') {
                $fields[] = $key;
                $placeholders[] = ":$key";
                $values[":$key"] = $value;
            }
        }
        
        // Add submitted_at
        $fields[] = 'submitted_at';
        $placeholders[] = ':submitted_at';
        $values[':submitted_at'] = date('Y-m-d H:i:s');
        
        $query = "INSERT INTO job_applications (" . implode(', ', $fields) . ") 
                  VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($values)) {
            $success_message = "success";
        } else {
            $error_message = "Ralat berlaku. Sila cuba sebentar lagi.";
        }
    } catch (PDOException $e) {
        $error_message = "Ralat sistem. Sila hubungi admin.";
    }
}
?>
<!doctype html>
<html lang="ms" class="h-full">
<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang Permohonan Kerja - Tuah Cafe</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script src="/_sdk/element_sdk.js"></script>
    <script src="/_sdk/data_sdk.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- RESET & BASIC --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f7f1e3;
            color: #4e342e;
            overflow-x: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
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
            height: 40vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1551782450-a2132b4ba21d') center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }
        .hero h1 { font-size: 45px; margin-bottom: 10px; }

        /* Form Input Styling */
        .form-input:focus { 
            box-shadow: 0 0 0 3px rgba(93, 64, 55, 0.2); 
        }
        .form-input.error { 
            border-color: #ef4444; 
            background-color: #fef2f2; 
        }
        .form-input.error:focus { 
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2); 
        }
        .form-input.valid { 
            border-color: #10b981; 
        }
        .form-input.valid:focus { 
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); 
        }

        /* Field Wrapper */
        .field-wrapper { 
            position: relative; 
        }

        /* Validation Messages */
        .error-message { 
            display: none; 
            color: #ef4444; 
            font-size: 0.875rem; 
            margin-top: 0.375rem; 
        }
        .error-message.show { 
            display: block; 
            animation: slideDown 0.2s ease-out; 
        }

        .success-check { 
            display: none; 
            color: #10b981; 
            font-weight: 600; 
            font-size: 0.875rem; 
            margin-top: 0.375rem; 
        }
        .success-check.show { 
            display: block; 
            animation: slideDown 0.2s ease-out; 
        }

        .check-icon { 
            display: inline-block; 
            margin-right: 4px; 
        }

        /* Animations */
        @keyframes slideDown {
            from { 
                opacity: 0; 
                transform: translateY(-4px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @keyframes pulse-soft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .loading-pulse { 
            animation: pulse-soft 1.5s infinite; 
        }
        
        /* Success Message Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes scaleCheck {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Footer */
        footer {
            background-color: #513627;
            color: #F9F2E8;
            padding: 4rem 2rem 2rem 2rem;
            margin-top: 0;
            width: 100%;
        }

        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 3rem;
            border-bottom: 1px solid rgba(249, 242, 232, 0.1);
        }

        .footer-container h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #E2C792;
        }

        .footer-container p {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #D6C8B8;
            margin-bottom: 0.8rem;
        }

        .footer-container ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-container ul li {
            margin-bottom: 0.8rem;
        }

        .footer-container ul li a {
            font-size: 0.95rem;
            color: #D6C8B8;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-container ul li a:hover {
            color: #ffffff;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 2rem auto 0;
            font-size: 0.9rem;
            color: #D6C8B8;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-icons {
            display: flex;
            gap: 1.5rem;
        }

        .footer-icons a {
            color: #F9F2E8;
            font-size: 1.25rem;
            text-decoration: none;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .footer-icons a:hover {
            color: #E2C792;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
            .footer-container {
                gap: 2rem;
            }
            .main-header {
                padding: 10px 20px;
            }
            .nav-links {
                display: none;
            }
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
            <h1>PERMOHONAN KERJA</h1>
        </div>
    </section>

    <div>
        <div id="app-wrapper" class="w-full bg-gradient-to-br from-[#4A372E] via-[#6b3f2a] to-[#4A372E]">
            <div class="py-8 px-4 sm:px-6 lg:px-8">
                <div class="max-w-4xl mx-auto">
                    <header class="text-center mb-8">
                        <div id="logo-container" class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4 shadow-lg" style="background-color: #5D4037; box-shadow: 0 10px 25px rgba(156, 163, 175, 0.5);">
                            <svg id="default-logo" class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <img id="custom-logo" class="w-12 h-12 object-cover rounded-xl hidden" alt="Company Logo">
                        </div>
                        <h1 id="form-title" class="text-3xl font-bold mb-2" style="color: #8B6F47;">BORANG PERMOHONAN KERJA</h1>
                        <p id="company-name" class="font-medium" style="color: #000000;">Tuah Cafe</p>
                    </header>
                    
                    <main>
                        <?php if ($success_message == "success"): ?>
                            <!-- Success Message -->
                            <div style="background: linear-gradient(135deg, #d4edda, #c3e6cb); border-radius: 20px; padding: 50px 40px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); animation: fadeInUp 0.5s ease;">
                                <div style="width: 90px; height: 90px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); animation: scaleCheck 0.5s ease;">
                                    <svg style="width: 50px; height: 50px; color: #28a745;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h2 style="font-size: 32px; font-weight: 700; color: #155724; margin-bottom: 15px;">Permohonan Berjaya Dihantar! 🎉</h2>
                                <p style="font-size: 16px; color: #155724; margin-bottom: 30px; line-height: 1.6;">Terima kasih kerana memohon ke Tuah Cafe. Kami akan menyemak permohonan anda dan akan menghubungi anda tidak lama lagi.</p>
                                <a href="Job_Form.php" style="display: inline-block; background: #155724; color: white; padding: 12px 35px; border: none; border-radius: 50px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.3s ease;">Hantar Permohonan Lain</a>
                            </div>
                        <?php elseif ($error_message): ?>
                            <div style="background: linear-gradient(135deg, #f8d7da, #f5c6cb); border-radius: 20px; padding: 20px; text-align: center; margin-bottom: 20px;">
                                <p style="color: #721c24;"><?php echo $error_message; ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message != "success"): ?>
                        <!-- Progress Bar -->
                        <div class="bg-white shadow-lg shadow-gray-200/50 p-6 mb-6" style="border-radius: 20px;">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-gray-700">Progres Permohonan</h3>
                                <span id="progress-text" class="text-sm font-bold text-gray-700">Langkah 1/5</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div id="progress-bar" class="h-full rounded-full transition-all duration-300" style="width: 20%; background-color: #D4A76A;"></div>
                            </div>
                        </div>

                        <!-- Form Section -->
                        <form id="job-form" method="POST" action="" class="animate-fade-in" enctype="multipart/form-data">
                            <!-- Tabs Navigation -->
                            <div class="bg-white shadow-lg shadow-gray-200/50 mb-6 overflow-hidden" style="border-radius: 20px; overflow: hidden;">
                                <div class="flex overflow-x-auto border-b border-gray-200">
                                    <button type="button" class="tab-button active flex-1 px-6 py-4 font-medium text-center transition-all border-b-2 text-white whitespace-nowrap" data-step="1" style="border-bottom-color: #5D4037; background-color: #5D4037;">1. Maklumat Peribadi</button>
                                    <button type="button" class="tab-button flex-1 px-6 py-4 font-medium text-center transition-all border-b-2 border-transparent text-gray-600 hover:bg-gray-50 whitespace-nowrap" data-step="2">2. Pendidikan</button>
                                    <button type="button" class="tab-button flex-1 px-6 py-4 font-medium text-center transition-all border-b-2 border-transparent text-gray-600 hover:bg-gray-50 whitespace-nowrap" data-step="3">3. Pengalaman Kerja</button>
                                    <button type="button" class="tab-button flex-1 px-6 py-4 font-medium text-center transition-all border-b-2 border-transparent text-gray-600 hover:bg-gray-50 whitespace-nowrap" data-step="4">4. Pengetahuan Syarikat</button>
                                    <button type="button" class="tab-button flex-1 px-6 py-4 font-medium text-center transition-all border-b-2 border-transparent text-gray-600 hover:bg-gray-50 whitespace-nowrap" data-step="5">5. Kontak Kecemasan</button>
                                </div>
                            </div>

                            <!-- Tab Content -->
                            <div class="bg-white shadow-lg shadow-gray-200/50 p-8" style="border-radius: 20px;">
                                <!-- Step 1: Personal Info -->
                                <div class="tab-content" data-step="1">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        <div class="field-wrapper">
                                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Penuh *</label>
                                            <input type="text" id="full_name" name="full_name" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="Ahmad bin Abdullah" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                            <div class="error-message" id="full_name-error"></div>
                                            <div class="success-check" id="full_name-success">
                                                <span class="check-icon">✓</span>Nama sah
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="nric" class="block text-sm font-medium text-gray-700 mb-1.5">No. K/P *</label>
                                            <input type="text" id="nric" name="nric" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="123456-01-1234" value="<?php echo isset($_POST['nric']) ? htmlspecialchars($_POST['nric']) : ''; ?>">
                                            <div class="error-message" id="nric-error"></div>
                                            <div class="success-check" id="nric-success">
                                                <span class="check-icon">✓</span>No. K/P sah
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Emel *</label>
                                            <input type="email" id="email" name="email" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="ahmad@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                            <div class="error-message" id="email-error"></div>
                                            <div class="success-check" id="email-success">
                                                <span class="check-icon">✓</span>Emel sah
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">No. Telefon *</label>
                                            <input type="tel" id="phone" name="phone" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="012-3456789" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                            <div class="error-message" id="phone-error"></div>
                                            <div class="success-check" id="phone-success">
                                                <span class="check-icon">✓</span>No. telefon sah
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1.5">Jantina *</label>
                                            <select id="gender" name="gender" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Jantina --</option>
                                                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Lelaki</option>
                                                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Perempuan</option>
                                            </select>
                                            <div class="error-message" id="gender-error"></div>
                                            <div class="success-check" id="gender-success">
                                                <span class="check-icon">✓</span>Jantina dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-1.5">Status Perkahwinan *</label>
                                            <select id="marital_status" name="marital_status" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Status --</option>
                                                <option value="Single" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Single') ? 'selected' : ''; ?>>Bujang</option>
                                                <option value="Married" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Married') ? 'selected' : ''; ?>>Berkahwin</option>
                                                <option value="Divorced" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Divorced') ? 'selected' : ''; ?>>Bercerai</option>
                                                <option value="Widowed" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Widowed') ? 'selected' : ''; ?>>Balu/Duda</option>
                                            </select>
                                            <div class="error-message" id="marital_status-error"></div>
                                            <div class="success-check" id="marital_status-success">
                                                <span class="check-icon">✓</span>Status dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="nationality" class="block text-sm font-medium text-gray-700 mb-1.5">Kerakyatan *</label>
                                            <select id="nationality" name="nationality" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Kerakyatan --</option>
                                                <option value="Malaysian" <?php echo (isset($_POST['nationality']) && $_POST['nationality'] == 'Malaysian') ? 'selected' : ''; ?>>Malaysia</option>
                                                <option value="Other" <?php echo (isset($_POST['nationality']) && $_POST['nationality'] == 'Other') ? 'selected' : ''; ?>>Lain-lain</option>
                                            </select>
                                            <div class="error-message" id="nationality-error"></div>
                                            <div class="success-check" id="nationality-success">
                                                <span class="check-icon">✓</span>Kerakyatan dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="religion" class="block text-sm font-medium text-gray-700 mb-1.5">Agama *</label>
                                            <select id="religion" name="religion" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Agama --</option>
                                                <option value="Islam" <?php echo (isset($_POST['religion']) && $_POST['religion'] == 'Islam') ? 'selected' : ''; ?>>Islam</option>
                                                <option value="Buddhism" <?php echo (isset($_POST['religion']) && $_POST['religion'] == 'Buddhism') ? 'selected' : ''; ?>>Buddha</option>
                                                <option value="Christianity" <?php echo (isset($_POST['religion']) && $_POST['religion'] == 'Christianity') ? 'selected' : ''; ?>>Kristian</option>
                                                <option value="Hinduism" <?php echo (isset($_POST['religion']) && $_POST['religion'] == 'Hinduism') ? 'selected' : ''; ?>>Hindu</option>
                                                <option value="Sikhism" <?php echo (isset($_POST['religion']) && $_POST['religion'] == 'Sikhism') ? 'selected' : ''; ?>>Sikh</option>
                                                <option value="Other" <?php echo (isset($_POST['religion']) && $_POST['religion'] == 'Other') ? 'selected' : ''; ?>>Lain-lain</option>
                                            </select>
                                            <div class="error-message" id="religion-error"></div>
                                            <div class="success-check" id="religion-success">
                                                <span class="check-icon">✓</span>Agama dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1.5">Bandar/Kota *</label>
                                            <input type="text" id="city" name="city" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="Kuala Lumpur" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                                            <div class="error-message" id="city-error"></div>
                                            <div class="success-check" id="city-success">
                                                <span class="check-icon">✓</span>Bandar diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="state" class="block text-sm font-medium text-gray-700 mb-1.5">Negeri *</label>
                                            <select id="state" name="state" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Negeri --</option>
                                                <option value="Johor">Johor</option>
                                                <option value="Kedah">Kedah</option>
                                                <option value="Kelantan">Kelantan</option>
                                                <option value="Melaka">Melaka</option>
                                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                                <option value="Pahang">Pahang</option>
                                                <option value="Penang">Penang</option>
                                                <option value="Perak">Perak</option>
                                                <option value="Perlis">Perlis</option>
                                                <option value="Sabah">Sabah</option>
                                                <option value="Sarawak">Sarawak</option>
                                                <option value="Selangor">Selangor</option>
                                                <option value="Terengganu">Terengganu</option>
                                                <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                                                <option value="WP Labuan">WP Labuan</option>
                                                <option value="WP Putrajaya">WP Putrajaya</option>
                                            </select>
                                            <div class="error-message" id="state-error"></div>
                                            <div class="success-check" id="state-success">
                                                <span class="check-icon">✓</span>Negeri dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1.5">Kod Pos *</label>
                                            <input type="text" id="postal_code" name="postal_code" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="50050" value="<?php echo isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : ''; ?>">
                                            <div class="error-message" id="postal_code-error"></div>
                                            <div class="success-check" id="postal_code-success">
                                                <span class="check-icon">✓</span>Kod pos sah
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="own_vehicle" class="block text-sm font-medium text-gray-700 mb-1.5">Adakah anda mempunyai kenderaan sendiri? *</label>
                                            <select id="own_vehicle" name="own_vehicle" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Jawapan --</option>
                                                <option value="Yes">Ya</option>
                                                <option value="No">Tidak</option>
                                            </select>
                                            <div class="error-message" id="own_vehicle-error"></div>
                                            <div class="success-check" id="own_vehicle-success">
                                                <span class="check-icon">✓</span>Jawapan dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper hidden" id="driving_license_wrapper">
                                            <label for="driving_license_class" class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelas Lesen Memandu *</label>
                                            <select id="driving_license_class" name="driving_license_class" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Kelas --</option>
                                                <option value="D">D</option>
                                                <option value="B2">B2</option>
                                                <option value="B">B</option>
                                                <option value="DA">DA</option>
                                            </select>
                                            <div class="error-message" id="driving_license_class-error"></div>
                                            <div class="success-check" id="driving_license_class-success">
                                                <span class="check-icon">✓</span>Kelas dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2">
                                            <label for="criminal_history" class="block text-sm font-medium text-gray-700 mb-1.5">Adakah anda mempunyai rekod jenayah? *</label>
                                            <select id="criminal_history" name="criminal_history" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Jawapan --</option>
                                                <option value="No">Tidak</option>
                                                <option value="Yes">Ya</option>
                                            </select>
                                            <div class="error-message" id="criminal_history-error"></div>
                                            <div class="success-check" id="criminal_history-success">
                                                <span class="check-icon">✓</span>Jawapan dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2 hidden" id="criminal_explanation_wrapper">
                                            <label for="criminal_explanation" class="block text-sm font-medium text-gray-700 mb-1.5">Jika ya, sila jelaskan *</label>
                                            <textarea id="criminal_explanation" name="criminal_explanation" rows="3" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all resize-none" placeholder="Sila nyatakan butiran rekod jenayah anda"></textarea>
                                            <div class="error-message" id="criminal_explanation-error"></div>
                                            <div class="success-check" id="criminal_explanation-success">
                                                <span class="check-icon">✓</span>Penjelasan diisi
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Education Details -->
                                <div class="tab-content hidden" data-step="2">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        <div class="field-wrapper">
                                            <label for="education_level" class="block text-sm font-medium text-gray-700 mb-1.5">Kelayakan Tertinggi *</label>
                                            <select id="education_level" name="education_level" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Kelayakan --</option>
                                                <option value="SPM">SPM</option>
                                                <option value="STPM">STPM</option>
                                                <option value="Diploma">Diploma</option>
                                                <option value="Degree">Ijazah Sarjana Muda</option>
                                                <option value="Master">Ijazah Sarjana</option>
                                                <option value="PhD">Doktor Falsafah</option>
                                                <option value="Professional Cert">Sijil Profesional</option>
                                            </select>
                                            <div class="error-message" id="education_level-error"></div>
                                            <div class="success-check" id="education_level-success">
                                                <span class="check-icon">✓</span>Kelayakan dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="field_of_study" class="block text-sm font-medium text-gray-700 mb-1.5">Bidang Pengajian *</label>
                                            <input type="text" id="field_of_study" name="field_of_study" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: Sains Komputer, Kejuruteraan, Perakaunan">
                                            <div class="error-message" id="field_of_study-error"></div>
                                            <div class="success-check" id="field_of_study-success">
                                                <span class="check-icon">✓</span>Bidang diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="school_name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Institusi Pendidikan *</label>
                                            <input type="text" id="school_name" name="school_name" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: Universiti Malaya, Universiti Teknologi Malaysia">
                                            <div class="error-message" id="school_name-error"></div>
                                            <div class="success-check" id="school_name-success">
                                                <span class="check-icon">✓</span>Institusi diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="graduation_year" class="block text-sm font-medium text-gray-700 mb-1.5">Tahun Tamat Pengajian *</label>
                                            <select id="graduation_year" name="graduation_year" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Tahun --</option>
                                                <?php for($year = 2015; $year <= 2026; $year++): ?>
                                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <div class="error-message" id="graduation_year-error"></div>
                                            <div class="success-check" id="graduation_year-success">
                                                <span class="check-icon">✓</span>Tahun dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2">
                                            <label for="skills" class="block text-sm font-medium text-gray-700 mb-1.5">Kemahiran Teknikal *</label>
                                            <input type="text" id="skills" name="skills" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: Microsoft Office, Python, SQL, Bahasa Inggeris">
                                            <div class="error-message" id="skills-error"></div>
                                            <div class="success-check" id="skills-success">
                                                <span class="check-icon">✓</span>Kemahiran diisi
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Work Experience -->
                                <div class="tab-content hidden" data-step="3">
                                    <div class="grid grid-cols-1 gap-6">
                                        <div class="field-wrapper">
                                            <label for="current_employment_status" class="block text-sm font-medium text-gray-700 mb-1.5">Status Pekerjaan Semasa *</label>
                                            <select id="current_employment_status" name="current_employment_status" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Status --</option>
                                                <option value="Employed">Bekerja</option>
                                                <option value="Unemployed">Menganggur</option>
                                                <option value="Self-employed">Bekerja Sendiri</option>
                                                <option value="Student">Pelajar</option>
                                                <option value="Fresher">Graduan Baru</option>
                                            </select>
                                            <div class="error-message" id="current_employment_status-error"></div>
                                            <div class="success-check" id="current_employment_status-success">
                                                <span class="check-icon">✓</span>Status dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="fb_experience" class="block text-sm font-medium text-gray-700 mb-1.5">Adakah anda mempunyai pengalaman di sektor F&B? *</label>
                                            <select id="fb_experience" name="fb_experience" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Jawapan --</option>
                                                <option value="Yes">Ya</option>
                                                <option value="No">Tidak</option>
                                            </select>
                                            <div class="error-message" id="fb_experience-error"></div>
                                            <div class="success-check" id="fb_experience-success">
                                                <span class="check-icon">✓</span>Jawapan dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2 hidden" id="fb_company_name_wrapper">
                                            <label for="fb_company_name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Syarikat F&B *</label>
                                            <input type="text" id="fb_company_name" name="fb_company_name" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: ABC Restaurant Sdn Bhd">
                                            <div class="error-message" id="fb_company_name-error"></div>
                                            <div class="success-check" id="fb_company_name-success">
                                                <span class="check-icon">✓</span>Nama syarikat diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2 hidden" id="fb_location_wrapper">
                                            <label for="fb_location" class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi Syarikat F&B *</label>
                                            <input type="text" id="fb_location" name="fb_location" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: No. 123, Jalan Merdeka">
                                            <div class="error-message" id="fb_location-error"></div>
                                            <div class="success-check" id="fb_location-success">
                                                <span class="check-icon">✓</span>Lokasi diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper hidden" id="fb_city_wrapper">
                                            <label for="fb_city" class="block text-sm font-medium text-gray-700 mb-1.5">Bandar/Kota *</label>
                                            <input type="text" id="fb_city" name="fb_city" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="Kuala Lumpur">
                                            <div class="error-message" id="fb_city-error"></div>
                                            <div class="success-check" id="fb_city-success">
                                                <span class="check-icon">✓</span>Bandar diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper hidden" id="fb_state_wrapper">
                                            <label for="fb_state" class="block text-sm font-medium text-gray-700 mb-1.5">Negeri *</label>
                                            <select id="fb_state" name="fb_state" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Negeri --</option>
                                                <option value="Johor">Johor</option>
                                                <option value="Kedah">Kedah</option>
                                                <option value="Kelantan">Kelantan</option>
                                                <option value="Melaka">Melaka</option>
                                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                                <option value="Pahang">Pahang</option>
                                                <option value="Penang">Penang</option>
                                                <option value="Perak">Perak</option>
                                                <option value="Perlis">Perlis</option>
                                                <option value="Sabah">Sabah</option>
                                                <option value="Sarawak">Sarawak</option>
                                                <option value="Selangor">Selangor</option>
                                                <option value="Terengganu">Terengganu</option>
                                                <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                                                <option value="WP Labuan">WP Labuan</option>
                                                <option value="WP Putrajaya">WP Putrajaya</option>
                                            </select>
                                            <div class="error-message" id="fb_state-error"></div>
                                            <div class="success-check" id="fb_state-success">
                                                <span class="check-icon">✓</span>Negeri dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper hidden" id="fb_postal_code_wrapper">
                                            <label for="fb_postal_code" class="block text-sm font-medium text-gray-700 mb-1.5">Kod Pos *</label>
                                            <input type="text" id="fb_postal_code" name="fb_postal_code" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="50050">
                                            <div class="error-message" id="fb_postal_code-error"></div>
                                            <div class="success-check" id="fb_postal_code-success">
                                                <span class="check-icon">✓</span>Kod pos diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2">
                                            <label class="block text-sm font-bold text-gray-900 mb-3 mt-4">Pekerjaan Sebelumnya (Paling Terkini)</label>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="previous_position" class="block text-sm font-medium text-gray-700 mb-1.5">Jawatan Sebelumnya</label>
                                            <input type="text" id="previous_position" name="previous_position" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: Chef, Waiter, Kitchen Assistant">
                                            <div class="success-check" id="previous_position-success">
                                                <span class="check-icon">✓</span>Jawatan diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="previous_employer" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Majikan Sebelumnya</label>
                                            <input type="text" id="previous_employer" name="previous_employer" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: ABC Restaurant Sdn Bhd">
                                            <div class="success-check" id="previous_employer-success">
                                                <span class="check-icon">✓</span>Majikan diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="employment_duration" class="block text-sm font-medium text-gray-700 mb-1.5">Tempoh Perkhidmatan</label>
                                            <select id="employment_duration" name="employment_duration" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Tempoh --</option>
                                                <option value="Less than 1 year">Kurang 1 Tahun</option>
                                                <option value="1-2 years">1-2 Tahun</option>
                                                <option value="3-5 years">3-5 Tahun</option>
                                                <option value="5-10 years">5-10 Tahun</option>
                                                <option value="More than 10 years">Lebih 10 Tahun</option>
                                            </select>
                                            <div class="success-check" id="employment_duration-success">
                                                <span class="check-icon">✓</span>Tempoh dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="previous_salary" class="block text-sm font-medium text-gray-700 mb-1.5">Gaji Sebelumnya (RM)</label>
                                            <input type="number" id="previous_salary" name="previous_salary" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: 3000" min="0">
                                            <div class="success-check" id="previous_salary-success">
                                                <span class="check-icon">✓</span>Gaji diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2">
                                            <label for="reason_for_leaving" class="block text-sm font-medium text-gray-700 mb-1.5">Sebab Meninggalkan Pekerjaan</label>
                                            <textarea id="reason_for_leaving" name="reason_for_leaving" rows="3" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all resize-none" placeholder="Ceritakan sebab anda meninggalkan pekerjaan sebelumnya (opsional)"></textarea>
                                            <div class="success-check" id="reason_for_leaving-success">
                                                <span class="check-icon">✓</span>Sebab diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2">
                                            <label class="block text-sm font-bold text-gray-900 mb-3 mt-4">Butiran Jawatan Yang Dipohon</label>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="applying_position" class="block text-sm font-medium text-gray-700 mb-1.5">Jawatan Yang Dipohon *</label>
                                            <select id="applying_position" name="applying_position" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Jawatan --</option>
                                                <option value="Server">Server</option>
                                                <option value="Chef">Chef</option>
                                                <option value="Cashier">Juruwang</option>
                                                <option value="Barista">Barista</option>
                                                <option value="Assistant Manager">Pembantu Pengurus</option>
                                                <option value="Supervisor">Penyelia</option>
                                            </select>
                                            <div class="error-message" id="applying_position-error"></div>
                                            <div class="success-check" id="applying_position-success">
                                                <span class="check-icon">✓</span>Jawatan dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="apply_outlet" class="block text-sm font-medium text-gray-700 mb-1.5">Outlet Yang Dipohon *</label>
                                            <select id="apply_outlet" name="apply_outlet" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Outlet --</option>
                                                <option value="Bangi">Bangi</option>
                                                <option value="Eco Majestic">Eco Majestic</option>
                                            </select>
                                            <div class="error-message" id="apply_outlet-error"></div>
                                            <div class="success-check" id="apply_outlet-success">
                                                <span class="check-icon">✓</span>Outlet dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="notice_period" class="block text-sm font-medium text-gray-700 mb-1.5">Notis Mula Bekerja *</label>
                                            <select id="notice_period" name="notice_period" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Tempoh --</option>
                                                <option value="Immediate">Serta-merta</option>
                                                <option value="1 week">1 Minggu</option>
                                                <option value="2 weeks">2 Minggu</option>
                                                <option value="1 month">1 Bulan</option>
                                                <option value="2 months">2 Bulan</option>
                                                <option value="3 months">3 Bulan</option>
                                            </select>
                                            <div class="error-message" id="notice_period-error"></div>
                                            <div class="success-check" id="notice_period-success">
                                                <span class="check-icon">✓</span>Notis dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="expected_salary" class="block text-sm font-medium text-gray-700 mb-1.5">Gaji Dijangka (RM) *</label>
                                            <input type="number" id="expected_salary" name="expected_salary" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: 3500" min="0">
                                            <div class="error-message" id="expected_salary-error"></div>
                                            <div class="success-check" id="expected_salary-success">
                                                <span class="check-icon">✓</span>Gaji diisi
                                            </div>
                                        </div>
                                        
                                        <!-- Documents Section - Resume on top, Supporting Documents below (not side by side) -->
                                        <div class="field-wrapper sm:col-span-2">
                                            <label class="block text-sm font-bold text-gray-900 mb-3 mt-4">Dokumen</label>
                                        </div>
                                        
                                        <!-- Resume/CV - Compulsory (required) -->
                                        <div class="field-wrapper sm:col-span-2">
                                            <label for="resume_document" class="block text-sm font-medium text-gray-700 mb-1.5">Resume/CV * (Wajib)</label>
                                            <input type="file" id="resume_document" name="resume_document" accept=".pdf,.doc,.docx" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                            <p class="text-xs text-gray-500 mt-1">Format yang diterima: PDF, DOC, DOCX (Maks 10MB) - Wajib diisi</p>
                                            <div class="error-message" id="resume_document-error"></div>
                                            <div class="success-check" id="resume_document-success">
                                                <span class="check-icon">✓</span>Resume dimuat naik
                                            </div>
                                        </div>
                                        
                                        <!-- Supporting Documents - Below Resume -->
                                        <div class="field-wrapper sm:col-span-2">
                                            <label for="supporting_document" class="block text-sm font-medium text-gray-700 mb-1.5">Dokumen Sokongan (Opsional)</label>
                                            <input type="file" id="supporting_document" name="supporting_document" accept=".pdf,.doc,.docx,.jpg,.png" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                            <p class="text-xs text-gray-500 mt-1">Sijil, transkrip, dsb. (PDF, DOC, JPG, PNG, Maks 10MB) - Opsional</p>
                                            <div class="error-message" id="supporting_document-error"></div>
                                            <div class="success-check" id="supporting_document-success">
                                                <span class="check-icon">✓</span>Dokumen sokongan dimuat naik
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 4: Company Knowledge -->
                                <div class="tab-content hidden" data-step="4">
                                    <div class="grid grid-cols-1 gap-6">
                                        <div class="field-wrapper">
                                            <label for="company_name_knowledge" class="block text-sm font-medium text-gray-700 mb-1.5">Bagaimana anda mengetahui tentang syarikat kami? *</label>
                                            <select id="company_name_knowledge" name="company_name_knowledge" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Sumber --</option>
                                                <option value="Website">Laman Web Syarikat</option>
                                                <option value="LinkedIn">LinkedIn</option>
                                                <option value="Job Portal">Portal Kerja</option>
                                                <option value="Friend Referral">Rujukan Kawan</option>
                                                <option value="Social Media">Media Sosial</option>
                                                <option value="News/Media">Berita/Media</option>
                                                <option value="University Career Fair">Majlis Kerjaya Universiti</option>
                                                <option value="Other">Lain-lain</option>
                                            </select>
                                            <div class="error-message" id="company_name_knowledge-error"></div>
                                            <div class="success-check" id="company_name_knowledge-success">
                                                <span class="check-icon">✓</span>Sumber dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="job_source" class="block text-sm font-medium text-gray-700 mb-1.5">Dari manakah anda mengetahui tentang jawatan ini? *</label>
                                            <select id="job_source" name="job_source" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Sumber --</option>
                                                <option value="Website">Laman Web Syarikat</option>
                                                <option value="LinkedIn">LinkedIn</option>
                                                <option value="Job Portal">Portal Kerja</option>
                                                <option value="Friend Referral">Rujukan Kawan</option>
                                                <option value="Social Media">Media Sosial</option>
                                                <option value="News/Media">Berita/Media</option>
                                                <option value="University Career Fair">Majlis Kerjaya Universiti</option>
                                                <option value="Other">Lain-lain</option>
                                            </select>
                                            <div class="error-message" id="job_source-error"></div>
                                            <div class="success-check" id="job_source-success">
                                                <span class="check-icon">✓</span>Sumber dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="why_hire_you" class="block text-sm font-medium text-gray-700 mb-1.5">Mengapa Tuah Cafe perlu mengupah anda? *</label>
                                            <textarea id="why_hire_you" name="why_hire_you" rows="5" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all resize-none" placeholder="Terangkan kekuatan anda, pengalaman yang berkaitan, dan apa yang boleh anda sumbangkan kepada Tuah Cafe..."></textarea>
                                            <div class="error-message" id="why_hire_you-error"></div>
                                            <div class="success-check" id="why_hire_you-success">
                                                <span class="check-icon">✓</span>Jawapan diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="why_join_company" class="block text-sm font-medium text-gray-700 mb-1.5">Mengapa anda ingin menyertai Tuah Cafe? *</label>
                                            <textarea id="why_join_company" name="why_join_company" rows="5" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all resize-none" placeholder="Ceritakan tentang minat anda terhadap Tuah Cafe dan jawatan ini. Apa aspirasi anda?"></textarea>
                                            <div class="error-message" id="why_join_company-error"></div>
                                            <div class="success-check" id="why_join_company-success">
                                                <span class="check-icon">✓</span>Motivasi diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="additional_info" class="block text-sm font-medium text-gray-700 mb-1.5">Maklumat Tambahan</label>
                                            <textarea id="additional_info" name="additional_info" rows="4" class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all resize-none" placeholder="Sebarang maklumat tambahan yang ingin anda kongsikan (opsional)"></textarea>
                                            <div class="success-check" id="additional_info-success">
                                                <span class="check-icon">✓</span>Maklumat diisi
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 5: Emergency Contact -->
                                <div class="tab-content hidden" data-step="5">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        <div class="field-wrapper">
                                            <label for="emergency_contact_person" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Orang Hubung Kecemasan *</label>
                                            <input type="text" id="emergency_contact_person" name="emergency_contact_person" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: Ibu Fatimah binti Ali">
                                            <div class="error-message" id="emergency_contact_person-error"></div>
                                            <div class="success-check" id="emergency_contact_person-success">
                                                <span class="check-icon">✓</span>Nama diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-1.5">No. Telefon Kecemasan *</label>
                                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="012-3456789">
                                            <div class="error-message" id="emergency_contact_phone-error"></div>
                                            <div class="success-check" id="emergency_contact_phone-success">
                                                <span class="check-icon">✓</span>No. telefon diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2">
                                            <label for="emergency_address" class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Kecemasan *</label>
                                            <input type="text" id="emergency_address" name="emergency_address" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="cth: 123, Jalan Merdeka">
                                            <div class="error-message" id="emergency_address-error"></div>
                                            <div class="success-check" id="emergency_address-success">
                                                <span class="check-icon">✓</span>Alamat diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="emergency_city" class="block text-sm font-medium text-gray-700 mb-1.5">Bandar/Kota *</label>
                                            <input type="text" id="emergency_city" name="emergency_city" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="Kuala Lumpur">
                                            <div class="error-message" id="emergency_city-error"></div>
                                            <div class="success-check" id="emergency_city-success">
                                                <span class="check-icon">✓</span>Bandar diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="emergency_state" class="block text-sm font-medium text-gray-700 mb-1.5">Negeri *</label>
                                            <select id="emergency_state" name="emergency_state" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Negeri --</option>
                                                <option value="Johor">Johor</option>
                                                <option value="Kedah">Kedah</option>
                                                <option value="Kelantan">Kelantan</option>
                                                <option value="Melaka">Melaka</option>
                                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                                <option value="Pahang">Pahang</option>
                                                <option value="Penang">Penang</option>
                                                <option value="Perak">Perak</option>
                                                <option value="Perlis">Perlis</option>
                                                <option value="Sabah">Sabah</option>
                                                <option value="Sarawak">Sarawak</option>
                                                <option value="Selangor">Selangor</option>
                                                <option value="Terengganu">Terengganu</option>
                                                <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                                                <option value="WP Labuan">WP Labuan</option>
                                                <option value="WP Putrajaya">WP Putrajaya</option>
                                            </select>
                                            <div class="error-message" id="emergency_state-error"></div>
                                            <div class="success-check" id="emergency_state-success">
                                                <span class="check-icon">✓</span>Negeri dipilih
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <label for="emergency_postal_code" class="block text-sm font-medium text-gray-700 mb-1.5">Kod Pos *</label>
                                            <input type="text" id="emergency_postal_code" name="emergency_postal_code" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all" placeholder="50050">
                                            <div class="error-message" id="emergency_postal_code-error"></div>
                                            <div class="success-check" id="emergency_postal_code-success">
                                                <span class="check-icon">✓</span>Kod pos diisi
                                            </div>
                                        </div>
                                        <div class="field-wrapper sm:col-span-2">
                                            <label for="emergency_relationship" class="block text-sm font-medium text-gray-700 mb-1.5">Hubungan *</label>
                                            <select id="emergency_relationship" name="emergency_relationship" required class="form-input w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:outline-none transition-all bg-white">
                                                <option value="">-- Pilih Hubungan --</option>
                                                <option value="Parent">Ibu Bapa</option>
                                                <option value="Spouse">Pasangan Hidup</option>
                                                <option value="Sibling">Adik Beradik</option>
                                                <option value="Child">Anak</option>
                                                <option value="Relative">Saudara</option>
                                                <option value="Friend">Kawan</option>
                                                <option value="Other">Lain-lain</option>
                                            </select>
                                            <div class="error-message" id="emergency_relationship-error"></div>
                                            <div class="success-check" id="emergency_relationship-success">
                                                <span class="check-icon">✓</span>Hubungan dipilih
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="flex justify-between gap-4 mt-8 pt-8 border-t border-gray-200">
                                    <button type="button" id="prev-btn" class="hidden px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition-all">← Kembali</button>
                                    <div class="flex-1"></div>
                                    <button type="button" id="next-btn" class="px-6 py-3 rounded-xl text-white font-medium transition-all" style="background-color: #5D4037;" onmouseover="this.style.backgroundColor='#3E2723'" onmouseout="this.style.backgroundColor='#5D4037'">Seterusnya →</button>
                                    <button type="submit" id="submit-btn" class="hidden px-8 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                        <span id="btn-text">Hantar Permohonan</span>
                                        <span id="btn-loading" class="hidden loading-pulse">Menghantar...</span>
                                    </button>
                                </div>

                                <!-- Error Toast -->
                                <div id="error-toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-xl shadow-lg">
                                    <span id="error-message">Ralat berlaku. Sila cuba lagi.</span>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div>
                <h2>Tuah Cafe</h2>
                <p>Tempat di mana tradisi kulinari bertemu inovasi! Dari permulaan kecil hingga menjadi nama yang 
				dikenali dalam industri makanan dan minuman, kisah kami adalah tentang semangat, ketahanan, 
				dan keinginan untuk terus memberi yang terbaik kepada komuniti.</p>
            </div>
            <div>
                <h2>Pautan Pantas</h2>
                <ul>
                    <li><a href="Tentang_Kami.html">Tentang Kami</a></li>
                    <li><a href="Menu_fyp2.html">Menu</a></li>
                    <li><a href="Promotion.html">Promosi</a></li>
                    <li><a href="Keahlian.html">Ahli</a></li>
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

    <script>
        // Dropdown Logic
        document.querySelectorAll(".dropdown-toggle").forEach(function(icon) {
            icon.addEventListener("click", function(e) {
                e.preventDefault();
                document.querySelectorAll(".dropdown").forEach(function(d) {
                    if (d !== icon.closest(".dropdown")) {
                        d.classList.remove("active");
                    }
                });
                let parent = this.closest(".dropdown");
                parent.classList.toggle("active");
            });
        });

        document.addEventListener("click", function(e) {
            if (!e.target.matches('.dropdown-toggle')) {
                document.querySelectorAll(".dropdown").forEach(function(dropdown) {
                    dropdown.classList.remove("active");
                });
            }
        });

        // Form Setup
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('job-form');
            if (!form) return;
            
            const submitBtn = document.getElementById('submit-btn');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            
            let currentStep = 1;
            
            // Scroll to form function
            function scrollToForm() {
                const formElement = document.getElementById('job-form');
                if (formElement) {
                    const formPosition = formElement.offsetTop - 100;
                    window.scrollTo({ top: formPosition, behavior: 'smooth' });
                }
            }
            
            // Tab switching
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const step = parseInt(btn.dataset.step);
                    if (btn.style.pointerEvents === 'none') return;
                    switchToStep(step);
                    scrollToForm();
                });
            });
            
            // Conditional field listeners
            const ownVehicleSelect = document.getElementById('own_vehicle');
            const drivingLicenseWrapper = document.getElementById('driving_license_wrapper');
            const drivingLicenseSelect = document.getElementById('driving_license_class');
            
            if (ownVehicleSelect) {
                ownVehicleSelect.addEventListener('change', () => {
                    if (ownVehicleSelect.value === 'Yes') {
                        drivingLicenseWrapper.classList.remove('hidden');
                        drivingLicenseSelect.setAttribute('required', 'required');
                    } else {
                        drivingLicenseWrapper.classList.add('hidden');
                        drivingLicenseSelect.removeAttribute('required');
                        drivingLicenseSelect.value = '';
                    }
                });
            }
            
            const criminalHistorySelect = document.getElementById('criminal_history');
            const criminalExplanationWrapper = document.getElementById('criminal_explanation_wrapper');
            const criminalExplanationTextarea = document.getElementById('criminal_explanation');
            
            if (criminalHistorySelect) {
                criminalHistorySelect.addEventListener('change', () => {
                    if (criminalHistorySelect.value === 'Yes') {
                        criminalExplanationWrapper.classList.remove('hidden');
                        criminalExplanationTextarea.setAttribute('required', 'required');
                    } else {
                        criminalExplanationWrapper.classList.add('hidden');
                        criminalExplanationTextarea.removeAttribute('required');
                        criminalExplanationTextarea.value = '';
                    }
                });
            }
            
            const fbExperienceSelect = document.getElementById('fb_experience');
            const fbWrappers = ['fb_company_name_wrapper', 'fb_location_wrapper', 'fb_city_wrapper', 'fb_state_wrapper', 'fb_postal_code_wrapper'];
            const fbInputs = ['fb_company_name', 'fb_location', 'fb_city', 'fb_state', 'fb_postal_code'];
            
            if (fbExperienceSelect) {
                fbExperienceSelect.addEventListener('change', () => {
                    if (fbExperienceSelect.value === 'Yes') {
                        fbWrappers.forEach(wrapper => {
                            document.getElementById(wrapper)?.classList.remove('hidden');
                        });
                        fbInputs.forEach(input => {
                            const field = document.getElementById(input);
                            if (field) field.setAttribute('required', 'required');
                        });
                    } else {
                        fbWrappers.forEach(wrapper => {
                            document.getElementById(wrapper)?.classList.add('hidden');
                        });
                        fbInputs.forEach(input => {
                            const field = document.getElementById(input);
                            if (field) {
                                field.removeAttribute('required');
                                field.value = '';
                            }
                        });
                    }
                });
            }
            
            function switchToStep(step) {
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active', 'text-white');
                    btn.style.borderBottomColor = 'transparent';
                    btn.style.backgroundColor = 'transparent';
                    btn.classList.add('border-transparent', 'text-gray-600');
                });
                document.querySelector(`.tab-content[data-step="${step}"]`).classList.remove('hidden');
                const activeBtn = document.querySelector(`.tab-button[data-step="${step}"]`);
                activeBtn.classList.add('active', 'text-white');
                activeBtn.style.borderBottomColor = '#5D4037';
                activeBtn.style.backgroundColor = '#5D4037';
                activeBtn.classList.remove('border-transparent', 'text-gray-600');
                currentStep = step;
                prevBtn.classList.toggle('hidden', step === 1);
                nextBtn.classList.toggle('hidden', step === 5);
                submitBtn.classList.toggle('hidden', step !== 5);
                updateProgress();
            }
            
            function updateProgress() {
                document.getElementById('progress-text').textContent = `Langkah ${currentStep}/5`;
                document.getElementById('progress-bar').style.width = `${(currentStep / 5) * 100}%`;
            }
            
            function validateCurrentStep() {
                const currentContent = document.querySelector(`.tab-content[data-step="${currentStep}"]`);
                const required = currentContent.querySelectorAll('[required]');
                let isValid = true;
                required.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                        field.classList.add('valid');
                    }
                });
                
                // Special validation for Step 3 to check resume file
                if (currentStep === 3) {
                    const resumeFile = document.getElementById('resume_document');
                    if (resumeFile && !resumeFile.files.length) {
                        isValid = false;
                        const errorEl = document.getElementById('resume_document-error');
                        if (errorEl) {
                            errorEl.textContent = 'Sila muat naik Resume/CV (Wajib)';
                            errorEl.classList.add('show');
                        }
                        resumeFile.classList.add('error');
                    } else if (resumeFile && resumeFile.files.length) {
                        const errorEl = document.getElementById('resume_document-error');
                        if (errorEl) errorEl.classList.remove('show');
                        resumeFile.classList.remove('error');
                        resumeFile.classList.add('valid');
                        const successEl = document.getElementById('resume_document-success');
                        if (successEl) successEl.classList.add('show');
                    }
                }
                
                return isValid;
            }
            
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (validateCurrentStep()) {
                    if (currentStep < 5) {
                        switchToStep(currentStep + 1);
                        scrollToForm();
                    }
                }
            });
            
            prevBtn.addEventListener('click', () => {
                if (currentStep > 1) {
                    switchToStep(currentStep - 1);
                    scrollToForm();
                }
            });
            
            switchToStep(1);
        });
    </script>
</body>
</html>