<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Lote
{
    use HasFactory;

    /**
     * Obtener un lote por su ID.
     */
    public static function find($id)
    {
        return DB::table('lotes')->where('id', $id)->first();
    }

    /**
     * Obtener todas las mediciones de un lote.
     */
    public function mediciones()
    {
        return DB::table('mediciones')->where('lote_id', $this->id)->orderBy('fecha_toma', 'desc')->get();
    }

    /**
     * Obtener el usuario dueño del lote.
     */
    public function user()
    {
        return DB::table('users')->where('id', $this->user_id)->first();
    }

    /**
     * Crear un nuevo lote.
     */
    public static function create(array $data)
    {
        $id = DB::table('lotes')->insertGetId([
            'numero' => $data['numero'],
            'direccion' => $data['direccion'],
            'medidor_id' => $data['medidor_id'],
            'user_id' => $data['user_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return self::find($id);
    }

    /**
     * Actualizar un lote.
     */
    public function update(array $data)
    {
        DB::table('lotes')->where('id', $this->id)->update([
            'numero' => $data['numero'] ?? $this->numero,
            'direccion' => $data['direccion'] ?? $this->direccion,
            'medidor_id' => $data['medidor_id'] ?? $this->medidor_id,
            'updated_at' => now(),
        ]);
        return self::find($this->id);
    }

    /**
     * Eliminar un lote.
     */
    public function delete()
    {
        DB::table('lotes')->where('id', $this->id)->delete();
    }
}
