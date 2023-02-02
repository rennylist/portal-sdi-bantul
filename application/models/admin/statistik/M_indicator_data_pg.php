<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_indicator_data_pg extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->db2 = $this->load->database('pg', TRUE);
    }

    public function get_data_by_params($params)
    {

        $sql = "SELECT *
                    FROM trx_indicator_data a
                    WHERE
                    a.data_id = ?
                    AND a.year = ?
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

    // get list
    public function get_list_by_params($params)
    {
        $sql = "SELECT * 
                FROM trx_indicator_data a
                WHERE a.year = ?";
        return $this->db2->query($sql, $params);
    }

    public function get_total_by_instansi()
    {
        $sql = "SELECT 
                a.instansi_cd, a.instansi_name, 
                SUM(IF(c.submission_st = 'pending',1,0)) AS 'tot_pending',
                SUM(IF(c.submission_st = 'rejected',1,0)) AS 'tot_reject',
                SUM(IF(c.submission_st = 'approved',1,0)) AS 'tot_approve'
                FROM mst_instansi a
                LEFT JOIN trx_indicator b ON a.instansi_cd = b.instansi_cd
                LEFT JOIN trx_indicator_data c ON b.data_id = c.data_id
                GROUP BY a.instansi_cd
                ORDER BY a.instansi_name ASC";
        $query = $this->db2->query($sql);
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
                SUM(IF(b.submission_st = 'pending',1,0)) AS 'tot_pending',
                SUM(IF(b.submission_st = 'rejected',1,0)) AS 'tot_reject',
                SUM(IF(b.submission_st = 'approved',1,0)) AS 'tot_approve'
                FROM trx_indicator a
                LEFT JOIN trx_indicator_data b ON a.data_id = b.data_id
                WHERE
                b.data_id LIKE ?";
        $query = $this->db2->query($sql, $params);
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
                SUM(IF(a.data_id IS NOT NULL,1,0)) AS 'tot',
                SUM(IF(b.submission_st = 'pending',1,0)) AS 'tot_pending',
                SUM(IF(b.submission_st = 'rejected',1,0)) AS 'tot_reject',
                SUM(IF(b.submission_st = 'approved',1,0)) AS 'tot_approve'
                FROM trx_indicator a
                LEFT JOIN trx_indicator_data b ON a.data_id = b.data_id
                WHERE
                b.data_id LIKE ?
                AND b.year = ?";
        $query = $this->db2->query($sql, $params);
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
                    COUNT(*) AS total
                    FROM trx_indicator a
                    WHERE
                    a.data_id LIKE ?
                )xx,
                (
                    SELECT 
                    -- SUM(IF(aa.value IS NOT NULL,1,0)) AS 'tot_fill',
                    -- SUM(IF(aa.submission_st = 'pending',1,0)) AS 'tot_pending',
                    -- SUM(IF(aa.submission_st = 'rejected',1,0)) AS 'tot_reject',
                    -- SUM(IF(aa.submission_st = 'approved',1,0)) AS 'tot_approve'
                    COUNT(CASE WHEN aa.data_id IS NOT NULL THEN 1 END) AS tot_fill,
                    COUNT(CASE WHEN aa.submission_st = 'pending' THEN 1 END) AS tot_pending,
                    COUNT(CASE WHEN aa.submission_st = 'rejected' THEN 1 END) AS tot_reject,
                    COUNT(CASE WHEN aa.submission_st = 'approved' THEN 1 END) AS tot_approve

                    FROM trx_indicator_data aa 
                    WHERE
                    aa.data_id LIKE ?
                    AND aa.year = ?
                )zz";
        $query = $this->db2->query($sql, $params);
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
                    COUNT(*) AS total
                    FROM trx_indicator a
                    WHERE
                    a.data_id LIKE ?
                    AND $indicator_class = 'yes'
                )xx,
                (
                    SELECT 
                    -- SUM(IF(aa.value IS NOT NULL,1,0)) AS 'tot_fill',
                    -- SUM(IF(aa.submission_st = 'pending',1,0)) AS 'tot_pending',
                    -- SUM(IF(aa.submission_st = 'rejected',1,0)) AS 'tot_reject',
                    -- SUM(IF(aa.submission_st = 'approved',1,0)) AS 'tot_approve'
                    COUNT(CASE WHEN aa.data_id IS NOT NULL THEN 1 END) AS tot_fill,
                    COUNT(CASE WHEN aa.submission_st = 'pending' THEN 1 END) AS tot_pending,
                    COUNT(CASE WHEN aa.submission_st = 'rejected' THEN 1 END) AS tot_reject,
                    COUNT(CASE WHEN aa.submission_st = 'approved' THEN 1 END) AS tot_approve
                    FROM trx_indicator_data aa 
                    WHERE
                    aa.data_id LIKE ?
                    AND aa.year = ?
                    
                )zz";
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
                    SUM(CASE WHEN aa.value is not null THEN 1 else 0 END) AS tot_fill,
                    SUM(CASE WHEN aa.submission_st = 'pending' THEN 1 else 0 END) AS tot_pending,
                    SUM(CASE WHEN aa.submission_st = 'rejected' THEN 1 else 0 END) AS tot_reject,
                    SUM(CASE WHEN aa.submission_st = 'approved' THEN 1 else 0 END) AS tot_approve,
                    SUM(CASE WHEN aa.submission_st = '-' THEN 1 else 0 END) AS tot_strip
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

    function insert_data($params)
    {
        $this->db2->insert_batch('trx_indicator_data', $params);
    }

    public function update_data($params, $where)
    {
        $this->db2->where('year', $where);
        $this->db2->update_batch('trx_indicator_data', $params, 'data_id');
    }


    function update($params, $where)
    {
        return $this->db2->update('trx_indicator_data', $params, $where);
    }

    function update_status_ba($params)
    {


        $sql = "UPDATE trx_indicator_data 
                SET 
                ba_status = 'yes'
                WHERE 
                data_id = ?
                AND year = ?";
        return $this->db2->query($sql, $params);
    }

    public function view()
    {
        return $this->db2->get('trx_indicator_data')->result();
    }

    function insert($params)
    {

        return $this->db2->insert('trx_indicator_data', $params);
    }

    // delete trx_slideshow
    function delete($params)
    {
        return $this->db2->delete('trx_indicator_data', $params);
    }

    public function get_data_xxx($params)
    {
        $sql = "SELECT
                trx_indicator.data_id,
                mst_instansi.instansi_name,
                trx_indicator.data_name,
                trx_indicator.data_unit,
                trx_indicator.rumus_type,
                satu.value as satu_value,
                satu.data_st as satu_st,
                dua.value as dua_value,
                dua.data_st as dua_st,
                tiga.value as value,
                tiga.data_st as data_st,
                tiga.submission_st  as submission_st,
                tiga.verify_comment  AS verify_comment
                FROM trx_indicator
                INNER JOIN mst_instansi ON mst_instansi.instansi_cd = trx_indicator.instansi_cd
                LEFT JOIN trx_indicator_data satu   ON trx_indicator.data_id = satu.data_id     AND satu.year = ?
                LEFT JOIN trx_indicator_data dua    ON trx_indicator.data_id = dua.data_id      AND dua.year = ?
                LEFT JOIN trx_indicator_data tiga   ON trx_indicator.data_id = tiga.data_id     AND tiga.year = ? 
                LEFT join trx_indicator_data on trx_indicator.data_id = trx_indicator_data.data_id 
                WHERE
                trx_indicator.urusan_id LIKE ?
                AND trx_indicator.data_id LIKE ?
                AND mst_instansi.instansi_cd LIKE ?
                GROUP BY 
                trx_indicator.data_id, trx_indicator.data_name, trx_indicator.data_unit,  trx_indicator.rumus_type, satu.value, dua.value,
                tiga.value, satu.data_st, dua.data_st, tiga.data_st, tiga.submission_st, tiga.verify_comment, mst_instansi.instansi_name
                ORDER BY data_id ASC;
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
