<?php
/**
 * Base Controller - Laravel Style
 */

namespace LaravelApp\Http\Controllers;

class Controller {
    /**
     * Render a view with data
     */
    protected function view($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../../resources/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: $viewPath");
        }

        ob_start();
        include $viewPath;
        return ob_get_clean();
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url, $status = 302) {
        header("Location: $url", true, $status);
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Check authentication
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }

    /**
     * Check specific role
     */
    protected function requireRole($role) {
        $this->requireAuth();
        if ($_SESSION['perfil'] !== $role) {
            $this->redirect('/');
        }
    }

    /**
     * Check if authenticated
     */
    protected function isAuthenticated() {
        return isset($_SESSION['usuario_id']);
    }

    /**
     * Get authenticated user
     */
    protected function getUser() {
        if ($this->isAuthenticated()) {
            return \LaravelApp\Models\User::find($_SESSION['usuario_id']);
        }
        return null;
    }

    /**
     * Redirect with message
     */
    protected function redirectWithMessage($url, $message, $type = 'success') {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
        $this->redirect($url);
    }
}
?>
