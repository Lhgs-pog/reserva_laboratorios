<?php
/**
 * Script de fragmentação do painel_coordenador.php legado para o padrão MVC.
 * Mantém a interface e lógicas intactas, apenas organiza em arquivos separados.
 */

$origem = 'painel_coordenador.php';
$conteudo = file_get_contents($origem);

if (!is_dir('app/Views/coordenador')) {
    mkdir('app/Views/coordenador', 0777, true);
}
if (!is_dir('app/Views/coordenador/abas')) {
    mkdir('app/Views/coordenador/abas', 0777, true);
}

// 1. Separar a lógica PHP do HTML
$partes = explode('<!DOCTYPE html>', $conteudo);
$php_logic = $partes[0];
$html_view = '<!DOCTYPE html>' . $partes[1];

// Substituir requires legados para se adequarem ao Controller
$php_logic = str_replace("session_start();", "", $php_logic);
$php_logic = str_replace("require 'conexao.php';", "", $php_logic);
$php_logic = str_replace("require 'Agendamento.php';", "", $php_logic);

// Vamos salvar a view base (HTML global sem as seções)
// Para extrair as seções HTML com segurança (já que tem <div> aninhadas), 
// vamos usar um método de busca e extração.
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
    'sessao-ensalamento',
    'sessao-aprovacoes'
];

$view_modificada = $html_view;

foreach ($secoes as $id_secao) {
    // Busca a posição inicial da div da aba
    $busca = '<div id="' . $id_secao . '" class="content-section">';
    $pos_inicio = strpos($view_modificada, $busca);
    
    if ($pos_inicio !== false) {
        // Encontra o fim correspondente dessa div principal
        $pos_atual = $pos_inicio + strlen('<div');
        $profundidade = 1;
        $tamanho_texto = strlen($view_modificada);
        
        while ($profundidade > 0 && $pos_atual < $tamanho_texto) {
            $pos_proxima_div = strpos($view_modificada, '<div', $pos_atual);
            $pos_proximo_fechamento = strpos($view_modificada, '</div>', $pos_atual);
            
            if ($pos_proximo_fechamento === false) break;
            
            if ($pos_proxima_div !== false && $pos_proxima_div < $pos_proximo_fechamento) {
                // Achou uma div abrindo antes da próxima fechando
                $profundidade++;
                $pos_atual = $pos_proxima_div + 4;
            } else {
                // Achou uma div fechando
                $profundidade--;
                $pos_atual = $pos_proximo_fechamento + 6;
            }
        }
        
        $pos_fim = $pos_atual;
        $tamanho_secao = $pos_fim - $pos_inicio;
        
        // Extrai o conteúdo da aba
        $conteudo_secao = substr($view_modificada, $pos_inicio, $tamanho_secao);
        
        // Salva em um arquivo separado na pasta abas/
        file_put_contents('app/Views/coordenador/abas/' . $id_secao . '.php', $conteudo_secao);
        
        // Substitui na view principal por um require
        $replace = "<?php require __DIR__ . '/abas/" . $id_secao . ".php'; ?>";
        $view_modificada = substr_replace($view_modificada, $replace, $pos_inicio, $tamanho_secao);
    }
}

// Salva a view principal limpa
file_put_contents('app/Views/coordenador/painel.php', $view_modificada);

// Monta o novo CoordenadorController.php
$controller_template = "<?php\nnamespace App\\Controllers;\n\nuse App\\Models\\Agendamento as AgendamentoModel;\nuse App\\Models\\User;\nuse PDO;\nuse PDOException;\n\nclass CoordenadorController extends BaseController {\n";
$controller_template .= "    public function index() {\n";
$controller_template .= "        // Pegar a instância PDO global (usada no código legado)\n";
$controller_template .= "        \$pdo = \\App\\Config\\Database::getInstance()->getPDO();\n";
$controller_template .= "        \$this->requirePerfil('coordenador');\n";
$controller_template .= "        \n";
$controller_template .= "        // === LÓGICA LEGADA EXTRAÍDA ===\n";
$controller_template .= preg_replace('/<\?php\s*/', '', $php_logic, 1);
$controller_template .= "\n        // Retorna todas as variáveis geradas para a view\n";
$controller_template .= "        \$vars = get_defined_vars();\n";
$controller_template .= "        unset(\$vars['pdo'], \$vars['this']);\n";
$controller_template .= "        return \$this->render('coordenador/painel', \$vars);\n";
$controller_template .= "    }\n";
$controller_template .= "}\n?>";

file_put_contents('app/Controllers/CoordenadorController.php', $controller_template);

// Substitui o arquivo raiz (painel_coordenador.php) pelo roteador MVC
$router_fallback = "<?php\n// Redirecionamento MVC. Arquivo legado substituído.\nrequire __DIR__ . '/app/router.php';\n?>";
file_put_contents('painel_coordenador.php', $router_fallback);

echo "Refatoração concluída com sucesso! Fragmentado em 12 arquivos.\n";
?>
