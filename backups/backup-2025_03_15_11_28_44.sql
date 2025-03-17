-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: cartelera
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `anuncios_andes`
--

DROP TABLE IF EXISTS `anuncios_andes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anuncios_andes` (
  `nombre` text NOT NULL,
  `path` text NOT NULL,
  `congregacion` text NOT NULL,
  `tema` text NOT NULL,
  `fecha` text NOT NULL,
  `color` text NOT NULL,
  `dueño` text NOT NULL,
  `siblings` text NOT NULL,
  `siblingsCount` text NOT NULL,
  `siblingsPath` text NOT NULL,
  `id` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anuncios_andes`
--

LOCK TABLES `anuncios_andes` WRITE;
/*!40000 ALTER TABLE `anuncios_andes` DISABLE KEYS */;
INSERT INTO `anuncios_andes` VALUES ('Prueba Tabla de Colores - Reuniones entre Semana','images/andes/Reuniones_Entre_Semana_17.png','andes','reuniones_entre_semana','','#d2b4de','andes','0','0','','1906777824'),('Prueba de Tabla de colores - Acomodadores Y Microfonistas','images/andes/Acomodadores_Microfonistas.png','andes','acomodadores_microfonistas','','#85c1e9','andes','0','0','','1707011307'),('Prueba Tabla de Colores - Reunion de Fin de Semana','images/andes/Reuniones_Publicas.png','andes','reuniones_entre_semana','','#d2b4de','andes','0','0','','205582003'),('Prueba de Tabla de Colores - Audio y Video Plataforma ','images/andes/Audio_Video_Tablero.png','andes','audio_video_plataforma','','#e59866','andes','0','0','','892662710'),('Prueba Tabla de Colores - Mapa (Test)','images/andes/Mapa_Territorio_Anuncios.jpeg','andes','salidas','','#82e0aa','andes','0','0','','2057891321'),('Prueba TABLA DE COLORES - Grupos','images/andes/Grupos_Predicacion_Tablero.jpg','andes','grupos','','#daf7a6','andes','0','0','','1991556177'),('Prueba de Tabla de Color - Mapa','images/andes/Mapa_Territorio_Anuncios.jpeg','andes','salidas','','#82e0aa','andes','0','0','','502661757'),('PDF Siblings Test','images/andes/visma_google_azure-1.png','andes','salidas','','#82e0aa','andes','1','5','Array','2013763272'),('PDF Siblings Test','images/andes/visma_google_azure-1.png','andes','salidas','','#82e0aa','andes','1','5','images/andes/visma_google_azure-2.png','2142427062'),('PDF Siblings Test','images/andes/visma_google_azure-1.png','andes','salidas','','#82e0aa','andes','1','5','images/andes/visma_google_azure-1.png;images/andes/visma_google_azure-2.png;images/andes/visma_google_azure-3.png;images/andes/visma_google_azure-4.png;images/andes/visma_google_azure-5.png;','1654411162'),('Prueba PDf N Siblings','images/andes/Limpieza_Tablero.png','andes','salidas','','#82e0aa','andes','0','0','','1821048292'),('Prueba PDF Siblings^N ','images/andes/Manual de ciberseguridad-01.png','andes','acomodadores_microfonistas','','#85c1e9','andes','1','86','images/andes/Manual de ciberseguridad-01.png;images/andes/Manual de ciberseguridad-02.png;images/andes/Manual de ciberseguridad-03.png;images/andes/Manual de ciberseguridad-04.png;images/andes/Manual de ciberseguridad-05.png;images/andes/Manual de ciberseguridad-06.png;images/andes/Manual de ciberseguridad-07.png;images/andes/Manual de ciberseguridad-08.png;images/andes/Manual de ciberseguridad-09.png;images/andes/Manual de ciberseguridad-10.png;images/andes/Manual de ciberseguridad-11.png;images/andes/Manual de ciberseguridad-12.png;images/andes/Manual de ciberseguridad-13.png;images/andes/Manual de ciberseguridad-14.png;images/andes/Manual de ciberseguridad-15.png;images/andes/Manual de ciberseguridad-16.png;images/andes/Manual de ciberseguridad-17.png;images/andes/Manual de ciberseguridad-18.png;images/andes/Manual de ciberseguridad-19.png;images/andes/Manual de ciberseguridad-20.png;images/andes/Manual de ciberseguridad-21.png;images/andes/Manual de ciberseguridad-22.png;images/andes/Manual de ciberseguridad-23.png;images/andes/Manual de ciberseguridad-24.png;images/andes/Manual de ciberseguridad-25.png;images/andes/Manual de ciberseguridad-26.png;images/andes/Manual de ciberseguridad-27.png;images/andes/Manual de ciberseguridad-28.png;images/andes/Manual de ciberseguridad-29.png;images/andes/Manual de ciberseguridad-30.png;images/andes/Manual de ciberseguridad-31.png;images/andes/Manual de ciberseguridad-32.png;images/andes/Manual de ciberseguridad-33.png;images/andes/Manual de ciberseguridad-34.png;images/andes/Manual de ciberseguridad-35.png;images/andes/Manual de ciberseguridad-36.png;images/andes/Manual de ciberseguridad-37.png;images/andes/Manual de ciberseguridad-38.png;images/andes/Manual de ciberseguridad-39.png;images/andes/Manual de ciberseguridad-40.png;images/andes/Manual de ciberseguridad-41.png;images/andes/Manual de ciberseguridad-42.png;images/andes/Manual de ciberseguridad-43.png;images/andes/Manual de ciberseguridad-44.png;images/andes/Manual de ciberseguridad-45.png;images/andes/Manual de ciberseguridad-46.png;images/andes/Manual de ciberseguridad-47.png;images/andes/Manual de ciberseguridad-48.png;images/andes/Manual de ciberseguridad-49.png;images/andes/Manual de ciberseguridad-50.png;images/andes/Manual de ciberseguridad-51.png;images/andes/Manual de ciberseguridad-52.png;images/andes/Manual de ciberseguridad-53.png;images/andes/Manual de ciberseguridad-54.png;images/andes/Manual de ciberseguridad-55.png;images/andes/Manual de ciberseguridad-56.png;images/andes/Manual de ciberseguridad-57.png;images/andes/Manual de ciberseguridad-58.png;images/andes/Manual de ciberseguridad-59.png;images/andes/Manual de ciberseguridad-60.png;images/andes/Manual de ciberseguridad-61.png;images/andes/Manual de ciberseguridad-62.png;images/andes/Manual de ciberseguridad-63.png;images/andes/Manual de ciberseguridad-64.png;images/andes/Manual de ciberseguridad-65.png;images/andes/Manual de ciberseguridad-66.png;images/andes/Manual de ciberseguridad-67.png;images/andes/Manual de ciberseguridad-68.png;images/andes/Manual de ciberseguridad-69.png;images/andes/Manual de ciberseguridad-70.png;images/andes/Manual de ciberseguridad-71.png;images/andes/Manual de ciberseguridad-72.png;images/andes/Manual de ciberseguridad-73.png;images/andes/Manual de ciberseguridad-74.png;images/andes/Manual de ciberseguridad-75.png;images/andes/Manual de ciberseguridad-76.png;images/andes/Manual de ciberseguridad-77.png;images/andes/Manual de ciberseguridad-78.png;images/andes/Manual de ciberseguridad-79.png;images/andes/Manual de ciberseguridad-80.png;images/andes/Manual de ciberseguridad-81.png;images/andes/Manual de ciberseguridad-82.png;images/andes/Manual de ciberseguridad-83.png;images/andes/Manual de ciberseguridad-84.png;images/andes/Manual de ciberseguridad-85.png;images/andes/Manual de ciberseguridad-86.png;','1302347335'),('Prueba Titulo','images/andes/Audio_Video_Tablero.png','andes','salidas','','#82e0aa','andes','0','0','','351504173');
/*!40000 ALTER TABLE `anuncios_andes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `anuncios_liniers`
--

DROP TABLE IF EXISTS `anuncios_liniers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anuncios_liniers` (
  `nombre` text NOT NULL,
  `path` text NOT NULL,
  `congregacion` text NOT NULL,
  `tema` text NOT NULL,
  `fecha` text NOT NULL,
  `color` text NOT NULL,
  `dueño` text NOT NULL,
  `siblings` text NOT NULL,
  `siblingsCount` text NOT NULL,
  `siblingsPath` text NOT NULL,
  `id` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anuncios_liniers`
--

LOCK TABLES `anuncios_liniers` WRITE;
/*!40000 ALTER TABLE `anuncios_liniers` DISABLE KEYS */;
/*!40000 ALTER TABLE `anuncios_liniers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `name` text NOT NULL,
  `passwd` text NOT NULL,
  `congregacion` text NOT NULL,
  `rights` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES ('admin','password123','','admin'),('andes','andes','andes','0');
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

-- Dump completed on 2025-03-15 11:28:44
