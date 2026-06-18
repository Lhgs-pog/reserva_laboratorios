<?php

declare(strict_types=1);

/**
 * Disponibilidade de laboratórios — reservas avulsas + grade fixa.
 */

function labhub_dia_semana_data(string $data_reserva): string
{
    $dias_map = [
        0 => 'Domingo', 1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta',
        4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado',
    ];

    return $dias_map[(int) date('w', strtotime($data_reserva))];
}

function labhub_periodos_conflitam(string $periodo_a, string $periodo_b): bool
{
    return $periodo_a === '1º e 2º Horários'
        || $periodo_b === '1º e 2º Horários'
        || $periodo_a === $periodo_b;
}

/**
 * @return string|false Mensagem de conflito ou false se livre
 */
function labhub_verifica_choque_horario(
    PDO $pdo,
    int $id_lab,
    string $data_reserva,
    string $turno,
    string $periodo,
    ?int $id_ignorar = null,
    bool $ignorar_grade_fixa = false,
    bool $incluir_pendentes = true
) {
    $statusSql = $incluir_pendentes ? "IN ('aprovado', 'pendente')" : "= 'aprovado'";
    $sql_ag = "SELECT a.periodo, a.status, u.nome AS professor
               FROM agendamentos a
               JOIN usuarios u ON a.id_professor = u.id
               WHERE a.id_laboratorio = ? AND a.data_reserva = ? AND a.turno = ? AND a.status {$statusSql}";
    $params_ag = [$id_lab, $data_reserva, $turno];

    if ($id_ignorar) {
        $sql_ag .= ' AND a.id != ?';
        $params_ag[] = $id_ignorar;
    }

    $stmt_ag = $pdo->prepare($sql_ag);
    $stmt_ag->execute($params_ag);
    $reservas = $stmt_ag->fetchAll(PDO::FETCH_ASSOC);

    foreach ($reservas as $row) {
        if (labhub_periodos_conflitam($periodo, (string) $row['periodo'])) {
            $tipo = ($row['status'] ?? '') === 'pendente' ? 'reserva pendente' : 'reserva aprovada';
            return "Já existe {$tipo} do(a) Prof. {$row['professor']} neste laboratório, dia e horário.";
        }
    }

    if ($ignorar_grade_fixa) {
        return false;
    }

    $dia_semana = labhub_dia_semana_data($data_reserva);
    $id_quadro_ativo = false;
    try {
        $id_quadro_ativo = $pdo->query('SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1')->fetchColumn();
    } catch (Exception $e) {
    }

    if ($id_quadro_ativo) {
        $stmt_qa = $pdo->prepare(
            'SELECT qa.horario, u.nome AS professor
             FROM quadro_aulas qa
             LEFT JOIN usuarios u ON qa.id_professor = u.id
             WHERE qa.id_quadro = ? AND qa.id_laboratorio = ? AND qa.dia_semana = ? AND qa.turno = ?'
        );
        $stmt_qa->execute([$id_quadro_ativo, $id_lab, $dia_semana, $turno]);
        $fixos = $stmt_qa->fetchAll(PDO::FETCH_ASSOC);

        foreach ($fixos as $fixo) {
            $h_fixo = (string) $fixo['horario'];
            if (labhub_periodos_conflitam($periodo, $h_fixo)) {
                $prof_grade = $fixo['professor'] ? 'Prof. ' . $fixo['professor'] : 'aula da grade';
                return "A grade fixa ocupa este lab em {$dia_semana} ({$turno}) — {$prof_grade}, horário {$h_fixo}.";
            }
        }
    }

    return false;
}

/**
 * @return list<array{id:int,nome:string,capacidade:int,label:string,status:string,motivo:string}>
 */
function labhub_labs_para_slot(
    PDO $pdo,
    string $data_reserva,
    string $turno,
    string $periodo,
    ?int $id_ignorar = null,
    bool $somente_livres = false
): array {
    $labs = $pdo->query('SELECT id, nome, capacidade FROM laboratorios ORDER BY nome ASC')->fetchAll(PDO::FETCH_ASSOC);
    $lista = [];

    foreach ($labs as $lab) {
        $idLab = (int) $lab['id'];
        $conflito = labhub_verifica_choque_horario($pdo, $idLab, $data_reserva, $turno, $periodo, $id_ignorar);
        $status = $conflito ? 'ocupado' : 'livre';

        if ($somente_livres && $status === 'ocupado') {
            continue;
        }

        $cap = (int) ($lab['capacidade'] ?? 0);
        $lista[] = [
            'id' => $idLab,
            'nome' => $lab['nome'],
            'capacidade' => $cap,
            'label' => $lab['nome'] . ' (Cap: ' . $cap . ')',
            'status' => $status,
            'motivo' => $conflito ?: '',
        ];
    }

    return $lista;
}
