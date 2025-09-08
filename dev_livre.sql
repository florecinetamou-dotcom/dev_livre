-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 08 sep. 2025 à 09:28
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dev_livre`
--

-- --------------------------------------------------------

--
-- Structure de la table `activite_utilisateur`
--

DROP TABLE IF EXISTS `activite_utilisateur`;
CREATE TABLE IF NOT EXISTS `activite_utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id_avis` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `id_livre` int NOT NULL,
  `note` int DEFAULT NULL,
  `commentaire` text,
  `date_avis` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_avis`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_livre` (`id_livre`)
) ;

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `nom_categorie` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id_categorie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `date_commande` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('en attente','payée','expédiée','livrée','annulée') DEFAULT 'en attente',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_commande`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commande_detail`
--

DROP TABLE IF EXISTS `commande_detail`;
CREATE TABLE IF NOT EXISTS `commande_detail` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `id_livre` int NOT NULL,
  `quantite` int NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_commande` (`id_commande`),
  KEY `id_livre` (`id_livre`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ligne_commande`
--

DROP TABLE IF EXISTS `ligne_commande`;
CREATE TABLE IF NOT EXISTS `ligne_commande` (
  `id_ligne` int NOT NULL AUTO_INCREMENT,
  `id_commande` int DEFAULT NULL,
  `id_produit` int DEFAULT NULL,
  `quantite` int DEFAULT NULL,
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_ligne`),
  KEY `id_commande` (`id_commande`),
  KEY `id_produit` (`id_produit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `sujet` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `nom`, `email`, `sujet`, `message`, `date_envoi`) VALUES
(1, 'Florecine Tamou', 'tamouflorecine@gmail.com', 'Remerciement', '                  Merci', '2025-09-04 13:06:13'),
(2, 'JOHNSON Percy', 'percy@gmail.com', 'Remerciement', 'Thanks my brother                  ', '2025-09-04 15:32:22'),
(4, 'Florecine Tamou', 'florecinetamou@gmail.com', 'Remerciement', '                  hy\r\n', '2025-09-04 15:36:52');

-- --------------------------------------------------------

--
-- Structure de la table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `date_abonnement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `newsletter`
--

INSERT INTO `newsletter` (`id`, `email`, `date_abonnement`) VALUES
(1, 'florecinetamou@gmail.com', '2025-08-29 16:11:47'),
(2, 'tamouflorecine@gmail.com', '2025-08-29 16:21:14'),
(5, 'poocy@gmail.com', '2025-08-29 16:34:59'),
(6, 'trito@gmail.com', '2025-08-29 16:35:28'),
(7, 'toto@gmail.com', '2025-09-03 11:10:34'),
(8, 'percy@gmail.com', '2025-09-03 11:13:57');

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

DROP TABLE IF EXISTS `panier`;
CREATE TABLE IF NOT EXISTS `panier` (
  `id_panier` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `id_produits` int NOT NULL,
  `quantite` int DEFAULT '1',
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_panier`),
  KEY `fk_panier_utilisateur` (`id_utilisateur`),
  KEY `fk_panier_produits` (`id_produits`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `panier`
--

INSERT INTO `panier` (`id_panier`, `id_utilisateur`, `id_produits`, `quantite`, `date_ajout`) VALUES
(18, 10, 10, 2, '2025-09-07 03:20:48');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

DROP TABLE IF EXISTS `produits`;
CREATE TABLE IF NOT EXISTS `produits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `auteur` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `prix` decimal(8,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `stock` int DEFAULT '0',
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  `est_numerique` tinyint(1) DEFAULT '0',
  `fichier` varchar(255) DEFAULT NULL,
  `taille_fichier` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `titre`, `auteur`, `description`, `prix`, `image`, `stock`, `date_ajout`, `est_numerique`, `fichier`, `taille_fichier`) VALUES
(5, 'La Force de la Persévérance', 'Florecine TAMOU', 'HHHUUIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIHHHHHHHHHH', 1200.00, 'uploads/livres/68af9f74d2708_WhatsApp Image 2025-06-28 à 09.58.19_ca5477ee.jpg', 0, '2025-08-28 01:14:44', 0, NULL, NULL),
(6, 'Se construire chaque jour', 'Florecine TAMOU', 'BIEN', 1000.00, 'uploads/livres/68afa03078bb7_WhatsApp Image 2025-07-07 à 10.56.11_1d06c849.jpg', 0, '2025-08-28 01:17:52', 0, NULL, NULL),
(7, 'La confiance en soi, clé de la réussite', 'Florecine TAMOU', 'HYUU', 1200.00, 'uploads/livres/68afa099c3fca_téléchargement (19).jpeg', 0, '2025-08-28 01:19:37', 0, NULL, NULL),
(8, 'Apprendre à gérer ses émotions', 'JOHNSON Percy', 'Controler ses émotions', 2000.00, 'uploads/livres/68b5cef30d63b_téléchargement (18).jpeg', 0, '2025-09-01 17:50:59', 0, NULL, NULL),
(10, 'la confiance en soi', 'Florecine TAMOU', 'La confiance en soi est la capacité à croire en ses compétences, en ses qualités et en sa valeur personnelle. Elle ne signifie pas que l’on est parfait ou que l’on réussit toujours, mais qu’on ose essayer, persévérer et apprendre de ses erreurs.\r\n\r\n✅ Origine : Elle se construit dès l’enfance à travers l’éducation, les expériences et les réussites. Mais elle peut aussi se développer à tout âge par un travail sur soi.\r\n\r\n✅ Importance : Elle aide à prendre des décisions, à s’affirmer, à surmonter les échecs et à saisir les opportunités.\r\n\r\n✅ Bienfaits : Moins de peur du jugement, plus de motivation, meilleure estime de soi, relations plus équilibrées.\r\n\r\n✅ Clés pour l’améliorer :\r\n\r\nReconnaître ses forces et ses réussites.\r\n\r\nFixer de petits objectifs réalistes et les atteindre.\r\n\r\nSortir progressivement de sa zone de confort.\r\n\r\nS’entourer de personnes positives.\r\n\r\nRemplacer l’autocritique par un dialogue intérieur encourageant.', 1000.00, 'uploads/livres/68b9c0c61d936_WhatsApp Image 2025-07-07 à 10.56.11_1d06c849.jpg', 0, '2025-09-04 17:39:34', 1, 'uploads/livres/fichiers/68b9c0c61dc41_ebook.pdf', 474909);

-- --------------------------------------------------------

--
-- Structure de la table `telechargements`
--

DROP TABLE IF EXISTS `telechargements`;
CREATE TABLE IF NOT EXISTS `telechargements` (
  `id_telechargement` int NOT NULL AUTO_INCREMENT,
  `id_commande` int DEFAULT NULL,
  `id_ligne_commande` int DEFAULT NULL,
  `id_utilisateur` int DEFAULT NULL,
  `id_produit` int DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_expiration` datetime DEFAULT NULL,
  `nombre_telechargements` int DEFAULT '0',
  PRIMARY KEY (`id_telechargement`),
  UNIQUE KEY `token` (`token`),
  KEY `id_commande` (`id_commande`),
  KEY `id_ligne_commande` (`id_ligne_commande`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_produit` (`id_produit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` datetime DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `telephone`, `password`, `date_inscription`, `derniere_connexion`, `role`, `actif`) VALUES
(1, 'Tamou', 'Florecine', 'florecinetamou@gmail.com', NULL, '$2y$10$r9.YCr4otqYZIOE.4sSqM.5jIN0FCNjAeuRk.Smv5C5aHoGYIUmzi', '2025-08-25 11:56:44', NULL, 'user', 1),
(9, 'Système', 'Admin', 'admin@inspilivres.com', NULL, '$2y$10$aUIIzVsL2MRRqcVdW9ACSO9PQCv/zTblG3pl7MEXyaIa5LA9qmSH2', '2025-08-27 11:13:55', NULL, 'admin', 1),
(10, 'Percy', 'JOHNSON', 'percy@gmail.com', '0152963478', '$2y$10$F.e9W24GB.78YBJFqd3Ct.ZrcQPe6K6hvzcEvGkg9Dp8PGUXX/sBK', '2025-09-04 14:13:50', NULL, 'user', 1);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `activite_utilisateur`
--
ALTER TABLE `activite_utilisateur`
  ADD CONSTRAINT `activite_utilisateur_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `fk_panier_produits` FOREIGN KEY (`id_produits`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_panier_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
