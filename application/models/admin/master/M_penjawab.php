<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_penjawab extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // list data pokok
    function get_datas_all() {
        $sql = "SELECT 
                a.*
                FROM 
                mst_penjawab a
                ORDER BY penjawab_nm ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_datas_params($params) {
        $sql = "SELECT 
                a.*
                FROM 
                mst_penjawab a
                WHERE 
                a.penjawab_nm LIKE ?
                AND a.publish_st LIKE ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get data pokok last kode
    function get_penjawab_last_kode($prefix, $params) {
        $sql = "SELECT RIGHT(penjawab_id, 6)'last_number'
                FROM mst_penjawab
                WHERE penjawab_id LIKE ?
                ORDER BY penjawab_id DESC
                LIMIT 1";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            // create next number
            $number = intval($result['last_number']) + 1;
            if ($number > 999999) {
                return false;
            }
            $zero = '';
            for ($i = strlen($number); $i < 6; $i++) {
                $zero .= '0';
            }
            return $prefix . $zero . $number;
        } else {
            // create new number
            return $prefix .  '000001';
        }
    }

    // get detail profil by id
    function get_data_by_id($penjawab_id) {
        $sql = "SELECT * 
                FROM mst_penjawab 
                WHERE penjawab_id = ?";
        $query = $this->db->query($sql, $penjawab_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert mst_penjawab
    function insert($params) {
        return $this->db->insert('mst_penjawab', $params);
    }

    // update mst_penjawab
    function update($params, $where) {
        return $this->db->update('mst_penjawab', $params, $where);
    }

    // delete mst_penjawab
    function delete($params) {
        return $this->db->delete('mst_penjawab', $params);
    }

    function get_penjawab_aspirasi_params($params) {
        $sql = "SELECT b.* 
                FROM `trx_aspirasi_process` a
                LEFT JOIN `mst_penjawab` b ON a.`penjawab_id` = b.`penjawab_id`
                WHERE 
                a.`aspirasi_id` = ?
                AND a.`flow_id` = ?
                AND a.`action_st` = ?
                AND a.`process_st` = ?
                GROUP BY b.`penjawab_id`
                ORDER BY b.`penjawab_nm` ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_penjawab_raperda_params($params) {
        $sql = "SELECT b.* 
                FROM `trx_raperda_aspirasi_process` a
                LEFT JOIN `mst_penjawab` b ON a.`penjawab_id` = b.`penjawab_id`
                WHERE 
                a.`aspirasi_id` = ?
                AND a.`flow_id` = ?
                AND a.`action_st` = ?
                AND a.`process_st` = ?
                GROUP BY b.`penjawab_id`
                ORDER BY b.`penjawab_nm` ASC";
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