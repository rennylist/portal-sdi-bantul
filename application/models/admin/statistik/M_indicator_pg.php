<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_indicator_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->db2 = $this->load->database('pg', TRUE);
    }

    //GET TOTAL INDICATOR STATISTIK -> PAGINATION
    public function get_total_indicator_statistik()
    {
        $sql = "SELECT COUNT(*) AS total FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                WHERE a.active_st = 'yes'";   
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    //GET DATA STATISTIK -> PAGINATION
    public function get_indicator_statistik($params)
    {
        $sql = "SELECT a.*, b.urusan_name, c.instansi_name
                FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                WHERE a.active_st = 'yes'
                ORDER BY a.data_id ASC
                LIMIT ? OFFSET ?";
        $query = $this->db2->query($sql,$params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_statistik_by_opd($params)
    {
        $sql = "SELECT a.*, b.urusan_name, c.instansi_name
                FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                WHERE a.active_st = 'yes'
                AND a.instansi_cd = ?
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql,$params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // GET TOTAL URUSAN
    public function get_total($params)
    {
        $sql = "SELECT COUNT(*) AS 'total' FROM
                (
                    SELECT * 
                    FROM trx_urusan a
                    WHERE
                    a.active_st = 'yes'
                    ORDER BY a.urusan_id ASC
                )xx";
        $query = $this->db2->query($sql, $params);

        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // GET LIST URUSAN
    public function get_list($params)
    {
        $sql = "SELECT * 
                FROM trx_urusan a
                WHERE
                a.active_st = 'yes'
                ORDER BY a.urusan_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_indicator_for_priviliges($params)
    {
        $sql = "SELECT *
                FROM trx_indicator
                WHERE 
                data_id LIKE ?
                AND data_type = 'indicator'
                AND active_st = 'yes'
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

    public function get_indicator_by_instansi($params)
    {

        $sql = "SELECT * 
                FROM trx_indicator a
                WHERE
                a.active_st = 'yes'
                AND a.urusan_id = ?
                AND a.instansi_cd = ?
                AND a.data_type = 'indikator' 
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_instansi_with_class($params, $indicator_class)
    {

        $sql = "SELECT * 
                FROM trx_indicator a
                WHERE
                a.active_st = 'yes'
                AND a.urusan_id = ?
                AND a.instansi_cd = ?
                AND a.data_type = 'indicator' 
                AND $indicator_class = 'yes'
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);

        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_indicator_all()
    {
        $sql = "SELECT a.*, b.urusan_name, c.instansi_name
                FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // test
    public function get_indicator_test_temp()
    {
        $sql = "SELECT a.*, b.urusan_name, c.instansi_name
                FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                WHERE a.instansi_cd = '11705'
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_urusan_instansi($params)
    {
        $sql = "SELECT a.*, b.urusan_name, c.instansi_name
                FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                WHERE a.urusan_id = ?
                AND a.instansi_cd = ?
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_all_by_instansi($params)
    {
        $db2 = $this->load->database('pg', TRUE);
        $sql = "SELECT a.*, b.instansi_name
                FROM trx_indicator a
                LEFT JOIN mst_instansi b ON a.instansi_cd = b.instansi_cd
                WHERE
                a.active_st = 'yes'
                AND a.instansi_cd = ?
                ORDER BY a.data_id ASC";
        //$query = $this->db->query($sql, $params);
        $query =  $db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_prioritas_statistik_by_opd($params)
    {
        $sql = "SELECT a.*, b.urusan_name, c.instansi_name
                FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                WHERE
                a.active_st = 'yes'
                AND a.prioritas_st = 'prioritas'
                AND a.instansi_cd = ?
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_prioritas_statistik()
    {
        $sql = "SELECT a.*, b.urusan_name, c.instansi_name
                FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                WHERE
                a.active_st = 'yes'
                AND a.prioritas_st = 'prioritas'
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_prioritas_geospasial()
    {
        $sql = "SELECT * 
                FROM geospasial a
                WHERE
                a.geo_prioritas_st = 'prioritas'
                ORDER BY a.geo_id ASC";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_prioritas_geospasial_by_opd($params)
    {
        $sql = "SELECT * 
                FROM geospasial a
                WHERE
                a.geo_prioritas_st = 'prioritas'
                AND a.geo_instansi_cd = ?
                ORDER BY a.geo_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_geospasial_by_opd($params)
    {
        $sql = "SELECT * FROM geospasial 
                WHERE geo_instansi_cd = ?
                ORDER BY geo_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_geospasial_all()
    {
        $sql = "SELECT * FROM geospasial 
                ORDER BY geo_id ASC";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_params($params)
    {

        $sql = "SELECT 
                a.data_id, a.parent_id, a.urusan_id, a.data_name, a.data_unit, a.rumus_type, a.rumus_detail,a.data_type,a.active_st,a.instansi_cd, a.mdb, a.mdd
                FROM trx_indicator a
                WHERE
                a.active_st = 'yes'
                AND a.urusan_id = ?
                AND a.data_id LIKE ?
                AND a.instansi_cd = ?
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);

        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_export_by_params_indicator_class($params, $indicator_class)
    {

        $sql = "SELECT * 
                FROM trx_indicator a
                WHERE
                a.active_st = 'yes'
                AND a.urusan_id = ?
                AND a.data_id LIKE ?
                AND a.instansi_cd = ?
                AND a.$indicator_class = 'yes'
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);

        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_indicator_class($params, $indicator_class)
    {
        $sql = "SELECT data_id, data_name
                FROM trx_indicator a
                WHERE a.active_st = 'yes'
                AND urusan_id LIKE ?
                AND instansi_cd = ?
                AND data_type = 'indikator'
                AND a.$indicator_class = 'yes'
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);

        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_by_indicator_class_semua($params)
    {

        $sql = "SELECT data_id, data_name
                FROM trx_indicator
                WHERE active_st = 'yes'
                AND urusan_id LIKE ?
                AND instansi_cd = ?
                AND data_type = 'indikator'
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

    public function get_indicator_export_by_params($params)
    {
        $sql = "SELECT * 
                FROM trx_indicator a
                WHERE
                a.active_st = 'yes'
                AND a.urusan_id = ?
                AND a.data_id LIKE ?
                AND a.instansi_cd = ?
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);
        //$query =  $db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_data_by_params($params)
    {
        $sql = "SELECT a.*, b.urusan_name, c.instansi_name
                FROM trx_indicator a
                LEFT JOIN trx_urusan b ON a.urusan_id = b.urusan_id
                LEFT JOIN mst_instansi c ON a.instansi_cd = c.instansi_cd
                WHERE
                a.active_st = 'yes'
                AND a.data_id LIKE ?
                ORDER BY a.data_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_variable($params)
    {
        $sql = "SELECT * 
                FROM trx_indicator a
                INNER JOIN trx_indicator_data b
                ON a. data_id = b. data_id
                WHERE
                a.active_st = 'yes'
                AND a.parent_id like ?
                GROUP BY a.data_id
                ORDER BY a.data_id ASC
                
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

    public function get_variable_1($params)
    {
        $sql = "SELECT * 
                FROM trx_indicator a
                INNER JOIN trx_indicator_data b
                ON a. data_id = b. data_id
                WHERE
                a.active_st = 'yes'
                AND a.parent_id like ?
                AND a.indikator_rpjmd = 'yes' 
                GROUP BY a.data_id
                ORDER BY a.data_id ASC
                
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

    public function get_data_tahun($params)
    {
        $query = "SELECT *
                    FROM trx_indicator_data a
                    WHERE
                    a.data_id = ?
                    AND a.year = ?
                    ";

        return $this->db2->query($query, $params)->row_array();
    }

    // function insert($params)
    // {
    //     return $this->db2->insert('trx_formula_request', $params);
    // }

    function insert_request($params)
    {
        return $this->db2->insert('trx_formula_request', $params);
    }

    function delete($params)
    {
        return $this->db2->delete('trx_formula_request', $params);
    }

    function update($params, $where)
    {
        return $this->db2->update('trx_formula_request', $params, $where);
    }
}
