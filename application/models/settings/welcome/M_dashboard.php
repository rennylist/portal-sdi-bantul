<?php

class M_dashboard extends CI_Model {

    // constructor
    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /*
     * NEW
     */

    // get las login
    function get_last_login($params) {
        $sql = "SELECT login_date 
                FROM com_user_login
                WHERE user_id = ?
                ORDER BY login_date DESC
                LIMIT 2";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $rs_id = $query->result_array();
            $query->free_result();
            for ($i = 0; $i < 2; $i++) {
                $login_date = isset($rs_id[$i]['login_date']) ? $this->tdtm->get_full_date($rs_id[$i]['login_date'], 'in') : '';
                $result[$i] = $login_date;
            }
            return $result;
        }
        return array(
            '0' => '',
            '1' => '',
        );
    }

    // get jaldin belum dilaporkan
    function get_total_jaldin_waiting($params) {
        $sql = "SELECT COUNT(a.spt_id) AS 'total'
                FROM surat_tugas a
                INNER JOIN surat_tugas_process b ON a.spt_id = b.spt_id
                WHERE a.user_id = ? AND b.flow_id = '14006' AND b.action_st = 'process'
                GROUP BY a.spt_id";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get summary cuti
    function get_summary_cuti_by_tahun($params) {
        $sql = "SELECT a.total AS 'total_kuota' , COUNT(c.cuti_tanggal) AS 'total_cuti'
                FROM pegawai_cuti_kuota a
                LEFT JOIN pegawai_cuti b ON a.user_id = b.user_id
                LEFT JOIN pegawai_cuti_tanggal c ON b.cuti_id = c.cuti_id AND YEAR(c.cuti_tanggal) = ?
                WHERE a.tahun = ? AND a.user_id = ? AND a.jenis_id = 'CT.01' 
                AND b.cuti_status NOT IN ('draft', 'rejected')
                GROUP BY YEAR(c.cuti_tanggal)";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array(
                'total_kuota' => 0,
                'total_cuti' => 0,
            );
        }
    }

    // get summary ijin
    function get_summary_ijin_by_tahun($params) {
        $sql = "SELECT IFNULL(SUM(sakit), 0) AS 'total_sakit', IFNULL(SUM(lain), 0) AS 'total_ijin'
                FROM
                (
                    SELECT IF(a.jenis_id = 'IZ.00', 1, 0) AS 'sakit', IF(a.jenis_id <> 'IZ.00', 1, 0) AS 'lain'
                    FROM pegawai_izin a
                    WHERE a.izin_status NOT IN ('draft', 'rejected') 
                    AND YEAR(a.izin_tanggal) = ? AND a.user_id = ?
                ) result";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array(
                'total_sakit' => 0,
                'total_ijin' => 0,
            );
        }
    }

    // get total task
    function get_total_task_by_user($params) {
        $sql = "SELECT COUNT(b.flow_id) AS 'total'
                FROM com_role_user a
                INNER JOIN task_flow b ON a.role_id = b.role_id AND b.group_id IN ('11', '12', '13', '14')
                INNER JOIN task_group c ON b.group_id = c.group_id
                WHERE a.user_id = ?";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get total task approval - jaldin
    function get_total_task_approval_jaldin_by_user($params) {
        $sql = "SELECT COUNT(d.spt_id) AS 'total'
                FROM com_role_user a
                INNER JOIN task_flow b ON a.role_id = b.role_id AND b.group_id IN ('11', '12', '13', '14')
                INNER JOIN task_group c ON b.group_id = c.group_id
                INNER JOIN surat_tugas_process d ON b.flow_id = d.flow_id AND d.action_st = 'process'
                WHERE a.user_id = ?";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get total task approval - cuti
    function get_total_task_approval_cuti_by_user($params) {
        $sql = "SELECT COUNT(d.cuti_id) AS 'total'
                FROM com_role_user a
                INNER JOIN task_flow b ON a.role_id = b.role_id AND b.group_id IN ('11', '12', '13', '14')
                INNER JOIN task_group c ON b.group_id = c.group_id
                INNER JOIN pegawai_cuti_process d ON b.flow_id = d.flow_id AND d.action_st = 'process'
                WHERE a.user_id = ?";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get total task approval - ijin
    function get_total_task_approval_ijin_by_user($params) {
        $sql = "SELECT COUNT(d.izin_id) AS 'total'
                FROM com_role_user a
                INNER JOIN task_flow b ON a.role_id = b.role_id AND b.group_id IN ('11', '12', '13', '14')
                INNER JOIN task_group c ON b.group_id = c.group_id
                INNER JOIN pegawai_izin_process d ON b.flow_id = d.flow_id AND d.action_st = 'process'
                WHERE a.user_id = ?";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    // get total task approval - lembur
    function get_total_task_approval_lembur_by_user($params) {
        $sql = "SELECT COUNT(d.overtime_id) AS 'total'
                FROM com_role_user a
                INNER JOIN task_flow b ON a.role_id = b.role_id AND b.group_id IN ('11', '12', '13', '14')
                INNER JOIN task_group c ON b.group_id = c.group_id
                INNER JOIN surat_lembur_process d ON b.flow_id = d.flow_id AND d.action_st = 'process'
                WHERE a.user_id = ?";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total'];
        } else {
            return 0;
        }
    }

    /*
     * Kedisiplinan
     */

    // get total hari kerja
    function get_total_hari_kerja($params) {
        $sql = "SELECT get_jumlah_hari_kerja_te_by_bulan_tahun(?, ?, ?) AS 'total_hari_kerja'";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total_hari_kerja'];
        } else {
            return 0;
        }
    }

    // get total keterlambatan
    function get_total_keterlambatan($params) {
        $sql = "SELECT COUNT(a.kehadiran_id) AS 'total_keterlambatan' 
                FROM pegawai_kehadiran a
                WHERE a.user_id = ? AND YEAR(a.kehadiran_tanggal) = ? AND MONTH(a.kehadiran_tanggal) = ? 
                AND keterlambatan <> '00:00:00'";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total_keterlambatan'];
        } else {
            return 0;
        }
    }

    // get total ijin
    function get_total_ijin($params) {
        $sql = "SELECT COUNT(a.izin_id) AS 'total_ijin' 
                FROM pegawai_izin a
                WHERE a.user_id = ? AND YEAR(a.izin_tanggal) = ? AND MONTH(a.izin_tanggal) = ?
                AND a.izin_status NOT IN ('draft', 'rejected')";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total_ijin'];
        } else {
            return 0;
        }
    }

    // get total cuti
    function get_total_cuti($params) {
        $sql = "SELECT COUNT(a.cuti_id) AS 'total_cuti' 
                FROM pegawai_cuti a
                INNER JOIN pegawai_cuti_tanggal b ON a.cuti_id = b.cuti_id
                WHERE a.user_id = ? AND YEAR(b.cuti_tanggal) = ? AND MONTH(b.cuti_tanggal) = ?
                AND a.cuti_status NOT IN ('draft', 'rejected')";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total_cuti'];
        } else {
            return 0;
        }
    }

    // get total lembur
    function get_total_lembur($params) {
        $sql = "SELECT COUNT(a.overtime_id) AS 'total_lembur' 
                FROM surat_lembur a
                INNER JOIN pegawai_lembur b ON a.overtime_id = b.overtime_id
                WHERE b.user_id = ? AND YEAR(a.overtime_date) = ? AND MONTH(a.overtime_date) = ?
                AND a.overtime_st NOT IN ('draft', 'rejected')";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total_lembur'];
        } else {
            return 0;
        }
    }

    // get total jaldin
    function get_total_jaldin($params) {
        $sql = "SELECT COUNT(a.spt_id) AS 'total_jaldin' 
                FROM surat_tugas a
                INNER JOIN surat_tugas_tanggal b ON a.spt_id = b.spt_id
                WHERE a.user_id = ? AND YEAR(b.tanggal) = ? AND MONTH(b.tanggal) = ?
                AND a.spt_status NOT IN ('draft', 'rejected')";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result['total_jaldin'];
        } else {
            return 0;
        }
    }

    // get chart otp bulanan
    function get_chart_otp_bulanan($params) {
        $sql = "SELECT bulan, IF(jumlah_presensi <> 0, ((jumlah_otp / jumlah_presensi) * 100), 0) AS 'otp' 
                FROM 
                (
                    SELECT MONTH(a.kehadiran_tanggal) AS 'bulan', COUNT(a.kehadiran_id) AS 'jumlah_presensi', SUM(IF(a.keterlambatan <= '00:00:00', 1, 0)) AS 'jumlah_otp'
                    FROM pegawai_kehadiran a
                    WHERE a.user_id = ? AND YEAR(a.kehadiran_tanggal) = ?
                    GROUP BY MONTH(a.kehadiran_tanggal)
                ) result";
        $query = $this->db->query($sql, $params);
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $rs_id = $query->result_array();
            $query->free_result();
            $data = array();
            foreach ($rs_id as $rec) {
                $data[intval($rec['bulan'])] = intval($rec['otp']);
            }
        }
        // get per bulan
        $result = array();
        for ($i = 1; $i <= 12; $i++) {
            $result[$i] = isset($data[$i]) ? $data[$i] : '0';
        }
        // return
        return $result;
    }

    // get list jaldin
    function get_list_jaldin_by_user($params) {
        $sql = "SELECT c.client_nm, b.project_name, b.project_alias, a.tanggal_berangkat, a.uraian_tugas, 
                a.spt_status, a.lokasi_tujuan
                FROM surat_tugas a
                INNER JOIN projects b ON a.project_id = b.project_id
                INNER JOIN projects_clients c ON b.client_id = c.client_id
                WHERE a.user_id = ? AND a.spt_status NOT IN ('draft', 'rejected')
                ORDER BY tanggal_berangkat DESC
                LIMIT 0, 10";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // get list personel info
    function get_list_personel_info_by_today($params) {
        $sql = "SELECT * FROM
                (
                    SELECT 'J' AS 'jenis_kode', c.nama_lengkap, a.lokasi_tujuan AS 'tempat', a.uraian_tugas AS 'uraian'
                    FROM surat_tugas a
                    INNER JOIN surat_tugas_tanggal b ON a.spt_id = b.spt_id AND b.tanggal = CURRENT_DATE
                    INNER JOIN pegawai c ON a.user_id = c.user_id
                    INNER JOIN data_struktur_organisasi d ON c.struktur_cd = d.struktur_cd
                    WHERE a.user_id = ?
                    UNION ALL
                    SELECT 'C' AS 'jenis_kode', c.nama_lengkap, NULL AS 'tempat', 'Cuti' AS 'uraian'
                    FROM pegawai_cuti a
                    INNER JOIN pegawai_cuti_tanggal b ON a.cuti_id = b.cuti_id AND b.cuti_tanggal = CURRENT_DATE
                    INNER JOIN pegawai c ON a.user_id = c.user_id
                    INNER JOIN data_struktur_organisasi d ON c.struktur_cd = d.struktur_cd
                    WHERE a.user_id = ?
                    UNION ALL
                    SELECT IF(b.jenis_id = 'IZ.00', 'S', 'I') AS 'jenis_kode', c.nama_lengkap, NULL AS 'tempat', e.jenis_izin AS 'uraian'
                    FROM pegawai_izin b
                    INNER JOIN pegawai c ON b.user_id = c.user_id
                    INNER JOIN data_struktur_organisasi d ON c.struktur_cd = d.struktur_cd
                    INNER JOIN data_jenis_izin e ON b.jenis_id = e.jenis_id
                    WHERE b.user_id = ? AND b.izin_tanggal = CURRENT_DATE
                ) result
                ORDER BY nama_lengkap ASC";
        $query = $this->db->query($sql, $params);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

}
