-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2021 at 08:11 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `buzzapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `barbershop`
--

CREATE TABLE `barbershop` (
  `barbershopId` int(11) NOT NULL,
  `adminemail` varchar(150) DEFAULT NULL,
  `name` text NOT NULL,
  `image` text DEFAULT NULL,
  `address` text NOT NULL,
  `city` text NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` text NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `barbershop`
--

INSERT INTO `barbershop` (`barbershopId`, `adminemail`, `name`, `image`, `address`, `city`, `state`, `zip`, `latitude`, `longitude`) VALUES
(1, 'kwolcott@longhornhdllc.com', 'Next Stop Hair Salon', 'https://buzzapp-profile-pictures.s3.us-east-2.amazonaws.com/phpHSafJf', '217 N Henderson Blvd', 'Kilgore', 'TX', '75662', 32.3818115, -94.8694254),
(2, 'KWolcott@patriots.uttyler.edu', 'Pruitt\'s Salon', 'https://lh3.googleusercontent.com/p/AF1QipPWBE9l4RbbyV9bF4rwhtBA-640pVlys17L_mBf=s1600-w400-h400', '2230 West Grande Blvd Ste #104', 'Tyler', 'TX', '75703', 32.2804463, -95.3317168),
(3, 'k@k.com', 'Trend Setters Hair Salon', '../images/clipartbarbershop.jpg', '203 East Main Street', 'Kilgore', 'TX', '75662', 32.3863724, -94.8755163),
(4, 'lee669592@gmail.com', 'Bruce Lee Master Cuts', NULL, '3900 University', 'Tyler', 'TX', '75703', 32.3157449, -95.2542312);

-- --------------------------------------------------------

--
-- Table structure for table `barbershophours`
--

CREATE TABLE `barbershophours` (
  `barbershopId` int(11) NOT NULL,
  `day` int(9) NOT NULL,
  `open` varchar(8) NOT NULL,
  `close` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `barbershophours`
--

INSERT INTO `barbershophours` (`barbershopId`, `day`, `open`, `close`) VALUES
(1, 2, '8:00 AM', '8:00 PM'),
(1, 3, '8:00 AM', '8:00 PM'),
(1, 4, '8:00 AM', '8:00 PM'),
(1, 5, '8:00 AM', '9:00 PM'),
(1, 6, '8:00 AM', '9:00 PM'),
(2, 2, '9:00 AM', '5:30 PM'),
(2, 3, '9:00 AM', '5:30 PM'),
(2, 4, '9:00 AM', '5:30 PM'),
(2, 5, '9:00 AM', '5:30 PM'),
(2, 6, '8:30 AM', '5:30 PM'),
(4, 0, '5:00 PM', '5:15 PM'),
(4, 1, '5:15 PM', '5:30 PM'),
(4, 2, '5:45 PM', '6:00 PM'),
(4, 3, '6:15 PM', '6:30 PM'),
(4, 4, '6:45 PM', '7:00 PM'),
(4, 5, '7:15 PM', '7:30 PM');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id` int(11) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `password` text NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` text DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `month` varchar(2) DEFAULT NULL,
  `day` varchar(2) DEFAULT NULL,
  `time` varchar(8) DEFAULT NULL,
  `profilePicture` text DEFAULT NULL,
  `vip` tinyint(1) DEFAULT NULL,
  `promotionId` int(11) DEFAULT NULL,
  `dob` varchar(5) DEFAULT NULL,
  `notificationMadeAppointment` tinyint(1) NOT NULL DEFAULT 1,
  `notificationProfessionalReschedule` tinyint(1) NOT NULL DEFAULT 1,
  `notificationReschedule` tinyint(1) NOT NULL DEFAULT 1,
  `notificationAccept` tinyint(1) NOT NULL DEFAULT 1,
  `notificationDecline` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`id`, `firstName`, `lastName`, `password`, `email`, `phone`, `year`, `month`, `day`, `time`, `profilePicture`, `vip`, `promotionId`, `dob`, `notificationMadeAppointment`, `notificationProfessionalReschedule`, `notificationReschedule`, `notificationAccept`, `notificationDecline`) VALUES
(1, 'Keith', 'Wolcott', '$2y$10$UTRVPGE7nh.ni2syYT5wr.ztsXlLQGKC4f39Bd2Xu.9VmDqPIKix2', 'wolcott.keith@gmail.com', '903-424-1868', '2019', '02', '21', '4:30 PM', '/images/profileimages/php4tyYey', NULL, NULL, '', 1, 1, 1, 1, 1),
(2, 'Zachary', 'Gray', '$2y$10$WsTtsiaRmeWrojdf9V0UZenxwTlDebHYRnBw7SLmIEk4VdOAJn44G', 'willmegas1@gmail.com', '903-7469989', '2019', '02', '25', '3:22 PM', 'https://s3.us-east-2.amazonaws.com/buzzapp-profile-pictures/6389cec3029a97c4962ead82eb3f8346.png', NULL, NULL, '', 1, 1, 1, 1, 1),
(3, 'Sean', 'Dykes', '$2y$10$6vEjWH/DVJlgGtdRvdyMG.hEqnesMj299U.lat1evEX120MhRAk2G', 'sedykes95@gmail.com', '9033407659', '2019', '02', '25', '5:10 PM', 'https://s3.us-east-2.amazonaws.com/buzzapp-profile-pictures/phpZQZgR1', NULL, NULL, '', 1, 1, 1, 1, 1),
(4, 'Denzel', 'Washington ', '$2y$10$hx6T0TPFUKgSWwBJiNoReujz9oH2AwsusIfNHR6LSSPocjmd0E8B.', 'dwash88@gmail.com', '1234567890', '2019', '02', '26', '12:30 PM', NULL, NULL, NULL, '', 1, 1, 1, 1, 1),
(5, 'Jamell', 'Ford', '$2y$10$ruQxiEn/rpJiQTSB1sFdXe0ZucAY9UUd0KnzixS.I7Gr7As.wS1m.', 'fordjamell3@gmail.com', '9035304120', '2019', '03', '19', '11:06 PM', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1),
(6, 'Mark', 'Wilson', '$2y$10$lQvRgH/qkG/.yBYGuaanNOd55O.8swQflSavKwRnUFuFRPih5DDOe', 'mw84460@gmail.com', NULL, '2019', '04', '16', '04:39 PM', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1),
(8, 'Kevin', 'Wolcott', '$2y$10$DH5UtAhdOTt76BrCuIruNeQ06q.lgWJGxl8qtnGJzTpu.wwEzAvfe', 'kwolcott@longhornhdllc.com', NULL, '2019', '04', '20', '02:26 PM', '/images/profileimages/phpfLQ3zk', NULL, NULL, NULL, 1, 1, 1, 1, 1),
(9, 'Gwendolyn ', 'Arhin', '$2y$10$ozlpZRQTiWc9GJ9zn/7j1u/Rt7mTH0knzeMUEOKyRyoZaDBoakJga', 'wendyarhin54@gmail.com', NULL, '2021', '07', '17', '08:34 PM', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `closed`
--

CREATE TABLE `closed` (
  `barbershopId` int(11) NOT NULL,
  `year` varchar(4) NOT NULL,
  `month` varchar(2) NOT NULL,
  `day` varchar(2) NOT NULL,
  `open` varchar(8) DEFAULT NULL,
  `close` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `closed`
--

INSERT INTO `closed` (`barbershopId`, `year`, `month`, `day`, `open`, `close`) VALUES
(1, '2021', '10', '26', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notificationboard`
--

CREATE TABLE `notificationboard` (
  `postid` int(11) NOT NULL,
  `professionalEmail` varchar(150) NOT NULL,
  `message` varchar(2000) NOT NULL,
  `year` varchar(4) NOT NULL,
  `day` varchar(2) NOT NULL,
  `month` varchar(2) NOT NULL,
  `time` varchar(8) NOT NULL,
  `upcomingonly` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `notificationboard`
--

INSERT INTO `notificationboard` (`postid`, `professionalEmail`, `message`, `year`, `day`, `month`, `time`, `upcomingonly`) VALUES
(1, 'kwolcott@longhornhdllc.com', 'This is a new barbershop.', '2019', '17', '04', '10:28 AM', 0),
(2, 'kwolcott@longhornhdllc.com', 'Call 903-424-1868 if you need to talk to me.', '2019', '17', '04', '10:28 AM', 1);

-- --------------------------------------------------------

--
-- Table structure for table `professional`
--

CREATE TABLE `professional` (
  `id` int(11) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `password` text NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` text DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `month` varchar(2) DEFAULT NULL,
  `day` varchar(2) DEFAULT NULL,
  `time` varchar(8) DEFAULT NULL,
  `barbershopId` int(11) DEFAULT NULL,
  `accepted` tinyint(1) NOT NULL DEFAULT 0,
  `beingdeleted` tinyint(1) NOT NULL DEFAULT 0,
  `paid` tinyint(1) NOT NULL DEFAULT 0,
  `notificationAccept` tinyint(1) NOT NULL DEFAULT 1,
  `notificationDecline` tinyint(1) NOT NULL DEFAULT 1,
  `notificationJoinRequest` tinyint(1) NOT NULL DEFAULT 1,
  `notificationClientReschedule` tinyint(1) NOT NULL DEFAULT 1,
  `notificationRemindReschedule` tinyint(1) NOT NULL DEFAULT 1,
  `notificationClientRequest` tinyint(1) NOT NULL DEFAULT 1,
  `notificationDeleteMessage` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `professional`
--

INSERT INTO `professional` (`id`, `firstName`, `lastName`, `password`, `email`, `phone`, `year`, `month`, `day`, `time`, `barbershopId`, `accepted`, `beingdeleted`, `paid`, `notificationAccept`, `notificationDecline`, `notificationJoinRequest`, `notificationClientReschedule`, `notificationRemindReschedule`, `notificationClientRequest`, `notificationDeleteMessage`) VALUES
(1, 'Keith', 'Wolcott', '$2y$10$lo42atk88iA6/.TyRCfMzOBLM.agxm5XmXm9dXQ/biXXKfLG4wuVa', 'KWolcott@patriots.uttyler.edu', '903-424-1868', '2019', '04', '16', '2:33 PM', 2, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(2, 'Andrew', 'Wolcott', '$2y$10$Cjl7.Q9jwKJgglH1XQsFm.JQVz0EggBdZhJljfDYr1lyyz4.a26te', 'wolcott.keith@gmail.com', NULL, '2019', '04', '16', '2:45 PM', 1, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(3, 'Will', 'Megas', '$2y$10$9ELFcE3cWl8CXMxeGXnzbeIOU/FVV39sskqDIeivQpS7SpQdsXnYe', 'willmegas1@gmail.com', '1111111111', '2019', '03', '16', '4:29 PM', NULL, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(5, 'Clark', 'Kent', '$2y$10$pXUKpC4wJ4ct7bUxA2X6keCZiY/VAbClHqW1BE77u0RWQasPdhCi6', 'jamellford3@gmail.com', '9035928111', '2019', '03', '19', '09:44 PM', NULL, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(6, 'Kevin', 'Wolcott', '$2y$10$UTRVPGE7nh.ni2syYT5wr.ztsXlLQGKC4f39Bd2Xu.9VmDqPIKix2', 'kwolcott@longhornhdllc.com', NULL, '2019', '03', '16', '09:03 PM', 1, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(7, 'Sean', 'Dykes', '$2y$10$ZzFs0S2HG.UPkRgNWLZuuuvbZTQv8rhyjmsGl6fCFpkEdT6.fmPWG', 'sedykes95@gmail.com', NULL, '2019', '04', '16', '09:32 PM', NULL, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(8, 'Bill', 'Walker', '$2y$10$glIbkssLTK/CWajITraqmettSvx6kr9fcSKB/EPtLvUe6D3RPnibK', 'bwalker@gmail.com', NULL, '2019', '04', '18', '04:58 PM', NULL, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(9, 'Richard', 'Wolcott', '$2y$10$IvPQPq.pQGOnG8i7BT1Y0eU8OlnBENdSy.d3IlhFLMjw6Eth68gzO', 'richard_wolcott@hotmail.com', NULL, '2019', '04', '18', '05:00 PM', NULL, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(10, 'Bruce', 'Lee', '$2y$10$vRwCIDa9hnYmRDfHB/HYxuTiAUC/dXMCsPrkWSp0ThAerKBwurGza', 'lee669592@gmail.com', '9035304120', '2019', '04', '18', '05:19 PM', 4, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `professionalbreak`
--

CREATE TABLE `professionalbreak` (
  `professionalEmail` varchar(200) NOT NULL,
  `day` int(11) NOT NULL,
  `start` varchar(8) NOT NULL,
  `end` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `professionalbreak`
--

INSERT INTO `professionalbreak` (`professionalEmail`, `day`, `start`, `end`) VALUES
('d@d.com', 2, '12:00 PM', '12:40 PM'),
('d@d.com', 3, '12:00 PM', '12:40 PM'),
('d@d.com', 4, '12:00 PM', '12:40 PM'),
('d@d.com', 5, '12:00 PM', '12:40 PM'),
('d@d.com', 6, '12:00 PM', '12:40 PM'),
('kwolcott@longhornhdllc.com', 4, '12:00 PM', '12:30 PM'),
('kwolcott@longhornhdllc.com', 5, '12:00 PM', '12:30 PM'),
('KWolcott@patriots.uttyler.edu', 2, '12:00 PM', '12:30 PM'),
('KWolcott@patriots.uttyler.edu', 3, '12:00 PM', '12:30 PM'),
('KWolcott@patriots.uttyler.edu', 4, '12:00 PM', '12:30 PM'),
('KWolcott@patriots.uttyler.edu', 5, '12:00 PM', '12:30 PM'),
('KWolcott@patriots.uttyler.edu', 6, '12:00 PM', '12:30 PM'),
('sedykes95@gmail.com', 1, '12:30 PM', '1:00 PM'),
('sedykes95@gmail.com', 2, '12:30 PM', '1:00 PM'),
('sedykes95@gmail.com', 3, '12:30 PM', '1:00 PM'),
('sedykes95@gmail.com', 4, '12:30 PM', '1:00 PM'),
('wolcott.keith@gmail.com', 3, '12:30 PM', '1:00 PM'),
('wolcott.keith@gmail.com', 6, '12:30 PM', '1:00 PM');

-- --------------------------------------------------------

--
-- Table structure for table `professionalhours`
--

CREATE TABLE `professionalhours` (
  `professionalEmail` varchar(150) NOT NULL,
  `day` int(11) NOT NULL,
  `start` varchar(8) NOT NULL,
  `end` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `professionalhours`
--

INSERT INTO `professionalhours` (`professionalEmail`, `day`, `start`, `end`) VALUES
('d@d.com', 2, '8:00 AM', '8:00 PM'),
('d@d.com', 3, '8:00 AM', '8:00 PM'),
('d@d.com', 4, '8:00 AM', '8:00 PM'),
('d@d.com', 5, '8:00 AM', '9:00 PM'),
('d@d.com', 6, '8:00 AM', '9:00 PM'),
('kwolcott@longhornhdllc.com', 2, '3:00 PM', '8:00 PM'),
('kwolcott@longhornhdllc.com', 3, '3:00 PM', '8:00 PM'),
('kwolcott@longhornhdllc.com', 4, '8:00 AM', '2:30 PM'),
('kwolcott@longhornhdllc.com', 5, '8:00 AM', '2:30 PM'),
('KWolcott@patriots.uttyler.edu', 2, '9:00 AM', '5:30 PM'),
('KWolcott@patriots.uttyler.edu', 3, '9:00 AM', '5:30 PM'),
('KWolcott@patriots.uttyler.edu', 4, '9:00 AM', '5:30 PM'),
('KWolcott@patriots.uttyler.edu', 5, '9:00 AM', '5:30 PM'),
('KWolcott@patriots.uttyler.edu', 6, '8:30 AM', '5:30 PM'),
('wolcott.keith@gmail.com', 3, '8:00 AM', '3:00 PM'),
('wolcott.keith@gmail.com', 4, '2:30 PM', '8:00 PM'),
('wolcott.keith@gmail.com', 5, '2:30 PM', '9:00 PM'),
('wolcott.keith@gmail.com', 6, '8:00 AM', '3:00 PM');

-- --------------------------------------------------------

--
-- Table structure for table `professionaloff`
--

CREATE TABLE `professionaloff` (
  `professionalEmail` varchar(150) NOT NULL,
  `year` varchar(4) NOT NULL,
  `day` varchar(2) NOT NULL,
  `month` varchar(2) NOT NULL,
  `start` varchar(8) DEFAULT NULL,
  `end` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `professionaloff`
--

INSERT INTO `professionaloff` (`professionalEmail`, `year`, `day`, `month`, `start`, `end`) VALUES
('wolcott.keith@gmail.com', '2021', '06', '10', NULL, NULL),
('wolcott.keith@gmail.com', '2021', '07', '10', '2:30 PM', '7:15 PM');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `ratingId` int(11) NOT NULL,
  `schedulingId` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `text` varchar(4000) NOT NULL,
  `year` varchar(4) NOT NULL,
  `month` varchar(2) NOT NULL,
  `day` varchar(2) NOT NULL,
  `time` varchar(8) NOT NULL,
  `readReview` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`ratingId`, `schedulingId`, `rating`, `text`, `year`, `month`, `day`, `time`, `readReview`) VALUES
(1, 1, 4, 'it was good', '2019', '04', '18', '05:08 PM', 1),
(2, 2, 4, 'It was a pleasant experience.', '2019', '04', '20', '03:06 PM', 1);

-- --------------------------------------------------------

--
-- Table structure for table `scheduling`
--

CREATE TABLE `scheduling` (
  `id` int(11) NOT NULL,
  `clientEmail` text NOT NULL,
  `professionalEmail` text NOT NULL,
  `year` varchar(4) NOT NULL,
  `month` varchar(2) NOT NULL,
  `day` varchar(2) NOT NULL,
  `timestart` text NOT NULL,
  `serviceId` int(11) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `madereview` tinyint(1) NOT NULL DEFAULT 0,
  `remindaboutreview` tinyint(1) NOT NULL DEFAULT 0,
  `cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `byprofessional` tinyint(1) NOT NULL DEFAULT 0,
  `reason` varchar(2000) DEFAULT NULL,
  `remind` tinyint(1) NOT NULL DEFAULT 0,
  `newclient` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `scheduling`
--

INSERT INTO `scheduling` (`id`, `clientEmail`, `professionalEmail`, `year`, `month`, `day`, `timestart`, `serviceId`, `confirmed`, `madereview`, `remindaboutreview`, `cancelled`, `byprofessional`, `reason`, `remind`, `newclient`) VALUES
(1, 'kwolcott@longhornhdllc.com', 'wolcott.keith@gmail.com', '2019', '04', '18', '02:00 PM', 6, 1, 1, 0, 0, 0, NULL, 0, 0),
(2, 'kwolcott@longhornhdllc.com', 'wolcott.keith@gmail.com', '2019', '04', '19', '09:00 AM', 6, 1, 1, 0, 0, 0, NULL, 0, 0),
(3, 'al4395440@gmail.com', 'KWolcott@patriots.uttyler.edu', '2019', '07', '03', '11:00 AM', 2, 1, 0, 1, 0, 0, NULL, 0, 0),
(4, 'kwolcott@longhornhdllc.com', 'wolcott.keith@gmail.com', '2019', '05', '15', '10:00 AM', 6, 0, 0, 1, 0, 0, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `barbershopId` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `duration` int(11) NOT NULL,
  `free` tinyint(1) NOT NULL DEFAULT 0,
  `discountedlimit` float NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `barbershopId`, `name`, `price`, `description`, `duration`, `free`, `discountedlimit`) VALUES
(1, 2, 'Basic Cut', '20.00', 'Basic cut for men and women', 30, 0, 0),
(2, 2, 'Kid Cut', '15.00', '', 20, 1, 0.25),
(3, 2, 'Shampoo', '5.00', '', 5, 1, 0),
(4, 2, 'Wet Cut', '30.00', '', 40, 0, 0),
(5, 2, 'Shave for Men', '15.00', '', 30, 0, 0),
(6, 1, 'Wash and Style', '20.00', 'Includes Blow Dry', 30, 0, 0),
(7, 1, 'Event Styling', '50.00', '', 60, 0, 0),
(8, 1, 'Bridal Styling', '60.00', '', 60, 0, 0),
(9, 1, 'Red Carpet Blowout (deep conditioning treatment)', '30.00', '', 45, 0, 0),
(10, 1, 'Child Cut (10 and under)', '10.00', '', 25, 0, 0),
(11, 1, 'Clipper Cut', '15.00', '', 30, 0, 0),
(12, 1, 'Wash, Cut & Style', '35.00', '', 40, 0, 0),
(13, 1, 'Bang Trim', '5.00', '', 10, 0, 0),
(14, 1, 'Single Process', '65.00', '', 50, 0, 0),
(15, 1, 'Single Process w/ Cut', '100.00', '', 80, 0, 0),
(16, 1, 'Full Foil Highlight', '85.00', '', 60, 0, 0),
(17, 1, 'Full Foil Highlight w/ Cut', '120.00', '', 90, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barbershop`
--
ALTER TABLE `barbershop`
  ADD PRIMARY KEY (`barbershopId`),
  ADD UNIQUE KEY `id` (`barbershopId`),
  ADD UNIQUE KEY `adminemail` (`adminemail`);

--
-- Indexes for table `barbershophours`
--
ALTER TABLE `barbershophours`
  ADD PRIMARY KEY (`barbershopId`,`day`,`open`,`close`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- Indexes for table `closed`
--
ALTER TABLE `closed`
  ADD PRIMARY KEY (`barbershopId`,`year`,`month`,`day`);

--
-- Indexes for table `notificationboard`
--
ALTER TABLE `notificationboard`
  ADD PRIMARY KEY (`postid`),
  ADD UNIQUE KEY `postid` (`postid`);

--
-- Indexes for table `professional`
--
ALTER TABLE `professional`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `professionalbreak`
--
ALTER TABLE `professionalbreak`
  ADD PRIMARY KEY (`professionalEmail`,`day`);

--
-- Indexes for table `professionalhours`
--
ALTER TABLE `professionalhours`
  ADD PRIMARY KEY (`professionalEmail`,`day`,`start`,`end`);

--
-- Indexes for table `professionaloff`
--
ALTER TABLE `professionaloff`
  ADD PRIMARY KEY (`professionalEmail`,`year`,`day`,`month`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`ratingId`),
  ADD UNIQUE KEY `schedulingId` (`schedulingId`);

--
-- Indexes for table `scheduling`
--
ALTER TABLE `scheduling`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barbershop`
--
ALTER TABLE `barbershop`
  MODIFY `barbershopId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notificationboard`
--
ALTER TABLE `notificationboard`
  MODIFY `postid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `professional`
--
ALTER TABLE `professional`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `ratingId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `scheduling`
--
ALTER TABLE `scheduling`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `fkey` FOREIGN KEY (`schedulingId`) REFERENCES `scheduling` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
