<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            //My token - 5f8a36355b288bb6f7d65f36a3c6e112a7707567
            //Fairtrade token - 445fff6e62891f56db7235772cce0df7605caad7 - asj6qHgZxYmyqV9x3zmHmW
            //fetch schema from kobo API

            //asset id - 2 repeat groups aUTFHegPoonczSUh7S4hNA
            //one repeat grup aS4CWuK8uM6a6MximvHLNd
            $response = Http::withHeaders([
                'Authorization' => 'Token 5f8a36355b288bb6f7d65f36a3c6e112a7707567'
                
            ])->get('https://kobo.humanitarianresponse.info/api/v2/assets/aS4CWuK8uM6a6MximvHLNd/?format=json');

            $response_body=json_decode($response->body());
            //return $response_body->content->survey;
            $json = '[{}]';
            $encodedJson = json_decode($json);
            $encodedJson=$response_body->content->survey;
            $collection = collect($encodedJson);
            //return $collection;
            //check for multiple repeat groups
            $multiplied = $collection->map(function ($item, $key) {
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
            $filtered = $multiplied->filter(function ($value, $key) {
                return $value !=null;
            });
            //Create a pair of repeat group;
            if (count($filtered)>2)
            {
                $groups = $filtered->split(2);
            }
            else
            {
                //if just one repeat group exists
                $groups = $filtered->split(1);
                //return $groups;
            }
            
            //extract the repeat groups and questions within only
            foreach ($groups as $value) { 
                    //return $value[1];
                    $numbrs=$value[1]-$value[0]+1;
                    $intermediate=$collection->slice($value[0],$numbrs);
                    $result = collect($intermediate)->reject(fn ($item) => in_array($item->type, ['begin_repeat', 'end_repeat']));
                    return $result;
                
            }

            //return array_search("begin_repeat", array_column($encodedJson, 'type'));
            //array_search("end_repeat", array_column($encodedJson, 'type'));
            //$collection->search("begin_repeat", array_column($encodedJson, 'type'));
            //return array_search("end_repeat", array_column($encodedJson, 'type'));
            $intermediate=$collection->slice(array_search("begin_repeat", array_column($encodedJson, 'type')), array_search("end_repeat", array_column($encodedJson, 'type')));
            $result = collect($intermediate)->reject(fn ($item) => in_array($item->type, ['begin_repeat', 'end_repeat']));
            return $result;
            //Go over the schema and add all the questions
            //fetch data from kobo api
            
        }
        catch (\Exception $e)
        {

        }
    }
    public function check($number){
        if($number % 2 == 0){
            return false; 
        }
        else{
            return true;
        }
    }
    public function getData()
    {
        try {
            //My token - 5f8a36355b288bb6f7d65f36a3c6e112a7707567
            //Fairtrade token - 445fff6e62891f56db7235772cce0df7605caad7 - asj6qHgZxYmyqV9x3zmHmW
            //fetch schema from kobo API
            $response = Http::withHeaders([
                'Authorization' => 'Token 5f8a36355b288bb6f7d65f36a3c6e112a7707567'
                
            ])->get('https://kobo.humanitarianresponse.info/api/v2/assets/aS4CWuK8uM6a6MximvHLNd/data/?format=json&limit=1');


            
            // $response = Http::withHeaders([
            //     'Authorization' => 'Token 5f8a36355b288bb6f7d65f36a3c6e112a7707567'
                
            // ])->get('https://kobo.humanitarianresponse.info/api/v2/assets/aS4CWuK8uM6a6MximvHLNd/?format=json');
            $response_body=json_decode($response->body());
            dd($response_body);
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
