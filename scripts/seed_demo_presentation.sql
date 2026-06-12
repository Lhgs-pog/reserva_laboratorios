-- Seed completo de demonstração — preenche calendário, quadro, SOS, chaves, ensalamento
-- Uso: docker compose exec -T mysql mysql -ulabs -plabs123 sistema_labs < scripts/seed_demo_presentation.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM controle_chaves;
DELETE FROM chamados_suporte;
DELETE FROM agendamentos;
DELETE FROM ensalamento;
DELETE FROM quadro_aulas;

-- Garantir grade ativa
INSERT IGNORE INTO quadros_horarios (id, nome, periodo_letivo) VALUES (1, 'Grade Oficial 2026.1', '2026.1');

-- ── Quadro de horários (grade fixa rica) ─────────────────────────────────────
INSERT INTO quadro_aulas (id_quadro, turno, dia_semana, curso, semestre, id_disciplina, modalidade, numero_alunos, id_professor, id_laboratorio, horario, bloco, andar, sala, carga_horaria_total, horas_laboratorio) VALUES
(1, 'Matutino', 'Segunda', 'Ciência da Computação', '2026.1', 4, 'Presencial', 38, 2, 1, '1º Horário', 'A', '1º Andar', '101', 4, 2),
(1, 'Matutino', 'Segunda', 'Sistemas de Informação', '2026.1', 2, 'Presencial', 32, 3, 2, '2º Horário', 'A', '1º Andar', '102', 4, 2),
(1, 'Vespertino', 'Terça', 'Ciência da Computação', '2026.1', 6, 'Presencial', 30, 2, 3, '1º Horário', 'B', '2º Andar', '201', 4, 3),
(1, 'Vespertino', 'Terça', 'Engenharia de Software', '2026.1', 3, 'Presencial', 28, 3, 4, '2º Horário', 'C', 'Térreo', '103', 4, 2),
(1, 'Noturno', 'Quarta', 'Análise e Desenvolvimento', '2026.1', 1, 'Presencial', 40, 2, 1, '1º Horário', 'A', '1º Andar', '101', 4, 2),
(1, 'Noturno', 'Quarta', 'Redes de Computadores', '2026.1', 5, 'Presencial', 35, 3, 3, '2º Horário', 'B', '2º Andar', '202', 4, 3),
(1, 'Matutino', 'Quinta', 'Ciência da Computação', '2026.1', 7, 'Presencial', 36, 2, 2, '1º e 2º Horários', 'A', '1º Andar', 'Lab', 6, 4),
(1, 'Vespertino', 'Quinta', 'Sistemas de Informação', '2026.1', 4, 'Presencial', 33, 3, 1, '1º Horário', 'A', '1º Andar', '101', 4, 2),
(1, 'Noturno', 'Quinta', 'Programação Web', '2026.1', 4, 'Presencial', 38, 2, 2, '2º Horário', 'A', '1º Andar', 'Lab 02', 4, 2),
(1, 'Matutino', 'Sexta', 'Banco de Dados', '2026.1', 2, 'Presencial', 30, 3, 4, '1º Horário', 'C', 'Térreo', 'Multimídia', 4, 3),
(1, 'Vespertino', 'Sexta', 'Programação Web', '2026.1', 4, 'EAD', 0, NULL, NULL, '1º Horário', NULL, NULL, 'EAD', 4, 0),
(1, 'Noturno', 'Sábado', 'Inteligência Artificial', '2026.1', 6, 'Presencial', 25, 2, 3, '1º e 2º Horários', 'B', '2º Andar', 'Redes', 6, 4);

-- ── Reservas avulsas (próximas 2 semanas) ────────────────────────────────────
INSERT INTO agendamentos (id_laboratorio, id_professor, id_disciplina, data_reserva, turno, periodo, status) VALUES
-- Hoje (mapa suporte + chaves)
(1, 2, 4, CURDATE(), 'Matutino', '1º Horário', 'aprovado'),
(2, 3, 2, CURDATE(), 'Matutino', '2º Horário', 'aprovado'),
(1, 2, 4, CURDATE(), 'Vespertino', '1º Horário', 'aprovado'),
(3, 3, 5, CURDATE(), 'Vespertino', '2º Horário', 'aprovado'),
(2, 2, 6, CURDATE(), 'Noturno', '1º Horário', 'aprovado'),
-- Próximos dias
(1, 2, 4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Noturno', '1º Horário', 'aprovado'),
(3, 3, 5, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Noturno', '2º Horário', 'pendente'),
(4, 2, 6, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Vespertino', '1º Horário', 'aprovado'),
(2, 3, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Noturno', '2º Horário', 'pendente'),
(1, 2, 4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Matutino', '1º e 2º Horários', 'aprovado'),
(3, 3, 5, DATE_ADD(CURDATE(), INTERVAL 4 DAY), 'Matutino', '1º Horário', 'pendente'),
(2, 2, 7, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Vespertino', '2º Horário', 'aprovado'),
(4, 3, 3, DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'Noturno', '1º Horário', 'rejeitado'),
(1, 2, 4, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Noturno', '2º Horário', 'aprovado'),
(3, 3, 5, DATE_ADD(CURDATE(), INTERVAL 8 DAY), 'Vespertino', '1º Horário', 'pendente'),
(2, 2, 2, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Matutino', '2º Horário', 'aprovado'),
-- Histórico (última semana)
(1, 2, 4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Vespertino', '1º Horário', 'aprovado'),
(2, 3, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Matutino', '1º Horário', 'aprovado'),
(3, 3, 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Noturno', '2º Horário', 'aprovado'),
(4, 2, 6, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Vespertino', '2º Horário', 'rejeitado');

-- ── Chaves ───────────────────────────────────────────────────────────────────
INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, funcionario_entrega, status)
SELECT a.id, 'Prof. Ana Silva', 'Laboratório de Informática 01', CURDATE(), '(62) 99999-8888', '14:25:00', '18:00:00', 'Carlos Suporte', 'em_uso'
FROM agendamentos a WHERE a.id_professor = 2 AND a.data_reserva = CURDATE() AND a.turno = 'Vespertino' LIMIT 1;

INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, funcionario_entrega, status)
SELECT a.id, 'Prof. João Mendes', 'Laboratório de Redes', CURDATE(), '(62) 98888-7777', '14:40:00', '18:00:00', 'Carlos Suporte', 'em_uso'
FROM agendamentos a WHERE a.id_professor = 3 AND a.data_reserva = CURDATE() AND a.turno = 'Vespertino' LIMIT 1;

INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, hora_devolucao_real, funcionario_entrega, funcionario_recebimento, status)
SELECT a.id, 'Prof. Ana Silva', 'Laboratório de Informática 01', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '(62) 99999-8888', '14:20:00', '18:00:00', '17:50:00', 'Carlos Suporte', 'Carlos Suporte', 'devolvido'
FROM agendamentos a WHERE a.id_professor = 2 AND a.data_reserva = DATE_SUB(CURDATE(), INTERVAL 1 DAY) LIMIT 1;

INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, hora_devolucao_real, funcionario_entrega, funcionario_recebimento, status)
SELECT a.id, 'Prof. João Mendes', 'Laboratório de Informática 02', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '(62) 98888-7777', '08:30:00', '12:00:00', '11:55:00', 'Carlos Suporte', 'Técnico Carlos Suporte', 'devolvido'
FROM agendamentos a WHERE a.id_professor = 3 AND a.data_reserva = DATE_SUB(CURDATE(), INTERVAL 2 DAY) LIMIT 1;

INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, hora_devolucao_real, funcionario_entrega, funcionario_recebimento, status)
SELECT a.id, 'Prof. João Mendes', 'Laboratório de Redes', DATE_SUB(CURDATE(), INTERVAL 3 DAY), '(62) 98888-7777', '21:00:00', '22:30:00', '22:15:00', 'Carlos Suporte', 'Carlos Suporte', 'devolvido'
FROM agendamentos a WHERE a.id_professor = 3 AND a.data_reserva = DATE_SUB(CURDATE(), INTERVAL 3 DAY) LIMIT 1;

-- ── SOS / Chamados ───────────────────────────────────────────────────────────
INSERT INTO chamados_suporte (id_professor, professor_nome, laboratorio, mensagem, status, data_hora) VALUES
(2, 'Prof. Ana Silva', 'Laboratório de Informática 01', 'Computador posição 12 não inicia. Turma de Programação Web aguardando.', 'pendente', DATE_SUB(NOW(), INTERVAL 12 MINUTE)),
(3, 'Prof. João Mendes', 'Laboratório de Redes', 'Projetor sem imagem — cabo HDMI testado, problema persiste.', 'pendente', DATE_SUB(NOW(), INTERVAL 35 MINUTE)),
(2, 'Prof. Ana Silva', 'Laboratório Multimídia', 'Som do sistema não funciona na sala. Aula de multimídia hoje.', 'pendente', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 'Prof. João Mendes', 'Laboratório de Informática 02', 'Mouse sem funcionar em 3 bancadas do fundo.', 'pendente', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(2, 'Prof. Ana Silva', 'Laboratório de Informática 02', 'Wi-Fi instável — resolvido após reinício do roteador.', 'resolvido', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'Prof. João Mendes', 'Laboratório Multimídia', 'Teclado quebrado bancada 5 — substituído.', 'resolvido', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'Prof. Ana Silva', 'Laboratório de Redes', 'Switch da rack desligado — energia restabelecida.', 'resolvido', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(3, 'Prof. João Mendes', 'Laboratório de Informática 01', 'Licença do software expirada — reativada.', 'resolvido', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(2, 'Prof. Ana Silva', 'Laboratório de Informática 01', 'Ar-condicionado com vazamento — manutenção predial acionada.', 'resolvido', DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ── Ensalamento (todos os turnos) ────────────────────────────────────────────
INSERT INTO ensalamento (id_professor, id_disciplina, curso, bloco, andar, sala, categoria, turno) VALUES
(2, 4, 'Ciência da Computação', 'A', '1º Andar', '201', 'Presencial', 'Matutino'),
(3, 5, 'Sistemas de Informação', 'B', '2º Andar', '102', 'Presencial', 'Matutino'),
(2, 2, 'Ciência da Computação', 'A', '1º Andar', '203', 'Presencial', 'Vespertino'),
(3, 3, 'Engenharia de Software', 'C', 'Térreo', '105', 'Presencial', 'Vespertino'),
(2, 4, 'Ciência da Computação', 'A', '1º Andar', '201', 'Presencial', 'Noturno'),
(3, 5, 'Sistemas de Informação', 'B', '2º Andar', '302', 'Presencial', 'Noturno'),
(2, 6, 'Análise e Desenvolvimento', 'A', '1º Andar', '104', 'EAD Polo Goiânia', 'Noturno');

SET FOREIGN_KEY_CHECKS = 1;

SELECT '=== RESUMO DEMO ===' AS '';
SELECT 'agendamentos' AS tipo, status, COUNT(*) AS qtd FROM agendamentos GROUP BY status;
SELECT 'quadro_aulas' AS tipo, COUNT(*) AS qtd FROM quadro_aulas;
SELECT 'sos_pendentes' AS tipo, COUNT(*) AS qtd FROM chamados_suporte WHERE status='pendente';
SELECT 'sos_resolvidos' AS tipo, COUNT(*) AS qtd FROM chamados_suporte WHERE status='resolvido';
SELECT 'chaves_em_uso' AS tipo, COUNT(*) AS qtd FROM controle_chaves WHERE status='em_uso';
SELECT 'chaves_devolvidas' AS tipo, COUNT(*) AS qtd FROM controle_chaves WHERE status='devolvido';
SELECT 'ensalamento' AS tipo, COUNT(*) AS qtd FROM ensalamento;
SELECT 'hoje_aprovadas' AS tipo, COUNT(*) AS qtd FROM agendamentos WHERE data_reserva=CURDATE() AND status='aprovado';
