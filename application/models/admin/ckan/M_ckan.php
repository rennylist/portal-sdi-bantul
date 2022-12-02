<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
    use GuzzleHttp\Client;

class M_ckan extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

function test(){

  $result="model test";
  return $result;
}

    function post_create_dataset($uuid_owner_org=null,$name_text=null,$title_text=null,$bol_private=boole)
    {

      try{
      $client = new Client([
                            'base_uri' => 'https://data.bantulkab.go.id',
                              //Header
                            'headers' => [
                              'Authorization'     => 'b85ce9ce-862d-41d9-bd79-15e7aa7fe103'
                            ],
                              // 'timeout' => 100.0,
                          ]);
      $response = $client->request('POST', '/api/3/action/package_create',[
                              'form_params' => [                            
                                'owner_org' => $uuid_owner_org,
                                'name' => $name_text,
                                'title' => $title_text,
                                'private' => $bol_private,                          
                              ]

                          ]);
    $result = json_decode($response->getBody()->getContents(),true); //OBYEK FORMAT

    $get_id_dataset=$result['result']['id'];
    // print_r($get_id_dataset);

    // CREATE RESOURCE
    // redirect('Ckan_post/post_create_resource/'.$get_id_dataset);

    return $result;

    }catch(\Exception $e){
      // print_r($e->getMessage());
      $Err_send=$e->getResponse()->getBody()->getContents();
      print_r(json_decode($Err_send, true));

      die;
    }

  }
    
    function post_create_resource($get_package_id=null,$name_resource=null,$nama_file=null)
    {

      try{
      $client = new Client([
                              'base_uri' => 'https://data.bantulkab.go.id',
                              //Header
                            'headers' => [
                              'Authorization'     => 'b85ce9ce-862d-41d9-bd79-15e7aa7fe103'
                            ],
                              // 'timeout' => 100.0,
                          ]);
      $response = $client->request('POST', '/api/3/action/resource_create',[
                              'multipart' => [
                                [
                                  'name' => 'package_id',
                                  'contents' => $get_package_id,
                                ],
                              
                                [
                                  'name' => 'name',
                                  'contents' => $name_resource,
                                ],
                              
                                [
                                  'name' => 'upload',
                                  'contents' => fopen(FCPATH . 'assets/docs/'.$nama_file,'rb'),
                                ],
                              
                                // [
                                // 'package_id' => $get_package_id,
                                // 'name' => 'testresource',
                                // 'upload' => fopen(FCPATH . 'assets/dikpora.xlsx','rb'),
                                // ]
                              ]

                          ]);
    $result = json_decode($response->getBody()->getContents(),true); //OBYEK FORMAT

    return $result;
    }catch(\Exception $e){
      print_r($e->getMessage());
      // $Err_send=$e->getResponse()->getBody()->getContents();
      // print_r(json_decode($Err_send, true));

      die;
    }

    }





    function get_organisation()
    {
      $client = new Client([
        'base_uri' => 'https://data.bantulkab.go.id',
        'timeout' => 100.0,
      ]);
      $response = $client->request('GET', '/api/3/action/organization_list?include_private=true&rows=999&all_fields=true',[
      // $response = $client->request('GET', '/api/3/action/organization_list',[
        
        //Authorization
        // 'auth' => ['diskominfo_bantul','b85ce9ce-862d-41d9-bd79-15e7aa7fe103'], 
        
        //Header
        'headers' => [
          'Authorization'     => 'b85ce9ce-862d-41d9-bd79-15e7aa7fe103'
          ]

        //Params
        // 'query' => [
        //   'include_private' => 'true',
        //   'rows' => '999',
        //   'all_fields' => 'true'
        //]
      ]);
      $result = json_decode($response->getBody()->getContents(),true); //OBYEK FORMAT

      print_r($result);
      die;

    return $result;
    }



    function get_organisation_by_id()
    {
      $client = new Client([
        'base_uri' => 'https://data.bantulkab.go.id',
        'timeout' => 100.0,
    ]);
    $response = $client->request('GET', '/api/3/action/organization_list?include_private=true&rows=999&all_fields=true',[
    // $response = $client->request('GET', '/api/3/action/organization_list',[
      // 'auth' => ['',''], //Authorization
      // 'query' => [
      //   'include_private' => 'true',
      //   'rows' => '999',
      //   'all_fields' => 'true',
      //   'id' => 'be0cc93d-1796-4603-97dc-51ccb993face'
      // ]
    ]);
    $result = json_decode($response->getBody()->getContents(),true); //OBYEK FORMAT

    print_r($result);
    die;

    return $result;
    }





}