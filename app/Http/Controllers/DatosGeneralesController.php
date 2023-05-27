<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatosGeneralesController extends Controller
{
    public function insertar(Request $request)
    {
        $id = $request->input('id');
        $nombre = $request->input('nombre');
        $objeto_estudio = $request->input('objeto_estudio');
        $asignatura = $request->input('asignatura');
        $fecha = $request->input('fecha');
        $competencia = $request->input('competencia');
        $resultado_aprendizaje = $request->input('resultado_aprendizaje');

        $datos = array(
            'id' => $id,
            'nombre' => $nombre,
            'objeto_estudio' => $objeto_estudio,
            'asignatura' => $asignatura,
            'fecha' => $fecha,
            'competencia' => $competencia,
            'resultado_aprendizaje' => $resultado_aprendizaje
        );

        DB::table('datos_generales')->insert($datos);

        return response()->json(array('mensaje' => 'Datos insertados correctamente'));
    }
}
