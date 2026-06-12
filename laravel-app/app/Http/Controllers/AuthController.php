<?php
/**
 * Auth Controller - Laravel Style
 */

namespace LaravelApp\Http\Controllers;

use LaravelApp\Models\User;

class AuthController extends Controller {
    /**
     * Show login form
     */
    public function showLogin() {
        if ($this->isAuthenticated()) {
            return $this->redirect('/painel/professor');
        }

        return $this->view('auth.login', [
            'erro' => $_GET['msg'] === 'erro' ? 'Email ou senha incorretos!' : '',
            'sucesso' => $_GET['msg'] === 'cadastro_ok' ? 'Cadastro realizado com sucesso!' : '',
        ]);
    }

    /**
     * Store login
     */
    public function store() {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        $usuario = User::findByEmail($email);

        if ($usuario && $usuario->verifyPassword($senha)) {
            if (!$usuario->email_verificado) {
                return $this->redirectWithMessage('/login', 'Confirme seu email antes de fazer login', 'error');
            }

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario->id;
            $_SESSION['nome'] = $usuario->nome;
            $_SESSION['perfil'] = $usuario->perfil;
            $_SESSION['foto'] = $usuario->foto_perfil;

            $destinos = [
                'professor' => '/painel/professor',
                'coordenador' => '/painel/coordenador',
                'suporte' => '/painel/suporte',
            ];

            $url = $destinos[$usuario->perfil] ?? '/';
            return $this->redirect($url);
        }

        return $this->redirectWithMessage('/login', 'Email ou senha incorretos!', 'error');
    }

    /**
     * Show signup form
     */
    public function showCadastro() {
        if ($this->isAuthenticated()) {
            return $this->redirect('/painel/professor');
        }

        return $this->view('auth.cadastro', ['mensagem' => '']);
    }

    /**
     * Store signup
     */
    public function storeCadastro() {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        // Validações
        if (!str_ends_with($email, '@uniceplac.edu.br')) {
            return $this->redirectWithMessage('/cadastro', 'Use apenas email institucional (@uniceplac.edu.br)', 'error');
        }

        if ($senha !== $confirmar) {
            return $this->redirectWithMessage('/cadastro', 'As senhas não coincidem', 'error');
        }

        if (strlen($senha) < 8) {
            return $this->redirectWithMessage('/cadastro', 'Senha deve ter no mínimo 8 caracteres', 'error');
        }

        if (User::emailExists($email)) {
            return $this->redirectWithMessage('/cadastro', 'Este email já está cadastrado', 'error');
        }

        try {
            User::createWithPassword([
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha,
                'perfil' => 'professor',
                'email_verificado' => 1,
            ]);

            return $this->redirectWithMessage('/login', 'Cadastro realizado com sucesso!', 'success');
        } catch (\Exception $e) {
            return $this->redirectWithMessage('/cadastro', 'Erro ao cadastrar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        return $this->redirect('/');
    }
}
?>
