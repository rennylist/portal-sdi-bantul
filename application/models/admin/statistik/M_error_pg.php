<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_error_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->db2 = $this->load->database('pg', TRUE);
    }

    function insert($params)
    {

        return $this->db2->insert('trx_upload', $params);
    }

    function insert_detail($params)
    {
        //print_r($params);
        return $this->db2->insert('trx_upload_error', $params);
    }
}
