<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_create_ba_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->db2 = $this->load->database('pg', TRUE);
        
    }

    public function get_data_all_by_urusan_instansi($params)
    {
        $sql = "SELECT 
        a.*
        FROM trx_indicator_data a
        LEFT JOIN trx_indicator b ON a.data_id = b.data_id
        WHERE
        b.instansi_cd = ?
        AND a.data_id LIKE ?
        AND a.year = ?
        AND b.active_st = 'yes'";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_instansi($params)
    {
        $sql = "SELECT a.*,b.data_st, b.value, b.year,  b.submission_st, b.detail_id, b.data_id
        FROM trx_indicator a
        LEFT JOIN trx_indicator_data b ON a.data_id = b.data_id
        WHERE
        a.active_st = 'yes'
        AND a.instansi_cd = ?
        AND b.year = ?

        AND b.submission_st = 'approved'
     
        ORDER BY a.data_id ASC";

        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_instansi_unduh_rincian($params)
    {

        $sql = "SELECT b.data_id, b.data_name, b.data_type, c.year, b.data_unit, c.value, c.data_st
        FROM trx_beritaacara_detail a
        LEFT JOIN trx_indicator b ON a.data_id = b.data_id
        LEFT JOIN trx_indicator_data c ON a.data_id = c.data_id
        WHERE
        a.ba_id = ?
        AND b.active_st = 'yes'
        AND b.instansi_cd = ?
        AND c.year = ?

        AND c.submission_st = 'approved'
       
        ORDER BY c.data_id";

        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_instansi_count($params)
    {
        $sql = "SELECT COUNT(*) AS total
        FROM trx_indicator a
        LEFT JOIN trx_indicator_data b ON a.data_id = b.data_id
        WHERE
        a.active_st = 'yes'
        AND a.instansi_cd = ?
        AND b.year = ?

        AND b.submission_st = 'approved'
      
       ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    public function get_sum_data_indicator($params)
    {
        $sql = "SELECT 
              
                COUNT(CASE WHEN a.data_type = 'indicator' THEN 1 END) AS sum_indicator,
                COUNT(CASE WHEN a.data_type = 'variable' THEN 1 END) AS sum_variabel,
                COUNT(CASE WHEN a.data_type = 'subvariable' THEN 1 END) AS sum_sub_variabel,
                COUNT(CASE WHEN a.data_type = 'subsubvariable' THEN 1 END) AS sum_sub_sub_variabel

               FROM trx_indicator a
                LEFT JOIN trx_indicator_data b ON a.data_id = b.data_id
                WHERE
                a.active_st = 'yes'
                AND a.instansi_cd = ?
                AND b.year = ?
                AND b.submission_st = 'approved'
                ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_berita_acara_all($params)
    {
        $sql = "SELECT a.*
        FROM trx_beritaacara a
        WHERE instansi_cd = ? 
        ORDER BY ba_id DESC
        ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_berita_acara_by_ba_id($params)
    {
        $sql = "SELECT a.*
        FROM trx_beritaacara a
        WHERE instansi_cd = ? 
        AND ba_id = ?
        ORDER BY ba_id ASC 
        ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_berita_acara($params)
    {
        $sql = "SELECT a.*
        FROM trx_beritaacara a
        WHERE instansi_cd = ? 
        AND ba_year = ?    
        ORDER BY ba_id ASC 
        ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    function insert($params)
    {
        $this->db2->insert('trx_beritaacara', $params);
    }

    function insert_detail($params)
    {
        $this->db2->insert_batch('trx_beritaacara_detail', $params);
    }


    public function update_ba($params, $where)
    {
        return $this->db2->update('trx_beritaacara', $params, $where);
    }
}
