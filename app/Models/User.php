<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Obtener todos los lotes asociados al usuario.
     */
    public function lotes()
    {
        return DB::table('lotes')->where('user_id', $this->id)->get();
    }

    /**
     * Obtener todas las mediciones asociadas a los lotes del usuario.
     */
    public function mediciones()
    {
        $lotes = $this->lotes();
        $loteIds = array_column($lotes->toArray(), 'id');
        return DB::table('mediciones')->whereIn('lote_id', $loteIds)->get();
    }

    /**
     * Verificar si el usuario tiene un lote específico.
     */
    public function tieneLote($loteId)
    {
        return DB::table('lotes')->where('id', $loteId)->where('user_id', $this->id)->exists();
    }
}
