<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_urusan_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->db2 = $this->load->database('pg', TRUE);
    }

    // get total
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

    // get list
    public function get_list($params)
    {
        $sql = "SELECT * 
                FROM trx_urusan a
                WHERE
                a.active_st = 'yes'
                ORDER BY a.urusan_id ASC
                LIMIT ?, ?";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_all()
    {
        $sql = "SELECT * 
                FROM trx_urusan a
                WHERE
                a.active_st = 'yes'
                ORDER BY a.urusan_id ASC";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_instansi_by_urusan($params)
    {
        $sql = "SELECT a.instansi_cd, b.instansi_name
                FROM trx_indicator a
                LEFT JOIN mst_instansi b ON a.instansi_cd = b.instansi_cd
                WHERE urusan_id = ?
                GROUP BY a.instansi_cd";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }


    public function get_all_by_instansi($params)
    {
            // $sql = "(
            //             SELECT a.*
            //             FROM trx_urusan a
            //             LEFT JOIN trx_indicator b ON a.urusan_id = b.urusan_id
            //             WHERE
            //             a.active_st = 'yes'
            //             AND b.active_st = 'yes'
            //             AND b.instansi_cd = ?
            //             GROUP BY a.urusan_id
                        
            //         )
            //         UNION ALL
            //         (
            //             SELECT bb.* 
            //             FROM 
            //             (
            //                 SELECT a.*, COUNT(*) AS total_indicator
            //                 FROM trx_urusan a
            //                 LEFT JOIN trx_indicator b ON a.urusan_id = b.urusan_id
            //                 WHERE
            //                 a.active_st = 'yes'
            //                 AND b.active_st = 'yes'
            //                 AND b.instansi_cd = ?
            //                 GROUP BY a.urusan_id
                            
            //             )aa
            //             LEFT JOIN trx_urusan bb ON aa.parent_id = bb.urusan_id
            //             WHERE bb.active_st = 'yes'
            //             GROUP BY bb.urusan_id
            //         )
            //     ";


            $sql = "(
                SELECT  a.urusan_id, a.parent_id, a.urusan_name, 0 as total_indicator
                FROM trx_urusan a
                LEFT JOIN trx_indicator b ON a.urusan_id = b.urusan_id
                WHERE
                a.active_st = 'yes'
                AND b.active_st = 'yes'
                AND b.instansi_cd = ?
                GROUP BY a.urusan_id, a.parent_id, a.urusan_name 
                
            )
            UNION ALL
            (
                SELECT bb.urusan_id, bb.parent_id, bb.urusan_name, aa.total_indicator
                FROM 
                (
                    SELECT a.urusan_id, a.parent_id, a.urusan_name, COUNT(*) AS total_indicator
                    FROM trx_urusan a
                    LEFT JOIN trx_indicator b ON a.urusan_id = b.urusan_id
                    WHERE
                    a.active_st = 'yes'
                    AND b.active_st = 'yes'
                    AND b.instansi_cd = ?
                    GROUP BY a.urusan_id, a.parent_id, a.urusan_name 
                    
                )aa
                LEFT JOIN trx_urusan bb ON aa.parent_id = bb.urusan_id
                WHERE bb.active_st = 'yes'
                GROUP BY bb.urusan_id, bb.parent_id, bb.urusan_name, aa.total_indicator
            )
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


    public function get_all_data()
    {
        $sql = "(
                    SELECT a.*
                    FROM trx_urusan a
                    LEFT JOIN trx_indicator b ON a.urusan_id = b.urusan_id
                    WHERE
                    a.active_st = 'yes'
                    AND b.active_st = 'yes'
                    GROUP BY a.urusan_id
                    ORDER BY a.urusan_id ASC
                )
                UNION ALL
                (
                    SELECT bb.* 
                    FROM 
                    (
                        SELECT a.*, COUNT(*) AS 'total_indicator'
                        FROM trx_urusan a
                        LEFT JOIN trx_indicator b ON a.urusan_id = b.urusan_id
                        WHERE
                        a.active_st = 'yes'
                        AND b.active_st = 'yes'
                        GROUP BY a.urusan_id
                        ORDER BY a.urusan_id ASC
                    )aa
                    LEFT JOIN trx_urusan bb ON aa.parent_id = bb.urusan_id
                    WHERE bb.active_st = 'yes'
                    GROUP BY bb.urusan_id
                )
                ORDER BY urusan_id";
        $query = $this->db2->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_list_urusan($params)
    {
        $sql = "SELECT * 
                FROM trx_urusan a
                WHERE
                a.active_st = 'yes' 
                AND a. urusan_id like ?
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

    public function get_detail_urusan($params)
    {
        $sql = "SELECT * 
                FROM trx_urusan a
                WHERE
                a.active_st = 'yes' 
                AND a. urusan_id = ?
                ORDER BY a.urusan_id ASC";
        $query = $this->db2->query($sql, $params);

        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_detail_urusan_parent($params)
    {
        $sql = "SELECT * 
                FROM trx_urusan a
                WHERE
                a.active_st = 'yes' 
                AND a. parent_id = ?
                ORDER BY a.urusan_id ASC";
        $query = $this->db2->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    public function get_urusan_by_instansi($params)
    {

        $sql = "SELECT a.urusan_id
                FROM trx_urusan a
                LEFT JOIN trx_indicator b ON a.urusan_id = b.urusan_id
                WHERE
                a.active_st= 'yes'
                AND b.active_st = 'yes'
                AND b.instansi_cd = ?
                GROUP BY a.urusan_id
                ORDER BY a.urusan_id ASC
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
}
