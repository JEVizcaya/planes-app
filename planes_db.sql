-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: planes_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `comentarios`
--

DROP TABLE IF EXISTS `comentarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios`
--

LOCK TABLES `comentarios` WRITE;
/*!40000 ALTER TABLE `comentarios` DISABLE KEYS */;
INSERT INTO `comentarios` VALUES (2,5,1,'de que hora estamos hablando','2025-05-09 02:06:25'),(3,5,2,'18:30 mas o menos','2025-05-09 02:07:15'),(4,1,4,'desde donde salimos','2025-05-09 02:39:34'),(5,1,1,'desde la catedral','2025-05-09 02:40:15'),(6,1,2,'perfecto','2025-05-09 02:40:40'),(7,1,1,'ok. nos vemos alli','2025-05-09 02:45:39'),(8,4,2,'me apunto','2025-05-09 23:08:07'),(9,4,5,'yo tambien. ????','2025-05-09 23:41:30'),(10,4,1,'????','2025-05-09 23:42:16'),(11,4,1,'?','2025-05-09 23:46:04');
/*!40000 ALTER TABLE `comentarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participantes`
--

DROP TABLE IF EXISTS `participantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`,`plan_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `participantes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `participantes_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantes`
--

LOCK TABLES `participantes` WRITE;
/*!40000 ALTER TABLE `participantes` DISABLE KEYS */;
INSERT INTO `participantes` VALUES (34,1,5),(31,2,4),(30,4,1),(32,5,4);
/*!40000 ALTER TABLE `participantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `planes`
--

DROP TABLE IF EXISTS `planes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `lugar` varchar(255) DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `creador_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `creador_id` (`creador_id`),
  CONSTRAINT `planes_ibfk_1` FOREIGN KEY (`creador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planes`
--

LOCK TABLES `planes` WRITE;
/*!40000 ALTER TABLE `planes` DISABLE KEYS */;
INSERT INTO `planes` VALUES (1,'Caminata por el Monte do Gozo','Acompáñanos a una caminata por el Monte do Gozo, una de las rutas más emblemáticas del Camino de Santiago. Será una oportunidad para disfrutar de la naturaleza, hacer ejercicio y compartir un día de buena compañía. Todos los niveles son bienvenidos.','2025-05-17','Santiago de Compostela',9,1),(2,'Tarde de juegos de mesa en el centro cultural','Ven a pasar una tarde divertida con juegos de mesa clásicos y modernos. Ideal para conocer gente nueva, relajarse y echar unas risas. ¡No hace falta traer nada, solo ganas de jugar!','2025-05-14','Calle Policarpo Sanz, 21 - 2ª. Vigo.',15,1),(3,'Picnic al atardecer en la playa de Samil','Organizamos un picnic para ver el atardecer en la playa de Samil. Cada persona puede llevar algo para compartir. Llévate tu toalla o manta, y prepárate para una tarde de desconexión junto al mar.','2025-05-17','Playa de Samil. Vigo',20,1),(4,'Ruta de tapas por el Casco Vello','Recorreremos algunos bares emblemáticos del Casco Vello para probar sus mejores tapas. Una buena forma de disfrutar la gastronomía local y hacer nuevos amigos en un ambiente relajado.','2025-05-18','Praza da iglesia, 3, Vigo, Pontevedra',8,1),(5,'Partido de fútbol entre amigos','¿Te apetece echar un partidillo de fútbol con buen ambiente? Hemos reservado el Campo de Fútbol Municipal Monte da Mina en Castrelos. No importa el nivel, solo las ganas de pasarlo bien. Después del partido, podemos ir a tomar algo por la zona.','2025-05-16','Camiño Galindra, s/n - Castrelos - Vigo',19,2),(11,'plan para expirar','se eliminara el domingo','2025-05-10','Vigo',1,1);
/*!40000 ALTER TABLE `planes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Jorge','Vizcaya','jorge@gmail.com','$2y$10$Fjdy2Fw/B7IF98vZd8s5QOPHO68xZhag0u12TPXANti5EgmAJoFtO'),(2,'Enrique','Vega','enrique@gmail.com','$2y$10$CRkm9XD7b03MKe.Kh9UwD.nY51fhZuwobLTDfd17RkAXxgnjrPixS'),(3,'Jorge Enrique','Vizcaya Vega','je@gmail.com','$2y$10$IzkJ6H5HRFTSm8TN2d94geeZW0WmZgq.upA9r7bniEsQpFh5ANtH2'),(4,'Pepe','Pérez','pepe@gmail.com','$2y$10$3GjGjVb595nO/KmJiqQAnetmOaXCj0R6PmZlhfkj3l9ljc5eQuZeG'),(5,'Sonia','Vizcaya','son@gmail.com','$2y$10$mVvUXkQTicIBkxNnw35jIOz2YvLAMfisJAfSyTk5M3GBr8xifgXIK');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-10  1:47:31
