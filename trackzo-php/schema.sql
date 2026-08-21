-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: trackzo
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `type` enum('bank','cash','credit') DEFAULT 'bank',
  `balance` decimal(15,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'USD',
  `last_transaction` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,'Chase Business Checking','bank',842600.00,'USD','2025-07-31'),(2,'Citibank Operations Account','bank',225000.00,'USD','2025-07-29'),(3,'Petty Cash - Site Office','cash',4200.00,'USD','2025-07-30'),(4,'AmEx Business Platinum','credit',-32800.00,'USD','2025-07-28');
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `event_date` date NOT NULL,
  `type` enum('meeting','deadline','inspection','delivery','task') DEFAULT 'task',
  `event_time` varchar(10) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
INSERT INTO `calendar_events` VALUES (1,'Foundation Inspection - Skyline','2025-08-04','inspection','09:00',1),(2,'Client Meeting - Apex Realty','2025-08-06','meeting','14:00',2),(3,'Steel Delivery - Ironclad','2025-08-05','delivery','08:00',1),(4,'Phase 3 Deadline - Skyline','2025-08-15','deadline',NULL,1),(5,'Safety Audit - Greenfield','2025-08-12','inspection','10:00',2),(6,'Concrete Pour - Level 14','2025-08-08','task','07:00',1),(7,'Subcontractor Review','2025-08-20','meeting','11:00',NULL),(8,'Material Delivery - Valley Quarry','2025-08-08','delivery','13:00',2),(9,'Northgate Site Survey','2025-08-22','inspection','09:30',5),(10,'Progress Report - Metro Dev','2025-08-25','meeting','15:00',1);
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `company` varchar(160) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `phone` varchar(60) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `joined_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'Robert Hargrove','Metro Developers Ltd.','r.hargrove@metrodev.com','+1 212-555-0181','88 Wall Street','New York, NY','active','2022-03-14','2026-08-21 07:26:12'),(2,'Priya Sharma','Apex Realty Group','priya@apexrealty.com','+1 718-555-0242','200 Industrial Ave','Queens, NY','active','2023-07-02','2026-08-21 07:26:12'),(3,'William Harrison III','Harrison Family Trust','wharrison@harrisonft.com','+1 347-555-0305','7 Crestwood Lane','Brooklyn, NY','active','2023-05-19','2026-08-21 07:26:12'),(4,'Maya Cohen','Urban Loft Holdings','maya@urbanloft.io','+1 201-555-0417','85 River St','Hoboken, NJ','active','2024-01-08','2026-08-21 07:26:12'),(5,'Dr. Samuel Adeyemi','City Education Authority','s.adeyemi@cea.gov','+1 718-555-0560','1 Academy Road','The Bronx, NY','active','2024-09-15','2026-08-21 07:26:12'),(6,'Linda Kwong','Pacific Hospitality Group','lkwong@pacifichg.com','+1 212-555-0648','300 Broadway','Manhattan, NY','inactive','2023-11-20','2026-08-21 07:26:12');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estimation_items`
--

DROP TABLE IF EXISTS `estimation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estimation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(220) NOT NULL,
  `unit` varchar(80) DEFAULT NULL,
  `qty` decimal(12,2) DEFAULT 0.00,
  `rate` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(6,2) DEFAULT 0.00,
  `discount` decimal(6,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estimation_items`
--

LOCK TABLES `estimation_items` WRITE;
/*!40000 ALTER TABLE `estimation_items` DISABLE KEYS */;
INSERT INTO `estimation_items` VALUES (2,'Concrete Foundation (M30)','Cubic Meter',480.00,145.00,8.00,2.00),(3,'Steel Reinforcement','Metric Ton',42.00,1200.00,8.00,0.00),(4,'Brick Masonry Work','Sq.Ft.',18000.00,12.00,8.00,5.00),(5,'Plastering (Internal)','Sq.Ft.',32000.00,4.50,8.00,0.00);
/*!40000 ALTER TABLE `estimation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `unit` varchar(80) DEFAULT NULL,
  `stock` decimal(12,2) DEFAULT 0.00,
  `min_stock` decimal(12,2) DEFAULT 0.00,
  `rate` decimal(12,2) DEFAULT 0.00,
  `supplier` varchar(160) DEFAULT NULL,
  `last_updated` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materials`
--

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
INSERT INTO `materials` VALUES (1,'Portland Cement (OPC 53)','Cement','Bags (50kg)',850.00,300.00,12.50,'Atlas Cement Co.','2025-07-28'),(2,'Steel Rebar (12mm)','Steel','MT',42.00,15.00,820.00,'Ironclad Steel Works','2025-07-30'),(3,'Coarse Aggregate (20mm)','Aggregate','Cubic Yard',180.00,80.00,65.00,'Valley Quarry LLC','2025-07-25'),(4,'Fine Sand (Washed)','Aggregate','Cubic Yard',95.00,100.00,55.00,'Valley Quarry LLC','2025-07-25'),(5,'Red Clay Bricks','Masonry','Thousand',28.00,10.00,480.00,'Heritage Brick Co.','2025-07-22'),(6,'Structural Timber (4x4)','Timber','Board Ft',3200.00,1000.00,3.20,'Pacific Lumber Inc.','2025-07-29'),(7,'PVC Pipes (4\")','Plumbing','Length (20ft)',12.00,40.00,28.00,'FlowTech Supplies','2025-07-18'),(8,'Electrical Conduit (1\")','Electrical','Roll (100m)',8.00,20.00,145.00,'Voltex Electrical','2025-07-20'),(9,'Plywood (3/4\" Marine)','Timber','Sheet',220.00,80.00,62.00,'Pacific Lumber Inc.','2025-07-27'),(10,'Portland Cement (White)','Cement','Bags (25kg)',5.00,50.00,22.00,'Atlas Cement Co.','2025-07-15');
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `site_address` varchar(220) DEFAULT NULL,
  `type` varchar(120) DEFAULT NULL,
  `status` enum('planning','active','on-hold','completed') DEFAULT 'planning',
  `budget` decimal(15,2) DEFAULT 0.00,
  `spent` decimal(15,2) DEFAULT 0.00,
  `progress` int(11) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `area` int(11) DEFAULT 0,
  `floors` int(11) DEFAULT 0,
  `manager` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'Skyline Tower Residences',1,'14 Harbor Blvd, Downtown, NY 10001','Residential High-Rise','active',4800000.00,2940000.00,62,'2024-02-01','2025-09-30',18400,22,'James Carter','22-floor luxury residential tower with 88 units, rooftop amenities, and underground parking.','2026-08-21 07:26:12'),(2,'Greenfield Commercial Park',2,'200 Industrial Ave, Queens, NY 11101','Commercial Complex','active',2600000.00,980000.00,38,'2024-06-15','2025-12-31',9200,4,'Sarah Mitchell','Multi-unit commercial park with office spaces, retail units, and shared amenities.','2026-08-21 07:26:12'),(3,'Sunrise Villa Estate',3,'7 Crestwood Lane, Brooklyn, NY 11215','Luxury Villa','completed',920000.00,905000.00,100,'2023-08-01','2024-07-31',4800,3,'James Carter','Bespoke luxury villa with pool, home theatre, smart home systems, and landscaped grounds.','2026-08-21 07:26:12'),(4,'Riverfront Warehouse Conversion',4,'85 River St, Hoboken, NJ 07030','Renovation','on-hold',1400000.00,320000.00,23,'2024-04-01','2025-06-30',6200,5,'Sarah Mitchell','Historic warehouse conversion to mixed-use loft apartments and ground-floor retail.','2026-08-21 07:26:12'),(5,'Northgate School Extension',5,'1 Academy Road, The Bronx, NY 10451','Institutional','planning',3200000.00,45000.00,5,'2025-01-15','2026-06-30',11000,3,'David Okonkwo','New STEM block, sports hall, and cafeteria extension for Northgate Academy.','2026-08-21 07:26:12');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier` varchar(160) NOT NULL,
  `item` varchar(200) DEFAULT NULL,
  `qty` decimal(12,2) DEFAULT 0.00,
  `rate` decimal(12,2) DEFAULT 0.00,
  `total` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','approved','delivered','cancelled') DEFAULT 'pending',
  `order_date` date DEFAULT NULL,
  `expected_date` date DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
INSERT INTO `purchase_orders` VALUES (1,'Atlas Cement Co.','Portland Cement OPC 53',500.00,12.50,6250.00,'delivered','2025-07-20','2025-07-25',1),(2,'Ironclad Steel Works','Steel Rebar 12mm',20.00,820.00,16400.00,'approved','2025-07-28','2025-08-05',1),(3,'Valley Quarry LLC','Coarse Aggregate 20mm',80.00,65.00,8500.00,'pending','2025-07-30','2025-08-08',2),(4,'FlowTech Supplies','PVC Pipes 4in',60.00,28.00,1680.00,'pending','2025-07-31','2025-08-10',2),(5,'Heritage Brick Co.','Red Clay Bricks',20.00,480.00,9600.00,'cancelled','2025-07-10','2025-07-18',4);
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_date` date DEFAULT NULL,
  `description` varchar(220) NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `type` enum('income','expense') DEFAULT 'expense',
  `amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('paid','pending','overdue') DEFAULT 'paid',
  `project_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,'2025-07-31','Client Payment - Skyline Tower Phase 3','Client Receipt','income',480000.00,'paid',1),(2,'2025-07-29','Steel Procurement - Ironclad Steel','Materials','expense',42600.00,'paid',1),(3,'2025-07-28','Labour Wages - July W4','Labour','expense',28400.00,'paid',1),(4,'2025-07-25','Client Payment - Greenfield Park Milestone 1','Client Receipt','income',240000.00,'paid',2),(5,'2025-07-22','Subcontractor - MEP Works','Subcontractor','expense',85000.00,'paid',2),(6,'2025-07-20','Equipment Rental - Tower Crane','Equipment','expense',18500.00,'paid',1),(7,'2025-07-18','Pending Invoice - Greenfield Phase 2','Client Receipt','income',180000.00,'pending',2),(8,'2025-07-15','Concrete Mix Supply - Atlas Cement','Materials','expense',12800.00,'paid',1),(9,'2025-07-12','Insurance Premium - Project Coverage','Insurance','expense',6200.00,'paid',1),(10,'2025-07-08','Client Payment - Sunrise Villa Final','Client Receipt','income',92000.00,'paid',3),(11,'2025-07-05','Overdue Payment - Urban Loft Deposit','Client Receipt','income',140000.00,'overdue',4),(12,'2025-07-03','Labour Wages - June Final','Labour','expense',31200.00,'paid',1);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(80) DEFAULT 'Member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'James Carter','james.carter@trackzo.io','$2y$10$2ARCsztcwByKcXau38MNbeWNcFT8wGAvr3dRjxVkdqz.Rk55JkaOy','Project Manager','2026-08-21 07:26:12');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-21 15:36:15
