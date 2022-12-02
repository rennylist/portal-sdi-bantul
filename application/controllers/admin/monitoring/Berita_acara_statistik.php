<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// LOAD BASE CLASS IF NEEDED
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Berita_acara_statistik extends OperatorBase
{

    // CONSTRUCTOR
    public function __construct()
    {
        parent::__construct();
        // LOAD MODEL
        $this->load->model('admin/master/M_instansi');
        $this->load->model('admin/berita_acara/M_verifikasi_ba');
        $this->load->model('admin/berita_acara/M_create_ba');
        $this->load->model('admin/statistik/M_urusan');

        // LOAD LIBRARY
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->load->library('SimpleXLSXGen');
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    public function index()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/verifikasi/berita_acara_statistik/index.html");

        // CREATE LIST OPTION FOR YEAR
        $option_year = date("Y");
        $option_years = array();
        $option_year_min = 10;
        for ($i = ($option_year -  $option_year_min); $i <= $option_year; $i++) {
            array_push($option_years, $i);
        }

        // GET SESSION SEARCH
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id  = empty($search['indicator_id']) ? "%" : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // GET DATA FOR TABLE
        $rs_id = $this->M_instansi->get_datas_all();

        foreach ($rs_id as $key => $value) {
            // GET COUNTING DATA
            $instansi_cd = $value['instansi_cd'];
            $param = array($instansi_cd, $tahun);
            $data = $this->M_verifikasi_ba->get_count_all_instansi($param);

            // PARSING TO ARRAY DATA
            $rs_id[$key]['tot']         = $data['total_ba'];
            $rs_id[$key]['ba_upload']    = $data['ba_upload'];
            $rs_id[$key]['tot_pending'] = $data['tot_pending'];
            $rs_id[$key]['tot_reject']  = $data['tot_reject'];
            $rs_id[$key]['tot_approve'] = $data['tot_approve'];
            $rs_id[$key]['ba_belum_upload']     = $data['total_ba'] - $data['ba_upload'];
        }

        // ASSIGN DATA
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("option_years", array_reverse($option_years));
        $this->tsmarty->assign("option_year", $option_year);
        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // OUTPUT
        parent::display();
    }

    public function instansi($instansi_cd)
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/verifikasi/berita_acara_statistik/instansi.html");

        // GET DATA INSTANSI & IF EMPTY REDIRECT TO INDEX

        $instansi = $this->M_instansi->get_detail_instansi(array($instansi_cd));
        if (empty($instansi)) redirect("admin/verifikasi/berita_acara_statistik");


        // CREATE LIST OPTION FOR YEAR
        $option_year = date("Y");
        $option_years = array();
        $option_year_min = 10;
        for ($i = ($option_year -  $option_year_min); $i <= $option_year; $i++) {
            array_push($option_years, $i);
        }

        // GET SESSION SEARCH
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id  = empty($search['indicator_id']) ? "%" : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);
        $param = array($instansi_cd, $tahun);
        // GET DATA FOR TABLE WITHOUT PRIVILEGES
        $rs_id = $this->M_verifikasi_ba->get_ba_by_instansi($param);

        // ASSIGN DATA
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("instansi_cd", $instansi_cd);
        $this->tsmarty->assign("instansi", $instansi);
        $this->tsmarty->assign("option_years", array_reverse($option_years));
        $this->tsmarty->assign("option_year", $option_year);
        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // OUTPUT
        parent::display();
    }

    public function search_instansi_process()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // CEK INPUT PROCESS EMPTY OR NOT
        if ($this->input->post('process') == "process") {
            // SET PARAMS
            $params = array(
                "tahun" => trim(strip_tags($this->input->post('tahun', TRUE))),
                // "indicator_id" => trim(strip_tags($this->input->post('indicator_id', TRUE))),
                // "tahun" => trim(strip_tags($this->input->post('tahun', TRUE))),
            );
            // GET SESSION
            $this->session->set_userdata("search_indicator", $params);
        } else {
            // UNSET SESSION
            $this->session->unset_userdata("search_indicator");
        }
        // REDIRECT
        if (empty(trim(strip_tags($this->input->post('instansi_cd', TRUE)))))
            redirect("admin/verifikasi/berita_acara_statistik");
        else
            redirect("admin/verifikasi/berita_acara_statistik/instansi/" . trim(strip_tags($this->input->post('instansi_cd', TRUE))));
    }

    public function proses()
    {

        // SET PAGE RULES
        $this->_set_page_rule("C");

        $ba_id = trim(strip_tags($this->input->post('ba_id', TRUE)));
        $instansi = $this->M_verifikasi_ba->get_id_instansi_by_ba(array($ba_id));
        $instansi_cd = $instansi['instansi_cd'];
        $ba_status = trim(strip_tags($this->input->post('ba_status', TRUE)));
        $ba_catatan = trim(strip_tags($this->input->post('ba_catatan', TRUE)));

        // UPDATE BA
        $param = array(
            "ba_status" => $ba_status,
            "ba_catatan" => $ba_catatan,
            "ba_status_update_date" => date("Y-m-d H:i:s")
        );
        $where = array(
            "ba_id" => $ba_id
        );

        //update tabel ba
        $this->M_create_ba->update_ba($param, $where);
        $this->tnotification->sent_notification("success",  "berita acara berhasil di verifikasi !");
        redirect("admin/verifikasi/berita_acara_statistik/instansi/" . $instansi_cd);
    }

    public function unduh_rincian($ba_id)
    {

        //GET INSTANSI 
        $ba_data = $this->M_verifikasi_ba->get_id_instansi_by_ba(array($ba_id));
        $instansi_cd = $ba_data['instansi_cd'];
        $instansi = $this->M_instansi->get_detail_instansi(array($instansi_cd));

        //GET BA 
        $param = array($instansi_cd,  $ba_id);
        $query = $this->M_create_ba->get_berita_acara_by_ba_id($param);

        foreach ($query as $data_ba) {
            $data_ba;
        }
        $tahun = $data_ba['ba_year'];

        // GET URUSAN
        $get_urusan = $this->M_urusan->get_urusan_by_instansi($instansi_cd);
        $urusan_id = array();
        foreach ($get_urusan as $result) {
            array_push(
                $urusan_id,
                $result['urusan_id']
            );
        }
        $arr = $urusan_id;
        $data_id = implode(', ', $arr);

        // GET INDICATOR
        $tahun = $data_ba['ba_year'];
        $param = array($ba_id, $instansi_cd,  $tahun);
        $rsid = $this->M_create_ba->get_indicator_by_instansi_unduh_rincian($param, $data_id);



        // SET TITLE
        $result = array();

        // TITLE
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_name'] = "<b>Nama Data</b>";
        $result[0]['data_type'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
        $result[0]['year'] = "<b>Tahun</b>";
        $result[0]['data_unit'] = "<b>Satuan</b>";
        $result[0]['value'] = "<b>Nilai</b>";
        $result[0]['data_st'] = "<b>Sifat Data</b>";


        // MERGE DATA;
        $instansi_name = $instansi['instansi_name'];
        $data = array_merge($result, $rsid);
        // print_r("<pre>");
        // print_r($data);
        // print_r("</pre>");
        // die;
        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($data);
        $filename =  "Rincian BA " . $data_ba['ba_month'] . " " . $data_ba['ba_year'] . " " . $instansi_name . ".xlsx";
        $xlsx->downloadAs($filename);
    }
    public function kirim_ckan($ba_id)
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/verifikasi/berita_acara_statistik/kirim_ckan.html");

        // GET DATA
        $instansi_cd = $this->com_user['instansi_cd'];

        print_r($instansi_cd);

        // ASSIGN
        $this->tsmarty->assign("instansi_cd", $instansi_cd);
        $this->tsmarty->assign("ba_id", $ba_id);

        // OUTPUT
        parent::display();
    }
}
