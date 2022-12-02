<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_indicator_privileges_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    // get total
    public function get_priv($params)
    {
        $sql = "SELECT COUNT(*) AS 'total'
                FROM `trx_indicator_privileges`
                WHERE
                `data_id` = ?
                AND `year` = ?
                AND `instansi_cd` = ? ";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if ($result['total'] == 0)
                return "no";
            else
                return "yes";
        } else {
            return "no";
        }
    }



    public function get_all_by_instansi($params)
    {
        $sql = "(
            SELECT a.*
            FROM `trx_urusan` a
            LEFT JOIN `trx_indicator` b ON a.`urusan_id` = b.`urusan_id`
            LEFT JOIN `trx_indicator_privileges` c ON b.`data_id` = c.`data_id`
            WHERE
            a.`active_st` = 'yes'
            AND b.`active_st` = 'yes'
            AND c.`instansi_cd` = ?
            AND c.`year` = ?
            GROUP BY a.`urusan_id`
            ORDER BY a.`urusan_id` ASC
        )
        UNION ALL
        (
            SELECT bb.* 
            FROM 
            (
            SELECT a.*, COUNT(*) AS 'total_indicator'
            FROM `trx_urusan` a
            LEFT JOIN `trx_indicator` b ON a.`urusan_id` = b.`urusan_id`
            LEFT JOIN `trx_indicator_privileges` c ON b.`data_id` = c.`data_id`
            WHERE
            a.`active_st` = 'yes'
            AND b.`active_st` = 'yes'
            AND c.`instansi_cd` = ?
            AND c.`year` = ?
            GROUP BY a.`urusan_id`
            ORDER BY a.`urusan_id` ASC
            )aa
            LEFT JOIN `trx_urusan` bb ON aa.`parent_id` = bb.`urusan_id`
            WHERE bb.`active_st` = 'yes'
            GROUP BY bb.`urusan_id`
        )
        ORDER BY `urusan_id`";
        $query = $this->db->query($sql, $params);
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
        $sql = "SELECT * 
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND a.`data_type` = 'indicator'
                AND a.`urusan_id` = ?
                ORDER BY a.`data_id` ASC";
        $query = $this->db->query($sql, $params);
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
                FROM `trx_indicator` a
                LEFT JOIN `trx_indicator_privileges` b ON a.`data_id` = b.`data_id`
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND b.`instansi_cd` = ?
                AND b.`year` = ?
                AND a.`data_type` = 'indicator' 
                ORDER BY a.`data_id` ASC";
        $query = $this->db->query($sql, $params);
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
                FROM `trx_indicator` a
                LEFT JOIN `trx_indicator_privileges` b ON a.`data_id` = b.`data_id`
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND a.`data_id` LIKE ?
                AND b.`instansi_cd` = ?
                AND b.`year` = ?
                ORDER BY a.`data_id` ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_indicator_export_detail_by_params($params)
    {
        $sql = "SELECT * 
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND a.`parent_id` = ?
                ORDER BY a.`data_id` ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_usaha_by_params($params)
    {
        $sql = "SELECT 
                a.*
                FROM `trx_indicator_privileges` a
                LEFT JOIN `trx_indicator` b ON a.`data_id` = b.`data_id`
                WHERE
                a.`urusan_id` = ?
                AND a.`year` = ?
                AND a.`instansi_cd` = ?
                AND b.`active_st` = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_total_indicator($params)
    {
        $sql = "SELECT
                COUNT(*) AS 'total'
                FROM `trx_indicator` a
                WHERE 
                a.`parent_id` = ?
                ";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    public function delete($where)
    {
        return $this->db->delete('trx_indicator_privileges', $where);
    }

    public function insert($params)
    {
        return $this->db->insert('trx_indicator_privileges', $params);
    }
}
