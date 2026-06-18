<?php

function sos_historico_decode(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }

    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function sos_historico_encode(array $items): string
{
    return json_encode(array_values($items), JSON_UNESCAPED_UNICODE);
}

function sos_historico_adicionar(
    array $historico,
    string $tipo,
    string $autor,
    string $texto,
    ?string $status = null
): array {
    $historico[] = [
        'em'     => date('Y-m-d H:i:s'),
        'autor'  => $autor,
        'tipo'   => $tipo,
        'texto'  => $texto,
        'status' => $status,
    ];

    return $historico;
}

function sos_historico_lista(array $chamado): array
{
    $historico = sos_historico_decode($chamado['historico_log'] ?? null);
    if ($historico !== []) {
        return sos_historico_ordenar($historico);
    }

    $legado = [];
    $quando = $chamado['atualizado_em'] ?? $chamado['data_hora'] ?? null;
    $autor = trim((string) ($chamado['nome_atendente'] ?? 'Suporte TI'));
    $status = $chamado['status'] ?? null;

    if (!empty($chamado['observacao_interna'])) {
        $legado[] = [
            'em'     => $quando,
            'autor'  => $autor !== '' ? $autor : 'Suporte TI',
            'tipo'   => 'observacao_interna',
            'texto'  => (string) $chamado['observacao_interna'],
            'status' => $status,
        ];
    }

    if (!empty($chamado['resposta_professor'])) {
        $legado[] = [
            'em'     => $quando,
            'autor'  => $autor !== '' ? $autor : 'Suporte TI',
            'tipo'   => 'resposta_professor',
            'texto'  => (string) $chamado['resposta_professor'],
            'status' => $status,
        ];
    }

    return sos_historico_ordenar($legado);
}

function sos_historico_ordenar(array $historico): array
{
    usort($historico, static function ($a, $b) {
        return strcmp((string) ($b['em'] ?? ''), (string) ($a['em'] ?? ''));
    });

    return $historico;
}

function sos_historico_ultima_obs_interna(array $chamado): string
{
    foreach (sos_historico_lista($chamado) as $item) {
        if (($item['tipo'] ?? '') === 'observacao_interna' && trim((string) ($item['texto'] ?? '')) !== '') {
            return (string) $item['texto'];
        }
    }

    return trim((string) ($chamado['observacao_interna'] ?? ''));
}

function sos_historico_tipo_label(string $tipo): string
{
    return match ($tipo) {
        'status'               => 'Alteração de status',
        'observacao_interna'   => 'Observação interna',
        'resposta_professor'   => 'Resposta ao professor',
        'email'                => 'E-mail enviado',
        default                => ucfirst(str_replace('_', ' ', $tipo)),
    };
}

function sos_historico_tipo_icone(string $tipo): string
{
    return match ($tipo) {
        'status'               => 'bi-arrow-repeat',
        'observacao_interna'   => 'bi-journal-text',
        'resposta_professor'   => 'bi-chat-left-text',
        'email'                => 'bi-envelope-check',
        default                => 'bi-clock-history',
    };
}
