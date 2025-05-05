-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : ven. 02 mai 2025 à 00:16
-- Version du serveur : 8.0.40
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ticket_233`
--

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `Id` int NOT NULL,
  `Ticket_id` int DEFAULT NULL,
  `Message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `Created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Updated_at` timestamp NULL DEFAULT NULL,
  `Updated_by` int DEFAULT NULL,
  `Created_by` int DEFAULT NULL,
  `Deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Permissions`
--

CREATE TABLE `Permissions` (
  `Id` int NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Status` varchar(1) NOT NULL DEFAULT 'N',
  `Created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Updated_at` timestamp NULL DEFAULT NULL,
  `Created_by` int DEFAULT NULL,
  `Updated_by` int DEFAULT NULL,
  `Deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Permissions`
--

INSERT INTO `Permissions` (`Id`, `Name`, `Status`, `Created_at`, `Updated_at`, `Created_by`, `Updated_by`, `Deleted_at`) VALUES
(1, 'View Tickets', 'Y', '2025-04-08 01:51:00', NULL, NULL, NULL, NULL),
(2, 'Create Tickets', 'Y', '2025-04-08 01:51:00', NULL, NULL, NULL, NULL),
(3, 'Edit Tickets', 'Y', '2025-04-08 01:51:00', NULL, NULL, NULL, NULL),
(4, 'Delete Tickets', 'Y', '2025-04-08 01:51:00', NULL, NULL, NULL, NULL),
(5, 'Manage Users', 'Y', '2025-04-08 01:51:00', NULL, NULL, NULL, NULL),
(6, 'Access Admin Panel', 'Y', '2025-04-08 01:51:00', NULL, NULL, NULL, NULL),
(7, 'Manage Roles', 'Y', '2025-04-08 01:51:00', NULL, NULL, NULL, NULL),
(8, 'Assign Permissions', 'Y', '2025-04-08 01:51:00', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `Permission_Roles`
--

CREATE TABLE `Permission_Roles` (
  `Id` int NOT NULL,
  `Role_id` int DEFAULT NULL,
  `Permission_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Permission_Roles`
--

INSERT INTO `Permission_Roles` (`Id`, `Role_id`, `Permission_id`) VALUES
(11, 1, 1),
(12, 1, 2),
(13, 1, 3),
(14, 1, 4),
(15, 1, 5),
(16, 1, 6),
(17, 1, 7),
(18, 1, 8),
(27, 3, 1),
(28, 3, 2),
(29, 3, 3),
(30, 3, 4),
(31, 3, 6),
(37, 4, 1),
(38, 4, 2),
(39, 4, 3),
(40, 2, 1),
(41, 2, 2);

-- --------------------------------------------------------

--
-- Structure de la table `Roles`
--

CREATE TABLE `Roles` (
  `Id` int NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Status` varchar(1) NOT NULL DEFAULT 'N',
  `Created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Updated_at` timestamp NULL DEFAULT NULL,
  `Created_by` int DEFAULT NULL,
  `Updated_by` int DEFAULT NULL,
  `Deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Roles`
--

INSERT INTO `Roles` (`Id`, `Name`, `Status`, `Created_at`, `Updated_at`, `Created_by`, `Updated_by`, `Deleted_at`) VALUES
(1, 'Admin', 'Y', '2025-04-07 07:57:22', NULL, NULL, NULL, NULL),
(2, 'Users', 'Y', '2025-04-07 08:26:02', NULL, NULL, NULL, NULL),
(3, 'Dev', 'Y', '2025-04-07 08:26:22', NULL, NULL, NULL, NULL),
(4, 'Helper', 'Y', '2025-04-07 08:26:41', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `Ticket`
--

CREATE TABLE `Ticket` (
  `Id` int NOT NULL,
  `Title` varchar(255) NOT NULL,
  `User_id` int DEFAULT NULL,
  `Created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Updated_at` timestamp NULL DEFAULT NULL,
  `Updated_by` int DEFAULT NULL,
  `Created_by` int DEFAULT NULL,
  `Deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE `Users` (
  `Id` int NOT NULL,
  `Role_id` int DEFAULT NULL,
  `Username` varchar(255) NOT NULL,
  `Firstname` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Status` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Y',
  `Created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `Deleted_at` timestamp NULL DEFAULT NULL,
  `Updated_at` timestamp NULL DEFAULT NULL,
  `Created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `Users`
--

INSERT INTO `Users` (`Id`, `Role_id`, `Username`, `Firstname`, `Password`, `mail`, `Image`, `Status`, `Created_at`, `Deleted_at`, `Updated_at`, `Created_by`) VALUES
(1, 1, 'nico', 'nico', '$2y$10$aDRmRyyK.qMu2s.UMSj21OfcXHrO1JQpQwPwmWSQ.UGpoGq/KK8s.', 'nico281107@gmail.com', '680d9584b53da.jpg', 'Y', '2025-04-07 18:24:21', NULL, NULL, NULL),
(3, 4, 'maxence', 'maxence', '$2y$10$D5r8O2v3v3wazrr.xN0/futbOYo2WUhq6GCdbSrBvEMNOwWZej9yq', 'maxence@gmail.com', NULL, 'Y', '2025-04-07 19:08:42', NULL, NULL, NULL),
(5, 2, 'max', 'max', '$2y$10$Edyr0m2Q1xZLyx6jHGIaruV.UQdu7ZbxCwctLPbLLCUo1lXEQNxPi', 'max@gmail.com', NULL, 'N', '2025-04-07 23:40:26', NULL, NULL, NULL),
(7, 2, 'jenni12', 'Jennifer Singh', '$2y$10$yW7ppCz2xKpuETbitPDjTehEXwBREg.3voX8jzpExkY3y4G4Nfxwu', 'jenniraj12@hotmail.com', NULL, 'Y', '2025-04-18 09:16:34', NULL, NULL, NULL),
(8, 3, 'adrien', 'adrien', '$2y$10$Ma0euB6Nku6gNoYFQpxe/.EwluAcjUXrp9v/Hup3UMEmwl6t.jVfC', 'adrien@gmail.com', '680b42597d7e6.jpg', 'Y', '2025-04-18 12:20:31', NULL, NULL, NULL),
(9, 2, 'jean', 'jean', '$2y$10$30jolXMeID76yK030o2D.eML8uJ5MH..7gstLxcjbteB3DZ5FaVT2', 'jean@gmail.com', '680a23f977e07.jpg', 'Y', '2025-04-18 12:54:23', NULL, NULL, NULL),
(10, 2, 'nico7600', 'nico7600', '$2y$10$enTlyrvU.yPM/82bOTrr9eAoAU9U67mkDutQeGV/ATSq8.0AozbXm', 'nico7600@gmail.com', NULL, 'Y', '2025-04-24 11:46:53', NULL, NULL, NULL),
(11, 2, 'Shanni', 'Shanni', '$2y$10$Qx.jsUGnYG/pBPEOuBT.VuShp1iBMJ1lW4BQHq4H.Fni2QgpsHuOG', 'Shanni@gmail.com', NULL, 'Y', '2025-04-24 11:48:51', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `User_Settings`
--

CREATE TABLE `User_Settings` (
  `user_id` int NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `User_Settings`
--

INSERT INTO `User_Settings` (`user_id`, `setting_key`, `setting_value`) VALUES
(1, 'language', 'fr'),
(1, 'theme', 'dark'),
(3, 'language', 'en'),
(8, 'language', 'fr'),
(8, 'theme', 'light'),
(9, 'language', 'fr'),
(9, 'theme', 'light'),
(10, 'language', 'fr'),
(10, 'theme', 'light'),
(11, 'language', 'fr'),
(11, 'theme', 'light');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Created_by` (`Created_by`),
  ADD KEY `messages_ibfk_1` (`Ticket_id`);

--
-- Index pour la table `Permissions`
--
ALTER TABLE `Permissions`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Index pour la table `Permission_Roles`
--
ALTER TABLE `Permission_Roles`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Role_id` (`Role_id`),
  ADD KEY `Permission_id` (`Permission_id`);

--
-- Index pour la table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Index pour la table `Ticket`
--
ALTER TABLE `Ticket`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `User_id` (`User_id`),
  ADD KEY `Created_by` (`Created_by`);

--
-- Index pour la table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Role_id` (`Role_id`);

--
-- Index pour la table `User_Settings`
--
ALTER TABLE `User_Settings`
  ADD PRIMARY KEY (`user_id`,`setting_key`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT pour la table `Permissions`
--
ALTER TABLE `Permissions`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `Permission_Roles`
--
ALTER TABLE `Permission_Roles`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pour la table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `Ticket`
--
ALTER TABLE `Ticket`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pour la table `Users`
--
ALTER TABLE `Users`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`Ticket_id`) REFERENCES `ticket` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`Created_by`) REFERENCES `Users` (`Id`);

--
-- Contraintes pour la table `Permission_Roles`
--
ALTER TABLE `Permission_Roles`
  ADD CONSTRAINT `permission_roles_ibfk_1` FOREIGN KEY (`Role_id`) REFERENCES `Roles` (`Id`),
  ADD CONSTRAINT `permission_roles_ibfk_2` FOREIGN KEY (`Permission_id`) REFERENCES `Permissions` (`Id`);

--
-- Contraintes pour la table `Ticket`
--
ALTER TABLE `Ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `Users` (`Id`);

--
-- Contraintes pour la table `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`Role_id`) REFERENCES `Roles` (`Id`);

--
-- Contraintes pour la table `User_Settings`
--
ALTER TABLE `User_Settings`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
