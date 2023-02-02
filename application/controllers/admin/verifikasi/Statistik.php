<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// LOAD BASE CLASS IF NEEDED
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Statistik extends OperatorBase
{

    // CONSTRUCTOR
    public function __construct()
    {
        parent::__construct();
        // LOAD MODEL
        $this->load->model('admin/master/M_instansi_pg');
        $this->load->model('admin/statistik/M_urusan_pg');
        $this->load->model('admin/statistik/M_indicator_pg');
        $this->load->model('admin/statistik/M_indicator_data_pg');
        $this->load->model('admin/statistik/M_indicator_data_detail_pg');
        $this->load->model('admin/statistik/M_error_pg');
        $this->load->model('admin/statistik/M_error_verifikasi_pg');
        //$this->load->model('admin/statistik/M_indicator_privileges_pg');
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
        $this->tsmarty->assign("template_content", "admin/verifikasi/statistik/index.html");

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
        $rs_id = $this->M_instansi_pg->get_datas_all();
        foreach ($rs_id as $key => $value) {
            // GET COUNTING DATA
            $instansi_cd = $value['instansi_cd'];
            $param = array($instansi_cd, "%", $instansi_cd, "%",  $tahun);
            $data = $this->M_indicator_data_pg->get_count_by_instansi($param);

            // PARSING TO ARRAY DATA
            $rs_id[$key]['tot']         = $data['total'];
            $rs_id[$key]['tot_fill']    = $data['tot_pending'] + $data['tot_reject'] + $data['tot_approve'];
            $rs_id[$key]['tot_pending'] = $data['tot_pending'];
            $rs_id[$key]['tot_reject']  = $data['tot_reject'];
            $rs_id[$key]['tot_approve'] = $data['tot_approve']; // + 
            $rs_id[$key]['tot_min']     = $data['total'] - ($data['tot_pending'] + $data['tot_reject'] + $data['tot_approve']);
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


    public function download_laporan_instansi()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");

        // GET SESSION SEARCH
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id  = empty($search['indicator_id']) ? "%" : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // GET DATA FOR TABLE
        $rs_id = $this->M_instansi_pg->get_datas_all();
        // TITLE
        $result[0]['instansi_name'] = "<b>Nama Instansi</b>";
        $result[0]['tot']           = "<b>Total</b>";
        $result[0]['tot_min']       = "<b>Belum Terisi</b>";
        $result[0]['tot_fill']      = "<b>Terisi</b>";
        $result[0]['tot_approve']   = "<b>Diterima</b>";
        $result[0]['tot_pending']   = "<b>Menunggu</b>";
        $result[0]['tot_reject']    = "<b>Ditolak</b>";


        $number = 1;
        foreach ($rs_id as $key => $value) {
            // GET COUNTING DATA

            $instansi_cd = $value['instansi_cd'];
            $param = array($instansi_cd, "%", $instansi_cd, "%",  $tahun);
            $data = $this->M_indicator_data_pg->get_count_by_instansi($param);
            $total          = empty($data['total'])         ? 0 : $data['total'];
            $tot_fill       = empty($data['tot_fill'])      ? 0 : $data['tot_pending'] + $data['tot_reject'] + $data['tot_approve'];;
            $tot_approve    = empty($data['tot_approve'])   ? 0 : $data['tot_approve'];
            $tot_pending    = empty($data['tot_pending'])   ? 0 : $data['tot_pending'];
            $tot_reject     = empty($data['tot_reject'])    ? 0 : $data['tot_reject'];
            $tot_min        =  $total -  $tot_fill;

            // PARSING TO ARRAY DATA
            $result[$number]['instansi_name']   = $value['instansi_name'];
            $result[$number]['tot']             = $total;
            $result[$number]['tot_min']         = $tot_min;
            $result[$number]['tot_fill']        = $tot_fill;
            $result[$number]['tot_approve']     = $tot_approve;
            $result[$number]['tot_pending']     = $tot_pending;
            $result[$number]['tot_reject']      = $tot_reject;


            $number++;
        }

        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($result);
        $filename =  "Laporan Instansi Tahun " . $tahun . ".xlsx";
        $xlsx->downloadAs($filename);
    }

    public function download_data_instansi($instansi_cd = "")
    {
        //GET INSTANSI DARI SESSION
        // $instansi_cd = $this->com_user['instansi_cd'];
        $instansi =  $this->M_instansi_pg->get_detail_instansi($instansi_cd);

        // // CEK URUSAN, JIKA TIDAK ADA DIREDIRECT   
        if (empty($instansi)) redirect("admin/statistik/indicator");

        // GET SESSION SEARCH & ASSIGN
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id_default = "%";
        $indicator_id  = empty($search['indicator_id']) ? $indicator_id_default : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // SET LOOP YEAR FOR TABLE
        $table_year = $tahun;
        $table_year_min = 4;
        $table_years = array();
        for ($i = ($table_year -  $table_year_min); $i <= $table_year; $i++) {
            array_push($table_years, $i);
        }

        // GET DATA INDICATOR TANPA PRIV
        $rs_id = $this->M_indicator_pg->get_indicator_all_by_instansi(array($instansi_cd));

        // SET DATE
        $result = array();

        // TITLE
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_name'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
        $result[0]['data_unit'] = "<b>Satuan</b>";
        // LOOP
        for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
            $result[0][$i] = "<b>" . $i . "</b>";
        }
        $result[0]['data_st'] = "<b>Sifat Data Tahun Terakhir</b>";
        $result[0]['zzz'] = "<b>Sumber Data</b>";

        // LOOP DATA
        $number = 1;
        foreach ($rs_id as $key => $value) {
            // INSERT TO ARRAY & CHECK IF indicator
            $bold_start = "";
            $bold_end   = "";
            if ($value['data_type'] == 'indicator') {
                $bold_start = "<b>";
                $bold_end   = "</b>";
            }

            // INSERT DATA TO ARRAY
            $result[$number]['data_id']     = $bold_start . $value['data_id'] . $bold_end;
            $result[$number]['data_name']   = $bold_start . $value['data_name'] . $bold_end;
            $result[$number]['data_unit']   = $bold_start . $value['data_unit'] . $bold_end;

            // LOOP DATA FOR GET DATA IN YEAR
            for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
                // PARSING DATA
                $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $i));
                $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
                $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];

                // INSERT TO ARRAY
                $result[$number][$i] =  $bold_start . $nilai . $bold_end;

                // IF TAHUN = SAMA
                if ($i == $tahun) {
                    if ($data_st == 'tetap')
                        $data_st = 'Tetap';
                    elseif ($data_st == 'tidakada')
                        $data_st = 'Tidak Ada';

                    // INSERT TO ARRAY
                    $result[$number]['data_st'] = $bold_start . $data_st . $bold_end;
                }
            }
            // INSERT TO ARRAY
            $result[$number]['zzz'] = $bold_start . $value['instansi_name'] . $bold_end;

            // ADD 
            $number++;
        }

        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($result);
        $filename =  "Laporan " . $instansi['instansi_name'] . ".xlsx";
        $xlsx->downloadAs($filename);
    }

    public function download_data_all()
    {
        // GET SESSION SEARCH & ASSIGN
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id_default = "%";
        $indicator_id  = empty($search['indicator_id']) ? $indicator_id_default : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // SET LOOP YEAR FOR TABLE
        $table_year = $tahun;
        $table_year_min = 1;
        $table_years = array();
        for ($i = ($table_year -  $table_year_min); $i <= $table_year; $i++) {
            array_push($table_years, $i);
        }

        // GET DATA INDICATOR TANPA PRIV
        // $rs_id = $this->M_indicator_pg->get_indicator_all();
        $rs_id = $this->M_indicator_pg->get_indicator_test_temp();

        // SET DATE
        $result = array();

        // TITLE
        $result[0]['data_name'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_unit'] = "<b>Satuan</b>";
        
       
        // LOOP
        for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
            $result[0][$i] = "<b>" . $i . "</b>";
        }
        $result[0]['data_st'] = "<b>Sifat Data Tahun Terakhir</b>";
        $result[0]['xxx'] = "<b>Bidang Urusan</b>";
        $result[0]['zzz'] = "<b>Sumber Data</b>";

        // LOOP DATA
        $number = 1;
        foreach ($rs_id as $key => $value) {
            // INSERT TO ARRAY & CHECK IF indicator
            $bold_start = "";
            $bold_end   = "";
            if ($value['data_type'] == 'indicator') {
                $bold_start = "<b>";
                $bold_end   = "</b>";
            }

            // INSERT DATA TO ARRAY
            $result[$number]['data_name']   = $bold_start . $value['data_name'] . $bold_end;
            $result[$number]['data_id']     = $bold_start . $value['data_id'] . $bold_end;
            $result[$number]['data_unit']   = $bold_start . $value['data_unit'] . $bold_end;

            // LOOP DATA FOR GET DATA IN YEAR
            for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
                // PARSING DATA
                $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $i));
                $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
                $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];

                // INSERT TO ARRAY
                $result[$number][$i] =  $bold_start . $nilai . $bold_end;

                // IF TAHUN = SAMA
                if ($i == $tahun) {
                    if ($data_st == 'tetap')
                        $data_st = 'Tetap';
                    elseif ($data_st == 'tidakada')
                        $data_st = 'Tidak Ada';

                    // INSERT TO ARRAY
                    $result[$number]['data_st'] = $bold_start . $data_st . $bold_end;
                }
            }
            // INSERT TO ARRAY
            $result[$number]['xxx'] = $bold_start . $value['urusan_name'] . $bold_end;
            $result[$number]['zzz'] = $bold_start . $value['instansi_name'] . $bold_end;

            // ADD 
            $number++;
        }

        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($result);
        $filename =  "Daftar Data Semua OPD.xlsx";
        $xlsx->downloadAs($filename);
    }
    
    public function download_template_verifikasi_all()
    {
        // GET SESSION SEARCH & ASSIGN
        $urusan_id = trim(strip_tags($this->input->post('urusan_id', TRUE)));
        $instansi_cd = trim(strip_tags($this->input->post('instansi_cd', TRUE)));

        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id_default = "%";
        $indicator_id  = empty($search['indicator_id']) ? $indicator_id_default : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // SET LOOP YEAR FOR TABLE
        $table_year = $tahun;
        $table_year_min = 2;
        $table_years = array();
        for ($i = ($table_year -  $table_year_min); $i <= $table_year; $i++) {
            array_push($table_years, $i);
        }

        // GET DATA INDICATOR BY INSTANSI CODE & URUSAN ID
        $rs_id = $this->M_indicator_pg->get_indicator_test_temp();

        // SET DATE
        $result = array();

        // TITLE
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_name'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
        $result[0]['data_unit'] = "<b>Satuan</b>";
        // LOOP
        for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
           $result[0][$i] = "<b>" . $i . "</b>";
        }
        $result[0]['data_st'] = "<b>Sifat Data Tahun Terakhir</b>";
        $result[0]['submission_st'] = "<b>Status Pengajuan (Diisi angka saja: 0=Ditolak 1=Menunggu 2=Diterima)</b>";
        $result[0]['verify_comment'] = "<b>Catatan</b>";
        $result[0]['year'] = "<b>Tahun</b>";
        $result[0]['data_type'] = "<b>Indikator/Variabel</b>";
        $result[0]['rumus_type'] = "<b>Rumus</b>";
        $result[0]['rumus_detail'] = "<b>Detail Rumus</b>";
        $result[0]['instansi_name'] = "<b>Nama Instansi / OPD</b>";
        $result[0]['urusan_id'] = "<b>Kode Urusan</b>";

        // LOOP DATA
        $number = 1;
        foreach ($rs_id as $key => $value) {

            // INSERT TO ARRAY & CHECK IF indicator
            $bold_start = "";
            $bold_end   = "";
            if ($value['data_type'] == 'indicator') {
                $bold_start = "<b>";
                $bold_end   = "</b>";
            }
             
            // INSERT DATA TO ARRAY
            $result[$number]['data_id']     = $bold_start . $value['data_id'] . $bold_end;
            $result[$number]['data_name']   = $bold_start . $value['data_name'] . $bold_end;
            $result[$number]['data_unit']   = $bold_start . $value['data_unit'] . $bold_end;


            // LOOP DATA FOR GET DATA IN YEAR
            for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
                // PARSING DATA
               $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $i));
               $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
               $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];
               $verify_comment = (!isset($data['verify_comment']) || empty($data['verify_comment'])) ? '' : $data['verify_comment'];
               $submission_st = (!isset($data['submission_st']) || empty($data['submission_st'])) ? '' : $data['submission_st'];
               if ($submission_st == 'approved')
                   $submission_st = '2';
               elseif ($submission_st == 'empty')
                   $submission_st = 'kosong';
               elseif ($submission_st == 'pending')
                   $submission_st = '1';
               elseif ($submission_st == 'rejected')
                   $submission_st = '0';
               else
                   $submission_st = '';
               
               // INSERT TO ARRAY
               $result[$number][$i] =  $bold_start . $nilai . $bold_end;

               // IF TAHUN = SAMA
               if ($i == $tahun) {
                   if ($data_st == 'tetap')
                       $data_st = 'Tetap';
                   elseif ($data_st == 'tidakada')
                       $data_st = 'Tidak Ada';

                   // INSERT TO ARRAY
                   $result[$number]['data_st'] = $bold_start . $data_st . $bold_end;
               }

            }
           
            // INSERT TO ARRAY
            $result[$number]['submission_st'] =  $bold_start . $submission_st . $bold_end;
            $result[$number]['verify_comment'] =  $bold_start . $verify_comment . $bold_end;
            $result[$number]['year']   = $bold_start . $tahun . $bold_end;
            $result[$number]['data_type'] = $bold_start . $value['data_type'] . $bold_end;
            $result[$number]['rumus_type'] = $bold_start . $value['rumus_type'] . $bold_end;
            $result[$number]['data_type'] = $bold_start . $value['data_type'] . $bold_end;
            $result[$number]['rumus_detail'] = $bold_start . $value['rumus_detail'] . $bold_end;
            $result[$number]['instansi_name'] = $bold_start . $value['instansi_name'] . $bold_end;
            $urusan_id = $bold_start . "'" . $value['urusan_id'] . $bold_end;
            $result[$number]['urusan_id'] = $bold_start . $urusan_id . $bold_end;

            // ADD 
            $number++;
        }

       //  print_r($result);

       // DONWLOAD EXCEL WITH PARAMS 
       $xlsx = new SimpleXLSXGen();
       $xlsx->addSheet($result);
       $filename =  "Template_Verifikasi_" . $tahun. ".xlsx";
       $xlsx->downloadAs($filename);
    }

    public function download_template_verifikasi_by_urusan()
    {
        // GET SESSION SEARCH & ASSIGN
        $urusan_id = trim(strip_tags($this->input->post('urusan_id', TRUE)));
        $instansi_cd = trim(strip_tags($this->input->post('instansi_cd', TRUE)));

        
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id_default = "%";
        $indicator_id  = empty($search['indicator_id']) ? $indicator_id_default : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // SET LOOP YEAR FOR TABLE
        $table_year = $tahun;
        $table_year_min = 2;
        $table_years = array();
        for ($i = ($table_year -  $table_year_min); $i <= $table_year; $i++) {
            array_push($table_years, $i);
        }

        $params = array(
            "urusan_id" => $urusan_id,
            "instansi_cd" => $instansi_cd
        );

        // GET DATA INDICATOR BY INSTANSI CODE & URUSAN ID
        $rs_id = $this->M_indicator_pg->get_indicator_by_urusan_instansi($params);

        // SET DATE
        $result = array();

        // TITLE
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_name'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
        $result[0]['data_unit'] = "<b>Satuan</b>";
        // LOOP
        for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
           $result[0][$i] = "<b>" . $i . "</b>";
        }
        $result[0]['data_st'] = "<b>Sifat Data Tahun Terakhir</b>";
        $result[0]['submission_st'] = "<b>Status Pengajuan (Diisi angka saja: 0=Ditolak 1=Menunggu 2=Diterima)</b>";
        $result[0]['verify_comment'] = "<b>Catatan</b>";
        $result[0]['year'] = "<b>Tahun</b>";
        $result[0]['data_type'] = "<b>Indikator/Variabel</b>";
        $result[0]['rumus_type'] = "<b>Rumus</b>";
        $result[0]['rumus_detail'] = "<b>Detail Rumus</b>";
        $result[0]['instansi_name'] = "<b>Nama Instansi / OPD</b>";
        $result[0]['urusan_id'] = "<b>Kode Urusan</b>";

        // LOOP DATA
        $number = 1;
        foreach ($rs_id as $key => $value) {

            // INSERT TO ARRAY & CHECK IF indicator
            $bold_start = "";
            $bold_end   = "";
            if ($value['data_type'] == 'indicator') {
                $bold_start = "<b>";
                $bold_end   = "</b>";
            }
             
            // INSERT DATA TO ARRAY
            $result[$number]['data_id']     = $bold_start . $value['data_id'] . $bold_end;
            $result[$number]['data_name']   = $bold_start . $value['data_name'] . $bold_end;
            $result[$number]['data_unit']   = $bold_start . $value['data_unit'] . $bold_end;


            // LOOP DATA FOR GET DATA IN YEAR
            for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
                // PARSING DATA
               $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $i));
               $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
               $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];
               $verify_comment = (!isset($data['verify_comment']) || empty($data['verify_comment'])) ? '' : $data['verify_comment'];
               $submission_st = (!isset($data['submission_st']) || empty($data['submission_st'])) ? '' : $data['submission_st'];
               if ($submission_st == 'approved')
                   $submission_st = '2';
               elseif ($submission_st == 'empty')
                   $submission_st = 'kosong';
               elseif ($submission_st == 'pending')
                   $submission_st = '1';
               elseif ($submission_st == 'rejected')
                   $submission_st = '0';
               else
                   $submission_st = '';
               
               // INSERT TO ARRAY
               $result[$number][$i] =  $bold_start . $nilai . $bold_end;

               // IF TAHUN = SAMA
               if ($i == $tahun) {
                   if ($data_st == 'tetap')
                       $data_st = 'Tetap';
                   elseif ($data_st == 'tidakada')
                       $data_st = 'Tidak Ada';

                   // INSERT TO ARRAY
                   $result[$number]['data_st'] = $bold_start . $data_st . $bold_end;
               }

            }
           
            // INSERT TO ARRAY
            $result[$number]['submission_st'] =  $bold_start . $submission_st . $bold_end;
            $result[$number]['verify_comment'] =  $bold_start . $verify_comment . $bold_end;
            $result[$number]['year']   = $bold_start . $tahun . $bold_end;
            $result[$number]['data_type'] = $bold_start . $value['data_type'] . $bold_end;
            $result[$number]['rumus_type'] = $bold_start . $value['rumus_type'] . $bold_end;
            $result[$number]['data_type'] = $bold_start . $value['data_type'] . $bold_end;
            $result[$number]['rumus_detail'] = $bold_start . $value['rumus_detail'] . $bold_end;
            $result[$number]['instansi_name'] = $bold_start . $value['instansi_name'] . $bold_end;
            $urusan_id = $bold_start . "'" . $value['urusan_id'] . $bold_end;
            $result[$number]['urusan_id'] = $bold_start . $urusan_id . $bold_end;

            // ADD 
            $number++;
        }

       // DONWLOAD EXCEL WITH PARAMS         
       $xlsx = new SimpleXLSXGen();
       $xlsx->addSheet($result);
       $filename =  "Template_Verifikasi_Bidang ".$value['urusan_name']."_".$value['instansi_name']."_".$tahun.".xlsx";
       $xlsx->downloadAs($filename);
    }

    public function download_template_data()
    {// GET SESSION SEARCH & ASSIGN
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id_default = "%";
        $indicator_id  = empty($search['indicator_id']) ? $indicator_id_default : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // GET DATA INDICATOR TANPA PRIV
        // $rs_id = $this->M_indicator_pg->get_indicator_active();
        $rs_id = $this->M_indicator_pg->get_indicator_test_temp();

        // SET DATE
        $result = array();

        // TITLE
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_name'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
        $result[0]['data_unit'] = "<b>Satuan</b>";
        $result[0]['year'] = "<b>Tahun</b>";
        $result[0]['value'] = "<b>Nilai</b>";
        $result[0]['data_st'] = "<b>Sifat Data Tahun Terakhir (Diisi angka saja: 0=tidak ada data; 1=TW-1; 2=TW-2; 3=TW-3; 4=TW-4; 5=Tetap)</b>";
        $result[0]['submission_st'] = "<b>Status Pengajuan (Diisi angka saja: 0=Ditolak 1=Menunggu 2=Diterima)</b>";
        $result[0]['verify_comment'] = "<b>Catatan</b>";
        $result[0]['data_type'] = "<b>Indikator/Variabel</b>";
        $result[0]['rumus_type'] = "<b>Rumus</b>";
        $result[0]['rumus_detail'] = "<b>Detail Rumus</b>";
        $result[0]['instansi_name'] = "<b>Nama Instansi / OPD</b>";
        $result[0]['urusan_id'] = "<b>Kode Urusan</b>";

        // LOOP DATA
        $number = 1;
        foreach ($rs_id as $key => $value) {

            // INSERT TO ARRAY & CHECK IF indicator
            $bold_start = "";
            $bold_end   = "";
            if ($value['data_type'] == 'indicator') {
                $bold_start = "<b>";
                $bold_end   = "</b>";
            }
             
            // INSERT DATA TO ARRAY
            $result[$number]['data_id']     = $bold_start . $value['data_id'] . $bold_end;
            $result[$number]['data_name']   = $bold_start . $value['data_name'] . $bold_end;
            $result[$number]['data_unit']   = $bold_start . $value['data_unit'] . $bold_end;


            // LOOP DATA FOR GET DATA IN YEAR
            // for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
                // PARSING DATA
               $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $tahun));
              
               $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
               $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];
                
                if ( $data_st == "tidakada" || $nilai == "n/a") {
                    $data_st = "0";
                } else if ($data_st == "TW-1") {
                     $data_st = "1";
                } else if ($data_st == "TW-2"){
                    $data_st = "2";
                } else if ($data_st == "TW-3"){
                    $data_st = "3";
                } else if ($data_st == "TW-4"){
                    $data_st = "4";
                } else if ($data_st == "tetap"){
                    $data_st = "5";
                } else {
                    $data_st = "";
                }
                    
               $verify_comment = (!isset($data['verify_comment']) || empty($data['verify_comment'])) ? '' : $data['verify_comment'];
               $submission_st = (!isset($data['submission_st']) || empty($data['submission_st'])) ? '' : $data['submission_st'];

               // INSERT TO ARRAY
                $result[$number]['year']   = $bold_start . $search['tahun'] . $bold_end;
                $result[$number]['value'] =  $bold_start . $nilai . $bold_end;
                $result[$number]['data_st'] = $bold_start . $data_st . $bold_end;
           
            // INSERT TO ARRAY
            $result[$number]['submission_st'] =  $bold_start . $submission_st . $bold_end;
            $result[$number]['verify_comment'] =  $bold_start . $verify_comment . $bold_end;
            $result[$number]['data_type'] = $bold_start . $value['data_type'] . $bold_end;
            $result[$number]['rumus_type'] = $bold_start . $value['rumus_type'] . $bold_end;
            $result[$number]['rumus_detail'] = $bold_start . $value['rumus_detail'] . $bold_end;
            $result[$number]['instansi_name'] = $bold_start . $value['instansi_name'] . $bold_end;
            $urusan_id = $bold_start . "'" . $value['urusan_id'] . $bold_end;
            $result[$number]['urusan_id'] = $bold_start . $urusan_id . $bold_end;

            // ADD 
            $number++;
        }

       // DONWLOAD EXCEL WITH PARAMS        
       $xlsx = new SimpleXLSXGen(); 
       $xlsx->addSheet($result);
       $filename =  "Template_Data_" . $tahun. ".xlsx";
       $xlsx->downloadAs($filename);
    }

    public function urusan($instansi_cd)
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/verifikasi/statistik/urusan.html");

        // GET DATA INSTANSI & IF EMPTY REDIRECT TO INDEX
        $instansi = $this->M_instansi_pg->get_detail_instansi(array($instansi_cd));
        if (empty($instansi)) redirect("admin/verifikasi/statistik");

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

        // GET DATA FOR TABLE WITHOUT PRIVILEGES
        $rs_id = $this->M_urusan_pg->get_all_by_instansi(array($instansi_cd, $instansi_cd,));

        $urusan =[];  // MENGGUNAKAN ARRAY TEMP
        foreach ($rs_id as $key => $value) {

            if (!isset($urusan[$value['urusan_id']])) {

                // GET COUNTING DATA
                $param = array($instansi_cd, $value['urusan_id'] . "%", $instansi_cd, $value['urusan_id'] . "%",  $tahun);
                // print_r($value);
                $data = $this->M_indicator_data_pg->get_count_by_instansi($param);

                // PARSING TO ARRAY DATA
                $rs_id[$key]['tot']         = $data['total'];
                $rs_id[$key]['tot_fill']    = $data['tot_fill'];
                $rs_id[$key]['tot_pending'] = $data['tot_pending'];
                $rs_id[$key]['tot_reject']  = $data['tot_reject'];
                $rs_id[$key]['tot_approve'] = $data['tot_approve'];
                $rs_id[$key]['tot_min']     = $data['total'] - ($data['tot_pending'] + $data['tot_reject'] + $data['tot_approve']) ;

                $urusan[$value['urusan_id']] = $rs_id[$key];
            }
        }
        $rs_id = $urusan;

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

    public function indicator($instansi_cd = "", $data_id = "")
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/verifikasi/statistik/indicator.html");

        // GET SESSION SEARCH
        $search = $this->session->userdata('search_indicator');
        if (empty($search['indicator_class'])) {
            $indicator_class = "";
        } else {
            $indicator_class = $search['indicator_class'];
        }
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id  = empty($search['indicator_id']) ? "%" : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // GET DATA INSTANSI & IF EMPTY REDIRECT TO INDEX
        $instansi = $this->M_instansi_pg->get_detail_instansi(array($instansi_cd));
        if (empty($instansi)) redirect("admin/verifikasi/statistik");

        // DATA WITHOUT PRIVILEGES
        // GET DETAIL URUSAN & REDIRECT IF EMPTY
        $urusan = $this->M_urusan_pg->get_detail_urusan(array($data_id));
        if (empty($urusan)) redirect("admin/verifikasi/statistik");

        // GET DETAIL AND LIST INDICATOR FOR SEARCH
        $urusan_parent = $this->M_urusan_pg->get_detail_urusan(array($urusan['parent_id']));
        $indicators = $this->M_indicator_pg->get_indicator_by_instansi(array($data_id, $instansi_cd));


        // GET LIST INDICATOR & VARIBLE WITHOUT PRIVILEGES
        $params = array($data_id, $indicator_id, $instansi_cd);
        //print_r($indicator_class);
        // if (
        //     $indicator_class == "class_sdgs" or $indicator_class == "class_rpjmd"
        //     or $indicator_class == "class_ikklppd" or $indicator_class == "class_spm"
        //     or $indicator_class == "class_dda" or $indicator_class == "class_datakudiy"
        //     or $indicator_class == "class_pilahgender"
        // ) {


        //     $rs_id = $this->M_indicator_pg->get_indicator_export_by_params_indicator_class($params, $indicator_class);
        // } else {
        //     $rs_id = $this->M_indicator_pg->get_indicator_export_by_params($params);
        // }

        // CREATE LIST OPTION FOR YEAR
        $option_year = date("Y");
        $option_years = array();
        $option_year_min = 10;
        for ($i = ($option_year -  $option_year_min); $i <= $option_year; $i++) {
            array_push($option_years, $i);
        }

        // CREATE LIST YEAR FOR LOAD LIST DATA
        $table_year = $tahun;
        $table_year_min = 2;
        $table_years = array();
        for ($i = ($table_year -  $table_year_min); $i <= $table_year; $i++) {
            array_push($table_years, $i);
        }

        $params = array($table_years[0], $table_years[1], $table_years[2], $data_id."%", $indicator_id."%", $instansi_cd);
        $rs_id = $this->M_indicator_data_pg->get_data_xxx($params);
        
        // print_r($params);
        // echo "<pre>";
        // print_r($rs_id);
        // die();
        // echo "<pre />";
        // exit();


        // // LOOP LIST AND PUSH DATA value PER YEAR
        // foreach ($rs_id as $key => $value) {
        //     // LOOP YEAR
        //     for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
        //         // GET DETAIL DATA & PARSING
        //         $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $i));
        //         $detail_id = (!isset($data['detail_id'])) ? '' : trim($data['detail_id']);
        //         $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
        //         $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];
        //         $submission_st = (!isset($data['submission_st']) || empty($data['submission_st'])) ? '' : $data['submission_st'];
        //         $verify_comment = (!isset($data['verify_comment']) || empty($data['verify_comment'])) ? '' : $data['verify_comment'];
        //         $mdd = (!isset($data['mdd']) || empty($data['mdd'])) ? '' : $this->tdtm->get_full_date($data['mdd']);
        //         $mdb_name = (!isset($data['mdb_name']) || empty($data['mdb_name'])) ? '' : $data['mdb_name'];
        //         $verify_mdd = (!isset($data['verify_mdd']) || empty($data['verify_mdd'])) ? '' : $this->tdtm->get_full_date($data['verify_mdd']);
        //         $verify_mdb_name = (!isset($data['verify_mdb_name']) || empty($data['verify_mdb_name'])) ? '' : $data['verify_mdb_name'];

        //         // PUSH TO MAIN ARRAY
        //         if ($table_year == $i) {
        //             // ADD HISTORY INDICATOR & VARIABLE DATA, IF YEAR = YEAR SELECTED
        //             $history = $this->M_indicator_data_detail_pg->get_history_by_params_limit(array($value['data_id'], $i));
        //             $rs_id[$key][$i] =  array(
        //                 'detail_id' => $detail_id,
        //                 'value' => $nilai,
        //                 'data_st' => $data_st,
        //                 'submission_st' => $submission_st,
        //                 'verify_comment' => $verify_comment,
        //                 'history' =>  $history,
        //                 'verify_mdd' => $verify_mdd,
        //                 'verify_mdb_name' => $verify_mdb_name,
        //                 'mdd' => $mdd,
        //                 'mdb_name' =>  $mdb_name,
        //             );
        //         } else {
        //             $rs_id[$key][$i] =  array(
        //                 'detail_id' => $detail_id,
        //                 'value' => $nilai,
        //                 'data_st' => $data_st,
        //                 'submission_st' => $submission_st,
        //                 'verify_comment' => $verify_comment,
        //                 'verify_mdd' => $verify_mdd,
        //                 'verify_mdb_name' => $verify_mdb_name,
        //                 'mdd' => $mdd,
        //                 'mdb_name' =>  $mdb_name,
        //             );
        //         }
        //     }
        // }

        // ASSIGN DATA
        $this->tsmarty->assign("data_id", $data_id);
        $this->tsmarty->assign("urusan_id", $data_id);
        $this->tsmarty->assign("urusan", $urusan);
        $this->tsmarty->assign("urusan_parent", $urusan_parent);
        $this->tsmarty->assign("indicators", $indicators);
        $this->tsmarty->assign("instansi_cd", $instansi_cd);
        $this->tsmarty->assign("instansi", $instansi);
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("option_years", array_reverse($option_years));
        $this->tsmarty->assign("option_year", $option_year);
        $this->tsmarty->assign("table_years", $table_years);
        $this->tsmarty->assign("table_year", $table_year);
        $this->tsmarty->assign("year_selected", $tahun);
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
                "indicator_id" => trim(strip_tags($this->input->post('indicator_id', TRUE))),
                "indicator_class" => trim(strip_tags($this->input->post('indicator_class', TRUE))),
            );
            // GET SESSION
            $this->session->set_userdata("search_indicator", $params);
        } else {
            // UNSET SESSION
            $this->session->unset_userdata("search_indicator");
        }
        // REDIRECT
        $urusan_id = trim(strip_tags($this->input->post('urusan_id', TRUE)));
        $instansi_cd = trim(strip_tags($this->input->post('instansi_cd', TRUE)));
        $url = "admin/verifikasi/statistik/indicator/" . $instansi_cd . '/' . $urusan_id;
        redirect($url);
    }

    public function ajukan_process()
    {
    
        // SET PAGE RULES
        $this->_set_page_rule("C");

        // GET DATA POST & PARSING
        $year = trim(strip_tags($this->input->post('year', TRUE)));
        $urusan_id = trim(strip_tags($this->input->post('urusan_id', TRUE)));
        $instansi_cd = trim(strip_tags($this->input->post('instansi_cd', TRUE)));
        $datas =  $this->input->post('datas', TRUE);
        $verify_comments =  $this->input->post('verify_comment', TRUE);
        $detail_ids =  $this->input->post('detail_ids', TRUE);
        $values =  $this->input->post('values', TRUE); 
        $old_values =  $this->input->post('old_values', TRUE); 
        $old_statuses =  $this->input->post('old_statuses', TRUE); 
        $statuses =  $this->input->post('statuses', TRUE); 
        $old_submission_sts =  $this->input->post('old_submission_st', TRUE); 
        $submission_sts =  $this->input->post('submission_st', TRUE); 

        $tot_insert = 0;
        $tot_insert_error = 0;

         // LOOP DATA FOR UPDATE pengajuan
        foreach ($datas as $key => $data) {
            $submission_st = trim(strip_tags($submission_sts[$key]));
            if($submission_st != '') {
                // PARSING DATA
                $detail_id = '';
                if ($detail_ids != ''){
                    $detail_id = trim(strip_tags($detail_ids[$key]));
                }
                $submission_st = trim(strip_tags($submission_sts[$key]));
                $verify_comment = trim(strip_tags($verify_comments[$key]));
                $verify_comment = (!isset($verify_comment) || empty($verify_comment)) ? NULL : $verify_comment;
                $value = trim(strip_tags($values[$key])); 
                $old_value = trim(strip_tags($old_values[$key])); 
                $value = str_replace(",", ".", $value); 
                $old_value = str_replace(",", ".", $old_value); 
                $old_status = trim(strip_tags($old_statuses[$key])); 
                $status = trim(strip_tags($statuses[$key]));

                if ($value == '') {
                    $value = NULL;
                }

                // $detail_id = $this->_get_id();
                
                $params = array(
                    "data_id" => $data,
                    "year" => $year
                );

                // UPDATE INDICATOR DATA
                if ($value == '' ) { 
                    $params = array(
                        "data_id" => $data,
                        "year" => $year,
                        "value" => $value, 
                        "data_st" => $status, 
                        "submission_st" => '-',
                        "detail_id" => $detail_id,
                        "verify_comment" => $verify_comment,
                        "verify_mdb_name" => $this->com_user['user_alias'],
                        "verify_mdb" => $this->com_user['user_id'],
                        "verify_mdd" => date("Y-m-d H:i:s")
                    );
                } else {
                    $params = array(
                        "data_id" => $data,
                        "year" => $year,
                        "value" => $value, 
                        "data_st" => $status, 
                        "submission_st" => $submission_st,
                        "detail_id" => $detail_id,
                        "verify_comment" => $verify_comment,
                        "verify_mdb_name" => $this->com_user['user_alias'],
                        "verify_mdb" => $this->com_user['user_id'],
                        "verify_mdd" => date("Y-m-d H:i:s")
                    );
                }
                
                $where = array(
                    "data_id" => $data,
                    "year" => $year
                );

                if ($submission_st == 'rejected' && $verify_comment == '') {
                    
                    // UPDATE DETAIL HISTORY DATA
                    $this->M_indicator_data_pg->update($params, $where);
                    
                    // INSERT DETAIL DATA
                    $this->M_indicator_data_detail_pg->update($params, $where); 
                    $tot_insert_error++;
                } 
                    
                // UPDATE DETAIL HISTORY DATA
                $this->M_indicator_data_pg->update($params, $where);
                
                // INSERT DETAIL DATA
                $this->M_indicator_data_detail_pg->update($params, $where);
                $tot_insert++;
                
            }
        }
        
        $this->tnotification->sent_notification("success", $tot_insert. " Data berhasil diverifikasi, " . $tot_insert_error. " Data ditolak tanpa catatan");
        redirect("admin/verifikasi/statistik/indicator/" . $instansi_cd . "/" . $urusan_id);
        // REDIRECT
        
    }

    public function search_urusan_process()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");

        // CEK INPUT PROCESS EMPTY OR NOT
        if ($this->input->post('process') == "process") {

            // SET PARAMS
            $params = array(
                "tahun" => trim(strip_tags($this->input->post('tahun', TRUE))),
                "indicator_id" => trim(strip_tags($this->input->post('indicator_id', TRUE))),
                "tahun" => trim(strip_tags($this->input->post('tahun', TRUE))),
            );
            // GET SESSION
            $this->session->set_userdata("search_indicator", $params);
        } else {
            // UNSET SESSION
            $this->session->unset_userdata("search_indicator");
        }
        // REDIRECT
        if (empty(trim(strip_tags($this->input->post('instansi_cd', TRUE)))))
            redirect("admin/verifikasi/statistik");
        else
            redirect("admin/verifikasi/statistik/urusan/" . trim(strip_tags($this->input->post('instansi_cd', TRUE))));
    }

    public function read($tahun, $instansi_cd, $path = "", $file_name = "")
    {
        // LOAD MODEL  
        $this->load->library('SimpleXLSX');

        // READ EXCEL
        $path_file = $path . $file_name;
        
        $xlsx = new SimpleXLSX($path_file);
        // PARSING
        $no = 0;
        $tot_insert = 0;
        $upload_id = $this->_get_id();   

        // LOOP DATA EXCEL
        foreach ($xlsx->rows() as $key => $data) {

            // SKIP FIRST ROW
            if ($no > 0) {
                // PARSING DATA
                $data_id            = (!isset($data[0])) ? '' : trim(strip_tags($data[0]));
                $data_name          = (!isset($data[1])) ? '' : trim(strip_tags($data[1]));
                $data_unit          = (!isset($data[2])) ? '' : trim(strip_tags($data[2]));
                $year               = (!isset($data[3])) ? '' : trim(strip_tags($data[3]));
                $value              = (!isset($data[4])) ? '' : trim(strip_tags($data[4]));
                $data_st            = (!isset($data[5])) ? '' : trim(strip_tags($data[5]));
                $submission_st      = (!isset($data[6])) ? '' : trim(strip_tags($data[6]));
                $verify_comment     = (!isset($data[7])) ? '' : trim(strip_tags($data[7]));
                $verify_comment_opd = (!isset($data[8])) ? '' : trim(strip_tags($data[8]));
                $data_type          = (!isset($data[9])) ? '' : trim(strip_tags($data[9]));
                $rumus_type         = (!isset($data[10])) ? '' : trim(strip_tags($data[9]));
                $rumus_detail       = (!isset($data[11])) ? '' : trim(strip_tags($data[11]));
                $instansi_name      = (!isset($data[12])) ? '' : trim(strip_tags($data[12]));
                $urusan_id          = (!isset($data[13])) ? '' : trim(strip_tags($data[13]));

                // SET ARRAY STATUS
                $list_data = array("", "0", "1", "2", "3", "4", "5");
                // CEK IF EXCEL VALUE NOT IN ARRAY
                if (!in_array(strtolower($data_st), $list_data)) {
                    // SET PARAMS & INSERT DATA IN ERROR LIST
                    $catatan = "Kesalahan status data";
                    $err_params = array(
                        "upload_id" => $upload_id,
                        "upload_line" => $no + 1,
                        "data_id" => $data_id,
                        "year" => $year,
                        // "value_old" => $value,
                        "value" => $value,
                        // "data_st_old" => $data_st,
                        "data_st" => $data_st,
                        "catatan" => $catatan,
                        "mdb_name" => $this->com_user['user_alias'],
                        "mdb" => $this->com_user['user_id'],
                        "mdd" => date("Y-m-d H:i:s")
                    );
                    $this->M_error_pg->insert_detail($err_params);
                }

                // CEK IF VALUE IS NOT EMPTY
                if ($data_st == "0") {
                    $data_st = "tidakada";
                    $value = "n/a";
                } else if ($data_st == "1")
                    $data_st = "TW-1";
                else if ($data_st == "2")
                    $data_st = "TW-2";
                else if ($data_st == "3")
                    $data_st = "TW-3";
                else if ($data_st == "4")
                    $data_st = "TW-4";
                else if ($data_st == "5")
                    $data_st = "tetap";

                // PARSING DATA
                if ($value == "") $value = NULL;
                $detail_id = $this->_get_id();

                // SET PARAMS & DELETE DATA BEFORE INSERT
                $params = array(
                    "data_id" => $data_id,
                    "year" => $year
                );
                $this->M_indicator_data_pg->delete($params);

                if ($value == "") {
                    $submission_status = "-";
                    //print_r($submission_status);
                } else if ($value != "")
                    $submission_status = "pending";

                // SET PARAMS INSERT DATA 
                $params = array(
                    "data_id" => $data_id,
                    "upload_id" => $upload_id,
                    "upload_line" => $no + 1,
                    "year" => $year,
                    "value" => $value,
                    "data_st" => $data_st,
                    "submission_st" => $submission_status,
                    "detail_id" => $detail_id,
                    "mdb_name" => $this->com_user['user_alias'],
                    'mdb' => $this->com_user['user_id'],
                    'mdd' => date('Y-m-d H:i:s')
                );

                // CEK IF INSERT SUCCESS OR NOT
                if ($this->M_indicator_data_pg->insert($params)) {
                    // IF SUCCESS THE INSERT TO DETAIL & ADD COUNT
                    $this->M_indicator_data_detail_pg->insert($params);
                    $tot_insert++;
                }
            }
            // ADD COUNTING
            $no++;
        }

        // SET COUNTING
        $no = $no - 1;
        $tot =  $no;
        $tot_error =  $tot - $tot_insert;

        // SET PARAMS AND INSERT ERROR
        $params = array(
            "upload_id" => $upload_id,
            "instansi_cd" => $instansi_cd,
            "year" => $tahun,
            "path" => $path,
            "file_name" => $file_name,
            "tot" => $tot,
            "tot_error" => $tot_error,
            "tot_insert" => $tot_insert,
            "mdb_name" => $this->com_user['user_alias'],
            "mdb" => $this->com_user['user_id'],
            "mdd" => date("Y-m-d H:i:s")
        );
        $this->M_error_pg->insert($params);

        // SET NOTIFICATION
        $result = "Import berhasil, dengan rincian jumlah data adalah " . $no . " data, data yang disimpan berjumlah " . $tot_insert . " data, dan data yang gagal disimpan berjumlah " . ($no - $tot_insert) . " data";
        $this->tnotification->sent_notification("success",  $result);

        // DEFAULT REDIRECT
        redirect("admin/verifikasi/statistik");
    }

    public function upload()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/verifikasi/statistik/upload.html");

        $rs_id = $this->M_instansi_pg->get_datas_all();

        // ASSIGN
        $this->tsmarty->assign("rs_id", $rs_id);
            
        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
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
        $year = date('Y');

        // CHECK NOTIFICATION
        if ($this->tnotification->run() !== FALSE) {
            // CHECK FILE POST EMPTY OR NOT
            if (empty($_FILES['template']['tmp_name'])) {

                // NOTIFICATION
                $this->tnotification->sent_notification("error", "File unggah tidak boleh kosong!");
                // REDIRECT
                redirect("admin/verifikasi/statistik/upload");
            } else {
                // SET FILE NAME
                $tahun =  trim(strip_tags($this->input->post('tahun', TRUE)));
                $instansi_cd =  trim(strip_tags($this->input->post('get_instansi_cd', TRUE)));
                $instansi_cd = substr($instansi_cd, 0, 5);

                $path = 'resource/doc/imports/data/' . $instansi_cd . '/';
                $file_name = $this->_get_id() . "_" . $_FILES['template']['name'];
                $path_file = $path . $file_name;
                // UPLOAD CONFIG
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'xlsx';
                $config['file_name'] = $file_name;
                $config['overwrite'] = TRUE;
                // SET CONFIG
                $this->tupload->initialize($config);
                // UPLOAD TO FTP
                if ($this->tupload->do_upload('template')) {
                    // READ EXCEL IN OTHER FUNCTION
                    $this->read($tahun, $instansi_cd, $path, $file_name);
                } else {
                    // NOTIFICATION
                    $this->tnotification->sent_notification("error", $this->tupload->display_errors());
                    // REDIRECT
                    redirect("admin/verifikasi/statistik/index");
                }
            }
        }
    }

    public function read_verifikasi($tahun, $instansi_cd, $path = "", $file_name = "")
    {
        // LOAD MODEL  
        $this->load->library('SimpleXLSX');

        // READ EXCEL
        $instansi_cd = $instansi_cd;
        $path_file = $path . $file_name;
        $xlsx = new SimpleXLSX($path_file);
        

        // PARSING
        $no = 0;
        $tot_insert = 0;
        $upload_id = $this->_get_id();   

        // LOOP DATA EXCEL
        foreach ($xlsx->rows() as $key => $data) {
            print_r($xlsx->rows());
            die();
            
            // SKIP FIRST ROW
            if ($no > 0) {
                // PARSING DATA
                $data_id        = (!isset($data[0])) ? '' : trim(strip_tags($data[0]));
                $data_name      = (!isset($data[1])) ? '' : trim(strip_tags($data[1]));
                $data_unit      = (!isset($data[2])) ? '' : trim(strip_tags($data[2]));
                $year_min3      = (!isset($data[3])) ? '' : trim(strip_tags($data[3]));
                $year_min2      = (!isset($data[4])) ? '' : trim(strip_tags($data[4]));
                $year_min1      = (!isset($data[5])) ? '' : trim(strip_tags($data[5]));
                $data_st        = (!isset($data[6])) ? '' : trim(strip_tags($data[6]));
                $submission_st  = (!isset($data[7])) ? '' : trim(strip_tags($data[7]));
                $verify_comment = (!isset($data[8])) ? '' : trim(strip_tags($data[8]));
                $year           = (!isset($data[9])) ? '' : trim(strip_tags($data[9]));
                $data_type      = (!isset($data[10])) ? '' : trim(strip_tags($data[10]));
                $rumus_type     = (!isset($data[11])) ? '' : trim(strip_tags($data[11]));
                $detail_rumus   = (!isset($data[12])) ? '' : trim(strip_tags($data[12]));
                $instansi_name  = (!isset($data[13])) ? '' : trim(strip_tags($data[13]));
                $urusan_id      = (!isset($data[14])) ? '' : trim(strip_tags($data[14]));

                // PARSING DATA
                if ($submission_st == "") $submission_st = NULL;
                $detail_id = $this->_get_id();

                // SET PARAMS & DELETE DATA BEFORE INSERT
                $params = array(
                    "data_id" => $data_id,
                    "year" => $year
                );

                // $this->M_indicator_data_pg->delete($params);

                if ($submission_st == '2')
                   $submission_st = 'approved';
                elseif ($submission_st == 'empty')
                    $submission_st = 'kosong';
                elseif ($submission_st == '1')
                    $submission_st = 'pending';
                elseif ($submission_st == '0')
                    $submission_st = 'rejected';
                else
                    $submission_st = '';

                // SET PARAMS INSERT DATA 
                $params = array(
                    "data_id" => $data_id,
                    "upload_id" => $upload_id,
                    "upload_line" => $no + 1,
                    "year" => $year,
                    "submission_st" => $submission_st,
                    "verify_comment" => $verify_comment,
                    "detail_id" => $detail_id,
                    "verify_mdb_name" => $this->com_user['user_alias'],
                    "verify_mdb" => $this->com_user['user_id'],
                    "verify_mdd" => date("Y-m-d H:i:s")
                );

                $where = array(
                    "data_id" => $data_id,
                    "year" => $year
                );

                // CEK IF INSERT SUCCESS OR NOT
                if ($this->M_indicator_data_pg->update($params, $where)) {
                    // IF SUCCESS THE INSERT TO DETAIL & ADD COUNT
                    $this->M_indicator_data_detail_pg->update($params, $where);
                    $tot_insert++;
                }
            }
            // ADD COUNTING
            $no++;
        }

        // SET COUNTING
        $no = $no - 1;
        $tot =  $no;
        $tot_error =  $tot - $tot_insert;

        // // SET PARAMS AND INSERT ERROR
        // $params = array(
        //     "upload_id" => $upload_id,
        //     "instansi_cd" => $instansi_cd,
        //     "year" => $tahun,
        //     "path" => $path,
        //     "file_name" => $file_name,
        //     "tot" => $tot,
        //     "tot_error" => $tot_error,
        //     "tot_insert" => $tot_insert,
        //     "mdb_name" => $this->com_user['user_alias'],
        //     "mdb" => $this->com_user['user_id'],
        //     "mdd" => date("Y-m-d H:i:s")
        // );
        // $this->M_error_verifikasi_pg->insert($params);

        // SET NOTIFICATION
        $result = "Import verifikasi berhasil, dengan rincian jumlah data adalah " . $no . " data, data yang disimpan berjumlah " . $tot_insert . " data, dan data yang gagal disimpan berjumlah " . ($no - $tot_insert) . " data";
        $this->tnotification->sent_notification("success",  $result);

        // DEFAULT REDIRECT
        redirect("admin/verifikasi/statistik");
    }

    public function upload_verifikasi()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/verifikasi/statistik/upload_verifikasi.html");

        $rs_id = $this->M_instansi_pg->get_datas_all();

        // ASSIGN
        $this->tsmarty->assign("rs_id", $rs_id);
            
        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // OUTPUT
        parent::display();
    }

    public function upload_process_verifikasi()
    {
        // SET PAGE RULES
        $this->_set_page_rule("C");
        // LOAD LIBRARY
        $this->load->library('tupload');
        // NOTIFICATION RULES
        $this->tnotification->set_rules('instansi_cd', 'ID instansi', 'trim|max_length[100]');
        $year = date('Y');

        // CHECK NOTIFICATION
        if ($this->tnotification->run() !== FALSE) {

            // CHECK FILE POST EMPTY OR NOT
            if (empty($_FILES['template']['tmp_name'])) {

                // NOTIFICATION
                $this->tnotification->sent_notification("error", "File unggah tidak boleh kosong!");

                // REDIRECT
                redirect("admin/verifikasi/statistik/upload_verifikasi");
            } else {
                // SET FILE NAME
                $tahun =  trim(strip_tags($this->input->post('tahun', TRUE)));
                // $instansi_cd =  trim(strip_tags($this->input->post('instansi_cd', TRUE)));
                $instansi_cd =  trim(strip_tags($this->input->post('get_instansi_cd', TRUE)));
                $instansi_cd = substr($instansi_cd, 0, 5);
                $path = 'resource/doc/imports/data_verifikasi/' . $instansi_cd . '/';
                $file_name = $this->_get_id() . "_" . $_FILES['template']['name'];
                $path_file = $path . $file_name;

                // UPLOAD CONFIG
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'xlsx';
                $config['file_name'] = $file_name;
                $config['overwrite'] = TRUE;

                // SET CONFIG
                $this->tupload->initialize($config);

                // UPLOAD TO FTP
                if ($this->tupload->do_upload('template')) {
                    // READ EXCEL IN OTHER FUNCTION
                    $this->read_verifikasi($tahun, $instansi_cd, $path, $file_name);
                } else {
                    // NOTIFICATION
                    $this->tnotification->sent_notification("error", $this->tupload->display_errors());
                    // REDIRECT
                    redirect("admin/verifikasi/statistik/index");
                }
            }
        }
    }
}
