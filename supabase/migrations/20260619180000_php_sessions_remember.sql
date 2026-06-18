-- Sessões PHP no banco (sobrevivem a deploy) + tokens "Lembrar-me"

CREATE TABLE IF NOT EXISTS php_sessions (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT NOT NULL DEFAULT '',
    last_access TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS auth_remember_tokens (
    id SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMPTZ NOT NULL,
    criado_em TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_remember_usuario ON auth_remember_tokens(id_usuario);
CREATE INDEX IF NOT EXISTS idx_remember_expires ON auth_remember_tokens(expires_at);
