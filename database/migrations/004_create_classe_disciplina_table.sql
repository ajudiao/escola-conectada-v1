-- Create classe_disciplina table
CREATE TABLE classe_disciplina (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classe_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (classe_id) REFERENCES classe(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplina(id) ON DELETE CASCADE
);