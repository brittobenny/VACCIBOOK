-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 20, 2026 at 12:00 PM
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
-- Database: `vaccibook`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `aemail` varchar(25) NOT NULL,
  `password` varchar(6) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`aemail`, `password`) VALUES
('admin@gmail.com', '1234');

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

DROP TABLE IF EXISTS `book`;
CREATE TABLE IF NOT EXISTS `book` (
  `bid` int NOT NULL AUTO_INCREMENT,
  `pid` int NOT NULL,
  `sid` int NOT NULL,
  `cid` int NOT NULL,
  `bookdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'pending',
  `certificate` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`bid`, `pid`, `sid`, `cid`, `bookdate`, `status`, `certificate`) VALUES
(1, 1, 6, 1, '2025-10-27 04:42:46', 'completed', 'certificate_1_20251027.html'),
(2, 2, 1, 3, '2025-10-27 06:26:06', 'completed', 'certificate_2_20251027.html'),
(3, 3, 2, 4, '2025-10-28 12:22:47', 'completed', 'certificate_3_20251028.html');

-- --------------------------------------------------------

--
-- Table structure for table `child`
--

DROP TABLE IF EXISTS `child`;
CREATE TABLE IF NOT EXISTS `child` (
  `cid` int NOT NULL AUTO_INCREMENT,
  `pid` int NOT NULL,
  `cname` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `gender` char(1) NOT NULL,
  `id_proof` varchar(180) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `child`
--

INSERT INTO `child` (`cid`, `pid`, `cname`, `dob`, `gender`, `id_proof`) VALUES
(1, 1, 'Abel', '2025-07-27', 'm', 'child_id_1761539374_68fef52e9c052.jpg'),
(2, 2, 'Alfred', '2025-06-27', 'm', 'child_id_1761546211_68ff0fe32edf9.jpg'),
(3, 2, 'Anu', '2025-10-08', 'f', 'child_id_1761546352_68ff10701a527.jpg'),
(4, 3, 'vijay', '2025-10-07', 'm', 'child_id_1761654110_6900b55eded6f.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `district`
--

DROP TABLE IF EXISTS `district`;
CREATE TABLE IF NOT EXISTS `district` (
  `did` int NOT NULL AUTO_INCREMENT,
  `dname` varchar(25) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`did`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `district`
--

INSERT INTO `district` (`did`, `dname`, `status`) VALUES
(1, 'Ernakulam', 1),
(2, 'Thrissur', 1),
(3, 'Palakkad', 1),
(4, 'Kottayam', 1),
(5, 'Malappuram', 1),
(6, 'Idukki', 1);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
  `fid` int NOT NULL AUTO_INCREMENT,
  `pid` int NOT NULL,
  `hid` int DEFAULT NULL,
  `bid` int DEFAULT NULL,
  `rating` tinyint NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `feedback_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  PRIMARY KEY (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`fid`, `pid`, `hid`, `bid`, `rating`, `subject`, `message`, `feedback_date`, `status`) VALUES
(1, 1, 9, 1, 4, 'About vaccination.', 'vaccibook helped to save my time.', '2025-10-27 05:09:31', 'reviewed'),
(2, 2, 1, 2, 5, 'tfyyt', 'ygygiuhuih', '2025-10-27 06:29:46', 'reviewed'),
(3, 3, 1, 3, 4, 'ugiu', 'hugyufytuf', '2025-10-28 12:27:27', 'reviewed'),
(4, 3, 1, 3, 4, 'ugiu', 'hugyufytuf', '2025-10-28 12:28:12', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `healthcentre`
--

DROP TABLE IF EXISTS `healthcentre`;
CREATE TABLE IF NOT EXISTS `healthcentre` (
  `hid` int NOT NULL AUTO_INCREMENT,
  `hname` varchar(70) NOT NULL,
  `did` int NOT NULL,
  `himage` varchar(150) NOT NULL,
  `loc` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(10) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`hid`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `healthcentre`
--

INSERT INTO `healthcentre` (`hid`, `hname`, `did`, `himage`, `loc`, `email`, `password`, `status`) VALUES
(1, 'Nedumbassery Primary Health Centre', 1, '96d3d78f133b8e503390be41af10771a_ac798ad4b92.jpg', 'Mekkad', 'nph@gmail.com', 'nph123', 1),
(2, 'URBAN HEALTH AND WELLNESS CENTER', 1, '569aefc6f90069a856137e654badcdad_714f9d1dcbadffb0fa.jpg', 'Fort Kochi', 'uhw@gmail.com', 'uhw123', 1),
(3, 'Chavakkad Community Health Centre', 2, 'a9cbe18aa2e41aa47f8022df491dfb31_c9d906ac065a029ae.png', 'Chavakkad', 'cch@gmail.com', 'cch123', 1),
(4, 'Thrissur District Hospital', 2, '0aef96aa944a87c6cc8a98509dcc9924_a424ea369bab2b71a.png', 'Chembukkavu', 'tdh@gmail.com', 'tdh123', 1),
(5, 'Irinjalakuda Taluk Hospital', 2, '932d60f869f57ee3ecdcd6ca27c5bc7e_d3e61e54eadb3.jpg', 'Irinjalakuda', 'ith@gmail.com', 'ith123', 1),
(6, 'Aluva Community Health Centre', 1, '4ed39d570519bb26e8edfc6280bd3cdf_84a1d7ff459268b4fabd.png', 'Aluva', 'ach@gmail.com', 'ach123', 1),
(7, 'Palakkad District Hospital', 3, '50ba0c3c241c757190ff999d61e7fc57_bd10bbc7017e3cf.jpg', 'Sultanpet', 'pdh@gmail.com', 'pdh123', 1),
(8, 'Ottapalam Taluk Hospital', 3, '6990e5fe5912161307425d377c014ae7_683ca33bf04f6c7.jpg', 'Ottapalam', 'oth@gmail.com', 'oth123', 1),
(9, 'Chittur Community Health Centre', 3, 'efd1a70b9675dbd9484ba0643d97dd46_39568a2a2fcc.jpg', 'Chittur', 'cah@gmail.com', 'cch123', 1);

-- --------------------------------------------------------

--
-- Table structure for table `parent`
--

DROP TABLE IF EXISTS `parent`;
CREATE TABLE IF NOT EXISTS `parent` (
  `pid` int NOT NULL AUTO_INCREMENT,
  `pname` varchar(30) NOT NULL,
  `mob` char(11) NOT NULL,
  `pemail` varchar(40) NOT NULL,
  `passwd` varchar(10) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `parent`
--

INSERT INTO `parent` (`pid`, `pname`, `mob`, `pemail`, `passwd`) VALUES
(1, 'Peter', '1234567890', 'peter123@gmail.com', 'abc123'),
(2, 'Arun', '1234567890', 'arun@gmail.com', 'abc123'),
(3, 'Gopika', '1234567890', 'gopu@gmail.com', 'abc123');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
  `sid` int NOT NULL AUTO_INCREMENT,
  `vid` int NOT NULL,
  `hid` int NOT NULL,
  `date` date NOT NULL,
  `units` int NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`sid`, `vid`, `hid`, `date`, `units`) VALUES
(1, 1, 1, '2025-10-28', 19),
(2, 1, 4, '2025-10-28', 14),
(3, 2, 2, '2025-10-29', 20),
(4, 3, 5, '2025-10-30', 15),
(5, 3, 7, '2025-10-28', 20),
(6, 4, 9, '2025-10-29', 14),
(7, 6, 1, '2025-10-30', 30),
(8, 7, 1, '2025-10-31', 25);

-- --------------------------------------------------------

--
-- Table structure for table `vaccine`
--

DROP TABLE IF EXISTS `vaccine`;
CREATE TABLE IF NOT EXISTS `vaccine` (
  `vid` int NOT NULL AUTO_INCREMENT,
  `vname` varchar(70) NOT NULL,
  `vimage` varchar(150) NOT NULL,
  `period` varchar(20) NOT NULL,
  PRIMARY KEY (`vid`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vaccine`
--

INSERT INTO `vaccine` (`vid`, `vname`, `vimage`, `period`) VALUES
(1, 'Oral Polio Vaccine (OPV) - 1', '3470516a60ce237bc19998e57f35d793_06dd827ba6f.jpg', '6weeks'),
(2, 'Rotavirus Vaccine (RVV) - 1', '79fc112536c1255c331d2bbb50d47b06_c80869cfe17e17f.png', '6weeks'),
(3, 'Pentavalent-1', 'f7b64cb7325c759f181f7c220bf11676_5bea1dd1db5fc.jpg', '6weeks'),
(4, 'Oral Polio Vaccine (OPV) - 3', 'b1b75895ad35fe116bdac57d19199609_5ca78ba449930bb.jpg', '14 weeks'),
(5, 'Inactivated Polio Vaccine (IPV-2)', '2385fd74ed730cd16c005c80bcb3f463_d61eecac6315c12966.jpg', '14 weeks'),
(6, 'Measles-Rubella   (MR-1)', 'd1be857596fcc51a0eedf79468181848_1ac07c093f.jpg', '9 months'),
(7, 'Pneumococcal Conjugate Vaccine', 'ab7187f18b8c06ba890a90df4dae0c0d_423236d74af.jpg', '9 months'),
(8, 'DPT Booster-1', 'ee9f17c217f411c94c5df1faa71f4e24_91e771b3b1a64.png', '16 months'),
(9, 'Measles-Rubella (MR-2)', '98a9f08c131912149a49e28fa30f7caa_6287ad949c54.jpg', '16 months'),
(10, 'Typhoid Vaccine', 'e039470373f6d74bd069f5cdb3823520_a3f37a78e4.jpg', '5 years'),
(11, 'MMR Vaccine', '63c06c5e17351a366ed4141d745e1c73_3b1999fe839ce6235.jpg', '5 years'),
(12, 'Human Papillomavirus Vaccine (HPV)', '9ba10661f5c606071780a101c96128e4_a888a1bc65ef055.jpg', '10 years');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
