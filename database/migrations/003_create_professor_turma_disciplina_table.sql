-- Migration: Create professor_turma_disciplina table
CREATE TABLE IF NOT EXISTS professor_turma_disciplina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_professor INT NOT NULL,
    id_turma INT NOT NULL,
    id_disciplina INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_assignment (id_professor, id_turma, id_disciplina),
    INDEX idx_id_professor (id_professor),
    INDEX idx_id_turma (id_turma),
    INDEX idx_id_disciplina (id_disciplina),
    FOREIGN KEY (id_professor) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_turma) REFERENCES turma(id) ON DELETE CASCADE,
    FOREIGN KEY (id_disciplina) REFERENCES disciplina(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;