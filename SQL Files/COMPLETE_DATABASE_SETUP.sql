-- ================================================
-- E-SATAHAN COMPLETE DATABASE SETUP
-- Database: onssdb
-- ================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ================================================
-- CREATE DATABASE
-- ================================================
CREATE DATABASE IF NOT EXISTS `onssdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `onssdb`;

-- ================================================
-- CORE TABLES
-- ================================================

-- Table structure for table `tbluser`
CREATE TABLE `tbluser` (
  `ID` int(5) NOT NULL,
  `FullName` varchar(250) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Email` varchar(250) DEFAULT NULL,
  `Password` varchar(250) DEFAULT NULL,
  `RegDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Insert sample users
INSERT INTO `tbluser` (`ID`, `FullName`, `MobileNumber`, `Email`, `Password`, `RegDate`) VALUES
(1, 'Nimal Kumara', 9798789789, 'nimal@gmail.com', '202cb962ac59075b964b07152d234b70', '2022-06-06 13:36:36'),
(2, 'Anuja Kumari', 1425362514, 'ak@gmail.com', 'f925916e2754e5e03f75dd58a5733251', '2022-06-11 11:48:57'),
(3, 'Raju', 7897979878, 'raju@gmail.com', '202cb962ac59075b964b07152d234b70', '2023-12-14 05:26:12'),
(4, 'John Doe', 1122112211, 'john12@gmail.com', 'f925916e2754e5e03f75dd58a5733251', '2023-12-15 17:46:20');

-- Table structure for table `tblnotes`
CREATE TABLE `tblnotes` (
  `ID` int(5) NOT NULL,
  `UserID` int(5) DEFAULT NULL,
  `Subject` varchar(250) DEFAULT NULL,
  `NotesTitle` varchar(250) DEFAULT NULL,
  `NotesDecription` longtext DEFAULT NULL,
  `File1` varchar(250) DEFAULT NULL,
  `File2` varchar(250) DEFAULT NULL,
  `File3` varchar(255) DEFAULT NULL,
  `File4` varchar(250) DEFAULT NULL,
  `CreationDate` timestamp NULL DEFAULT current_timestamp(),
  `UpdationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample notes
INSERT INTO `tblnotes` (`ID`, `UserID`, `Subject`, `NotesTitle`, `NotesDecription`, `File1`, `File2`, `File3`, `File4`, `CreationDate`, `UpdationDate`) VALUES
(1, 3, 'Math', 'Maths Shortcuts', 'It contain math shortcuts.', 'd41d8cd98f00b204e9800998ecf8427e1702536045.pdf', 'd41d8cd98f00b204e9800998ecf8427e1702536260', 'd41d8cd98f00b204e9800998ecf8427e1702536700', 'd41d8cd98f00b204e9800998ecf8427e1702534796.pdf', '2023-12-14 06:19:56', '2023-12-14 06:51:40'),
(2, 3, 'English', 'English Vocabulary', 'English vocabulary and grammar tips', 'd41d8cd98f00b204e9800998ecf8427e1702539232.pdf', 'd41d8cd98f00b204e9800998ecf8427e1702539232.pdf', 'd41d8cd98f00b204e9800998ecf8427e1702539232.pdf', '', '2023-12-14 07:33:52', NULL);

-- ================================================
-- CHAT SYSTEM TABLES
-- ================================================

-- Unified chat table for both user-to-user and bot conversations
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL COMMENT '0 = Bot, >0 = User ID',
  `receiver_id` int(11) NOT NULL COMMENT '0 = Bot, >0 = User ID',
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `sender_receiver` (`sender_id`, `receiver_id`),
  KEY `idx_bot_messages` (`sender_id`, `receiver_id`, `created_at`),
  KEY `idx_user_bot_conversation` (`sender_id`, `receiver_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bot responses table for intelligent chat responses
CREATE TABLE `bot_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL,
  `response` text NOT NULL,
  `category` varchar(100) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default bot responses
INSERT INTO `bot_responses` (`keyword`, `response`, `category`) VALUES
('hello', 'Hello! How can I help you with your studies today?', 'greeting'),
('hi', 'Hi there! I''m here to assist you with your notes and learning.', 'greeting'),
('hey', 'Hey! What can I help you with regarding your academic work?', 'greeting'),
('good morning', 'Good morning! Ready to start learning something new today?', 'greeting'),
('good afternoon', 'Good afternoon! How can I help you with your studies?', 'greeting'),
('good evening', 'Good evening! What can I help you with tonight?', 'greeting'),
('notes', 'I can help you organize your notes better. What subject are you working on?', 'academic'),
('study', 'Studying effectively is key to success! What study techniques work best for you?', 'academic'),
('help', 'I''m here to help! You can ask me about notes, study tips, or any academic questions.', 'support'),
('exam', 'Preparing for exams? I can suggest some effective study strategies!', 'academic'),
('math', 'Math can be challenging but rewarding! What specific math topic are you working on?', 'academic'),
('english', 'English is a beautiful language! Are you working on grammar, literature, or writing?', 'academic'),
('science', 'Science is fascinating! Which branch of science interests you the most?', 'academic'),
('thanks', 'You''re welcome! Happy to help with your learning journey.', 'support'),
('thank you', 'My pleasure! Feel free to ask if you need any more help.', 'support'),
('bye', 'Goodbye! Keep up the great work with your studies!', 'farewell'),
('goodbye', 'See you later! Don''t forget to review your notes!', 'farewell');

-- ================================================
-- ANALYTICS & ENHANCEMENT TABLES
-- ================================================

-- Table for user analytics and recommendations
CREATE TABLE `user_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for notes ratings and feedback
CREATE TABLE `note_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `review` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_note_rating` (`note_id`, `user_id`),
  KEY `note_id` (`note_id`),
  KEY `user_id` (`user_id`),
  KEY `rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for system notifications
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================
-- INDEXES AND CONSTRAINTS
-- ================================================

-- Primary key constraints
ALTER TABLE `tblnotes` ADD PRIMARY KEY (`ID`);
ALTER TABLE `tbluser` ADD PRIMARY KEY (`ID`);

-- Auto increment settings
ALTER TABLE `tblnotes` MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
ALTER TABLE `tbluser` MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `chat_messages` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `bot_responses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user_analytics` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `note_ratings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `notifications` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Foreign key constraints (optional - can be added for data integrity)
-- ALTER TABLE `tblnotes` ADD CONSTRAINT `fk_notes_user` FOREIGN KEY (`UserID`) REFERENCES `tbluser`(`ID`) ON DELETE CASCADE;
-- ALTER TABLE `note_ratings` ADD CONSTRAINT `fk_ratings_note` FOREIGN KEY (`note_id`) REFERENCES `tblnotes`(`ID`) ON DELETE CASCADE;
-- ALTER TABLE `note_ratings` ADD CONSTRAINT `fk_ratings_user` FOREIGN KEY (`user_id`) REFERENCES `tbluser`(`ID`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ================================================
-- SETUP COMPLETE!
-- ================================================
-- Database: onssdb
-- Tables Created:
-- 1. tbluser (User management)
-- 2. tblnotes (Notes storage)
-- 3. chat_messages (Unified chat system)
-- 4. bot_responses (Bot intelligence)
-- 5. user_analytics (User tracking)
-- 6. note_ratings (Feedback system)
-- 7. notifications (System alerts)
-- ================================================
