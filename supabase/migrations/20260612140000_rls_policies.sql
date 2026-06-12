-- RLS para demo web (leitura pública, escrita autenticada via service role nas APIs)
ALTER TABLE usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE laboratorios ENABLE ROW LEVEL SECURITY;
ALTER TABLE disciplinas ENABLE ROW LEVEL SECURITY;
ALTER TABLE agendamentos ENABLE ROW LEVEL SECURITY;
ALTER TABLE chamados_suporte ENABLE ROW LEVEL SECURITY;
ALTER TABLE controle_chaves ENABLE ROW LEVEL SECURITY;
ALTER TABLE ensalamento ENABLE ROW LEVEL SECURITY;
ALTER TABLE quadros_horarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE quadro_aulas ENABLE ROW LEVEL SECURITY;

CREATE POLICY "leitura_publica_usuarios" ON usuarios FOR SELECT USING (true);
CREATE POLICY "leitura_publica_laboratorios" ON laboratorios FOR SELECT USING (true);
CREATE POLICY "leitura_publica_disciplinas" ON disciplinas FOR SELECT USING (true);
CREATE POLICY "leitura_publica_agendamentos" ON agendamentos FOR SELECT USING (true);
CREATE POLICY "leitura_publica_chamados" ON chamados_suporte FOR SELECT USING (true);
CREATE POLICY "leitura_publica_chaves" ON controle_chaves FOR SELECT USING (true);
CREATE POLICY "leitura_publica_ensalamento" ON ensalamento FOR SELECT USING (true);
CREATE POLICY "leitura_publica_quadros" ON quadros_horarios FOR SELECT USING (true);
CREATE POLICY "leitura_publica_quadro_aulas" ON quadro_aulas FOR SELECT USING (true);

CREATE POLICY "insert_agendamentos" ON agendamentos FOR INSERT WITH CHECK (true);
CREATE POLICY "update_agendamentos" ON agendamentos FOR UPDATE USING (true);
CREATE POLICY "insert_chamados" ON chamados_suporte FOR INSERT WITH CHECK (true);
CREATE POLICY "update_chamados" ON chamados_suporte FOR UPDATE USING (true);
