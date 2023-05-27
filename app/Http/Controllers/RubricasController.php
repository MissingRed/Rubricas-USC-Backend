<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RubricasController extends Controller
{
    public function crearRubrica(Request $request)
    {
        $data = $request->json()->all();

        DB::table('evaluations')->insert([
            'id' => $data['id'],
            'name' => $data['name'],
            'subject' => $data['subject'],
            'signature' => $data['signature'],
            'date' => $data['date'],
            'competency' => $data['competency'],
            'learnResult' => $data['learnResult'],
            'total' => $data['total'],
            'id_user' => DB::select('select id from users where email = ?', [$data['email']])[0]->id
        ]);

        foreach ($data['_listCriterions'] as $criterion) {
            DB::table('criterions')->insert([
                'id' => $criterion['id'],
                'evaluation_id' => $data['id'],
                'title_ctro' => $criterion['title_ctro'],
                'commentaries' => $criterion['commentaries'],
                'proposers' => $criterion['proposers'],
                'value' => $criterion['value'],
                'subtotal' => $criterion['subtotal'],
            ]);

            foreach ($criterion['_listDescripters'] as $descripter) {
                DB::table('descriptors')->insert([
                    'id' => $descripter['id'],
                    'criterion_id' => $criterion['id'],
                    'title_desc' => $descripter['title_desc'],
                    'contextA' => $descripter['contextA'],
                    'contextB' => $descripter['contextB'],
                    'average' => $descripter['average'],
                    'value' => $descripter['value'],
                    'result' => $descripter['result'],
                    'approveA' => $descripter['approveA'],
                    'approveB' => $descripter['approveB']
                ]);
            }
        }

        return response()->json(['success' => true]);
    }


    public function eliminarRubrica($id)
    {
        // Eliminar rúbrica de la tabla evaluations
        DB::table('evaluations')->where('id', $id)->delete();

        // Obtener criterios asociados a la rúbrica
        $criterions = DB::table('criterions')->where('evaluation_id', $id)->get();

        foreach ($criterions as $criterion) {
            // Eliminar descriptores asociados a cada criterio
            DB::table('descriptors')->where('criterion_id', $criterion->id)->delete();
        }

        // Eliminar criterios asociados a la rúbrica
        DB::table('criterions')->where('evaluation_id', $id)->delete();

        return response()->json(['success' => true]);
    }


    public function actualizarRubrica(Request $request, $id)
    {
        $data = $request->json()->all();

        DB::table('evaluations')->where('id', $id)->update([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'signature' => $data['signature'],
            'date' => $data['date'],
            'competency' => $data['competency'],
            'learnResult' => $data['learnResult'],
            'total' => $data['total'],
            'id_user' => DB::table('users')->where('email', $data['email'])->value('id')
        ]);

        // Eliminar criterios y descriptores existentes
        DB::table('criterions')->where('evaluation_id', $id)->delete();
        DB::table('descriptors')->whereIn('criterion_id', DB::table('criterions')->where('evaluation_id', $id)->pluck('id'))->delete();

        foreach ($data['_listCriterions'] as $criterion) {
            $criterionId = $criterion['id'];

            DB::table('criterions')->insert([
                'id' => $criterionId,
                'evaluation_id' => $id,
                'title_ctro' => $criterion['title_ctro'],
                'commentaries' => $criterion['commentaries'],
                'proposers' => $criterion['proposers'],
                'value' => $criterion['value'],
                'subtotal' => $criterion['subtotal'],
            ]);

            foreach ($criterion['_listDescripters'] as $descripter) {
                $existingDescriptor = DB::table('descriptors')
                    ->where('id', $descripter['id'])
                    ->first();

                if ($existingDescriptor) {
                    // El descriptor ya existe, realizar una actualización
                    DB::table('descriptors')
                        ->where('id', $descripter['id'])
                        ->update([
                            'criterion_id' => $criterionId,
                            'title_desc' => $descripter['title_desc'],
                            'contextA' => $descripter['contextA'],
                            'contextB' => $descripter['contextB'],
                            'average' => $descripter['average'],
                            'value' => $descripter['value'],
                            'result' => $descripter['result'],
                            'approveA' => $descripter['approveA'],
                            'approveB' => $descripter['approveB']
                        ]);
                } else {
                    // El descriptor es nuevo, realizar una inserción
                    DB::table('descriptors')->insert([
                        'id' => $descripter['id'],
                        'criterion_id' => $criterionId,
                        'title_desc' => $descripter['title_desc'],
                        'contextA' => $descripter['contextA'],
                        'contextB' => $descripter['contextB'],
                        'average' => $descripter['average'],
                        'value' => $descripter['value'],
                        'result' => $descripter['result'],
                        'approveA' => $descripter['approveA'],
                        'approveB' => $descripter['approveB']
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }


    public function show($id)
    {
        // Obtener datos de la evaluación
        $evaluation = DB::table('evaluations')->where('id', $id)->first();
        $criterions = DB::table('criterions')->where('evaluation_id', $id)->get()->toArray();

        // Crear estructura de datos a retornar
        $data = [
            'id' => $evaluation->id,
            'name' => $evaluation->name,
            'subject' => $evaluation->subject,
            'signature' => $evaluation->signature,
            'date' => $evaluation->date,
            'competency' => $evaluation->competency,
            'learnResult' => $evaluation->learnResult,
            'total' => $evaluation->total,
            'criterions' => []
        ];

        // Iterar sobre los criterios y descriptores relacionados para agregarlos a la estructura de datos
        foreach ($criterions as $criterion) {
            $descriptors = DB::table('descriptors')->where('criterion_id', $criterion->id)->get()->toArray();
            $descriptorList = [];

            foreach ($descriptors as $descriptor) {
                $descriptorList[] = [
                    'title_desc' => $descriptor->title_desc,
                    'result' => $descriptor->result,
                    'value' => $descriptor->value,
                    'contextA' => $descriptor->contextA,
                    'contextB' => $descriptor->contextB,
                    'approveA' => boolval($descriptor->approveA),
                    'approveB' => boolval($descriptor->approveB)
                ];
            }

            $data['criterions'][] = [
                'title_ctro' => $criterion->title_ctro,
                'value' => $criterion->value,
                'descripters' => $descriptorList
            ];
        }

        // Retornar los datos como respuesta en formato JSON
        return response()->json($data);
    }

    public function shows(Request $request)
    {
        // Obtener datos de la evaluación
        $evaluations = DB::table('evaluations')->where('id_user', '=', DB::select('select id from users where email = ?', [$request->email])[0]->id)->get();

        // Retornar los datos como respuesta en formato JSON
        return response()->json($evaluations);
    }

    public function getAsignaturas()
    {
        $asignaturas = DB::table('asignaturas')->get();
        return response()->json($asignaturas);
    }

    public function getObjetosEstudio()
    {
        $objetosEstudio = DB::table('objetos_estudio')->get();
        return response()->json($objetosEstudio);
    }
}