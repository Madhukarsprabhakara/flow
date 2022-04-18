<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\CollectionController;

class KoboController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        try {
            
            
        }
        catch (\Exception $e)
        {

        }
    }
    public function DiscoverSchema()
    {
        try {
            //My token - 5f8a36355b288bb6f7d65f36a3c6e112a7707567
            //Fairtrade token - 445fff6e62891f56db7235772cce0df7605caad7 - asj6qHgZxYmyqV9x3zmHmW
            //OOrja token - 1da35c62389c0df4b9e88766bf4e7ab4ebd3d948 - ax3tAgSgKwjnGgmpiN2QXb
            //fetch schema from kobo API

            //asset id - 2 repeat groups aUTFHegPoonczSUh7S4hNA
            //one repeat grup aS4CWuK8uM6a6MximvHLNd

            $StdFields=[['type' => 'integer', '$autoname'=>'_id'], ['type' => 'string', '$autoname'=>'formhub/uuid'], ['type'=>'string', '$autoname'=>'__version__'],['type'=>'string', '$autoname'=>'meta/instanceID'], ['type'=>'string', '$autoname'=>'_xform_id_string'], ['type'=>'string', '$autoname'=>'_uuid'], ['type'=>'string', '$autoname'=>'_attachments'], ['type'=>'string', '$autoname'=>'_status'], ['type'=>'string', '$autoname'=>'_geolocation'], ['type'=>'string', '$autoname'=>'_submission_time'], ['type'=>'string', '$autoname'=>'_tags'], ['type'=>'string', '$autoname'=>'_notes'], ['type'=>'string', '$autoname'=>'_validation_status'], ['type'=>'string', '$autoname'=>'_submitted_by'],['type'=>'string', '$autoname'=>'meta/deprecatedID']];

            //Humanitarian account
            // $response = Http::withHeaders([
            //     'Authorization' => 'Token 5f8a36355b288bb6f7d65f36a3c6e112a7707567'
                
            // ])->get('https://kobo.humanitarianresponse.info/api/v2/assets/aUTFHegPoonczSUh7S4hNA/?format=json');

            //Researchers account
            $response = Http::withHeaders([
                'Authorization' => 'Token 1da35c62389c0df4b9e88766bf4e7ab4ebd3d948',
                'Accept' => 'application/json'
                
            ])->get('https://kf.kobotoolbox.org/api/v2/assets/ax3tAgSgKwjnGgmpiN2QXb/');
            
            $response_body=json_decode($response->body());
            //return $response_body;
            $SurveySchemaResponse=$response_body->content->survey;
            $SurveySchemaChoices=$response_body->content->choices;
            //return $SurveySchemaResponse;
            $SurveySchemaCollection = collect($SurveySchemaResponse);
            $RepeatGroups=$this->GetRepeatGroupsSchema($SurveySchemaCollection);
            
            $SurveySchemaWithoutRepeatGroups=$this->GetSurveySchemaWithoutRepeatGroups($SurveySchemaCollection);
            //return $SurveySchemaWithoutRepeatGroups;
            $SurveySchemaWithkoboMatrix=$this->GetSurveySchemaWithKoboMatrixQuestions($SurveySchemaWithoutRepeatGroups, $SurveySchemaChoices);
            //return $SurveySchemaWithoutRepeatGroups;
            $RepeatGroupsWithStdFields=$this->AddStdFieldsToRepeatGroupSchema($RepeatGroups);
            //return $RepeatGroupsWithStdFields;
            $SurveySchemaWithStdFields=$this->AddStdFieldsToNonRepeatGroupSchema($SurveySchemaWithkoboMatrix,$StdFields);

            //$SurveySchemaWithStdDataFields=$this->CheckSchemaFieldsWithDataFields($SurveySchemaWithStdFields);
            $schema['repeat_groups']=$RepeatGroupsWithStdFields;
            $schema['main_survey']=$SurveySchemaWithStdFields;
            return $schema;
        }
        catch (\Exception $e)
        {

        }
    }
    public function GetRepeatGroupsSchema($SurveySchemaCollection)
    {
        try {
            //get the repeat group pairs begin_repeat and end_repeat indexes
            
            $groups=$this->GetRepeatGroupIndex($SurveySchemaCollection);
            //extract the repeat groups and questions within only
            //extract schema without the repeat groups 
            $SurveySchemaCollectionCopy = $SurveySchemaCollection->collect();
            $repeat_groups=array();
            foreach ($groups as $value) { 
                    //return $value[1];
                    $numbrs=$value[1]-$value[0]+1;
                    $intermediate=$SurveySchemaCollection->slice($value[0],$numbrs);
                   // $SurveySchemaCollectionCopy=$this->GetSurveySchemaWithoutRepeatGroups($SurveySchemaCollectionCopy,$value[0],$numbrs);

                    array_push($repeat_groups,$this->GetRepeatGroupQuestionsOnly($intermediate));
            }
            return $repeat_groups;
            //dd($SurveySchemaCollectionCopy);
        }
        catch(\Exception $e)
        {

        }
    }
    public function GetRepeatGroupQuestionsOnly($intermediate)
    {
        try {
            $result = collect($intermediate)->reject(fn ($item) => in_array($item->type, ['begin_repeat', 'end_repeat']));
            return array_values($result->toArray());
        }
        catch(\Exception $e)
        {

        }
    }
    public function GetSurveySchemaWithKoboMatrixQuestions($SurveySchemaWithoutRepeatGroups, $SurveySchemaChoices)
    {
        try {
            $MatrixGroups=$this->GetKoboMatrixGroupIndex($SurveySchemaWithoutRepeatGroups);
            //return $MatrixGroups;
            if (count($MatrixGroups->toArray())==0)
            {
                return $SurveySchemaWithoutRepeatGroups;
            }

            $DeleteIndex=array();
            $DeleteIndexCollect=collect();
            foreach ($MatrixGroups as $value) {
              
              array_push($DeleteIndex,collect()->range($value[0], $value[1]));
                
            }
            $DeleteIndexCollect=collect($DeleteIndex);
            $SurveySchemaMatrixQuestions = $SurveySchemaWithoutRepeatGroups->except($DeleteIndexCollect->flatten());
            $SurveySchemaWithoutMatrixQuestions=collect($SurveySchemaMatrixQuestions->all())->flatten();
            $SurveySchemaWithMatrixWithoutGroups=$this->GetMatrixRowColumnCombination($SurveySchemaWithoutRepeatGroups, $SurveySchemaWithoutMatrixQuestions, $MatrixGroups, $SurveySchemaChoices);
            return $SurveySchemaWithMatrixWithoutGroups;
        }
        catch (\Exception $e)
        {

        }
    }
    public function GetMatrixRowColumnCombination($SurveySchemaWithoutRepeatGroups, $SurveySchemaWithoutMatrixQuestions, $MatrixGroups, $SurveySchemaChoices)
    {
        try {
            
            $MatrixRowsColumnsCollection=collect();
            //$SurveySchemaWoOrigMatrixQs=$SurveySchemaWithoutRepeatGroups;
            foreach ($MatrixGroups as $value) {
                $MatrixQuestions=$SurveySchemaWithoutRepeatGroups->only(collect()->range($value[0], $value[1]));
                $MatrixList=$MatrixQuestions->flatten()[0]->{'kobo--matrix_list'};
                $MatrixGroupName=$MatrixQuestions->flatten()[0]->{'$autoname'};
                $filtered=collect($SurveySchemaChoices)->filter(function ($value, $key) use ($MatrixList) {
                    return $value->list_name==$MatrixList;

                });
                $MatrixRows=collect($filtered->all())->flatten();
                $GroupNameMatrixRows = collect($MatrixRows)->map(function ($item, $key) use ($MatrixGroupName) {
                    $item->db_val=$MatrixGroupName.'_'.$item->{'$autovalue'};
                    return $item;
                });
                $MatrixQuestionsFlattened=$MatrixQuestions->flatten();
                $MatrixQuestionsFlattenedWoSE = collect($MatrixQuestionsFlattened)->reject(fn ($item) => in_array($item->type, ['end_kobomatrix', 'begin_kobomatrix']));
                $MatrixQuestionsFlattenedWoSE=$MatrixQuestionsFlattenedWoSE->flatten();
                $GroupNameMatrixRowsColumns=array();
                $i=0;
                //return $MatrixQuestionsFlattenedWoSE;
                foreach ($GroupNameMatrixRows as $GroupNameMatrixRow)
                {   
                    
                    //return $GroupNameMatrixRow->label[0];
                    foreach ($MatrixQuestionsFlattenedWoSE as $MatrixQuestionFlattenedWoSE)
                    {
                            //return $MatrixQuestionFlattenedWoSE;
                            
                            $GroupNameMatrixRowsColumns[$i] =json_decode(json_encode($GroupNameMatrixRow), true);

                            $GroupNameMatrixRowsColumns[$i]['$autoname']=$GroupNameMatrixRow->db_val.'/'.$GroupNameMatrixRow->db_val.'_'.$MatrixQuestionFlattenedWoSE->{'$autoname'};
                            $GroupNameMatrixRowsColumns[$i]['raw_db_column']=$GroupNameMatrixRow->label[0].'_'.$MatrixQuestionFlattenedWoSE->label[0];
                            $i++;
                           
                    }
                    
                }
                $GroupNameMatrixRowsColumns=collect($GroupNameMatrixRowsColumns)->prepend($MatrixQuestions[$value[0]]);
                $GroupNameMatrixRowsColumns=collect($GroupNameMatrixRowsColumns)->push($MatrixQuestions[$value[1]]);
                //return $GroupNameMatrixRowsColumns;
                $MatrixRowsColumnsCollection=$MatrixRowsColumnsCollection->merge($GroupNameMatrixRowsColumns);
                
                $CollectionObj=new CollectionController;
                $SurveySchemaWoOrigMatrixQs=$CollectionObj->DeleteIndexesFromCollection($SurveySchemaWithoutRepeatGroups, $MatrixGroups);
                
                //delete matrix questions from the survey without groups
                //add the processed matirx questions into the above survey
                //return $merged;
                //create a mxn data keys
                //group_ax7qc79_row/group_ax7qc79_row_column
                

                //get the group name from autoname
                //get the matrix--list
                //
                //$replaced = $SurveySchemaWithoutRepeatGroups->replace([$value[0]+1 => 'Victoria']);
                //return $replaced->all();
            }
            $merged = collect($SurveySchemaWoOrigMatrixQs)->merge($MatrixRowsColumnsCollection);
            return $merged;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function GetKoboMatrixGroupIndex($SurveySchemaCollection)
    {
        try {
            $RepeatGroupPairs = $SurveySchemaCollection->map(function ($item, $key) {
                if ($item->type=='begin_kobomatrix')
                {
                    return $key;
                }
                if ($item->type=='end_kobomatrix')
                {
                    return $key;
                }
                
            });
            //Remove nulls so only pairs or repeat groups remain;
            //return $RepeatGroupPairs;
            $NullFilteredRepeatGroupPairs = $RepeatGroupPairs->filter(function ($value, $key) {
                return $value !=null;
            });
            //return $RepeatGroupPairs;
            //Create a pair of repeat group if more than one repeat group;
            if (count($NullFilteredRepeatGroupPairs)>2)
            {   
                $no_of_groups=count($NullFilteredRepeatGroupPairs)/2;
                $groups = $NullFilteredRepeatGroupPairs->split($no_of_groups);
            }
            else
            {
                //if just one repeat group exists
                $groups = $NullFilteredRepeatGroupPairs->split(1);
                //return $groups;
            }
            return $groups;
        }
        catch (\Exeption $e)
        {

        }
    }
    public function GetSurveySchemaWithoutRepeatGroups($SurveySchemaCollection)
    {
        try {
            $groups=$this->GetRepeatGroupIndex($SurveySchemaCollection);
            
            if (count($groups->toArray())==0)
            {
                return $SurveySchemaCollection;
            }
            $DeleteIndex=array();
            $DeleteIndexCollect=collect();
            foreach ($groups as $value) {
              array_push($DeleteIndex,collect()->range($value[0]+1, $value[1]));
                
            }
            $DeleteIndexCollect=collect($DeleteIndex);
            $SurveySchemaWithoutGroups = $SurveySchemaCollection->except($DeleteIndexCollect->flatten());
            return collect($SurveySchemaWithoutGroups->all())->flatten();
        }
        catch(\Exception $e)
        {

        }

    }
    public function GetRepeatGroupIndex($SurveySchemaCollection)
    {
        try {
            $RepeatGroupPairs = $SurveySchemaCollection->map(function ($item, $key) {
                if ($item->type=='begin_repeat')
                {
                    return $key;
                }
                if ($item->type=='end_repeat')
                {
                    return $key;
                }
                
            });
            //Remove nulls so only pairs or repeat groups remain;
            $NullFilteredRepeatGroupPairs = $RepeatGroupPairs->filter(function ($value, $key) {
                return $value !=null;
            });
            //return $RepeatGroupPairs;
            //Create a pair of repeat group if more than one repeat group;
            if (count($NullFilteredRepeatGroupPairs)>2)
            {
                $no_of_groups=count($NullFilteredRepeatGroupPairs)/2;
                $groups = $NullFilteredRepeatGroupPairs->split($no_of_groups);
            }
            else
            {
                //if just one repeat group exists
                $groups = $NullFilteredRepeatGroupPairs->split(1);
                //return $groups;
            }
            return $groups;
        }
        catch (\Exception $e)
        {

        }
    }
    public function getData()
    {
        try {
            //My token - 5f8a36355b288bb6f7d65f36a3c6e112a7707567
            //Fairtrade token - 445fff6e62891f56db7235772cce0df7605caad7 - asj6qHgZxYmyqV9x3zmHmW
            //OOrja token - 1da35c62389c0df4b9e88766bf4e7ab4ebd3d948 - ax3tAgSgKwjnGgmpiN2QXb
            //fetch schema from kobo API
            //Humanitarian account
            // $response = Http::withHeaders([
            //     'Authorization' => 'Token 5f8a36355b288bb6f7d65f36a3c6e112a7707567'
                
            // ])->get('https://kobo.humanitarianresponse.info/api/v2/assets/aUTFHegPoonczSUh7S4hNA/data/?format=json&limit=6');

            //Researchers account
            $response = Http::withHeaders([
                'Authorization' => 'Token 1da35c62389c0df4b9e88766bf4e7ab4ebd3d948',
                'Accept' => 'application/json'
                
            ])->get('https://kf.kobotoolbox.org/api/v2/assets/ax3tAgSgKwjnGgmpiN2QXb/data/?limit=2');

            
            // $response = Http::withHeaders([
            //     'Authorization' => 'Token 5f8a36355b288bb6f7d65f36a3c6e112a7707567'
                
            // ])->get('https://kobo.humanitarianresponse.info/api/v2/assets/aS4CWuK8uM6a6MximvHLNd/?format=json');
            $response_body=json_decode($response->body());
            return $response_body;
            //Go over the schema and add all the questions
            //fetch data from kobo api
            
        }
        catch (\Exception $e)
        {

        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function AddStdFieldsToRepeatGroupSchema($RepeatGroups)
    {
        try {
            //return collect($RepeatGroups[0])->prepend(['foo' => 10]);
            if (!$RepeatGroups)
            {
                return;
            }
            $RepeatGroupsWithStdFields=[];
            for ($i=0;$i<count($RepeatGroups);$i++)
            {
                $RepeatGroupsWithStdFields[]=collect($RepeatGroups[$i])->prepend(['type' => 'integer', '$autoname'=>'_id']);
                //return $RepeatGroups;
            }

            return $RepeatGroupsWithStdFields;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }   
    }
    public function AddStdFieldsToNonRepeatGroupSchema($SurveySchemaWithoutRepeatGroups, $StdFields)
    {
        try {
            //return $SurveySchemaWithoutRepeatGroups; 
            
            $StdFieldsIfNotPresent=[['type'=>'string', '$autoname'=>'start'],['type'=>'string', '$autoname'=>'end']];
            foreach ($StdFields as $StdField)
            {
                $SurveySchemaWithoutRepeatGroups=collect($SurveySchemaWithoutRepeatGroups)->prepend($StdField);
            }   
            
            //$SurveySchemaWithoutRepeatGroups=collect($SurveySchemaWithoutRepeatGroups)->prepend(['type' => 'string', 'name'=>'formhub/uuid']);
            return $SurveySchemaWithoutRepeatGroups;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function CheckSchemaFieldsWithDataFields($SurveySchemaWithoutRepeatGroups)
    {
        try {
            //get the first record
            //return $SurveySchemaWithoutRepeatGroups;
            //Humanitarian account
            $response = Http::withHeaders([
                'Authorization' => 'Token 5f8a36355b288bb6f7d65f36a3c6e112a7707567'
                
            ])->get('https://kobo.humanitarianresponse.info/api/v2/assets/aS4CWuK8uM6a6MximvHLNd/data/?format=json&limit=1');


            //Researchers account
            // $response = Http::withHeaders([
            //     'Authorization' => 'Token 1da35c62389c0df4b9e88766bf4e7ab4ebd3d948',
            //     'Accept' => 'application/json'
                
            // ])->get('https://kf.kobotoolbox.org/api/v2/assets/ax3tAgSgKwjnGgmpiN2QXb/data/?limit=1');
            //return $response->body();
            $response_body=json_decode($response->body());
            $data_array=$response_body->results[0];

            $data_array_one=collect($data_array)->keys();
            $DataKeysWithoutGroups = collect($data_array_one)->map(function ($item, $key) {
                if (!in_array($item,['meta/instanceID','meta/deprecatedID','formhub/uuid']))
                {
                    $ItemExploded=explode("/", $item);
                    $ItemData=collect($ItemExploded)->pop();
                    return $ItemData;
                }
                else
                {
                    return $item;
                }
            });

            //return collect($DataKeysWithoutGroups)->all();

            //compare the keys with keys in the schema
            $SurveySchemaWithoutRepeatGroups=json_decode($SurveySchemaWithoutRepeatGroups);
            //return $SurveySchemaWithoutRepeatGroups;
            $SurveySchemaWithoutRepeatGroupsEndGroup = collect($SurveySchemaWithoutRepeatGroups)->reject(fn ($item) => in_array($item->type, ['end_group']));
            //$result2 = collect($result)->reject(fn ($item) => in_array($item->{'$autoname'}, ['meta/instanceID','meta/deprecatedID']));
            $plucked=collect($SurveySchemaWithoutRepeatGroupsEndGroup)->pluck('$autoname');
            //return $plucked->all();
            $diff=collect($DataKeysWithoutGroups->all())->diff(collect($plucked->all()));
            $DataFields=array_values($diff->all());
            //return $DataFields;
            return $SchemaSurveyWithDataFields=$this->AddDataFieldsToSchemaField($DataFields, $SurveySchemaWithoutRepeatGroupsEndGroup);
            
            //add the ones that are not found in the schema to schema collection

        }
        catch (\Exception $e)
        {

        }
    }
    public function AddDataFieldsToSchemaField($DataFields, $SurveySchemaWithoutRepeatGroups)
    {
        try {
            $ModifiedFieldsAray=array();
            $DataFieldsArray=collect($DataFields)->map(function ($item, $key) use($ModifiedFieldsAray) {
                $ModifiedFieldsAray['type']= 'string';
                $ModifiedFieldsAray['$autoname']= $item;
                $ModifiedFieldsAray['field_type']= 'data_field';
                return $ModifiedFieldsAray;
            });
            return $this->AddStdFieldsToNonRepeatGroupSchema($SurveySchemaWithoutRepeatGroups, $DataFieldsArray);

            //return $DataFieldsArray;
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
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
