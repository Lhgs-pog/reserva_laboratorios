<?php
/**
 * API Routes
 * 
 * These routes are intended for API consumption and are stateless.
 */

return [
    'GET' => [
        '/api/agendamentos' => 'AgendamentoController@index',
        '/api/agendamentos/{id}' => 'AgendamentoController@show',
        '/api/sos/pendentes' => 'SOSController@pendentes',
    ],
    'POST' => [
        '/api/agendamentos' => 'AgendamentoController@store',
        '/api/agendamentos/{id}' => 'AgendamentoController@update',
        '/api/sos' => 'SOSController@store',
    ],
    'DELETE' => [
        '/api/agendamentos/{id}' => 'AgendamentoController@destroy',
    ],
];
?>
