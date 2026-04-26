-- Criar tabela encarregado
CREATE TABLE IF NOT EXISTS `encarregado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `data_nascimento` date NOT NULL,
  `n_identidade` varchar(50) NOT NULL,
  `profissao` varchar(100) DEFAULT NULL,
  `relacao_educando` enum('pai','mae','tio','avo','responsavel','outro') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `n_identidade` (`n_identidade`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `encarregado_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;