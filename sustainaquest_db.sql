-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 29, 2025 at 06:19 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sustainaquest`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `adminId` int NOT NULL AUTO_INCREMENT,
  `adminName` varchar(50) NOT NULL,
  `adminPassword` varchar(255) NOT NULL,
  PRIMARY KEY (`adminId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
CREATE TABLE IF NOT EXISTS `badges` (
  `badgeId` int NOT NULL AUTO_INCREMENT,
  `badgeName` varchar(50) NOT NULL,
  `badgeIconURL` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`badgeId`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`badgeId`, `badgeName`, `badgeIconURL`, `description`) VALUES
(1, 'Green Rookie', 'assets/image/badges/green_rookie.png', 'Welcome to SustainaQuest! Your journey begins here.'),
(2, 'Earth Defender', 'assets/image/badges/earth_defender.png', 'Earn 500 Green Points'),
(3, 'Eco Legend', 'assets/image/badges/eco_legend.png', 'Earn 1,000 Green Points'),
(4, 'Sustainability God', 'assets/image/badges/sustainability_god.png', 'Earn 5,000 Green Points'),
(5, 'Level Up', 'assets/image/badges/level_up.png', 'Reach Level 2'),
(6, 'High Flyer', 'assets/image/badges/high_flyer.png', 'Reach Level 5'),
(7, 'Top Tier', 'assets/image/badges/top_tier.png', 'Reach Level 10'),
(8, 'First Step', 'assets/image/badges/first_step.png', 'Complete your first Quest'),
(9, 'Quest Hunter', 'assets/image/badges/quest_hunter.png', 'Complete 5 Quests'),
(10, 'Quest Master', 'assets/image/badges/quest_master.png', 'Complete 20 Quests');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
  `itemId` int NOT NULL AUTO_INCREMENT,
  `itemName` varchar(100) NOT NULL,
  `itemDesc` text,
  `itemPictureURL` varchar(255) DEFAULT NULL,
  `quantity` int DEFAULT '0',
  `itemType` varchar(50) DEFAULT NULL COMMENT 'Permanent or Limited',
  `pointCost` int NOT NULL,
  `availableStatus` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`itemId`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`itemId`, `itemName`, `itemDesc`, `itemPictureURL`, `quantity`, `itemType`, `pointCost`, `availableStatus`) VALUES
(1, 'Bamboo Toothbrush', '100% Biodegradable handle.', 'assets/image/items/toothbrush.png', 96, 'Permanent', 200, 1),
(2, 'Metal Straw Set', 'Includes cleaner brush and pouch.', 'assets/image/items/straws.png', 80, 'Permanent', 350, 1),
(3, 'Reusable Tote Bag', 'Canvas grocery bag.', 'assets/image/items/totebag.png', 50, 'Permanent', 500, 1),
(4, 'Recycled Notebook', 'Paper made from recycled waste.', 'assets/image/items/notebook.png', 60, 'Permanent', 400, 1),
(5, 'LED Bulb Pack', 'Energy saving light bulbs.', 'assets/image/items/led_bulb.png', 40, 'Permanent', 600, 1),
(6, 'Organic Soap Bar', 'Chemical free soap.', 'assets/image/items/soap.png', 200, 'Permanent', 150, 1),
(7, 'Sunflower Seeds', 'Plant your own flowers.', 'assets/image/items/seeds.png', 300, 'Permanent', 100, 1),
(8, 'Eco Bento Box', 'Lunch box for work.', 'assets/image/items/bento.png', 25, 'Permanent', 800, 1),
(9, 'RM10 Grab Voucher', 'Digital ride code.', 'assets/image/items/voucher.png', 10, 'Limited', 1000, 1),
(10, 'SustainaQuest T-Shirt', 'Official Merch.', 'assets/image/items/tshirt.png', 5, 'Limited', 2500, 1),
(11, 'Movie Ticket Pair', 'Tickets for two.', 'assets/image/items/movie_ticket.png', 8, 'Limited', 1200, 1),
(12, 'Concert Pass', 'VIP Green Concert access.', 'assets/image/items/concert.png', 2, 'Limited', 5000, 1),
(13, 'RM50 Gift Card', 'Generic grocery gift card.', 'assets/image/items/giftcard.png', 3, 'Limited', 4500, 1),
(14, 'Rare Bonsai Tree', 'A real living bonsai.', 'assets/image/items/rare_plant.png', 1, 'Limited', 3000, 1),
(15, 'Premium Metal Bottle', 'Vacuum insulated bottle.', 'assets/image/items/metal_bottle.png', 7, 'Limited', 1500, 1),
(16, 'Eco Hoodie', 'Made from recycled cotton.', 'assets/image/items/hoodie.png', 4, 'Limited', 2800, 1);

-- --------------------------------------------------------

--
-- Table structure for table `moderators`
--

DROP TABLE IF EXISTS `moderators`;
CREATE TABLE IF NOT EXISTS `moderators` (
  `moderatorId` int NOT NULL AUTO_INCREMENT,
  `modName` varchar(50) NOT NULL,
  `modPassword` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phoneNumber` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`moderatorId`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questdelete`
--

DROP TABLE IF EXISTS `questdelete`;
CREATE TABLE IF NOT EXISTS `questdelete` (
  `deleteId` int NOT NULL AUTO_INCREMENT,
  `questId` int DEFAULT NULL,
  `deletedByModeratorId` int DEFAULT NULL,
  `deletedByAdminId` int DEFAULT NULL,
  `reason` text,
  `deleteDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`deleteId`),
  KEY `deletedByModeratorId` (`deletedByModeratorId`),
  KEY `deletedByAdminId` (`deletedByAdminId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quests`
--

DROP TABLE IF EXISTS `quests`;
CREATE TABLE IF NOT EXISTS `quests` (
  `questId` int NOT NULL AUTO_INCREMENT,
  `createdByModeratorId` int DEFAULT NULL,
  `createdByAdminId` int DEFAULT NULL,
  `questIconURL` varchar(255) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `type` varchar(50) DEFAULT NULL COMMENT 'Daily or Weekly',
  `pointReward` int DEFAULT '0',
  `expReward` int DEFAULT '0',
  `createDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `isActive` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`questId`),
  KEY `createdByModeratorId` (`createdByModeratorId`),
  KEY `createdByAdminId` (`createdByAdminId`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quests`
--

INSERT INTO `quests` (`questId`, `createdByModeratorId`, `createdByAdminId`, `questIconURL`, `title`, `description`, `type`, `pointReward`, `expReward`, `createDate`, `isActive`) VALUES
(1, NULL, NULL, 'assets/image/quests/bottle.png', 'Recycle Plastic Bottle', 'Find a plastic bottle. The AI looks for a \"water bottle\" or \"bottle\".', 'Daily', 10, 50, '2025-12-29 14:01:26', 1),
(2, NULL, NULL, 'assets/image/quests/apple.png', 'Eat a Healthy Apple', 'Eat a fruit! Show us a red or green apple.', 'Daily', 10, 40, '2025-12-29 14:01:26', 1),
(3, NULL, NULL, 'assets/image/quests/laptop.png', 'Eco-Friendly Work', 'Show us your workspace setup (Laptop).', 'Daily', 15, 60, '2025-12-29 14:01:26', 1),
(4, NULL, NULL, 'assets/image/quests/mug.png', 'Reusable Coffee Mug', 'Drink from a reusable mug. Show us your \"cup\" or \"coffee mug\".', 'Daily', 10, 45, '2025-12-29 14:01:26', 1),
(5, NULL, NULL, 'assets/image/quests/mouse.png', 'Sustainable Tech', 'Use a computer mouse instead of a trackpad for efficiency.', 'Daily', 5, 30, '2025-12-29 14:01:26', 1),
(6, NULL, NULL, 'assets/image/quests/plant.png', 'Plant a Small Tree', 'Show us a potted plant or flower pot.', 'Weekly', 150, 500, '2025-12-29 14:01:26', 1),
(7, NULL, NULL, 'assets/image/quests/bicycle.png', 'Cycle to Work', 'Ride a bicycle! Show us your bike.', 'Weekly', 200, 600, '2025-12-29 14:01:26', 1),
(8, NULL, NULL, 'assets/image/quests/backpack.png', 'Pack a Reusable Bag', 'Show us your backpack or bag ready for the day.', 'Weekly', 100, 400, '2025-12-29 14:01:26', 1);

-- --------------------------------------------------------

--
-- Table structure for table `questsubmissions`
--

DROP TABLE IF EXISTS `questsubmissions`;
CREATE TABLE IF NOT EXISTS `questsubmissions` (
  `submissionId` int NOT NULL AUTO_INCREMENT,
  `questId` int DEFAULT NULL,
  `submittedByUserId` int DEFAULT NULL,
  `evidencePictureURL` varchar(255) DEFAULT NULL,
  `evidenceVideoURL` varchar(255) DEFAULT NULL,
  `submitDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `verifyDate` datetime DEFAULT NULL,
  `approveStatus` varchar(20) DEFAULT 'Pending',
  `verifiedByAi` tinyint(1) DEFAULT '0',
  `verifiedByModeratorId` int DEFAULT NULL,
  `verifiedByAdminId` int DEFAULT NULL,
  `declinedReason` text,
  PRIMARY KEY (`submissionId`),
  KEY `questId` (`questId`),
  KEY `submittedByUserId` (`submittedByUserId`),
  KEY `verifiedByModeratorId` (`verifiedByModeratorId`),
  KEY `verifiedByAdminId` (`verifiedByAdminId`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questsubmissions`
--

INSERT INTO `questsubmissions` (`submissionId`, `questId`, `submittedByUserId`, `evidencePictureURL`, `evidenceVideoURL`, `submitDate`, `verifyDate`, `approveStatus`, `verifiedByAi`, `verifiedByModeratorId`, `verifiedByAdminId`, `declinedReason`) VALUES
(1, 1, 1, 'assets/uploads/1766988919_img_69521c776278f.jpg', NULL, '2025-12-29 14:15:19', '2025-12-29 06:15:19', 'Approved', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `redemptions`
--

DROP TABLE IF EXISTS `redemptions`;
CREATE TABLE IF NOT EXISTS `redemptions` (
  `redemptionId` int NOT NULL AUTO_INCREMENT,
  `userId` int DEFAULT NULL,
  `itemId` int DEFAULT NULL,
  `redempQuantity` int DEFAULT '1',
  `redempStatus` tinyint(1) DEFAULT '0' COMMENT '0=Pending, 1=Redeemed',
  `redempDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`redemptionId`),
  KEY `userId` (`userId`),
  KEY `itemId` (`itemId`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `redemptions`
--

INSERT INTO `redemptions` (`redemptionId`, `userId`, `itemId`, `redempQuantity`, `redempStatus`, `redempDate`) VALUES
(1, 1, 1, 4, 1, '2025-12-29 14:16:06');

-- --------------------------------------------------------

--
-- Table structure for table `userbadges`
--

DROP TABLE IF EXISTS `userbadges`;
CREATE TABLE IF NOT EXISTS `userbadges` (
  `userId` int NOT NULL,
  `badgeId` int NOT NULL,
  PRIMARY KEY (`userId`,`badgeId`),
  KEY `badgeId` (`badgeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `userbadges`
--

INSERT INTO `userbadges` (`userId`, `badgeId`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(4, 7),
(5, 1),
(5, 2),
(5, 3),
(5, 5),
(5, 6),
(6, 1),
(6, 2),
(6, 3),
(6, 5),
(6, 6),
(7, 1),
(7, 2),
(7, 3),
(7, 5),
(7, 6),
(8, 1),
(8, 2),
(8, 3),
(8, 5),
(8, 6),
(9, 1),
(9, 2),
(9, 3),
(9, 5),
(10, 1),
(10, 2),
(10, 5),
(11, 1),
(11, 5),
(12, 1),
(12, 5),
(13, 1),
(14, 1),
(15, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `userId` int NOT NULL AUTO_INCREMENT,
  `userName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `level` int DEFAULT '1',
  `levelProgress` decimal(5,2) DEFAULT '0.00',
  `greenPoints` int DEFAULT '0',
  `isBanned` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`userId`),
  UNIQUE KEY `userName` (`userName`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `userName`, `email`, `passwordHash`, `level`, `levelProgress`, `greenPoints`, `isBanned`) VALUES
(1, 'Adam_Super', 'adam@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 25, 149.00, 14660, 0),
(2, 'Eco_Warrior_X', 'warrior@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 18, 45.00, 9800, 0),
(3, 'Green_Queen', 'queen@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 14, 20.00, 7250, 0),
(4, 'Planet_Protector', 'planet@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 10, 10.00, 5100, 0),
(5, 'Solar_Sam', 'solar@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 9, 85.00, 4200, 0),
(6, 'Nature_Nora', 'nora@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 8, 30.00, 3600, 0),
(7, 'Recycle_Rick', 'rick@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 6, 70.00, 2500, 0),
(8, 'Bio_Bella', 'bella@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 5, 50.00, 1800, 0),
(9, 'Windy_Wendy', 'wendy@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 4, 90.00, 1250, 0),
(10, 'Carbon_Carl', 'carl@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 3, 20.00, 950, 0),
(11, 'Clean_Air_Alice', 'alice@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 2, 40.00, 450, 0),
(12, 'Ocean_Orion', 'orion@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 2, 10.00, 300, 0),
(13, 'Litter_Larry', 'larry@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 1, 80.00, 150, 0),
(14, 'Plastic_Pat', 'pat@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 1, 20.00, 50, 0),
(15, 'Newbie_Tom', 'tom@gmail.com', '$2y$10$Oa1hBxlpK1yqmaPxKy78kuQeAR2X139MXTwYEddjXr1RwcDuCkNjO', 1, 0.00, 0, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
