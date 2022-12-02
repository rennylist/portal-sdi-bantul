<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class M_indicator_data_detail extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function get_history_by_params($params)
    {
        $sql = "SELECT * 
                FROM `trx_indicator_data_detail` a
                WHERE
                a.`data_id` = ?
                AND a.`year` =  ?
                ORDER BY a.`mdd` DESC
                LIMIT 1,100";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_history_by_params_limit($params)
    {
        $sql = "SELECT * 
                FROM `trx_indicator_data_detail` a
                WHERE
                a.`data_id` = ?
                AND a.`year` =  ?
                AND a.`submission_st` = 'approved'
                ORDER BY a.`mdd` DESC
                LIMIT 1,1";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function insert($params) {
        return $this->db->insert('trx_indicator_data_detail', $params);
    }

    // delete trx_slideshow
    function delete($params) {
        return $this->db->delete('trx_indicator_data_detail', $params);
    }

    
    function update($params, $where) {
        return $this->db->update('trx_indicator_data_detail', $params, $where);
    }
}
