<?php

class M_statistik_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->db2 = $this->load->database('pg', TRUE);
    }

    // get all menu by parent
    function get_all_menu_by_parent()
    {
        $sql = "SELECT urusan_id, parent_id, urusan_name FROM trx_urusan
                WHERE parent_id = '0'
                ";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_list_urusan_id()
    {
        $sql = "SELECT urusan_id, urusan_name FROM trx_urusan
                WHERE parent_id <> '0'
                ";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_list_parent_indicator()
    {
        $sql = "SELECT data_id, parent_id, data_name FROM trx_indicator
                WHERE data_id = parent_id";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_intsansi()
    {
        $sql = "SELECT * FROM mst_instansi";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_parent_indicator($params)
    {

        $sql = "SELECT * FROM trx_indicator
        WHERE active_st = 'yes'
        AND urusan_id = ? 
        AND parent_id = data_id                                     
        ORDER BY data_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function insert_urusan($params)
    {
        return $this->db2->insert('trx_urusan', $params);
    }
    public function insert_indikator($params)
    {
        return $this->db2->insert('trx_indicator', $params);
    }
}
