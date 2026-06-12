<?php
/**
 * SOS Model - Eloquent Style
 */

namespace LaravelApp\Models;

class SOS extends Model {
    protected $table = 'chamados_suporte';
    protected $fillable = ['id_usuario', 'titulo', 'descricao', 'status', 'data_criacao'];
    protected $timestamps = false;

    /**
     * Get pendentes
     */
    public static function getPendentes() {
        return self::query()
            ->where('status', 'pendente')
            ->orderBy('data_criacao', 'desc')
            ->get();
    }

    /**
     * Count pendentes
     */
    public static function countPendentes() {
        return self::query()->where('status', 'pendente')->count();
    }

    /**
     * Resolve chamado
     */
    public function resolve() {
        $this->setAttribute('status', 'resolvido');
        return $this->save();
    }
}
?>
