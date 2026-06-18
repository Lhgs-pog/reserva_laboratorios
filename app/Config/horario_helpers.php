<?php

function labhub_horario_intervalo(string $turno, string $periodo): array
{
    $start = '00:00';
    $end = '01:00';

    if ($turno === 'Matutino') {
        if ($periodo === '1º Horário') {
            $start = '08:20';
            $end = '10:00';
        } elseif ($periodo === '2º Horário') {
            $start = '10:15';
            $end = '11:55';
        } else {
            $start = '08:20';
            $end = '11:55';
        }
    } elseif ($turno === 'Vespertino') {
        if ($periodo === '1º Horário') {
            $start = '14:20';
            $end = '16:00';
        } elseif ($periodo === '2º Horário') {
            $start = '16:20';
            $end = '18:00';
        } else {
            $start = '14:20';
            $end = '18:00';
        }
    } elseif ($turno === 'Noturno') {
        if ($periodo === '1º Horário') {
            $start = '19:20';
            $end = '21:00';
        } elseif ($periodo === '2º Horário') {
            $start = '21:10';
            $end = '22:50';
        } else {
            $start = '19:20';
            $end = '22:50';
        }
    }

    return [$start, $end];
}

function labhub_horario_label(string $turno, string $periodo): string
{
    [$start, $end] = labhub_horario_intervalo($turno, $periodo);
    return $start . '–' . $end;
}

function labhub_horario_intervalo_sql(string $turno, string $periodo): array
{
    [$start, $end] = labhub_horario_intervalo($turno, $periodo);
    return [$start . ':00', $end . ':00'];
}
