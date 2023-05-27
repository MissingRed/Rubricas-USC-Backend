<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/rubricasCrear', 'App\Http\Controllers\RubricasController@crearRubrica');
Route::get('/asignaturas', 'App\Http\Controllers\RubricasController@getAsignaturas');
Route::get('/objetos', 'App\Http\Controllers\RubricasController@getObjetosEstudio');
Route::get('/rubrica/{id}', 'App\Http\Controllers\RubricasController@show');
Route::get('/rubricas/{email}', 'App\Http\Controllers\RubricasController@shows');
Route::delete('/rubricas/{id}', 'App\Http\Controllers\RubricasController@eliminarRubrica');