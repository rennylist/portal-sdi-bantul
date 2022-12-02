<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_user extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // data com user
    function get_data_user_by_id($params) {
        $sql = "SELECT * FROM com_user a 
                LEFT JOIN pegawai b ON a.user_id = b.user_id
                WHERE a.user_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert data_pokok
    function insert_data_pokok($params) {
        return $this->db->insert('data_pokok', $params);
    }

    // update data_pokok
    function update_data_pokok($params, $where) {
        return $this->db->update('data_pokok', $params, $where);
    }

    // delete data_pokok
    function delete_data_pokok($params) {
        return $this->db->delete('data_pokok', $params);
    }

    function update($params, $where) {
        return $this->db->update('pegawai', $params, $where);
    }
}