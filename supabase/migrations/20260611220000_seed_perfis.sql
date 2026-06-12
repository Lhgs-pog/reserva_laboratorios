-- Perfis de demonstração — senha: password (bcrypt)
INSERT INTO usuarios (nome, email, senha, perfil, email_verificado) VALUES
  ('Prof. Ana Silva', 'professor@uniceplac.edu.br',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'professor', TRUE),
  ('Prof. João Mendes', 'joao@uniceplac.edu.br',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'professor', TRUE),
  ('Técnico Carlos Suporte', 'suporte@uniceplac.edu.br',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'suporte', TRUE)
ON CONFLICT (email) DO NOTHING;

-- Reservas de exemplo para o professor Ana (lab 1, disciplina Programação Web = id 4)
INSERT INTO agendamentos (id_laboratorio, id_professor, id_disciplina, turno, periodo, data_reserva, status)
SELECT 1, u.id, 4, 'Noturno', '1º Horário', CURRENT_DATE + 2, 'aprovado'
FROM usuarios u WHERE u.email = 'professor@uniceplac.edu.br'
ON CONFLICT DO NOTHING;

INSERT INTO agendamentos (id_laboratorio, id_professor, id_disciplina, turno, periodo, data_reserva, status)
SELECT 3, u.id, 5, 'Noturno', '2º Horário', CURRENT_DATE + 4, 'pendente'
FROM usuarios u WHERE u.email = 'professor@uniceplac.edu.br'
ON CONFLICT DO NOTHING;
