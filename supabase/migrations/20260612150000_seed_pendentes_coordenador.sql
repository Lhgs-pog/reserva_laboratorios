-- Reservas pendentes para painel do coordenador (aprovar/recusar)
INSERT INTO usuarios (nome, email, senha, perfil, email_verificado)
VALUES (
  'Prof. Maria Santos',
  'maria@uniceplac.edu.br',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'professor',
  TRUE
)
ON CONFLICT (email) DO NOTHING;

INSERT INTO agendamentos (id_laboratorio, id_professor, id_disciplina, turno, periodo, data_reserva, status)
SELECT 1, u.id, 4, 'Noturno', '1º Horário', CURRENT_DATE + 3, 'pendente'
FROM usuarios u WHERE u.email = 'joao@uniceplac.edu.br'
ON CONFLICT DO NOTHING;

INSERT INTO agendamentos (id_laboratorio, id_professor, id_disciplina, turno, periodo, data_reserva, status)
SELECT 3, u.id, 2, 'Vespertino', '1º e 2º Horários', CURRENT_DATE + 5, 'pendente'
FROM usuarios u WHERE u.email = 'maria@uniceplac.edu.br'
ON CONFLICT DO NOTHING;
