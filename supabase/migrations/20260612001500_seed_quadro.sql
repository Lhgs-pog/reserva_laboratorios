INSERT INTO quadros_horarios (nome, periodo_letivo)
SELECT 'Grade Oficial 2026.1', '2026.1'
WHERE NOT EXISTS (SELECT 1 FROM quadros_horarios);
