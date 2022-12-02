<?php

if (!defined('BASEPATH'))
exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/UboldBase.php' );

class Hakakses extends OperatorBase {

    // constructor
    public function __construct() {
        parent::__construct();
        // load model
        $this->load->model('admin/statistik/M_indicator');
        $this->load->model('admin/statistik/M_indicator_data');
        $this->load->model('admin/statistik/M_urusan');
        $this->load->model('admin/statistik/M_indicator_privileges');
        $this->load->model('admin/master/M_instansi');
        // load library
        $this->load->library('tnotification');
        $this->load->library('pagination');
        $this->load->library("tdtm");
        //assign
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    // list data
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/hakases/index.html");
        // get session search
        $search = $this->session->userdata('search_hakakses');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $search['tahun'] = $tahun;
        //get data
        $rs_id = $this->M_instansi->get_datas_all();
        foreach ($rs_id as $key => $value) {
            $data = $this->M_instansi->get_total_by_params(array($tahun, $value['instansi_cd']));
            $rs_id[$key]['tot_indicator']= $data['tot_indicator'];
            $rs_id[$key]['tot_variable']= $data['tot_variable'];
            $rs_id[$key]['tot_subvariable']= $data['tot_subvariable'];
            $rs_id[$key]['tot_subsubvariable']= $data['tot_subsubvariable'];
        }
        // echo "<pre>";
        // print_r($rs_id);
        // echo "</pre>";
        // exit();
        // print_r($rs_id);
        //loop tahun untuk pilihan pencarian
        $option_year = date("Y");
        $option_years = array();
        $option_year_min = 2000;
        for ($i = $option_year_min; $i <= $option_year; $i++) {
            array_push($option_years, $i);
        }
        //assign data
        $this->tsmarty->assign("option_years", array_reverse($option_years));
        $this->tsmarty->assign("option_year", $option_year);
        $this->tsmarty->assign("year_selected", $tahun);
        $this->tsmarty->assign("search", $search);
        $this->tsmarty->assign("rs_id", $rs_id);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    public function detail($instansi_cd = "", $tahun = ""){
        if($instansi_cd == "" || $tahun == "") redirect("admin/master/hakakses");

        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/hakases/detail.html");

        $search = $this->session->userdata('search_hakakses');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $search['tahun'] = $tahun;

        // echo $instansi_cd;
        // echo "<br />";
        // echo $tahun;
        // echo "<br />";
        // echo "<br />";

        //get data
        $rs_id = $this->M_urusan->get_all_data();
        foreach ($rs_id as $key => $value) {
            // echo $value;
            if($value['parent_id'] != ''){
                //get list data
                $datas = $this->M_indicator->get_indicator_for_priviliges($value['parent_id']."%");
                foreach ($datas as $keyy => $data) {
                    //get check data
                    $params =array( $data['data_id'], $tahun, $instansi_cd);
                    $datas[$keyy]['check'] = $this->M_indicator_privileges->get_priv($params);
                }
                $rs_id[$key]['lists'] = $datas;
            }
        }
        // echo "<pre>";
        // print_r($rs_id);
        // echo "</pre>";

        //assign data
        $this->tsmarty->assign("instansi_cd", $instansi_cd);
        $this->tsmarty->assign("search", $search);
        $this->tsmarty->assign("rs_id", $rs_id);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // search process
    public function search_process()
    {
        // set page rules
        $this->_set_page_rule("R");
        // session
        if ($this->input->post('process') == "process") {
            // params
            $params = array(
                "tahun" => trim(strip_tags($this->input->post('tahun', TRUE))),
            );
            // set session
            $this->session->set_userdata("search_hakakses", $params);
            // print_r("s");
        } else {
            // unset session
            $this->session->unset_userdata("search_hakakses");
            // print_r("x");
        }
        $url = "admin/master/hakakses";
        redirect($url);
    }


        // welcome operator
    public function indicator($instansi_cd, $tahun, $data_id ="")
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/hakases/indicator.html");
        //get data detail urusan
        $urusan = $this->M_urusan->get_detail_urusan(array($data_id));
        $urusan_parent = $this->M_urusan->get_detail_urusan(array($urusan['parent_id']));
        $indicators = $this->M_indicator->get_indicator_by_instansi(array($data_id, $instansi_cd));

        //get data indicator
        $rs_id = $this->M_indicator_privileges->get_indicator_by_params(array($data_id));
        foreach ($rs_id as $keyy => $data) {
            //get check data
            $params =array($data['data_id'], $tahun, $instansi_cd);
            $rs_id[$keyy]['check'] = $this->M_indicator_privileges->get_priv($params);
        }

        //assign data
        $this->tsmarty->assign("data_id", $data_id);
        $this->tsmarty->assign("urusan_id", $data_id);
        $this->tsmarty->assign("urusan", $urusan);
        $this->tsmarty->assign("urusan_parent", $urusan_parent);
        $this->tsmarty->assign("indicators", $indicators);
        $this->tsmarty->assign("instansi_cd", $instansi_cd);
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("tahun", $tahun);

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    
    public function ajukan_process()
    {
        // set page rules
        $this->_set_page_rule("C");

        //get data
        $year = trim(strip_tags($this->input->post('year', TRUE)));
        $urusan_id = trim(strip_tags($this->input->post('data_id', TRUE)));
        $instansi_cd = trim(strip_tags($this->input->post('instansi_cd', TRUE)));
        $datas =  $this->input->post('datas', TRUE);
        // $statuses =  $this->input->post('statuses', TRUE);

        // print_r("TAHUN      : " . $year);
        // print_r("<br />");
        // print_r("URUSAN CD  : " . $urusan_id);
        // print_r("<br />");
        // print_r("instansi_cd: " . $instansi_cd);
        // print_r("<br />");
        // print_r("<br />");

        //delete
        $params = array(
            "urusan_id" => $urusan_id,
            "instansi_cd" => $instansi_cd,
            "year" => $year
        );
        print_r($params);
        $this->M_indicator_privileges->delete($params);

        // print_r($datas);
        foreach ($datas as $key => $data) {
            $submission_st =  $this->input->post('submission_st_'.$key, TRUE);
            if($submission_st == 'yes'){
                //insert
                $params = array(
                    "data_id" => $data,
                    "urusan_id" => $urusan_id,
                    "year" => $year,
                    "instansi_cd" => $instansi_cd
                );
                $this->M_indicator_privileges->insert($params);
            }
        }

        // exit();
        redirect("admin/master/hakakses/indicator/" . $instansi_cd ."/".$year ."/". $urusan_id);
    }
}
