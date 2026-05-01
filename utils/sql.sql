

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `classe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
CREATE TABLE `aniversariantes_mes` (
`id` int(11)
,`nome` varchar(100)
,`data_nascimento` date
,`telefone` varchar(15)
,`classe_id` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamadas`
--

CREATE TABLE `chamadas` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `classe_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `oferta_classe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_biblias` int(10) NOT NULL,
  `total_revistas` int(10) NOT NULL,
  `total_visitantes` int(10) NOT NULL,
  `trimestre` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1=1º Trimestre, 2=2º Trimestre, 3=3º Trimestre, 4=4º Trimestre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `chamadas`
--

INSERT INTO `chamadas` (`id`, `data`, `classe_id`, `professor_id`, `criado_em`, `oferta_classe`, `total_biblias`, `total_revistas`, `total_visitantes`, `trimestre`) VALUES
(45, '2025-04-06', 8, 7, '2025-04-06 13:25:04', 7.00, 2, 13, 17, 2),
(46, '2025-04-06', 10, 7, '2025-04-06 13:35:35', 3.00, 4, 4, 5, 2),
(47, '2025-04-06', 6, 7, '2025-04-06 13:38:54', 0.00, 10, 10, 10, 2),
(48, '2025-04-06', 9, 7, '2025-04-06 13:41:20', 3.50, 2, 3, 5, 2),
(49, '2025-04-06', 7, 7, '2025-04-06 13:53:10', 35.00, 17, 43, 17, 2),
(62, '2025-04-13', 10, 7, '2025-04-13 15:41:47', 2.00, 6, 7, 2, 2),
(63, '2025-04-13', 8, 7, '2025-04-13 15:45:07', 12.00, 14, 12, 1, 2),
(64, '2025-04-13', 6, 7, '2025-04-13 15:47:37', 4.00, 7, 9, 0, 2),
(65, '2025-04-13', 7, 7, '2025-04-13 15:54:42', 31.80, 28, 27, 12, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `classes`
--

INSERT INTO `classes` (`id`, `nome`, `created_at`) VALUES
(6, 'ADOLESCENTES', '2025-02-21 14:47:42'),
(7, 'ADULTOS', '2025-02-22 11:45:17'),
(8, 'JOVENS', '2025-02-22 11:51:06'),
(9, 'JUNIORES', '2025-03-09 14:09:05'),
(10, 'JARDIM DA INFÂNCIA', '2025-03-09 14:09:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `congregacoes`
--

CREATE TABLE `congregacoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `congregacoes`
--

INSERT INTO `congregacoes` (`id`, `nome`, `created_at`) VALUES
(7, 'SEDE', '2025-02-20 15:52:53');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `tabela_afetada` varchar(100) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `congregacao_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_matricula` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('ativo','concluido','cancelado') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ativo',
  `trimestre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `matriculas` (`id`, `aluno_id`, `classe_id`, `congregacao_id`, `usuario_id`, `data_matricula`, `status`, `trimestre`) VALUES
(23, 1527, 7, 7, 27, '2025-03-17 03:00:00', 'ativo', 2),
(24, 23, 7, 7, 27, '2025-03-17 03:00:00', 'ativo', 2),
(25, 25, 7, 7, 27, '2025-03-17 03:00:00', 'ativo', 2),
(26, 1518, 7, 7, 27, '2025-03-17 03:00:00', 'ativo', 2),
(27, 1517, 7, 7, 27, '2025-03-17 03:00:00', 'ativo', 2),
(28, 49, 7, 7, 27, '2025-03-17 03:00:00', 'ativo', 2),
(29, 56, 7, 7, 27, '2025-03-17 03:00:00', 'ativo', 2),
(30, 29, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(31, 1, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(32, 2, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(33, 4, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(34, 3, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(35, 5, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(36, 6, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(37, 7, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(38, 8, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(39, 9, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(40, 10, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(41, 11, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(42, 12, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(43, 13, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(44, 14, 6, 7, 28, '2025-03-18 03:00:00', 'ativo', 2),
(45, 24, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(46, 26, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(47, 27, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(48, 28, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(49, 30, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(50, 31, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(51, 32, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(52, 33, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(53, 34, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(54, 35, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(55, 36, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(56, 37, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(57, 38, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(58, 39, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(59, 40, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(60, 41, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(61, 42, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(62, 43, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(63, 44, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(64, 45, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(65, 46, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(66, 47, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(67, 48, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(68, 50, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(69, 51, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(70, 52, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(71, 53, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(72, 54, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(73, 55, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(74, 57, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(75, 58, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(76, 59, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(77, 60, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(78, 61, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(79, 62, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(80, 63, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(81, 64, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(82, 65, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(83, 66, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(84, 67, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(85, 68, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(86, 70, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(87, 71, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(88, 72, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(89, 73, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(90, 74, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(91, 75, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(92, 76, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(93, 77, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(94, 78, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(95, 79, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(96, 80, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(97, 81, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(98, 82, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(99, 83, 8, 7, 21, '2025-03-18 03:00:00', 'ativo', 2),
(100, 84, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(101, 87, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(102, 90, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(103, 91, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(104, 92, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(105, 93, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(106, 94, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(107, 95, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(108, 96, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(109, 1519, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(110, 1520, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(111, 1521, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(112, 1523, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(113, 1524, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(114, 1525, 7, 7, 27, '2025-03-18 03:00:00', 'ativo', 2),
(115, 1548, 9, 7, 29, '2025-03-22 03:00:00', 'ativo', 2),
(116, 1547, 9, 7, 29, '2025-03-22 03:00:00', 'ativo', 2),
(117, 1546, 9, 7, 29, '2025-03-22 03:00:00', 'ativo', 2),
(118, 1545, 9, 7, 29, '2025-03-22 03:00:00', 'ativo', 2),
(119, 1544, 8, 7, 21, '2025-03-22 03:00:00', 'ativo', 2),
(120, 1543, 10, 7, 30, '2025-03-22 03:00:00', 'ativo', 2),
(121, 1528, 6, 7, 28, '2025-03-22 03:00:00', 'ativo', 2),
(122, 1529, 7, 7, 27, '2025-03-22 03:00:00', 'ativo', 2),
(123, 1530, 7, 7, 27, '2025-03-22 03:00:00', 'ativo', 2),
(124, 1531, 7, 7, 27, '2025-03-22 03:00:00', 'ativo', 2),
(125, 1532, 7, 7, 27, '2025-03-22 03:00:00', 'ativo', 2),
(126, 1533, 7, 7, 27, '2025-03-22 03:00:00', 'ativo', 2),
(127, 1534, 7, 7, 27, '2025-03-22 03:00:00', 'ativo', 2),
(128, 1535, 10, 7, 30, '2025-03-22 03:00:00', 'ativo', 2),
(129, 1536, 10, 7, 30, '2025-03-22 03:00:00', 'ativo', 2),
(130, 1537, 10, 7, 30, '2025-03-22 03:00:00', 'ativo', 2),
(131, 1538, 10, 7, 30, '2025-03-22 03:00:00', 'ativo', 2),
(133, 1540, 10, 7, 30, '2025-03-22 03:00:00', 'ativo', 2),
(134, 1541, 10, 7, 30, '2025-03-22 03:00:00', 'ativo', 2),
(135, 1542, 10, 7, 30, '2025-03-22 03:00:00', 'ativo', 2),
(136, 1560, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(137, 1559, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(139, 1558, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(140, 1557, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(141, 1556, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(142, 1555, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(143, 1554, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(144, 1553, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(145, 1552, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(146, 1550, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(147, 1549, 7, 7, 27, '2025-03-23 03:00:00', 'ativo', 2),
(148, 1561, 10, 7, 30, '2025-03-23 03:00:00', 'ativo', 2),
(149, 1562, 10, 7, 30, '2025-03-23 03:00:00', 'ativo', 2),
(151, 1564, 10, 7, 30, '2025-03-23 03:00:00', 'ativo', 2),
(152, 1565, 10, 7, 30, '2025-03-23 03:00:00', 'ativo', 2),
(153, 1566, 10, 7, 30, '2025-03-23 03:00:00', 'ativo', 2),
(154, 1567, 10, 7, 30, '2025-03-23 03:00:00', 'ativo', 2),
(155, 1568, 10, 7, 30, '2025-03-23 03:00:00', 'ativo', 2),
(156, 1569, 6, 7, 28, '2025-03-23 03:00:00', 'ativo', 2),
(157, 1570, 6, 7, 28, '2025-03-23 03:00:00', 'ativo', 2),
(158, 1571, 6, 7, 28, '2025-03-23 03:00:00', 'ativo', 2),
(159, 1576, 6, 7, 28, '2025-03-29 03:00:00', 'ativo', 2),
(160, 1575, 6, 7, 28, '2025-03-29 03:00:00', 'ativo', 2),
(161, 1574, 6, 7, 28, '2025-03-29 03:00:00', 'ativo', 2),
(162, 1573, 6, 7, 28, '2025-03-29 03:00:00', 'ativo', 2),
(163, 1572, 6, 7, 28, '2025-03-29 03:00:00', 'ativo', 2),
(164, 1577, 8, 7, 21, '2025-03-29 03:00:00', 'ativo', 2),
(165, 1578, 8, 7, 21, '2025-03-29 03:00:00', 'ativo', 2),
(166, 1579, 8, 7, 21, '2025-03-29 03:00:00', 'ativo', 2),
(168, 1581, 8, 7, 21, '2025-03-29 03:00:00', 'ativo', 2),
(169, 1582, 8, 7, 21, '2025-03-29 03:00:00', 'ativo', 2),
(170, 1583, 8, 7, 21, '2025-03-29 03:00:00', 'ativo', 2),
(171, 1584, 8, 7, 21, '2025-03-29 03:00:00', 'ativo', 2),
(172, 1585, 10, 7, 30, '2025-04-06 03:00:00', 'ativo', 2),
(173, 1586, 10, 7, 30, '2025-04-06 03:00:00', 'ativo', 2),
(174, 1587, 10, 7, 30, '2025-04-06 03:00:00', 'ativo', 2),
(175, 1588, 10, 7, 30, '2025-04-06 03:00:00', 'ativo', 2),
(176, 1539, 9, 7, 29, '2025-04-06 03:00:00', 'ativo', 2),
(177, 1564, 9, 7, 29, '2025-04-06 03:00:00', 'ativo', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL COMMENT 'Ex: gerenciar_alunos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura para tabela `presencas`
--

CREATE TABLE `presencas` (
  `id` int(11) NOT NULL,
  `chamada_id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `presente` enum('presente','ausente','justificado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Despejando dados para a tabela `presencas`
--


CREATE TABLE `professores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `congregacao_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `professores_classes` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `relatorio_consolidado` (
`congregacao_id` int(11)
,`congregacao_nome` varchar(255)
,`classe_id` int(11)
,`classe_nome` varchar(100)
,`trimestre` int(11)
,`data_inicio` date
,`data_fim` date
,`total_alunos_matriculados` bigint(21)
,`total_presentes` bigint(21)
,`total_ausentes` bigint(21)
,`total_justificados` bigint(21)
,`total_biblias` decimal(32,0)
,`total_revistas` decimal(32,0)
,`total_visitantes` decimal(32,0)
,`total_ofertas_distintas` bigint(21)
,`ofertas` text
);

CREATE TABLE `relatorio_trimestre_congregacao` (
`classe_nome` varchar(100)
,`congregacao_nome` varchar(255)
,`trimestre` int(11)
,`total_biblias` decimal(32,0)
,`total_revistas` decimal(32,0)
,`total_visitantes` decimal(32,0)
,`total_ofertas` decimal(32,2)
);


CREATE TABLE `resumo_presenca` (
`aluno_id` int(11)
,`aluno_nome` varchar(100)
,`classe_id` int(11)
,`classe_nome` varchar(100)
,`congregacao_id` int(11)
,`congregacao_nome` varchar(255)
,`trimestre` int(11)
,`total_presentes` bigint(21)
,`total_ausentes` bigint(21)
);


CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','user','professor') NOT NULL,
  `congregacao_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `usuario_permissoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;