<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OracleController extends Controller
{
    public function publicPath()
    {
        return response(['path' => public_path() . "\\respaldos"], 200);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //Schemas
    public function schemas()
    {
        return DB::select(
            'select username as schema_name
            from sys.dba_users
            order by username'
        );
    }
    public function tablasDeSchemas($schema)
    {
        return DB::table('all_tables')
            ->select('table_name')
            ->where('owner', $schema)
            ->orderBy('table_name')
            ->get();
    }
    public function createSchemaBackUp($schema)
    {
        //permitir los cors
        header('Access-Control-Allow-Origin: *');
        DB::statement('alter session set "_oracle_script"=true');

        
        DB::statement("CREATE OR REPLACE DIRECTORY RESPALDO AS " . "'" . public_path() . "\\respaldos'");

        $cmd = "EXPDP SYSTEM/Qwsd1234@XE SCHEMAS=" . $schema . " DIRECTORY=RESPALDO DUMPFILE=" . $schema . ".DMP LOGFILE=" . $schema . ".LOG";

        shell_exec($cmd);

        $path = 'respaldos/' . $schema . '.DMP';

        return response()->download($path);
    }

    /**
     * Delete the DUMP and LOG file created for the backup of a certain $schema.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteSchemaBackUp($schema)
    {
        $path = 'respaldos/' . $schema . '.DMP';

        File::delete($path);

        $path = 'respaldos/' . $schema . '.LOG';

        File::delete($path);
       
        return response(['message' => 'Se elimino correctamente'], 200);
    }


    public function createTableOfSchemaBackUp($schema, $table)
    {
        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("CREATE OR REPLACE DIRECTORY RESPALDO AS " . "'" . public_path() . "\\respaldos'");

        
        $cmd = "EXPDP SYSTEM/Qwsd1234@XE TABLES=" . $schema . "." . $table . " DIRECTORY=RESPALDO DUMPFILE=" . $schema . $table . ".DMP LOGFILE=" . $schema . $table . ".LOG";

        shell_exec($cmd);

        $path = 'respaldos/' . $schema . $table . '.DMP';
       
        return response()->download($path);
        //return response(['message' => 'Se creo correctamente el respaldo de la tabla ' . $table . ' del esquema ' . $schema], 200);
    }

    public function deleteTableOfSchemaBackUp($schema, $table)
    {
        $path = 'respaldos/' . $schema . $table . '.DMP';

        File::delete($path);

        $path = 'respaldos/' . $schema . $table . '.LOG';

        File::delete($path);

        return response(['message' => 'Se elimino correctamente'], 200);
    }

    public function createDatabaseBackUp()
    {
        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("CREATE OR REPLACE DIRECTORY RESPALDO AS " . "'" . public_path() . "\\respaldos'");

        $cmd = "EXPDP SYSTEM/Qwsd1234@XE FULL=Y  DIRECTORY=RESPALDO DUMPFILE=XE.DMP LOGFILE=XE.LOG";

        shell_exec($cmd);

        $path = 'respaldos/XE.DMP';

        //mesaje se creo
        return response()->download($path);
    }

    /**
     * Delete the DUMP and LOG file created for the backup of the database.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteDatabaseBackUp()
    {
        $path = 'respaldos/XE.DMP';

        File::delete($path);

        $path = 'respaldos/XE.LOG';

        File::delete($path);

        return response(['message' => 'Se elimino correctamente'], 200);
    }
    public function createEstadisticaSchema(Request $request)
    {
        $fields = $request->validate([
            "schema" => "required",
        ]);

        $schema = $fields['schema'];

        DB::statement('alter session set "_oracle_script"=true');

        DB::raw("EXECUTE dbms_stats.gather_schema_stats('$schema' ,cascade=> true)");

        return response(['message' => 'Se creo correctamente la estadistica del esquema ' . $schema], 200);


    }
    public function createIndexOnColumnOfTableOfSchema(Request $request)//este metodo lo que hace es crear un indice en una columna de una tabla de un esquema
    {
       
        $fields = $request->validate([
            "schema" => "required",
            "table" => "required",
            "campos" => "required",
        ]);

        $schema = $fields['schema'];
        $table = $fields['table'];
        $campos = $fields['campos'];

        DB::statement('alter session set "_oracle_script"=true');

        DB::raw("EXECUTE dbms_stats.create_index('$schema' , '$table' , '$campos' ,cascade=> true)");

        return response(['message' => 'Se creo correctamente el indice en la columna ' . $campos . ' de la tabla ' . $table . ' del esquema ' . $schema], 200);
    }
    

    /**
     * Analize all the tables of a schema.
     *
     * @return \Illuminate\Http\Response
     */
    public function analizeSchema(Request $request)//este metodo lo que hace es analizar todas las tablas de un esquema
    {
        $fields = $request->validate([
            "schema" => "required",
        ]);
        $schema = $fields['schema'];
        DB::statement('alter session set "_oracle_script"=true');

        $tablas = DB::table('all_tables')
            ->select('table_name')
            ->where('owner', $fields['schema'])
            ->orderBy('table_name')
            ->get();

        foreach ($tablas as $tabla) {
            DB::statement("ANALYZE TABLE " . $fields['schema'] . "." . $tabla->table_name . " COMPUTE STATISTICS");
        }

        return response(['message' => 'Se analizaron correctamente todas las tablas del esquema ' . $schema], 200);
    
    }

    /**
     * Analize a table the tables of a schema.
     *
     * @return \Illuminate\Http\Response
     */
    public function analizeTableOfSchema(Request $request)
    {
        $fields = $request->validate([
            "schema" => "required",
            "tabla" => "required",
        ]);

        $schema = $fields['schema'];
        $tabla = $fields['tabla'];

        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("ANALYZE TABLE " . $fields['schema'] . "." . $fields['tabla'] . " COMPUTE STATISTICS");

        return response(['message' => 'Se analizo correctamente la tabla ' . $tabla . ' del esquema ' . $schema], 200);
    }

    /**
     * Return the list of privileges that a user can have.
     *
     * @return \Illuminate\Http\Response
     */
    public function privileges()//este metodo lo que hace es 
    {
        return DB::table('DBA_SYS_PRIVS')
            ->select('privilege')
            ->distinct()
            ->get();
    }

    public function createRol(Request $request)
    {
        $fields = $request->validate([
            "rol_name" => "required",
            "privilegios_rol" => "nullable",
        ]);

        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("CREATE ROLE " . $fields['rol_name'] . " NOT IDENTIFIED");

        $privilegios = explode('|', $fields['privilegios_rol']);

        foreach ($privilegios as $privilegio) {
            DB::statement("GRANT $privilegio to " . $fields['rol_name']);
        }

        return response(['message' => 'Rol creado con los permisos especificados'], 201);
    }

    public function getRolOfUser($schema)
    {
        return DB::table('DBA_ROLE_PRIVS')
            ->select('GRANTED_ROLE')
            ->where('GRANTEE', $schema)
            ->get();
    }
    //obtener usuarios de un esquema
    public function getUsersOfSchema($schema)
    {
        return DB::table('DBA_USERS')
            ->select('USERNAME')
            ->where('USERNAME', 'like', $schema . '%')
            ->get();
    }

    public function asignarRolAUsuario($rol, $usuario)
    {
        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("GRANT $rol to $usuario");

        return response(['message' => 'Rol asignado correctamente'], 201);
    }

    public function desasignarRolAUsuario($rol, $usuario){
        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("REVOKE $rol from $usuario");

        return response(['message' => 'Rol desasignado correctamente'], 201);
    }
    //traer todos los usurios

    //Select name, value from v$parameterwhere name like 'audit_trail'; 
    //activar auditoria
    public function activarAuditoria(){
        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("ALTER SYSTEM SET AUDIT_TRAIL=DB SCOPE=SPFILE");

        return response(['message' => 'Auditoria activada correctamente'], 201);
    }
    //desactivar auditoria
    public function desactivarAuditoria(){
        DB::statement('alter session set "_oracle_script"=true');

        DB::statement("ALTER SYSTEM SET AUDIT_TRAIL=NONE SCOPE=SPFILE");

        return response(['message' => 'Auditoria desactivada correctamente'], 201);
    }
    //ver el estado de la auditoria
    public function estadoAuditoria(){
        DB::statement('alter session set "_oracle_script"=true');

        $estado = DB::select("SELECT name, value FROM v\$parameter WHERE name LIKE 'audit_trail'");

        return response(['message' => $estado], 201);
    }

    //plan de ejecucion
    public function planEjecucion($sql){
        DB::statement('alter session set "_oracle_script"=true');

        $plan = DB::select("EXPLAIN PLAN FOR $sql");

        return response(['message' => $plan], 201);
    }
    public function doMonitoreoEstado()
    {
        return DB::table("V\$INSTANCE")
            ->select()
            ->get();
    }

    public function doMonitoreoParametros()
    {
        return DB::table("V\$system_parameter")
            ->select('name', 'value', 'description')
            ->get();
    }

    public function doMonitoreoConexiones()
    {
        return DB::table("V\$session")
            ->select('osuser', 'username', 'machine', 'program')
            ->where('username', '<>', null)
            ->get();
    }


    

    
}
