<?php
/**
 * Web Routes
 * 
 * These routes receive session, CSRF protection, etc.
 */

// Exemplo de como as rotas funcionam no Laravel refatorado
return [
    'GET|POST' => [
        '/' => 'AuthController@showLogin',
        '/login' => 'AuthController@showLogin',
        '/login/store' => 'AuthController@store',
    ],
    'POST' => [
        '/cadastro' => 'AuthController@storeCadastro',
        '/logout' => 'AuthController@logout',
    ],
    'GET' => [
        '/cadastro' => 'AuthController@showCadastro',
        '/painel/professor' => 'PainelController@professor',
        '/painel/coordenador' => 'PainelController@coordenador',
        '/painel/suporte' => 'PainelController@suporte',
        '/agendamentos/{id}/edit' => 'AgendamentoController@edit',
    ],
];
?>
