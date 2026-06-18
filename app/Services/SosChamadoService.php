<?php

namespace App\Services;



use App\Models\SOS as SOSModel;

use PDO;



class SosChamadoService {

    private PDO $pdo;

    private SOSModel $sos;

    private MailService $mail;



    public function __construct(PDO $pdo) {

        $this->pdo = $pdo;

        $this->sos = new SOSModel();

        $this->mail = new MailService();

    }



    public function atualizar(int $idChamado, array $input, int $idAtendente, string $nomeAtendente): array {

        $chamado = $this->sos->buscarPorId($idChamado);

        if (!$chamado) {

            return ['ok' => false, 'msg' => 'Chamado não encontrado.'];

        }



        $status = trim((string) ($input['status'] ?? ''));

        if (!array_key_exists($status, sos_status_opcoes())) {

            return ['ok' => false, 'msg' => 'Status inválido.'];

        }



        $obsInterna = trim((string) ($input['observacao_interna'] ?? ''));

        $resposta = trim((string) ($input['resposta_professor'] ?? ''));

        $enviarEmail = !empty($input['enviar_email']);

        $statusAnterior = (string) ($chamado['status'] ?? 'pendente');



        $historico = sos_historico_decode($chamado['historico_log'] ?? null);
        if ($historico === []) {
            $historico = array_reverse(sos_historico_lista($chamado));
        }



        $dados = [

            'status'         => $status,

            'id_atendente'   => $idAtendente,

            'nome_atendente' => $nomeAtendente,

        ];



        if ($obsInterna !== '') {

            $dados['observacao_interna'] = $obsInterna;

            $historico = sos_historico_adicionar($historico, 'observacao_interna', $nomeAtendente, $obsInterna, $status);

        }



        if ($resposta !== '') {

            $dados['resposta_professor'] = $resposta;

            $historico = sos_historico_adicionar($historico, 'resposta_professor', $nomeAtendente, $resposta, $status);

        }



        if ($status !== $statusAnterior) {

            $textoStatus = sos_status_label($statusAnterior) . ' → ' . sos_status_label($status);

            $historico = sos_historico_adicionar($historico, 'status', $nomeAtendente, $textoStatus, $status);

        }



        if (in_array($status, sos_status_encerrados(), true)) {

            $dados['resolvido_em'] = date('Y-m-d H:i:s');

        } else {

            $dados['resolvido_em'] = null;

        }



        $emailEnviado = false;

        $emailErro = null;



        if ($enviarEmail && $resposta !== '') {

            $stmt = $this->pdo->prepare('SELECT email, nome FROM usuarios WHERE id = ?');

            $stmt->execute([(int) $chamado['id_professor']]);

            $prof = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($prof && !empty($prof['email'])) {

                if ($this->mail->isConfigured()) {

                    $emailEnviado = $this->mail->enviarAtualizacaoChamadoSos(

                        $prof['email'],

                        $prof['nome'] ?? $chamado['professor_nome'],

                        $chamado,

                        sos_status_label($status),

                        $resposta

                    );

                    if ($emailEnviado) {

                        $dados['ultimo_email_em'] = date('Y-m-d H:i:s');

                        $historico = sos_historico_adicionar($historico, 'email', $nomeAtendente, 'E-mail enviado ao professor com a resposta.', $status);

                    } else {

                        $emailErro = $this->mail->lastError() ?: 'Falha ao enviar e-mail.';

                    }

                } else {

                    $emailErro = 'Serviço de e-mail não configurado no servidor.';

                }

            } else {

                $emailErro = 'E-mail do professor não encontrado.';

            }

        }



        $dados['historico_log'] = sos_historico_encode($historico);



        $this->sos->atualizarChamado($idChamado, $dados);



        $msg = 'Chamado atualizado com sucesso.';

        if ($enviarEmail && $resposta !== '') {

            if ($emailEnviado) {

                $msg .= ' E-mail enviado ao professor.';

            } elseif ($emailErro) {

                $msg .= ' Porém o e-mail não foi enviado: ' . $emailErro;

            }

        }



        return ['ok' => true, 'msg' => $msg, 'email_enviado' => $emailEnviado];

    }

}

