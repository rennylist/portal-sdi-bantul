<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_admin_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->db2 = $this->load->database('pg', TRUE);
    }



    public function get_opd()
    {
        $sql = "SELECT instansi_name
                FROM mst_instansi a
                WHERE a.show_st = 'yes'
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


    public function get_bidang_urusan($params)
    {
        $sql = "SELECT a.*
        FROM trx_urusan a
        LEFT JOIN trx_indicator b ON a.urusan_id = b.urusan_id
        WHERE
        a.active_st = 'yes'
        AND b.active_st = 'yes'
        AND b.instansi_cd = ?
        GROUP BY a.urusan_id
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


    public function get_total($params)
    {
        $sql = "SELECT COUNT(*) AS total 
        FROM trx_indicator a
         JOIN trx_indicator_data b ON a.data_id = b.data_id
         WHERE b.year = ? 
         AND a.active_st = 'yes'";

        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }
    public function get_kosong($params)
    {
        $sql = "SELECT COUNT(IF(b.value IS NULL  , 1, NULL))  AS total 
        FROM trx_indicator a
         LEFT JOIN trx_indicator_data b ON a.data_id = b.data_id
         WHERE b.year = ?
         ";

        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }


    public function get_count_total_data($params)
    {
        $sql = "SELECT * FROM
                (
                    SELECT 
                    COUNT(*) AS total
                    FROM trx_indicator a
                    where a.active_st = 'yes'
                                     
                )xx,
                (
                    SELECT 
                    SUM(case when(aa.data_id IS NOT NULL)then 1 else 0 end) AS tot_fill, 
                    SUM(case when(aa.value <> '' )then 1 else 0 end) AS tot_terisi,
                    SUM(case when(aa.value = '' OR aa.value IS NULL )then 1 end) AS tot_kosong
                    FROM trx_indicator_data aa
                    LEFT JOIN trx_indicator bb ON aa.data_id = bb.data_id
                    WHERE
                    aa.year = ?
                )zz
                ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_count_by_instansi($params)
    {
        $sql = "SELECT * FROM
                (
                    SELECT 
                    COUNT(*) AS total
                    FROM trx_indicator a
                    WHERE
                    a.instansi_cd = ?
                    AND a.data_id LIKE ?
                    AND a.active_st = 'yes'
                )xx,
                (
                    SELECT 
                    SUM(case when(aa.data_id IS NOT NULL)then 1 else 0 end) AS tot_fill, 
                    SUM(case when(aa.value <> '' )then 1 else 0 end) AS tot_terisi
                    FROM trx_indicator_data aa
                    LEFT JOIN trx_indicator bb ON aa.data_id = bb.data_id
                    WHERE
                    bb.instansi_cd = ?
                    AND aa.data_id LIKE ?
                    AND aa.year = ?
                    AND bb.active_st = 'yes'
                )zz
                ";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }
}
