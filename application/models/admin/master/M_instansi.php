<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_instansi extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // list data pokok
    function get_datas_all() {
        $sql = "SELECT 
                a.*
                FROM 
                mst_instansi a
                WHERE 
                show_st = 'yes'
                ORDER BY instansi_name ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_detail_instansi($params) {
        $sql = "SELECT 
                a.*
                FROM 
                mst_instansi a
                WHERE 
                instansi_cd = ?
                ORDER BY instansi_name ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    function get_total_by_params($params) {
        $sql = "SELECT 
                SUM(IF(b.`data_type` = 'indicator',1,0)) AS 'tot_indicator',
                SUM(IF(b.`data_type` = 'variable',1,0)) AS 'tot_variable',
                SUM(IF(b.`data_type` = 'subvariable',1,0)) AS 'tot_subvariable',
                SUM(IF(b.`data_type` = 'subsubvariable',1,0)) AS 'tot_subsubvariable'
                FROM `trx_indicator_privileges` a
                LEFT JOIN `trx_indicator` b ON a.`data_id` = b.`data_id`
                WHERE
                a.`year` = ?
                AND a.`instansi_cd` = ? ";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }
}