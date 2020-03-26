-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 26. Mrz 2020 um 22:34
-- Server-Version: 10.4.11-MariaDB
-- PHP-Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `nachbarschaftssystem`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `email` varchar(256) NOT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `password` varchar(128) NOT NULL,
  `vorname` varchar(128) NOT NULL,
  `nachname` varchar(128) NOT NULL,
  `optin` tinyint(1) NOT NULL,
  `token` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hilfsangebote`
--

CREATE TABLE `hilfsangebote` (
  `id` int(11) NOT NULL,
  `what` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `plz` int(11) NOT NULL,
  `ref_account` int(11) NOT NULL,
  `montag` tinyint(1) NOT NULL,
  `dienstag` tinyint(1) NOT NULL,
  `mittwoch` tinyint(1) NOT NULL,
  `donnerstag` tinyint(1) NOT NULL,
  `freitag` tinyint(1) NOT NULL,
  `samstag` tinyint(1) NOT NULL,
  `sonntag` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `plz_centers`
--

CREATE TABLE `plz_centers` (
  `id` int(11) NOT NULL,
  `plz` int(11) NOT NULL,
  `center` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `plz_polygons`
--

CREATE TABLE `plz_polygons` (
  `id` int(11) NOT NULL,
  `plz` int(11) NOT NULL,
  `polygon` text NOT NULL,
  `center` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pwreset`
--

CREATE TABLE `pwreset` (
  `id` int(11) NOT NULL,
  `ref_account` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `hilfsangebote`
--
ALTER TABLE `hilfsangebote`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `plz_centers`
--
ALTER TABLE `plz_centers`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `plz_polygons`
--
ALTER TABLE `plz_polygons`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `pwreset`
--
ALTER TABLE `pwreset`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hilfsangebote`
--
ALTER TABLE `hilfsangebote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `plz_centers`
--
ALTER TABLE `plz_centers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `plz_polygons`
--
ALTER TABLE `plz_polygons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `pwreset`
--
ALTER TABLE `pwreset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
