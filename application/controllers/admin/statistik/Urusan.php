<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// LOAD BASE CLASS IF NEEDED
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Urusan extends OperatorBase
{
    // CONSTRUCTOR
    public function __construct()
    {
        parent::__construct();
        // LOAD MODEL
        $this->load->model('admin/statistik/M_urusan');
        $this->load->model('admin/statistik/M_urusan_pg');
        $this->load->model('admin/statistik/M_indicator_data');
        $this->load->model('admin/statistik/M_indicator_data_pg');
        $this->load->model('admin/statistik/M_indicator_privileges');
        $this->load->model('settings/M_user');
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
        $this->tsmarty->assign("template_content", "admin/statistik/urusan/index.html");

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

        // GET INSTANSI CD
        $instansi_cd = $this->com_user['instansi_cd'];

        // GET DATA FOR TABLE WITHOUT PRIVILEGES
        $rs_id = $this->M_urusan_pg->get_all_by_instansi(array($instansi_cd, $instansi_cd,));

        // LOOP DATA URUSAN DAN BIDANG URUSAN
        $total = ['total' => 0, 'tot_min' => 0, 'tot_approve' =>0, 'tot_pending' => 0, 'tot_reject' => 0];
        $urusan =[];
        foreach ($rs_id as $key => $value) {

            if (!isset($urusan[$value['urusan_id']])) {

                // GET COUNTING DATA
                $param = array($instansi_cd, $value['urusan_id'] . "%", $instansi_cd, $value['urusan_id'] . "%",  $tahun);
                $data = $this->M_indicator_data_pg->get_count_by_instansi($param);

                // PARSING TO ARRAY DATA
                $rs_id[$key]['tot']         = $data['total'];
                $rs_id[$key]['tot_fill']    = $data['tot_fill'];
                // $rs_id[$key]['tot_unfill']  = $data['tot_unfill'];
                $rs_id[$key]['tot_pending'] = $data['tot_pending'];
                $rs_id[$key]['tot_reject']  = $data['tot_reject'];
                $rs_id[$key]['tot_approve'] = $data['tot_approve'];
                $rs_id[$key]['tot_min']     = $data['total'] - ($data['tot_pending'] + $data['tot_reject'] + $data['tot_approve']);

                // NEW - GET TOTAL AKHIR URUSAN
                if (empty($rs_id[$key]['parent_id'])) {
                    $total['total'] += $rs_id[$key]['tot'];
                    $total['tot_min'] += $rs_id[$key]['tot_min'];
                    $total['tot_approve'] += $rs_id[$key]['tot_approve'];
                    $total['tot_pending'] += $rs_id[$key]['tot_pending'];
                    $total['tot_reject'] += $rs_id[$key]['tot_reject'];
                }

                $urusan[$value['urusan_id']] = $rs_id[$key];
            }
        }
        $rs_id = $urusan;

        // GET DATA FOR TABLE WITH PRIVILEGES
        // $params = array($instansi_cd,$tahun, $instansi_cd,$tahun);
        // $rs_id = $this->M_indicator_privileges->get_all_by_instansi($params);
        // GET LIST DATA
        // foreach ($rs_id as $key => $value) {
        //     if(!empty($value['parent_id']))
        //     {
        //         // GET COUNTING DATA
        //         $params = array($value['urusan_id'], $tahun, $instansi_cd, "yes");
        //         $vals = $this->M_indicator_privileges->get_usaha_by_params($params);
        //         $tot = 0;
        //         foreach ($vals as $keyy => $val) {
        //             $tot = $tot + $this->M_indicator_privileges->get_total_indicator(array($val['data_id']));
        //         }
        //         $data = $this->M_indicator_data->get_total_urusan_by_params(array($value['urusan_id']."%",  $tahun));
        //         // PARSING TO ARRAY DATA
        //         $rs_id[$key]['tot'] = $tot;
        //         $rs_id[$key]['tot_min'] = $tot - ($data['tot_pending'] + $data['tot_reject'] + $data['tot_approve']);
        //         $rs_id[$key]['tot_pending'] = $data['tot_pending'];
        //         $rs_id[$key]['tot_reject']  = $data['tot_reject'];
        //         $rs_id[$key]['tot_approve'] = $data['tot_approve'];
        //     }    
        // }

        // ASSIGN DATA
        $this->tsmarty->assign("option_years", array_reverse($option_years));
        $this->tsmarty->assign("option_year", $option_year);
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("total", $total);
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
                "tahun" => trim(strip_tags($this->input->post('tahun', TRUE))),
            );
            // GET SESSION
            $this->session->set_userdata("search_indicator", $params);
        } else {
            // UNSET SESSION
            $this->session->unset_userdata("search_indicator");
        }
        // REDIRECT
        $url = "admin/statistik/urusan";
        redirect($url);
    }
}
