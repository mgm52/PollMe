SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `poll_app` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `poll_app`;

CREATE TABLE `polls` (
  `poll_id` int(11) NOT NULL,
  `poll_id_secret` varchar(127) NOT NULL,
  `question` varchar(255) NOT NULL,
  `owner_ip` int(11) NOT NULL,
  `owner_session_id` varchar(127) NOT NULL,
  `strict_voting` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `polls_responses` (
  `poll_id` int(11) NOT NULL,
  `response` varchar(255) NOT NULL,
  `response_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `poll_owners_users` (
  `poll_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pword` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `votes` (
  `response_id` int(11) NOT NULL,
  `ip` int(11) NOT NULL,
  `session_id` varchar(127) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `words` (
  `word_id` int(11) NOT NULL,
  `word` varchar(63) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `polls`
  ADD PRIMARY KEY (`poll_id`),
  ADD KEY `poll_id_secret` (`poll_id_secret`);

ALTER TABLE `polls_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `polls_responses_pfk` (`poll_id`);

ALTER TABLE `poll_owners_users`
  ADD PRIMARY KEY (`user_id`,`poll_id`),
  ADD KEY `users_polls_pfk` (`poll_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `votes`
  ADD PRIMARY KEY (`response_id`,`ip`,`session_id`) USING BTREE;

ALTER TABLE `words`
  ADD PRIMARY KEY (`word_id`);

ALTER TABLE `polls`
  MODIFY `poll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

ALTER TABLE `polls_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `words`
  MODIFY `word_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8533;

ALTER TABLE `polls_responses`
  ADD CONSTRAINT `polls_responses_pfk` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`poll_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `poll_owners_users`
  ADD CONSTRAINT `users_polls_pfk` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`poll_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_polls_ufk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `votes`
  ADD CONSTRAINT `responses_votes_rfk` FOREIGN KEY (`response_id`) REFERENCES `polls_responses` (`response_id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
