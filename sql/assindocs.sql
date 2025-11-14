-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 14/11/2025 às 21:20
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
-- Banco de dados: `assindocs`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `assinaturas_digitais`
--

CREATE TABLE `assinaturas_digitais` (
  `id` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `assinatura` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `assinaturas_digitais`
--

INSERT INTO `assinaturas_digitais` (`id`, `documento_id`, `usuario_id`, `assinatura`, `timestamp`, `ip_address`, `user_agent`) VALUES
(1, 2, 5, 'JPgvSaLebVIHHg8i43RqBeBMW+ls0gEUDRknr4SCX/yRZSv+Aa9t5UI42m9VVDwzbRZ3SvDpOfdQOzyiuniMiLOgh0tIQw/GxJUJcRG1BTFhfZzgxtg9Z42m3Ju1YeCjDNdGEjW0WUw6t9uC2NX0hIENW9gZpbI7BH7gly/1cLsynmejH300emZunruAcrzfElfOKaXDVC57+55y5f89yOsJg/fjEK5kauiHoH10kS/gW3EMBpAo/eTnvWOLmfo67bweKPXM47pmhHFp6jTxB7tF4RhGmW6YsLePOaiPWm9MWNMEtX3APKFmxtIdkvXOy6aGyoTvXxXrzLVrX/yLPw==', '2025-11-14 18:47:41', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chaves_usuarios`
--

CREATE TABLE `chaves_usuarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `chave_publica` text NOT NULL,
  `chave_privada_criptografada` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chaves_usuarios`
--

INSERT INTO `chaves_usuarios` (`id`, `usuario_id`, `chave_publica`, `chave_privada_criptografada`, `criado_em`, `atualizado_em`) VALUES
(1, 5, '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmy+U545PAUwUb5Di1QPm\nIRImzD3cxJyp2IwVt3Omt+p2J8o+d7CbV8W1uZMFUSBjI2bMFvdX2SimEDL7ukKL\nuhfB6pNmuY/lO/KmsyNdbrJzt2tCoVROjFA+qJpCJXNWF/IjI2lqn3S9+Yo/GLMp\nIGf5d23N36RtPKSmoGHDofbieEB8JF+wN4BbxAoTFpPCCf47xYtZPDD2ST1GaLhd\nI4JI8/Wd/WS/R5psDXcrLE3nX3nEsd8QAF7mqn7gnkeo9TfDymICpah5Rxw0Ogb8\nhknHOt575s1nFx4/CA5y1cnMUNUlQYRoE9RiRAgEzA8AHNQzGqlXsrVr3QFsqMBe\nxQIDAQAB\n-----END PUBLIC KEY-----\n', 'HvqFj7mKcVfKjLPjlc/ZIWVBK3J5dUtTdUVxVTdscTd4NlhmNG9NQ1dwVXc1MUhjaWh1bXN5d3AzU1U4R2JGbC9hNG5uVTdldW0wd3RoaTdlZmMvd1pma2FrVTJzcTFjc2pBM2N3NjJEMnBIOGd5bDNPRXczZXhDK2VrbVd2bGovNmx4UVFNZ1VaNTV0WnZ5TnRBVWxKdld5ODBINnlMOGZBc2c2UjhOUHJCUjhrVm9lbFdDSXJvbkdmYXF4K1BuNWJKbHhlblo5TllZTDI0ZHU0QXZGRVJ1N2thSXJSYkVDaEdxNmRXSTQ0NUxwMWg4WmwyV28xR2JycVppYy9MNUJzSmNPd1BxU2JxbTNMK1k4eXlkTGgrQUpNYXRJOXA3NHc0aDlxcUgxSitCZDh5aVFodlZ5aG9VNmhvbHBPRElHanRNWFJ5cVhmTEdGNzNKbWl0TE9OSHR3aVdVOTVvVzJaNnBLUUpPMUFGaHRPYUFrSGRZMkZLRStKL1FZd0JVMjhndm15NGJJV0xGNEpSRU1uTTF0NGp3TzRTdE4rc2xUdG1jT0l4dmRlTzlEdlp3aUt1aElrTzk3UnJiekFDZDJISU5NOWNRTmR4UEt1M3VmSlltOUJzdjlqNmpzWU5IZzJJVTNnWFQ0RElhdEJ5ZU45SzFZZGZnTFBjRlZFMk0xL1laV2JEUXhJRVFjaDBlSkFBSlpMZ0RJQ0lwSVlNZXAyMitPRG1yYm9XU0UzNXdzaVQxM09EdmJOaEpqQnBlbjg1TmYybnVhaS9YMWlSMU4wUzlQMXJRWElDLzkwb2IwclFMRFhJTkNsR3c3U1U3MlJNUkUyNXFUOUFKdGg5SmRiWlozWkJOQjBmUzdBSnArNEtDOGs1enFScWtNMklPYTF4c0NYV0FKRlBaaDlHckhzRDdBTEVnYW5Bcy9vMURldDg3dFRsOU1YYk9xaTRVQi9VYVd3MHBMWVFLSEtocys0NHA1bkl6S2pPL0tlbGJjdDFNYzBmeEZBbUU0alJ1NmY1REVHQ1BQZmExTDdBZitCMndGWnpzQnptREN1Tm9QYVE4UFI5dENpYWdaNkw0TlpXWTlveU4zdFJXNWgyYmZXZXg4dkFqTTVzVXFOd0hwWFRzQ2dLaWxDQzZWSzZ5MUhNdFF4NERPTzVWSmhsMzJYbEsvdmY4WUtZMnJjVXhiS2FoeUFLUUFyd3FYaFFDSXAxd0FycHdGYWxtVHVLZDV0aTdQb2djREN3em5kL3ZibDRoNU9yMmc1eUJxTkNGb0NoSFhqRXNma1BkNnd4cm9DNFR5cHJqTTYweFh0Ny9QbFRJNnZKaGhNTFhYamN5Nm9CQmJGeHBHY1liZnN0YXQxR01QUnVKMHlpbFA2Yk41cXRtUm9tRFZBMnVHV21xVGpzUkVLQVRYQStvM3M5MXp5VFVvY0VZTndoTThwa3h0S1kwSlE5STErMUNjeUVlK05TaHlQaFVQaGtuaHJjTkJhZ1Zxd3htUHNnaVdOUDVWMWluSmZEWGo4SGwxUG1ZOUNVbmZneXJ2czhwakZpQjdUN1BoM2lNWENZOURHM1hoaG96OVNZQ1JXTjZMb2Rqc2JNNFZpdWpRQUgwYzBiQlpueFpQSXlvcDJzU05EMTJhTlMvTHdMb3dPek9kbHlOM294bUtUS2JuRnhaaXJyU0pCeEFCYkQrUVlnamlvamhGcVVFMVJtL3ZPRldBckNBMmppWEt4M2VTU0dkUjFGclhOTFJnSkVTVVZuSXAySXFTUnFzYnZ4TlV1NmtkTnZ5M3JDVEJ6SU1oU1dndXgybkg2c28xZjhTYmhFNWxUZ2s4WVlmTndyUTFDcnVSN1Q3cDFEZ0lqTWluemlFQmRYQ0V3dld1OTlTcWdhTlUvdmpEaXpSaGk5VHhDaW1xb1dLY3dFaFo3MGg3NU01SEdyQlF1WWhWTnVHV2s1elZ0Y1BtVXZsVGFwOUFUVjdKL0xOUHgvbWFKQUhFYVRHS0FHdEFKU0Fub25VQXBUS2loWWMvMDdaZjdNekpLVmJlOWpxTnZHSDhMMjc2MnNXcndnU2RRc3BXOXA0SmNQaHhDa1lqLy9JdVJZUEVnVnVVenkrVkFCemFTUU11d3pzMXZzalN2NnlQd01mUUduUTg2dmZJdHZsak55ZG9TcjZPZ250OXh1VGV5YkVROGNhUk52L0hyMFk0SkI2UE1WVDZvQTlWSjJaeWFvQVlCT2c3RXFjRUQyeGhBektaUk1RRGhtWEFrcitHTXZTcHo1UHJjRGpaeGRBeGFESlJpcXNtU2pMVWlsdTUxVHBiRTZDNi9WNHlEOHpMUDZnYXl3UjNHK1ZiNW1Sczl4eUxKUzY3U29aa2JPOFJTeFg3Vjg2MlZsMVh0Nk45SFNwL2VNOU9wbi83eDFZZkhYTitqRmlzc0JRMk92ZTd0YWtyc0YrVDM1VHFJdXR6MjFmSjdRMVJ2QVpGRjFQWVZVMmsxZ1FHK213K1hDL0o4cUlHSXFkdVhjcFpra2FRL2thTDh4SDkrV2tLemZEUmRqUGIzVnJ0M3VvSnZxbFNUNDhLVDFqMy91SXBsTXcrN1VMMml5cjZYekZOZlNLb01hckFtZUd4aTFMWXg3YlhUVW1leUZjSkZ1ZFljQlFOLysyR004S05URDl0clZXbXdGNG5TVjVwaGxKVzNSRVdIa2NuNTNEU1IrWlk2UjhxRnk1Zys3Zy9xZmRsMEUzK1AwUkxvZ0xYakRRVUk5M1FkN01nT1JSckJmZS9TM0dFMzVuTWhGSlBZVVVWL1BBNnYxaGlCOEJMMmticjJEYUJwWkQ4WmNiZmQwdWluT2ZPcUVkbEtjYTVtSFpXZjlzalJYUEg0YzlpZ01hUjMvdUJ5WTQ3eWJRblBjMi9QVm9UbjdGUzFLYmN4REt4MFIyZXVTaHJTbVpJTkdhWE9DOXBNM2xKbFl5YjVWMXRORkRXS1R1dUp3NGtjZWxMcS9pWHAzZi9JSVVOaVhubFRTa3BPV2RRTFdwK0QyYWpFYkJVVXdjQlRBUkR3UUVyMkMzQVRUOTQ1QT0=', '2025-11-14 18:47:41', '2025-11-14 18:47:41');

-- --------------------------------------------------------

--
-- Estrutura para tabela `documentos`
--

CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `projeto_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `arquivo_path` varchar(255) DEFAULT NULL,
  `hash_documento` varchar(64) DEFAULT NULL,
  `status` enum('pendente','assinado','rejeitado') DEFAULT 'pendente',
  `versao` int(11) DEFAULT 1,
  `documento_original` int(11) DEFAULT NULL,
  `motivo_alteracao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `documentos`
--

INSERT INTO `documentos` (`id`, `usuario_id`, `projeto_id`, `titulo`, `descricao`, `arquivo_path`, `hash_documento`, `status`, `versao`, `documento_original`, `motivo_alteracao`, `criado_em`) VALUES
(1, 5, 0, 'Contrato_Estagio', NULL, 'uploads/69176c39cc891.pdf', '918c8523da14c7dd8259e77bfbd7eb61d2de39462dee676dd1774b40126d1757', 'pendente', 1, NULL, NULL, '2025-11-14 17:51:53'),
(2, 5, 0, 'Contrato_Estagiario', NULL, 'uploads/691773b4c44f4.pdf', '19e9dd168921106e7a5348d2df04020f944cf9cbcf669b9043a30f75b34c9845', 'assinado', 1, NULL, NULL, '2025-11-14 18:23:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_assinaturas`
--

CREATE TABLE `historico_assinaturas` (
  `id` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` enum('enviado','assinado','rejeitado') NOT NULL,
  `data_acao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `links_compartilhamento`
--

CREATE TABLE `links_compartilhamento` (
  `id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('visualizacao','assinatura','download') DEFAULT 'visualizacao',
  `expira_em` datetime NOT NULL,
  `max_usos` int(11) DEFAULT NULL,
  `usos` int(11) DEFAULT 0,
  `senha` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `links_compartilhamento`
--

INSERT INTO `links_compartilhamento` (`id`, `token`, `documento_id`, `usuario_id`, `tipo`, `expira_em`, `max_usos`, `usos`, `senha`, `ativo`, `criado_em`) VALUES
(1, 'fae2039c071c4f4259cad249ea2b7e5a3ffb2712d7446c5247bfa706442342eb', 1, 5, 'assinatura', '2025-11-16 20:18:59', NULL, 0, NULL, 1, '2025-11-14 19:18:59'),
(2, '8b7066730ac91c6b3710a971d86eb47f4d9c4178aea6f2e2e63aafcb7dd0c880', 1, 5, 'assinatura', '2025-11-16 20:19:54', NULL, 0, NULL, 1, '2025-11-14 19:19:54'),
(3, 'e5519a6b96bb60ef5b7e4787a26bdbf99ecfe8089600f03cca4de76b38d1e3d4', 1, 5, 'assinatura', '2025-11-16 20:44:20', NULL, 2, NULL, 1, '2025-11-14 19:44:20'),
(4, 'b8b90c3493a8ede31f7d0e86188e7b9f317c390776f6c6ba0903e0cc164a7022', 1, 5, 'assinatura', '2025-11-16 20:56:40', NULL, 0, NULL, 1, '2025-11-14 19:56:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_acesso_links`
--

CREATE TABLE `logs_acesso_links` (
  `id` int(11) NOT NULL,
  `link_id` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `acao` enum('visualizacao','assinatura','download','criacao') DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `logs_acesso_links`
--

INSERT INTO `logs_acesso_links` (`id`, `link_id`, `documento_id`, `ip_address`, `user_agent`, `acao`, `criado_em`) VALUES
(1, 2, 1, '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'criacao', '2025-11-14 19:19:54'),
(2, 3, 1, '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'criacao', '2025-11-14 19:44:20'),
(3, 3, 1, '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'visualizacao', '2025-11-14 19:48:11'),
(4, 4, 1, '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'criacao', '2025-11-14 19:56:40'),
(5, 3, 1, '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'visualizacao', '2025-11-14 19:56:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_assinatura`
--

CREATE TABLE `logs_assinatura` (
  `id` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `logs_assinatura`
--

INSERT INTO `logs_assinatura` (`id`, `documento_id`, `usuario_id`, `acao`, `descricao`, `ip_address`, `user_agent`, `criado_em`) VALUES
(1, 2, 5, 'ASSINAR', 'Documento assinado digitalmente', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-11-14 18:47:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_auditoria`
--

CREATE TABLE `logs_auditoria` (
  `id` int(11) NOT NULL,
  `acao` varchar(255) DEFAULT NULL,
  `tipo` enum('login','upload','assinatura','download','seguranca') DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `documento_id` int(11) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `tipo` enum('info','success','warning','error') DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `organizacoes`
--

CREATE TABLE `organizacoes` (
  `id` char(36) NOT NULL DEFAULT uuid(),
  `nome` text NOT NULL,
  `cnpj_cpf` varchar(32) DEFAULT NULL,
  `endereco` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`endereco`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `projetos`
--

CREATE TABLE `projetos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `signatarios`
--

CREATE TABLE `signatarios` (
  `id` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `status` enum('pendente','assinado','rejeitado') DEFAULT 'pendente',
  `data_assinatura` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `signatarios`
--

INSERT INTO `signatarios` (`id`, `documento_id`, `nome`, `email`, `status`, `data_assinatura`) VALUES
(1, 1, '', 'artursantana123@gmail.com', 'pendente', NULL),
(2, 2, '', 'artursantana123@gmail.com', 'pendente', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','usuario') DEFAULT 'usuario',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `data_cadastro`) VALUES
(5, 'ARTUR OLIVEIRA DE SANTANA', 'artursantana123@gmail.com', '$2y$10$grFNHCAal3bRTpT8o4m2iuqfz.LOEHLjol..uOTKDpLHf0olnmQKS', 'usuario', '2025-11-14 15:32:23');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `assinaturas_digitais`
--
ALTER TABLE `assinaturas_digitais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_doc_user` (`documento_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `chaves_usuarios`
--
ALTER TABLE `chaves_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_documentos_status` (`status`);

--
-- Índices de tabela `historico_assinaturas`
--
ALTER TABLE `historico_assinaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_historico_data` (`data_acao`);

--
-- Índices de tabela `links_compartilhamento`
--
ALTER TABLE `links_compartilhamento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `documento_id` (`documento_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `logs_acesso_links`
--
ALTER TABLE `logs_acesso_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `link_id` (`link_id`),
  ADD KEY `documento_id` (`documento_id`);

--
-- Índices de tabela `logs_assinatura`
--
ALTER TABLE `logs_assinatura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auditoria_tipo` (`tipo`),
  ADD KEY `idx_auditoria_usuario` (`usuario_id`),
  ADD KEY `idx_auditoria_data` (`criado_em`),
  ADD KEY `idx_auditoria_documento` (`documento_id`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `organizacoes`
--
ALTER TABLE `organizacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_organizacoes_cnpj` (`cnpj_cpf`);

--
-- Índices de tabela `projetos`
--
ALTER TABLE `projetos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `signatarios`
--
ALTER TABLE `signatarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documento_id` (`documento_id`),
  ADD KEY `idx_signatarios_email` (`email`);

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
-- AUTO_INCREMENT de tabela `assinaturas_digitais`
--
ALTER TABLE `assinaturas_digitais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `chaves_usuarios`
--
ALTER TABLE `chaves_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `historico_assinaturas`
--
ALTER TABLE `historico_assinaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `links_compartilhamento`
--
ALTER TABLE `links_compartilhamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `logs_acesso_links`
--
ALTER TABLE `logs_acesso_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `logs_assinatura`
--
ALTER TABLE `logs_assinatura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `projetos`
--
ALTER TABLE `projetos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `signatarios`
--
ALTER TABLE `signatarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `assinaturas_digitais`
--
ALTER TABLE `assinaturas_digitais`
  ADD CONSTRAINT `assinaturas_digitais_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assinaturas_digitais_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `chaves_usuarios`
--
ALTER TABLE `chaves_usuarios`
  ADD CONSTRAINT `chaves_usuarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_assinaturas`
--
ALTER TABLE `historico_assinaturas`
  ADD CONSTRAINT `historico_assinaturas_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_assinaturas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `links_compartilhamento`
--
ALTER TABLE `links_compartilhamento`
  ADD CONSTRAINT `links_compartilhamento_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `links_compartilhamento_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `logs_acesso_links`
--
ALTER TABLE `logs_acesso_links`
  ADD CONSTRAINT `logs_acesso_links_ibfk_1` FOREIGN KEY (`link_id`) REFERENCES `links_compartilhamento` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logs_acesso_links_ibfk_2` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `logs_assinatura`
--
ALTER TABLE `logs_assinatura`
  ADD CONSTRAINT `logs_assinatura_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logs_assinatura_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD CONSTRAINT `logs_auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `logs_auditoria_ibfk_2` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`);

--
-- Restrições para tabelas `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `projetos`
--
ALTER TABLE `projetos`
  ADD CONSTRAINT `projetos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `signatarios`
--
ALTER TABLE `signatarios`
  ADD CONSTRAINT `signatarios_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
