<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_account extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        // load encryption
        // $this->load->library('encryption');
        $this->load->library('encryption');
    }

    // rand password
    public function rand_password()
    {
        $pool = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $str = '';
        for ($i = 0; $i < 6; $i++) {
            $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
        }
        return $str;
    }

    /*
     * LOGIN BY ROLES
     */

    // get user detail by role
    function get_user_detail_by_role($params)
    {
        $sql = "SELECT a.*, c.role_id, c.role_nm, c.default_page
                FROM com_user a
                LEFT JOIN com_role_user b ON a.user_id = b.user_id
                LEFT JOIN com_role c ON b.role_id = c.role_id
                WHERE user_name = ? AND c.portal_id = ? AND c.role_id = ?
                LIMIT 0, 1";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get login
    function get_user_login_by_role($username, $password, $role_id, $portal)
    {
        // get hash key
        $result = $this->get_user_detail_by_role(array($username, $portal, $role_id));
        if (!empty($result)) {
            $password_decode = $this->encryption->decrypt($result['user_pass'], $result['user_key']);
            // get user
            if ($password_decode === md5($password)) {
                // cek authority then return id
                return $result;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /*
     * LOGIN WITH ALL ROLES
     */

    // get user detail with auto role
    function get_user_detail_with_all_portal($params)
    {
        $sql = "SELECT a.*, c.default_page, b.role_default, b.role_id
                FROM com_user a
                INNER JOIN com_role_user b ON a.user_id = b.user_id
                INNER JOIN com_role c ON b.role_id = c.role_id
                INNER JOIN com_role_menu d ON c.role_id = d.role_id
                INNER JOIN com_menu e ON d.nav_id = e.nav_id
                WHERE user_name = ?
                ORDER BY b.role_default ASC
                LIMIT 0, 1";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get user detail with auto role
    function get_user_detail_with_all_roles($params)
    {
        $sql = "SELECT a.*, c.default_page, b.role_default, b.role_id
                FROM com_user a
                INNER JOIN com_role_user b ON a.user_id = b.user_id
                INNER JOIN com_role c ON b.role_id = c.role_id
                INNER JOIN com_role_menu d ON c.role_id = d.role_id
                INNER JOIN com_menu e ON d.nav_id = e.nav_id
                WHERE user_name = ? AND e.portal_id = ?
                ORDER BY b.role_default ASC
                LIMIT 0, 1";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get login auto role
    function get_user_login_by_user_pass($username, $password)
    {
        // load encryption
        $this->load->library('encryption');
        // process
        // get hash key
        $result = $this->get_user_detail_with_all_portal(array($username));
        if (!empty($result)) {
            $password_decode = $this->encryption->decrypt($result['user_pass'], $result['user_key']);
            // get user
            if ($password_decode === md5($password)) {
                // cek authority then return id
                return $result;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    // get login auto role
    function get_user_login_all_roles($username, $password, $portal)
    {
        // process
        // get hash key
        $result = $this->get_user_detail_with_all_roles(array($username, $portal));
        // print_r("<pre>");
        // print_r($result);
        // print_r("</pre>");

        if (!empty($result)) {
            //init
            // $key = $result['user_key'];
            // echo  md5($password);
            // echo "<br />";
            // echo  $result['user_key'];
            // echo "<br />";
            // echo  $result['user_pass'];
            // echo "<br />";
            //die;
            // $key = bin2hex($this->encryption->create_key(16));
            // $this->encryption->initialize(array('cipher' => 'aes-256', 'mode' => 'ctr', 'key' => $key));
            $password_decode = $result['user_pass'];
            // exit();
            // get user
            if ($password_decode === md5($password)) {
                // print_r("TRUE");
                // cek authority then return id
                return $result;
            } else {
                print_r("FALSE;");
                // return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    // save user login
    function save_user_login($user_id, $remote_address)
    {
        // get today login
        $sql = "SELECT * FROM com_user_login WHERE user_id = ? AND DATE(login_date) = CURRENT_DATE";
        $query = $this->db->query($sql, array($user_id));
        if ($query->num_rows() > 0) {
            // tidak perlu diinputkan lagi
            return FALSE;
        } else {
            $sql = "INSERT INTO com_user_login (user_id, login_date, ip_address) VALUES (?, NOW(), ?)";
            return $this->db->query($sql, array($user_id, $remote_address));
        }
    }

    // get list roles
    function get_list_user_roles($params)
    {
        $sql = "SELECT e.portal_nm, e.portal_id, b.*, role_display
                FROM com_role_user a 
                INNER JOIN com_role b ON a.role_id = b.role_id
                INNER JOIN com_role_menu c ON b.role_id = c.role_id
                INNER JOIN com_menu d ON c.nav_id = d.nav_id
                INNER JOIN com_portal e ON d.portal_id = e.portal_id
                WHERE a.user_id = ?
                GROUP BY b.role_id
                ORDER BY b.role_id ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get login history
    function get_login_history($params)
    {
        $sql = "SELECT a.*, b.nama_lengkap
                FROM com_user_login a 
                INNER JOIN com_user b ON a.user_id = b.user_id
                WHERE a.user_id = ? AND a.login_date LIKE ?
                ORDER BY a.login_date DESC
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

    // save user logout
    function update_user_logout($user_id)
    {
        // update by this date
        $sql = "UPDATE com_user_login SET logout_date = NOW() WHERE user_id = ? AND DATE(login_date) = CURRENT_DATE";
        return $this->db->query($sql, $user_id);
    }

    /*
     * RESET PASSWORD
     */

    // get username by email
    function get_username_by_email($params)
    {
        $sql = "SELECT * FROM com_user WHERE user_mail = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return FALSE;
        }
    }

    // get pegawai by email
    function get_pegawai_by_email($params)
    {
        $sql = "SELECT b.user_id, b.nama_lengkap, b.nomor_telepon, a.user_mail
                FROM com_user a
                INNER JOIN pegawai b ON a.user_id = b.user_id
                WHERE a.user_mail = ? AND a.user_st = '1'";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return FALSE;
        }
    }

    /*
     * CHECK ACCOUNT
     */

    // check username
    function is_exist_username($params)
    {
        $sql = "SELECT * FROM com_user WHERE user_name = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $query->free_result();
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // check mail
    function is_exist_email($params)
    {
        $sql = "SELECT * FROM com_user WHERE user_mail = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $query->free_result();
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // check password
    function is_exist_password($user_id, $password)
    {
        $sql = "SELECT * FROM com_user WHERE user_id = ?";
        $query = $this->db->query($sql, $user_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
        } else {
            return FALSE;
        }
        // --
        $password_decode = $this->encryption->decrypt($result['user_pass'], $result['user_key']);
        if ($password_decode == $password) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // get user account
    function get_user_account_by_id($params)
    {
        $sql = "SELECT a.*
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

    // update data account
    function update_data_account($params, $where)
    {
        $sql = "SELECT * FROM com_user WHERE user_id = ?";
        $query = $this->db->query($sql, $where);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
        } else {
            return FALSE;
        }
        // encode password
        $params[1] = $this->encryption->encode($params[1], $result['user_key']);
        // update
        $sql = "UPDATE com_user SET user_name = ?, user_pass = ? WHERE user_id = ?";
        return $this->db->query($sql, $params);
    }

    // roles
    function get_all_roles_by_portal($portal_id)
    {
        $sql = "SELECT * FROM com_role WHERE portal_id = ?";
        $query = $this->db->query($sql, $portal_id);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // detail roles
    function get_detail_roles_by_id($params)
    {
        $sql = "SELECT * FROM com_role WHERE role_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // update permissions
    function update_permissions($params)
    {
        // delete by user & portal
        $sql = "DELETE a.* FROM com_role_user a
                INNER JOIN com_role b ON a.role_id = b.role_id
                WHERE a.user_id = ? AND b.portal_id = 2";
        $this->db->query($sql, $params);
        // insert
        $sql = "INSERT INTO com_role_user (user_id, role_id) VALUES (?, ?)";
        return $this->db->query($sql, $params);
    }

    // roles
    function get_all_roles_by_users($params)
    {
        $sql = "SELECT * FROM com_role a
                INNER JOIN com_role_user b ON a.role_id = b.role_id
                WHERE portal_id = ? AND b.user_id = ?
                ORDER BY a.role_nm ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // check mail
    function get_detail_user_by_email($params)
    {
        $sql = "SELECT * FROM com_user WHERE user_mail = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // check user reset password
    function is_reset_password($params)
    {
        $sql = "SELECT * FROM com_user a
                INNER JOIN com_user_reset b ON a.user_id = b.user_id
                WHERE b.user_id = ? AND b.reset_st = '0'";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // save user request
    function save_request_password($params)
    {
        // execute
        return $this->db->insert('request_password', $params);
    }

    // save user reset
    function save_user_reset($params)
    {
        // execute
        return $this->db->insert('com_user_reset', $params);
    }

    // get data id
    function get_token()
    {
        $time = microtime(true);
        $token = str_replace('.', '', $time);
        return md5(md5($token));
    }

    // update data account
    function is_exist_token($params)
    {
        $sql = "SELECT * FROM com_user a
                INNER JOIN com_user_reset b ON a.user_id = b.user_id
                WHERE b.token = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return FALSE;
        }
        return $result;
    }

    // save user logout
    function update_user_password($params)
    {
        // update by this date
        $sql = "UPDATE com_user SET user_pass = ?, user_key = ? WHERE user_id = ?";
        if ($this->db->query($sql, $params)) {
            return true;
        } else {
            return FALSE;
        }
    }

    // reset password
    function update_user_reset($params)
    {
        // update by this date
        $sql = "UPDATE com_user_reset SET reset_st = '1', reset_date = NOW() WHERE user_id = ? AND token = ?";
        return $this->db->query($sql, $params);
    }

    /*
     * ACCOUNT SETTINGS
     */

    // get detail users
    function get_detail_user_by_id($params)
    {
        $sql = "SELECT *
                FROM com_user
                WHERE user_id = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // update
    function update_user($params, $where)
    {
        // update
        return $this->db->update('com_user', $params, $where);
    }

    // insert
    public function insert_reset($params)
    {
        return $this->db->insert('com_reset_pass', $params);
    }

    // update reset
    public function update_reset($params, $where)
    {
        return $this->db->update('com_reset_pass', $params, $where);
    }

    // get data by id
    public function get_reset_data_by_id($params)
    {
        $sql = "SELECT *
                FROM com_reset_pass
                WHERE md5(request_key) = ? AND request_st = 'waiting'";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    /*
     * Pegawai
     */

    // get pegawai login
    function get_pegawai_login_by_id($params)
    {
        $sql = "SELECT a.user_id, user_name as 'user_alias', user_pass, user_key, user_mail, user_img_name, user_img_path,
                pegawai_nip, pegawai_status, nama_lengkap, jenis_kelamin
                FROM com_user a
                INNER JOIN pegawai b ON a.user_id = b.user_id
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

    function get_pegawai_login_by_id_new($params)
    {
        $sql = "SELECT 
                a.user_id, a.user_group, user_name as 'user_alias', 
                a.instansi_cd,
                user_pass, user_key, user_mail, user_img_name, user_img_path,
                pegawai_nip, pegawai_status, nama_lengkap, jenis_kelamin
                FROM 
                com_user a
                INNER JOIN pegawai b ON a.user_id = b.user_id
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
}
