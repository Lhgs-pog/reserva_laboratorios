-- Tabela ensalamento (faltava na migração inicial)
CREATE TABLE IF NOT EXISTS ensalamento (
  id SERIAL PRIMARY KEY,
  id_professor INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
  id_disciplina INTEGER NOT NULL REFERENCES disciplinas(id) ON DELETE CASCADE,
  curso VARCHAR(150) NOT NULL,
  bloco VARCHAR(50) NOT NULL,
  andar VARCHAR(50) NOT NULL,
  sala VARCHAR(50) NOT NULL,
  categoria VARCHAR(100),
  turno turno_aula NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_ens_professor ON ensalamento (id_professor);
CREATE INDEX IF NOT EXISTS idx_ens_sala_turno ON ensalamento (bloco, andar, sala, turno);
