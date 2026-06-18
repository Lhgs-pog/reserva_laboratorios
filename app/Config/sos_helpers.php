<?php

function sos_status_opcoes(): array
{
    return [
        'pendente'               => 'Aguardando atendimento',
        'em_andamento'           => 'Em andamento',
        'aguardando_verificacao' => 'Aguardando verificação',
        'resolvido'              => 'Resolvido',
        'nao_resolvido'          => 'Não resolvido',
    ];
}

function sos_status_ativos(): array
{
    return ['pendente', 'em_andamento', 'aguardando_verificacao'];
}

function sos_status_encerrados(): array
{
    return ['resolvido', 'nao_resolvido'];
}

function sos_status_label(string $status): string
{
    return sos_status_opcoes()[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function sos_status_badge_class(string $status): string
{
    return match ($status) {
        'pendente'               => 'bg-warning',
        'em_andamento'           => 'bg-primary',
        'aguardando_verificacao' => 'bg-info',
        'resolvido'              => 'bg-success',
        'nao_resolvido'          => 'bg-secondary',
        default                  => 'bg-secondary',
    };
}

function sos_render_badge(string $status): string
{
    $label = htmlspecialchars(sos_status_label($status));
    $class = sos_status_badge_class($status);
    return '<span class="badge ' . $class . ' rounded-pill px-3">' . $label . '</span>';
}

function sos_sql_in_ativos(string $col = 'status'): string
{
    $vals = array_map(static fn($s) => "'" . $s . "'", sos_status_ativos());
    return $col . ' IN (' . implode(',', $vals) . ')';
}

function sos_sql_in_encerrados(string $col = 'status'): string
{
    $vals = array_map(static fn($s) => "'" . $s . "'", sos_status_encerrados());
    return $col . ' IN (' . implode(',', $vals) . ')';
}

require_once __DIR__ . '/sos_historico_helpers.php';
