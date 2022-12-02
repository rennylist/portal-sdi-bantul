<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_komisi extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // list data pokok
    function get_list_data_komisi($params) {
        $sql = "SELECT * 
                FROM mst_komisi 
                WHERE 
                komisi_nm LIKE ?";
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
    function get_data_komisi_last_kode($prefix, $params) {
        $sql = "SELECT 
                RIGHT(komisi_id, 6)'last_number'
                FROM mst_komisi
                WHERE 
                komisi_id LIKE ?
                ORDER BY komisi_id DESC
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
    function get_data_komisi_by_id($komisi_id) {
        $sql = "SELECT * FROM mst_komisi WHERE komisi_id = ?";
        $query = $this->db->query($sql, $komisi_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert mst_komisi
    function insert_data_komisi($params) {
        return $this->db->insert('mst_komisi', $params);
    }

    // update mst_komisi
    function update_data_komisi($params, $where) {
        return $this->db->update('mst_komisi', $params, $where);
    }

    // delete mst_komisi
    function delete_data_komisi($params) {
        return $this->db->delete('mst_komisi', $params);
    }
}