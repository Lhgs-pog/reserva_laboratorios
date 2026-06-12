<?php
/**
 * SOS Controller - Laravel Style
 */

namespace LaravelApp\Http\Controllers;

use LaravelApp\Models\SOS;

class SOSController extends Controller {
    /**
     * List SOS chamados
     */
    public function index() {
        $this->requireRole('suporte');
        return $this->json(['chamados' => SOS::all()]);
    }

    /**
     * Get pending SOS count
     */
    public function pendentes() {
        return $this->json(['qtd' => SOS::countPendentes()]);
    }

    /**
     * Create SOS chamado
     */
    public function store() {
        $this->requireAuth();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $chamado = SOS::create([
                'id_usuario' => $_SESSION['usuario_id'],
                'titulo' => $data['titulo'],
                'descricao' => $data['descricao'],
                'status' => 'pendente',
                'data_criacao' => date('Y-m-d H:i:s'),
            ]);

            return $this->json(['message' => 'Chamado criado', 'data' => $chamado->toArray()], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Resolve SOS chamado
     */
    public function resolve($id) {
        $this->requireRole('suporte');

        $chamado = SOS::find($id);
        if (!$chamado) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $chamado->resolve();
        return $this->json(['message' => 'Chamado resolvido', 'data' => $chamado->toArray()]);
    }
}
?>
