<?php
/**
 * Agendamento Model - Eloquent Style
 */

namespace LaravelApp\Models;

class Agendamento extends Model {
    protected $table = 'agendamentos';
    protected $fillable = ['id_laboratorio', 'id_professor', 'id_disciplina', 'turno', 'periodo', 'data_reserva', 'status'];
    protected $timestamps = true;

    /**
     * Get agendamentos pending
     */
    public static function getPendentes() {
        return self::query()->where('status', 'pendente')->orderBy('data_reserva', 'asc')->get();
    }

    /**
     * Get agendamentos aprovados
     */
    public static function getAprovados() {
        return self::query()->where('status', 'aprovado')->orderBy('data_reserva', 'desc')->get();
    }

    /**
     * Get by professor
     */
    public static function byProfessor($id_professor) {
        return self::query()
            ->where('id_professor', $id_professor)
            ->orderBy('data_reserva', 'desc')
            ->get();
    }

    /**
     * Approve agendamento
     */
    public function approve() {
        $this->setAttribute('status', 'aprovado');
        return $this->save();
    }

    /**
     * Reject agendamento
     */
    public function reject() {
        $this->setAttribute('status', 'rejeitado');
        return $this->save();
    }
}
?>
