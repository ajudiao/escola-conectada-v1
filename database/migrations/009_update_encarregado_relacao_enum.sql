-- Alterar coluna relacao_educando para enum
ALTER TABLE `encarregado` 
MODIFY COLUMN `relacao_educando` enum('pai','mae','tio','avo','responsavel','outro') NOT NULL;