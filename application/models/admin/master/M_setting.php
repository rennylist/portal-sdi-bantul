<?php

// class for core system
class M_setting extends CI_Model {

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    public function get_settting()
    {
        $sql = "SELECT * 
                FROM `trx_footer` 
                WHERE id = 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function update_setting($params, $where){
        return $this->db->update('trx_footer', $params, $where);
    }
}
