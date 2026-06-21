-- MoviePlex init.sql - MySQL 8.0 compatible
-- Converted from MariaDB 10.4 dump

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_code` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `showtime_id` int(10) UNSIGNED NOT NULL,
  `seats_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `num_tickets` tinyint(4) NOT NULL DEFAULT 1,
  `subtotal` decimal(12,0) NOT NULL,
  `discount` decimal(12,0) DEFAULT 0,
  `total_amount` decimal(12,0) NOT NULL,
  `payment_method` enum('momo','vnpay','zalopay','napas','cash') DEFAULT 'momo',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `status` enum('confirmed','cancelled','checked_in') DEFAULT 'confirmed',
  `voucher_code` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cancel_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_code` (`booking_code`),
  KEY `user_id` (`user_id`),
  KEY `showtime_id` (`showtime_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `checkin_hourly` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hour_label` varchar(10) NOT NULL,
  `checkins` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cinemas` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) DEFAULT 'Hà Nội',
  `phone` varchar(20) DEFAULT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cinema_halls` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cinema_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `total_seats` int(10) UNSIGNED DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cinema_id` (`cinema_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `emp_code` varchar(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `kpis` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `total_tickets` int(11) NOT NULL DEFAULT 0,
  `total_checkins` int(11) NOT NULL DEFAULT 0,
  `reconciliation_errors` int(11) NOT NULL DEFAULT 0,
  `active_staff` int(11) NOT NULL DEFAULT 0,
  `locked_accounts` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `movies` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `genre` varchar(150) DEFAULT NULL,
  `duration_min` int(11) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `poster_url` varchar(500) DEFAULT NULL,
  `backdrop_url` varchar(500) DEFAULT NULL,
  `trailer_url` varchar(500) DEFAULT NULL,
  `director` varchar(150) DEFAULT NULL,
  `cast_list` text DEFAULT NULL,
  `age_rating` varchar(10) DEFAULT 'T16',
  `status` enum('now_showing','coming_soon','ended') DEFAULT 'coming_soon',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `movie_reviews` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `booking_code` varchar(50) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_movie_booking` (`user_id`,`movie_id`,`booking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `token` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `reconciliation_errors` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_code` varchar(50) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `error_type` varchar(100) NOT NULL,
  `time_ago` varchar(50) NOT NULL,
  `sys_time` varchar(50) NOT NULL,
  `sys_amount` varchar(50) NOT NULL,
  `bank_time` varchar(50) NOT NULL,
  `bank_amount` varchar(50) NOT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sales_trend` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `day_name` varchar(20) NOT NULL,
  `tickets_sold` int(11) NOT NULL DEFAULT 0,
  `order_index` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `seats` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `showtime_id` int(10) UNSIGNED NOT NULL,
  `seat_row` char(1) NOT NULL,
  `seat_num` tinyint(4) NOT NULL,
  `seat_type` enum('standard','vip','sweetbox') DEFAULT 'standard',
  `status` enum('available','booked','hold') DEFAULT 'available',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seat` (`showtime_id`,`seat_row`,`seat_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `showtimes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `cinema_id` int(10) UNSIGNED NOT NULL,
  `hall_name` varchar(50) DEFAULT 'Phòng 01',
  `show_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `format` enum('2D','3D','IMAX','PREMIUM','4DX') DEFAULT '2D',
  `subtitle_type` enum('Phụ đề','Lồng tiếng','Thuyết minh') DEFAULT 'Phụ đề',
  `price` decimal(10,0) DEFAULT 90000,
  `total_seats` int(11) DEFAULT 120,
  `available_seats` int(11) DEFAULT 120,
  `is_cancelled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `movie_id` (`movie_id`),
  KEY `cinema_id` (`cinema_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `snacks` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `img_url` varchar(500) DEFAULT NULL,
  `category` enum('popcorn','drink','combo') DEFAULT 'combo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `support_tickets` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `system_logs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_name` varchar(150) NOT NULL,
  `role` varchar(50) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_desc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `role` enum('user','admin','staff','admin_monitor') NOT NULL DEFAULT 'user',
  `status` enum('active','locked','pending') NOT NULL DEFAULT 'active',
  `loyalty_points` int(10) UNSIGNED DEFAULT 0,
  `member_tier` enum('STANDARD','SILVER','GOLD','PLATINUM') DEFAULT 'STANDARD',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `vouchers` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_pct` tinyint(4) DEFAULT 0,
  `discount_amt` decimal(10,0) DEFAULT 0,
  `min_order` decimal(10,0) DEFAULT 0,
  `max_uses` int(11) DEFAULT 100,
  `used_count` int(11) DEFAULT 0,
  `expire_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Foreign Keys
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`showtime_id`) REFERENCES `showtimes` (`id`) ON DELETE CASCADE;

ALTER TABLE `cinema_halls`
  ADD CONSTRAINT `cinema_halls_ibfk_1` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`) ON DELETE CASCADE;

ALTER TABLE `seats`
  ADD CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`showtime_id`) REFERENCES `showtimes` (`id`) ON DELETE CASCADE;

ALTER TABLE `showtimes`
  ADD CONSTRAINT `showtimes_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showtimes_ibfk_2` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`) ON DELETE CASCADE;

-- ===================== DATA =====================

INSERT INTO `cinemas` VALUES (1,'CGV Vincom Center','191 Bà Triệu, Hai Bà Trưng, Hà Nội','Hà Nội','1900 6017',NULL),(2,'Lotte Cinema Q7','469 Nguyễn Hữu Thọ, Quận 7, TP.HCM','TP.HCM','1900 6666',NULL),(3,'Galaxy Nguyễn Du','116 Nguyễn Du, Quận 1, TP.HCM','TP.HCM','1900 2224',NULL),(4,'CGV Vincom Metropolis','Vincom Metropolis, 29 Liễu Giai, Ba Đình, Hà Nội','Hà Nội','1900 6017',NULL),(5,'BHD Star Phạm Ngọc Thạch','499 Phạm Ngọc Thạch, Đống Đa, Hà Nội','Hà Nội','1900 2345',NULL);

INSERT INTO `movies` VALUES (1,'Dune: Hành Tinh Cát Phần Hai','Hành trình của Paul Atreides trong việc đoàn kết với người Fremen trong khi phải đưa ra một lựa chọn khó khăn nhất.','Hành động, Phiêu lưu, Khoa học viễn tưởng',167,'2024-03-01',10.0,'https://wsrv.nl/?url=image.tmdb.org/t/p/w500/8b8R8l88Qje9dn9OE8PY05Nxl1X.jpg','https://wsrv.nl/?url=image.tmdb.org/t/p/original/xOMo8BRK7PfcJv9JCnx7s5hj0PX.jpg',NULL,'Denis Villeneuve','Timothée Chalamet, Zendaya, Rebecca Ferguson','T16','now_showing','2026-05-19 07:40:55'),(2,'Avatar: Dòng Chảy Của Nước','Jake Sully và Ney\'tiri đã lập gia đình và đang cố gắng ở lại sống với nhau.','Hành động, Phiêu lưu, Khoa học viễn tưởng',192,'2022-12-16',8.5,'https://upload.wikimedia.org/wikipedia/en/5/54/Avatar_The_Way_of_Water_poster.jpg','https://images.unsplash.com/photo-1482862549707-f63cb32c5fd9?q=80&w=1600',NULL,'James Cameron','Sam Worthington, Zoe Saldana, Sigourney Weaver','T13','now_showing','2026-05-19 07:40:55'),(3,'Oppenheimer','Câu chuyện về cuộc đời của J. Robert Oppenheimer - cha đẻ của bom nguyên tử.','Tiểu sử, Lịch sử, Chính kịch',180,'2023-07-21',9.0,'https://upload.wikimedia.org/wikipedia/en/4/4a/Oppenheimer_%28film%29.jpg','https://wsrv.nl/?url=image.tmdb.org/t/p/original/rLb2cwF3Pazuxaj0sRXQ037tGI1.jpg',NULL,'Christopher Nolan','Cillian Murphy, Emily Blunt, Matt Damon','T18','now_showing','2026-05-19 07:40:55'),(4,'Inception','Một tên trộm ăn cắp thông tin bằng cách thâm nhập vào tiềm thức của người ngủ.','Hành động, Khoa học viễn tưởng, Hồi hộp',148,'2010-07-16',9.3,'https://upload.wikimedia.org/wikipedia/en/2/2e/Inception_%282010%29_theatrical_poster.jpg','https://wsrv.nl/?url=image.tmdb.org/t/p/original/s3TBrRGB1iav7gFOCNx3H31MoES.jpg',NULL,'Christopher Nolan','Leonardo DiCaprio, Marion Cotillard, Joseph Gordon-Levitt','T13','now_showing','2026-05-19 07:40:55'),(5,'Interstellar','Câu chuyện về một nhóm nhà thám hiểm sử dụng một con sâu được phát hiện gần Sao Thổ.','Hành động, Kịch tính, Khoa học viễn tưởng',169,'2014-11-07',9.4,'https://upload.wikimedia.org/wikipedia/en/b/bc/Interstellar_film_poster.jpg','https://wsrv.nl/?url=image.tmdb.org/t/p/original/xJHokMbljvjADYdit5fK5VQsXEG.jpg',NULL,'Christopher Nolan','Matthew McConaughey, Anne Hathaway, Jessica Chastain','T13','now_showing','2026-05-19 07:40:55'),(6,'Guardians of the Galaxy Vol. 3','Nhóm Vệ binh Dải Ngân Hà đang cố gắng bảo vệ Rocket.','Hành động, Phiêu lưu, Hài hước',150,'2026-07-24',8.7,'https://upload.wikimedia.org/wikipedia/en/7/74/Guardians_of_the_Galaxy_Vol._3_poster.jpg','https://wsrv.nl/?url=image.tmdb.org/t/p/original/nHf61UzkfFno5X1ofIhugCPus2R.jpg',NULL,'James Gunn','Chris Pratt, Zoe Saldana, Bradley Cooper','T13','coming_soon','2026-05-19 07:40:55'),(7,'The Batman','Khi một kẻ giết người ám ảnh gây ra hành vi tội ác ở Gotham City.','Hành động, Tội phạm, Chính kịch',176,'2026-10-20',8.8,'https://upload.wikimedia.org/wikipedia/en/f/ff/The_Batman_%28film%29_poster.jpg','https://wsrv.nl/?url=image.tmdb.org/t/p/original/b0PlSFdDwbyK0cf5RxwDpaOJQvQ.jpg',NULL,'Matt Reeves','Robert Pattinson, Zoë Kravitz, Paul Dano','T16','coming_soon','2026-05-19 07:40:55'),(8,'Spider-Man: Across the Spider-Verse','Miles Morales trở lại trong một cuộc phiêu lưu sử thi mới.','Hoạt hình, Hành động, Phiêu lưu',140,'2026-07-24',9.1,'https://wsrv.nl/?url=image.tmdb.org/t/p/w500/8Vt6mWEReuy4Of61Lnj5Xj704m8.jpg','https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=1600',NULL,'Joaquim Dos Santos','Shameik Moore, Hailee Steinfeld, Oscar Isaac','T13','coming_soon','2026-05-19 07:40:55');

INSERT INTO `users` VALUES (1,'Admin MovieFlex','admin@movieflex.com','0901234567','$2y$12$2C/BeVyKfd9YP.BN5ybTq.3AOC/cjM3jmr5va1sGtj3N3vrx5e0wG',NULL,'admin','active',0,'STANDARD','2026-05-19 07:40:55','2026-06-20 17:13:42'),(2,'Nguyễn Văn An','an@gmail.com','0912345678','$2y$10$h74AVrRJQDDdwajM1JxB8ONO.0UNR0FcUvqHZEwKLeYDvPlYZxb66',NULL,'user','active',98,'STANDARD','2026-05-19 07:40:55','2026-05-25 21:02:20'),(6,'Trần Đức Phát','tmlong1702@gmail.com','0999999999','$2y$12$CWfL8YWu9zjiafR03WfrGO0FUYP6un6VBtOF4wMVvopkOh2FIOvxm',NULL,'user','active',266,'STANDARD','2026-05-24 08:12:58','2026-06-20 17:15:39'),(8,'Trần phát','nghia@gmail.com','0377272727','$2y$12$PNwijGP5GGkltE/qRlXhhup7Z4pqD0LbBE/Kr3yyGwF5gHmcqayE6',NULL,'user','active',0,'STANDARD','2026-05-24 13:15:15','2026-05-24 20:15:20'),(9,'Nhân Viên Quầy','staff@movieflex.vn',NULL,'$2y$12$MmnEiwQIXdGaW.zYXQd.QOxEl83h85JSARXC1z/MPCqK2s2yBhZni',NULL,'staff','active',0,'STANDARD','2026-05-25 08:29:11','2026-06-20 17:13:59'),(10,'Khách Vãng Lai','counter_guest@movieflex.vn',NULL,'$2y$10$cWFNhM5B8w8637zRfGApD.4oTLdEDLEe4kv//adB4Tis8xhZ5iWCa',NULL,'user','active',0,'STANDARD','2026-05-25 08:35:21',NULL),(11,'Admin Monitor','monitor@movieflex.vn',NULL,'$2y$12$wdMDAKM50lQZWZY784enh.pC1VdP.dRXwC/nLTqRurZQCgaMKHuSe',NULL,'admin','active',0,'STANDARD','2026-05-26 15:22:18','2026-05-26 23:13:06');

INSERT INTO `checkin_hourly` VALUES (1,'08:00',80),(2,'10:00',120),(3,'12:00',210),(4,'14:00',180),(5,'16:00',320),(6,'18:00',580),(7,'20:00',850),(8,'22:00',420);

INSERT INTO `kpis` VALUES (1,8420,7150,3,14,2);

INSERT INTO `sales_trend` VALUES (1,'Thứ Hai',450,1),(2,'Thứ Ba',380,2),(3,'Thứ Tư',520,3),(4,'Thứ Năm',410,4),(5,'Thứ Sáu',680,5),(6,'Thứ Bảy',950,6),(7,'Chủ Nhật',1100,7);

INSERT INTO `snacks` VALUES (1,'Bắp rang bơ size L',45000,NULL,'popcorn'),(2,'Bắp rang caramel size M',40000,NULL,'popcorn'),(3,'Nước ngọt Pepsi size L',35000,NULL,'drink'),(4,'Combo 1 bắp + 1 nước',75000,NULL,'combo'),(5,'Combo 2 bắp + 2 nước',130000,NULL,'combo');

INSERT INTO `employees` VALUES (1,'NV001','Nguyễn Văn Đô','donv@movieflex.com','Admin Portal','active','2026-05-24 05:19:39'),(2,'NV002','Trần Minh Anh','anhtm@movieflex.com','Kế toán trưởng','active','2026-05-24 05:19:39'),(3,'NV003','Lê Thị Bình','binhlt@movieflex.com','Nhân viên CSKH','active','2026-05-24 05:19:39'),(4,'NV004','Hoàng Minh Đức','duchm@movieflex.com','Kỹ thuật viên phòng máy','active','2026-05-24 05:19:39'),(5,'NV005','Phạm Hồng Nhung','nhungph@movieflex.com','Marketing Executive','locked','2026-05-24 05:19:39');

INSERT INTO `reconciliation_errors` VALUES (1,'#MF20260524ERR1','Galaxy Nguyễn Du','LỆCH GIÁ TIỀN (Hệ thống > Ngân hàng)','2 phút trước','24/05/2026 10:15','120.000₫','24/05/2026 10:17','100.000₫',0),(2,'#MF20260524ERR2','CGV Vincom Center','THIẾU GIAO DỊCH NGÂN HÀNG','15 phút trước','24/05/2026 09:40','150.000₫','—','0₫',0),(3,'#MF20260524ERR3','Galaxy Nguyễn Du','SAI BIÊN LAI THANH TOÁN','1 giờ trước','24/05/2026 08:12','90.000₫','24/05/2026 08:12','80.000₫',0);

INSERT INTO `support_tickets` VALUES (1,'Trần Đức Phát','phatnha1702@gmail.com',NULL,'adsd','sdsdsd','pending','2026-05-19 13:30:25');

INSERT INTO `password_resets` VALUES (2,'phatnha1702@gmail.com','581450','2026-05-19 14:58:40','2026-05-19 22:13:40');

INSERT INTO `movie_reviews` VALUES (1,2,1,'MF20260519001',10,'Hay mà đau ví :_))','2026-05-19 10:37:23'),(4,3,1,'MF202605191E2ECE',10,'chào bạn','2026-05-20 01:33:52');

-- AUTO_INCREMENT reset
ALTER TABLE `bookings` AUTO_INCREMENT = 45;
ALTER TABLE `checkin_hourly` AUTO_INCREMENT = 9;
ALTER TABLE `cinemas` AUTO_INCREMENT = 7;
ALTER TABLE `cinema_halls` AUTO_INCREMENT = 37;
ALTER TABLE `employees` AUTO_INCREMENT = 6;
ALTER TABLE `kpis` AUTO_INCREMENT = 2;
ALTER TABLE `movies` AUTO_INCREMENT = 9;
ALTER TABLE `movie_reviews` AUTO_INCREMENT = 5;
ALTER TABLE `password_resets` AUTO_INCREMENT = 3;
ALTER TABLE `reconciliation_errors` AUTO_INCREMENT = 4;
ALTER TABLE `sales_trend` AUTO_INCREMENT = 8;
ALTER TABLE `showtimes` AUTO_INCREMENT = 993;
ALTER TABLE `snacks` AUTO_INCREMENT = 6;
ALTER TABLE `support_tickets` AUTO_INCREMENT = 2;
ALTER TABLE `system_logs` AUTO_INCREMENT = 27;
ALTER TABLE `users` AUTO_INCREMENT = 12;
ALTER TABLE `vouchers` AUTO_INCREMENT = 36;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;