<?php

/** Ordenação de turno compatível com MySQL (FIELD) e PostgreSQL (CASE). */
function app_sql_order_turno(string $column): string
{
    if (app_db_driver() === 'pgsql') {
        return "CASE {$column} WHEN 'Matutino' THEN 1 WHEN 'Vespertino' THEN 2 WHEN 'Noturno' THEN 3 ELSE 4 END";
    }
    return "FIELD({$column}, 'Matutino', 'Vespertino', 'Noturno')";
}
