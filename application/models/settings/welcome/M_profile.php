<?php

class M_profile extends CI_Model {

    // constructor
    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // get_detail_pegawai_by_id
    function get_detail_pegawai_by_id($params) {
        $sql = "SELECT a.*, b.user_mail
                FROM pegawai a 
                INNER JOIN com_user b ON a.user_id = b.user_id
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
