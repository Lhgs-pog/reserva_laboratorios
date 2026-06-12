<?php
/**
 * User Model - Eloquent Style
 */

namespace LaravelApp\Models;

class User extends Model {
    protected $table = 'usuarios';
    protected $fillable = ['nome', 'email', 'senha', 'perfil', 'email_verificado', 'google_id', 'foto_perfil'];
    protected $hidden = ['senha'];
    protected $timestamps = true;

    /**
     * Find by email
     */
    public static function findByEmail($email) {
        return self::query()->where('email', $email)->first();
    }

    /**
     * Find by Google ID
     */
    public static function findByGoogleId($google_id) {
        return self::query()->where('google_id', $google_id)->first();
    }

    /**
     * Create with hashed password
     */
    public static function createWithPassword(array $attributes) {
        if (isset($attributes['senha'])) {
            $attributes['senha'] = password_hash($attributes['senha'], PASSWORD_DEFAULT);
        }
        return self::create($attributes);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->senha);
    }

    /**
     * Get professors
     */
    public static function getProfessores() {
        return self::query()
            ->where('perfil', 'professor')
            ->orderBy('nome', 'asc')
            ->get();
    }

    /**
     * Check if email exists
     */
    public static function emailExists($email) {
        return self::query()->where('email', $email)->count() > 0;
    }
}
?>
