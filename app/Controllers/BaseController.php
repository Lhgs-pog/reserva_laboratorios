<?php
namespace App\Controllers;

class BaseController {
    protected $data = [];

    /**
     * Renderiza uma view com dados
     */
    protected function render($view, $data = []) {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View não encontrada: $viewPath");
        }
        
        require $viewPath;
    }

    /**
     * Redireciona para uma URL
     */
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }

    /**
     * Redireciona com mensagem de sucesso
     */
    protected function redirectWithSuccess($url, $message) {
        $_SESSION['success'] = $message;
        $this->redirect($url);
    }

    /**
     * Redireciona com mensagem de erro
     */
    protected function redirectWithError($url, $message) {
        $_SESSION['error'] = $message;
        $this->redirect($url);
    }

    /**
     * Verifica se o usuário está autenticado
     */
    protected function requireAuth() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('index.php');
        }
    }

    /**
     * Verifica se o usuário tem um perfil específico
     */
    protected function requirePerfil($perfil) {
        $this->requireAuth();
        if ($_SESSION['perfil'] !== $perfil) {
            $this->redirect('index.php');
        }
    }

    /**
     * Obtém dados POST
     */
    protected function getPost($key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtém dados GET
     */
    protected function getGet($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Retorna JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
