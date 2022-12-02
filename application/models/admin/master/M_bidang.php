<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_bidang extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // list data pokok
    function get_list_data_bidang($params) {
        $sql = "SELECT * 
                FROM mst_bidang 
                WHERE 
                bidang_nm LIKE ?";
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
    function get_data_bidang_last_kode($prefix, $params) {
        $sql = "SELECT 
                RIGHT(bidang_id, 6)'last_number'
                FROM mst_bidang
                WHERE 
                bidang_id LIKE ?
                ORDER BY bidang_id DESC
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
    function get_data_bidang_by_id($bidang_id) {
        $sql = "SELECT * FROM mst_bidang WHERE bidang_id = ?";
        $query = $this->db->query($sql, $bidang_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert mst_bidang
    function insert_data_bidang($params) {
        return $this->db->insert('mst_bidang', $params);
    }

    // update mst_bidang
    function update_data_bidang($params, $where) {
        return $this->db->update('mst_bidang', $params, $where);
    }

    // delete mst_bidang
    function delete_data_bidang($params) {
        return $this->db->delete('mst_bidang', $params);
    }
}