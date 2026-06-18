<?php
namespace App\Services;

class MailService {
    private ?string $lastError = null;

    public function lastError(): ?string {
        return $this->lastError;
    }

    /** Provedor configurado (para exibir no painel). */
    public function provedorAtivo(): string {
        if ($this->resendApiKey() !== null) {
            return 'Resend';
        }
        if ($this->brevoApiKey() !== null) {
            return 'Brevo';
        }
        if (str_starts_with($this->smtpPassword(), 'xsmtpsib-')) {
            return 'Brevo SMTP';
        }
        return 'nenhum';
    }

    private function resendApiKey(): ?string {
        $key = app_env('RESEND_API_KEY', '');
        return ($key !== '' && str_starts_with($key, 're_')) ? $key : null;
    }

    private function brevoApiKey(): ?string {
        $apiKey = app_env('BREVO_API_KEY', '');
        if ($apiKey !== '' && str_starts_with($apiKey, 'xkeysib-')) {
            return $apiKey;
        }
        $key = app_env('MAIL_PASSWORD', '');
        if ($key !== '' && str_starts_with($key, 'xkeysib-')) {
            return $key;
        }
        return null;
    }

    private function smtpPassword(): string {
        $smtp = app_env('MAIL_SMTP_PASSWORD', '');
        if ($smtp !== '') {
            return $smtp;
        }
        $pass = app_env('MAIL_PASSWORD', '');
        if (str_starts_with($pass, 'xsmtpsib-')) {
            return $pass;
        }
        return $pass;
    }

    public function brevoWhitelistHint(): string {
        $v4 = app_env('BREVO_EGRESS_IPV4', '');
        $v6 = app_env('BREVO_EGRESS_IPV6', '');
        $base = 'https://app.brevo.com/security/authorised_ips';
        if ($v4 === '' && $v6 === '') {
            return $base;
        }
        $ips = array_filter([$v4, $v6]);
        return $base . ' — autorize: ' . implode(' e ', $ips);
    }

    private function sender(): array {
        $from = app_env('MAIL_FROM_ADDRESS', app_env('MAIL_USERNAME', 'contatovinicius.mends@gmail.com'));
        $name = app_env('MAIL_FROM_NAME', 'LabHub UNICEPLAC');
        return ['email' => $from, 'name' => $name];
    }

    /** @var list<string>|null */
    private static ?array $brevoSendersCache = null;

    /** E-mails remetentes ativos na conta Brevo (evita falso positivo HTTP 201). */
    private function brevoRemetentesAtivos(): array {
        if (self::$brevoSendersCache !== null) {
            return self::$brevoSendersCache;
        }
        self::$brevoSendersCache = [];
        $apiKey = $this->brevoApiKey();
        if ($apiKey === null) {
            return self::$brevoSendersCache;
        }
        $ch = curl_init('https://api.brevo.com/v3/senders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER     => ['accept: application/json', 'api-key: ' . $apiKey],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response === false) {
            return self::$brevoSendersCache;
        }
        $data = json_decode($response, true);
        foreach ($data['senders'] ?? [] as $row) {
            if (!empty($row['active']) && !empty($row['email'])) {
                self::$brevoSendersCache[] = strtolower($row['email']);
            }
        }
        return self::$brevoSendersCache;
    }

    private function remetenteValidoNaBrevo(): bool {
        $from = strtolower($this->sender()['email']);
        $ativos = $this->brevoRemetentesAtivos();
        if ($ativos === []) {
            return true;
        }
        return in_array($from, $ativos, true);
    }

    private function sendViaBrevoApi(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): bool {
        $this->lastError = null;
        $apiKey = $this->brevoApiKey();
        if ($apiKey === null) {
            return false;
        }

        if (!$this->remetenteValidoNaBrevo()) {
            $from = $this->sender()['email'];
            $lista = implode(', ', $this->brevoRemetentesAtivos()) ?: '(nenhum)';
            $this->lastError = "Remetente «{$from}» não está verificado na Brevo. Remetentes ativos: {$lista}. "
                . 'Cadastre em https://app.brevo.com/senders';
            error_log('[MailService] brevo_api: ' . $this->lastError);
            return false;
        }

        $payload = json_encode([
            'sender'      => $this->sender(),
            'to'          => [['email' => $toEmail, 'name' => $toName]],
            'subject'     => $subject,
            'htmlContent' => $htmlBody,
            'textContent' => $textBody,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
                'api-key: ' . $apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            $hint = str_contains((string) $response, 'unrecognised IP') || str_contains((string) $response, 'Unauthorized IP')
                ? ' ' . $this->brevoWhitelistHint()
                : '';
            $this->lastError = 'Brevo API HTTP ' . $httpCode . ': ' . ($error ?: trim((string) $response)) . $hint;
            error_log('[MailService] brevo_api: ' . $this->lastError);
            return false;
        }

        error_log('[MailService] brevo_api: enviado para ' . $toEmail . ' HTTP ' . $httpCode);
        return true;
    }

    private function sendViaResend(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): bool {
        $this->lastError = null;
        $apiKey = $this->resendApiKey();
        if ($apiKey === null) {
            return false;
        }

        $sender = $this->sender();
        $from   = $sender['name'] . ' <' . $sender['email'] . '>';
        $payload = json_encode([
            'from'    => $from,
            'to'      => [$toEmail],
            'subject' => $subject,
            'html'    => $htmlBody,
            'text'    => $textBody,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            $this->lastError = 'Resend HTTP ' . $httpCode . ': ' . ($error ?: trim((string) $response));
            error_log('[MailService] resend: ' . $this->lastError);
            return false;
        }

        error_log('[MailService] resend: enviado para ' . $toEmail . ' HTTP ' . $httpCode);
        return true;
    }

    private function createMailer(): \PHPMailer\PHPMailer\PHPMailer {
        $mailerPath = __DIR__ . '/../../PHPMailer/src/';
        require_once $mailerPath . 'Exception.php';
        require_once $mailerPath . 'PHPMailer.php';
        require_once $mailerPath . 'SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $host = app_env('MAIL_HOST', 'smtp.gmail.com');
        $resolved = @gethostbyname($host);
        $mail->Host       = ($resolved !== $host) ? $resolved : $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = app_env('MAIL_USERNAME', '');
        $mail->Password   = $this->smtpPassword();
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) app_env('MAIL_PORT', '587');
        $mail->CharSet    = 'UTF-8';

        $from = app_env('MAIL_FROM_ADDRESS', app_env('MAIL_USERNAME', 'noreply@uniceplac.edu.br'));
        $name = app_env('MAIL_FROM_NAME', 'LabHub UNICEPLAC');
        $mail->setFrom($from, $name);

        return $mail;
    }

    private function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): bool {
        $this->lastError = null;

        if ($this->resendApiKey() !== null && $this->sendViaResend($toEmail, $toName, $subject, $htmlBody, $textBody)) {
            return true;
        }

        if ($this->brevoApiKey() !== null && $this->sendViaBrevoApi($toEmail, $toName, $subject, $htmlBody, $textBody)) {
            return true;
        }

        if ($this->smtpPassword() === '' || !str_starts_with($this->smtpPassword(), 'xsmtpsib-')) {
            if ($this->lastError === null) {
                $this->lastError = 'Nenhum provedor de e-mail ativo. Configure RESEND_API_KEY (re_...) ou BREVO_API_KEY (xkeysib-...).';
            }
            return false;
        }
        try {
            $mail = $this->createMailer();
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;
            $mail->send();
            error_log('[MailService] smtp: enviado para ' . $toEmail);
            return true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Unauthorized IP') || str_contains($msg, '525')) {
                $msg .= ' — ' . $this->brevoWhitelistHint();
            }
            $this->lastError = 'SMTP: ' . $msg;
            error_log('[MailService] smtp: ' . $this->lastError);
            return false;
        }
    }

    public function isConfigured(): bool {
        $fromOk = app_env('MAIL_FROM_ADDRESS') !== null && app_env('MAIL_FROM_ADDRESS') !== '';
        if (!$fromOk) {
            return false;
        }
        if ($this->resendApiKey() !== null) {
            return true;
        }
        $hasBrevo = $this->brevoApiKey() !== null
            || (str_starts_with($this->smtpPassword(), 'xsmtpsib-') && app_env('MAIL_USERNAME') !== '');
        if (!$hasBrevo) {
            return false;
        }
        return $this->remetenteValidoNaBrevo();
    }

    public function baseUrl(): string {
        $url = app_env('APP_URL', '');
        if ($url === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            return $scheme . '://' . $host;
        }
        return rtrim($url, '/');
    }

    public function enviarRedefinicaoSenha(string $email, string $nome, string $token): bool {
        $link = $this->baseUrl() . '/redefinir_senha.php?token=' . urlencode($token);
        $html = '
            <div style="font-family:Segoe UI,sans-serif;max-width:520px;margin:0 auto;">
                <h2 style="color:#00734F;">Redefinição de senha</h2>
                <p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>!</p>
                <p>Recebemos uma solicitação para redefinir a senha da sua conta no LabHub.</p>
                <p><a href="' . htmlspecialchars($link) . '" style="display:inline-block;background:#00734F;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:600;">Criar nova senha</a></p>
                <p style="color:#666;font-size:13px;">O link expira em 24 horas. Se você não solicitou, ignore este e-mail.</p>
                <p style="color:#999;font-size:12px;word-break:break-all;">' . htmlspecialchars($link) . '</p>
            </div>';
        $text = "Acesse para redefinir sua senha (válido por 24h): {$link}";

        return $this->sendMail($email, $nome, 'Redefinição de senha — LabHub UNICEPLAC', $html, $text);
    }

    public function enviarVerificacaoEmail(string $email, string $nome, string $token): bool {
        $link = $this->baseUrl() . '/verificar.php?token=' . urlencode($token);
        $html = '
            <div style="font-family:Segoe UI,sans-serif;max-width:520px;margin:0 auto;">
                <h2 style="color:#00734F;">Confirme seu e-mail</h2>
                <p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>!</p>
                <p><strong>Passo 1:</strong> clique no botão abaixo para confirmar seu e-mail.</p>
                <p><a href="' . htmlspecialchars($link) . '" style="display:inline-block;background:#00734F;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:600;">Confirmar e-mail</a></p>
                <p><strong>Passo 2:</strong> depois da confirmação, acesse o LabHub e faça login com a senha enviada pela coordenação.</p>
                <p style="color:#666;font-size:13px;">Se o botão não abrir, copie e cole no navegador:<br><span style="word-break:break-all;">' . htmlspecialchars($link) . '</span></p>
            </div>';
        $text = "Passo 1 — Confirme seu e-mail: {$link}\nPasso 2 — Faça login com sua senha no LabHub.";

        return $this->sendMail($email, $nome, 'Confirme seu e-mail — LabHub UNICEPLAC', $html, $text);
    }

    public function enviarSenhaTemporaria(string $email, string $nome, string $senhaTemp): bool {
        $link = $this->baseUrl() . '/index.php';
        $html = '
            <div style="font-family:Segoe UI,sans-serif;max-width:520px;margin:0 auto;">
                <h2 style="color:#00734F;">Senha redefinida</h2>
                <p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>!</p>
                <p>A coordenação definiu uma senha temporária para sua conta:</p>
                <p style="font-size:18px;font-weight:bold;background:#f4f6f8;padding:12px;border-radius:8px;">' . htmlspecialchars($senhaTemp) . '</p>
                <p>Recomendamos alterá-la após o login em <strong>Meu Perfil</strong>.</p>
                <p><a href="' . htmlspecialchars($link) . '">Acessar o sistema</a></p>
            </div>';
        $text = "Senha temporária: {$senhaTemp}. Acesse: {$link}";

        return $this->sendMail($email, $nome, 'Nova senha temporária — LabHub UNICEPLAC', $html, $text);
    }

    public function enviarAtualizacaoChamadoSos(
        string $email,
        string $nome,
        array $chamado,
        string $statusLabel,
        string $respostaProfessor
    ): bool {
        $lab = htmlspecialchars($chamado['laboratorio'] ?? '');
        $problema = nl2br(htmlspecialchars($chamado['mensagem'] ?? ''));
        $resposta = nl2br(htmlspecialchars(trim($respostaProfessor)));
        $statusHtml = htmlspecialchars($statusLabel);
        $link = htmlspecialchars($this->baseUrl() . '/painel_professor.php#sessao-chamados-ti');

        $html = '
            <div style="font-family:Segoe UI,sans-serif;max-width:560px;margin:0 auto;">
                <h2 style="color:#F0733C;">Atualização do seu chamado — Suporte TI</h2>
                <p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>!</p>
                <p>Há uma nova atualização sobre o chamado aberto no laboratório <strong>' . $lab . '</strong>.</p>
                <p style="margin:16px 0;"><span style="display:inline-block;background:#00734F;color:#fff;padding:6px 14px;border-radius:20px;font-size:14px;">Status: ' . $statusHtml . '</span></p>
                <div style="background:#f8f9fa;border-left:4px solid #F0733C;padding:14px;border-radius:6px;margin:16px 0;">
                    <p style="margin:0 0 8px;font-size:13px;color:#666;"><strong>Problema relatado:</strong></p>
                    <p style="margin:0;">' . $problema . '</p>
                </div>
                <div style="background:#eef6ff;border-left:4px solid #2563eb;padding:14px;border-radius:6px;margin:16px 0;">
                    <p style="margin:0 0 8px;font-size:13px;color:#666;"><strong>Resposta do Suporte TI:</strong></p>
                    <p style="margin:0;">' . ($resposta !== '' ? $resposta : '<em>Status atualizado. Em breve entraremos em contato.</em>') . '</p>
                </div>
                <p><a href="' . $link . '" style="display:inline-block;background:#00734F;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:600;">Ver meus chamados no LabHub</a></p>
                <p style="color:#666;font-size:13px;">Você também pode acompanhar pelo painel do professor, aba <strong>Chamados ao TI</strong>.</p>
            </div>';
        $text = "Chamado TI — {$lab}\nStatus: {$statusLabel}\n\nResposta:\n" . trim($respostaProfessor) . "\n\nAcesse: {$link}";

        return $this->sendMail($email, $nome, 'Chamado TI — ' . $statusLabel . ' — LabHub UNICEPLAC', $html, $text);
    }

    public function enviarListaEsperaLaboratorio(string $email, string $nome, array $dados): bool
    {
        require_once __DIR__ . '/../Config/horario_helpers.php';

        $dataFmt = !empty($dados['data_reserva'])
            ? date('d/m/Y', strtotime($dados['data_reserva']))
            : '';
        $turno = (string) ($dados['turno'] ?? '');
        $periodo = (string) ($dados['periodo'] ?? '');
        $horario = labhub_horario_label($turno, $periodo);
        $disciplina = htmlspecialchars((string) ($dados['disciplina'] ?? ''));
        $posicao = (int) ($dados['posicao'] ?? 1);
        $link = htmlspecialchars($this->baseUrl() . '/painel_professor.php#sessao-historico');

        $html = '
            <div style="font-family:Segoe UI,sans-serif;max-width:560px;margin:0 auto;">
                <h2 style="color:#00734F;">Lista de espera — Laboratório</h2>
                <p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>!</p>
                <p>Todos os laboratórios estavam ocupados no horário solicitado. Você foi incluído na <strong>lista de espera</strong>.</p>
                <div style="background:#f0fdf4;border-left:4px solid #00734F;padding:14px;border-radius:6px;margin:16px 0;">
                    <p style="margin:0 0 6px;"><strong>Data:</strong> ' . htmlspecialchars($dataFmt) . '</p>
                    <p style="margin:0 0 6px;"><strong>Horário:</strong> ' . htmlspecialchars($horario) . '</p>
                    <p style="margin:0 0 6px;"><strong>Disciplina:</strong> ' . $disciplina . '</p>
                    <p style="margin:0;"><strong>Sua posição na fila:</strong> ' . $posicao . 'º</p>
                </div>
                <p>Quando houver liberação, a coordenação será avisada e você receberá atualização na plataforma.</p>
                <p><a href="' . $link . '" style="display:inline-block;background:#00734F;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:600;">Ver histórico no LabHub</a></p>
            </div>';
        $text = "Lista de espera — {$dataFmt} ({$horario})\nDisciplina: {$dados['disciplina']}\nPosição: {$posicao}º\nAcesse: {$this->baseUrl()}/painel_professor.php#sessao-historico";

        return $this->sendMail($email, $nome, 'Lista de espera — laboratório — LabHub UNICEPLAC', $html, $text);
    }
}
