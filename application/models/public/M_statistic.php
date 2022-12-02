<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_statistic extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function insert($params){
        $this->db->insert('trx_statistic', $params);
        return $this->db->insert_id();
    }

    function update($params) {
        $sql = "UPDATE trx_statistic 
                SET 
                browse_hits = browse_hits+1 ,
                browse_end = ?,
                user_uuid = ?
                WHERE 
                statistic_id = ?";
        return $this->db->query($sql, $params);
    }

    function update_nonuuid($params) {
        $sql = "UPDATE trx_statistic 
                SET 
                browse_hits = browse_hits+1 ,
                browse_end = ?
                WHERE 
                statistic_id = ?";
        return $this->db->query($sql, $params);
    }

    function get_stat() {
        $sql = "SELECT 
                (
                    SELECT COUNT(*)'stat_total' 
                    FROM `trx_statistic`
                ) AS 'stat_total'
                ,
                (
                    SELECT COUNT(*)'stat_total' 
                    FROM `trx_statistic`
                    WHERE MONTH(`mdd`) = MONTH(NOW())
                ) AS 'stat_month'
                ,
                (
                    SELECT COUNT(*)'stat_today' 
                    FROM `trx_statistic`
                    WHERE DATE(`mdd`) = DATE(NOW())
                ) AS 'stat_today'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    //DETAIL
    
    function insert_detail($params){
        $this->db->insert('trx_statistic_detail', $params);
        return $this->db->insert_id();
    }

    function check_exist_stat_detail($params)
    {
        $sql = "SELECT 
                COUNT(*) AS 'total'
                FROM `trx_statistic_detail`
                WHERE 
                `statistic_id` = ?
                AND `url` = ?";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            if( $result['total'] == 0){
                return true;
            }else{
                return false;
            }
        } else {
            return false;
        }
    }

    function update_detail($params) {
        $sql = "UPDATE trx_statistic_detail 
                SET 
                browse_hits = browse_hits+1 ,
                browse_end = ?,
                user_uuid = ?
                WHERE 
                statistic_id = ?
                AND `url` = ?";
        return $this->db->query($sql, $params);
    }

    function update_detail_nonuuid($params) {
        $sql = "UPDATE trx_statistic_detail 
                SET 
                browse_hits = browse_hits+1 ,
                browse_end = ?
                WHERE 
                statistic_id = ?
                AND `url` = ?";
        return $this->db->query($sql, $params);
    }
}

?>