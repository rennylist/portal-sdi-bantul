<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_navigasi extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get last inserted id
    function get_last_inserted_id() {
        return $this->db->insert_id();
    }

    // get detail data by id
    function get_portal_by_id($portal_id) {
        $sql = "SELECT * FROM com_portal WHERE portal_id = ?";
        $query = $this->db->query($sql, $portal_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all menu by parent
    function get_all_menu_by_parent($params) {
        $sql = "SELECT * FROM com_menu
                WHERE portal_id = ? AND parent_id = ? ORDER BY nav_no ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail menu by id
    function get_detail_menu_by_id($id_role) {
        $sql = "SELECT * FROM com_menu WHERE nav_id = ?";
        $query = $this->db->query($sql, $id_role);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    //insert menu
    function insert_menu($params) {
        $sql = "INSERT INTO com_menu (portal_id, parent_id, nav_title, nav_desc, nav_url, nav_no, active_st, display_st, nav_icon, nav_st, mdb, mdd)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , NOW())";
        return $this->db->query($sql, $params);
    }

    // update icon
    function update_icon($params) {
        $sql = "UPDATE com_menu SET nav_icon = ? WHERE nav_id = ?";
        return $this->db->query($sql, $params);
    }

    // delete menu
    function delete_menu($params) {
        $sql = "DELETE FROM com_menu WHERE nav_id = ?";
        return $this->db->query($sql, $params);
    }

    // update menu
    function update_menu($params) {
        $sql = "UPDATE com_menu
                SET portal_id = ?, parent_id = ?, nav_title = ?, nav_desc = ?, nav_url = ?, nav_no = ?, active_st = ?, display_st = ?, nav_icon = ?, nav_st = ?, mdb = ?
                WHERE nav_id = ?";
        return $this->db->query($sql, $params);
    }
}