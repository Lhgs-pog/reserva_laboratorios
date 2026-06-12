-- Chamados de demonstração para painel de suporte
INSERT INTO chamados_suporte (id_professor, professor_nome, laboratorio, mensagem, status)
SELECT u.id, u.nome, 'Laboratório de Informática 01',
       'Projetor não liga na sala. Preciso para aula às 19h.', 'pendente'
FROM usuarios u WHERE u.email = 'professor@uniceplac.edu.br'
ON CONFLICT DO NOTHING;

INSERT INTO chamados_suporte (id_professor, professor_nome, laboratorio, mensagem, status)
SELECT u.id, u.nome, 'Laboratório de Redes',
       'Ar-condicionado com vazamento. Ambiente muito quente.', 'pendente'
FROM usuarios u WHERE u.email = 'joao@uniceplac.edu.br'
ON CONFLICT DO NOTHING;
