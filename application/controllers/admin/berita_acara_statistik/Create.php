<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// LOAD BASE CLASS IF NEEDED
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Create extends OperatorBase
{
    // CONSTRUCTOR
    public function __construct()
    {
        parent::__construct();
        // LOAD MODEL

        $this->load->model('admin/berita_acara/M_create_ba');
        $this->load->model('admin/berita_acara/M_create_ba_pg');
        $this->load->model('admin/statistik/M_indicator_data');
        $this->load->model('admin/statistik/M_urusan');
        $this->load->model('admin/statistik/M_urusan_pg');
        $this->load->model('settings/M_user');
        $this->load->model('admin/master/M_instansi');

        // LOAD LIBRARY
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
        $this->load->helper("terbilang");
    }

    public function index()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/berita_acara_statistik/index.html");

        // GET INSTANSI CD
        $instansi_cd = $this->com_user['instansi_cd'];
        //$rsid = $this->M_create_ba->get_berita_acara_all($instansi_cd);

        // GET SESSION SEARCH
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id  = empty($search['indicator_id']) ? "%" : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // CREATE LIST OPTION FOR YEAR
        $option_year = date("Y");
        $option_years = array();
        $option_year_min = 10;
        for ($i = ($option_year -  $option_year_min); $i <= $option_year; $i++) {
            array_push($option_years, $i);
        }

        // GET URUSAN
        $get_urusan = $this->M_urusan_pg->get_urusan_by_instansi($instansi_cd);
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
        $param = array($instansi_cd,  $tahun);

        $rsid = $this->M_create_ba_pg->get_indicator_by_instansi($param, $data_id);
        $rsid_count = $this->M_create_ba_pg->get_indicator_by_instansi_count($param, $data_id);
        $sum_data = $this->M_create_ba_pg->get_sum_data_indicator($param);

        //die;

        foreach ($sum_data as $sum_data) {
            $sum_indicator = $sum_data['sum_indicator'];
            $sum_variabel = $sum_data['sum_variabel'];
            $sum_sub_variabel = $sum_data['sum_sub_variabel'];
            $sum_sub_sub_variabel = $sum_data['sum_sub_sub_variabel'];
        }
        $print_data = $sum_indicator . " (" . terbilang($sum_indicator) . ") indikator, " .
            $sum_variabel . " (" . terbilang($sum_variabel) . ") variabel, " .
            $sum_sub_variabel . " (" . terbilang($sum_sub_variabel) . ") sub variabel, dan " .
            $sum_sub_sub_variabel . " (" . terbilang($sum_sub_sub_variabel) . ") sub sub variabel ";
        $nomor = 1;
        //print_r($sum_variabel);

        // ASSIGN DATA
        $this->tsmarty->assign("option_years", array_reverse($option_years));
        $this->tsmarty->assign("option_year", $option_year);
        $this->tsmarty->assign("tahun", $tahun);
        $this->tsmarty->assign("nomor", $nomor);
        $this->tsmarty->assign("rsid", $rsid);
        $this->tsmarty->assign("rsid_count", $rsid_count);
        $this->tsmarty->assign("sum_indicator", $sum_indicator);
        $this->tsmarty->assign("sum_variabel", $sum_variabel);
        $this->tsmarty->assign("sum_sub_variabel", $sum_sub_variabel);
        $this->tsmarty->assign("sum_sub_sub_variabel", $sum_sub_sub_variabel);
        $this->tsmarty->assign("print_data", $print_data);


        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // OUTPUT
        parent::display();
    }

    public function search_process()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // CEK INPUT PROCESS EMPTY OR NOT
        if ($this->input->post('process') == "process") {
            // SET PARAMS
            $params = array(
                "tahun" => trim(strip_tags($this->input->post('tahun', TRUE))),
            );
            // GET SESSION
            $this->session->set_userdata("search_indicator", $params);
        } else {
            // UNSET SESSION
            $this->session->unset_userdata("search_indicator");
        }
        // REDIRECT
        $url = "admin/berita_acara_statistik/create";
        redirect($url);
    }

    public function add_draft()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");

        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/berita_acara_statistik/add.html");

        $tahun = trim(strip_tags($this->input->post('tahun', TRUE)));
        $instansi_cd = $this->com_user['instansi_cd'];

        // GET INSTANSI CD
        $print_data = trim(strip_tags($this->input->post('print_data', TRUE)));
        $sum_indicator = trim(strip_tags($this->input->post('sum_indicator', TRUE)));
        $sum_variabel = trim(strip_tags($this->input->post('sum_variabel', TRUE)));
        $sum_sub_variabel = trim(strip_tags($this->input->post('sum_sub_variabel', TRUE)));
        $sum_sub_sub_variabel = trim(strip_tags($this->input->post('sum_sub_sub_variabel', TRUE)));

        $tgl = array();
        for ($i = 1; $i <= 31; $i++) {
            array_push($tgl, $i);
        }
        $instansi = $this->M_instansi->get_detail_instansi($instansi_cd);


        // ASSIGN DATA
        $this->tsmarty->assign("tahun", $tahun);
        $this->tsmarty->assign("print_data", $print_data);
        $this->tsmarty->assign("tgl", $tgl);
        $this->tsmarty->assign("instansi", $instansi);
        $this->tsmarty->assign("sum_indicator", $sum_indicator);
        $this->tsmarty->assign("sum_variabel", $sum_variabel);
        $this->tsmarty->assign("sum_sub_variabel", $sum_sub_variabel);
        $this->tsmarty->assign("sum_sub_sub_variabel", $sum_sub_sub_variabel);
        // $this->tsmarty->assign("rsid", $rsid);


        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // OUTPUT
        parent::display();
    }

    public function add_save()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");

        // GET INSTANSI CD
        $ba_id = $this->_get_id();
        $hari = trim(strip_tags($this->input->post('hari', TRUE)));
        $tanggal = trim(strip_tags($this->input->post('tanggal', TRUE)));
        $bulan = trim(strip_tags($this->input->post('bulan', TRUE)));
        $nomor = trim(strip_tags($this->input->post('nomor', TRUE)));
        $judul = trim(strip_tags($this->input->post('judul', TRUE)));
        $tahun = trim(strip_tags($this->input->post('tahun', TRUE)));
        $instansi_cd = $this->com_user['instansi_cd'];
        $print_data = trim(strip_tags($this->input->post('print_data', TRUE)));
        $sum_indicator = trim(strip_tags($this->input->post('sum_indicator', TRUE)));
        $sum_variabel = trim(strip_tags($this->input->post('sum_variabel', TRUE)));
        $sum_sub_variabel = trim(strip_tags($this->input->post('sum_sub_variabel', TRUE)));
        $sum_sub_sub_variabel = trim(strip_tags($this->input->post('sum_sub_sub_variabel', TRUE)));

        // GET URUSAN
        $get_urusan = $this->M_urusan_pg->get_urusan_by_instansi($instansi_cd);
        $urusan_id = array();
        foreach ($get_urusan as $result) {
            array_push(
                $urusan_id,
                $result['urusan_id']
            );
        }
        $arr = $urusan_id;
        $data_id = implode(', ', $arr);

        $param = array($instansi_cd,  $tahun);
        $rsid = $this->M_create_ba->get_indicator_by_instansi($param, $data_id);
        $params = array(
            "ba_id" => $ba_id,
            "ba_judul" => $judul,
            "ba_nomor" => $nomor,
            "ba_day" => $hari,
            "ba_date" => $tanggal,
            "ba_month" => $bulan,
            "ba_year" => $tahun,
            "ba_upload" => NULL,
            "instansi_cd" => $instansi_cd,
            "info_jumlah_data" => $print_data,
            "mdb_name" => $this->com_user['user_alias'],
            "mdb" => $this->com_user['user_id'],
            "mdd" => date("Y-m-d H:i:s"),
            "sum_indicator" => $sum_indicator,
            "sum_variabel" => $sum_variabel,
            "sum_sub_variabel" => $sum_sub_variabel,
            "sum_sub_sub_variabel" => $sum_sub_sub_variabel
        );

        $data_insert = array();
        foreach ($rsid as $result) {
            array_push($data_insert, array(
                'ba_id' => $ba_id,
                'data_id' => $result['data_id'],
                'detail_id' => $result['detail_id']

            ));
        }

        //UPDATE TBL INDIKATOR DATA
        // foreach ($rsid as $result) {
        //     $id = $result['data_id'];

        //     $this->M_indicator_data->update_status_ba(array($id, $tahun));
        // }

        // $data_update = array();
        // foreach ($rsid as $result) {
        //     array_push($data_update, array(
        //         'data_id' => $result['data_id'],
        //         'ba_status' => "yes"
        //     ));
        // }

        // $this->db->where('year', $tahun);
        // $this->db->update_batch('trx_indicator_data', $data_update, 'data_id');


        //INSERT TABEL BERITA ACARA DAN DETAIL BA
        $this->M_create_ba_pg->insert($params);
        $this->M_create_ba_pg->insert_detail($data_insert);

        //NOTIF SUKSES & REDIRECT
        $this->tnotification->sent_notification("success",  "draft berita acara berhasil disimpan");
        redirect("admin/berita_acara_statistik/view/");
    }
}
