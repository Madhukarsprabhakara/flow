<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
class MaterializationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createSchema($asset_id, $survey_name, $SurveyDBJson, $database_connection, $schema_name)
    {
        try {
            //return $SurveyDBJson->collapse();
            $tableName=$this->getTableNameForCreation($survey_name);
            $SchemaExists=$this->checkSchemaExistence($database_connection, $schema_name);
            if (!$SchemaExists[0]->count)
            {
                $SchemaExists=$this->createPrivateSchema($database_connection, $schema_name);
            }
            $TableExists=$this->checkTableInSchemaExistence($database_connection, $tableName, $schema_name);
            if (!$TableExists[0]->count)
            {
                $TableExists=$this->createTableInSchema($database_connection, $schema_name, $tableName, $SurveyDBJson);
            }
            return $TableExists;
            return $SchemaExists;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
        //
    }
    public function getTableNameForCreation($survey_name)
    {
        try {
            $survey_name=strtolower(preg_replace('/\s+/', '_', $survey_name));
            $survey_name=preg_replace('/[^A-Za-z0-9\-\_]/', '', $survey_name);
            $survey_name=Str::of($survey_name)->ascii();
            if (Str::length($survey_name)>50)
            {
                $length=Str::length($survey_name);
                $first=Str::substr($survey_name,0,30);
                //$middle=
                $last=Str::substr($survey_name,$length-20,$length);
                $survey_name=$first.'_'.$last;


            }
            return $survey_name;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function checkTableInSchemaExistence($database_connection, $table, $schema_name)
    {
        try {
            return \DB::connection($database_connection)->select("select count(*) from information_schema.tables where table_name='$table' and table_schema='$schema_name'");
        }
        catch (\Exception $e)
        {

        }
    }
    public function checkSchemaExistence($database_connection, $schema_name)
    {
        try {
            return \DB::connection($database_connection)->select("select count(*) from information_schema.schemata where schema_name='$schema_name'");
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function createPrivateSchema($database_connection, $schema_name)
    {
        try {
            \DB::connection($database_connection)->statement("create schema $schema_name");
            return $this->checkSchemaExistence($database_connection, $schema_name);
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function createTableInSchema($database_connection, $schema_name, $tableName, $SurveyDBJson)
    {
        try {
            $table=$tableName;
            $datatypes=$SurveyDBJson->collapse();
            
            //create table in schema if it doesn't already exist
            $status=Schema::connection($database_connection)->create($schema_name.".".$tableName, function(Blueprint $table) use ($datatypes)
            {
                foreach ($datatypes as $key=>$value)
                {
                    $table->{$value}($key)->nullable()->default(NULL);
                }
            });
            return $status;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
