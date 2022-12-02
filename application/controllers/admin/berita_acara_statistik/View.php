<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// LOAD BASE CLASS IF NEEDED
require_once(APPPATH . 'controllers/base/UboldBase.php');

class View extends OperatorBase
{
    // CONSTRUCTOR
    public function __construct()
    {
        parent::__construct();

        $this->load->helper(array('form', 'url'));
        // LOAD MODEL

        $this->load->model('admin/berita_acara/M_create_ba');
        $this->load->model('admin/berita_acara/M_create_ba_pg');
        $this->load->model('admin/statistik/M_indicator_data');
        $this->load->model('settings/M_user');
        $this->load->model('admin/master/M_instansi');
        // LOAD LIBRARY
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    public function index()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/berita_acara_statistik/view.html");

        // GET INSTANSI CD
        $instansi_cd = $this->com_user['instansi_cd'];
        $rsid = $this->M_create_ba->get_berita_acara_all($instansi_cd);

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


        // GET INDICATOR
        $param = array($instansi_cd,  $tahun);
        $rsid = $this->M_create_ba_pg->get_berita_acara($param);
        $rincian = $this->M_create_ba_pg->get_berita_acara($param);
        $nomor = 1;
        // print_r($rsid);

        // ASSIGN DATA
        $this->tsmarty->assign("option_years", array_reverse($option_years));
        $this->tsmarty->assign("option_year", $option_year);
        $this->tsmarty->assign("tahun", $tahun);
        $this->tsmarty->assign("nomor", $nomor);
        $this->tsmarty->assign("rsid", $rsid);
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
        $url = "admin/berita_acara_statistik/view";
        redirect($url);
    }
    public function upload($ba_id)
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/berita_acara_statistik/upload.html");
        // GET INSTANSI CD FROM SESSION
        $instansi_cd = $this->com_user['instansi_cd'];

        // ASSIGN
        $this->tsmarty->assign("instansi_cd", $instansi_cd);
        $this->tsmarty->assign("ba_id", $ba_id);

        // OUTPUT
        parent::display();
    }

    public function upload_process()
    {
        // SET PAGE RULES
        $this->_set_page_rule("C");
        // LOAD LIBRARY
        $this->load->library('tupload');
        // NOTIFICATION RULES
        $this->tnotification->set_rules('instansi_cd', 'ID instansi', 'trim|max_length[100]');

        $ba_id = trim(strip_tags($this->input->post('ba_id', TRUE)));

        // CHECK NOTIFICATION
        if ($this->tnotification->run() !== FALSE) {

            // CHECK FILE POST EMPTY OR NOT
            if (empty($_FILES['template']['tmp_name'])) {

                // NOTIFICATION
                $this->tnotification->sent_notification("error", "File unggah tidak boleh kosong!");
                // REDIRECT
                redirect("admin/statistik/indicator/upload");
            } else {

                // SET FILE NAME
                $instansi_cd = trim(strip_tags($this->input->post('instansi_cd', TRUE)));
                $data_instansi = $this->M_instansi->get_detail_instansi(array($instansi_cd));
                $nama_instansi =  $data_instansi['instansi_name'];

                $path = 'resource/doc/imports/berita acara/';
                $file_name =  $ba_id . "_" . $nama_instansi . "_" . $_FILES['template']['name'];
                $path_file = $path . $file_name;

                // UPLOAD CONFIG
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'pdf';
                $config['file_name'] = $file_name;
                $config['overwrite'] = TRUE;
                // SET CONFIG
                $this->tupload->initialize($config);
                // UPLOAD TO FTP
                if (!$this->tupload->do_upload('template')) {
                    $this->tnotification->sent_notification("error", $this->tupload->display_errors());
                    // REDIRECT
                    redirect("admin/berita_acara_statistik/view/index");
                } else {
                    // Jika Suskses
                    $param = array(
                        'ba_upload' => $file_name,
                        'ba_upload_date' => date("Y-m-d H:i:s"),
                        'ba_status' => 'pending'
                    );
                    $where = array(
                        'ba_id' => $ba_id
                    );
                    //update tabel ba
                    $this->M_create_ba_pg->update_ba($param, $where);
                    $this->tnotification->sent_notification("success",  "unggah berita acara berhasil !");
                    redirect("admin/berita_acara_statistik/view/index");
                }
            }
        }
    }

    public function edit($ba_id)
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/berita_acara_statistik/edit.html");

        // GET INSTANSI CD
        $instansi_cd = $this->com_user['instansi_cd'];
        $rsid = $this->M_create_ba_pg->get_berita_acara_by_ba_id(array($instansi_cd, $ba_id));

        $tahun = trim(strip_tags($this->input->post('tahun', TRUE)));
        $print_data = trim(strip_tags($this->input->post('print_data', TRUE)));
        $sum_indicator = trim(strip_tags($this->input->post('sum_indicator', TRUE)));
        $sum_variabel = trim(strip_tags($this->input->post('sum_variabel', TRUE)));
        $sum_sub_variabel = trim(strip_tags($this->input->post('sum_sub_variabel', TRUE)));
        $sum_sub_sub_variabel = trim(strip_tags($this->input->post('sum_sub_sub_variabel', TRUE)));
        $data_detail = $this->input->post('data_detail[]');

        // print_r("<pre>");
        // print_r($rsid);
        // print_r("</pre>");

        // die;
        $tgl = array();
        for ($i = 1; $i <= 31; $i++) {
            array_push($tgl, $i);
        }
        $instansi = $this->M_instansi->get_detail_instansi($instansi_cd);


        // ASSIGN DATA
        $this->tsmarty->assign("rsid", $rsid);
        $this->tsmarty->assign("tahun", $tahun);
        $this->tsmarty->assign("print_data", $print_data);
        $this->tsmarty->assign("tgl", $tgl);
        $this->tsmarty->assign("instansi", $instansi);
        $this->tsmarty->assign("sum_indicator", $sum_indicator);
        $this->tsmarty->assign("sum_variabel", $sum_variabel);
        $this->tsmarty->assign("sum_sub_variabel", $sum_sub_variabel);
        $this->tsmarty->assign("sum_sub_sub_variabel", $sum_sub_sub_variabel);
        $this->tsmarty->assign("data_detail", $data_detail);

        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // OUTPUT
        parent::display();
    }
    public function edit_save()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");

        // GET INSTANSI CD
        $ba_id = trim(strip_tags($this->input->post('ba_id', TRUE)));
        $hari = trim(strip_tags($this->input->post('hari', TRUE)));
        $tanggal = trim(strip_tags($this->input->post('tanggal', TRUE)));
        $bulan = trim(strip_tags($this->input->post('bulan', TRUE)));
        $nomor = trim(strip_tags($this->input->post('nomor', TRUE)));
        $judul = trim(strip_tags($this->input->post('judul', TRUE)));

        // print_r($ba_id);
        // print_r($judul);
        // print_r($bulan);
        // print_r($hari);
        // print_r($tanggal);
        // print_r($nomor);
        // die;

        $params = array(
            'ba_judul' => $judul,
            'ba_month' => $bulan,
            'ba_day' => $hari,
            'ba_date' => $tanggal,
            'ba_nomor' => $nomor
        );
        $where = array(
            'ba_id' => $ba_id
        );


        $this->M_create_ba_pg->update_ba($params, $where);

        $this->tnotification->sent_notification("success",  "Data berhasil diubah");
        redirect("admin/berita_acara_statistik/view/");
    }
    public function list()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/berita_acara_statistik/list.html");

        // GET INSTANSI CD
        $instansi_cd = $this->com_user['instansi_cd'];
        $rsid = $this->M_create_ba_pg->get_berita_acara_all($instansi_cd);

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

        // GET INDICATOR
        $param = array($instansi_cd,  $tahun);
        $rsid = $this->M_create_ba_pg->get_indicator_by_instansi($param);
        $sum_data = $this->M_create_ba_pg->get_sum_data_indicator($param);

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
}
