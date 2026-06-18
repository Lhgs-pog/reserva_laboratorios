<?php
namespace App\Controllers;

use App\Services\MailService;
use App\Services\UsuarioService;
use Illuminate\Http\Request;

class AuthController extends BaseController {

    protected Request $request;

    /**
     * Aceita o Request via injeção de dependência (feita pelo router).
     * Fallback: captura da requisição global se não for injetado.
     */
    public function __construct(?Request $request = null) {
        $this->request = $request ?? Request::capture();
    }

    /**
     * Página de login
     */
    public function login() {
        if ($this->request->isMethod('post')) {
            return $this->processLogin();
        }

        // Lê mensagens da sessão PHP nativa (Illuminate session não está disponível
        // sem o kernel completo do Laravel — usamos $_SESSION diretamente).
        $erro = '';
        $sucesso = '';

        if (isset($_SESSION['error'])) {
            $erro = $_SESSION['error'];
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            $sucesso = $_SESSION['success'];
            unset($_SESSION['success']);
        }

        if ($this->request->query('msg') == 'email_confirmado') {
            $sucesso = 'E-mail confirmado! Agora faça login com a sua senha.';
        } elseif ($this->request->query('msg') == 'acesso_coordenador') {
            $erro = 'O acesso é criado pela coordenação. Peça ao coordenador para cadastrar seu e-mail e enviar a confirmação.';
        }

        $redirect = trim((string) $this->request->query('redirect', ''));

        return compact('erro', 'sucesso', 'redirect');
    }

    /**
     * Processa login via email/senha
     */
    private function processLogin() {
        $email = trim($this->request->input('email'));
        $senha_digitada = $this->request->input('senha');

        $pdo = \App\Config\Database::getInstance()->getPDO();
        $stmt = $pdo->prepare('SELECT id, nome, email, senha, perfil, foto_perfil FROM usuarios WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

        $hash = $usuario['senha'] ?? '';
        if ($usuario && is_string($hash) && $hash !== '' && password_verify($senha_digitada, $hash)) {
            $stmtVer = $pdo->prepare('SELECT email_verificado FROM usuarios WHERE id = ? LIMIT 1');
            $stmtVer->execute([(int) $usuario['id']]);
            $emailVerificado = $stmtVer->fetchColumn();
            if ($emailVerificado === false || $emailVerificado === 0 || $emailVerificado === '0' || $emailVerificado === 'f') {
                $this->redirectWithError(
                    'index.php',
                    'Confirme seu e-mail pelo link enviado antes de entrar. Verifique também a pasta de spam.'
                );
            }

            $_SESSION['usuario_id']  = $usuario['id'];
            $_SESSION['nome']        = $usuario['nome'];
            $_SESSION['email']       = $usuario['email'];
            $_SESSION['perfil']      = $usuario['perfil'];
            $_SESSION['foto_perfil'] = $usuario['foto_perfil'] ?? null;

            if ($this->request->input('lembrar_me')) {
                try {
                    labhub_issue_remember_token($pdo, (int) $usuario['id']);
                } catch (\Throwable $e) {
                    error_log('[AuthController] remember-me: ' . $e->getMessage());
                }
            }

            $returnUrl = trim((string) $this->request->input('redirect', ''));
            $url = labhub_login_destination((string) $usuario['perfil'], $returnUrl !== '' ? $returnUrl : null);
            $this->redirect($url);
        } elseif ($usuario && (empty($usuario['senha']) || !is_string($usuario['senha']))) {
            $this->redirectWithError('index.php', 'Sua conta ainda não tem senha. Use «Esqueci minha senha» ou peça à coordenação para reenviar o link.');
        } else {
            $this->redirectWithError('index.php', "E-mail ou senha incorretos!");
        }
    }

    /**
     * Solicitar link de redefinição de senha (público)
     */
    public function esqueciSenha() {
        $mensagem = '';
        $tipo = '';

        if ($this->request->isMethod('post')) {
            $email = trim($this->request->input('email', ''));
            if (!app_email_institucional_valido($email)) {
                $mensagem = 'Informe um e-mail válido.';
                $tipo = 'danger';
            } else {
                $usuarioSvc = new UsuarioService();
                $mailSvc    = new MailService();
                $user = $usuarioSvc->buscarPorEmail($email);
                if ($user && $mailSvc->isConfigured()) {
                    $token = $usuarioSvc->gerarTokenRedefinicao((int) $user['id']);
                    if (!$mailSvc->enviarRedefinicaoSenha($user['email'], $user['nome'], $token)) {
                        error_log('[AuthController] esqueciSenha falhou: ' . ($mailSvc->lastError() ?: 'sem detalhe'));
                    }
                }
                // Mesma mensagem sempre — não revela se o e-mail existe
                $mensagem = 'Se o e-mail estiver cadastrado, enviamos um link para redefinir a senha. Verifique também o spam.';
                $tipo = 'success';
            }
        }

        return compact('mensagem', 'tipo');
    }

    /**
     * Logout
     */
    public function logout() {
        if (isset($_SESSION['usuario_id'])) {
            try {
                $pdo = \App\Config\Database::getInstance()->getPDO();
                labhub_revoke_remember_tokens($pdo, (int) $_SESSION['usuario_id']);
            } catch (\Throwable $e) {
            }
        }
        labhub_clear_remember_cookie();
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->redirect('index.php');
    }

    /**
     * Verifica email por token
     */
    public function verificarEmail() {
        if (isset($_GET['token']) && trim($_GET['token']) !== '') {
            $token = trim($_GET['token']);

            try {
                $usuarioSvc = new UsuarioService();
                $usuario    = $usuarioSvc->buscarPorTokenVerificacao($token);

                if ($usuario) {
                    $usuarioSvc->confirmarEmail((int) $usuario['id']);
                    $this->redirect('index.php?msg=email_confirmado');
                }

                $linkSenha = $usuarioSvc->buscarPorTokenRedefinicao($token);
                if ($linkSenha && !empty($linkSenha['token_expira_em'])) {
                    $mensagem    = 'Este link é para <strong>criar ou redefinir senha</strong>, não para confirmar o e-mail. Peça à coordenação um novo e-mail de confirmação ou use «Esqueci minha senha» após confirmar.';
                    $tipo_alerta = 'warning';
                    return compact('mensagem', 'tipo_alerta');
                }
            } catch (\Exception $e) {
                error_log('[AuthController] verificarEmail: ' . $e->getMessage());
            }
        }

        $mensagem    = 'Link de verificação inválido ou expirado. Peça um novo envio à coordenação ou faça login se já confirmou antes.';
        $tipo_alerta = 'danger';

        return compact('mensagem', 'tipo_alerta');
    }

    /**
     * Redefinição de senha via link enviado por e-mail
     */
    public function redefinirSenha() {
        $usuarioSvc = new \App\Services\UsuarioService();
        $token      = trim($this->request->query('token', ''));
        $erro       = '';
        $sucesso    = '';
        $tokenValido = false;
        $usuario    = null;

        if ($token !== '') {
            $usuario = $usuarioSvc->buscarPorTokenRedefinicao($token);
            $tokenValido = (bool) $usuario;
        }

        if ($this->request->isMethod('post')) {
            $tokenPost = trim($this->request->input('token', ''));
            $senha     = $this->request->input('senha', '');
            $confirma  = $this->request->input('confirmar_senha', '');

            if ($senha !== $confirma) {
                $erro = 'As senhas não coincidem.';
            } elseif (strlen($senha) < 6) {
                $erro = 'A senha deve ter pelo menos 6 caracteres.';
            } elseif (!$usuarioSvc->redefinirSenhaPorToken($tokenPost, $senha)) {
                $erro = 'Link inválido ou expirado. Solicite um novo link à coordenação.';
            } else {
                $sucesso = 'Senha alterada com sucesso! Você já pode fazer login.';
                $tokenValido = false;
            }
        }

        return compact('token', 'erro', 'sucesso', 'tokenValido', 'usuario');
    }
}
?>
