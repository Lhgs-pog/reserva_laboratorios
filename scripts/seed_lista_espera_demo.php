<?php

declare(strict_types=1);

/**
 * Seed demo: slot lotado + 2 professores na fila de espera.
 * Uso: php scripts/seed_lista_espera_demo.php
 */

$root = dirname(__DIR__);
require_once $root . '/app/Config/env.php';
app_load_env($root);
require_once $root . '/app/Config/session_bootstrap.php';
labhub_bootstrap_autoload();
require_once $root . '/app/Config/disponibilidade_helpers.php';

$pdo = App\Config\Database::getInstance()->getPDO();
$demoDate = date('Y-m-d', strtotime('+7 days'));
$turno = 'Vespertino';
$periodo = '1º Horário';

echo "Seed lista de espera — slot {$demoDate} / {$turno} / {$periodo}\n";

$ocupar = [
    ['lab' => 1, 'email' => 'professor@uniceplac.edu.br', 'disc' => 4],
    ['lab' => 2, 'email' => 'joao@uniceplac.edu.br', 'disc' => 2],
    ['lab' => 3, 'email' => 'professorgirafales@uniceplac.edu.br', 'disc' => 1],
    ['lab' => 4, 'email' => 'teste999@uniceplac.edu.br', 'disc' => 3],
];

$stmtIns = $pdo->prepare(
    "INSERT INTO agendamentos (id_laboratorio, id_professor, id_disciplina, data_reserva, turno, periodo, status)
     SELECT ?, u.id, ?, ?::date, ?::turno_aula, ?::periodo_horario, 'aprovado'::status_agendamento
     FROM usuarios u WHERE u.email = ?
     ON CONFLICT (id_laboratorio, data_reserva, turno, periodo) DO NOTHING"
);

$isPgsql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
if (!$isPgsql) {
    $stmtIns = $pdo->prepare(
        "INSERT IGNORE INTO agendamentos (id_laboratorio, id_professor, id_disciplina, data_reserva, turno, periodo, status)
         SELECT ?, u.id, ?, ?, ?, ?, 'aprovado' FROM usuarios u WHERE u.email = ?"
    );
}

foreach ($ocupar as $row) {
    if ($isPgsql) {
        $stmtIns->execute([$row['lab'], $row['disc'], $demoDate, $turno, $periodo, $row['email']]);
    } else {
        $stmtIns->execute([$row['lab'], $row['disc'], $demoDate, $turno, $periodo, $row['email']]);
    }
}

$livres = array_filter(
    labhub_labs_para_slot($pdo, $demoDate, $turno, $periodo, null, true),
    static fn($l) => ($l['status'] ?? '') === 'livre'
);
echo 'Labs livres no slot: ' . count($livres) . "\n";

$fila = [
    ['email' => 'maria@uniceplac.edu.br', 'disc' => 4],
    ['email' => 'vinicius.correia@esoftware.uniceplac.edu.br', 'disc' => 2],
    ['email' => 'teste2@uniceplac.edu.br', 'disc' => 5],
];

require_once $root . '/app/Config/lista_espera_schema.php';
app_ensure_lista_espera_schema($pdo);

$stmtFila = $pdo->prepare(
    $isPgsql
        ? "INSERT INTO lista_espera_laboratorio (id_professor, id_disciplina, data_reserva, turno, periodo, status, criado_em)
           SELECT u.id, ?, ?::date, ?::turno_aula, ?::periodo_horario, 'aguardando', NOW() - (? || ' minutes')::interval
           FROM usuarios u WHERE u.email = ?
           ON CONFLICT (id_professor, data_reserva, turno, periodo)
           DO UPDATE SET id_disciplina = EXCLUDED.id_disciplina, status = 'aguardando', criado_em = EXCLUDED.criado_em"
        : "INSERT INTO lista_espera_laboratorio (id_professor, id_disciplina, data_reserva, turno, periodo, status, criado_em)
           SELECT u.id, ?, ?, ?, ?, 'aguardando', DATE_SUB(NOW(), INTERVAL ? MINUTE)
           FROM usuarios u WHERE u.email = ?
           ON DUPLICATE KEY UPDATE id_disciplina = VALUES(id_disciplina), status = 'aguardando', criado_em = VALUES(criado_em)"
);

$minutos = 0;
$inseridos = 0;
foreach ($fila as $row) {
    if ($inseridos >= 2) {
        break;
    }
    $minutos += 5;
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$row['email']]);
    $uid = (int) $stmt->fetchColumn();
    if ($uid <= 0) {
        echo "SKIP fila: usuário não encontrado — {$row['email']}\n";
        continue;
    }
    if ($isPgsql) {
        $stmtFila->execute([$row['disc'], $demoDate, $turno, $periodo, (string) $minutos, $row['email']]);
    } else {
        $stmtFila->execute([$row['disc'], $demoDate, $turno, $periodo, $minutos, $row['email']]);
    }
    echo "Fila: {$row['email']} ({$minutos} min atrás)\n";
    $inseridos++;
}

$total = (int) $pdo->query("SELECT COUNT(*) FROM lista_espera_laboratorio WHERE status = 'aguardando'")->fetchColumn();
echo "\nTotal na fila (aguardando): {$total}\n";
exit($total >= 2 ? 0 : 1);
