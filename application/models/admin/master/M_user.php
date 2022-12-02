<?php

// class for core system
class M_user extends CI_Model {

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get last id
    public function get_user_last_id($prefixdate, $params) {
        $sql = "SELECT RIGHT(user_id, 4) AS 'last_number'
                FROM com_user
                WHERE user_id LIKE ?
                ORDER BY user_id DESC
                LIMIT 1";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            // create next number
            $number = intval($result['last_number']) + 1;
            if ($number > 9999) {
                return false;
            }
            $zero = '';
            for ($i = strlen($number); $i < 4; $i++) {
                $zero .= '0';
            }
            return $prefixdate . $zero . $number;
        } else {
            // create new number
            return $prefixdate . '0001';
        }
    }

    // total users by group
    public function get_total_users($params) {
        $sql = "SELECT COUNT(*) AS 'total' FROM
                (
                    SELECT a.*, d.`user_id` AS 'super'
                    FROM com_user a 
                    LEFT JOIN `com_user_super` d ON a.user_id = d.`user_id`
                    WHERE 
                    a.user_st LIKE ? 
                    AND ( a.user_alias LIKE ? OR a.user_mail LIKE ?) 
                )xx
                WHERE xx.super IS NULL";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // total users by group
    public function get_all_satker() {
        $sql = "SELECT * FROM spip_satuan_kerja ORDER BY satker_nama ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get list users by group
    public function get_list_users_by_group($params) {
        // $sql = "SELECT a.*, c.role_id, c.role_nm
        //         FROM com_user a
        //         LEFT JOIN com_role_user b ON a.user_id = b.user_id
        //         LEFT JOIN com_role c ON b.role_id = c.role_id
        //         WHERE a.user_st LIKE ? 
        //         AND ( a.user_alias LIKE ? OR a.user_mail LIKE ?)
        //         GROUP BY a.user_id
        //         ORDER BY a.user_alias ASC
        //         LIMIT ?, ?";
        $sql = "SELECT * FROM(
                    SELECT 
                    a.*, c.role_id, c.role_nm, d.`user_id` AS 'super', e.`instansi_name`
                    FROM com_user a
                    LEFT JOIN com_role_user b ON a.user_id = b.user_id
                    LEFT JOIN com_role c ON b.role_id = c.role_id
                    LEFT JOIN `com_user_super` d ON a.user_id = d.`user_id`
                    LEFT JOIN `mst_instansi` e ON a.`instansi_cd` = e.`instansi_cd`
                    WHERE a.user_st LIKE ? 
                    AND ( a.user_alias LIKE ? OR a.user_mail LIKE ?)
                    ORDER BY a.user_alias ASC
                )xx
                WHERE xx.super IS NULL
                LIMIT ?, ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get detail user by id
    public function get_detail_user_by_id($params) {
        $sql = "SELECT *
                FROM com_user a
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

    // get detail user by email
    public function get_detail_user_by_email($params) {
        $sql = "SELECT *
                FROM com_user a
                WHERE a.user_mail = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get roles by user
    public function get_roles_by_user($params) {
        $sql = "SELECT a.*, b.group_id, b.role_nm
                FROM com_role_user a
                INNER JOIN com_role b ON b.role_id = a.role_id
                INNER JOIN com_group c ON c.group_id = b.group_id
                WHERE a.user_id = ?
                ORDER BY c.group_name ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get group id by user
    public function get_group_id_by_user($params) {
        $sql = "SELECT DISTINCT(b.group_id)
                FROM com_role_user a
                INNER JOIN com_role b ON b.role_id = a.role_id
                INNER JOIN com_group c ON c.group_id = b.group_id
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

    // get list roles by group
    public function get_list_roles_by_group($params) {
        $sql = "SELECT a.*,b.*
                FROM com_group a
                JOIN com_role b ON a.group_id=b.group_id
                WHERE a.group_id = ?
                ORDER BY a.group_name ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get groups by user
    public function get_groups_by_user($params) {
        $sql = "SELECT a.*,b.*
                FROM com_group a
                JOIN com_role b ON a.group_id=b.group_id
                WHERE a.group_id = ?
                ORDER BY a.group_name ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get all roles
    public function get_roles_by_portal($params) {
        $sql = "SELECT b.group_name, a.*
                FROM com_role a
                INNER JOIN com_group b ON a.group_id = b.group_id
                INNER JOIN com_role_menu c ON a.role_id = c.role_id
                INNER JOIN com_menu d ON c.nav_id = d.nav_id
                WHERE 
                d.portal_id = ?
                AND a.group_id != 01
                GROUP BY role_id
                ORDER BY b.group_name ASC, a.role_nm ASC";
        // $sql = "SELECT b.group_name, a.*
        //         FROM com_role a
        //         INNER JOIN com_group b ON a.group_id = b.group_id
        //         INNER JOIN com_role_menu c ON a.role_id = c.role_id
        //         INNER JOIN com_menu d ON c.nav_id = d.nav_id
        //         WHERE d.portal_id = ?
        //         GROUP BY role_id
        //         ORDER BY b.group_name ASC, a.role_nm ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // check username
    public function is_exist_username($username) {
        $sql = "SELECT COUNT(*)'total' FROM com_user WHERE user_name = ?";
        $query = $this->db->query($sql, $username);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if ($result['total'] == 0) {
                return false;
            }
        }
        return true;
    }

    // check email
    public function is_exist_email($email) {
        $sql = "SELECT COUNT(*)'total' FROM com_user WHERE user_mail = ?";
        $query = $this->db->query($sql, $email);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if ($result['total'] == 0) {
                return false;
            }
        }
        return true;
    }
    public function insert_pegawai($params) {
        return $this->db->insert('pegawai', $params);
    }
    // insert user
    public function insert_user($params) {
        return $this->db->insert('com_user', $params);
    }

    // insert role user
    public function insert_role_user($params) {
        return $this->db->insert('com_role_user', $params);
    }

    // edit user
    public function update_user($params, $where) {
        return $this->db->update('com_user', $params, $where);
    }

    // delete user
    public function delete_user($where) {
        return $this->db->delete('com_user', $where);
    }

    // delete role user
    public function delete_role_user($where) {
        return $this->db->delete('com_role_user', $where);
    }


}
