<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\KoboDBController;
use Illuminate\Http\Request;
use App\Models\KoboDbColumn;
use App\Models\KoboDbColumnChoice;
use App\Models\KoboSurveySchema;
class KoboDBControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function ProcessSurveySchema($asset_id, $SurveySchema)
    {
        try {
            
            $ModifiedSurveySchema=array();
            $SurveySchemaWDBColumn=collect($SurveySchema)->map(function ($item, $key) use($asset_id, $ModifiedSurveySchema) {
                $item=(array)$item;
                
                $ModifiedSurveySchema['asset_id']=$asset_id;
                if (array_key_exists('kobo--matrix_list', $item))
                {
                    $ModifiedSurveySchema['kobo_matrix_list']=$item['kobo--matrix_list'];
                }
                else
                {
                    $ModifiedSurveySchema['kobo_matrix_list']=null;
                }
                if (array_key_exists('kobo--score-choices', $item))
                {
                    $ModifiedSurveySchema['kobo_score_choices']=$item['kobo--score-choices'];
                }
                else
                {
                    $ModifiedSurveySchema['kobo_score_choices']=null;
                }
                if (array_key_exists('type',$item))
                {
                    $ModifiedSurveySchema['type']=$item['type'];
                }
                else
                {
                    $ModifiedSurveySchema['type']=null;
                }
                if (array_key_exists('$kuid',$item))
                {
                    $ModifiedSurveySchema['kuid']=$item['$kuid'];
                }
                else
                {
                    $ModifiedSurveySchema['kuid']=null;
                }
                if (array_key_exists('label',$item))
                {
                    $ModifiedSurveySchema['label']=$item['label'][0];
                }
                else
                {
                    $ModifiedSurveySchema['label']=null;
                }
                if (array_key_exists('$autoname',$item))
                {
                    $ModifiedSurveySchema['autoname']=$item['$autoname'];
                }
                else
                {
                    $ModifiedSurveySchema['autoname']=null;
                }
                if (!array_key_exists('raw_db_column_name',$item))
                {
                    $ModifiedSurveySchema['raw_db_column_name']=null;
                }
                
                if (array_key_exists('raw_db_column',$item))
                {
                    $ModifiedSurveySchema['raw_db_column_name']=$item['raw_db_column'];
                    $ModifiedSurveySchema['db_column_name']=$this->getDBColumnName($item['raw_db_column']);
                }
                else
                {
                    $ModifiedSurveySchema['db_column_name']=null;
                }
                
                if (array_key_exists('select_from_list_name',$item))
                {
                    $ModifiedSurveySchema['select_from_list_name']=$item['select_from_list_name'];
                }
                else
                {
                    $ModifiedSurveySchema['select_from_list_name']='';
                }
                $ModifiedSurveySchema['created_at']=null;
                $ModifiedSurveySchema['updated_at']=null;
                return $ModifiedSurveySchema;
            });
            return $SurveySchemaWDBColumn;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function ProcessSurveySchemaChoices($asset_id, $SurveySchema, $SurveySchemaChoices, $survey_content_hash)
    {
        try {
            //return $SurveySchemaChoices;
            $ModifiedSurveySchemaChoices=array();
            $ProcessedSchemaChoices=collect($SurveySchemaChoices)->map(function ($item, $key) use ($ModifiedSurveySchemaChoices, $SurveySchema) {
                $item=(array)$item;
                $ModifiedSurveySchemaChoices['kobo_db_column_id']=$this->getIdOnListName($item['list_name']);
                $ModifiedSurveySchemaChoices['autovalue']=$item['$autovalue'];
                $ModifiedSurveySchemaChoices['label']=$item['label'][0];
                $ModifiedSurveySchemaChoices['name']=$item['name'];
                $ModifiedSurveySchemaChoices['kuid']=$item['$kuid'];
                $ModifiedSurveySchemaChoices['list_name']=$item['list_name'];
                $ModifiedSurveySchemaChoices['created_at']=null;
                $ModifiedSurveySchemaChoices['updated_at']=null;
                return $ModifiedSurveySchemaChoices;

            });
            return $this->InsertChoicesIntoDb($ProcessedSchemaChoices, $survey_content_hash, $asset_id);
            return $ProcessedSchemaChoices;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function InsertChoicesIntoDb($ProcessedSchemaChoices, $survey_content_hash, $asset_id)
    {
        try {
            $SurveySchemaExist = KoboSurveySchema::where('survey_hash', $survey_content_hash)->where('asset_id',$asset_id)->exists();
            if ($SurveySchemaExist)
            {
                $insert_status=KoboDbColumnChoice::insert($ProcessedSchemaChoices->toArray());
                return 1;
            }
            
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function getIdOnListName($list_name)
    {
        try {
            $list_item=KoboDbColumn::where('select_from_list_name',$list_name)->first();
            if (!$list_item)
            {
                $list_item=KoboDbColumn::where('kobo_matrix_list',$list_name)->first();
                if (!$list_item)
                {
                    $list_item=KoboDbColumn::where('kobo_score_choices',$list_name)->first();
                    return $list_item->id;
                }
                return $list_item->id;
            }
            return $list_item->id;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function getDBColumnName($RawDBColumn)
    {
        try {
            $RawDBColumn=strtolower(preg_replace('/\s+/', '_', $RawDBColumn));
            $RawDBColumn=preg_replace('/[^A-Za-z0-9\-\_]/', '', $RawDBColumn);
            $RawDBColumn=Str::of($RawDBColumn)->ascii();
            if (Str::length($RawDBColumn)>50)
            {
                $length=Str::length($RawDBColumn);
                $first=Str::substr($RawDBColumn,0,30);
                //$middle=
                $last=Str::substr($RawDBColumn,$length-20,$length);
                $RawDBColumn=$first.'_'.$last;


            }
            return $RawDBColumn;

        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function InsertIntoDb($ProcessedDBSurvey, $asset_id, $survey_content_hash)
    {
        try {
            //$insert_status=KoboDbColumn::insert($ProcessedDBSurvey->toArray());
            $SurveySchemaExist = KoboSurveySchema::where('survey_hash', $survey_content_hash)->where('asset_id',$asset_id)->exists();
            if ($SurveySchemaExist)
            {
                $insert_status=KoboDbColumn::insert($ProcessedDBSurvey->toArray());
                if(!$insert_status)
                {
                    return "Not inserted";
                }
            }
            return KoboDbColumn::where('asset_id', $asset_id)->get();
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function StartMaterialization($assetId)
    {
        try {
            $IgnoredColumns=['note','begin_group','end_group','begin_kobomatrix','end_kobomatrix','begin_score','end_score'];
            $DBTable=KoboDbColumn::whereNotIn('type',$IgnoredColumns)->orwhereNull('type')->where('asset_id',$assetId)->get();
            $DBTableWithColumnTypes=$this->getColumnTypeForMaterialization($DBTable);
            return $DBTableWithColumnTypes;
            
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function getColumnTypeForMaterialization($DBTable)
    {
        try {
            $MaterialJson=array();
            $MaterialQuery=collect($DBTable)->map(function ($item, $key) use ($MaterialJson) {

                $MaterialJson[$item->db_column_name]=$this->getDbColumnType($item->type);
                return $MaterialJson;

            });
            return $MaterialQuery;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function getDbColumnType($type)
    {
        try {
            //return $type;
            if (is_null($type) || $type=='text')
            {
                return "longText";
            }
            if ($type=='select_one' || $type=='string' || $type=='geopoint' || $type=='select_multiple' || $type=='score__row')
            {
                return "longText";
            }
            if ($type=='integer' || $type=='decimal' ||$type=='integer' || $type=='calculate' )
            {
                return "double";
            }
            if ($type=='start' || $type=='end' || $type=='datetime')
            {
                return "timestamp";
            }
            return $type;

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
     * @param  \App\Models\KoboDBController  $koboDBController
     * @return \Illuminate\Http\Response
     */
    public function show(KoboDBController $koboDBController)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\KoboDBController  $koboDBController
     * @return \Illuminate\Http\Response
     */
    public function edit(KoboDBController $koboDBController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\KoboDBController  $koboDBController
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, KoboDBController $koboDBController)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\KoboDBController  $koboDBController
     * @return \Illuminate\Http\Response
     */
    public function destroy(KoboDBController $koboDBController)
    {
        //
    }
}
