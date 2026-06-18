-- Fluxo completo de chamados SOS (status, observações, resposta ao professor)

ALTER TYPE status_sos ADD VALUE IF NOT EXISTS 'em_andamento';
ALTER TYPE status_sos ADD VALUE IF NOT EXISTS 'aguardando_verificacao';
ALTER TYPE status_sos ADD VALUE IF NOT EXISTS 'nao_resolvido';

ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS observacao_interna TEXT;
ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS resposta_professor TEXT;
ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS id_atendente INTEGER REFERENCES usuarios(id);
ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS nome_atendente VARCHAR(150);
ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMPTZ DEFAULT NOW();
ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS resolvido_em TIMESTAMPTZ;
ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS ultimo_email_em TIMESTAMPTZ;
