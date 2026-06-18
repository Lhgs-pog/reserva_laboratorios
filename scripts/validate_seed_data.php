<?php

declare(strict_types=1);

/**
 * Valida volume de dados demo por perfil/módulo.
 * Uso: php scripts/validate_seed_data.php
 */

$root = dirname(__DIR__);
require_once $root . '/app/Config/env.php';
app_load_env($root);
require_once $root . '/app/Config/session_bootstrap.php';
labhub_bootstrap_autoload();
require_once $root . '/app/Config/Database.php';

$pdo = App\Config\Database::getInstance()->getPDO();

$checks = [];
$failures = 0;

function countQ(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function check(string $label, int $got, int $min, array &$checks, int &$failures): void
{
    $ok = $got >= $min;
    if (!$ok) {
        $failures++;
    }
    $checks[] = [
        'label' => $label,
        'count' => $got,
        'min'   => $min,
        'ok'    => $ok,
    ];
}

// ── Usuários por perfil ─────────────────────────────────────────────────────
foreach (['coordenador', 'professor', 'suporte'] as $perfil) {
    $n = countQ($pdo, 'SELECT COUNT(*) FROM usuarios WHERE perfil = ?', [$perfil]);
    check("Usuários ({$perfil})", $n, 1, $checks, $failures);
}

// ── Cadastros base ──────────────────────────────────────────────────────────
check('Laboratórios', countQ($pdo, 'SELECT COUNT(*) FROM laboratorios'), 4, $checks, $failures);
check('Disciplinas', countQ($pdo, 'SELECT COUNT(*) FROM disciplinas'), 5, $checks, $failures);
check('Cursos', countQ($pdo, 'SELECT COUNT(*) FROM cursos'), 1, $checks, $failures);
check('Semestres', countQ($pdo, 'SELECT COUNT(*) FROM semestres'), 1, $checks, $failures);

// ── Coordenador ─────────────────────────────────────────────────────────────
check('Quadros de horário', countQ($pdo, 'SELECT COUNT(*) FROM quadros_horarios'), 1, $checks, $failures);
check('Aulas no quadro', countQ($pdo, 'SELECT COUNT(*) FROM quadro_aulas'), 10, $checks, $failures);
check('Reservas pendentes (coord.)', countQ($pdo, "SELECT COUNT(*) FROM agendamentos WHERE status = 'pendente'"), 3, $checks, $failures);
check('Reservas aprovadas', countQ($pdo, "SELECT COUNT(*) FROM agendamentos WHERE status = 'aprovado'"), 10, $checks, $failures);
check('Reservas rejeitadas', countQ($pdo, "SELECT COUNT(*) FROM agendamentos WHERE status = 'rejeitado'"), 1, $checks, $failures);

// ── Professor (Ana) ─────────────────────────────────────────────────────────
$anaId = countQ($pdo, "SELECT id FROM usuarios WHERE email = 'professor@uniceplac.edu.br' LIMIT 1");
if ($anaId > 0) {
    check('Reservas Prof. Ana', countQ($pdo, 'SELECT COUNT(*) FROM agendamentos WHERE id_professor = ?', [$anaId]), 5, $checks, $failures);
    check('Ensalamento Prof. Ana', countQ($pdo, 'SELECT COUNT(*) FROM ensalamento WHERE id_professor = ?', [$anaId]), 3, $checks, $failures);
    check('Chamados TI Prof. Ana', countQ($pdo, 'SELECT COUNT(*) FROM chamados_suporte WHERE id_professor = ?', [$anaId]), 3, $checks, $failures);
} else {
    check('Usuário professor@uniceplac.edu.br', 0, 1, $checks, $failures);
}

// ── Professor (João) ────────────────────────────────────────────────────────
$joaoId = countQ($pdo, "SELECT id FROM usuarios WHERE email = 'joao@uniceplac.edu.br' LIMIT 1");
if ($joaoId > 0) {
    check('Reservas Prof. João', countQ($pdo, 'SELECT COUNT(*) FROM agendamentos WHERE id_professor = ?', [$joaoId]), 5, $checks, $failures);
    check('Ensalamento Prof. João', countQ($pdo, 'SELECT COUNT(*) FROM ensalamento WHERE id_professor = ?', [$joaoId]), 3, $checks, $failures);
    check('Chamados TI Prof. João', countQ($pdo, 'SELECT COUNT(*) FROM chamados_suporte WHERE id_professor = ?', [$joaoId]), 3, $checks, $failures);
}

// ── Suporte ─────────────────────────────────────────────────────────────────
check('Chamados SOS pendentes', countQ($pdo, "SELECT COUNT(*) FROM chamados_suporte WHERE status = 'pendente'"), 4, $checks, $failures);
check('Chamados SOS resolvidos', countQ($pdo, "SELECT COUNT(*) FROM chamados_suporte WHERE status = 'resolvido'"), 4, $checks, $failures);
check('Chaves em uso hoje', countQ($pdo, "SELECT COUNT(*) FROM controle_chaves WHERE status = 'em_uso' AND data_uso = CURRENT_DATE"), 2, $checks, $failures);

// ── Lista de espera (opcional — seed pode não ter) ───────────────────────────
$listaEspera = countQ($pdo, 'SELECT COUNT(*) FROM lista_espera_laboratorio WHERE status = \'aguardando\'');
check('Lista de espera (aguardando)', $listaEspera, 2, $checks, $failures);

// ── Saída ───────────────────────────────────────────────────────────────────
echo str_pad('MÓDULO', 36) . str_pad('QTD', 6) . str_pad('MÍN', 6) . "STATUS\n";
echo str_repeat('-', 60) . "\n";

foreach ($checks as $c) {
    $status = $c['ok'] ? 'OK' : 'FALTA';
    if (!empty($c['info'])) {
        $status = $c['info'];
    }
    echo str_pad($c['label'], 36)
        . str_pad((string) $c['count'], 6)
        . str_pad((string) $c['min'], 6)
        . $status . "\n";
}

echo "\n";
if ($failures > 0) {
    fwrite(STDERR, "{$failures} verificação(ões) abaixo do esperado.\n");
    exit(1);
}

echo "Todos os módulos principais têm dados suficientes.\n";
exit(0);
