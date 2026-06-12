<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    // 1. Configurações da Tabela
    protected $table = 'usuarios';
    
    // Como a tabela legada não tem colunas created_at e updated_at por padrão, desativamos
    public $timestamps = false;
    
    // 2. Colunas permitidas para preenchimento em massa (Mass Assignment)
    protected $fillable = [
        'nome', 
        'email', 
        'senha', 
        'perfil', 
        'email_verificado', 
        'token_verificacao', 
        'google_id', 
        'foto_perfil'
    ];

    // 3. Ocultar dados sensíveis ao converter para Array/JSON
    protected $hidden = [
        'senha',
        'token_verificacao'
    ];

    /**
     * Verifica a senha criptografada do usuário
     */
    public function verificarSenha($senha) {
        return password_verify($senha, $this->senha);
    }
}
?>
