-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03/11/2026 às 20:43
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
-- Banco de dados: `acessolivre`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `idUsuario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `local` varchar(255) NOT NULL,
  `dataEvento` date NOT NULL,
  `horario` time NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `acessibilidade` text DEFAULT NULL,
  `audioDescricao` text DEFAULT NULL,
  `mapaSensorial` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `eventos`
--

INSERT INTO `eventos` (`idUsuario`, `nome`, `descricao`, `local`, `dataEvento`, `horario`, `preco`, `acessibilidade`, `audioDescricao`, `mapaSensorial`) VALUES
(1, 'Show do Luan Santana', 'Um grande evento musical com recursos de acessibilidade.', 'São Paulo - SP', '2026-11-20', '20:00:00', 150.00, 'Intérprete de Libras, espaço PCD e acessibilidade física.', 'Audiodescrição disponível.', 'Mapa sensorial disponível.'),
(2, 'Festival Acesso Livre', 'Festival com música, cultura e inclusão.', 'São Paulo - SP', '2026-12-10', '18:00:00', 80.00, 'Libras, audiodescrição e espaço acessível.', 'Audiodescrição disponível.', 'Mapa sensorial disponível.'),
(3, 'Teatro Acessível', 'Uma apresentação teatral com cultura, arte e inclusão para todos.', 'Ourinhos - SP', '2026-11-28', '19:30:00', 50.00, 'Intérprete de Libras e espaço para pessoas com deficiência.', 'Audiodescrição disponível durante a apresentação.', 'Mapa sensorial disponível para os visitantes.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ingressos`
--

CREATE TABLE `ingressos` (
  `idIngresso` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `idEvento` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `compradoEm` time NOT NULL,
  `cadeiraEscolhida` varchar(10) DEFAULT NULL,
  `areaEscolhida` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ingressos`
--

INSERT INTO `ingressos` (`idIngresso`, `idUsuario`, `idEvento`, `codigo`, `compradoEm`, `cadeiraEscolhida`, `areaEscolhida`) VALUES
(1, 4, 1, 'AL-4AB74370', '10:02:20', NULL, NULL),
(2, 4, 1, '73CAEA0A', '10:35:28', 'E3', 'Espaço reservado'),
(3, 4, 2, 'B5B6D4EA', '10:44:59', 'A5', 'Espaço reservado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `e-mail` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `acessibilidade` text DEFAULT NULL,
  `criado_em` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nome`, `e-mail`, `senha`, `acessibilidade`, `criado_em`) VALUES
(1, 'Carolina Miranda', 'Caroranda@gmail.com', '$2y$10$hyzlN2uFKkGumd/CcjroKODhpTpsTb6dnyVKVDwDGpBG26DlIoU5y', NULL, '2026-10-27'),
(2, 'Cassiano', 'cassianoff@gmail.com', '$2y$10$7y.J0xof7JvIp/WEccCRQu70Bcuc7dZIHhDfSIWIjfnBarN9Wugsu', NULL, '2026-10-27'),
(3, 'ELIENAI22', 'elienai22@gmail.com', '$2y$10$DEh6yJ21VWfZ0BK5dOzD8OwVrh1IwhgUgKv0JnphUPcukC5RSeyga', NULL, '2026-10-27'),
(4, 'Elienai Rodrigues', 'elienai.aguiar@aluno.senai.br', '$2y$10$1s2g38BhsRS4QHwVZoaeLuz3dFTB4xALVx0mQqGvH2DcN8DZZWrwe', NULL, '2026-11-03');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`idUsuario`);

--
-- Índices de tabela `ingressos`
--
ALTER TABLE `ingressos`
  ADD PRIMARY KEY (`idIngresso`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `idx_evento_cadeira` (`idEvento`,`cadeiraEscolhida`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `e-mail` (`e-mail`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `ingressos`
--
ALTER TABLE `ingressos`
  MODIFY `idIngresso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
