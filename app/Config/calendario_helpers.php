<?php

function labhub_cal_normalizar_evento(array $evento): array
{
    if (isset($evento['className']) && !isset($evento['classNames'])) {
        $cn = $evento['className'];
        $evento['classNames'] = is_array($cn) ? $cn : [$cn];
        unset($evento['className']);
    }
    return $evento;
}

function labhub_cal_normalizar_eventos(array $eventos): array
{
    return array_map('labhub_cal_normalizar_evento', $eventos);
}

function labhub_cal_eventos_json(array $eventos): string
{
    return json_encode(labhub_cal_normalizar_eventos($eventos), JSON_UNESCAPED_UNICODE);
}
