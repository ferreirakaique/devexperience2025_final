-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24-Out-2025 às 21:11
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `youtan_gestao_ativos`
--
CREATE DATABASE IF NOT EXISTS `youtan_gestao_ativos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `youtan_gestao_ativos`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ativos`
--

CREATE TABLE `ativos` (
  `id_ativo` int(11) NOT NULL,
  `nome_ativo` varchar(255) NOT NULL,
  `categoria_ativo` varchar(255) NOT NULL,
  `valor_ativo` float NOT NULL,
  `data_aquisicao_ativo` date NOT NULL,
  `numero_serie_ativo` varchar(255) NOT NULL,
  `status_ativo` enum('ativo','inativo') NOT NULL,
  `localizacao_ativo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `ativos`
--

INSERT INTO `ativos` (`id_ativo`, `nome_ativo`, `categoria_ativo`, `valor_ativo`, `data_aquisicao_ativo`, `numero_serie_ativo`, `status_ativo`, `localizacao_ativo`) VALUES
(1, 'Notebook Dell Latitude 4050', 'Computador', 7850, '2023-08-15', 'DLL-7430-9R2K4F', 'inativo', 'Escritório Central — Estação 12'),
(2, 'Servidor HP ProLiant DL380', 'Servidor', 38900, '2022-11-03', 'HP-DL380-G10-7X8Q', 'inativo', 'Datacenter — Rack A04'),
(3, 'Impressora HP LaserJet Pro M404dn', 'Impressora', 1650, '2024-02-21', 'HPM404-0A9C-4412', 'ativo', 'Almoxarifado — Prateleira P2'),
(4, 'Smartphone Samsung Galaxy S22', 'Dispositivo Móvel', 3499.9, '2023-05-10', 'S22-SM-G991B-1XZ8', 'ativo', 'Comercial — Equipe Externa'),
(5, 'Monitor LG 27UL500', 'Monitor', 1299, '2023-09-30', 'LG27UL500-SN-77KQ', 'ativo', 'Escritório Central — Sala de Suporte'),
(6, 'Software Microsoft 365 E3 (assinatura)', 'Software', 0, '2024-01-01', 'O365-E3-ORG-2024', 'ativo', 'Ativo Digital — Contas Corporativas'),
(7, 'Veículo Fiat Fiorino 1.4 Flex 2022', 'Veículo', 78500, '2023-02-18', 'VIN-9BD225A35N1234567', 'ativo', 'Depósito Central — Vaga B12'),
(8, 'NoBreak APC Smart-UPS 1500', 'NoBreak', 2890, '2022-07-19', 'APC-SMT1500I-55TZ', 'inativo', 'Datacenter — Sala Elétrica'),
(9, 'Câmera IP Intelbras VIP 3430 B', 'CFTV', 699, '2024-06-05', 'VIP3430B-IB-9Q1L', 'ativo', 'Sede — Segurança'),
(10, 'Roteador Cisco Catalyst 9200', 'Switch/Roteador', 15400, '2022-10-11', 'CAT9200L-24T-4G-2M1', 'ativo', 'Infraestrutura — Armário de Rede 2'),
(11, 'Tablet iPad 10ª Geração 64GB', 'Dispositivo Móvel', 2499, '2024-03-14', 'IPAD-10G-64-PL-8FD2', 'ativo', 'Engenharia — Equipe de Campo'),
(12, 'Cadeira Ergonômica Cavaletti S080', 'Mobiliário', 1180, '2023-01-27', 'CAV-S080-CH-9921', 'inativo', 'Patrimônio — Baixa 2025');

-- --------------------------------------------------------

--
-- Estrutura da tabela `manutencoes`
--

CREATE TABLE `manutencoes` (
  `id_manutencao` int(11) NOT NULL,
  `tipo_manutencao` varchar(255) NOT NULL,
  `data_manutencao` date NOT NULL,
  `responsavel_manutencao` int(11) NOT NULL,
  `custo_manutencao` float NOT NULL,
  `descricao_manutencao` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `manutencoes`
--

INSERT INTO `manutencoes` (`id_manutencao`, `tipo_manutencao`, `data_manutencao`, `responsavel_manutencao`, `custo_manutencao`, `descricao_manutencao`) VALUES
(3, 'Corretiva', '2025-10-09', 1, 200.9, 'Capacitor e leitor'),
(5, 'Preventiva', '2025-10-06', 2, 359.9, 'Arrumando a tela LCD');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id_notificacao` int(11) NOT NULL,
  `fk_id_ativo` int(11) NOT NULL,
  `fk_id_usuario` int(11) NOT NULL,
  `descricao_notificacao` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome_usuario` varchar(255) NOT NULL,
  `email_usuario` varchar(255) NOT NULL,
  `senha_usuario` varchar(255) NOT NULL,
  `tipo_usuario` enum('Admin','Colaborador') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome_usuario`, `email_usuario`, `senha_usuario`, `tipo_usuario`) VALUES
(1, 'Kaique Ferreira', 'kaique1245br@gmail.com', '$2y$10$a4TsiiVZxsAIoKScuRsRLO2DKuB6OC9NXmGzGVmQ.iIivvcAb1qrK', 'Admin'),
(2, 'Yago', 'yago@gmail.com', '$2y$10$a4TsiiVZxsAIoKScuRsRLO2DKuB6OC9NXmGzGVmQ.iIivvcAb1qrK', 'Colaborador'),
(3, 'João Silva', 'joao.silva@email.com', '$2y$10$a4TsiiVZxsAIoKScuRsRLO2DKuB6OC9NXmGzGVmQ.iIivvcAb1qrK', 'Admin');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `ativos`
--
ALTER TABLE `ativos`
  ADD PRIMARY KEY (`id_ativo`);

--
-- Índices para tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD PRIMARY KEY (`id_manutencao`),
  ADD KEY `fk_responsavel_manutencao` (`responsavel_manutencao`);

--
-- Índices para tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id_notificacao`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ativos`
--
ALTER TABLE `ativos`
  MODIFY `id_ativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  MODIFY `id_manutencao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id_notificacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD CONSTRAINT `fk_responsavel_manutencao` FOREIGN KEY (`responsavel_manutencao`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
