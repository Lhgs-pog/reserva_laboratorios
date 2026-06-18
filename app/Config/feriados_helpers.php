<?php
require_once __DIR__ . '/../Services/FeriadosService.php';

use App\Services\FeriadosService;

function labhub_feriados_eventos_calendario(?int $ano = null): array
{
    $ano = $ano ?? (int) date('Y');
    $svc = new FeriadosService();
    return $svc->eventosFullCalendar($ano - 1, $ano + 1);
}

function labhub_feriados_mapa_datas(?int $ano = null): array
{
    $ano = $ano ?? (int) date('Y');
    $svc = new FeriadosService();
    return $svc->mapaDatasEspeciaisRange($ano - 1, $ano + 1);
}

function labhub_feriados_mapa_datas_json(?int $ano = null): string
{
    return json_encode(labhub_feriados_mapa_datas($ano), JSON_UNESCAPED_UNICODE);
}
