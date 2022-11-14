<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class TablespaceController extends Controller
{
    //
    public function tablespaces()
    {
        return DB::table('USER_TABLESPACES')
            ->select('TABLESPACE_NAME')
            ->get();
    }
    public function createTablespace(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
        ]);
        DB::statement('alter session set "_ORACLE_SCRIPT"=true');
        DB::statement("CREATE TABLESPACE " . $fields['name'] . " DATAFILE '" . 'C:\\app\\oradata\\XE\\proyecto_db\\' . $fields['name'] . ".DBF' SIZE 100M AUTOEXTEND ON NEXT 50");

        return response(['message' => 'Tablespace creado con éxito'], 201);
    }
    public function createTemporaryTablespace(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
        ]);

        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("CREATE TEMPORARY TABLESPACE " . $fields['name'] . "_TEMP TEMPFILE '" . 'C:\\app\\oradata\\XE\\proyecto_db\\' . $fields['name'] . "_TEMP.DBF' SIZE 25M AUTOEXTEND ON NEXT 50");

        return response(['message' => 'Tablespace temporal creado con éxito'], 201);
    }


    public function deleteTablespace($tablespace)
    {
        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("DROP TABLESPACE " . $tablespace . " INCLUDING CONTENTS AND DATAFILES");

        return response(['message' => 'Tablespace eliminado con éxito'], 201);
    }
    public function columnOfATableOfASchema($schema, $table) //este metodo lo que hace es devolver las columnas de una tabla de un esquema
    {
        return DB::table('all_tab_columns')
            ->select('column_name')
            ->where('owner', $schema)
            ->where('table_name', $table)
            ->orderBy('column_name')
            ->get();
    }
    

    public function resizeTablespace(Request $request)
    {
        $fields = $request->validate([
            "tablespace" => "required",
            "size" => "required",
        ]);

        DB::statement('alter session set "_oracle_script"=true');

        $resultado = DB::table("v\$datafile")
            ->select("NAME")
            ->where("NAME", "LIKE", "%" . $fields['tablespace'] . "%")
            ->get();

        $resultado = $resultado[0]->name;

        DB::statement("ALTER DATABASE DATAFILE '$resultado' resize " . $fields['size'] . "M");

        return response(['route' => 'Resize exitoso'], 200);
    }
    public function resizeTemporaryTablespace(Request $request)
    {
        $fields = $request->validate([
            "tablespace" => "required",
            "size" => "required",
        ]);

        DB::statement('alter session set "_oracle_script"=true');

        $resultado = DB::table("v\$tempfile")
            ->select("NAME")
            ->where("NAME", "LIKE", "%" . $fields['tablespace'] . "%")
            ->get();
    
        $resultado = $resultado[0]->name;

        DB::statement("ALTER DATABASE TEMPFILE '$resultado' resize " . $fields['size'] . "M");

        return response(['route' => 'Resize exitoso'], 200);
    }
    public function createEstadisticaTabla(Request $request)
    {
        $fields = $request->validate([
            "schema" => "required",
            "tabla" => "required",
        ]);

        $schema = $fields['schema'];

        $tabla = $fields['tabla'];

        DB::statement('alter session set "_oracle_script"=true');

        DB::raw("EXECUTE dbms_stats.gather_table_stats('$schema', '$tabla' ,cascade=> true)");

        return response(['message' => 'Se creo correctamente la estadistica de la tabla ' . $tabla . ' del esquema ' . $schema], 200);
    }
    //metodo ver estadisticas de una tabla
    public function verEstadisticasTabla($schema, $tabla)
    {
        return DB::table('all_tab_statistics')
            ->select('num_rows', 'blocks', 'empty_blocks', 'avg_space', 'last_analyzed')
            ->where('owner', $schema)
            ->where('table_name', $tabla)
            ->get();
    }

    //Metodo para auditar tablas
    public function auditarTabla(Request $request)
    {
        $fields = $request->validate([
            "schema" => "required",
            "tabla" => "required",
        ]);

        $schema = $fields['schema'];

        $tabla = $fields['tabla'];

        DB::statement('alter session set "_oracle_script"=true');

        DB::raw("EXECUTE dbms_audit_trail.enable('TABLE', '$schema', '$tabla')");

        return response(['message' => 'Se creo correctamente la auditoria de la tabla ' . $tabla . ' del esquema ' . $schema], 200);
    }
    


    
}
