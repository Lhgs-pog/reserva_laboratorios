-- Seed completo para dashboards (mapa suporte + relatórios coordenação)
-- Reexecutável: limpa dados operacionais e repovoa com datas relativas a CURRENT_DATE

DELETE FROM controle_chaves;
DELETE FROM chamados_suporte;
DELETE FROM agendamentos;
DELETE FROM ensalamento;
DELETE FROM quadro_aulas;

INSERT INTO blocos (nome)
SELECT v FROM (VALUES ('A'), ('B'), ('C')) AS t(v)
WHERE NOT EXISTS (SELECT 1 FROM blocos b WHERE b.nome = t.v);

INSERT INTO andares (nome)
SELECT v FROM (VALUES ('Térreo'), ('1º Andar'), ('2º Andar')) AS t(v)
WHERE NOT EXISTS (SELECT 1 FROM andares a WHERE a.nome = t.v);

INSERT INTO salas (nome)
SELECT v FROM (VALUES
  ('101'), ('102'), ('103'), ('201'), ('202'), ('203'), ('302'),
  ('Lab'), ('Lab 02'), ('Multimídia'), ('Redes'), ('EAD')
) AS t(v)
WHERE NOT EXISTS (SELECT 1 FROM salas s WHERE s.nome = t.v);

INSERT INTO quadros_horarios (nome, periodo_letivo)
SELECT 'Grade Oficial 2026.1', '2026.1'
WHERE NOT EXISTS (SELECT 1 FROM quadros_horarios);

INSERT INTO quadro_aulas (id_quadro, turno, dia_semana, curso, semestre, id_disciplina, modalidade, numero_alunos, id_professor, id_laboratorio, horario, bloco, andar, sala, carga_horaria_total, horas_laboratorio)
SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Matutino'::turno_aula, 'Segunda'::dia_semana, 'Ciência da Computação', '2026.1', 4, 'Presencial', 38,
       (SELECT id FROM usuarios WHERE email = 'professor@uniceplac.edu.br'), 1, '1º Horário'::periodo_horario, 'A', '1º Andar', '101', 4, 2
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Matutino', 'Segunda', 'Sistemas de Informação', '2026.1', 2, 'Presencial', 32,
       (SELECT id FROM usuarios WHERE email = 'joao@uniceplac.edu.br'), 2, '2º Horário', 'A', '1º Andar', '102', 4, 2
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Vespertino', 'Terça', 'Ciência da Computação', '2026.1', 6, 'Presencial', 30,
       (SELECT id FROM usuarios WHERE email = 'professor@uniceplac.edu.br'), 3, '1º Horário', 'B', '2º Andar', '201', 4, 3
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Vespertino', 'Terça', 'Engenharia de Software', '2026.1', 3, 'Presencial', 28,
       (SELECT id FROM usuarios WHERE email = 'joao@uniceplac.edu.br'), 4, '2º Horário', 'C', 'Térreo', '103', 4, 2
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Noturno', 'Quarta', 'Análise e Desenvolvimento', '2026.1', 1, 'Presencial', 40,
       (SELECT id FROM usuarios WHERE email = 'professor@uniceplac.edu.br'), 1, '1º Horário', 'A', '1º Andar', '101', 4, 2
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Noturno', 'Quarta', 'Redes de Computadores', '2026.1', 5, 'Presencial', 35,
       (SELECT id FROM usuarios WHERE email = 'joao@uniceplac.edu.br'), 3, '2º Horário', 'B', '2º Andar', '202', 4, 3
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Matutino', 'Quinta', 'Ciência da Computação', '2026.1', 7, 'Presencial', 36,
       (SELECT id FROM usuarios WHERE email = 'professor@uniceplac.edu.br'), 2, '1º e 2º Horários', 'A', '1º Andar', 'Lab', 6, 4
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Vespertino', 'Quinta', 'Sistemas de Informação', '2026.1', 4, 'Presencial', 33,
       (SELECT id FROM usuarios WHERE email = 'joao@uniceplac.edu.br'), 1, '1º Horário', 'A', '1º Andar', '101', 4, 2
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Noturno', 'Quinta', 'Programação Web', '2026.1', 4, 'Presencial', 38,
       (SELECT id FROM usuarios WHERE email = 'professor@uniceplac.edu.br'), 2, '2º Horário', 'A', '1º Andar', 'Lab 02', 4, 2
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Matutino', 'Sexta', 'Banco de Dados', '2026.1', 2, 'Presencial', 30,
       (SELECT id FROM usuarios WHERE email = 'joao@uniceplac.edu.br'), 4, '1º Horário', 'C', 'Térreo', 'Multimídia', 4, 3
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Vespertino', 'Sexta', 'Programação Web', '2026.1', 4, 'Presencial', 0,
       NULL, NULL, '1º Horário', NULL, NULL, 'EAD', 4, 0
UNION ALL SELECT (SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1), 'Noturno', 'Sábado', 'Inteligência Artificial', '2026.1', 6, 'Presencial', 25,
       (SELECT id FROM usuarios WHERE email = 'professor@uniceplac.edu.br'), 3, '1º e 2º Horários', 'B', '2º Andar', 'Redes', 6, 4;

INSERT INTO agendamentos (id_laboratorio, id_professor, id_disciplina, data_reserva, turno, periodo, status)
SELECT v.lab, u.id, v.disc, CURRENT_DATE + v.dia_offset, v.turno::turno_aula, v.periodo::periodo_horario, v.status::status_agendamento
FROM (VALUES
  (1, 'professor@uniceplac.edu.br', 4, 0, 'Matutino', '1º Horário', 'aprovado'),
  (2, 'joao@uniceplac.edu.br', 2, 0, 'Matutino', '2º Horário', 'aprovado'),
  (1, 'professor@uniceplac.edu.br', 4, 0, 'Vespertino', '1º Horário', 'aprovado'),
  (3, 'joao@uniceplac.edu.br', 5, 0, 'Vespertino', '2º Horário', 'aprovado'),
  (2, 'professor@uniceplac.edu.br', 6, 0, 'Noturno', '1º Horário', 'aprovado'),
  (1, 'professor@uniceplac.edu.br', 4, 1, 'Noturno', '1º Horário', 'aprovado'),
  (3, 'joao@uniceplac.edu.br', 5, 1, 'Noturno', '2º Horário', 'pendente'),
  (4, 'professor@uniceplac.edu.br', 6, 2, 'Vespertino', '1º Horário', 'aprovado'),
  (2, 'joao@uniceplac.edu.br', 2, 2, 'Noturno', '2º Horário', 'pendente'),
  (1, 'professor@uniceplac.edu.br', 4, 3, 'Matutino', '1º e 2º Horários', 'aprovado'),
  (3, 'joao@uniceplac.edu.br', 5, 4, 'Matutino', '1º Horário', 'pendente'),
  (2, 'professor@uniceplac.edu.br', 7, 5, 'Vespertino', '2º Horário', 'aprovado'),
  (4, 'joao@uniceplac.edu.br', 3, 6, 'Noturno', '1º Horário', 'rejeitado'),
  (1, 'professor@uniceplac.edu.br', 4, 7, 'Noturno', '2º Horário', 'aprovado'),
  (3, 'joao@uniceplac.edu.br', 5, 8, 'Vespertino', '1º Horário', 'pendente'),
  (2, 'professor@uniceplac.edu.br', 2, 10, 'Matutino', '2º Horário', 'aprovado'),
  (1, 'professor@uniceplac.edu.br', 4, -1, 'Vespertino', '1º Horário', 'aprovado'),
  (2, 'joao@uniceplac.edu.br', 2, -2, 'Matutino', '1º Horário', 'aprovado'),
  (3, 'joao@uniceplac.edu.br', 5, -3, 'Noturno', '2º Horário', 'aprovado'),
  (4, 'professor@uniceplac.edu.br', 6, -5, 'Vespertino', '2º Horário', 'rejeitado')
) AS v(lab, email, disc, dia_offset, turno, periodo, status)
JOIN usuarios u ON u.email = v.email;

INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, funcionario_entrega, status)
SELECT a.id, 'Prof. Ana Silva', 'Laboratório de Informática 01', CURRENT_DATE, '(62) 99999-8888', '14:25:00', '18:00:00', 'Carlos Suporte', 'em_uso'
FROM agendamentos a JOIN usuarios u ON a.id_professor = u.id
WHERE u.email = 'professor@uniceplac.edu.br' AND a.data_reserva = CURRENT_DATE AND a.turno = 'Vespertino' LIMIT 1;

INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, funcionario_entrega, status)
SELECT a.id, 'Prof. João Mendes', 'Laboratório de Redes', CURRENT_DATE, '(62) 98888-7777', '14:40:00', '18:00:00', 'Carlos Suporte', 'em_uso'
FROM agendamentos a JOIN usuarios u ON a.id_professor = u.id
WHERE u.email = 'joao@uniceplac.edu.br' AND a.data_reserva = CURRENT_DATE AND a.turno = 'Vespertino' LIMIT 1;

INSERT INTO chamados_suporte (id_professor, professor_nome, laboratorio, mensagem, status, data_hora)
SELECT u.id, v.nome, v.lab, v.msg, v.status::status_sos, NOW() + v.offset
FROM (VALUES
  ('professor@uniceplac.edu.br','Prof. Ana Silva','Laboratório de Informática 01','Computador posição 12 não inicia. Turma de Programação Web aguardando.','pendente', INTERVAL '-12 minutes'),
  ('joao@uniceplac.edu.br','Prof. João Mendes','Laboratório de Redes','Projetor sem imagem — cabo HDMI testado, problema persiste.','pendente', INTERVAL '-35 minutes'),
  ('professor@uniceplac.edu.br','Prof. Ana Silva','Laboratório Multimídia','Som do sistema não funciona na sala. Aula de multimídia hoje.','pendente', INTERVAL '-2 hours'),
  ('joao@uniceplac.edu.br','Prof. João Mendes','Laboratório de Informática 02','Mouse sem funcionar em 3 bancadas do fundo.','pendente', INTERVAL '-4 hours'),
  ('professor@uniceplac.edu.br','Prof. Ana Silva','Laboratório de Informática 02','Wi-Fi instável — resolvido após reinício do roteador.','resolvido', INTERVAL '-1 day'),
  ('joao@uniceplac.edu.br','Prof. João Mendes','Laboratório Multimídia','Teclado quebrado bancada 5 — substituído.','resolvido', INTERVAL '-2 days'),
  ('professor@uniceplac.edu.br','Prof. Ana Silva','Laboratório de Redes','Switch da rack desligado — energia restabelecida.','resolvido', INTERVAL '-4 days'),
  ('joao@uniceplac.edu.br','Prof. João Mendes','Laboratório de Informática 01','Licença do software expirada — reativada.','resolvido', INTERVAL '-6 days'),
  ('professor@uniceplac.edu.br','Prof. Ana Silva','Laboratório de Informática 01','Ar-condicionado com vazamento — manutenção predial acionada.','resolvido', INTERVAL '-10 days')
) AS v(email, nome, lab, msg, status, offset)
JOIN usuarios u ON u.email = v.email;

INSERT INTO ensalamento (id_professor, id_disciplina, curso, bloco, andar, sala, categoria, turno)
SELECT u.id, v.disc, v.curso, v.bloco, v.andar, v.sala, v.cat, v.turno::turno_aula
FROM (VALUES
  ('professor@uniceplac.edu.br', 4, 'Ciência da Computação', 'A', '1º Andar', '201', 'Presencial', 'Matutino'),
  ('joao@uniceplac.edu.br', 5, 'Sistemas de Informação', 'B', '2º Andar', '102', 'Presencial', 'Matutino'),
  ('professor@uniceplac.edu.br', 2, 'Ciência da Computação', 'A', '1º Andar', '203', 'Presencial', 'Vespertino'),
  ('joao@uniceplac.edu.br', 3, 'Engenharia de Software', 'C', 'Térreo', '105', 'Presencial', 'Vespertino'),
  ('professor@uniceplac.edu.br', 4, 'Ciência da Computação', 'A', '1º Andar', '201', 'Presencial', 'Noturno'),
  ('joao@uniceplac.edu.br', 5, 'Sistemas de Informação', 'B', '2º Andar', '302', 'Presencial', 'Noturno'),
  ('professor@uniceplac.edu.br', 6, 'Análise e Desenvolvimento', 'A', '1º Andar', '104', 'EAD Polo Goiânia', 'Noturno')
) AS v(email, disc, curso, bloco, andar, sala, cat, turno)
JOIN usuarios u ON u.email = v.email;
