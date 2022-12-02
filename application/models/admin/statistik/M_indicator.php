<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_indicator extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    // get total
    public function get_total($params)
    {
        $sql = "SELECT COUNT(*) AS 'total' FROM
                (
                    SELECT * 
                    FROM `trx_urusan` a
                    WHERE
                    a.`active_st` = 'yes'
                    ORDER BY a.`urusan_id` ASC
                )xx";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get list
    public function get_list($params)
    {
        $sql = "SELECT * 
                FROM `trx_urusan` a
                WHERE
                a.`active_st` = 'yes'
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


    public function get_indicator_for_priviliges($params)
    {
        $sql = "SELECT *
                FROM `trx_indicator`
                WHERE 
                `data_id` LIKE ?
                AND `data_type` = 'indicator'
                AND `active_st` = 'yes'
                ORDER BY `data_id` ASC";
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
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND a.`instansi_cd` = ?
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

    public function get_indicator_by_instansi_with_class($params, $indicator_class)
    {
        $sql = "SELECT * 
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND a.`instansi_cd` = ?
                AND a.`data_type` = 'indicator' 
                AND $indicator_class = 'yes'
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


    public function get_indicator_all()
    {
        $sql = "SELECT a.*, b.`urusan_name`, c.`instansi_name`
                FROM `trx_indicator` a
                LEFT JOIN `trx_urusan` b ON a.`urusan_id` = b.`urusan_id`
                LEFT JOIN `mst_instansi` c ON a.`instansi_cd` = c.`instansi_cd`
                ORDER BY a.`data_id` ASC";
        $query = $this->db->query($sql);
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
        $sql = "SELECT a.*, b.`instansi_name`
                FROM `trx_indicator` a
                LEFT JOIN `mst_instansi` b ON a.`instansi_cd` = b.`instansi_cd`
                WHERE
                a.`active_st` = 'yes'
                AND a.`instansi_cd` = ?
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

    public function get_indicator_by_params($params)
    {
        $sql = "SELECT 
                a.`data_id`, a.`parent_id`, a.`urusan_id`, a.`data_name`, a.`data_unit`,a.`data_type`,a.`active_st`,a.`instansi_cd`, a.`mdb`, a.`mdd`
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND a.`data_id` LIKE ?
                AND a.`instansi_cd` = ?
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

    public function get_indicator_export_by_params_indicator_class($params, $indicator_class)
    {

        $sql = "SELECT * 
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND a.`data_id` LIKE ?
                AND a.`instansi_cd` = ?
                AND a.`$indicator_class` = 'yes'
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

    public function get_indicator_by_indicator_class($params, $indicator_class)
    {

        $sql = "SELECT data_id, data_name
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND `urusan_id` LIKE ?
                AND `instansi_cd` = ?
               AND `data_type` = 'indicator'
                AND a.`$indicator_class` = 'yes'
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

    public function get_indicator_by_indicator_class_semua($params)
    {

        $sql = "SELECT data_id, data_name
                FROM `trx_indicator` a
                WHERE
                a.`active_st` = 'yes'
                AND `urusan_id` LIKE ?
                AND `instansi_cd` = ?
               AND `data_type` = 'indicator'
                
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
                WHERE
                a.`active_st` = 'yes'
                AND a.`urusan_id` = ?
                AND a.`data_id` LIKE ?
                AND a.`instansi_cd` = ?
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

    public function get_data_by_params($params)
    {
        $sql = "SELECT a.*, b.`urusan_name`, c.`instansi_name`
                FROM `trx_indicator` a
                LEFT JOIN `trx_urusan` b ON a.`urusan_id` = b.`urusan_id`
                LEFT JOIN `mst_instansi` c ON a.`instansi_cd` = c.`instansi_cd`
                WHERE
                a.`active_st` = 'yes'
                AND a.`data_id` LIKE ?
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

    public function get_variable($params)
    {
        $sql = "SELECT * 
                FROM `trx_indicator` a
                INNER JOIN `trx_indicator_data` b
                ON a. `data_id` = b. `data_id`
                WHERE
                a.`active_st` = 'yes'
                AND a.`parent_id` like ?
                GROUP BY a.`data_id`
                ORDER BY a.`data_id` ASC
                
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

    public function get_variable_1($params)
    {
        $sql = "SELECT * 
                FROM `trx_indicator` a
                INNER JOIN `trx_indicator_data` b
                ON a. `data_id` = b. `data_id`
                WHERE
                a.`active_st` = 'yes'
                AND a.`parent_id` like ?
                AND a.`indikator_rpjmd` = 'yes' 
                GROUP BY a.`data_id`
                ORDER BY a.`data_id` ASC
                
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

    public function get_data_tahun($params)
    {
        $query = "SELECT *
                    FROM `trx_indicator_data` a
                    WHERE
                    a.`data_id` = ?
                    AND a.`year` = ?
                    ";

        return $this->db->query($query, $params)->row_array();
    }
}
