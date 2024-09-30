-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_library_management
DROP DATABASE IF EXISTS `db_library_management`;
CREATE DATABASE IF NOT EXISTS `db_library_management` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_library_management`;

-- Dumping structure for table db_library_management.tb_authors
DROP TABLE IF EXISTS `tb_authors`;
CREATE TABLE IF NOT EXISTS `tb_authors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `bio` text COLLATE utf8mb4_general_ci,
  `birth_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_library_management.tb_authors: ~5 rows (approximately)
INSERT INTO `tb_authors` (`id`, `name`, `bio`, `birth_date`) VALUES
	(1, 'Robby Suryanata', 'Robby is a passionate software engineer with over 5 years of experience in building scalable web applications. When not coding, Robby loves exploring the great outdoors and capturing nature\'s beauty through the lens of a camera. Always curious and constantly learning, Robby enjoys staying up-to-date with the latest technology trends and working on personal development projects.', '2024-09-28'),
	(2, 'Mia Roberts', 'Mia is a creative graphic designer with a flair for bringing ideas to life through visual design. With a strong background in branding and digital media, she enjoys creating designs that make an impact. In her free time, Mia can be found exploring the city by bike, sketching in local parks, or diving into a good book. She is always looking for inspiration and new ways to express her creativity.', '2022-05-24'),
	(3, 'Jordan Taylor', 'Jordan is a dynamic marketing specialist with a knack for developing compelling campaigns that connect with audiences. With a keen interest in digital marketing and analytics, Jordan has helped multiple brands grow their presence online. When not working, you can find Jordan experimenting with new recipes, strumming tunes on the guitar, or planning the next travel adventure to explore different cultures and cuisines.', '2015-10-07'),
	(4, 'Samira Patel', 'Samira is a detail-oriented data analyst with a passion for turning complex datasets into actionable insights. With expertise in data visualization and predictive modeling, she has worked with companies to streamline their decision-making processes. Outside of work, Samira enjoys unwinding through yoga, expressing her creativity with painting, and challenging her mind with various puzzles and brain games.', '1995-02-14'),
	(6, 'Lucas Henderson', 'Lucas is a mechanical engineer with a love for solving complex problems and designing innovative mechanical systems. With a strong background in robotics and renewable energy, Lucas enjoys working on projects that contribute to sustainability. When not working, he’s usually out on the trails mountain biking, crafting furniture in his home workshop, or enjoying the night sky through his telescope.', '1997-12-31');

-- Dumping structure for table db_library_management.tb_books
DROP TABLE IF EXISTS `tb_books`;
CREATE TABLE IF NOT EXISTS `tb_books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `publish_date` date DEFAULT NULL,
  `author_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_tb_books_tb_authors` (`author_id`),
  CONSTRAINT `FK_tb_books_tb_authors` FOREIGN KEY (`author_id`) REFERENCES `tb_authors` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_library_management.tb_books: ~7 rows (approximately)
INSERT INTO `tb_books` (`id`, `title`, `description`, `publish_date`, `author_id`) VALUES
	(1, 'The Alchemist', 'A philosophical story about a young Andalusian shepherd named Santiago who sets off on a journey to discover a hidden treasure near the pyramids of Egypt. Along the way, he learns about the importance of following one\'s dreams, listening to one\'s heart, and discovering the true meaning of life.\n\n', '2024-09-28', 3),
	(2, 'To Kill a Mockingbird', 'Set in the racially charged atmosphere of 1930s Alabama, this novel explores the moral nature of human beings through the eyes of a young girl, Scout Finch. Her father, Atticus Finch, defends a black man falsely accused of raping a white woman, unraveling the harsh realities of racism and injustice.', '2013-06-01', 2),
	(3, '1984', 'A dystopian novel that explores the dangers of totalitarianism. In a world where Big Brother watches every move, Winston Smith dares to defy the oppressive regime, fighting for truth and individuality in a society where freedom of thought is suppressed.', '2018-11-17', 1),
	(4, 'Pride and Prejudice', 'This classic novel delves into the life of Elizabeth Bennet, one of five daughters, as she navigates issues of manners, upbringing, morality, education, and marriage in early 19th-century England. Her tumultuous relationship with the proud Mr. Darcy forms the heart of the story, revealing the nuances of human relationships.', '2007-03-14', 4),
	(5, 'Sapiens: A Brief History of Humankind', 'This non-fiction book takes readers on a journey through the history of humanity, from the emergence of Homo sapiens in the Stone Age to the political and technological revolutions of the 21st century. Harari explores how biology, culture, and technology have shaped human societies and the world as we know it.', '2020-05-28', 1),
	(6, 'The Catcher in the Rye', 'This iconic novel captures the teenage angst and alienation of Holden Caulfield, a young boy expelled from his prestigious boarding school. As he wanders New York City, Holden grapples with feelings of loneliness, confusion, and a desire to protect the innocence of childhood.', '1983-07-29', 3),
	(7, 'The Road', 'A post-apocalyptic novel that follows a father and his young son as they travel through a desolate, ash-covered landscape in search of safety. Facing starvation, harsh conditions, and bands of cannibals, the bond between father and son is tested in this bleak yet powerful story of survival and love.', '2004-12-14', 1),
	(9, 'Educated', 'A memoir about Tara Westover, who was born into a strict and abusive survivalist family in rural Idaho. With no formal education, she self-taught herself enough to attend college and eventually earned a Ph.D. from Cambridge University. It’s a story of resilience, self-discovery, and the power of education.', '1997-12-31', 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
