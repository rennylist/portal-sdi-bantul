<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_statistik extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    // list data
    function get_datas() {
        $sql = "SELECT * FROM(
                    SELECT COUNT(*) AS 'tot_raperda'
                    FROM `trx_raperda`
                )a, 
                (
                    SELECT COUNT(*) AS 'tot_raperda_aspirasi'
                    FROM `trx_raperda_aspirasi`
                )b,
                (
                    SELECT COUNT(*) AS 'tot_aspirasi'
                    FROM `trx_aspirasi`
                )c,
                (
                    SELECT COUNT(*) AS 'tot_aspirasi_finish'
                    FROM `trx_aspirasi`
                    WHERE `aspirasi_st` = 'finish'
                )d";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

}

?>