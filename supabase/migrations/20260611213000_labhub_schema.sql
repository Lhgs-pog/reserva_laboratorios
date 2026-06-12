-- LabHub UNICEPLAC — schema PostgreSQL (Supabase)
-- Migrado de sistema_labs.sql (MySQL)

CREATE TYPE perfil_usuario AS ENUM ('coordenador', 'professor', 'suporte');
CREATE TYPE turno_aula AS ENUM ('Matutino', 'Vespertino', 'Noturno');
CREATE TYPE periodo_horario AS ENUM ('1º e 2º Horários', '1º Horário', '2º Horário');
CREATE TYPE status_agendamento AS ENUM ('pendente', 'aprovado', 'rejeitado');
CREATE TYPE status_chave AS ENUM ('em_uso', 'devolvido');
CREATE TYPE status_sos AS ENUM ('pendente', 'resolvido');
CREATE TYPE dia_semana AS ENUM ('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo');

CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255),
  perfil perfil_usuario NOT NULL DEFAULT 'professor',
  email_verificado BOOLEAN NOT NULL DEFAULT FALSE,
  token_verificacao VARCHAR(255),
  google_id VARCHAR(100) UNIQUE,
  foto_perfil VARCHAR(500)
);

CREATE TABLE laboratorios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  capacidade INTEGER NOT NULL DEFAULT 0,
  localizacao VARCHAR(150),
  andar VARCHAR(50)
);

CREATE TABLE disciplinas (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(150) NOT NULL
);

CREATE TABLE cursos (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(150) NOT NULL
);

CREATE TABLE semestres (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(50) NOT NULL
);

CREATE TABLE blocos (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(50) NOT NULL
);

CREATE TABLE andares (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(50) NOT NULL
);

CREATE TABLE salas (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(50) NOT NULL
);

CREATE TABLE agendamentos (
  id SERIAL PRIMARY KEY,
  id_laboratorio INTEGER NOT NULL REFERENCES laboratorios(id) ON DELETE CASCADE,
  id_professor INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
  id_disciplina INTEGER NOT NULL REFERENCES disciplinas(id) ON DELETE CASCADE,
  turno turno_aula NOT NULL,
  periodo periodo_horario NOT NULL,
  data_reserva DATE NOT NULL,
  status status_agendamento NOT NULL DEFAULT 'pendente',
  UNIQUE (id_laboratorio, data_reserva, turno, periodo)
);

CREATE TABLE controle_chaves (
  id SERIAL PRIMARY KEY,
  id_agendamento INTEGER NOT NULL REFERENCES agendamentos(id) ON DELETE CASCADE,
  professor_nome VARCHAR(150) NOT NULL,
  laboratorio VARCHAR(100) NOT NULL,
  data_uso DATE NOT NULL,
  celular VARCHAR(20),
  hora_retirada TIME NOT NULL,
  hora_devolucao_prevista TIME NOT NULL,
  hora_devolucao_real TIME,
  funcionario_entrega VARCHAR(100) NOT NULL,
  funcionario_recebimento VARCHAR(100),
  status status_chave NOT NULL DEFAULT 'em_uso'
);

CREATE TABLE chamados_suporte (
  id SERIAL PRIMARY KEY,
  id_professor INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
  professor_nome VARCHAR(150) NOT NULL,
  laboratorio VARCHAR(100) NOT NULL,
  mensagem TEXT NOT NULL,
  status status_sos NOT NULL DEFAULT 'pendente',
  data_hora TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE quadros_horarios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  periodo_letivo VARCHAR(50) NOT NULL,
  data_criacao TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE quadro_aulas (
  id SERIAL PRIMARY KEY,
  id_quadro INTEGER NOT NULL REFERENCES quadros_horarios(id) ON DELETE CASCADE,
  turno turno_aula NOT NULL,
  dia_semana dia_semana NOT NULL,
  curso VARCHAR(150) NOT NULL,
  semestre VARCHAR(50) NOT NULL,
  id_disciplina INTEGER NOT NULL REFERENCES disciplinas(id) ON DELETE CASCADE,
  modalidade VARCHAR(100),
  numero_alunos INTEGER,
  id_professor INTEGER REFERENCES usuarios(id) ON DELETE SET NULL,
  id_laboratorio INTEGER REFERENCES laboratorios(id) ON DELETE SET NULL,
  horario periodo_horario NOT NULL,
  bloco VARCHAR(50),
  andar VARCHAR(50),
  sala VARCHAR(50),
  carga_horaria_total DECIMAL(5,2),
  horas_laboratorio DECIMAL(5,2)
);

-- Seed
INSERT INTO usuarios (nome, email, senha, perfil, email_verificado) VALUES
('Administrador', 'admin@uniceplac.edu.br',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'coordenador', TRUE);

INSERT INTO laboratorios (nome, capacidade, localizacao, andar) VALUES
('Laboratório de Informática 01', 40, 'Bloco A', '1º Andar'),
('Laboratório de Informática 02', 40, 'Bloco A', '1º Andar'),
('Laboratório de Redes', 30, 'Bloco B', '2º Andar'),
('Laboratório Multimídia', 35, 'Bloco C', 'Térreo');

INSERT INTO disciplinas (nome) VALUES
('Algoritmos e Estruturas de Dados'),
('Banco de Dados'),
('Engenharia de Software'),
('Programação Web'),
('Redes de Computadores'),
('Inteligência Artificial'),
('Sistemas Operacionais');

INSERT INTO cursos (nome) VALUES
('Ciência da Computação'),
('Sistemas de Informação'),
('Engenharia de Software'),
('Análise e Desenvolvimento de Sistemas');

INSERT INTO semestres (nome) VALUES ('2026.1'), ('2026.2');
