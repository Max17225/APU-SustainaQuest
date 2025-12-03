-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 03, 2025 at 02:33 AM
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
  `obtainedDate` date DEFAULT NULL,
  PRIMARY KEY (`badgeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
