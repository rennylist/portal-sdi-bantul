<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_verifikasi_ba extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function get_count_all_instansi($params)
    {
        $sql = "SELECT 
                COUNT(*) AS 'total_ba',
                SUM(IF(`ba_upload` IS NOT NULL,1,0)) AS 'ba_upload',              
                SUM(IF(`ba_status` = 'pending',1,0)) AS 'tot_pending',   
                SUM(IF(`ba_status` = 'rejected',1,0)) AS 'tot_reject',     
                SUM(IF(`ba_status` = 'approved',1,0)) AS 'tot_approve'            
                    FROM `trx_beritaacara` 
                    WHERE `instansi_cd` = ?
                    AND `ba_year` = ?
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
    public function get_ba_by_instansi($params)
    {
        $sql = "SELECT  *
                          
                    FROM `trx_beritaacara` 
                    WHERE `instansi_cd` = ?
                    AND `ba_year` = ?
                ";
        $query = $this->db->query($sql, $params);

        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function get_id_instansi_by_ba($params)
    {

        $sql = "SELECT 
                a.*
                FROM 
                trx_beritaacara a
                WHERE 
                ba_id = ?
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
