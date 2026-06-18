-- Histórico de atualizações nos chamados SOS (observações, respostas, status)
ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS historico_log TEXT;
