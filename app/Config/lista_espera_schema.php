<?php

declare(strict_types=1);

function app_ensure_lista_espera_schema(PDO $pdo): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    try {
        if ($driver === 'pgsql') {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS lista_espera_laboratorio (
                    id SERIAL PRIMARY KEY,
                    id_professor INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                    id_disciplina INTEGER NOT NULL REFERENCES disciplinas(id) ON DELETE CASCADE,
                    data_reserva DATE NOT NULL,
                    turno turno_aula NOT NULL,
                    periodo periodo_horario NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'aguardando',
                    criado_em TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                    email_enviado_em TIMESTAMPTZ,
                    UNIQUE (id_professor, data_reserva, turno, periodo)
                )"
            );
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_lista_espera_slot ON lista_espera_laboratorio (data_reserva, turno, periodo, status)');
        } else {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS lista_espera_laboratorio (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    id_professor INT UNSIGNED NOT NULL,
                    id_disciplina INT UNSIGNED NOT NULL,
                    data_reserva DATE NOT NULL,
                    turno ENUM('Matutino','Vespertino','Noturno') NOT NULL,
                    periodo ENUM('1º e 2º Horários','1º Horário','2º Horário') NOT NULL,
                    status ENUM('aguardando','cancelado','atendido') NOT NULL DEFAULT 'aguardando',
                    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    email_enviado_em DATETIME NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_lista_espera_prof_slot (id_professor, data_reserva, turno, periodo),
                    KEY idx_lista_espera_slot (data_reserva, turno, periodo, status),
                    CONSTRAINT fk_le_professor FOREIGN KEY (id_professor) REFERENCES usuarios (id) ON DELETE CASCADE,
                    CONSTRAINT fk_le_disciplina FOREIGN KEY (id_disciplina) REFERENCES disciplinas (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
    } catch (Throwable $e) {
        // tabela já existe ou ambiente legado
    }
}
