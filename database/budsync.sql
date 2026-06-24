-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 03:29 PM
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
-- Database: `budsync`
--

-- --------------------------------------------------------

--
-- Table structure for table `alertes`
--

CREATE TABLE `alertes` (
  `id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('seuil','depassement') NOT NULL,
  `seuil_pourcentage` int(11) DEFAULT 80,
  `message` text DEFAULT NULL,
  `lue` tinyint(1) DEFAULT 0,
  `date_alerte` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alertes`
--

INSERT INTO `alertes` (`id`, `budget_id`, `user_id`, `type`, `seuil_pourcentage`, `message`, `lue`, `date_alerte`) VALUES
(1, 6, 2, 'depassement', 100, 'Le budget \"Budget Avril 2025\" a dépassé son plafond de 1 500 DT.', 1, '2025-04-27 20:00:00'),
(2, 1, 2, 'seuil', 80, 'Vous avez atteint 80 % du plafond de \"Budget Mai 2025\".', 0, '2025-05-22 17:30:00'),
(3, 3, 4, 'seuil', 85, 'Le budget partagé \"Coloc Juin 2025\" approche de son plafond (85 %).', 0, '2025-06-06 11:00:00'),
(4, 3, 2, 'seuil', 85, 'Le budget partagé \"Coloc Juin 2025\" approche de son plafond (85 %).', 0, '2025-06-06 11:00:00'),
(5, 2, 3, 'depassement', 100, 'Le budget \"Semaine 20\" a dépassé son plafond de 500 DT.', 1, '2025-05-17 22:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('individuel','partage') DEFAULT 'individuel',
  `periode` enum('mensuel','hebdomadaire','personnalise') DEFAULT 'mensuel',
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `plafond_global` decimal(10,2) DEFAULT NULL,
  `seuil_alerte` int(11) DEFAULT 80,
  `proprietaire_id` int(11) NOT NULL,
  `statut` enum('actif','depasse','proche_limite','expire') DEFAULT 'actif',
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `nom`, `description`, `type`, `periode`, `date_debut`, `date_fin`, `plafond_global`, `seuil_alerte`, `proprietaire_id`, `statut`, `date_creation`) VALUES
(1, 'Budget Mai 2025', 'Dépenses personnelles de mai', 'individuel', 'mensuel', '2026-05-01', '2026-05-31', 2000.00, 80, 2, 'expire', '2026-06-05 17:38:21'),
(2, 'Semaine 20', 'Budget semaine du 12 mai', 'individuel', 'hebdomadaire', '2026-05-12', '2026-05-18', 500.00, 75, 3, 'expire', '2026-06-05 17:38:21'),
(3, 'Coloc Juin 2025', 'Charges partagées appartement', 'partage', 'mensuel', '2026-06-01', '2026-06-30', 1500.00, 85, 4, 'expire', '2026-06-05 17:38:21'),
(5, 'Budget Juin 2025', 'Mois de juin Karim', 'individuel', 'mensuel', '2026-06-01', '2026-06-30', 1800.00, 80, 3, 'actif', '2026-06-05 17:38:21'),
(6, 'Budget Avril 2025', 'Dépenses avril – dépassé', 'individuel', 'mensuel', '2026-04-01', '2026-04-30', 1500.00, 80, 2, 'depasse', '2026-06-05 17:38:21'),
(7, 'test', 'soutenance', 'partage', 'hebdomadaire', '2026-06-06', '2026-06-30', 2000.00, 80, 7, 'actif', '2026-06-06 12:04:46'),
(8, 'hfhfh', 'jjfjfj', 'partage', 'hebdomadaire', '2026-06-01', '2026-06-30', 2000.00, 80, 2, 'actif', '2026-06-06 12:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `budget_membres`
--

CREATE TABLE `budget_membres` (
  `id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('proprietaire','membre') DEFAULT 'membre',
  `date_ajout` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_membres`
--

INSERT INTO `budget_membres` (`id`, `budget_id`, `user_id`, `role`, `date_ajout`) VALUES
(1, 1, 2, 'proprietaire', '2026-06-05 17:38:21'),
(2, 2, 3, 'proprietaire', '2026-06-05 17:38:21'),
(3, 3, 4, 'proprietaire', '2026-06-05 17:38:21'),
(4, 3, 2, 'membre', '2026-06-05 17:38:21'),
(6, 5, 3, 'proprietaire', '2026-06-05 17:38:21'),
(7, 6, 2, 'proprietaire', '2026-06-05 17:38:21'),
(8, 7, 7, 'proprietaire', '2026-06-06 12:04:46'),
(9, 8, 2, 'proprietaire', '2026-06-06 12:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `budget_plafonds`
--

CREATE TABLE `budget_plafonds` (
  `id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `plafond` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_plafonds`
--

INSERT INTO `budget_plafonds` (`id`, `budget_id`, `categorie_id`, `plafond`) VALUES
(1, 1, 1, 400.00),
(2, 1, 2, 150.00),
(3, 1, 5, 200.00),
(4, 1, 11, 120.00),
(5, 1, 12, 80.00),
(6, 3, 1, 600.00),
(7, 3, 3, 750.00),
(8, 3, 2, 150.00),
(13, 5, 1, 350.00),
(14, 5, 2, 200.00),
(15, 5, 14, 150.00),
(16, 5, 15, 200.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT 'bi-tag',
  `couleur` varchar(20) DEFAULT '#6366f1',
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `icone`, `couleur`, `user_id`) VALUES
(1, 'Alimentation', 'bi-basket', '#f59e0b', NULL),
(2, 'Transport', 'bi-car-front', '#3b82f6', NULL),
(3, 'Logement', 'bi-house', '#8b5cf6', NULL),
(4, 'Santé', 'bi-heart-pulse', '#ef4444', NULL),
(5, 'Loisirs', 'bi-controller', '#10b981', NULL),
(6, 'Études', 'bi-mortarboard', '#6366f1', NULL),
(7, 'Vêtements', 'bi-bag', '#ec4899', NULL),
(8, 'Épargne', 'bi-piggy-bank', '#14b8a6', NULL),
(9, 'Salaire', 'bi-cash-coin', '#22c55e', NULL),
(10, 'Autres', 'bi-three-dots', '#94a3b8', NULL),
(11, 'Café & Resto', 'bi-cup-hot', '#f97316', 2),
(12, 'Abonnements', 'bi-credit-card-2', '#8b5cf6', 2),
(13, 'Sport', 'bi-bicycle', '#10b981', 2),
(14, 'Courses en ligne', 'bi-cart3', '#3b82f6', 3),
(15, 'Carburant', 'bi-fuel-pump', '#f59e0b', 3),
(16, 'Électronique', 'bi-laptop', '#6366f1', 3),
(17, 'Beauté & Soins', 'bi-stars', '#ec4899', 4),
(18, 'Livres', 'bi-book', '#14b8a6', 4),
(19, 'Voyages', 'bi-airplane', '#0ea5e9', 4),
(22, 'Animaux', 'bi-paw', '#f59e0b', 6),
(23, 'Décoration', 'bi-lamp', '#10b981', 6);

-- --------------------------------------------------------

--
-- Table structure for table `demandes_suppression`
--

CREATE TABLE `demandes_suppression` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `motif` text DEFAULT NULL,
  `nom_utilisateur` varchar(100) DEFAULT NULL,
  `prenom_utilisateur` varchar(100) DEFAULT NULL,
  `statut` enum('en_attente','validee','refusee') DEFAULT 'en_attente',
  `date_demande` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demandes_suppression`
--

INSERT INTO `demandes_suppression` (`id`, `user_id`, `motif`, `nom_utilisateur`, `prenom_utilisateur`, `statut`, `date_demande`) VALUES
(1, 6, 'Je souhaite supprimer mon compte car je n\'utilise plus ce service.', 'Martin', 'Clara', 'en_attente', '2025-05-15 10:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE `invitations` (
  `id` int(11) NOT NULL,
  `budget_id` int(11) NOT NULL,
  `invite_par` int(11) NOT NULL,
  `email_invite` varchar(150) NOT NULL,
  `statut` enum('en_attente','accepte','refuse') DEFAULT 'en_attente',
  `token` varchar(64) DEFAULT NULL,
  `date_invitation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invitations`
--

INSERT INTO `invitations` (`id`, `budget_id`, `invite_par`, `email_invite`, `statut`, `token`, `date_invitation`) VALUES
(1, 3, 4, 'karim.benali@mail.com', 'refuse', '4ccbcc032a7deeda9736a1557f1aba3ebc269ca38187ac275ed6d56dc59417b9', '2025-05-28 10:00:00'),
(2, 3, 4, 'clara.martin@mail.com', 'en_attente', '3d450ee07d47fa5c9784482ce3f2017c8146214492ce78619d89a57ca2f8e55b', '2025-05-30 11:30:00'),
(3, 5, 3, 'yassine.amrani@mail.com', 'accepte', '2ce19610ff16aa78b6d0bbb8b54a77b012aac142bc3b24316da0ee62aa53e0ce', '2025-05-31 09:15:00'),
(4, 1, 2, 'sophie.lefebvre@mail.com', 'en_attente', '0e288e6e4b766e89a869a70afe61cd649343508bcfaab75f33c05db6364306de', '2025-06-01 14:00:00'),
(5, 7, 7, 'marie.dupont@mail.com', 'accepte', 'e575e917c626735fb2f046e1c5923d582a30f30671549c41fcebd9520bd2d4db', '2026-06-06 12:14:47'),
(6, 6, 2, 'ihebn91@gmail.com', 'en_attente', '65a97413b7f6232a0398daec3d9c7e682457ea246d5bbf3195347e6a7a16f001', '2026-06-06 12:17:56');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `lue` tinyint(1) DEFAULT 0,
  `date_notif` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `lue`, `date_notif`) VALUES
(2, 2, 'Sophie Lefebvre vous a ajouté au budget partagé \"Coloc Juin 2025\".', 0, '2025-05-31 12:00:00'),
(3, 2, 'Rappel : votre budget \"Budget Mai 2025\" expire dans 5 jours.', 1, '2025-05-26 09:00:00'),
(4, 3, 'Votre invitation sur le budget \"Coloc Juin 2025\" a été refusée.', 1, '2025-05-29 08:30:00'),
(5, 3, 'Yassine Amrani a accepté votre invitation sur \"Budget Juin 2025\".', 0, '2025-06-01 10:00:00'),
(6, 4, 'Bienvenue ! Votre compte a été activé avec succès.', 1, '2025-01-03 11:05:00'),
(7, 4, 'Clara Martin a refusé votre invitation sur \"Coloc Juin 2025\".', 0, '2025-06-01 15:00:00'),
(10, 6, 'Votre compte a été bloqué. Contactez le support pour plus d\'informations.', 0, '2025-04-11 09:00:00'),
(11, 7, 'Votre compte a été activé et validé par l\'administrateur. Vous pouvez maintenant vous connecter et gérer vos budgets.', 0, '2026-06-06 12:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `budget_id` int(11) DEFAULT NULL,
  `categorie_id` int(11) DEFAULT NULL,
  `type` enum('revenu','depense') NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_transaction` date NOT NULL,
  `commentaire` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `budget_id`, `categorie_id`, `type`, `montant`, `description`, `date_transaction`, `commentaire`, `date_creation`) VALUES
(1, 2, 1, 9, 'revenu', 2400.00, 'Salaire mai 2025', '2026-05-01', 'Virement employeur', '2026-06-05 17:38:21'),
(2, 2, 1, 1, 'depense', 320.50, 'Supermarché Carrefour', '2026-05-03', NULL, '2026-06-05 17:38:21'),
(3, 2, 1, 2, 'depense', 55.00, 'Abonnement métro mai', '2026-05-05', NULL, '2026-06-05 17:38:21'),
(4, 2, 1, 11, 'depense', 43.80, 'Café avec amies', '2026-05-08', 'Petit déj + lunch', '2026-06-05 17:38:21'),
(5, 2, 1, 12, 'depense', 14.99, 'Netflix', '2026-05-09', NULL, '2026-06-05 17:38:21'),
(6, 2, 1, 12, 'depense', 9.99, 'Spotify', '2026-05-09', NULL, '2026-06-05 17:38:21'),
(7, 2, 1, 5, 'depense', 80.00, 'Concert jazz', '2026-05-15', NULL, '2026-06-05 17:38:21'),
(8, 2, 1, 13, 'depense', 35.00, 'Cours de yoga', '2026-05-17', NULL, '2026-06-05 17:38:21'),
(9, 2, 1, 1, 'depense', 95.60, 'Marché bio', '2026-05-20', NULL, '2026-06-05 17:38:21'),
(10, 2, 1, 8, 'depense', 200.00, 'Épargne mensuelle', '2026-05-25', 'Virement livret A', '2026-06-05 17:38:21'),
(11, 2, 6, 9, 'revenu', 2400.00, 'Salaire avril 2025', '2026-04-01', NULL, '2026-06-05 17:38:21'),
(12, 2, 6, 1, 'depense', 510.00, 'Courses alimentaires', '2026-04-05', 'Dépassement prévu', '2026-06-05 17:38:21'),
(13, 2, 6, 7, 'depense', 320.00, 'Shopping printemps', '2026-04-12', NULL, '2026-06-05 17:38:21'),
(14, 2, 6, 3, 'depense', 700.00, 'Loyer avril', '2026-04-01', NULL, '2026-06-05 17:38:21'),
(15, 2, 6, 2, 'depense', 130.00, 'Taxi + métro', '2026-04-20', NULL, '2026-06-05 17:38:21'),
(16, 3, 2, 9, 'revenu', 450.00, 'Salaire semaine partiel', '2026-05-12', NULL, '2026-06-05 17:38:21'),
(17, 3, 2, 1, 'depense', 87.30, 'Épicerie semaine', '2026-05-13', NULL, '2026-06-05 17:38:21'),
(18, 3, 2, 15, 'depense', 65.00, 'Plein carburant', '2026-05-14', 'Route Tunis-Sfax', '2026-06-05 17:38:21'),
(19, 3, 2, 14, 'depense', 48.99, 'Amazon commande', '2026-05-15', NULL, '2026-06-05 17:38:21'),
(20, 3, 2, 5, 'depense', 30.00, 'Cinéma × 2', '2026-05-17', NULL, '2026-06-05 17:38:21'),
(21, 3, 5, 9, 'revenu', 1900.00, 'Salaire juin 2025', '2026-06-01', NULL, '2026-06-05 17:38:21'),
(22, 3, 5, 1, 'depense', 145.00, 'Supermarché', '2026-06-03', NULL, '2026-06-05 17:38:21'),
(23, 3, 5, 15, 'depense', 70.00, 'Carburant', '2026-06-05', NULL, '2026-06-05 17:38:21'),
(24, 3, 5, 14, 'depense', 112.50, 'Aliexpress gadgets', '2026-06-07', NULL, '2026-06-05 17:38:21'),
(25, 4, 3, 3, 'depense', 750.00, 'Loyer juin', '2026-06-01', 'Part Sophie', '2026-06-05 17:38:21'),
(26, 4, 3, 1, 'depense', 210.00, 'Courses communes', '2026-06-04', NULL, '2026-06-05 17:38:21'),
(27, 4, 3, 17, 'depense', 85.00, 'Beauté – pharmacie', '2026-06-06', NULL, '2026-06-05 17:38:21'),
(28, 4, 3, 9, 'revenu', 2100.00, 'Salaire juin Sophie', '2026-06-01', NULL, '2026-06-05 17:38:21'),
(29, 4, 3, 18, 'depense', 45.00, 'Romans achetés', '2026-06-08', NULL, '2026-06-05 17:38:21'),
(30, 2, 3, 1, 'depense', 195.00, 'Courses coloc semaine 1', '2026-06-02', NULL, '2026-06-05 17:38:21'),
(31, 2, 3, 2, 'depense', 55.00, 'Abonnement transport', '2026-06-05', NULL, '2026-06-05 17:38:21'),
(37, 2, NULL, 4, 'depense', 120.00, 'Médecin spécialiste', '2026-05-22', NULL, '2026-06-05 17:38:21'),
(38, 3, NULL, 6, 'depense', 75.00, 'Livres universitaires', '2026-05-25', NULL, '2026-06-05 17:38:21'),
(39, 4, NULL, 9, 'revenu', 500.00, 'Freelance mission web', '2026-06-10', NULL, '2026-06-05 17:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','utilisateur') DEFAULT 'utilisateur',
  `statut` enum('actif','en_attente','bloque','suspendu') DEFAULT 'en_attente',
  `date_inscription` datetime DEFAULT current_timestamp(),
  `derniere_connexion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `statut`, `date_inscription`, `derniere_connexion`) VALUES
(1, '', 'Admin', 'admin@budget.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif', '2026-06-05 17:37:05', '2026-06-06 11:59:51'),
(2, 'Dupont', 'Marie', 'marie.dupont@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilisateur', 'actif', '2024-10-05 09:15:00', '2026-06-06 12:15:34'),
(3, 'Benali', 'Karim', 'karim.benali@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilisateur', 'actif', '2024-11-12 14:22:00', '2025-05-21 10:05:00'),
(4, 'Lefebvre', 'Sophie', 'sophie.lefebvre@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilisateur', 'actif', '2025-01-03 11:00:00', '2025-05-19 17:45:00'),
(6, 'Martin', 'Clara', 'clara.martin@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilisateur', 'bloque', '2024-09-25 08:00:00', '2025-04-10 09:20:00'),
(7, 'Iheb', 'Test', 'ihebn91@gmail.com', '$2y$10$wPkvY.oUTQ1F7FD7xnAJ3O0B5pJACtGsy3bZh6ChOJ7JFPUahdftq', 'utilisateur', 'actif', '2026-06-06 11:58:57', '2026-06-06 12:02:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alertes`
--
ALTER TABLE `alertes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_id` (`budget_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proprietaire_id` (`proprietaire_id`);

--
-- Indexes for table `budget_membres`
--
ALTER TABLE `budget_membres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_membre` (`budget_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `budget_plafonds`
--
ALTER TABLE `budget_plafonds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_plafond` (`budget_id`,`categorie_id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `demandes_suppression`
--
ALTER TABLE `demandes_suppression`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `budget_id` (`budget_id`),
  ADD KEY `invite_par` (`invite_par`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `budget_id` (`budget_id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alertes`
--
ALTER TABLE `alertes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `budget_membres`
--
ALTER TABLE `budget_membres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `budget_plafonds`
--
ALTER TABLE `budget_plafonds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `demandes_suppression`
--
ALTER TABLE `demandes_suppression`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alertes`
--
ALTER TABLE `alertes`
  ADD CONSTRAINT `alertes_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`proprietaire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_membres`
--
ALTER TABLE `budget_membres`
  ADD CONSTRAINT `budget_membres_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_membres_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_plafonds`
--
ALTER TABLE `budget_plafonds`
  ADD CONSTRAINT `budget_plafonds_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_plafonds_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demandes_suppression`
--
ALTER TABLE `demandes_suppression`
  ADD CONSTRAINT `demandes_suppression_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`invite_par`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
