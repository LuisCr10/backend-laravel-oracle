<?php

use App\Http\Controllers\OracleController;
use App\Http\Controllers\TablespaceController;
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
//Schemas
Route::get('schemas', [OracleController::class, 'schemas']);
Route::get('schemas/tablas/{schema}', [OracleController::class, 'tablasDeSchemas']);
Route::get('backup/schemas/{schema}', [OracleController::class, 'createSchemaBackUp']);
Route::delete('backup/schemas/{schema}', [OracleController::class, 'deleteSchemaBackUp']);
Route::delete('backup/schemas/{schema}', [OracleController::class, 'deleteSchemaBackUp']);
Route::get('backup/schemas/tables/{schema}/{table}', [OracleController::class, 'createTableOfSchemaBackUp']);
Route::delete('backup/schemas/tables/{schema}/{table}', [OracleController::class, 'deleteTableOfSchemaBackUp']);
Route::get('backup/full', [OracleController::class, 'createDatabaseBackUp']);
Route::delete('backup/full', [OracleController::class, 'deleteDatabaseBackUp']);
Route::post('/estadistica/schema', [OracleController::class, 'createEstadisticaSchema']);
//auditoria
Route::get('auditoria', [OracleController::class, 'activarAuditoria']);
Route::get('desauditoria', [OracleController::class, 'desactivarAuditoria']);
Route::get('auditoria/estado', [OracleController::class, 'estadoAuditoria']);

//tablespsaces
Route::post('create/tablespace', [TablespaceController::class, 'createTablespace']);
Route::post('create/temporary-tablespace', [TablespaceController::class, 'createTemporaryTablespace']);
Route::delete('delete/tablespace/{tablespace}', [TablespaceController::class, 'deleteTablespace']);
Route::get('tablespaces', [TablespaceController::class, 'tablespaces']);
Route::get('columns/{schema}/{table}', [TablespaceController::class, 'columnOfATableOfASchema']);
Route::post('tablespaces/resize', [TablespaceController::class, 'resizeTablespace']);
Route::post('temporary-tablespaces/resize', [TablespaceController::class, 'resizeTemporaryTablespace']);
Route::post('estadistica/tabla', [TablespaceController::class, 'createEstadisticaTabla']);
Route::get('ver/estadistica/tabla/{schema}/{tabla}', [TablespaceController::class, 'verEstadisticasTabla']);
Route::get('monitoreo', [OracleController::class, 'doMonitoreoEstado']);
Route::get('monitoreo2', [OracleController::class, 'doMonitoreoParametros']);
Route::get('monitoreo3', [OracleController::class, 'doMonitoreoConexiones']);
//obtener usuarios por schema
Route::get('usuarios/{schema}', [OracleController::class, 'getUsersOfSchema']);

Route::post('create-index/schema', [OracleController::class, 'createIndexOnColumnOfTableOfSchema']);
Route::post('analize/schema', [OracleController::class, 'analizeSchema']);
Route::post('analize-table/schema', [OracleController::class, 'analizeTableOfSchema']);
//plan de ejecucion
Route::get('plan-ejecucion/{sql}', [OracleController::class, 'planDeEjecucion']);
Route::get('privileges', [OracleController::class, 'privileges']);
Route::post('create/rol', [OracleController::class, 'createRol']);
Route::get('roles-schema/{schema}', [OracleController::class, 'getRolOfUser']);
Route::post('asignar-rol/{user}/{rol}', [OracleController::class, 'asignarRolAUsuario']);
Route::post('desasignar-rol/{user}/{rol}', [OracleController::class, 'desasignarRolAUsuario']);

//trer usuario



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
