-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2025 at 05:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `musiclibrary`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `AlbumID` int(11) NOT NULL,
  `AlbumName` varchar(100) NOT NULL,
  `ReleaseYear` year(4) DEFAULT NULL,
  `ArtistID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`AlbumID`, `AlbumName`, `ReleaseYear`, `ArtistID`) VALUES
(1, 'Metallica (The Black Album)', '1991', 1),
(2, 'Reload', '1997', 1),
(3, 'Deep Purple in Rock', '1970', 2),
(4, 'Machine Head', '1972', 2),
(5, 'First Impressions of Earth', '2006', 3),
(6, 'Room on Fire', '2003', 3),
(7, 'Echoes, Silence, Patience & Grace', '2007', 4),
(8, 'Only by the Night', '2008', 5);

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

CREATE TABLE `artists` (
  `ArtistID` int(11) NOT NULL,
  `ArtistName` varchar(100) NOT NULL,
  `CompanyID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artists`
--

INSERT INTO `artists` (`ArtistID`, `ArtistName`, `CompanyID`) VALUES
(1, 'Metallica', 1),
(2, 'Deep Purple', 2),
(3, 'The Strokes', 3),
(4, 'Foo Fighters', 4),
(5, 'Kings of Leon', 5);

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `CompanyID` int(11) NOT NULL,
  `CompanyName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`CompanyID`, `CompanyName`) VALUES
(1, 'Elektra Records'),
(2, 'Harvest Records'),
(3, 'RCA Records'),
(4, 'Roswell Records'),
(5, 'Columbia Records');

-- --------------------------------------------------------

--
-- Table structure for table `creatoralbums`
--

CREATE TABLE `creatoralbums` (
  `CreatorAlbumID` int(11) NOT NULL,
  `CreatorID` int(11) NOT NULL,
  `CreatorAlbumName` varchar(255) NOT NULL,
  `ReleaseYear` year(4) DEFAULT NULL,
  `IsPublic` tinyint(1) DEFAULT 0,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `creatorsongs`
--

CREATE TABLE `creatorsongs` (
  `CreatorSongID` int(11) NOT NULL,
  `CreatorAlbumID` int(11) NOT NULL,
  `CreatorSongName` varchar(255) NOT NULL,
  `ReleaseYear` year(4) DEFAULT NULL,
  `FilePath` varchar(255) NOT NULL,
  `Duration` int(11) DEFAULT NULL,
  `IsPublic` tinyint(1) DEFAULT 0,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listeninghistory`
--

CREATE TABLE `listeninghistory` (
  `HistoryID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `SongID` int(11) DEFAULT NULL,
  `ListenedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listeninghistory`
--

INSERT INTO `listeninghistory` (`HistoryID`, `UserID`, `SongID`, `ListenedAt`) VALUES
(1, 2, 1, '2025-05-01 10:15:00'),
(2, 3, 2, '2025-05-02 15:30:00'),
(3, 4, 3, '2025-05-03 12:45:00'),
(4, 5, 4, '2025-05-04 08:00:00'),
(5, 6, 5, '2025-05-05 18:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `pageviews`
--

CREATE TABLE `pageviews` (
  `ViewID` int(11) NOT NULL,
  `PageURL` varchar(255) NOT NULL,
  `VisitTime` datetime DEFAULT current_timestamp(),
  `VisitorIP` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pageviews`
--

INSERT INTO `pageviews` (`ViewID`, `PageURL`, `VisitTime`, `VisitorIP`) VALUES
(1, '/music-portal/admin/dashboard.php', '2025-05-23 05:12:10', '::1'),
(2, '/music-portal/admin/manage_users.php', '2025-05-23 05:12:11', '::1'),
(3, '/music-portal/admin/dashboard.php', '2025-05-23 05:12:13', '::1'),
(4, '/music-portal/admin/analytics.php', '2025-05-23 05:12:14', '::1'),
(5, '/music-portal/admin/dashboard.php', '2025-05-23 05:13:08', '::1'),
(6, '/music-portal/admin/manage_users.php', '2025-05-23 05:13:09', '::1'),
(7, '/music-portal/admin/dashboard.php', '2025-05-23 05:13:10', '::1'),
(8, '/music-portal/admin/analytics.php', '2025-05-23 05:13:11', '::1'),
(9, '/music-portal/admin/dashboard.php', '2025-05-23 05:13:25', '::1'),
(10, '/music-portal/admin/manage_users.php', '2025-05-23 05:13:26', '::1'),
(11, '/music-portal/admin/dashboard.php', '2025-05-23 05:13:27', '::1'),
(12, '/music-portal/index.php?message=logged_out', '2025-05-23 05:13:28', '::1'),
(13, '/music-portal/admin/dashboard.php', '2025-05-23 05:13:34', '::1'),
(14, '/music-portal/admin/analytics.php', '2025-05-23 05:13:35', '::1'),
(15, '/music-portal/admin/dashboard.php', '2025-05-23 05:13:52', '::1'),
(16, '/music-portal/index.php?message=logged_out', '2025-05-23 05:13:54', '::1'),
(17, '/music-portal/index.php?message=logged_out', '2025-05-23 05:14:40', '::1'),
(18, '/music-portal/admin/dashboard.php', '2025-05-23 05:15:01', '::1'),
(19, '/music-portal/index.php?message=logged_out', '2025-05-23 05:15:13', '::1'),
(20, '/music-portal/index.php?message=logged_out', '2025-05-23 05:15:35', '::1'),
(21, '/music-portal/listener/dashboard.php', '2025-05-23 05:23:22', '::1'),
(22, '/music-portal/listener/dashboard.php', '2025-05-23 05:23:45', '::1'),
(23, '/music-portal/listener/dashboard.php', '2025-05-23 05:25:27', '::1'),
(24, '/music-portal/index.php', '2025-05-23 05:25:31', '::1'),
(25, '/music-portal/index.php', '2025-05-23 05:33:01', '::1'),
(26, '/music-portal/index.php', '2025-05-23 05:35:25', '::1'),
(27, '/music-portal/listener/my_profile.php', '2025-05-23 05:35:42', '::1'),
(28, '/music-portal/index.php', '2025-05-23 05:35:49', '::1'),
(29, '/music-portal/listener/my_profile.php', '2025-05-23 05:39:13', '::1'),
(30, '/music-portal/index.php?message=logged_out', '2025-05-23 05:39:14', '::1'),
(31, '/music-portal/index.php?message=logged_out', '2025-05-23 05:39:18', '::1'),
(32, '/music-portal/', '2025-05-23 05:39:23', '::1'),
(33, '/music-portal/', '2025-05-23 05:41:36', '::1'),
(34, '/music-portal/', '2025-05-23 05:41:38', '::1'),
(35, '/music-portal/listener/my_profile.php', '2025-05-23 05:41:46', '::1'),
(36, '/music-portal/index.php', '2025-05-23 05:41:53', '::1'),
(37, '/music-portal/listener/my_profile.php', '2025-05-23 05:49:03', '::1'),
(38, '/music-portal/index.php?message=logged_out', '2025-05-23 05:49:05', '::1'),
(39, '/music-portal/', '2025-05-23 05:49:10', '::1'),
(40, '/music-portal/index.php', '2025-05-23 05:49:22', '::1'),
(41, '/music-portal/index.php', '2025-05-23 05:51:24', '::1'),
(42, '/music-portal/index.php', '2025-05-23 05:51:28', '::1'),
(43, '/music-portal/index.php', '2025-05-23 05:51:31', '::1'),
(44, '/music-portal/index.php', '2025-05-23 05:51:37', '::1'),
(45, '/music-portal/index.php', '2025-05-23 05:51:50', '::1'),
(46, '/music-portal/listener/my_profile.php', '2025-05-23 05:52:14', '::1'),
(47, '/music-portal/index.php?message=logged_out', '2025-05-23 05:52:18', '::1'),
(48, '/music-portal/index.php?message=logged_out', '2025-05-23 05:54:13', '::1'),
(49, '/music-portal/index.php', '2025-05-23 05:54:30', '::1'),
(50, '/music-portal/listener/my_profile.php', '2025-05-23 05:54:36', '::1'),
(51, '/music-portal/index.php?message=logged_out', '2025-05-23 05:54:39', '::1'),
(52, '/music-portal/index.php?message=logged_out', '2025-05-23 05:55:18', '::1'),
(53, '/music-portal/index.php', '2025-05-23 05:55:28', '::1'),
(54, '/music-portal/listener/my_profile.php', '2025-05-23 05:55:32', '::1'),
(55, '/music-portal/listener/my_profile.php', '2025-05-23 05:56:40', '::1'),
(56, '/music-portal/listener/my_profile.php', '2025-05-23 05:56:44', '::1'),
(57, '/music-portal/index.php', '2025-05-23 05:56:54', '::1'),
(58, '/music-portal/index.php', '2025-05-23 05:56:56', '::1'),
(59, '/music-portal/listener/my_profile.php', '2025-05-23 05:56:58', '::1'),
(60, '/music-portal/index.php', '2025-05-23 05:57:06', '::1'),
(61, '/music-portal/listener/my_profile.php', '2025-05-23 05:58:14', '::1'),
(62, '/music-portal/index.php?message=logged_out', '2025-05-23 05:58:17', '::1'),
(63, '/music-portal/admin/dashboard.php', '2025-05-23 05:58:23', '::1'),
(64, '/music-portal/index.php?message=logged_out', '2025-05-23 05:58:51', '::1'),
(65, '/music-portal/admin/dashboard.php', '2025-05-23 05:58:56', '::1'),
(66, '/music-portal/admin/dashboard.php', '2025-05-23 05:59:15', '::1'),
(67, '/music-portal/admin/manage_users.php', '2025-05-23 05:59:16', '::1'),
(68, '/music-portal/admin/dashboard.php', '2025-05-23 05:59:18', '::1'),
(69, '/music-portal/admin/dashboard.php', '2025-05-23 05:59:22', '::1'),
(70, '/music-portal/index.php?message=logged_out', '2025-05-23 05:59:23', '::1'),
(71, '/music-portal/index.php?message=logged_out', '2025-05-23 06:00:12', '::1'),
(72, '/music-portal/admin/dashboard.php', '2025-05-23 06:00:18', '::1'),
(73, '/music-portal/admin/dashboard.php', '2025-05-23 06:00:23', '::1'),
(74, '/music-portal/admin/analytics.php', '2025-05-23 06:00:25', '::1'),
(75, '/music-portal/admin/dashboard.php', '2025-05-23 06:00:27', '::1'),
(76, '/music-portal/admin/manage_users.php', '2025-05-23 06:00:28', '::1'),
(77, '/music-portal/admin/dashboard.php', '2025-05-23 06:00:29', '::1'),
(78, '/music-portal/admin/dashboard.php', '2025-05-23 06:00:31', '::1'),
(79, '/music-portal/index.php?message=logged_out', '2025-05-23 06:00:32', '::1'),
(80, '/music-portal/index.php?message=logged_out', '2025-05-23 06:01:23', '::1'),
(81, '/music-portal/index.php', '2025-05-23 06:01:32', '::1'),
(82, '/music-portal/index.php', '2025-05-23 06:01:41', '::1'),
(83, '/music-portal/listener/my_profile.php', '2025-05-23 06:01:42', '::1'),
(84, '/music-portal/index.php', '2025-05-23 06:01:45', '::1'),
(85, '/music-portal/listener/my_profile.php', '2025-05-23 06:01:48', '::1'),
(86, '/music-portal/index.php', '2025-05-23 06:01:51', '::1'),
(87, '/music-portal/index.php', '2025-05-23 06:11:06', '::1'),
(88, '/music-portal/artist_profile.php?artist_id=1', '2025-05-23 06:11:09', '::1'),
(89, '/music-portal/artist_profile.php?artist_id=1', '2025-05-23 06:11:55', '::1'),
(90, '/music-portal/artist_profile.php?artist_id=2', '2025-05-23 06:12:13', '::1'),
(91, '/music-portal/artist_profile.php?artist_id=3', '2025-05-23 06:12:16', '::1'),
(92, '/music-portal/artist_profile.php?artist_id=4', '2025-05-23 06:12:19', '::1'),
(93, '/music-portal/listener/my_profile.php', '2025-05-23 06:12:28', '::1'),
(94, '/music-portal/index.php?message=logged_out', '2025-05-23 06:12:30', '::1'),
(95, '/music-portal/index.php?message=logged_out', '2025-05-23 06:13:22', '::1'),
(96, '/music-portal/', '2025-05-23 06:13:30', '::1'),
(97, '/music-portal/', '2025-05-23 06:13:52', '::1'),
(98, '/music-portal/', '2025-05-23 06:14:50', '::1'),
(99, '/music-portal/index.php', '2025-05-23 06:14:51', '::1'),
(100, '/music-portal/index.php', '2025-05-23 06:14:52', '::1'),
(101, '/music-portal/artist_profile.php?artist_id=1', '2025-05-23 06:14:58', '::1'),
(102, '/music-portal/index.php', '2025-05-23 06:14:59', '::1'),
(103, '/music-portal/index.php', '2025-05-23 06:22:13', '::1'),
(104, '/music-portal/index.php', '2025-05-23 06:23:50', '::1'),
(105, '/music-portal/index.php', '2025-05-23 06:23:55', '::1'),
(106, '/music-portal/index.php', '2025-05-23 06:24:01', '::1'),
(107, '/music-portal/index.php', '2025-05-23 06:25:02', '::1'),
(108, '/music-portal/index.php', '2025-05-23 06:25:07', '::1'),
(109, '/music-portal/index.php', '2025-05-23 06:25:18', '::1'),
(110, '/music-portal/index.php', '2025-05-23 06:25:37', '::1'),
(111, '/music-portal/admin/dashboard.php', '2025-05-23 06:25:43', '::1'),
(112, '/music-portal/index.php?message=logged_out', '2025-05-23 06:25:48', '::1'),
(113, '/music-portal/index.php', '2025-05-23 06:25:51', '::1'),
(114, '/music-portal/listener/my_profile.php', '2025-05-23 06:25:54', '::1'),
(115, '/music-portal/index.php?message=logged_out', '2025-05-23 06:25:56', '::1'),
(116, '/music-portal/index.php?message=logged_out', '2025-05-23 06:27:25', '::1'),
(117, '/music-portal/index.php', '2025-05-23 06:27:30', '::1'),
(118, '/music-portal/index.php', '2025-05-23 06:27:36', '::1'),
(119, '/music-portal/index.php', '2025-05-23 06:27:39', '::1'),
(120, '/music-portal/index.php', '2025-05-23 06:28:08', '::1'),
(121, '/music-portal/admin/dashboard.php', '2025-05-23 06:28:12', '::1'),
(122, '/music-portal/index.php?message=logged_out', '2025-05-23 06:28:15', '::1'),
(123, '/music-portal/index.php', '2025-05-23 06:28:20', '::1'),
(124, '/music-portal/index.php', '2025-05-23 06:29:46', '::1'),
(125, '/music-portal/index.php', '2025-05-23 06:29:51', '::1'),
(126, '/music-portal/index.php', '2025-05-23 06:31:40', '::1'),
(127, '/music-portal/artist/dashboard.php', '2025-05-23 06:31:49', '::1'),
(128, '/music-portal/index.php', '2025-05-23 06:31:53', '::1'),
(129, '/music-portal/index.php', '2025-05-23 06:31:53', '::1'),
(130, '/music-portal/index.php', '2025-05-23 06:31:53', '::1'),
(131, '/music-portal/index.php', '2025-05-23 06:31:53', '::1'),
(132, '/music-portal/index.php', '2025-05-23 06:32:00', '::1'),
(133, '/music-portal/index.php?message=logged_out', '2025-05-23 06:32:00', '::1'),
(134, '/music-portal/admin/dashboard.php', '2025-05-23 06:32:01', '::1'),
(135, '/music-portal/index.php?error=access_denied', '2025-05-23 06:32:01', '::1'),
(136, '/music-portal/index.php?error=access_denied', '2025-05-23 06:32:05', '::1'),
(137, '/music-portal/index.php?error=access_denied', '2025-05-23 06:32:45', '::1'),
(138, '/music-portal/index.php', '2025-05-23 06:32:50', '::1'),
(139, '/music-portal/index.php', '2025-05-23 06:34:00', '::1'),
(140, '/music-portal/index.php', '2025-05-23 06:36:09', '::1'),
(141, '/music-portal/artist_profile.php?artist_id=1', '2025-05-23 06:36:54', '::1'),
(142, '/music-portal/index.php', '2025-05-23 06:36:58', '::1'),
(143, '/music-portal/index.php', '2025-05-23 06:36:59', '::1'),
(144, '/music-portal/index.php', '2025-05-23 06:39:42', '::1'),
(145, '/music-portal/index.php', '2025-05-23 06:39:47', '::1'),
(146, '/music-portal/index.php', '2025-05-23 06:39:51', '::1'),
(147, '/music-portal/artist/dashboard.php', '2025-05-23 06:40:22', '::1'),
(148, '/music-portal/index.php', '2025-05-23 06:48:57', '::1'),
(149, '/music-portal/creator/dashboard.php', '2025-05-23 06:49:00', '::1'),
(150, '/music-portal/index.php', '2025-05-23 06:49:00', '::1'),
(151, '/music-portal/index.php', '2025-05-23 06:49:03', '::1'),
(152, '/music-portal/index.php', '2025-05-23 06:52:28', '::1'),
(153, '/music-portal/index.php', '2025-05-23 06:52:33', '::1'),
(154, '/music-portal/creator/dashboard.php', '2025-05-23 06:53:24', '::1'),
(155, '/music-portal/creator/dashboard.php', '2025-05-23 07:13:56', '::1'),
(156, '/music-portal/creator/dashboard.php', '2025-05-23 07:16:05', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `playlists`
--

CREATE TABLE `playlists` (
  `PlaylistID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PlaylistName` varchar(100) NOT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlists`
--

INSERT INTO `playlists` (`PlaylistID`, `UserID`, `PlaylistName`, `CreatedAt`) VALUES
(1, 2, 'My Top Hits', '2025-05-23 05:38:05'),
(2, 2, 'Chill Vibes', '2025-05-23 05:38:05'),
(3, 8, 'Morning Motivation', '2025-05-23 05:38:05'),
(4, 2, 'new', '2025-05-23 05:51:31'),
(5, 19, 'new', '2025-05-23 06:01:32');

-- --------------------------------------------------------

--
-- Table structure for table `playlistsongs`
--

CREATE TABLE `playlistsongs` (
  `PlaylistSongID` int(11) NOT NULL,
  `PlaylistID` int(11) NOT NULL,
  `SongID` int(11) NOT NULL,
  `AddedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlistsongs`
--

INSERT INTO `playlistsongs` (`PlaylistSongID`, `PlaylistID`, `SongID`, `AddedAt`) VALUES
(1, 1, 1, '2025-05-23 05:38:05'),
(2, 1, 4, '2025-05-23 05:38:05'),
(3, 2, 5, '2025-05-23 05:38:05'),
(4, 2, 6, '2025-05-23 05:38:05'),
(5, 3, 2, '2025-05-23 05:38:05'),
(6, 3, 7, '2025-05-23 05:38:05'),
(7, 4, 1, '2025-05-23 05:51:50');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `RoleName`) VALUES
(1, 'Admin'),
(2, 'Creator'),
(4, 'Guest'),
(3, 'Listener');

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `SongID` int(11) NOT NULL,
  `SongName` varchar(100) NOT NULL,
  `ReleaseYear` year(4) DEFAULT NULL,
  `AlbumID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`SongID`, `SongName`, `ReleaseYear`, `AlbumID`) VALUES
(1, 'Nothing Else Matters', '1991', 1),
(2, 'Fuel', '1997', 2),
(3, 'Child in Time', '1970', 3),
(4, 'Smoke on the Water', '1972', 4),
(5, 'Ize of the World', '2006', 5),
(6, 'Reptilia', '2003', 6),
(7, 'Come Alive', '2007', 7),
(8, 'Sex on Fire', '2008', 8),
(9, 'Use Somebody', '2008', 8);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `RoleID` int(11) NOT NULL,
  `IsDeleted` tinyint(1) DEFAULT 0,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `FullName`, `UserName`, `Email`, `Password`, `RoleID`, `IsDeleted`, `CreatedAt`) VALUES
(1, 'Admin User', 'admin', 'admin@music.com', 'adminpass', 1, 0, '2025-05-22 22:43:41'),
(2, 'John Doe', 'john_doe', 'john.doe@email.com', 'password123', 3, 0, '2025-05-22 22:43:41'),
(3, 'Jane Smith', 'jane_smith', 'jane.smith@email.com', 'securepass', 4, 0, '2025-05-22 22:43:41'),
(4, 'Alice Johnson', 'alice_j', 'alice.j@email.com', 'mypassword', 3, 0, '2025-05-22 22:43:41'),
(5, 'Bob Brown', 'bob_b', 'bob.b@email.com', 'pass123', 3, 0, '2025-05-22 22:43:41'),
(6, 'Eva White', 'eva_w', 'eva.w@email.com', 'letmein', 3, 0, '2025-05-22 22:43:41'),
(7, 'James Hetfield', 'james_hetfield', 'james@metallica.com', 'metalpass1', 2, 0, '2025-05-22 22:44:55'),
(8, 'Ian Gillan', 'ian_gillan', 'ian@deeppurple.com', 'purplepass1', 2, 0, '2025-05-22 22:44:55'),
(9, 'Julian Casablancas', 'julian_c', 'julian@strokes.com', 'strokepass1', 2, 0, '2025-05-22 22:44:55'),
(10, 'Dave Grohl', 'dave_grohl', 'dave@foofighters.com', 'foopass1', 2, 0, '2025-05-22 22:44:55'),
(11, 'Caleb Followill', 'caleb_f', 'caleb@kol.com', 'kolpass1', 2, 0, '2025-05-22 22:44:55'),
(12, 'tako', 'tutisani', 'dfgk@gmail.com', 'aa', 3, 0, '2025-05-23 03:29:51'),
(13, 'tako', 'tut', 'tut@gmail.com', '$2y$10$t40zaD5v5Cg5PdNkS21DCuynrLgbY9Ls66VIlZNmhytMOdNrJhzju', 3, 0, '2025-05-23 03:51:11'),
(18, 'tako', 'usern', 'mail@gmail.com', 'pass', 3, 0, '2025-05-23 05:15:32'),
(19, 'tt', 'tt', 'tt@gmail.com', 'tt', 3, 0, '2025-05-23 06:00:50'),
(21, 'Artist', 'artist', 'artist@gmail.com', 'artist', 2, 0, '2025-05-23 06:23:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`AlbumID`),
  ADD KEY `ArtistID` (`ArtistID`);

--
-- Indexes for table `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`ArtistID`),
  ADD KEY `CompanyID` (`CompanyID`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`CompanyID`);

--
-- Indexes for table `creatoralbums`
--
ALTER TABLE `creatoralbums`
  ADD PRIMARY KEY (`CreatorAlbumID`),
  ADD KEY `idx_creator_albums_creatorid` (`CreatorID`);

--
-- Indexes for table `creatorsongs`
--
ALTER TABLE `creatorsongs`
  ADD PRIMARY KEY (`CreatorSongID`),
  ADD KEY `idx_creator_songs_albumid` (`CreatorAlbumID`);

--
-- Indexes for table `listeninghistory`
--
ALTER TABLE `listeninghistory`
  ADD PRIMARY KEY (`HistoryID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `SongID` (`SongID`);

--
-- Indexes for table `pageviews`
--
ALTER TABLE `pageviews`
  ADD PRIMARY KEY (`ViewID`);

--
-- Indexes for table `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`PlaylistID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `playlistsongs`
--
ALTER TABLE `playlistsongs`
  ADD PRIMARY KEY (`PlaylistSongID`),
  ADD UNIQUE KEY `PlaylistID` (`PlaylistID`,`SongID`),
  ADD KEY `SongID` (`SongID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`),
  ADD UNIQUE KEY `RoleName` (`RoleName`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`SongID`),
  ADD KEY `AlbumID` (`AlbumID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `UserName` (`UserName`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `RoleID` (`RoleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `AlbumID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `artists`
--
ALTER TABLE `artists`
  MODIFY `ArtistID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `CompanyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `creatoralbums`
--
ALTER TABLE `creatoralbums`
  MODIFY `CreatorAlbumID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `creatorsongs`
--
ALTER TABLE `creatorsongs`
  MODIFY `CreatorSongID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listeninghistory`
--
ALTER TABLE `listeninghistory`
  MODIFY `HistoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pageviews`
--
ALTER TABLE `pageviews`
  MODIFY `ViewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `playlists`
--
ALTER TABLE `playlists`
  MODIFY `PlaylistID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `playlistsongs`
--
ALTER TABLE `playlistsongs`
  MODIFY `PlaylistSongID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `SongID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `albums`
--
ALTER TABLE `albums`
  ADD CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`ArtistID`) REFERENCES `artists` (`ArtistID`);

--
-- Constraints for table `artists`
--
ALTER TABLE `artists`
  ADD CONSTRAINT `artists_ibfk_1` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`);

--
-- Constraints for table `creatoralbums`
--
ALTER TABLE `creatoralbums`
  ADD CONSTRAINT `creatoralbums_ibfk_1` FOREIGN KEY (`CreatorID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `creatorsongs`
--
ALTER TABLE `creatorsongs`
  ADD CONSTRAINT `creatorsongs_ibfk_1` FOREIGN KEY (`CreatorAlbumID`) REFERENCES `creatoralbums` (`CreatorAlbumID`) ON DELETE CASCADE;

--
-- Constraints for table `listeninghistory`
--
ALTER TABLE `listeninghistory`
  ADD CONSTRAINT `listeninghistory_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `listeninghistory_ibfk_2` FOREIGN KEY (`SongID`) REFERENCES `songs` (`SongID`);

--
-- Constraints for table `playlists`
--
ALTER TABLE `playlists`
  ADD CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `playlistsongs`
--
ALTER TABLE `playlistsongs`
  ADD CONSTRAINT `playlistsongs_ibfk_1` FOREIGN KEY (`PlaylistID`) REFERENCES `playlists` (`PlaylistID`),
  ADD CONSTRAINT `playlistsongs_ibfk_2` FOREIGN KEY (`SongID`) REFERENCES `songs` (`SongID`);

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`AlbumID`) REFERENCES `albums` (`AlbumID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
