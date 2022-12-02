<?php

class M_settings extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get last inserted id
    function get_last_inserted_id() {
        return $this->db->insert_id();
    }

    // ------------------
    // <editor-fold defaultstate="collapsed" desc="PORTAL MANAGEMENT">
    // get last id
    function get_portal_last_id() {
        $sql = "SELECT LEFT(portal_id, 1)'last_number'
                FROM com_portal 
                ORDER BY portal_id DESC 
                LIMIT 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            // create next number
            $number = intval($result['last_number']) + 1;
            if ($number >= 10) {
                return false;
            }
            $zero = '';
            for ($i = strlen($number); $i < 2; $i++) {
                $zero .= '0';
            }
            return $number . $zero;
        } else {
            // create new number
            return '10';
        }
    }

    // get total data
    function get_total_data_portal() {
        $sql = "SELECT COUNT(*)'total' FROM com_portal";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get all portal
    function get_all_portal() {
        $sql = "SELECT * FROM com_portal ORDER BY portal_id ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
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

    // insert data portal
    function insert_portal($params) {
        return $this->db->insert('com_portal', $params);
    }

    // update data portal
    function update_portal($params, $where) {
        return $this->db->update('com_portal', $params, $where);
    }

    // delete data portal
    function delete_portal($params) {
        return $this->db->delete('com_portal', $params);
    }

    // </editor-fold>
    // ------------------ 
    // ------------------
    // <editor-fold defaultstate="collapsed" desc="GROUPS MANAGEMENT">
    // get last id
    function get_group_last_id() {
        $sql = "SELECT group_id'last_number' FROM com_group ORDER BY group_id DESC LIMIT 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            // create next number
            $number = intval($result['last_number']) + 1;
            if ($number >= 99) {
                return false;
            }
            $zero = '';
            for ($i = strlen($number); $i < 2; $i++) {
                $zero .= '0';
            }
            return $zero . $number;
        } else {
            // create new number
            return '01';
        }
    }

    // get total group
    function get_total_data_group() {
        $sql = "SELECT COUNT(*)'total' FROM com_group";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get all group
    function get_all_group() {
        $sql = "SELECT * FROM com_group ORDER BY group_id ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail group by id
    function get_group_by_id($group_id) {
        $sql = "SELECT * FROM com_group WHERE group_id = ?";
        $query = $this->db->query($sql, $group_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert data group
    function insert_group($params) {
        return $this->db->insert('com_group', $params);
    }

    // update data group
    function update_group($params, $where) {
        return $this->db->update('com_group', $params, $where);
    }

    // delete data group
    function delete_group($params) {
        return $this->db->delete('com_group', $params);
    }

    // </editor-fold>
    // ------------------
    // ------------------
    // <editor-fold defaultstate="collapsed" desc="ROLE MANAGEMENT">
    // get last id
    function get_role_last_id($group_id) {
        $sql = "SELECT RIGHT(role_id, 3)'last_number'
                FROM com_role 
                WHERE group_id = ?
                ORDER BY role_id DESC 
                LIMIT 1";
        $query = $this->db->query($sql, $group_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            // create next number
            $number = intval($result['last_number']) + 1;
            if ($number > 999) {
                return false;
            }
            $zero = '';
            for ($i = strlen($number); $i < 3; $i++) {
                $zero .= '0';
            }
            return $group_id . $zero . $number;
        } else {
            // create new number
            return $group_id . '001';
        }
    }

    // get all roles
    function get_all_roles($params) {
        $sql = "SELECT b.group_name, a.* 
                FROM com_role a
                INNER JOIN com_group b ON a.group_id = b.group_id
                WHERE a.role_nm LIKE ? AND b.group_id LIKE ?
                ORDER BY a.role_id ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail role by id
    function get_detail_role_by_id($id_role) {
        $sql = "SELECT b.group_name, a.* 
                FROM com_role a
                INNER JOIN com_group b ON a.group_id = b.group_id
                WHERE role_id = ?";
        $query = $this->db->query($sql, $id_role);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert role
    function insert_role($params) {
        return $this->db->insert('com_role', $params);
    }

    // update role
    function update_role($params, $where) {
        return $this->db->update('com_role', $params, $where);
    }

    // delete role
    function delete_role($params) {
        return $this->db->delete('com_role', $params);
    }

    // insert role menu
    function insert_role_menu($params) {
        $sql = "INSERT INTO com_role_menu (role_id, nav_id, role_tp) VALUES (?, ?, ?)";
        return $this->db->query($sql, $params);
    }

    // delete role menu
    function delete_role_menu($params) {
        $sql = "DELETE a.* FROM com_role_menu a
                INNER JOIN com_menu b ON a.nav_id = b.nav_id
                WHERE role_id = ? AND b.portal_id = ?";
        return $this->db->query($sql, $params);
    }

    // </editor-fold>
    // ------------------
    // ------------------
    // <editor-fold defaultstate="collapsed" desc="MENU MANAGEMENT">
    // // get last id
    function get_nav_last_id($portal_id) {
        $sql = "SELECT RIGHT(nav_id, 8) AS 'last_number'
                FROM com_menu 
                WHERE portal_id = ?
                ORDER BY nav_id DESC 
                LIMIT 1";
        $query = $this->db->query($sql, $portal_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            // create next number
            $number = intval($result['last_number']) + 1;
            if ($number > 99999999) {
                return false;
            }
            $zero = '';
            for ($i = strlen($number); $i < 8; $i++) {
                $zero .= '0';
            }
            return $portal_id . $zero . $number;
        } else {
            // create new number
            return $portal_id . '00000001';
        }
    }

    // get all portal with menu
    function get_all_portal_menu() {
        $sql = "SELECT a.*, COUNT(b.nav_id)'total_menu' 
                FROM com_portal a
                LEFT JOIN com_menu b ON a.portal_id = b.portal_id
                GROUP BY a.portal_id
                ORDER BY a.portal_id ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all menu by parent
    function get_all_menu_by_parent($params) {
        $sql = "SELECT * FROM com_menu
                WHERE portal_id = ? AND parent_id = ? 
                ORDER BY nav_no ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all menu by parent
    function get_all_menu_selected_by_parent($params) {
        $sql = "SELECT a.*, b.role_id, b.role_tp
                FROM com_menu a
                LEFT JOIN (SELECT * FROM com_role_menu WHERE role_id = ?) b ON a.nav_id = b.nav_id
                WHERE portal_id = ? AND parent_id = ?
                ORDER BY nav_no ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    //get_list_portal by role_id
    function get_list_portal_by_role_id($params) {
        $sql = "SELECT DISTINCT c.* FROM com_menu a 
                LEFT JOIN (SELECT * FROM com_role_menu WHERE role_id = ?) b ON a.nav_id = b.nav_id 
                INNER JOIN com_portal c ON a.portal_id = c.portal_id
                ORDER BY nav_no ASC";
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

    // insert menu
    function insert_menu($params) {
        return $this->db->insert('com_menu', $params);
    }

    // update menu
    function update_menu($params, $where) {
        return $this->db->update('com_menu', $params, $where);
    }

    // delete menu
    function delete_menu($params) {
        return $this->db->delete('com_menu', $params);
    }

    // update parent
    function update_parent($params) {
        $sql = "UPDATE com_menu SET parent_id = ? WHERE parent_id = ?";
        return $this->db->query($sql, $params);
    }

    // </editor-fold>
    // ------------------
    // <editor-fold defaultstate="collapsed" desc="PREFERENCES MANAGEMENT">
    // // get last id
    // get all preferences
    function get_all_preferences() {
        $sql = "SELECT * FROM com_preferences
                WHERE pref_id > 10 
                ORDER BY pref_group ASC, pref_nm ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all preferences by range
    function get_all_preferences_by_range($params) {
        $sql = "SELECT * FROM com_preferences
                WHERE pref_id >= ? AND  pref_id <= ?
                ORDER BY pref_group ASC, pref_nm ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all preferences
    function get_all_preferences_by_params($params) {
        $sql = "SELECT * FROM com_preferences
                WHERE pref_nm LIKE ? AND pref_group LIKE ?
                ORDER BY pref_nm ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all preferences by range
    function get_all_preferences_group() {
        $sql = "SELECT * FROM com_preferences
                GROUP BY pref_group
                ORDER BY pref_group ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all preferences by range
    function get_all_preferences_by_group($params) {
        $sql = "SELECT * FROM com_preferences
                WHERE pref_group = ?
                ORDER BY pref_nm ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all preferences by group name
    function get_value_preferences_by_group_name($params) {
        $sql = "SELECT * FROM com_preferences
                WHERE pref_group = ? AND pref_nm = ?
                ORDER BY pref_nm ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['pref_value'];
        } else {
            return '';
        }
    }

    //get detail preferences
    function get_preference_by_id($pref_id) {
        $sql = "SELECT * FROM com_preferences WHERE pref_id = ?";
        $query = $this->db->query($sql, $pref_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail preferences
    function get_preference_by_group_id($params) {
        $sql = "SELECT * FROM com_preferences WHERE pref_group = ? AND pref_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert preferences
    function insert_preferences($params) {
        return $this->db->insert('com_preferences', $params);
    }

    // update preferences
    function update_preferences($params, $where) {
        return $this->db->update('com_preferences', $params, $where);
    }

    // delete preferences
    function delete_preferences($where) {
        return $this->db->delete('com_preferences', $where);
    }

    // </editor-fold>
}
