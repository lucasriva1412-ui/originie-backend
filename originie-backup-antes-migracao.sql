-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: originie
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `administradores`
--

DROP TABLE IF EXISTS `administradores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administradores` (
  `id` int NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administradores`
--

LOCK TABLES `administradores` WRITE;
/*!40000 ALTER TABLE `administradores` DISABLE KEYS */;
INSERT INTO `administradores` VALUES (1,'Administrador','admin@originie.com','originie2024','2026-08-13 00:17:22');
/*!40000 ALTER TABLE `administradores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Lucas Riva Teixeira','lucasriva1412@gmail.com','(44) 99967-6138'),(2,'Zenaide Riva','zenaideriba@gmail.com','(44) 99967-6138'),(3,'Pedro','pedro123@gmail.com','(44) 9879-8578'),(4,'Jair da Silva Pinto','pinto.grande123@gmail.com','(44) 4002-8922');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contatos`
--

DROP TABLE IF EXISTS `contatos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contatos` (
  `id` int NOT NULL,
  `id_cliente` int NOT NULL,
  `servico` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mensagem` text COLLATE utf8mb4_general_ci NOT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contatos`
--

LOCK TABLES `contatos` WRITE;
/*!40000 ALTER TABLE `contatos` DISABLE KEYS */;
INSERT INTO `contatos` VALUES (8,1,'Clip Musical','AAAAAAAAAAAAAAAAAAa','2026-09-01 20:44:04','Conclu├¡do'),(9,4,'V├¡deo de Inaugura├º├úo','Ola quero fazer um Video de inaugura├º├úo da minha empresa rolax','2026-09-01 20:45:58','Conclu├¡do'),(10,1,'Casamento','aaaaaaaaaaaaaaaaaaaaaaaa','2026-09-13 20:04:19','Conclu├¡do');
/*!40000 ALTER TABLE `contatos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projetos_finalizados`
--

DROP TABLE IF EXISTS `projetos_finalizados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projetos_finalizados` (
  `id` int NOT NULL,
  `id_contato` int NOT NULL,
  `valor_final` decimal(10,2) NOT NULL,
  `data_conclusao` date NOT NULL,
  `observacao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projetos_finalizados`
--

LOCK TABLES `projetos_finalizados` WRITE;
/*!40000 ALTER TABLE `projetos_finalizados` DISABLE KEYS */;
INSERT INTO `projetos_finalizados` VALUES (1,9,5000.00,'2026-09-02','','2026-09-01 23:46:26'),(2,8,3500.00,'2026-09-02','','2026-09-01 23:46:44'),(3,10,15000.00,'2026-09-05','entegue e recebido o pagamento','2026-09-13 23:05:29');
/*!40000 ALTER TABLE `projetos_finalizados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicos_cat`
--

DROP TABLE IF EXISTS `servicos_cat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicos_cat` (
  `id` int NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_general_ci NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `categoria` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `video` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicos_cat`
--

LOCK TABLES `servicos_cat` WRITE;
/*!40000 ALTER TABLE `servicos_cat` DISABLE KEYS */;
INSERT INTO `servicos_cat` VALUES (1,'Resumo de Evento','resumo-evento','Transformamos eventos em narrativas din├ómicas e memor├íveis.',1000.00,'evento','videos/RESUMO_EVENTO.mp4',1),(2,'VT Comercial','vt-comercial','Criamos v├¡deos comerciais que unem estrat├®gia, est├®tica e posicionamento.',1200.00,'comercial','videos/VT_COMERCIAL.mp4',1),(3,'Highlights','highlights','Os melhores momentos reunidos em uma edi├º├úo intensa.',600.00,'evento','videos/HIGHLIGHTS.mp4',1),(4,'V├¡deo de Inaugura├º├úo','inauguracao','Produzimos v├¡deos que capturam a atmosfera do evento.',1000.00,'comercial','videos/INAUGURACAO.mp4',1),(5,'Same Day Edit','same-day-edit','Produ├º├úo, edi├º├úo e entrega no mesmo dia.',1500.00,'evento','videos/SAME_DAY_EDIT.mp4',1),(6,'Reels para Instagram','reels','Conte├║do estrat├®gico para Instagram.',400.00,'social','videos/REELS.mp4',1),(7,'Clip Musical','clip-musical','Produ├º├Áes visuais com identidade art├¡stica.',2000.53,'artistico','videos/CLIP_MUSICAL.mp4',1),(8,'Document├írio Empresarial','documentario','Hist├│rias reais contadas atrav├®s de narrativa cinematogr├ífica.',3000.00,'comercial','videos/DOCUMENTARIO.mp4',1);
/*!40000 ALTER TABLE `servicos_cat` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_servico_preco_positivo` BEFORE UPDATE ON `servicos_cat` FOR EACH ROW BEGIN

    IF NEW.preco < 0 THEN
        SET NEW.preco = ABS(NEW.preco);
    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `servicos_interesse`
--

DROP TABLE IF EXISTS `servicos_interesse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicos_interesse` (
  `id_contato` int NOT NULL,
  `id_servico` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicos_interesse`
--

LOCK TABLES `servicos_interesse` WRITE;
/*!40000 ALTER TABLE `servicos_interesse` DISABLE KEYS */;
/*!40000 ALTER TABLE `servicos_interesse` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('usuario','admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'usuario',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','admin@originie.com','$2y$10$awRq2H6haYb9RxTvrafUjOFfvo.SJ/XdXaYarCJHZO/kjOsMenRra','admin','2026-08-17 23:14:00'),(2,'Lucas Teixeira','lucasriva719@gmail.com','$2y$10$32VSQ0plCxCvw.bHNuddsefhrhGRWi7mu9Gv4q.DaQXm1mxB1vsE2','usuario','2026-08-17 23:20:43'),(3,'Lucas Riva','lucasriva1412@gmail.com','$2y$10$AfER43Go1P340TveyF7fk.2SGdXez7W0nW5/.dwPGcqhft6UF6hIe','usuario','2026-08-20 00:53:12');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `vw_servicos_dashboard`
--

DROP TABLE IF EXISTS `vw_servicos_dashboard`;
/*!50001 DROP VIEW IF EXISTS `vw_servicos_dashboard`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_servicos_dashboard` AS SELECT 
 1 AS `id`,
 1 AS `nome`,
 1 AS `categoria`,
 1 AS `preco`,
 1 AS `ativo`,
 1 AS `total_interesses`,
 1 AS `total_orcamentos`,
 1 AS `total_clientes`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'originie'
--
/*!50003 DROP FUNCTION IF EXISTS `fn_calcular_valor_adicional` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_calcular_valor_adicional`(`p_valor` DECIMAL(10,2), `p_percentual` DECIMAL(5,2)) RETURNS decimal(10,2)
    DETERMINISTIC
BEGIN

    DECLARE v_resultado DECIMAL(10,2);

    SET v_resultado =
        p_valor + (p_valor * p_percentual / 100);

    RETURN v_resultado;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_indicadores_dashboard` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_indicadores_dashboard`(IN `p_busca` VARCHAR(100), IN `p_categoria` VARCHAR(100), IN `p_ativo` INT, IN `p_limite` INT, IN `p_offset` INT)
BEGIN

    DECLARE v_busca VARCHAR(100);
    DECLARE v_categoria VARCHAR(100);
    DECLARE v_limite INT DEFAULT 10;
    DECLARE v_offset INT DEFAULT 0;

    SET v_busca = TRIM(IFNULL(p_busca, ''));
    SET v_categoria = TRIM(IFNULL(p_categoria, ''));

    SET v_limite =
        CASE
            WHEN p_limite IS NULL OR p_limite <= 0 THEN 10
            WHEN p_limite > 100 THEN 100
            ELSE p_limite
        END;

    SET v_offset =
        CASE
            WHEN p_offset IS NULL OR p_offset < 0 THEN 0
            ELSE p_offset
        END;


    

    SELECT
        COUNT(*) AS total_servicos,

        COALESCE(
            SUM(
                CASE
                    WHEN ativo = 1 THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS servicos_ativos,

        COALESCE(
            AVG(preco),
            0
        ) AS media_preco,

        (
            SELECT COUNT(*)
            FROM contatos
            WHERE LOWER(TRIM(status)) = 'pendente'
        ) AS orcamentos_pendentes

    FROM servicos_cat;


    

    SELECT
        id,
        nome,
        categoria,
        preco,
        ativo,
        total_interesses,
        total_orcamentos,
        total_clientes

    FROM vw_servicos_dashboard

    WHERE
        (
            v_busca = ''
            OR nome LIKE CONCAT('%', v_busca, '%')
        )

        AND

        (
            v_categoria = ''
            OR categoria = v_categoria
        )

        AND

        (
            p_ativo IS NULL
            OR ativo = p_ativo
        )

    ORDER BY
        total_interesses DESC,
        nome ASC

    LIMIT v_limite
    OFFSET v_offset;


    

    SELECT
        COUNT(*) AS total_filtrado

    FROM vw_servicos_dashboard

    WHERE
        (
            v_busca = ''
            OR nome LIKE CONCAT('%', v_busca, '%')
        )

        AND

        (
            v_categoria = ''
            OR categoria = v_categoria
        )

        AND

        (
            p_ativo IS NULL
            OR ativo = p_ativo
        );

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `vw_servicos_dashboard`
--

/*!50001 DROP VIEW IF EXISTS `vw_servicos_dashboard`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_servicos_dashboard` AS select `s`.`id` AS `id`,`s`.`nome` AS `nome`,`s`.`categoria` AS `categoria`,`s`.`preco` AS `preco`,`s`.`ativo` AS `ativo`,count(distinct `si`.`id_contato`) AS `total_interesses`,count(distinct `c`.`id`) AS `total_orcamentos`,count(distinct `cli`.`id`) AS `total_clientes` from (((`servicos_cat` `s` left join `servicos_interesse` `si` on((`si`.`id_servico` = `s`.`id`))) left join `contatos` `c` on((`c`.`id` = `si`.`id_contato`))) left join `clientes` `cli` on((`cli`.`id` = `c`.`id_cliente`))) group by `s`.`id`,`s`.`nome`,`s`.`categoria`,`s`.`preco`,`s`.`ativo` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-21  1:31:19
