<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class Medicion
{
    use HasFactory;

    /**
     * Obtener una medición por su ID.
     */
    public static function find($id)
    {
        return DB::table('mediciones')->where('id', $id)->first();
    }

    /**
     * Obtener el lote asociado a la medición.
     */
    public function lote()
    {
        return DB::table('lotes')->where('id', $this->lote_id)->first();
    }

    /**
     * Obtener el usuario que realizó la medición.
     */
    public function user()
    {
        return DB::table('users')->where('id', $this->user_id)->first();
    }

    /**
     * Crear una nueva medición.
     */
    public static function create(array $data)
    {
        $id = DB::table('mediciones')->insertGetId([
            'lote_id' => $data['lote_id'],
            'lectura_actual' => $data['lectura_actual'],
            'lectura_anterior' => $data['lectura_anterior'] ?? null,
            'fecha_toma' => $data['fecha_toma'],
            'fecha_anterior' => $data['fecha_anterior'] ?? null,
            'foto_path' => $data['foto_path'] ?? null,
            'user_id' => $data['user_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return self::find($id);
    }

    /**
     * Validar que la lectura actual no sea menor a la anterior.
     */
    public static function validarLectura($loteId, $lecturaActual, $lecturaAnterior = null)
    {
        if ($lecturaAnterior !== null && $lecturaActual < $lecturaAnterior) {
            throw new \Exception("La lectura actual no puede ser menor a la anterior.");
        }
        return true;
    }

    /**
     * Guardar y procesar la foto del medidor.
     */
    public static function guardarFoto($foto)
    {
        $path = 'fotos_medidores/' . uniqid() . '.jpg';
        $img = Image::make($foto->getRealPath());
        $img->resize(800, 600, function ($constraint) {
            $constraint->aspectRatio();
        })->save(storage_path('app/public/' . $path));
        return $path;
    }

    /**
     * Actualizar una medición.
     */
    public function update(array $data)
    {
        DB::table('mediciones')->where('id', $this->id)->update([
            'lectura_actual' => $data['lectura_actual'] ?? $this->lectura_actual,
            'lectura_anterior' => $data['lectura_anterior'] ?? $this->lectura_anterior,
            'fecha_toma' => $data['fecha_toma'] ?? $this->fecha_toma,
            'fecha_anterior' => $data['fecha_anterior'] ?? $this->fecha_anterior,
            'foto_path' => $data['foto_path'] ?? $this->foto_path,
            'updated_at' => now(),
        ]);
        return self::find($this->id);
    }

    /**
     * Eliminar una medición.
     */
    public function delete()
    {
        if ($this->foto_path) {
            Storage::disk('public')->delete($this->foto_path);
        }
        DB::table('mediciones')->where('id', $this->id)->delete();
    }

    /**
     * Obtener el consumo calculado para esta medición.
     */
    public function consumo()
    {
        return $this->lectura_actual - ($this->lectura_anterior ?? 0);
    }
}
