<?php
/**
 * Agendamento Controller - Laravel Style
 */

namespace LaravelApp\Http\Controllers;

use LaravelApp\Models\Agendamento;

class AgendamentoController extends Controller {
    /**
     * List agendamentos
     */
    public function index() {
        $this->requireAuth();
        return $this->json(['agendamentos' => Agendamento::all()]);
    }

    /**
     * Show single agendamento
     */
    public function show($id) {
        $this->requireAuth();
        $agendamento = Agendamento::find($id);

        if (!$agendamento) {
            return $this->json(['error' => 'Not found'], 404);
        }

        return $this->json($agendamento->toArray());
    }

    /**
     * Create agendamento
     */
    public function store() {
        $this->requireRole('coordenador');

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $agendamento = Agendamento::create([
                'id_laboratorio' => $data['id_laboratorio'],
                'id_professor' => $data['id_professor'],
                'id_disciplina' => $data['id_disciplina'],
                'turno' => $data['turno'],
                'periodo' => $data['periodo'],
                'data_reserva' => $data['data_reserva'],
                'status' => 'aprovado',
            ]);

            return $this->json(['message' => 'Agendamento criado', 'data' => $agendamento->toArray()], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update agendamento
     */
    public function update($id) {
        $this->requireRole('coordenador');

        $agendamento = Agendamento::find($id);
        if (!$agendamento) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $agendamento->fill($data)->save();
            return $this->json(['message' => 'Agendamento atualizado', 'data' => $agendamento->toArray()]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete agendamento
     */
    public function destroy($id) {
        $this->requireAuth();

        $agendamento = Agendamento::find($id);
        if (!$agendamento) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $agendamento->delete();
        return $this->json(['message' => 'Agendamento deletado']);
    }
}
?>
