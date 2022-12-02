<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Admin extends OperatorBase
{

    // constructor
    public function __construct()
    {
        parent::__construct();
        // load model
        //$this->load->model('admin/master/M_instansi');
        $this->load->model('admin/master/M_instansi_pg');
        //$this->load->model('admin/dashboard/M_admin');
        $this->load->model('admin/dashboard/M_admin_pg');
        $this->load->model('settings/M_user');
        // load library
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    // welcome operator
    public function index()
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/dashboard/admin/index.html");


        $year = date("Y");
        $option_years = array();
        for ($i = ($year - 11); $i <= $year; $i++) {
            array_push($option_years, $i);
        }

        // get session search
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];

        if (empty($search)) {
            $search['tahun'] =  date("Y");
        }
        $this->tsmarty->assign("search", $search);

        //get data 
        $data = $this->M_admin_pg->get_count_total_data(array($tahun));
        $total        = $data['total'];
        $terisi        = $data['tot_terisi'];
        $kosong        = $data['total'] - $data['tot_terisi'];
        // $kosong        = $data['tot_kosong'];

        $rs_id = $this->M_instansi_pg->get_datas_all();

        //LOOP DATA OPD
        foreach ($rs_id as $key => $value) {
            // print_r($value);
            // GET COUNTING DATA
            $instansi_cd = $value['instansi_cd'];
            $param = array($instansi_cd, "%", $instansi_cd, "%",  $tahun);
            $data = $this->M_admin_pg->get_count_by_instansi($param);
            //print_r($data);
            // PARSING TO ARRAY DATA
            $rs_id[$key]['tot']         = $data['total'];
            $rs_id[$key]['tot_fill']    = $data['tot_fill'];
            $rs_id[$key]['tot_terisi']  = $data['tot_terisi'];
            $rs_id[$key]['tot_kosong']  = $data['total'] - $data['tot_terisi'];
            if ($data['total'] == 0) {
                $rs_id[$key]['persen'] = "0";
            } else {
                $rs_id[$key]['persen']  = $data['tot_terisi'] / $data['total'] * 100;
            }
        }

        //print_r($rs_id);
        $nomor = 1;


        //assign data
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("total", $total);
        $this->tsmarty->assign("terisi", $terisi);
        $this->tsmarty->assign("kosong", $kosong);
        $this->tsmarty->assign("nomor", $nomor);
        //$this->tsmarty->assign("get_tabel_opd", $get_tabel_opd);
        // $this->tsmarty->assign("get_opd", $get_opd);


        $this->tsmarty->assign("option_years", array_reverse($option_years));

        // user
        $this->tsmarty->assign('user', $this->com_user['user_id']);
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
            $this->session->set_userdata("search_indicator", $params);
            // print_r($params);
            // die;
        } else {
            // unset session
            $this->session->unset_userdata("search_indicator");
            // print_r("x");
        }
        // exit();
        // redirect
        redirect("admin/dashboard/admin/index");
    }
}
