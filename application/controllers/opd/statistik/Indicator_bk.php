<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Indicator extends OperatorBase
{

    // constructor
    public function __construct()
    {
        parent::__construct();
        // load model
        $this->load->model('admin/statistik/M_urusan');
        $this->load->model('admin/statistik/M_indicator');
        $this->load->model('admin/statistik/M_indicator_data');
        $this->load->model('settings/M_user');

        // load library
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    // welcome operator
    public function index($data_id)
    {
        $user_id = $this->com_user['user_id'];
        $result_user = $this->M_user->get_data_user_by_id($user_id);
        //print_r($result_user);
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/statistik/indicator/index.html");
        //get data
        $rs_id = $this->M_urusan->get_list_urusan(array($data_id . '%'));
        //print_r($rs_id);

        $year = date("Y");
        $select_tahun = array();
        for ($i = ($year -  11); $i <= $year; $i++) {
            array_push($select_tahun, $i);
        }

        //assign data
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("select_tahun", $select_tahun);
        // user
        $this->tsmarty->assign('user', $this->com_user['user_id']);
        $this->tsmarty->assign("result_user", $result_user);
        // output
        parent::display();
    }

    // welcome operator
    public function indicator($data_id)
    {
        // get session search
        $search = $this->session->userdata('search_indicator');
        $tahun = empty($search['tahun']) ? date("Y") : $search['tahun'];
        $indikator = empty($search['indikator']) ? 0 : $search['indikator'];
        if ($indikator == 0) {
            //echo "semua";
            //get data indicator
            $rs_id = $this->M_indicator->get_variable(array($data_id . '%'));
        }
        if ($indikator == 1) {

            //echo "satu";
            $rs_id = $this->M_indicator->get_variable_1(array($data_id . '%'));
        }
        if ($indikator == 2) {

            //echo "satu";
            $rs_id = $this->M_indicator->get_variable(array($data_id . '%'));
        }
        if ($indikator == 3) {

            //echo "satu";
            $rs_id = $this->M_indicator->get_variable(array($data_id . '%'));
        }
        if ($indikator == 4) {

            //echo "satu";
            $rs_id = $this->M_indicator->get_variable(array($data_id . '%'));
        }
        if ($indikator == 5) {

            //echo "satu";
            $rs_id = $this->M_indicator->get_variable(array($data_id . '%'));
        }
        if ($indikator == 6) {

            //echo "satu";
            $rs_id = $this->M_indicator->get_variable(array($data_id . '%'));
        }
        if ($indikator == 7) {

            //echo "satu";
            $rs_id = $this->M_indicator->get_variable(array($data_id . '%'));
        }

        $year_now = date("Y");
        $select_tahun = array();
        for ($i = ($year_now -  11); $i <= $year_now; $i++) {
            array_push($select_tahun, $i);
        }

        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/statistik/indicator/indicator.html");
        //clean
        $data_id = trim(strip_tags($data_id));
        if (empty($data_id)) {
            redirect("admin/statistik/urusan");
        }
        //init
        $year = $tahun;
        $year_min = 4;

        foreach ($rs_id as $key => $value) {
            for ($i = ($year - 4); $i <= $year; $i++) {
                $data = $this->M_indicator->get_data_tahun(array($value['data_id'], $i));
                // array_push($datas, $data);
                // $nilai = if(empty($data['nilai'])) $nilai : 0;
                // $nilai = empty($data['nilai']) ? $$data['nilai'] : 'new text value';
                $nilai = (!isset($data['value']) || empty($data['value'])) ? '' : $data['value'];
                $rs_id[$key][$i] =  $nilai;
            }
        }
        // print_r($value['data_id']);
        //get year for array
        $years = array();
        for ($i = ($year -  $year_min); $i <= $year; $i++) {
            array_push($years, $i);
        }

        // if (empty($rs_id)) {
        //     redirect("admin/statistik/urusan");
        //  }
        if (!empty($_SESSION["success"])) {
            $sukses = $_SESSION["success"];
            unset($_SESSION["success"]);
            $this->tsmarty->assign("status", $sukses);
        }
        //assign data
        $this->tsmarty->assign("urusan_name", $search['urusan_name']);
        $this->tsmarty->assign("tahun_id", $tahun);
        $this->tsmarty->assign("urusan_id", $data_id);
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("years", $years);
        $this->tsmarty->assign("year_selected", $year);
        $this->tsmarty->assign("select_tahun", $select_tahun);
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
                "tahun" => $this->stripHTMLtags($this->input->post('tahun', TRUE)),
                "indikator" => $this->stripHTMLtags($this->input->post('indikator', TRUE)),
                "urusan_id" => $this->stripHTMLtags($this->input->post('urusan_id', TRUE)),
                "urusan_name" => $this->stripHTMLtags($this->input->post('urusan_name', TRUE))
            );
            // set session
            $this->session->set_userdata("search_indicator", $params);
            // print_r("s");
        } else {
            // unset session
            $this->session->unset_userdata("search_indicator");
            // print_r("x");
        }
        // exit();
        // redirect
        redirect("admin/statistik/indicator/indicator/" . $this->stripHTMLtags($this->input->post('urusan_id', TRUE)));
    }

    public function ajukan_process()
    {

        // set page rules
        $this->_set_page_rule("R");

        // session
        if ($this->input->post('process') == "process") {

            $tahun = array(
                "year" => $this->stripHTMLtags($this->input->post('tahun_id', TRUE))
            );

            $query = $this->M_indicator_data->get_list_by_params($tahun);

            if ($query->num_rows() > 0) {

                //Update data 
                $urusan_id = $this->stripHTMLtags($this->input->post('urusan_id', TRUE));
                $data = $this->input->post();
                for ($i = 0; $i < count($data['kode']); $i++) {
                    $params[] = array(
                        'data_id' => $data['kode'][$i],
                        'value' => $data['data_tahun'][$i]

                    );
                }

                // print_r("<pre>");
                // print_r($query);
                // print_r("</pre>");
                // die;
                //$kode = $this->input->post('kode');
                // foreach ($kode as $key => $val) {
                //     $params[] = array(
                //         "data_id" => $kode[$key],
                //         "value"  => $_POST['data_tahun'][$key]
                //         // "valid_st"  => "yes",
                //         // "submission_st"  => "pending",
                //         // "mdd"  => $mdd
                //     );
                // }
                // print_r("<pre>");
                // print_r($params);
                // print_r("</pre>");
                $where =  $this->stripHTMLtags($this->input->post('tahun_id', TRUE));
                $this->M_indicator_data->update_data($params, $where);

                session_start();
                $_SESSION["success"] = '<div class="alert alert-success" role="alert">
                <strong>Sukses!</strong> Data berhasil diajukan
                </div>';
                //header('Location: ' . site_url('admin/statistik/indicator/indicator/' . $urusan_id));
                redirect("admin/statistik/indicator/indicator/" . $urusan_id);
            } else {
                //Insert data 
                //print_r("x");
                // $id = $this->input->post('id');
                // $tgl_now = date("Y-m-d h:i:s");

                // foreach ($id as $key => $val) {
                //     $result[] = array(
                //         "kode_urusan_indikator" => $id[$key],
                //         "tahun" => $tahun,
                //         "nilai"  => $_POST['data_tahun'][$key],
                //         "status_data"  => "Sementara",
                //         "tgl_data"  => $tgl_now,
                //         "status_pengajuan"  => "Menunggu"
                //     );
                // }
                // // $this->db->where('tahun', $tahun);
                // $this->db->insert_batch('tb_data', $result);


                $year = $this->stripHTMLtags($this->input->post('tahun_id', TRUE));
                $urusan_id = $this->stripHTMLtags($this->input->post('urusan_id', TRUE));
                $data = $this->input->post();
                for ($i = 0; $i < count($data['kode']); $i++) {
                    $params[] = array(
                        'data_id' => $data['kode'][$i],
                        'value' => $data['data_tahun'][$i],
                        "year" => $year

                    );
                }
                $this->M_indicator_data->insert_data($params);
                session_start();
                $_SESSION["success"] = '<div class="alert alert-success" role="alert">
                <strong>Sukses!</strong> Data berhasil diajukan
                </div>';
                redirect("admin/statistik/indicator/indicator/" . $urusan_id);
            }
        } else {
            // unset session
            //$this->session->unset_userdata("search_indicator");
            print_r("y");
        }
    }
}
