<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// --
class M_preferences extends CI_Model {

    function  __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get total data
    function get_total_data () {
        $sql = "SELECT COUNT(*)'total' FROM com_preferences";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        }else {
            return 0;
        }
    }

    // get all preferences
    function get_all_preferences () {
        $sql = "SELECT * FROM com_preferences ORDER BY pref_group ASC, pref_nm ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail data by id
    function get_preferences_by_id ($pref_id) {
        $sql = "SELECT * FROM com_preferences WHERE pref_id = ?";
        $query = $this->db->query($sql, $pref_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail data by group
    function get_preferences_by_group ($pref_group) {
        $sql = "SELECT * FROM com_preferences WHERE pref_group = ? ORDER BY pref_nm ASC";
        $query = $this->db->query($sql, $pref_group);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // get detail data by group and name
    function get_preferences_by_group_name ($params) {
        $sql = "SELECT * FROM com_preferences WHERE pref_group = ? AND pref_nm = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        }else {
            return array();
        }
    }

    // insert
    function insert ($params) {
        $sql = "INSERT INTO com_preferences (pref_group, pref_nm, pref_value, mdb, mdd)
                VALUES (?, ?, ?, ?, NOW())";
        return $this->db->query($sql, $params);
    }

    // update
    function update ($params) {
        $sql = "UPDATE com_preferences SET pref_group = ?, pref_nm = ?, pref_value = ?, mdb = ?, mdd = NOW()
                WHERE pref_id = ?";
        return $this->db->query($sql, $params);
    }

    // update by group name
    function update_by_group_nm ($params) {
        $sql = "UPDATE com_preferences SET pref_value = ? WHERE pref_group = ? AND pref_nm = ?";
        return $this->db->query($sql, $params);
    }

    // delete
    function delete ($params) {
        $sql = "DELETE FROM com_preferences WHERE pref_id = ?";
        return $this->db->query($sql, $params);
    }

    // delete by group name
    function delete_by_group_nm ($params) {
        $sql = "DELETE FROM com_preferences WHERE pref_group = ? AND pref_nm = ?";
        return $this->db->query($sql, $params);
    }

    // update_preferences_memo_last_update
    function update_preferences_memo_last_update ($params) {
        $sql = "UPDATE com_preferences SET pref_value = NOW(), mdb = ?, mdd = NOW()
                WHERE pref_group = ? AND pref_nm = ?";
        return $this->db->query($sql, $params);
    }
    
    function get_mail() {
        $sql = "SELECT * FROM com_preferences WHERE pref_group = 'EMAIL_CONF'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }
}