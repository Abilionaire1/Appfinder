-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 06, 2026 at 07:40 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `appfinder`
--

-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

CREATE TABLE `apps` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `category_id` int NOT NULL,
  `description` text,
  `developer` varchar(100) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) NOT NULL DEFAULT '0.0',
  `reviews_count` int NOT NULL DEFAULT '0',
  `play_store_url` varchar(255) NOT NULL,
  `screenshots` text,
  `is_free` tinyint(1) NOT NULL DEFAULT '1',
  `platform` enum('android','ios','both') NOT NULL DEFAULT 'android',
  `has_ads` tinyint(1) NOT NULL DEFAULT '0',
  `has_inapp` tinyint(1) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `download_count` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `license` varchar(100) DEFAULT 'Freeware',
  `alternative_to` varchar(255) DEFAULT '',
  `website_url` varchar(255) DEFAULT '',
  `is_open_source` tinyint(1) DEFAULT '0',
  `version` varchar(50) DEFAULT '1.0',
  `platforms` varchar(100) DEFAULT 'Android, Web'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `apps`
--

INSERT INTO `apps` (`id`, `name`, `slug`, `category_id`, `description`, `developer`, `icon`, `rating`, `reviews_count`, `play_store_url`, `screenshots`, `is_free`, `platform`, `has_ads`, `has_inapp`, `featured`, `download_count`, `created_at`, `updated_at`, `license`, `alternative_to`, `website_url`, `is_open_source`, `version`, `platforms`) VALUES
(1, 'Blender', 'blender', 7, 'Suite pembuatan 3D open-source kelas dunia untuk modeling, rigging, animasi, simulasi, rendering, dan video editing tanpa batasan royalti.', 'Blender Foundation', 'https://upload.wikimedia.org/wikipedia/commons/0/0c/Blender_logo_no_text.svg', 4.9, 14800, 'https://play.google.com/store/apps/details?id=org.blender.blender', NULL, 1, 'android', 0, 0, 1, '10M+', '2026-10-04 15:57:05', '2026-10-04 15:57:05', 'GPL v3', 'Autodesk Maya, 3ds Max, Cinema 4D', 'https://www.blender.org', 1, '4.2 LTS', 'Windows, macOS, Linux'),
(2, 'Visual Studio Code', 'visual-studio-code', 4, 'Code editor modern super cepat dan fleksibel dengan dukungan ekosistem ribuan ekstensi, debugging terintegrasi, dan kontrol Git bawaan.', 'Microsoft', 'https://upload.wikimedia.org/wikipedia/commons/9/9a/Visual_Studio_Code_1.35_icon.svg', 4.8, 52000, 'https://play.google.com/store/apps', NULL, 1, 'android', 0, 0, 1, '50M+', '2026-10-04 15:57:05', '2026-10-04 15:57:05', 'MIT / Code-OSS', 'Sublime Text, JetBrains WebStorm', 'https://code.visualstudio.com', 1, '1.93', 'Windows, macOS, Linux, Web'),
(3, 'GIMP', 'gimp', 3, 'Program manipulasi gambar GNU untuk photo retouching canggih, komposisi gambar, dan pembuatan grafik ilustrasi bebas royalti.', 'GIMP Team', 'https://upload.wikimedia.org/wikipedia/commons/4/45/The_GIMP_icon_-_gnome.svg', 4.6, 9500, 'https://play.google.com/store/apps', NULL, 1, 'android', 0, 0, 1, '25M+', '2026-10-04 15:57:05', '2026-10-04 15:57:05', 'GNU GPLv3', 'Adobe Photoshop, Corel Photo-Paint', 'https://www.gimp.org', 1, '2.10.38', 'Windows, macOS, Linux'),
(4, 'LibreOffice', 'libreoffice', 2, 'Office suite lengkap dan bertenaga tinggi mencakup Writer, Calc, Impress, dan Draw. Format file 100% kompatibel dengan Microsoft Office.', 'The Document Foundation', 'https://upload.wikimedia.org/wikipedia/commons/4/4d/LibreOffice_Logo.svg', 4.7, 18400, 'https://play.google.com/store/apps', NULL, 1, 'android', 0, 0, 1, '100M+', '2026-10-04 15:57:05', '2026-10-04 15:57:05', 'MPL 2.0', 'Microsoft 365, MS Office', 'https://www.libreoffice.org', 1, '24.8', 'Windows, macOS, Linux'),
(5, 'VLC Media Player', 'vlc-media-player', 6, 'Pemutar multimedia universal open-source yang memutar hampir semua format file video, audio, DVD, dan streaming tanpa butuh download codec tambahan.', 'VideoLAN', 'https://upload.wikimedia.org/wikipedia/commons/e/e6/VLC_Icon_%282012-present%29.svg', 4.8, 125000, 'https://play.google.com/store/apps/details?id=org.videolan.vlc', NULL, 1, 'android', 0, 0, 1, '500M+', '2026-10-04 15:57:05', '2026-10-04 15:57:05', 'GPL v2+', 'Windows Media Player, QuickTime', 'https://www.videolan.org', 1, '3.5.4', 'Android, iOS, Windows, Mac'),
(6, 'Obsidian', 'obsidian', 2, 'Aplikasi pencatat basis data pengetahuan pribadi kedua (second brain) berbasis Markdown lokal yang aman dan tidak bergantung cloud vendor.', 'Dynalist Inc.', 'https://upload.wikimedia.org/wikipedia/commons/1/10/Obsidian_Logo.png', 4.8, 31000, 'https://play.google.com/store/apps/details?id=md.obsidian', NULL, 1, 'android', 0, 0, 1, '5M+', '2026-10-04 15:57:05', '2026-10-04 15:57:05', 'Freeware (Personal)', 'Notion, Roam Research, Bear', 'https://obsidian.md', 0, '1.6.7', 'Android, iOS, Windows, Mac'),
(7, 'Audacity', 'audacity', 8, 'Multi-track audio editor dan recorder serbaguna untuk kebutuhan rekaman podcast, mixing instrumen, speech enhancement, dan konversi format audio.', 'Muse Group', 'https://upload.wikimedia.org/wikipedia/commons/c/ca/Audacity_Logo.svg', 4.7, 22000, 'https://play.google.com/store/apps', NULL, 1, 'android', 0, 0, 0, '100M+', '2026-10-04 15:57:05', '2026-10-04 15:57:05', 'GPL v3', 'Adobe Audition, Reaper', 'https://www.audacityteam.org', 1, '3.6.4', 'Windows, macOS, Linux'),
(8, 'Krita', 'krita', 7, 'Studio lukis dan ilustrasi digital profesional dengan brush engine mutakhir, stabilisator goresan tangan, dan dukungan animasi frame-by-frame.', 'Krita Foundation', 'https://upload.wikimedia.org/wikipedia/commons/7/73/Calligrakrita-base.svg', 4.7, 19500, 'https://play.google.com/store/apps/details?id=org.krita', NULL, 1, 'android', 0, 0, 1, '10M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'GPL v3', 'Clip Studio Paint, Photoshop, Procreate', 'https://krita.org', 1, '5.2.3', 'Android Tablet, Windows, macOS, Linux'),
(9, 'CapCut', 'capcut', 3, 'Video editor mobile & desktop intuitif dengan efek AI instan, template tren modern, auto-caption akurat, dan chroma keying halus.', 'Bytedance Pte. Ltd.', 'https://play-lh.googleusercontent.com/s65a9v0QGZ42a_s2sD-z_W_4T_7yH4xY3A62n2o1b1c', 4.5, 890000, 'https://play.google.com/store/apps/details?id=com.lemon.lvoverseas', NULL, 1, 'android', 0, 0, 1, '1B+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'Freemium', 'Adobe Premiere Pro, InShot', 'https://www.capcut.com', 0, '12.4', 'Android, iOS, Windows, Mac'),
(10, 'Godot Engine', 'godot-engine', 4, 'Game engine 2D & 3D open-source terkemuka dengan node workflow yang elegan, GDScript responsif, dan tanpa potongan royalti seumur hidup.', 'Godot Foundation', 'https://upload.wikimedia.org/wikipedia/commons/6/6a/Godot_icon_%282014%29.svg', 4.9, 14300, 'https://play.google.com/store/apps/details?id=org.godotengine.editor.v4', NULL, 1, 'android', 0, 0, 1, '5M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'MIT License', 'Unity, GameMaker', 'https://godotengine.org', 1, '4.3', 'Android, Windows, macOS, Linux'),
(11, 'Duolingo', 'duolingo', 1, 'Aplikasi belajar bahasa asing berbasis gamifikasi terpopuler di dunia dengan pelajaran interaktif singkat yang terbukti efektif dan gratis.', 'Duolingo', 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Duolingo_2019_logo.svg', 4.7, 18000000, 'https://play.google.com/store/apps/details?id=com.duolingo', NULL, 1, 'android', 0, 0, 1, '500M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'Freemium', 'Babbel, Rosetta Stone', 'https://www.duolingo.com', 0, '5.160', 'Android, iOS, Web'),
(12, 'Khan Academy', 'khan-academy', 1, 'Pendidikan kelas dunia tanpa dipungut biaya untuk siapa saja, di mana saja. Mencakup matematika, sains, ekonomi, dan sejarah.', 'Khan Academy', 'https://upload.wikimedia.org/wikipedia/commons/0/06/Khan_Academy_Logo_Old_version_%282018%29.svg', 4.8, 195000, 'https://play.google.com/store/apps/details?id=org.khanacademy.android', NULL, 1, 'android', 0, 0, 0, '10M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', '100% Free / Non-Profit', 'Coursera, Udemy', 'https://www.khanacademy.org', 0, '7.5', 'Android, iOS, Web'),
(13, 'Bitwarden', 'bitwarden', 2, 'Password manager open-source dengan enkripsi end-to-end zero-knowledge yang mengamankan kredensial login Anda di seluruh perangkat secara gratis.', 'Bitwarden Inc.', 'https://upload.wikimedia.org/wikipedia/commons/1/1a/Bitwarden_logo.svg', 4.7, 110000, 'https://play.google.com/store/apps/details?id=com.x8bit.bitwarden', NULL, 1, 'android', 0, 0, 0, '10M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'GPL v3', '1Password, LastPass, Dashlane', 'https://bitwarden.com', 1, '2024.9', 'Android, iOS, Windows, Mac, Linux, Browser'),
(14, 'Inkscape', 'inkscape', 7, 'Editor grafik vektor open-source profesional untuk ilustrasi, logo, diagram, dan tipografi dengan format standar industri SVG murni.', 'Inkscape Community', 'https://upload.wikimedia.org/wikipedia/commons/0/08/Inkscape_logo_%282-color%29.svg', 4.6, 8400, 'https://play.google.com/store/apps', NULL, 1, 'android', 0, 0, 0, '20M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'GPL v3', 'Adobe Illustrator, CorelDRAW', 'https://inkscape.org', 1, '1.3.2', 'Windows, macOS, Linux'),
(15, 'Signal', 'signal', 6, 'Aplikasi pesan instan pribadi dengan standar enkripsi end-to-end tertinggi, tanpa pelacak data pengguna dan dikelola yayasan nirlaba.', 'Signal Foundation', 'https://upload.wikimedia.org/wikipedia/commons/8/8d/Signal-Logo.svg', 4.6, 2400000, 'https://play.google.com/store/apps/details?id=org.thoughtcrime.securesms', NULL, 1, 'android', 0, 0, 0, '100M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'GPL v3', 'WhatsApp, Telegram', 'https://signal.org', 1, '7.18', 'Android, iOS, Windows, Mac'),
(16, 'Quran.com Mobile', 'quran-com-mobile', 10, 'Baca, dengar, dan pelajari Al-Quran secara digital tanpa iklan, terjemahan multisumber lengkap, dan rekaman audio qari internasional.', 'Quran.com', 'https://quran.com/favicon.ico', 4.9, 89000, 'https://play.google.com/store/apps/details?id=com.quran.labs.androidquran', NULL, 1, 'android', 0, 0, 1, '10M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', '100% Free / Open', 'Paid Quran Apps', 'https://quran.com', 1, '3.6.0', 'Android, iOS, Web'),
(17, 'Calibre', 'calibre', 9, 'Manajer e-book komprehensif untuk katalogisasi koleksi buku digital, konversi antarekstensi EPUB/MOBI/PDF, dan sinkronisasi perangkat e-reader.', 'Kovid Goyal', 'https://upload.wikimedia.org/wikipedia/commons/5/5f/Calibre_logo.svg', 4.8, 14000, 'https://play.google.com/store/apps', NULL, 1, 'android', 0, 0, 0, '15M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'GPL v3', 'Kindle App, Adobe Digital Editions', 'https://calibre-ebook.com', 1, '7.18', 'Windows, macOS, Linux'),
(18, 'Snapseed', 'snapseed', 3, 'Editor foto kelas profesional garapan Google dengan fitur pengeditan RAW komprehensif, filter kurva kontrol titik, dan penghilang noda akurat.', 'Google LLC', 'https://upload.wikimedia.org/wikipedia/commons/4/4b/Snapseed_logo.png', 4.4, 1600000, 'https://play.google.com/store/apps/details?id=com.niksoftware.snapseed', NULL, 1, 'android', 0, 0, 1, '100M+', '2026-10-04 15:57:06', '2026-10-04 15:57:06', 'Freeware (100% Free)', 'Adobe Lightroom Mobile', 'https://play.google.com/store/apps/details?id=com.niksoftware.snapseed', 0, '2.21', 'Android, iOS'),
(19, 'YouTube', 'youtube', 5, 'Platform streaming video dan siaran langsung global terbesar di dunia dengan miliaran konten kreator, musik, dan edukasi bebas akses.', 'Google LLC', 'https://upload.wikimedia.org/wikipedia/commons/e/ef/Youtube_logo.png', 4.3, 140000000, 'https://play.google.com/store/apps/details?id=com.google.android.youtube', NULL, 1, 'android', 0, 0, 1, '10B+', '2026-10-04 16:21:34', '2026-10-04 16:21:34', 'Freeware (Ad-Supported)', 'Paid Cable TV', 'https://www.youtube.com', 0, '19.38', 'Android, iOS, Web, Smart TV'),
(20, 'Twitch', 'twitch', 5, 'Platform live streaming terkemuka untuk siaran langsung game esports, musik, podcast, dan komunitas kreatif secara real-time.', 'Twitch Interactive, Inc.', 'https://upload.wikimedia.org/wikipedia/commons/d/d3/Twitch_Glitch_Logo_Purple.svg', 4.4, 5400000, 'https://play.google.com/store/apps/details?id=tv.twitch.android.app', NULL, 1, 'android', 0, 0, 0, '100M+', '2026-10-04 16:21:34', '2026-10-04 16:21:34', 'Freeware (Free Tier)', 'Paid Streaming Services', 'https://www.twitch.tv', 0, '19.4', 'Android, iOS, Web');

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `app_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `created_at`) VALUES
(1, 'Education', 'education', '📚', 'Belajar bahasa, kursus online, dan materi pendidikan gratis.', '2026-10-04 10:59:39'),
(2, 'Office', 'office', '📄', 'Dokumen, spreadsheet, presentasi, dan produktivitas kerja.', '2026-10-04 10:59:39'),
(3, 'Editing', 'editing', '🎨', 'Edit foto dan video, termasuk aplikasi photography.', '2026-10-04 10:59:39'),
(4, 'Coding', 'coding', '💻', 'Belajar programming, versioning, dan terminal Android.', '2026-10-04 10:59:39'),
(5, 'Streaming', 'streaming', '📺', 'Platform streaming video gratis dan open.', '2026-10-04 10:59:39'),
(6, 'Entertainment', 'entertainment', '🎭', 'Hiburan dan komunikasi — media sosial, chat, dan streaming pendek.', '2026-10-04 10:59:39'),
(7, 'Art', 'art', '🖌️', 'Menggambar, ilustrasi, dan desain digital.', '2026-10-04 10:59:39'),
(8, 'Music', 'music', '🎵', 'Putar musik, cari lirik, dan dengarkan tanpa batas.', '2026-10-04 10:59:39'),
(9, 'Books', 'books', '📖', 'Baca e-book gratis dan novel dari Various sumber legal.', '2026-10-04 10:59:39'),
(10, 'Quran', 'quran', '📿', 'Al-Qur’an digital, terjemahan, dan murojaah.', '2026-10-04 10:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@appfinder.local', '$2y$10$f0KA598irKcw6ccqqxHVmu6B9e/otKvhLMlWmd6nWMZJFUB1Pe5dW', 'admin', '2026-10-04 10:59:40'),
(2, 'testuser', 'test@example.com', '$2y$10$3Iog3Wod6CswpgYO9/J4/e59CVpLXiN50yqFgMPmFSFOZeKLsSAE.', 'user', '2026-10-04 12:37:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apps`
--
ALTER TABLE `apps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_apps_category` (`category_id`),
  ADD KEY `idx_apps_featured` (`featured`);
ALTER TABLE `apps` ADD FULLTEXT KEY `ft_apps_search` (`name`,`description`,`developer`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_app` (`user_id`,`app_id`),
  ADD KEY `fk_bm_app` (`app_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apps`
--
ALTER TABLE `apps`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apps`
--
ALTER TABLE `apps`
  ADD CONSTRAINT `fk_apps_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `fk_bm_app` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
