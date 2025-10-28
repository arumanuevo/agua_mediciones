<?php

namespace App\Http\Controllers;

use App\Models\Medicion;
use App\Http\Requests\StoreMedicionRequest;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MedicionController extends Controller
{
    public function store(StoreMedicionRequest $request)
    {
        $lote = Lote::findOrFail($request->lote_id);
        $this->authorize('view', $lote);

        $fotoPath = $this->guardarFoto($request->file('foto'));

        $medicion = Medicion::create([
            'lote_id' => $request->lote_id,
            'lectura_actual' => $request->lectura_actual,
            'lectura_anterior' => $request->lectura_anterior,
            'fecha_toma' => $request->fecha_toma,
            'fecha_anterior' => $request->fecha_anterior,
            'foto_path' => $fotoPath,
            'user_id' => auth()->id(),
        ]);

        return response()->json($medicion, 201);
    }

    protected function guardarFoto($foto)
    {
        $path = 'fotos_medidores/' . uniqid() . '.jpg';
        $img = Image::make($foto->getRealPath());
        $img->resize(800, 600, function ($constraint) {
            $constraint->aspectRatio();
        })->save(storage_path('app/public/' . $path));

        return $path;
    }
}
