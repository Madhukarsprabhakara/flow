<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function DeleteRangeFromCollection($collection, $start, $end)
    {
        try {
            $DeleteIndex=array();
            $DeleteIndexCollect=collect();
            array_push($DeleteIndex,collect()->range($start, $end));
            $DeleteIndexCollect=collect($DeleteIndex);
            //return $collection;
            return $collection->except($DeleteIndexCollect->flatten())->flatten();
            
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function DeleteIndexesFromCollection($collection, $DeleteGroups)
    {
        try {
            $DeleteIndex=array();
            foreach ($DeleteGroups as $value) {
              
              array_push($DeleteIndex,collect()->range($value[0], $value[1]));
                
            }
            $DeleteIndexCollect=collect($DeleteIndex);
            $SurveySchemaMatrixQuestions = $collection->except($DeleteIndexCollect->flatten());
            return $SurveySchemaMatrixQuestions->flatten();
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function index()
    {
        //
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
