<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_opd extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    // get list opd
    public function get_list_opd($params)
    {
        $sql = "SELECT * 
                FROM `mst_instansi` a
                WHERE
                a.`instansi_cd` = ? AND
                a.`show_st` = 'yes'";
        $query = $this->db->query($sql, $params);
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
        $sql = "SELECT a.*, REPLACE(a.`urusan_id`,'.','') AS id
        FROM `trx_urusan` a
        LEFT JOIN `trx_indicator` b ON a.`urusan_id` = b.`urusan_id`
        WHERE
        a.`active_st` = 'yes'
        AND b.`active_st` = 'yes'
        AND b.`instansi_cd` = ?
        GROUP BY a.`urusan_id`
        ORDER BY a.`urusan_id` ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_grafik($params)
    {

        $tahun = $params[0];
        $kode_opd = $params[1];

        $sql = " SELECT 
        a.`urusan_id`, a.`urusan_name`,
        REPLACE(a.`urusan_id`,'.','') AS id, 
        COUNT(*) AS total , 
        COUNT(IF(c.`value` !='',1,NULL )) As `terisi`, 
        COUNT(IF(c.`value` IS NULL OR c.`value` = '', 1, NULL)) As `kosong`
        FROM `trx_urusan` a
                           LEFT JOIN `trx_indicator` b ON a.`urusan_id` = b.`urusan_id`
                           LEFT JOIN `trx_indicator_data` c ON b.`data_id` = c.`data_id`
                           WHERE b.`instansi_cd` = $kode_opd   AND
                                    c.`year` =  $tahun
                           GROUP By a.`urusan_id`";

        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_count_total_data($params)
    {
        $sql = "SELECT * FROM
                (
                    SELECT 
                    COUNT(*) AS 'total'
                    FROM `trx_indicator` a
                    WHERE
                    a.`instansi_cd` = ?
                    AND a.`active_st` = 'yes'
                )xx,
                (
                    SELECT 
                    SUM(IF(aa.`data_id` IS NOT NULL,1,0)) AS 'tot_fill',
                    SUM(IF(aa.`value` IS NOT NULL,1,0)) AS 'tot_terisi'
                    
                    FROM `trx_indicator_data` aa
                    LEFT JOIN `trx_indicator` bb ON aa.`data_id` = bb.`data_id`
                    WHERE
                    bb.`instansi_cd` = ?
                    AND aa.`year` = ?
                )zz
                ";
        $query = $this->db->query($sql, $params);
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
                    COUNT(*) AS 'total'
                    FROM `trx_indicator` a
                    WHERE
                    a.`instansi_cd` = ?
                    AND a.`data_id` LIKE ?
                    AND a.`active_st` = 'yes'
                )xx,
                (
                    SELECT 
                    SUM(IF(aa.`data_id` IS NOT NULL,1,0)) AS 'tot_fill',
                    SUM(IF(aa.`value` IS NOT NULL,1,0)) AS 'tot_terisi'
                    
                    FROM `trx_indicator_data` aa
                    LEFT JOIN `trx_indicator` bb ON aa.`data_id` = bb.`data_id`
                    WHERE
                    bb.`instansi_cd` = ?
                    AND aa.`data_id` LIKE ?
                    AND aa.`year` = ?
                    AND bb.`active_st` = 'yes'
                )zz
                ";
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
