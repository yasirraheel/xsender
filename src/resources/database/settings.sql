-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 06, 2025 at 06:04 AM
-- Server version: 8.0.41-0ubuntu0.24.04.1
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xsender`
--

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `uid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel` enum('email','sms','whatsapp') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `uid`, `channel`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'rFyYKwQO-5QJsTuH0s6qU5k-kyM08PW4', NULL, 'email', 'xsender@admin.com', NULL, NULL),
(2, '2TJubrjz-xY2zmhhqJN9h0w-UthyilfK', NULL, 'phone', NULL, NULL, NULL),
(3, 'yp7lgEgq-QtF93UkA3hF3tQ-EqEoeFY1', NULL, 'plugin', '1', NULL, NULL),
(4, '0QXGbYSc-hCCUZOpKIkIGOV-F4vBD7ef', NULL, 'captcha', '0', NULL, NULL),
(5, '0B30ihTm-juv55N1ESURaHE-HAk57B7G', NULL, 'address', NULL, NULL, NULL),
(6, '203Lx1iO-w3r657IPrXLErx-4ZCO7HR9', NULL, 'google_map_iframe', NULL, NULL, NULL),
(7, 'I6aVANCA-VoQOOf9vxTw3Gy-Akc6Jp41', NULL, 'site_name', 'xsender', NULL, NULL),
(8, 'VHkaCCnV-KVgvh5kk8JZYaj-KyjzjtX5', NULL, 'time_zone', 'UTC', NULL, NULL),
(9, '1mNQq92G-oBRS8IX0MkvCju-fks5dYfl', NULL, 'app_version', '3.2.4', NULL, NULL),
(10, '7HWZnBWQ-75HCj8E1sOyrjn-tbWcKQsR', NULL, 'country_code', '1', NULL, NULL),
(11, '1zwVvFtQ-fXkaf5aVvSCsBX-rQFiwQUX', NULL, 'currency_name', 'USD', NULL, NULL),
(12, 'V0UZBbxd-bSdtpANj8zUjRo-9oVXI4H8', NULL, 'currency_symbol', '$', NULL, NULL),
(13, '2mK5ZcR9-wtatC2tpSBF8Bk-uPJu6I07', NULL, 'webhook_verify_token', 'xsender', NULL, NULL),
(14, '1ulzrxlO-4cXY563NNdQ5PW-lAGpE4OU', NULL, 'api_sms_method', '0', NULL, NULL),
(15, '3B5Auk4a-7p0Bbt9Kdvd6Hq-UDajdHkY', NULL, 'app_link', '###', NULL, NULL),
(16, '6KL6gkY5-xX5d4vjGOOg8jr-MmmMgnnE', NULL, 'theme_dir', '0', NULL, NULL),
(17, '7FQQHEvc-K9Q07vyo86llm3-9rnZlfo0', NULL, 'theme_mode', '1', NULL, NULL),
(18, '6eJQn1dP-aoYalwvjbwYE6x-UJ1X1KL2', NULL, 'social_login', '0', NULL, NULL),
(19, '8hRLHqZa-m0cMK52lBUZxU4-1pqVVUz3', NULL, 'social_login_with', '{\"google_oauth\":{\"status\":\"1\",\"client_id\":\"580301070453-job03fms4l7hrlnobt7nr5lbsk9bvoq9.apps.googleusercontent.com\",\"client_secret\":\"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\"}}', NULL, NULL),
(20, '03AXWJKo-60eh7Z07v6gmwp-9yeBJcgd', NULL, 'available_plugins', '{\"beefree\":{\"status\":\"1\",\"client_id\":\"b2369021-3e95-4ca4-a8c8-2ed3e2531865\",\"client_secret\":\"uL3UKV8V4RLv77vodnNTM8e93np9OYsS5P2mJ0373Nt9ghbwoRbn\"}}', NULL, NULL),
(21, 'GDiKkJ06-eBe8iz6V382VqV-pgJtUJ09', NULL, 'member_authentication', '{\"registration\":\"1\",\"login\":\"1\"}', NULL, NULL),
(22, '6c1zQKTY-fR9AvXpvbrlBOD-FySl17wT', NULL, 'google_recaptcha', '{\"status\":\"0\",\"key\":\"6Lc5PpImAAAAABM-m4EgWw8vGEb7Tqq5bMOSI1Ot\",\"secret_key\":\"6Lc5PpImAAAAACdUh5Hth8NXRluA04C-kt4Xdbw7\"}', NULL, NULL),
(23, '8v2xSfZH-0PqOHfBpmmAzXl-KiA87YD4', NULL, 'captcha_with_login', '0', NULL, NULL),
(24, 'Z7TY2zuX-W47ixYAZ3gTJBq-nNqul5t8', NULL, 'captcha_with_registration', '0', NULL, NULL),
(25, '2e2qgVPH-LUy76cLuru66gA-xYEjh4bD', NULL, 'registration_otp_verification', '1', NULL, NULL),
(26, '2ymRPCEi-K0O2tceMZBeUA0-qFVC30Rs', NULL, 'email_otp_verification', '1', NULL, NULL),
(27, '9cHlp4o7-Kdrp4Yf8p31A6q-4KKdxSGE', NULL, 'otp_expired_status', '0', NULL, NULL),
(28, '7GfONHOZ-NNXHrpdr2XG2GP-ReAaL8dA', NULL, 'email_notifications', '1', NULL, NULL),
(29, '9BUd8sMA-0dDix4fZHfIinL-fTw1EAAV', NULL, 'default_email_template', 'hi, {{message}}', NULL, NULL),
(30, 'VncrZ8aO-mlQYdHcyE7yFR7-yB3q9T67', NULL, 'contact_meta_data', '{\"date_of_birth\":{\"status\":\"1\",\"type\":1}}', NULL, NULL),
(31, 'r8XP36e4-LZrZwxOSP8MXIO-PVFo9Nk1', NULL, 'last_cron_run', '2024-08-27 04:05:01', NULL, NULL),
(32, 'wHdX8sBS-GnRWoW6B9HAfW8-UN8Zv3c6', NULL, 'onboarding_bonus', '1', NULL, NULL),
(33, 'G3oXJ1z4-D5990vtUAEpU9S-8M5MD7O4', NULL, 'onboarding_bonus_plan', '1', NULL, NULL),
(34, 'UMLAozGc-xgEHgP8rqfWY1m-zxpVQeG9', NULL, 'debug_mode', '0', NULL, NULL),
(35, '82pa8Qtl-FVF7XaQsPIr1ie-cG3S0toS', NULL, 'maintenance_mode', '0', NULL, NULL),
(36, '0avaIyi7-Jqc6cd0yjg0AZj-vpkPbnHM', NULL, 'maintenance_mode_message', '<p>Please be advised that there will be scheduled downtime across our network from 12.00AM to 2.00AM</p>', NULL, NULL),
(37, 'GKlQxcJI-VPq924JOzH7VgW-yseto2C3', NULL, 'landing_page', '1', NULL, NULL),
(38, '8Tv5Oohf-jsdQI3BHUuQpW6-rRUG8iI1', NULL, 'whatsapp_word_count', '320', NULL, NULL),
(39, '8zKUyTGG-SUhN5t0hGjp8RO-zbrW84mu', NULL, 'sms_word_count', '320', NULL, NULL),
(40, '2xPkslLo-GJyb12kS11icWb-tM4dEN78', NULL, 'sms_word_unicode_count', '320', NULL, NULL),
(41, 'KAnrxpSi-2acNDru8x08Yfg-ybrVmBn0', NULL, 'primary_color', '#f25d6d', NULL, NULL),
(42, '0iz3UQDs-84aDS7aDPoOEDH-cOTdzx00', NULL, 'secondary_color', '#f64b4d', NULL, NULL),
(43, 'qireFSc1-ctxFGLJS3W4sGz-IkyBeSN3', NULL, 'trinary_color', '#ffa360', NULL, NULL),
(44, 'luP6P2Ai-RJZMCmzcEPpjVA-E62axLw4', NULL, 'primary_text_color', '#ffffff', NULL, NULL),
(45, 'zVkjKXs9-Cba9axyFjLo5vI-m9TJkNs5', NULL, 'copyright', 'iGen Solutions', NULL, NULL),
(46, 'mSvG0735-xwXCbqyGBHU24F-UnwIPul4', NULL, 'mime_types', '[\"jpeg\",\"jpg\",\"png\",\"svg\",\"webp\"]', NULL, NULL),
(47, 'UrGrB5hN-PBWVBPmrD5Zc9y-CXdowpr2', NULL, 'max_file_size', '20000', NULL, NULL),
(48, 'dd78N5hh-SxBwM1lnPLw1wf-032hSkh8', NULL, 'max_file_upload', '4', NULL, NULL),
(49, 'p8Ubg6jn-JlWc1ZOynaVC9l-PR8qaSF7', NULL, 'currencies', '{\"BDT\":{\"name\":\"Bangladeshi Taka\",\"symbol\":\"\\u09f3\",\"rate\":\"114\",\"status\":\"1\",\"is_default\":\"0\"},\"USD\":{\"name\":\"United States Dollar\",\"symbol\":\"$\",\"rate\":\"0.005\",\"status\":\"1\",\"is_default\":\"1\"}}', NULL, NULL),
(50, 'GQb7ax68-yuUIaw8jkX8Tj3-lqho8qI8', NULL, 'paginate_number', '20', NULL, NULL),
(51, 'Wqv7Cv7Z-e4at1mBCphFC4I-YK3gKS60', NULL, 'auth_heading', 'Start turning your ideas into reality.', NULL, NULL),
(52, '4INBxIeE-NowH2bQoIR8VTa-3IzWy47P', NULL, 'authentication_background', '67de83bb5571c1742635963.webp', NULL, NULL),
(53, 'fRSVqw0H-xfrxtnm3odVOeb-z3Et0Bf0', NULL, 'authentication_background_inner_image_one', '67de83bb8edcf1742635963.webp', NULL, NULL),
(54, '30dyexAV-WgSvUkfA8DWH7w-557tR0wU', NULL, 'authentication_background_inner_image_two', '67de83bb989b11742635963.webp', NULL, NULL),
(55, 'GhjR5iQW-JM1COfpsp6HC1T-oHmel0M5', NULL, 'meta_title', 'Welcome To Xsender', NULL, NULL),
(56, 'odzKTTck-wTYHeiIo23vfAk-SjIa1VA4', NULL, 'meta_description', 'Start your marketing journey today', NULL, NULL),
(57, '5bF6uvpw-kiutKDZjocfNxp-EbllI028', NULL, 'meta_keywords', '[\"bulk\",\"sms\",\"email\",\"whatsapp\",\"marketing\"]', NULL, NULL),
(58, NULL, NULL, 'site_logo', '66e9dd6484e241726602596.webp', NULL, NULL),
(59, NULL, NULL, 'site_square_logo', '66e9dd64e27d11726602596.webp', NULL, NULL),
(60, NULL, NULL, 'panel_logo', '66e9dd64e9c721726602596.webp', NULL, NULL),
(61, NULL, NULL, 'panel_square_logo', '66e9dd64f10b61726602596.webp', NULL, NULL),
(62, NULL, NULL, 'favicon', '66e9dd65033111726602597.webp', NULL, NULL),
(63, NULL, NULL, 'meta_image', '66e9dd65076b11726602597.webp', NULL, NULL),
(64, NULL, NULL, 'store_as_webp', '1', NULL, NULL),
(65, NULL, NULL, 'last_reset_time', '2024-08-27 04:04:09', NULL, NULL),
(66, NULL, NULL, 'filter_duplicate_contact', '0', NULL, NULL),
(67, NULL, '', 'queue_connection_config', '{\"driver\":\"database\"}', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settings_uid_index` (`uid`),
  ADD KEY `settings_channel_index` (`channel`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
