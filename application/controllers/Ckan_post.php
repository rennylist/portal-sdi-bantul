<?php

use GuzzleHttp\Client;

class Ckan_post extends CI_Controller
{
      function __construct()
      {
        parent::__construct();
        $this->load->model('admin/ckan/M_ckan');
      }
      
          
    function send($uuid_owner_org=null,$name_text=null,$title_text=null,$name_resource=null,$nama_file=null,$bol_private=null)
    {

      $return_get_dataset=$this->M_ckan->post_create_dataset($uuid_owner_org,$name_text,$title_text,$bol_private);
      $process_result="Create DATASET"."<br/>";
      $get_id_dataset=$return_get_dataset['result']['id'];
      $dataset_success=$return_get_dataset['success'];
      // if($dataset_success==1){
      //   print_r("dataset = success, ");
      // }else{
      // print_r("gagal membuat dataset");
      // }
      $process_result=$process_result."id dataset: ".$get_id_dataset."<br/>";
      $process_result=$process_result."Create resource"."<br/>";
      $return_get_resources=$this->M_ckan->post_create_resource($get_id_dataset,$name_resource,$nama_file);
      $status_resource=$return_get_resources['success'];
      // if($status_resource==1){
      //   print_r("Resource = success, ");
      // }else{
      //   print_r("gagal membuat resource");
      // }
      $process_result=$process_result."id resource: ".$return_get_resources['result']['id'];
      print_r($process_result);

      return $process_result;
    }


}
