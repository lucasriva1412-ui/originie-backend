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
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contatos`
--

DROP TABLE IF EXISTS `contatos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contatos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `servico` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mensagem` text COLLATE utf8mb4_general_ci NOT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pendente',
  PRIMARY KEY (`id`),
  KEY `fk_contatos_cliente` (`id_cliente`),
  CONSTRAINT `fk_contatos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projetos_finalizados`
--

DROP TABLE IF EXISTS `projetos_finalizados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projetos_finalizados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_contato` int NOT NULL,
  `valor_final` decimal(10,2) NOT NULL,
  `data_conclusao` date NOT NULL,
  `observacao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_projetos_contato` (`id_contato`),
  CONSTRAINT `fk_projetos_contato` FOREIGN KEY (`id_contato`) REFERENCES `contatos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servicos_cat`
--

DROP TABLE IF EXISTS `servicos_cat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicos_cat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_general_ci NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `categoria` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `video` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
  `id_servico` int NOT NULL,
  PRIMARY KEY (`id_contato`,`id_servico`),
  KEY `fk_interesse_servico` (`id_servico`),
  CONSTRAINT `fk_interesse_contato` FOREIGN KEY (`id_contato`) REFERENCES `contatos` (`id`),
  CONSTRAINT `fk_interesse_servico` FOREIGN KEY (`id_servico`) REFERENCES `servicos_cat` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `cpf` char(11) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('usuario','admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'usuario',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  UNIQUE KEY `uq_usuarios_cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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

-- Dump completed on 2026-10-28 23:48:23
