<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// LOAD BASE CLASS IF NEEDED
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Indicator extends OperatorBase
{

    // CONSTRUCTOR
    public function __construct()
    {
        parent::__construct();
        // LOAD MODEL
        //$this->load->model('admin/statistik/M_urusan');
        $this->load->model('admin/statistik/M_urusan_pg');
        //$this->load->model('admin/statistik/M_indicator');
        $this->load->model('admin/statistik/M_indicator_pg');
        //$this->load->model('admin/statistik/M_indicator_data');
        $this->load->model('admin/statistik/M_indicator_data_pg');
        //$this->load->model('admin/statistik/M_indicator_data_detail');
        $this->load->model('admin/statistik/M_indicator_data_detail_pg');
        //$this->load->model('admin/statistik/M_error');
        $this->load->model('admin/statistik/M_error_pg');
        //$this->load->model('admin/statistik/M_indicator_privileges');
        $this->load->model('settings/M_user');
        $this->load->model('admin/master/M_instansi');
        // LOAD LIBRARY
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->load->library('SimpleXLSXGen');
        //ASSIGN
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    public function index($data_id = "")
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/statistik/indicator/index.html");

        // GET INSTANSI CODE FROM SESSION
        $instansi_cd = $this->com_user['instansi_cd'];

        // GET SESSION SEARCH & ASSIGN
        $search = $this->session->userdata('search_indicator');

        //$indicator_class = $search['indicator_class'];

        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];

        //$indicator_id_default = $data_id . ".0001" . "%";
        $indicator_id_default = $data_id . "%";

        if ($instansi_cd == "10900") $indicator_id_default = $data_id . ".0001" . "%";
        $indicator_id  = empty($search['indicator_id']) ? $indicator_id_default : $search['indicator_id'] . "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);


        if (empty($search['indicator_class'])) {
            $indicator_class = "";
            $params = array($data_id, $instansi_cd);
            $indicators = $this->M_indicator_pg->get_indicator_by_indicator_class_semua($params);
        } else {
            $indicator_class = $search['indicator_class'];
            $param = array($data_id, $instansi_cd);
            $indicators = $this->M_indicator_pg->get_indicator_by_indicator_class($param, $indicator_class);
        }

        // GET DETAIL DATA
        $urusan = $this->M_urusan_pg->get_detail_urusan(array($data_id));
        //print_r($data_id);
        $urusan_parent = $this->M_urusan_pg->get_detail_urusan(array($urusan['parent_id']));

        //GET DATA DETAIL URUSAN TANPA PRIV
        $params = array($data_id, $indicator_id, $instansi_cd);

        if (
            $indicator_class == "class_sdgs" or $indicator_class == "class_rpjmd"
            or $indicator_class == "class_ikklppd" or $indicator_class == "class_spm"
            or $indicator_class == "class_dda" or $indicator_class == "class_datakudiy"
            or $indicator_class == "class_pilahgender" or $indicator_class == "class_sektoral"
        ) {

            $rs_id = $this->M_indicator_pg->get_indicator_export_by_params_indicator_class($params, $indicator_class);

            
        } else {

            $rs_id = $this->M_indicator_pg->get_indicator_export_by_params($params);
        }
        
        // SET LOOP YEAR FOR SEARCH
        $option_year = date("Y");
        $option_years = array();
        $option_year_min = 10;
        for ($i = ($option_year -  $option_year_min); $i <= $option_year; $i++) {
            array_push($option_years, $i);
        }

        // SET LOOP YEAR FOR TABLE
        $table_year = $tahun;
        $table_year_min = 2;
        $table_years = array();
        for ($i = ($table_year -  $table_year_min); $i <= $table_year; $i++) {
            array_push($table_years, $i);
        }

        // LOOP DATA FOT GET DATA
        foreach ($rs_id as $key => $value) {

            // LOOP DATA FOR GET DATA IN YEAR
            for ($i = ($table_year - $table_year_min); $i <= $table_year; $i++) {
                // PARSING DATA
                $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $i));
                $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
                $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];
                $submission_st = (!isset($data['submission_st']) || empty($data['submission_st'])) ? '' : $data['submission_st'];
                $verify_comment = (!isset($data['verify_comment']) || empty($data['verify_comment'])) ? '' : $data['verify_comment'];
                $mdd = (!isset($data['mdd']) || empty($data['mdd'])) ? '' : $this->tdtm->get_full_date($data['mdd']);
                $mdb_name = (!isset($data['mdb_name']) || empty($data['mdb_name'])) ? '' : $data['mdb_name'];
                $verify_mdd = (!isset($data['verify_mdd']) || empty($data['verify_mdd'])) ? '' : $this->tdtm->get_full_date($data['verify_mdd']);
                $verify_mdb_name = (!isset($data['verify_mdb_name']) || empty($data['verify_mdb_name'])) ? '' : $data['verify_mdb_name'];

                // INSERT TO ARRAY RESULT
                $rs_id[$key][$i] =  array(
                    'value' => $nilai,
                    'data_st' => $data_st,
                    'submission_st' => $submission_st,
                    'verify_comment' => $verify_comment,
                    'verify_mdd' => $verify_mdd,
                    'verify_mdb_name' => $verify_mdb_name,
                    'mdd' => $mdd,
                    'mdb_name' =>  $mdb_name,
                );
            }
        }

   
        // ASSIGN DATA
        $this->tsmarty->assign("data_id", $data_id);
        $this->tsmarty->assign("urusan_id", $data_id);
        $this->tsmarty->assign("urusan", $urusan);
        $this->tsmarty->assign("urusan_parent", $urusan_parent);
        $this->tsmarty->assign("indicators", $indicators);
        //$this->tsmarty->assign("indicators_with_class", $indicators_with_class);
        $this->tsmarty->assign("instansi_cd", $instansi_cd);
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

    public function download($data_id)
    {
        //GET INSTANSI DARI SESSION
        $instansi_cd = $this->com_user['instansi_cd'];

        // CEK URUSAN, JIKA TIDAK ADA DIREDIRECT
        $urusan = $this->M_urusan_pg->get_detail_urusan(array($data_id));


        if (empty($urusan)) redirect("admin/statistik/indicator");

        // GET SESSION SEARCH & ASSIGN
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id  = empty($search['indicator_id']) ? "%" : $search['indicator_id'] . "%";
        // if($instansi_cd == "10900") $indicator_id ="%";
        // $indicator_id  ="%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // GET DATA INDICATOR DENGAN PRIV
        // $params = array($data_id, $indicator_id, $instansi_cd, $tahun);
        // $rs_id = $this->M_indicator_privileges->get_indicator_export_by_params($params);
        // $result = array();
        // foreach ($rs_id as $key => $value) {
        //     //ambil list data
        //     $datas = $this->M_indicator_privileges->get_indicator_export_detail_by_params(array($value['data_id'] ));
        //     foreach ($datas as $keys => $data) {
        //         array_push($result, $data);
        //     }
        // }
        // $rs_id = $result;

        // GET DATA INDICATOR TANPA PRIV
        $rs_id = $this->M_indicator_pg->get_indicator_by_params(array($data_id, $indicator_id, $instansi_cd));
        // print_r($rs_id);
        // die();
        // LOOP DATA RESULT
        foreach ($rs_id as $key => $value) {
            // PARSING DATA
            $data = $this->M_indicator_data_pg->get_data_by_params(array($value['data_id'], $tahun));
            $nilai = (!isset($data['value'])) ? '' : trim($data['value']);
            $data_st = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];
            $verify_comment = (!isset($data['verify_comment']) || empty($data['verify_comment'])) ? '' : $data['verify_comment'];
            $submission_st = (!isset($data['submission_st']) || empty($data['submission_st'])) ? '' : $data['submission_st'];
            if ($submission_st == 'approved')
                $submission_st = 'diterima';
            elseif ($submission_st == 'empty')
                $submission_st = 'kosong';
            elseif ($submission_st == 'pending')
                $submission_st = 'menunggu';
            elseif ($submission_st == 'rejected')
                $submission_st = '<s>ditolak</s>';
            else
                $submission_st = '';

            // INSERT DATA TO ARRAY
            $rs_id[$key]['data_id'] =  "<left>" . $rs_id[$key]['data_id'] . "</left>";
            $rs_id[$key]['year'] =   $search['tahun'];
            $rs_id[$key]['value_new'] = NULL;
            $rs_id[$key]['data_st_new'] = NULL;
            $rs_id[$key]['value'] =  $nilai;
            $rs_id[$key]['data_st'] =  $data_st;
            $rs_id[$key]['submission_st'] =  $submission_st;
            $rs_id[$key]['verify_comment'] =  $verify_comment;

            // UNSET DATA ARRAY
            unset($rs_id[$key]['parent_id']);
            unset($rs_id[$key]['urusan_id']);
            unset($rs_id[$key]['active_st']);
            unset($rs_id[$key]['data_type']);
            unset($rs_id[$key]['instansi_cd']);
            unset($rs_id[$key]['mdd']);
            unset($rs_id[$key]['mdb']);
        }

        // INSERT TITLE IN EXCEL TEMPLATE
        $title = array(
            "data_id" => "<b>Kode ID</b>",
            "data_name" => "<b>Nama</b>",
            "data_unit" => "<b>Satuan</b>",
            "year" => "<b>Tahun</b>",
            "value_new" => "<b>Nilai Baru</b>",
            "data_st_new" => "<b>Sifat Data Baru (Diisi angka saja: 0=tidak ada data; 1=TW-1; 2=TW-2; 3=TW-3; 4=TW-4; 5=Tetap)</b>",
            "value" => "<b>Nilai Lama (tidak boleh diubah)</b>",
            "data_st" => "<b>Sifat Data Lama (tidak boleh diubah)</b>",
            "submission_st" => "<b>Status Pengajuan (tidak boleh diubah)</b>",
            "verify_comment" => "<b>Catatan (tidak boleh diubah)</b>",

        );
        array_unshift($rs_id, $title);

        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($rs_id);
        $filename =  "Template Bidang " . $urusan['urusan_name'] . ".xlsx";
        $filename = str_replace(" ", "_", strtolower($filename));
        $filename =  "template_upload_data_bidang_urusan_" . $urusan['urusan_id'] . ".xlsx";
        $xlsx->downloadAs($filename);
    }

    public function download_template_data_opd($data_id)
    {
        //GET INSTANSI DARI SESSION
        $instansi_cd = $this->com_user['instansi_cd'];

        // CEK URUSAN, JIKA TIDAK ADA DIREDIRECT
        $urusan = $this->M_urusan_pg->get_detail_urusan(array($data_id));
        if (empty($urusan)) redirect("admin/statistik/indicator");

        // GET SESSION SEARCH & ASSIGN
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indicator_id  = empty($search['indicator_id']) ? "%" : $search['indicator_id'] . "%";
        if ($instansi_cd == "10900") $indicator_id = "%";
        $indicator_id  = "%";
        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        // SET DATE
        $result = array();

        // TITLE
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_name'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
        $result[0]['data_unit'] = "<b>Satuan</b>";
        $result[0]['year'] = "<b>Tahun</b>";
        $result[0]['value_new'] = "<b>Nilai Baru</b>";
        $result[0]['data_st_new'] = "<b>Sifat Data Baru (Diisi angka saja: 0=tidak ada data; 1=TW-1; 2=TW-2; 3=TW-3; 4=TW-4; 5=Tetap)</b>";
        $result[0]['submission_st'] = "<b>Status Pengajuan (Diisi angka saja: 0=Ditolak 1=Menunggu 2=Diterima)</b>";
        $result[0]['verify_comment'] = "<b>Catatan Admin (Data Tidak Boleh Diubah)</b>";
        $result[0]['verify_comment_opd'] = "<b>Catatan OPD</b>";
        $result[0]['rumus_type'] = "<b>Rumus Type</b>";
        $result[0]['rumus_detail'] = "<b>Rumus Detail</b>";
        $result[0]['value'] = "<b>Nilai Lama (Data Tidak Boleh Diubah)</b>";
        $result[0]['data_st'] = "<b>Sifat Data Lama Tahun Terakhir (Data Tidak Boleh Diubah)</b>";

        // GET DATA INDICATOR TANPA PRIV
        $rs_id = $this->M_indicator_pg->get_indicator_by_params(array($data_id, $indicator_id, $instansi_cd));

        // LOOP DATA RESULT
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
            $data_st_new = (!isset($data['data_st']) || empty($data['data_st'])) ? '' : $data['data_st'];
            if ( $data_st_new == "tidakada" || $nilai == "n/a") {
                $data_st_new = "0";
            } else if ($data_st_new == "TW-1") {
                    $data_st_new = "1";
            } else if ($data_st_new == "TW-2"){
                $data_st_new = "2";
            } else if ($data_st_new == "TW-3"){
                $data_st_new = "3";
            } else if ($data_st_new == "TW-4"){
                $data_st_new = "4";
            } else if ($data_st_new == "tetap"){
                $data_st_new = "5";
            } else {
                $data_st_new = "";
            }
            
            $verify_comment = (!isset($data['verify_comment']) || empty($data['verify_comment'])) ? '' : $data['verify_comment'];
            $verify_comment_opd = null;
            $submission_st = (!isset($data['submission_st']) || empty($data['submission_st'])) ? '' : $data['submission_st'];

            // INSERT TO ARRAY
            $result[$number]['year']   = $bold_start . $search['tahun'] . $bold_end;
            $result[$number]['value_new'] =  $bold_start . $nilai . $bold_end;
            $result[$number]['data_st_new'] = $bold_start . $data_st_new . $bold_end;
            
            // INSERT TO ARRAY
            $result[$number]['submission_st'] =  $bold_start . $submission_st . $bold_end;
            $result[$number]['verify_comment'] =  $bold_start . $verify_comment . $bold_end;
            $result[$number]['verify_comment_opd'] =  $bold_start . $verify_comment_opd . $bold_end;
            $result[$number]['rumus_type'] =  $bold_start . $value['rumus_type'] . $bold_end;
            $result[$number]['rumus_detail'] =  $bold_start . $value['rumus_detail'] . $bold_end;
            $result[$number]['value'] =  $bold_start . $nilai . $bold_end;
            $result[$number]['data_st'] = $bold_start . $data_st . $bold_end;

            // ADD 
            $number++;
        }

        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($result);
        $filename =  "Template Bidang " . $urusan['urusan_name'] . ".xlsx";
        $filename = str_replace(" ", "_", strtolower($filename));
        $filename =  "template_upload_data_bidang_urusan_" . $urusan['urusan_id'] . ".xlsx";
        $xlsx->downloadAs($filename);
    }

    public function download_laporan()
    {
        //GET INSTANSI DARI SESSION
        $instansi_cd = $this->com_user['instansi_cd'];
        $instansi =  $this->M_instansi->get_detail_instansi($instansi_cd);

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

        // print_r("<pre>");
        // print_r('$result');
        // print_r("</pre>");
        // die;
        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($result);
        $filename =  "Laporan " . $instansi['instansi_name'] . ".xlsx";
        $xlsx->downloadAs($filename);
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

        // print_r($xlsx->rows());
        // die();
        
        // LOOP DATA EXCEL
        foreach ($xlsx->rows() as $key => $data) {

            // SKIP FIRST ROW
            if ($no > 0) {
                // PARSING DATA
                $data_id            = (!isset($data[0])) ? '' : trim(strip_tags($data[0]));
                $data_name          = (!isset($data[1])) ? '' : trim(strip_tags($data[1]));
                $data_unit          = (!isset($data[2])) ? '' : trim(strip_tags($data[2]));
                $year               = (!isset($data[3])) ? '' : trim(strip_tags($data[3]));
                $value_new          = (!isset($data[4])) ? '' : trim(strip_tags($data[4]));
                $data_st_new        = (!isset($data[5])) ? '' : trim(strip_tags($data[5]));
                $submission_st      = (!isset($data[6])) ? '' : trim(strip_tags($data[6]));
                $verify_comment     = (!isset($data[7])) ? '' : trim(strip_tags($data[7]));
                $verify_comment_opd = (!isset($data[8])) ? '' : trim(strip_tags($data[8]));
                $rumus_type         = (!isset($data[9])) ? '' : trim(strip_tags($data[9]));
                $rumus_detail       = (!isset($data[10])) ? '' : trim(strip_tags($data[10]));
                $value              = (!isset($data[11])) ? '' : trim(strip_tags($data[11]));
                $data_st            = (!isset($data[12])) ? '' : trim(strip_tags($data[12]));

                // SET ARRAY STATUS
                $list_data = array("0", "1", "2", "3", "4", "5");
                // CEK IF EXCEL VALUE NOT IN ARRAY
                if (!in_array(strtolower($data_st_new), $list_data)) {
                    // SET PARAMS & INSERT DATA IN ERROR LIST
                    $catatan = "Kesalahan status data";
                    $err_params = array(
                        "upload_id" => $upload_id,
                        "upload_line" => $no + 1,
                        "data_id" => $data_id,
                        "year" => $year,
                        "value_old" => $value,
                        "value" => $value_new,
                        "data_st_old" => $data_st,
                        "data_st" => $data_st_new,
                        "catatan" => $catatan,
                        "mdb_name" => $this->com_user['user_alias'],
                        "mdb" => $this->com_user['user_id'],
                        "mdd" => date("Y-m-d H:i:s")
                    );
                    $this->M_error_pg->insert_detail($err_params);
                }
                // CEK IF VALUE IS NOT EMPTY
                else if ($data_id != "" && $year != "" &&  $value_new != "" && $data_st_new != "") {

                    // PARSING DATA
                    if ($data_st_new == "0") {
                        $data_st_new = "tidakada";
                        $value_new = "n/a";
                    } else if ($data_st_new == "1")
                        $data_st_new = "TW-1";
                    else if ($data_st_new == "2")
                        $data_st_new = "TW-2";
                    else if ($data_st_new == "3")
                        $data_st_new = "TW-3";
                    else if ($data_st_new == "4")
                        $data_st_new = "TW-4";
                    else if ($data_st_new == "5")
                        $data_st_new = "tetap";

                    // CHECK IF OLD & NEW DATA IS DIFF
                    if ($value != $value_new || $data_st != $data_st_new) {
                        // PARSING DATA
                        if ($value == "") $value = NULL;
                        $detail_id = $this->_get_id();
                        // SET PARAMS & DELETE DATA BEFORE INSERT
                        $params = array(
                            "data_id" => $data_id,
                            "year" => $year
                        );
                        $this->M_indicator_data_pg->delete($params);

                        // SET PARAMS INSERT DATA 
                        $params = array(
                            "data_id" => $data_id,
                            "upload_id" => $upload_id,
                            "upload_line" => $no + 1,
                            "year" => $year,
                            "value" => $value_new,
                            "data_st" => $data_st_new,
                            "submission_st" => "pending",
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
                        } else {
                            $catatan = "Error saat insert";
                            $err_params = array(
                                "upload_id" => $upload_id,
                                "upload_line" => $no + 1,
                                "data_id" => $data_id,
                                "year" => $year,
                                "value_old" => $value,
                                "value" => $value_new,
                                "data_st_old" => $data_st,
                                "data_st" => $data_st_new,
                                "catatan" => $catatan,
                                "mdb_name" => $this->com_user['user_alias'],
                                "mdb" => $this->com_user['user_id'],
                                "mdd" => date("Y-m-d H:i:s")
                            );
                            $this->M_error_pg->insert_detail($err_params);
                        }
                    } else {
                        // SET PARAMS & INSERT TO ERROR BECAUSE DATA SIMILAR
                        $catatan = "Adanya kesamaan data";
                        $err_params = array(
                            "upload_id" => $upload_id,
                            "upload_line" => $no + 1,
                            "data_id" => $data_id,
                            "year" => $year,
                            "value_old" => $value,
                            "value" => $value_new,
                            "data_st_old" => $data_st,
                            "data_st" => $data_st_new,
                            "catatan" => $catatan,
                            "mdb_name" => $this->com_user['user_alias'],
                            "mdb" => $this->com_user['user_id'],
                            "mdd" => date("Y-m-d H:i:s")
                        );
                        $this->M_error_pg->insert_detail($err_params);
                    }
                } else {
                    // SET PARAMS & INSERT TO ERROR BECAUSE DATA EMPTY
                    $catatan = "Adanya kekosongan data (data id, tahun, nilai baru dan status data baru)";
                    $err_params = array(
                        "upload_id" => $upload_id,
                        "upload_line" => $no + 1,
                        "data_id" => $data_id,
                        "year" => $year,
                        "value_old" => $value,
                        "value" => $value_new,
                        "data_st_old" => $data_st,
                        "data_st" => $data_st_new,
                        "catatan" => $catatan,
                        "mdb_name" => $this->com_user['user_alias'],
                        "mdb" => $this->com_user['user_id'],
                        "mdd" => date("Y-m-d H:i:s")
                    );
                    $this->M_error_pg->insert_detail($err_params);
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
        redirect("admin/statistik/urusan");
    }

    // SEARCH PROCESS
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
        redirect("admin/statistik/indicator/index/" . trim(strip_tags($this->input->post('urusan_id', TRUE))));
    }

    public function ajukan_process()
    {

        // SET PAGE RULES
        $this->_set_page_rule("R");

        // GET DATA FROM POST
        $year = trim(strip_tags($this->input->post( 'year', TRUE)));
        $urusan_id = trim(strip_tags($this->input->post('urusan_id', TRUE)));
        $instansi_cd = trim(strip_tags($this->input->post('instansi_cd', TRUE)));
        $datas =   $this->input->post('datas', TRUE);
        $values =  $this->input->post('values', TRUE);
        $old_values =  $this->input->post('old_values', TRUE);
        $old_statuses =  $this->input->post('old_statuses', TRUE);
        $statuses =  $this->input->post('statuses', TRUE);
        $verify_comments =  $this->input->post('verify_comment', TRUE);

        $tot_insert = 0;
        // LOOP ARRAY POST DATA
        foreach ($datas as $key => $data) {

            $check_sts =  $this->input->post('check_st_' . $key, TRUE);
            
            // PARSING DATA POST
            if ($check_sts == '1') {
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

                // SET DETAIL DATA
                $detail_id = $this->_get_id();

                // DELETE DATA BEFORE INSERT
                $params = array(
                    "data_id" => $data,
                    "year" => $year
                );
                $this->M_indicator_data_pg->delete($params);

                if ($value == '') {
                    $params = array(
                        "data_id" => $data,
                        "year" => $year,
                        "value" => $value,
                        "data_st" => $status,
                        "submission_st" => '-',
                        "verify_comment" => $verify_comment,
                        "detail_id" => $detail_id,
                        "mdb_name" => $this->com_user['user_alias'],
                        'mdb' => $this->com_user['user_id'],
                        'mdd' => date('Y-m-d H:i:s')
                    );
                } else {

                    // SET PARAMS
                    $params = array(
                        "data_id" => $data,
                        "year" => $year,
                        "value" => $value,
                        "data_st" => $status,
                        "submission_st" => 'pending',
                        "verify_comment" => $verify_comment,
                        "detail_id" => $detail_id,
                        "mdb_name" => $this->com_user['user_alias'],
                        'mdb' => $this->com_user['user_id'],
                        'mdd' => date('Y-m-d H:i:s')
                    );
                }

                if ($verify_comment != 'system') {
                    //INSERT DATA
                    $this->M_indicator_data_pg->insert($params);
                    // INSERT DETAIL DATA
                    $this->M_indicator_data_detail_pg->insert($params);

                    $tot_insert++;
                }
                
            }
        }

        //}

        // REDIRECT
        $this->tnotification->sent_notification("success",  $tot_insert ." Data berhasil diajukan");
        redirect("admin/statistik/indicator/index/" . $urusan_id);
    }

    public function ajukan_rumus()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");

        // GET DATA FROM POST
        $year = trim(strip_tags($this->input->post('year', TRUE)));
        $urusan_id = trim(strip_tags($this->input->post('urusan_id', TRUE)));
        $instansi_cd = trim(strip_tags($this->input->post('instansi_cd', TRUE)));
        $datas =   $this->input->post('datas', TRUE);
        $data_types =   $this->input->post('data_type', TRUE); // add
        $values =  $this->input->post('values', TRUE);
        $old_values =  $this->input->post('old_values', TRUE);
        $old_statuses =  $this->input->post('old_statuses', TRUE);
        $statuses =  $this->input->post('statuses', TRUE);
        $verify_comments =  $this->input->post('verify_comment', TRUE);

        // LOOP ARRAY POST DATA
        foreach ($datas as $key => $data) {

            $check_sts =  $this->input->post('check_st_' . $key, TRUE);

            // PARSING DATA POST
            $verify_comment = trim(strip_tags($verify_comments[$key]));
            $verify_comment = (!isset($verify_comment) || empty($verify_comment)) ? NULL : $verify_comment;
            $value = trim(strip_tags($values[$key]));
            $old_value = trim(strip_tags($old_values[$key]));
            $value = str_replace(",", ".", $value);
            $old_value = str_replace(",", ".", $old_value);
            $old_status = trim(strip_tags($old_statuses[$key]));
            $status = trim(strip_tags($statuses[$key]));
            $data_type = trim(strip_tags($data_types[$key])); // add
            
            // SET DETAIL DATA
            $request_id = $this->_get_id(); // add

            // DELETE DATA BEFORE INSERT
            $params = array(
                "data_id" => $data,
                "year" => $year
            );
            $this->M_indicator_pg->delete($params);
                
            // SET PARAMS // add
            $params = array(
                "data_id" => $data,
                "year" => $year,
                "data_type" => $data_type,
                "request_st" => 'pending',
                "request_id" => $request_id,
                'mdb' => $this->com_user['user_id'],
                'mdd' => date('Y-m-d H:i:s')
            );

            // INSERT DATA
            if ($params['data_type'] == 'indikator') {
                $params = array(
                    "data_id" => $data,
                    "year" => $year,
                    "request_st" => 'pending',
                    "request_id" => $request_id,
                    'mdb' => $this->com_user['user_id'],
                    'mdd' => date('Y-m-d H:i:s')
                );
                $this->M_indicator_pg->insert_request($params);
            } else {
                // 
            }
        }
        
        // REDIRECT
        $this->tnotification->sent_notification("success",  "Rumus berhasil diajukan");
        redirect("admin/statistik/indicator/index/" . $urusan_id);
        
    }

    public function klasifikasi()
    {
        $instansi_cd = $this->com_user['instansi_cd'];
        $urusan_id =  $this->input->post('urusan_id', TRUE);
        $params = array($urusan_id, $instansi_cd);
        $indicator_class = $this->input->post('id', TRUE);

        $data = $this->M_indicator_pg->get_indicator_by_indicator_class($params, $indicator_class);

        echo json_encode($data);
    }
    public function klasifikasi_semua()
    {
        $instansi_cd = $this->com_user['instansi_cd'];
        $urusan_id =  $this->input->post('urusan_id', TRUE);
        $params = array($urusan_id, $instansi_cd);

        $data = $this->M_indicator_pg->get_indicator_by_indicator_class_semua($params);

        echo json_encode($data);
    }


    public function upload()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/statistik/indicator/upload.html");

        // GET INSTANSI CD FROM SESSION
        $instansi_cd = $this->com_user['instansi_cd'];
        // ASSIGN
        $this->tsmarty->assign("instansi_cd", $instansi_cd);

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
                redirect("admin/statistik/indicator/upload");
            } else {
                // SET FILE NAME
                $tahun =  trim(strip_tags($this->input->post('tahun', TRUE)));
                $instansi_cd =  trim(strip_tags($this->input->post('instansi_cd', TRUE)));
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
                    redirect("admin/statistik/indicator/index");
                }
            }
        }
    }
}
