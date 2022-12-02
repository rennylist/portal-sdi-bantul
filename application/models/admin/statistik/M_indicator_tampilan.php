<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_indicator_tampilan extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

   

    public function get_indicator_for_search($params)
    {
        $sql = "SELECT * 
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND a.`data_type` = 'indicator' 
                ORDER BY a.`data_id` ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_data_by_params($params)
    {
        $sql = "SELECT * 
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND a.`data_id` LIKE ?
                ORDER BY a.`data_id` ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

}
