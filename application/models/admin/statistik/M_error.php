<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_error extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function insert($params) {
        return $this->db->insert('trx_upload', $params);
    }

    function insert_detail($params) {
        return $this->db->insert('trx_upload_error', $params);
    }


}
