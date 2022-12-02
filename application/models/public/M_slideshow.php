<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_slideshow extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // list data
    function get_datas() {
        $sql = "SELECT *
                FROM trx_slideshow
                WHERE 
                publish_st = 'yes'
                ORDER BY slideshow_order ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

}

?>