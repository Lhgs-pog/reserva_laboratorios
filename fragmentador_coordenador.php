<?php
$file = 'painel_coordenador.php';
$conteudo = file_get_contents($file);

// 1. Separar lógica de cabeçalho (PHP) do View (HTML)
$separador_view = '<!DOCTYPE html>';
$partes = explode($separador_view, $conteudo);

$php_logic = $partes[0];
$view_html = $separador_view . $partes[1];

// 2. Extrair seções (abas) do View
$secoes = [
    'sessao-calendario-geral',
    'sessao-relatorios',
    'sessao-historico-geral',
    'sessao-quadro-horario',
    'sessao-cursos',
    'sessao-semestres',
    'sessao-disciplinas',
    'sessao-labs',
    'sessao-locais',
    'sessao-ensalamento'
];

mkdir('app/Views/coordenador', 0755, true);
mkdir('app/Views/coordenador/abas', 0755, true);

$novo_html = $view_html;

foreach ($secoes as $secao) {
    // Regex para extrair a div completa da seção
    // Usa uma abordagem simples: encontra a div inicial, e depois conta divs aninhadas (forma aproximada)
    // Para simplificar e ser seguro com regex, vamos encontrar o inicio <div id="sessao-nome" ...
    // e capturar até antes do próximo <div id="sessao- ou até o fim
    
    // Expressão regular com lookahead
    $padrao = '/(<div[^>]+id="' . preg_quote($secao, '/') . '"[^>]*class="content-section"[^>]*>.*?(?=<!-- FIM ' . preg_quote($secao, '/') . ' -->|<div[^>]+id="sessao-|<\/body>|<main[^>]*>|<\/div>\s*<\/main>|<\/div>\s*<\/div>\s*<\/div>\s*<script))/is';
    
    // Como o arquivo original pode não ter delimitadores tão claros para regex que processa HTML aninhado,
    // a forma mais segura em PHP é usar strpos e extrair pelo balanceamento de tags.
    // Mas para manter a segurança do sistema legado (não alterar a estrutura visual),
    // vamos separar a lógica PHP e deixar o view.
}

// Vamos implementar a fragmentação na prática
// Primeiro criamos as pastas
echo "Pastas criadas.\n";

// Em vez de arriscar um regex complexo que pode quebrar a div (já que HTML aninhado não é regular),
// vamos dividir o arquivo em dois: app/Controllers/CoordenadorController.php e app/Views/coordenador/painel.php
// e no painel.php manteremos o HTML, e faremos refatorações controladas.
