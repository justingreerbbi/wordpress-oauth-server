-- phpMyAdmin SQL Dump
-- version 4.1.9
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2014 at 04:46 PM
-- Server version: 5.5.34
-- PHP Version: 5.5.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `development`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_oauth_access_tokens`
--

DROP TABLE IF EXISTS `wp_oauth_access_tokens`;
CREATE TABLE `wp_oauth_access_tokens` (
  `access_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`access_token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_oauth_access_tokens`
--

INSERT INTO `wp_oauth_access_tokens` (`access_token`, `client_id`, `user_id`, `expires`, `scope`) VALUES
('ighkvr0ymekQMuXiwXfLS3FgeR4qAZbSgscMUdkg', 'c42eaad93bdfb69dff238031c7c94613', '1', '2014-11-03 20:36:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wp_oauth_clients`
--

DROP TABLE IF EXISTS `wp_oauth_clients`;
CREATE TABLE `wp_oauth_clients` (
  `client_id` varchar(80) NOT NULL,
  `client_secret` varchar(80) NOT NULL,
  `redirect_uri` varchar(2000) NOT NULL,
  `grant_types` varchar(80) DEFAULT NULL,
  `scope` varchar(100) DEFAULT NULL,
  `user_id` varchar(80) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_oauth_clients`
--

INSERT INTO `wp_oauth_clients` (`client_id`, `client_secret`, `redirect_uri`, `grant_types`, `scope`, `user_id`, `name`, `description`) VALUES
('c42eaad93bdfb69dff238031c7c94613', 'f67c2bcbfcfa30fccb36f72dca22a817', 'http://oauth-client.dev/callback.php', NULL, NULL, '1', 'Test Cient', 'This is a test client account');

-- --------------------------------------------------------

--
-- Table structure for table `wp_oauth_codes`
--

DROP TABLE IF EXISTS `wp_oauth_codes`;
CREATE TABLE `wp_oauth_codes` (
  `client_id` varchar(62) NOT NULL,
  `code` varchar(62) NOT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_oauth_codes`
--

INSERT INTO `wp_oauth_codes` (`client_id`, `code`, `expires`) VALUES
('c42eaad93bdfb69dff238031c7c94613', '4HKUD9nL1uKNrmN5VwUr35Mj5KfHNPISxtNaDaWE', '2014-11-03 18:43:51'),
('c42eaad93bdfb69dff238031c7c94613', 'GMetxpqqklRPuIc2AsU3uM3AgJVoFuwlhKPPafgu', '2014-11-03 17:37:49'),
('c42eaad93bdfb69dff238031c7c94613', 'W0jE5i5BpDEBe9NgYtK9KF348kkwknLgn5Vsn04N', '2014-11-03 17:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `wp_oauth_jwt`
--

DROP TABLE IF EXISTS `wp_oauth_jwt`;
CREATE TABLE `wp_oauth_jwt` (
  `client_id` varchar(80) NOT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `public_key` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `wp_oauth_refresh_tokens`;
CREATE TABLE `wp_oauth_refresh_tokens` (
  `refresh_token` varchar(40) NOT NULL,
  `client_id` varchar(80) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`refresh_token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_oauth_refresh_tokens`
--

INSERT INTO `wp_oauth_refresh_tokens` (`refresh_token`, `client_id`, `user_id`, `expires`, `scope`) VALUES
('rE6d94wG1LrUP7a3YeuyeFqv8Dh2QBjigpwqu26v', 'c42eaad93bdfb69dff238031c7c94613', '1', '2014-11-03 20:36:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wp_oauth_scopes`
--

DROP TABLE IF EXISTS `wp_oauth_scopes`;
CREATE TABLE `wp_oauth_scopes` (
  `scope` text,
  `is_default` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_oauth_scopes`
--

INSERT INTO `wp_oauth_scopes` (`scope`, `is_default`) VALUES
('profile', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
