-- Lista de espera para laboratórios lotados
CREATE TABLE IF NOT EXISTS lista_espera_laboratorio (
  id SERIAL PRIMARY KEY,
  id_professor INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
  id_disciplina INTEGER NOT NULL REFERENCES disciplinas(id) ON DELETE CASCADE,
  data_reserva DATE NOT NULL,
  turno turno_aula NOT NULL,
  periodo periodo_horario NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'aguardando',
  criado_em TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  email_enviado_em TIMESTAMPTZ,
  UNIQUE (id_professor, data_reserva, turno, periodo)
);

CREATE INDEX IF NOT EXISTS idx_lista_espera_slot ON lista_espera_laboratorio (data_reserva, turno, periodo, status);
