-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 06:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tuahcafe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `event_bookings`
--

CREATE TABLE `event_bookings` (
  `id` int(11) NOT NULL,
  `nama_penuh` varchar(255) NOT NULL,
  `no_tel` varchar(20) NOT NULL,
  `jenis_acara` varchar(100) DEFAULT NULL,
  `tarikh_acara` date DEFAULT NULL,
  `pakej_pilihan` varchar(50) DEFAULT NULL,
  `tambahan_dessert` varchar(100) DEFAULT NULL,
  `permintaan_khas` text DEFAULT NULL,
  `status_bayaran` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `full_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `membership_status` varchar(50) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `start_time` varchar(20) DEFAULT NULL,
  `end_time` varchar(20) DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `package_selected` varchar(50) DEFAULT NULL,
  `pax_count` int(11) DEFAULT NULL,
  `menu_details` text DEFAULT NULL,
  `addons_details` text DEFAULT NULL,
  `special_remarks` text DEFAULT NULL,
  `main_course` text DEFAULT NULL,
  `meat_choice` text DEFAULT NULL,
  `salad_choice` text DEFAULT NULL,
  `appetizer_choice` text DEFAULT NULL,
  `dessert_choice` text DEFAULT NULL,
  `addons_json` text DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_bookings`
--

INSERT INTO `event_bookings` (`id`, `nama_penuh`, `no_tel`, `jenis_acara`, `tarikh_acara`, `pakej_pilihan`, `tambahan_dessert`, `permintaan_khas`, `status_bayaran`, `created_at`, `full_name`, `phone_number`, `membership_status`, `event_type`, `event_name`, `event_date`, `start_time`, `end_time`, `venue`, `package_selected`, `pax_count`, `menu_details`, `addons_details`, `special_remarks`, `main_course`, `meat_choice`, `salad_choice`, `appetizer_choice`, `dessert_choice`, `addons_json`, `is_read`, `status`) VALUES
(7, 'asd', '016-2941551', 'Birthday Party', '2026-05-23', 'Package B - RM45.90', NULL, NULL, 'Pending', '2026-05-20 12:35:37', NULL, NULL, 'Normal', NULL, 'adf', NULL, NULL, NULL, 'Tuah Cafe Bangi', NULL, 30, NULL, NULL, 'sdfa', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'pending'),
(8, 'asd', '016-2941551', 'Birthday Party', '2026-05-23', 'Package B - RM45.90', NULL, NULL, 'Pending', '2026-05-20 12:43:40', NULL, NULL, 'Normal', NULL, 'adf', NULL, '00:00:00', '00:00:00', 'Tuah Cafe Bangi', NULL, 30, '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'pending'),
(9, 'asd', '016-2941551', 'Engagement Ceremony', '2026-05-20', 'A', NULL, NULL, 'Pending', '2026-05-20 12:44:22', NULL, NULL, 'Bronze', NULL, 'asd', NULL, '09:00:00', '12:00:00', 'Tuah Cafe Eco Majestic', NULL, 21, 'A_Main: Spicy Beef Olio', '', '', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'pending'),
(10, 'asd', '016-2941551', 'Engagement Ceremony', '2026-05-20', NULL, '', '', 'Pending', '2026-05-20 12:59:01', 'asd', '016-2941551', 'Bronze', 'Engagement Ceremony', 'asd', '2026-05-20', '09:00:00', '12:00:00', 'Tuah Cafe Eco Majestic', 'A', 21, 'A_Main: Spicy Beef Olio', '', '', 'Spicy Beef Olio', '', '', '', '', NULL, 1, 'pending'),
(11, 'asd', '016-2941551', 'Engagement Ceremony', '2026-05-29', NULL, '', '', 'Pending', '2026-05-20 12:59:54', 'asd', '016-2941551', 'Silver', 'Engagement Ceremony', 'zxc', '2026-05-29', '09:00:00', '12:00:00', 'Tuah Cafe Bangi', 'A', 30, 'A_Main: Spicy Beef Olio, A_Meat: Grilled Chicken with Grilled Vegetables, A_Salad: Coleslaw, A_Dessert: Brownies', '', '', 'Spicy Beef Olio', 'Grilled Chicken with Grilled Vegetables', 'Coleslaw', '', 'Brownies', NULL, 1, 'pending'),
(12, '', '', NULL, NULL, NULL, NULL, NULL, 'Pending', '2026-05-20 13:19:38', 'asd', '016-2941551', 'Silver', 'Engagement Ceremony', 'zxc', '2026-05-29', '09:00:00', '12:00:00', 'Tuah Cafe Bangi', 'A', 30, 'A_Main: Spicy Beef Olio, A_Meat: Grilled Chicken with Grilled Vegetables, A_Salad: Coleslaw, A_Dessert: Brownies', '', '', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'pending'),
(13, '', '', NULL, NULL, NULL, NULL, NULL, 'Pending', '2026-05-20 13:20:25', 'asd', '016-2941551', 'Silver', 'Engagement Ceremony', 'zxc', '2026-05-29', '09:00:00', '12:00:00', 'Tuah Cafe Bangi', 'A', 30, 'A_Main: Spicy Beef Olio, A_Meat: Grilled Chicken with Grilled Vegetables, A_Salad: Coleslaw, A_Dessert: Brownies', '', '', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `nric` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `own_vehicle` varchar(10) DEFAULT NULL,
  `driving_license_class` varchar(10) DEFAULT NULL,
  `criminal_history` varchar(10) DEFAULT NULL,
  `criminal_explanation` text DEFAULT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `field_of_study` varchar(100) DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,
  `graduation_year` int(11) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `current_employment_status` varchar(50) DEFAULT NULL,
  `fb_experience` varchar(10) DEFAULT NULL,
  `fb_company_name` varchar(200) DEFAULT NULL,
  `fb_location` text DEFAULT NULL,
  `fb_city` varchar(100) DEFAULT NULL,
  `fb_state` varchar(100) DEFAULT NULL,
  `fb_postal_code` varchar(10) DEFAULT NULL,
  `previous_position` varchar(100) DEFAULT NULL,
  `previous_employer` varchar(200) DEFAULT NULL,
  `employment_duration` varchar(50) DEFAULT NULL,
  `previous_salary` decimal(10,2) DEFAULT NULL,
  `reason_for_leaving` text DEFAULT NULL,
  `applying_position` varchar(100) DEFAULT NULL,
  `apply_outlet` varchar(100) DEFAULT NULL,
  `notice_period` varchar(50) DEFAULT NULL,
  `expected_salary` decimal(10,2) DEFAULT NULL,
  `company_name_knowledge` varchar(100) DEFAULT NULL,
  `job_source` varchar(100) DEFAULT NULL,
  `why_hire_you` text DEFAULT NULL,
  `why_join_company` text DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `emergency_contact_person` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_address` text DEFAULT NULL,
  `emergency_city` varchar(100) DEFAULT NULL,
  `emergency_state` varchar(100) DEFAULT NULL,
  `emergency_postal_code` varchar(10) DEFAULT NULL,
  `emergency_relationship` varchar(50) DEFAULT NULL,
  `resume_link` varchar(500) DEFAULT NULL,
  `status` enum('pending','reviewed','interviewed','hired','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `full_name`, `nric`, `email`, `phone`, `gender`, `marital_status`, `nationality`, `religion`, `city`, `state`, `postal_code`, `own_vehicle`, `driving_license_class`, `criminal_history`, `criminal_explanation`, `education_level`, `field_of_study`, `school_name`, `graduation_year`, `skills`, `current_employment_status`, `fb_experience`, `fb_company_name`, `fb_location`, `fb_city`, `fb_state`, `fb_postal_code`, `previous_position`, `previous_employer`, `employment_duration`, `previous_salary`, `reason_for_leaving`, `applying_position`, `apply_outlet`, `notice_period`, `expected_salary`, `company_name_knowledge`, `job_source`, `why_hire_you`, `why_join_company`, `additional_info`, `emergency_contact_person`, `emergency_contact_phone`, `emergency_address`, `emergency_city`, `emergency_state`, `emergency_postal_code`, `emergency_relationship`, `resume_link`, `status`, `submitted_at`, `is_read`) VALUES
(1, 'AMBER ISABEL FERNANDEZ', '050615-12-0056', 'amberisabel.fernandez@student.gmi.edu.my', '0162941551', 'Female', 'Single', 'Malaysian', 'Christianity', 'kota kinabalu', 'Sabah', '88450', 'No', NULL, 'No', NULL, 'Diploma', 'adsdf', 'sdg', 2026, 'dsfsfds', 'Unemployed', 'Yes', 'sdfs', 'sdfs', 'sdfsf', 'Sabah', '23422', 'dfsdf', 'sdfsd', 'Less than 1 year', 1500.00, 'asda', 'Waiter', 'Bangi', 'Immediate', 1500.00, 'Website', 'LinkedIn', 'asdsf', 'sdfs', 'sdffs', 'sdfsf', 'aeer', 'sdfsfd', 'sdfs', 'Terengganu', '56666', 'Spouse', NULL, 'interviewed', '2026-05-20 00:09:10', 1);

-- --------------------------------------------------------

--
-- Table structure for table `komuniti_events`
--

CREATE TABLE `komuniti_events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `proposal` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `komuniti_events`
--

INSERT INTO `komuniti_events` (`id`, `name`, `phone`, `proposal`) VALUES
(2, 'AMBER ISABEL FERNANDEZ', '0162941551', 'asdasdasd'),
(3, 'abu babu', '0162941550', 'sdfdbsgfb g stshhe');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `name`, `phone`, `email`, `created_at`) VALUES
(1, 'Ahmad Faizal', '012-3456789', 'ahmad@email.com', '2026-05-20 03:37:59'),
(2, 'Siti Nurhaliza', '019-8765432', 'siti@email.com', '2026-05-20 03:37:59'),
(3, 'Tan Wei Ming', '016-2345678', 'tan@email.com', '2026-05-20 03:37:59');

-- --------------------------------------------------------

--
-- Table structure for table `sponsorships`
--

CREATE TABLE `sponsorships` (
  `id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `package` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `table_reservations`
--

CREATE TABLE `table_reservations` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `venue` varchar(255) NOT NULL,
  `reservation_date` date NOT NULL,
  `time_slot` varchar(50) NOT NULL,
  `guests_count` varchar(50) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_reservations`
--

INSERT INTO `table_reservations` (`id`, `full_name`, `phone_number`, `venue`, `reservation_date`, `time_slot`, `guests_count`, `special_requests`, `is_read`, `status`) VALUES
(1, 'Amber Fernandez', '0162941551', 'Tuah Cafe Bangi', '2026-05-19', '02:00 PM - 04:00 PM', '3-4 Pax', '', 1, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','staff','main_admin') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `created_at`) VALUES
(1, 'abu', '123admin', 'abu', 'staff', '2026-05-20 03:33:06'),
(4, 'ali', 'abc123', 'ali ali', 'main_admin', '2026-05-20 04:15:44'),
(5, 'gaga', 'gaga123', 'gugu gaga', 'staff', '2026-05-20 05:14:26'),
(6, 'amber', 'asdf', 'AMBER ISABEL FERNANDEZ', 'staff', '2026-05-20 13:45:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event_bookings`
--
ALTER TABLE `event_bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `komuniti_events`
--
ALTER TABLE `komuniti_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sponsorships`
--
ALTER TABLE `sponsorships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `table_reservations`
--
ALTER TABLE `table_reservations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event_bookings`
--
ALTER TABLE `event_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `komuniti_events`
--
ALTER TABLE `komuniti_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sponsorships`
--
ALTER TABLE `sponsorships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `table_reservations`
--
ALTER TABLE `table_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
