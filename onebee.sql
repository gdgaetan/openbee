-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mar 25 Avril 2017 à 08:59
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `onebee`
--

CREATE DATABASE IF NOT EXISTS `comptage_video`;

USE comptage_video;

-- --------------------------------------------------------

--
-- Structure de la table `abeille`
--

CREATE TABLE IF NOT EXISTS `abeille` (
  `idAbeille` int(11) NOT NULL AUTO_INCREMENT,
  `dateEnregistrement` datetime NOT NULL,
  `nbEntreesAbeille` int(11) NOT NULL,
  `nbSortiesAbeille` int(11) NOT NULL,
  `NBEFauxBourdon` int(11) NOT NULL,
  `NBSFauxBourdon` int(11) NOT NULL,
  `NBEAbeillePollen` int(11) NOT NULL,
  `compteurEntree` bigint(20) NOT NULL,
  `compteurSortie` bigint(20) NOT NULL,
  PRIMARY KEY (`idAbeille`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

--
-- Structure de la table `compteur`
--

CREATE TABLE IF NOT EXISTS `compteur` (
  `idCompteur` int(11) NOT NULL,
  `inTotal` bigint(20) NOT NULL,
  `outTotal` bigint(20) NOT NULL,
  PRIMARY KEY (`idCompteur`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, FILE, INDEX, ALTER, CREATE TEMPORARY TABLES, EXECUTE, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER ON *.* TO 'onebee'@'localhost';

INSERT INTO `compteur` (`idCompteur`, `inTotal`, `outTotal`) VALUES
(1, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
