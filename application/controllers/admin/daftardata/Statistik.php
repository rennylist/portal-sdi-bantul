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
        //$this->load->model('admin/statistik/M_indicator_privileges_pg');
        
        // LOAD LIBRARY
        $this->load->library('tnotification');
        $this->load->library('pagination');
        $this->load->library("tdtm");
        $this->load->library('SimpleXLSXGen');
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    public function index()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R"); //enable
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/daftardata/statistik/index.html");

        // GET SESSION CODE INSTANSI
        $data_session = $this->session->userdata();
        $data_instansi_cd = $data_session['session_operator']['instansi_cd'];

        // GET SESSION SEARCH
        $search = $this->session->userdata('search_indicator');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }

        // get param 
        $user_st = !isset($search['user_st']) ? '%' : $search['user_st'];
        $this->tsmarty->assign("search", $search);

        // GET DATA INDICATOR PRIORITAS STATISTIK BY PARAM
        $params = $data_instansi_cd;
        if (!empty($params)) {
            $rs_id = $this->M_indicator_pg->get_indicator_statistik_by_opd($params);

        } else {
             /* start of pagination --------------------- */
            // pagination
            $config['base_url'] = site_url("admin/daftardata/statistik/index/");
            $config['total_rows'] = $this->M_indicator_pg->get_total_indicator_statistik();
            $config['uri_segment'] = 5;
            $config['per_page'] = 10;
            $config['attributes'] = array('class' => 'page-link');
            $this->pagination->initialize($config);
            $pagination['data'] = $this->pagination->create_links();
            // pagination attribute
            $start = $this->uri->segment(5, 0) + 1;
            $end = $this->uri->segment(5, 0) + $config['per_page'];
            $end = (($end > $config['total_rows']) ? $config['total_rows'] : $end);
            $pagination['start'] = ($config['total_rows'] == 0) ? 0 : $start;
            $pagination['end'] = $end;
            $pagination['total'] = $config['total_rows'];
            // pagination assign value
            $this->tsmarty->assign("pagination", $pagination);
            $this->tsmarty->assign("no", $start);
            /* end of pagination ---------------------- */

            // GET DATA INDICATOR PRIORITAS STATISTIK BY PARAM
            $params = array($config['per_page'],$start - 1);
            $rs_id = $this->M_indicator_pg->get_indicator_statistik($params);
        }
        
        // ASSIGN DATA
        $this->tsmarty->assign("rs_id", $rs_id);
        // $this->tsmarty->assign("result_data", $result_data);
        
        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();

        // OUTPUT
        parent::display();
    }


    public function download_laporan_instansi()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R"); //enable

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
            $tot_fill       = empty($data['tot_fill'])      ? 0 : $data['tot_pending'] + $data['tot_reject'] + $data['tot_approve'];
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
        if (empty($instansi)) redirect("admin/daftardata/statistik/indicator");

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
        
        // // GET SESSION SEARCH & ASSIGN
        // $search = $this->session->userdata('search_indicator');
        // $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        // $indicator_id_default = "%";
        // $indicator_id  = empty($search['indicator_id']) ? $indicator_id_default : $search['indicator_id'] . "%";
        // if (empty($search)) {
        //     $search['tahun'] =  date("Y");
        // }
        // $this->tsmarty->assign("search", $search);

        // // SET LOOP YEAR FOR TABLE
        // $table_year = $tahun;
        // $table_year_min = 4;
        // $table_years = array();
        // for ($i = ($table_year -  $table_year_min); $i <= $table_year; $i++) {
        //     array_push($table_years, $i);
        // }

        // GET DATA INDICATOR TANPA PRIV
        $rs_id = $this->M_indicator_pg->get_indicator_all();
        // print_r($rs_id);
        // // SET DATE
        // $result = array();
        

        // TITLE
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_name'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
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
            $result[$number]['xxx'] = $bold_start . $value['urusan_name'] . $bold_end;
            $result[$number]['zzz'] = $bold_start . $value['instansi_name'] . $bold_end;

            // ADD 
            $number++;
        }

        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($result);
        $filename =  "Daftar Data Statistik.xlsx";
        $xlsx->downloadAs($filename);
    }

    public function get_all_data()
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
        $table_year_min = 4;
        $table_years = array();
        for ($i = ($table_year -  $table_year_min); $i <= $table_year; $i++) {
            array_push($table_years, $i);
        }

        // GET DATA INDICATOR TANPA PRIV
        $rs_id = $this->M_indicator_pg->get_indicator_all();

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
            $result[$number]['xxx'] = $bold_start . $value['urusan_name'] . $bold_end;
            $result[$number]['zzz'] = $bold_start . $value['instansi_name'] . $bold_end;

            // ADD 
            $number++;
        }
    }

    public function urusan($instansi_cd)
    {
        // SET PAGE RULES
        $this->_set_page_rule("R"); //enable
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/daftardata/statistik/urusan.html");

        // GET DATA INSTANSI & IF EMPTY REDIRECT TO INDEX
        $instansi = $this->M_instansi_pg->get_detail_instansi(array($instansi_cd));
        if (empty($instansi)) redirect("admin/daftardata");

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
        $this->_set_page_rule("R"); //enable
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/daftardata/statistik/indicator.html");

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
        if (empty($instansi)) redirect("admin/daftardata");

        // DATA WITHOUT PRIVILEGES
        // GET DETAIL URUSAN & REDIRECT IF EMPTY
        $urusan = $this->M_urusan_pg->get_detail_urusan(array($data_id));
        if (empty($urusan)) redirect("admin/daftardata");

        // GET DETAIL AND LIST INDICATOR FOR SEARCH
        $urusan_parent = $this->M_urusan_pg->get_detail_urusan(array($urusan['parent_id']));
        $indicators = $this->M_indicator_pg->get_indicator_by_instansi(array($data_id, $instansi_cd));

        // GET LIST INDICATOR & VARIBLE WITHOUT PRIVILEGES
        $params = array($data_id, $indicator_id, $instansi_cd);
        if (
            $indicator_class == "class_sdgs" or $indicator_class == "class_rpjmd"
            or $indicator_class == "class_ikklppd" or $indicator_class == "class_spm"
            or $indicator_class == "class_dda" or $indicator_class == "class_datakudiy"
            or $indicator_class == "class_pilahgender"
        ) {


            $rs_id = $this->M_indicator_pg->get_indicator_export_by_params_indicator_class($params, $indicator_class);
        } else {
            $rs_id = $this->M_indicator_pg->get_indicator_export_by_params($params);
        }
        //$rs_id = $this->M_indicator->get_indicator_export_by_params(array($data_id, $indicator_id, $instansi_cd));

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

        // LOOP LIST AND PUSH DATA value PER YEAR
        foreach ($rs_id as $key => $value) {
            // LOOP YEAR
            for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
                // GET DETAIL DATA & PARSING
                $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $i));
                $detail_id = (!isset($data['detail_id'])) ? '' : trim($data['detail_id']);
                $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
                $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];
                $submission_st = (!isset($data['submission_st']) || empty($data['submission_st'])) ? '' : $data['submission_st'];
                $verify_comment = (!isset($data['verify_comment']) || empty($data['verify_comment'])) ? '' : $data['verify_comment'];
                // PUSH TO MAIN ARRAY
                if ($table_year == $i) {
                    // ADD HISTORY INDICATOR & VARIABLE DATA, IF YEAR = YEAR SELECTED
                    $history = $this->M_indicator_data_detail_pg->get_history_by_params_limit(array($value['data_id'], $i));
                    $rs_id[$key][$i] =  array(
                        'detail_id' => $detail_id,
                        'value' => $nilai,
                        'data_st' => $data_st,
                        'submission_st' => $submission_st,
                        'verify_comment' => $verify_comment,
                        'history' =>  $history
                    );
                } else {
                    $rs_id[$key][$i] =  array(
                        'detail_id' => $detail_id,
                        'value' => $nilai,
                        'data_st' => $data_st,
                        'submission_st' => $submission_st,
                        'verify_comment' => $verify_comment,
                    );
                }
            }
        }
        
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

    // search process
    public function search_process()
    {
        // set page rules
        $this->_set_page_rule("R");
        // session
        if ($this->input->post('save') == "Cari") {
            // params
            $params = array(
                "indikator" => $this->stripHTMLtags($this->input->post('indikator', TRUE)),
                "urusan_id" => $this->stripHTMLtags($this->input->post('urusan_id', TRUE)),
                "urusan_name" => $this->stripHTMLtags($this->input->post('urusan_name', TRUE))
            );
            // set session
            $this->session->set_userdata("search_indicator", $params);
        } else {
            // unset session
            $this->session->unset_userdata("search_indicator");
        }
        // redirect
        redirect("admin/daftardata/statistik");
    }
    

    public function search_urusan_process()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R"); //enable
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
            redirect("admin/daftardata");
        else
            redirect("admin/daftardata/statistik/urusan/" . trim(strip_tags($this->input->post('instansi_cd', TRUE))));
    }
}
