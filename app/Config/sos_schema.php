<?php

function app_ensure_sos_schema(PDO $pdo): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    try {
        if ($driver === 'pgsql') {
            foreach (['em_andamento', 'aguardando_verificacao', 'nao_resolvido'] as $valor) {
                try {
                    $pdo->exec("ALTER TYPE status_sos ADD VALUE IF NOT EXISTS '{$valor}'");
                } catch (Throwable $e) {
                    // tipo já atualizado ou ambiente legado
                }
            }
        } else {
            $pdo->exec("ALTER TABLE chamados_suporte MODIFY COLUMN status ENUM(
                'pendente','em_andamento','aguardando_verificacao','resolvido','nao_resolvido'
            ) NOT NULL DEFAULT 'pendente'");
        }
    } catch (Throwable $e) {
        // coluna/tipo pode já estar correto
    }

    $colunas = [
        'observacao_interna' => $driver === 'pgsql' ? 'TEXT' : 'TEXT NULL',
        'resposta_professor' => $driver === 'pgsql' ? 'TEXT' : 'TEXT NULL',
        'id_atendente'       => $driver === 'pgsql' ? 'INTEGER REFERENCES usuarios(id)' : 'INT UNSIGNED NULL',
        'nome_atendente'     => $driver === 'pgsql' ? 'VARCHAR(150)' : 'VARCHAR(150) NULL',
        'atualizado_em'      => $driver === 'pgsql' ? 'TIMESTAMPTZ' : 'DATETIME NULL',
        'resolvido_em'       => $driver === 'pgsql' ? 'TIMESTAMPTZ' : 'DATETIME NULL',
        'ultimo_email_em'    => $driver === 'pgsql' ? 'TIMESTAMPTZ' : 'DATETIME NULL',
        'historico_log'      => $driver === 'pgsql' ? 'TEXT' : 'TEXT NULL',
    ];

    foreach ($colunas as $coluna => $tipo) {
        try {
            if ($driver === 'pgsql') {
                $pdo->exec("ALTER TABLE chamados_suporte ADD COLUMN IF NOT EXISTS {$coluna} {$tipo}");
            } else {
                $stmt = $pdo->query("SHOW COLUMNS FROM chamados_suporte LIKE " . $pdo->quote($coluna));
                if (!$stmt->fetch()) {
                    $pdo->exec("ALTER TABLE chamados_suporte ADD COLUMN {$coluna} {$tipo}");
                }
            }
        } catch (Throwable $e) {
            // ignora se já existir
        }
    }
}
