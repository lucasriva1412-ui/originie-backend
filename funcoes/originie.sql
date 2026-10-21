-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14/09/2026 às 03:00
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `originie`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_indicadores_dashboard` (IN `p_busca` VARCHAR(100), IN `p_categoria` VARCHAR(100), IN `p_ativo` INT, IN `p_limite` INT, IN `p_offset` INT)   BEGIN

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


    /* =====================================================
       RESULTADO 1 - INDICADORES GERAIS
       ===================================================== */

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


    /* =====================================================
       RESULTADO 2 - SERVIÇOS FILTRADOS E PAGINADOS
       ===================================================== */

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


    /* =====================================================
       RESULTADO 3 - TOTAL APÓS OS FILTROS
       ===================================================== */

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

END$$

--
-- Funções
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_calcular_valor_adicional` (`p_valor` DECIMAL(10,2), `p_percentual` DECIMAL(5,2)) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN

    DECLARE v_resultado DECIMAL(10,2);

    SET v_resultado =
        p_valor + (p_valor * p_percentual / 100);

    RETURN v_resultado;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `administradores`
--

INSERT INTO `administradores` (`id`, `nome`, `email`, `senha`, `criado_em`) VALUES
(1, 'Administrador', 'admin@originie.com', 'originie2024', '2026-08-13 00:17:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `email`, `telefone`) VALUES
(1, 'Lucas Riva Teixeira', 'lucasriva1412@gmail.com', '(44) 99967-6138'),
(2, 'Zenaide Riva', 'zenaideriba@gmail.com', '(44) 99967-6138'),
(3, 'Pedro', 'pedro123@gmail.com', '(44) 9879-8578'),
(4, 'Jair da Silva Pinto', 'pinto.grande123@gmail.com', '(44) 4002-8922');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contatos`
--

CREATE TABLE `contatos` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `servico` varchar(100) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'Pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `contatos`
--

INSERT INTO `contatos` (`id`, `id_cliente`, `servico`, `mensagem`, `criado_em`, `status`) VALUES
(8, 1, 'Clip Musical', 'AAAAAAAAAAAAAAAAAAa', '2026-09-01 20:44:04', 'Concluído'),
(9, 4, 'Vídeo de Inauguração', 'Ola quero fazer um Video de inauguração da minha empresa rolax', '2026-09-01 20:45:58', 'Concluído'),
(10, 1, 'Casamento', 'aaaaaaaaaaaaaaaaaaaaaaaa', '2026-09-13 20:04:19', 'Concluído');

-- --------------------------------------------------------

--
-- Estrutura para tabela `projetos_finalizados`
--

CREATE TABLE `projetos_finalizados` (
  `id` int(11) NOT NULL,
  `id_contato` int(11) NOT NULL,
  `valor_final` decimal(10,2) NOT NULL,
  `data_conclusao` date NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `projetos_finalizados`
--

INSERT INTO `projetos_finalizados` (`id`, `id_contato`, `valor_final`, `data_conclusao`, `observacao`, `criado_em`) VALUES
(1, 9, 5000.00, '2026-09-02', '', '2026-09-01 23:46:26'),
(2, 8, 3500.00, '2026-09-02', '', '2026-09-01 23:46:44'),
(3, 10, 15000.00, '2026-09-05', 'entegue e recebido o pagamento', '2026-09-13 23:05:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_cat`
--

CREATE TABLE `servicos_cat` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descricao` text NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `video` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `servicos_cat`
--

INSERT INTO `servicos_cat` (`id`, `nome`, `slug`, `descricao`, `preco`, `categoria`, `video`, `ativo`) VALUES
(1, 'Resumo de Evento', 'resumo-evento', 'Transformamos eventos em narrativas dinâmicas e memoráveis.', 1000.00, 'evento', 'videos/RESUMO_EVENTO.mp4', 1),
(2, 'VT Comercial', 'vt-comercial', 'Criamos vídeos comerciais que unem estratégia, estética e posicionamento.', 1200.00, 'comercial', 'videos/VT_COMERCIAL.mp4', 1),
(3, 'Highlights', 'highlights', 'Os melhores momentos reunidos em uma edição intensa.', 600.00, 'evento', 'videos/HIGHLIGHTS.mp4', 1),
(4, 'Vídeo de Inauguração', 'inauguracao', 'Produzimos vídeos que capturam a atmosfera do evento.', 1000.00, 'comercial', 'videos/INAUGURACAO.mp4', 1),
(5, 'Same Day Edit', 'same-day-edit', 'Produção, edição e entrega no mesmo dia.', 1500.00, 'evento', 'videos/SAME_DAY_EDIT.mp4', 1),
(6, 'Reels para Instagram', 'reels', 'Conteúdo estratégico para Instagram.', 400.00, 'social', 'videos/REELS.mp4', 1),
(7, 'Clip Musical', 'clip-musical', 'Produções visuais com identidade artística.', 2000.53, 'artistico', 'videos/CLIP_MUSICAL.mp4', 1),
(8, 'Documentário Empresarial', 'documentario', 'Histórias reais contadas através de narrativa cinematográfica.', 3000.00, 'comercial', 'videos/DOCUMENTARIO.mp4', 1);

--
-- Acionadores `servicos_cat`
--
DELIMITER $$
CREATE TRIGGER `trg_servico_preco_positivo` BEFORE UPDATE ON `servicos_cat` FOR EACH ROW BEGIN

    IF NEW.preco < 0 THEN
        SET NEW.preco = ABS(NEW.preco);
    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_interesse`
--

CREATE TABLE `servicos_interesse` (
  `id_contato` int(11) NOT NULL,
  `id_servico` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `criado_em`) VALUES
(1, 'Administrador', 'admin@originie.com', '$2y$10$awRq2H6haYb9RxTvrafUjOFfvo.SJ/XdXaYarCJHZO/kjOsMenRra', 'admin', '2026-08-17 23:14:00'),
(2, 'Lucas Teixeira', 'lucasriva719@gmail.com', '$2y$10$32VSQ0plCxCvw.bHNuddsefhrhGRWi7mu9Gv4q.DaQXm1mxB1vsE2', 'usuario', '2026-08-17 23:20:43'),
(3, 'Lucas Riva', 'lucasriva1412@gmail.com', '$2y$10$AfER43Go1P340TveyF7fk.2SGdXez7W0nW5/.dwPGcqhft6UF6hIe', 'usuario', '2026-08-20 00:53:12');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_servicos_dashboard`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_servicos_dashboard` (
`id` int(11)
,`nome` varchar(100)
,`categoria` varchar(50)
,`preco` decimal(10,2)
,`ativo` tinyint(1)
,`total_interesses` bigint(21)
,`total_orcamentos` bigint(21)
,`total_clientes` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_servicos_dashboard`
--
DROP TABLE IF EXISTS `vw_servicos_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_servicos_dashboard`  AS WITH dados_servicos AS (SELECT `s`.`id` AS `id`, `s`.`nome` AS `nome`, `s`.`categoria` AS `categoria`, `s`.`preco` AS `preco`, `s`.`ativo` AS `ativo`, count(distinct `si`.`id_contato`) AS `total_interesses`, count(distinct `c`.`id`) AS `total_orcamentos`, count(distinct `cli`.`id`) AS `total_clientes` FROM (((`servicos_cat` `s` left join `servicos_interesse` `si` on(`si`.`id_servico` = `s`.`id`)) left join `contatos` `c` on(`c`.`id` = `si`.`id_contato`)) left join `clientes` `cli` on(`cli`.`id` = `c`.`id_cliente`)) GROUP BY `s`.`id`, `s`.`nome`, `s`.`categoria`, `s`.`preco`, `s`.`ativo`) SELECT `dados_servicos`.`id` AS `id`, `dados_servicos`.`nome` AS `nome`, `dados_servicos`.`categoria` AS `categoria`, `dados_servicos`.`preco` AS `preco`, `dados_servicos`.`ativo` AS `ativo`, `dados_servicos`.`total_interesses` AS `total_interesses`, `dados_servicos`.`total_orcamentos` AS `total_orcamentos`, `dados_servicos`.`total_clientes` AS `total_clientes` FROM `dados_servicos``dados_servicos`  ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `contatos`
--
ALTER TABLE `contatos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices de tabela `projetos_finalizados`
--
ALTER TABLE `projetos_finalizados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_projeto_contato` (`id_contato`);

--
-- Índices de tabela `servicos_cat`
--
ALTER TABLE `servicos_cat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `servicos_interesse`
--
ALTER TABLE `servicos_interesse`
  ADD PRIMARY KEY (`id_contato`,`id_servico`),
  ADD KEY `id_servico` (`id_servico`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `contatos`
--
ALTER TABLE `contatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `projetos_finalizados`
--
ALTER TABLE `projetos_finalizados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `servicos_cat`
--
ALTER TABLE `servicos_cat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `contatos`
--
ALTER TABLE `contatos`
  ADD CONSTRAINT `contatos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`);

--
-- Restrições para tabelas `projetos_finalizados`
--
ALTER TABLE `projetos_finalizados`
  ADD CONSTRAINT `fk_projeto_contato` FOREIGN KEY (`id_contato`) REFERENCES `contatos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `servicos_interesse`
--
ALTER TABLE `servicos_interesse`
  ADD CONSTRAINT `servicos_interesse_ibfk_1` FOREIGN KEY (`id_contato`) REFERENCES `contatos` (`id`),
  ADD CONSTRAINT `servicos_interesse_ibfk_2` FOREIGN KEY (`id_servico`) REFERENCES `servicos_cat` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
