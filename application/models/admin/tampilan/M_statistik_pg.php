<?php

class M_statistik_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->db2 = $this->load->database('pg', TRUE);
    }

    function get_list_urusan()
    {
        $sql = "SELECT urusan_id, parent_id, urusan_name FROM trx_urusan
                ORDER BY urusan_id ASC";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_list_urusan_by_parent()
    {
        $sql = "SELECT urusan_id, parent_id, urusan_name FROM trx_urusan
                WHERE parent_id <> '0'
                ORDER BY urusan_id ASC";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_list_indikator()
    {
        $sql = "SELECT data_id, data_name, data_unit, data_type FROM trx_indicator LIMIT 6500";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_list_indikator_by_urusan($params)
    {
        $sql = "SELECT trx_indicator.data_id, trx_indicator.urusan_id, trx_indicator.data_name, trx_indicator.data_type FROM trx_indicator 
        --  LEFT JOIN mst_instansi
        -- ON trx_indicator.instansi_cd = mst_instansi.instansi_cd 
        WHERE trx_indicator.urusan_id = ?";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_data_indicator($params)
    {
        //print_r($params);
        $sql = "SELECT * FROM trx_indicator 
        LEFT JOIN mst_instansi
        ON trx_indicator.instansi_cd = mst_instansi.instansi_cd 
        WHERE trx_indicator.data_id = ?";
        $query = $this->db2->query($sql, $params);
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
        $sql = "SELECT * FROM mst_instansi ORDER BY instansi_name ASC";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    function get_parent_id($params)
    {
        $sql = "SELECT * FROM trx_urusan
                WHERE urusan_id = ?
                OR parent_id = '0'
                ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    function get_urusan_by_id($params)
    {
        $sql = "SELECT * FROM trx_urusan
                WHERE urusan_id = ?
                ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_list_urusan_by_id($params)
    {
        $sql = "SELECT * FROM trx_urusan
                WHERE parent_id = '0' ORDER BY cast (urusan_id AS INT) ASC
                ";
        $query = $this->db2->query($sql, $params);
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
    public function update_urusan($params, $where)
    {
        return $this->db2->update('trx_urusan', $params, $where);
    }
    public function update_indicator($params, $where)
    {
        return $this->db2->update('trx_indicator', $params, $where);
    }
    public function delete_urusan($where)
    {
        return $this->db2->delete('trx_urusan', $where);
    }
}
