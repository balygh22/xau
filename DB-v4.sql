-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for xau1
CREATE DATABASE IF NOT EXISTS `xau1` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `xau1`;

-- Dumping structure for table xau1.accounts
CREATE TABLE IF NOT EXISTS `accounts` (
  `AccountID` int NOT NULL AUTO_INCREMENT,
  `AccountName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AccountType` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Identifier` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`AccountID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.accounts: ~4 rows (approximately)
INSERT INTO `accounts` (`AccountID`, `AccountName`, `AccountType`, `Identifier`, `IsActive`) VALUES
	(1, 'الصندوق الرئيسي', 'Cashbox', NULL, 1),
	(2, 'عميل نقدي', 'Customer', NULL, 1),
	(8, 'بليغ تاج', 'Supplier', NULL, 1),
	(10, 'صندوق بليغ', 'Cashbox', NULL, 1),
	(11, 'خليل الخولاني', 'Customer', NULL, 1);

-- Dumping structure for table xau1.account_balances
CREATE TABLE IF NOT EXISTS `account_balances` (
  `AccountID` int NOT NULL,
  `CurrencyID` int NOT NULL,
  `CurrentBalance` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`AccountID`,`CurrencyID`),
  KEY `CurrencyID` (`CurrencyID`),
  CONSTRAINT `account_balances_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `accounts` (`AccountID`) ON DELETE CASCADE,
  CONSTRAINT `account_balances_ibfk_2` FOREIGN KEY (`CurrencyID`) REFERENCES `currencies` (`CurrencyID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.account_balances: ~9 rows (approximately)
INSERT INTO `account_balances` (`AccountID`, `CurrencyID`, `CurrentBalance`, `created_at`, `updated_at`) VALUES
	(1, 1, 100000.0000, NULL, '2025-08-29 12:02:28'),
	(1, 2, 0.0000, NULL, '2025-08-28 18:32:52'),
	(1, 3, 0.0000, NULL, '2025-08-28 20:49:38'),
	(2, 1, 250000.0000, NULL, '2025-08-29 12:02:28'),
	(2, 2, 0.0000, '2025-08-28 18:32:52', '2025-08-28 18:32:52'),
	(2, 3, 0.0000, NULL, '2025-08-28 20:49:38'),
	(8, 1, 100000.0000, '2025-08-29 12:33:55', '2025-08-29 12:41:03'),
	(10, 1, -100000.0000, '2025-08-28 18:27:49', '2025-08-29 12:41:03'),
	(10, 2, 0.0000, '2025-08-28 18:27:49', '2025-08-28 18:27:49'),
	(10, 3, 0.0000, '2025-08-28 18:27:49', '2025-08-28 18:32:26'),
	(11, 1, 0.0000, '2025-08-29 12:30:51', '2025-08-29 12:30:51');

-- Dumping structure for table xau1.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `CategoryID` int NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`CategoryID`),
  UNIQUE KEY `CategoryName` (`CategoryName`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.categories: ~0 rows (approximately)
INSERT INTO `categories` (`CategoryID`, `CategoryName`) VALUES
	(1, 'خواتم'),
	(2, 'ساعات');

-- Dumping structure for table xau1.currencies
CREATE TABLE IF NOT EXISTS `currencies` (
  `CurrencyID` int NOT NULL AUTO_INCREMENT,
  `CurrencyCode` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CurrencyName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IsDefault` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CurrencyID`),
  UNIQUE KEY `CurrencyCode` (`CurrencyCode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.currencies: ~3 rows (approximately)
INSERT INTO `currencies` (`CurrencyID`, `CurrencyCode`, `CurrencyName`, `IsDefault`) VALUES
	(1, 'YER', 'ريال يمني', 1),
	(2, 'SAR', 'ريال سعودي', 0),
	(3, 'USD', 'دولار أمريكي', 0);

-- Dumping structure for table xau1.grouppermissions
CREATE TABLE IF NOT EXISTS `grouppermissions` (
  `GroupID` int NOT NULL,
  `PermissionID` int NOT NULL,
  PRIMARY KEY (`GroupID`,`PermissionID`),
  KEY `PermissionID` (`PermissionID`),
  CONSTRAINT `grouppermissions_ibfk_1` FOREIGN KEY (`GroupID`) REFERENCES `usergroups` (`GroupID`) ON DELETE CASCADE,
  CONSTRAINT `grouppermissions_ibfk_2` FOREIGN KEY (`PermissionID`) REFERENCES `permissions` (`PermissionID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.grouppermissions: ~13 rows (approximately)
INSERT INTO `grouppermissions` (`GroupID`, `PermissionID`) VALUES
	(1, 1),
	(2, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(2, 4),
	(1, 5),
	(1, 6),
	(2, 6),
	(1, 7),
	(1, 8),
	(1, 9),
	(1, 10);

-- Dumping structure for table xau1.inventorylog
CREATE TABLE IF NOT EXISTS `inventorylog` (
  `LogID` int NOT NULL AUTO_INCREMENT,
  `ProductID` int NOT NULL,
  `TransactionID` int DEFAULT NULL,
  `MovementType` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `WeightChange` decimal(12,3) NOT NULL DEFAULT '0.000',
  `UnitChange` int NOT NULL DEFAULT '0',
  `LogDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UserID` int NOT NULL,
  PRIMARY KEY (`LogID`),
  KEY `ProductID` (`ProductID`),
  KEY `UserID` (`UserID`),
  KEY `TransactionID` (`TransactionID`),
  CONSTRAINT `inventorylog_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE,
  CONSTRAINT `inventorylog_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  CONSTRAINT `inventorylog_ibfk_3` FOREIGN KEY (`TransactionID`) REFERENCES `transactions` (`TransactionID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.inventorylog: ~0 rows (approximately)
INSERT INTO `inventorylog` (`LogID`, `ProductID`, `TransactionID`, `MovementType`, `WeightChange`, `UnitChange`, `LogDate`, `UserID`) VALUES
	(7, 6, NULL, 'Sale', 1.000, 5, '2025-08-28 23:48:07', 1),
	(8, 6, 9, 'Sale', 0.000, 2, '2025-08-29 15:01:51', 3);

-- Dumping structure for table xau1.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.migrations: ~0 rows (approximately)

-- Dumping structure for table xau1.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `PaymentID` int NOT NULL AUTO_INCREMENT,
  `PaymentNumber` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TransactionID` int DEFAULT NULL,
  `FromAccountID` int NOT NULL,
  `ToAccountID` int NOT NULL,
  `PaymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Amount` decimal(15,2) NOT NULL,
  `CurrencyID` int NOT NULL,
  `Description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UserID` int NOT NULL,
  PRIMARY KEY (`PaymentID`),
  UNIQUE KEY `PaymentNumber_UNIQUE` (`PaymentNumber`),
  KEY `TransactionID` (`TransactionID`),
  KEY `FromAccountID` (`FromAccountID`),
  KEY `ToAccountID` (`ToAccountID`),
  KEY `CurrencyID` (`CurrencyID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`TransactionID`) REFERENCES `transactions` (`TransactionID`) ON DELETE SET NULL,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`FromAccountID`) REFERENCES `accounts` (`AccountID`),
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`ToAccountID`) REFERENCES `accounts` (`AccountID`),
  CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`CurrencyID`) REFERENCES `currencies` (`CurrencyID`),
  CONSTRAINT `payments_ibfk_5` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.payments: ~2 rows (approximately)
INSERT INTO `payments` (`PaymentID`, `PaymentNumber`, `TransactionID`, `FromAccountID`, `ToAccountID`, `PaymentDate`, `Amount`, `CurrencyID`, `Description`, `UserID`) VALUES
	(13, 'PAY-000011', 9, 2, 1, '2025-08-29 15:01:51', 70000.00, 1, 'دفعة مقدّمة على فاتورة بيع', 3),
	(14, 'PAY-000014', 9, 2, 1, '2025-08-29 15:02:00', 30000.00, 1, NULL, 3),
	(17, 'PAY-000015', NULL, 10, 8, '2025-08-29 15:38:00', 100000.00, 1, NULL, 1);

-- Dumping structure for table xau1.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `PermissionID` int NOT NULL AUTO_INCREMENT,
  `PermissionName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`PermissionID`),
  UNIQUE KEY `PermissionName` (`PermissionName`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.permissions: ~10 rows (approximately)
INSERT INTO `permissions` (`PermissionID`, `PermissionName`, `Description`) VALUES
	(1, 'View_Dashboard', 'عرض لوحة التحكم الرئيسية'),
	(2, 'Manage_Products', 'إدارة المنتجات (إضافة, تعديل, حذف)'),
	(3, 'Manage_Inventory', 'إدارة المخزون (تسوية, جرد)'),
	(4, 'Create_Sale_Invoice', 'إنشاء فاتورة بيع'),
	(5, 'Create_Purchase_Invoice', 'إنشاء فاتورة شراء'),
	(6, 'Receive_Payments', 'استلام دفعات من العملاء'),
	(7, 'Manage_Accounts', 'إدارة الحسابات (العملاء, الموردين, الصناديق)'),
	(8, 'View_Financial_Reports', 'عرض التقارير المالية والأرباح'),
	(9, 'Manage_Users', 'إدارة المستخدمين والصلاحيات'),
	(10, 'Manage_Settings', 'إدارة إعدادات النظام وأسعار الصرف');

-- Dumping structure for table xau1.products
CREATE TABLE IF NOT EXISTS `products` (
  `ProductID` int NOT NULL AUTO_INCREMENT,
  `ProductCode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ProductName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CategoryID` int NOT NULL,
  `GoldWeight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `Purity` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StoneWeight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `LaborCost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `StockByWeight` decimal(12,3) NOT NULL DEFAULT '0.000',
  `StockByUnit` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`ProductID`),
  UNIQUE KEY `ProductCode` (`ProductCode`),
  KEY `CategoryID` (`CategoryID`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `categories` (`CategoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.products: ~1 rows (approximately)
INSERT INTO `products` (`ProductID`, `ProductCode`, `ProductName`, `CategoryID`, `GoldWeight`, `Purity`, `StoneWeight`, `LaborCost`, `StockByWeight`, `StockByUnit`) VALUES
	(6, 'KHOATM-21-1CHHZT', 'خواتم 21', 1, 8.000, '21', 5.000, 0.00, 500.000, 48);

-- Dumping structure for table xau1.transactiondetails
CREATE TABLE IF NOT EXISTS `transactiondetails` (
  `DetailID` int NOT NULL AUTO_INCREMENT,
  `TransactionID` int NOT NULL,
  `ProductID` int NOT NULL,
  `Quantity` int NOT NULL,
  `Weight` decimal(12,3) NOT NULL,
  `UnitPrice` decimal(12,2) NOT NULL,
  `LineTotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`DetailID`),
  KEY `TransactionID` (`TransactionID`),
  KEY `ProductID` (`ProductID`),
  CONSTRAINT `transactiondetails_ibfk_1` FOREIGN KEY (`TransactionID`) REFERENCES `transactions` (`TransactionID`) ON DELETE CASCADE,
  CONSTRAINT `transactiondetails_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.transactiondetails: ~1 rows (approximately)
INSERT INTO `transactiondetails` (`DetailID`, `TransactionID`, `ProductID`, `Quantity`, `Weight`, `UnitPrice`, `LineTotal`, `created_at`, `updated_at`) VALUES
	(8, 9, 6, 2, 0.000, 50000.00, 100000.00, '2025-08-29 12:01:51', '2025-08-29 12:01:51');

-- Dumping structure for table xau1.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `TransactionID` int NOT NULL AUTO_INCREMENT,
  `TransactionNumber` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TransactionType` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AccountID` int NOT NULL,
  `TransactionDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CurrencyID` int NOT NULL,
  `TotalAmount` decimal(15,2) NOT NULL,
  `PaidAmount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `Notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UserID` int NOT NULL,
  `OriginalTransactionID` int DEFAULT NULL COMMENT 'Used for returns to link to the original invoice',
  PRIMARY KEY (`TransactionID`),
  UNIQUE KEY `TransactionNumber` (`TransactionNumber`),
  KEY `AccountID` (`AccountID`),
  KEY `CurrencyID` (`CurrencyID`),
  KEY `UserID` (`UserID`),
  KEY `OriginalTransactionID` (`OriginalTransactionID`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `accounts` (`AccountID`),
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`CurrencyID`) REFERENCES `currencies` (`CurrencyID`),
  CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`OriginalTransactionID`) REFERENCES `transactions` (`TransactionID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.transactions: ~1 rows (approximately)
INSERT INTO `transactions` (`TransactionID`, `TransactionNumber`, `TransactionType`, `AccountID`, `TransactionDate`, `CurrencyID`, `TotalAmount`, `PaidAmount`, `Notes`, `UserID`, `OriginalTransactionID`) VALUES
	(9, 'INV-2025-009', 'Sale', 2, '2025-08-29 14:58:00', 1, 100000.00, 100000.00, NULL, 3, NULL);

-- Dumping structure for table xau1.usergroups
CREATE TABLE IF NOT EXISTS `usergroups` (
  `GroupID` int NOT NULL AUTO_INCREMENT,
  `GroupName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`GroupID`),
  UNIQUE KEY `GroupName` (`GroupName`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.usergroups: ~3 rows (approximately)
INSERT INTO `usergroups` (`GroupID`, `GroupName`) VALUES
	(2, 'البائعين'),
	(3, 'المحاسبين'),
	(1, 'المدراء');

-- Dumping structure for table xau1.users
CREATE TABLE IF NOT EXISTS `users` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `FullName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PasswordHash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GroupID` int NOT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Username` (`Username`),
  KEY `GroupID` (`GroupID`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`GroupID`) REFERENCES `usergroups` (`GroupID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table xau1.users: ~3 rows (approximately)
INSERT INTO `users` (`UserID`, `FullName`, `Username`, `PasswordHash`, `GroupID`, `IsActive`) VALUES
	(1, 'مدير النظام', 'admin', '$2y$12$P9TzJfzVjvENpxJuNcBxLuqNE/aTHXytci1x8AT5xJcyVPFfs3QXi', 1, 1),
	(2, 'موظف مبيعات', 'seller', '123', 2, 1),
	(3, 'بليغ تاج الدين', 'Balygh', '$2y$12$rGLexpVh.lYAVo0m7nXlv.vH1oK9s4ESKpaWAYghDKoiOegWG4V3K', 1, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
