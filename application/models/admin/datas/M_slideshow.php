<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_slideshow extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // list data pokok
    function get_list_data_slideshow($params) {
        $sql = "SELECT * FROM trx_slideshow 
                WHERE 
                slideshow_title LIKE ?
                ORDER BY slideshow_order ASC";
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
    function get_data_slideshow_last_kode($prefix, $params) {
        $sql = "SELECT RIGHT(slideshow_id, 6)'last_number'
                FROM trx_slideshow
                WHERE slideshow_id LIKE ?
                ORDER BY slideshow_id DESC
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
    function get_data_slideshow_by_id($slideshow_id) {
        $sql = "SELECT * FROM trx_slideshow WHERE slideshow_id = ?";
        $query = $this->db->query($sql, $slideshow_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert trx_slideshow
    function insert_data_slideshow($params) {
        return $this->db->insert('trx_slideshow', $params);
    }

    // update trx_slideshow
    function update_data_slideshow($params, $where) {
        return $this->db->update('trx_slideshow', $params, $where);
    }

    // delete trx_slideshow
    function delete_data_slideshow($params) {
        return $this->db->delete('trx_slideshow', $params);
    }
}