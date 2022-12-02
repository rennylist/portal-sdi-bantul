<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_indicator_data extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function get_data_by_params($params)
    {
        $sql = "SELECT *
                    FROM `trx_indicator_data` a
                    WHERE
                    a.`data_id` = ?
                    AND a.`year` = ?
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

    // get list
    public function get_list_by_params($params)
    {
        $sql = "SELECT * 
                FROM `trx_indicator_data` a
                WHERE a.`year` = ?";
        return $this->db->query($sql, $params);
    }

    public function get_total_by_instansi()
    {
        $sql = "SELECT 
                a.`instansi_cd`, a.`instansi_name`, 
                SUM(IF(c.`submission_st` = 'pending',1,0)) AS 'tot_pending',
                SUM(IF(c.`submission_st` = 'rejected',1,0)) AS 'tot_reject',
                SUM(IF(c.`submission_st` = 'approved',1,0)) AS 'tot_approve'
                FROM `mst_instansi` a
                LEFT JOIN `trx_indicator` b ON a.`instansi_cd` = b.`instansi_cd`
                LEFT JOIN `trx_indicator_data` c ON b.`data_id` = c.`data_id`
                GROUP BY a.`instansi_cd`
                ORDER BY a.`instansi_name` ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_total_by_urusan($params)
    {
        $sql = "SELECT 
                SUM(IF(b.`submission_st` = 'pending',1,0)) AS 'tot_pending',
                SUM(IF(b.`submission_st` = 'rejected',1,0)) AS 'tot_reject',
                SUM(IF(b.`submission_st` = 'approved',1,0)) AS 'tot_approve'
                FROM `trx_indicator` a
                LEFT JOIN `trx_indicator_data` b ON a.`data_id` = b.`data_id`
                WHERE
                b.`data_id` LIKE ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_total_urusan_by_params($params)
    {
        $sql = "SELECT 
                SUM(IF(a.`data_id` IS NOT NULL,1,0)) AS 'tot',
                SUM(IF(b.`submission_st` = 'pending',1,0)) AS 'tot_pending',
                SUM(IF(b.`submission_st` = 'rejected',1,0)) AS 'tot_reject',
                SUM(IF(b.`submission_st` = 'approved',1,0)) AS 'tot_approve'
                FROM `trx_indicator` a
                LEFT JOIN `trx_indicator_data` b ON a.`data_id` = b.`data_id`
                WHERE
                b.`data_id` LIKE ?
                AND b.`year` = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_count($params)
    {
        $sql = "SELECT * FROM
                (
                    SELECT 
                    COUNT(*) AS 'total'
                    FROM `trx_indicator` a
                    WHERE
                    a.`data_id` LIKE ?
                )xx,
                (
                    SELECT 
                    SUM(IF(aa.`value` IS NOT NULL,1,0)) AS 'tot_fill',
                    SUM(IF(aa.`submission_st` = 'pending',1,0)) AS 'tot_pending',
                    SUM(IF(aa.`submission_st` = 'rejected',1,0)) AS 'tot_reject',
                    SUM(IF(aa.`submission_st` = 'approved',1,0)) AS 'tot_approve'
                    FROM `trx_indicator_data` aa 
                    WHERE
                    aa.`data_id` LIKE ?
                    AND aa.`year` = ?
                )zz";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_count_with_class($params, $indicator_class)
    {
        $sql = "SELECT * FROM
                (
                    SELECT 
                    COUNT(*) AS 'total'
                    FROM `trx_indicator` a
                    WHERE
                    a.`data_id` LIKE ?
                    AND $indicator_class = 'yes'
                )xx,
                (
                    SELECT 
                    SUM(IF(aa.`value` IS NOT NULL,1,0)) AS 'tot_fill',
                    SUM(IF(aa.`submission_st` = 'pending',1,0)) AS 'tot_pending',
                    SUM(IF(aa.`submission_st` = 'rejected',1,0)) AS 'tot_reject',
                    SUM(IF(aa.`submission_st` = 'approved',1,0)) AS 'tot_approve'
                    FROM `trx_indicator_data` aa 
                    WHERE
                    aa.`data_id` LIKE ?
                    AND aa.`year` = ?
                    
                )zz";
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
                    SUM(IF(aa.`submission_st` = 'pending',1,0)) AS 'tot_pending',
                    SUM(IF(aa.`submission_st` = 'rejected',1,0)) AS 'tot_reject',
                    SUM(IF(aa.`submission_st` = 'approved',1,0)) AS 'tot_approve'
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

    function insert_data($params)
    {
        $this->db->insert_batch('trx_indicator_data', $params);
    }

    public function update_data($params, $where)
    {
        $this->db->where('year', $where);
        $this->db->update_batch('trx_indicator_data', $params, 'data_id');
    }


    function update($params, $where)
    {
        return $this->db->update('trx_indicator_data', $params, $where);
    }

    function update_status_ba($params)
    {


        $sql = "UPDATE trx_indicator_data 
                SET 
                ba_status = 'yes'
                WHERE 
                data_id = ?
                AND `year` = ?";
        return $this->db->query($sql, $params);
    }

    public function view()
    {
        return $this->db->get('trx_indicator_data')->result();
    }

    function insert($params)
    {
        return $this->db->insert('trx_indicator_data', $params);
    }

    // delete trx_slideshow
    function delete($params)
    {
        return $this->db->delete('trx_indicator_data', $params);
    }
}
