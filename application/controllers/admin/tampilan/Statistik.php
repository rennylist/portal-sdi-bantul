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
        $this->load->model('settings/sistem/M_settings');
        $this->load->model('settings/M_user');
        $this->load->model('admin/tampilan/M_statistik_pg');
        // LOAD LIBRARY
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    public function index()
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/tampilan/statistik/index.html");

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // get last field
        //$result = $this->tnotification->get_field_data();
        //$parent_selected = isset($result['parent_id']['postdata']) ? $result['parent_id']['postdata'] : 0;
        // get list parent
        $rs_id = $this->M_statistik_pg->get_list_urusan_by_parent();
        $this->tsmarty->assign("rs_id", $rs_id);

        //print_r($rs_id);

        // output
        parent::display();
    }

    public function urusan()
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/tampilan/statistik/urusan.html");

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // get last field
        //$result = $this->tnotification->get_field_data();
        //$parent_selected = isset($result['parent_id']['postdata']) ? $result['parent_id']['postdata'] : 0;
        // get list parent
        $rs_id = $this->M_statistik_pg->get_list_urusan();
        $this->tsmarty->assign("rs_id", $rs_id);

        //print_r($rs_id);

        // output
        parent::display();
    }

    public function indikator()
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/tampilan/statistik/indikator.html");

        // GET SESSION SEARCH
        $search = $this->session->userdata('search_indicator');
        if (empty($search)) {
            $urusan_id = '1';
            $search['urusan_id'] = '1';
            $params =  array($search['urusan_id'] . "%");
        }
        $this->tsmarty->assign("search", $search);
        //$params = array($search . "%");
        $urusan_id = $search['urusan_id'];
        $params =  array($urusan_id);
        //die;
        //GET DATA
        $induk_urusan = $this->M_statistik_pg->get_list_urusan_by_parent();
        $rs_id = $this->M_statistik_pg->get_list_indikator_by_urusan($params);

        // ASSIGN DATA
        $this->tsmarty->assign("induk_urusan", $induk_urusan);
        $this->tsmarty->assign("rs_id", $rs_id);

        // output
        parent::display();
    }

    public function edit_urusan($urusan_id)
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/tampilan/statistik/edit_urusan.html");

        // GET URUSAN BY ID
        $list_urusan = $this->M_statistik_pg->get_list_urusan_by_id(array($urusan_id));
        $rs_id = $this->M_statistik_pg->get_urusan_by_id(array($urusan_id));

        // ASSIGN DATA
        $this->tsmarty->assign("list_urusan", $list_urusan);
        $this->tsmarty->assign("rs_id", $rs_id);

        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // OUTPUT
        parent::display();
    }

    public function edit_indikator($data_id)
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // SET TEMPLATE CONTENT
        $this->tsmarty->assign("template_content", "admin/tampilan/statistik/edit_indikator.html");

        // GET URUSAN BY ID
        $urusan_id = $this->M_statistik_pg->get_list_urusan_by_parent();
        // $list_urusan = $this->M_statistik_pg->get_list_urusan_by_id(array($data_id));
        $rs_id = $this->M_statistik_pg->get_data_indicator(array($data_id));

        //print_r($rs_id);

        $instansi = $this->M_statistik_pg->get_intsansi();

        // // ASSIGN DATA
        $this->tsmarty->assign("urusan_id", $urusan_id);
        $this->tsmarty->assign("instansi", $instansi);
        // $this->tsmarty->assign("list_urusan", $list_urusan);
        $this->tsmarty->assign("rs_id", $rs_id);

        // NOTIFICATION
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // OUTPUT
        parent::display();
    }

    // proses edit
    public function edit_urusan_process()
    {

        // set page rules
        $this->_set_page_rule("C");

        // cek input
        $this->tnotification->set_rules('parent_id', 'Induk Urusan', 'trim|required');
        $this->tnotification->set_rules('kode_urusan', 'Kode Urusan', 'trim|required');
        $this->tnotification->set_rules('nama_urusan', 'Nama Urusan', 'trim|required');

        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
                'parent_id' => $this->input->post('parent_id', TRUE),
                'urusan_id' => $this->input->post('kode_urusan', TRUE),
                'urusan_name' => $this->input->post('nama_urusan', TRUE),
                'active_st' => "yes",
                'mdb_name' => $this->com_user['user_alias'],
                'mdb' => $this->com_user['user_id'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'urusan_id' => $this->stripHTMLtags($this->input->post('urusan_id', TRUE))
            );

            if ($this->M_statistik_pg->update_urusan($params, $where)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
                // redirect
                redirect("admin/tampilan/statistik/edit_urusan/" . $this->input->post('kode_urusan'));
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/tampilan/statistik/edit_urusan/" . $this->input->post('kode_urusan'));
    }

    // proses edit
    public function edit_indicator_process()
    {

        // set page rules
        $this->_set_page_rule("C");

        // cek input
        $this->tnotification->set_rules('kode_urusan', 'Kode Urusan', 'trim|required');
        $this->tnotification->set_rules('induk_indikator', 'Induk Indikator', 'trim|required');
        $this->tnotification->set_rules('kode_indikator', 'Kode Indikatorr/Variabel/Sub Variabel/Sub-sub Variabel', 'trim|required');
        $this->tnotification->set_rules('nama_indikator', 'Nama Indikatorr/Variabel/Sub Variabel/Sub-sub Variabel', 'trim|required');
        $this->tnotification->set_rules('unit', 'Unit', 'trim|required');
        $this->tnotification->set_rules('type_indikator', 'Tingkatan Data', 'trim|required');
        $this->tnotification->set_rules('instansi', 'Instansi', 'trim|required');
        // $this->tnotification->set_rules('rumus_type', 'Rumus Type', 'trim|required');
        $this->tnotification->set_rules('sdgs', 'Indikator SDGS', 'trim|required');
        $this->tnotification->set_rules('rpjmd', 'Indikator RPJMD', 'trim|required');
        $this->tnotification->set_rules('ikklppd', 'IKK LPPD', 'trim|required');
        $this->tnotification->set_rules('spm', 'Indikator SPM', 'trim|required');
        $this->tnotification->set_rules('dda', 'Data Daerah Dalam Angka', 'trim|required');
        $this->tnotification->set_rules('datakudiy', 'Data Aplikasi Dataku DIY', 'trim|required');
        $this->tnotification->set_rules('gender', 'Data Pilah Gender', 'trim|required');
        $this->tnotification->set_rules('sektoral', 'Statistik Sektoral', 'trim|required');
        $this->tnotification->set_rules('spasial', 'Geospasial', 'trim|required');


        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(

                'urusan_id' => $this->input->post('kode_urusan', TRUE),
                'parent_id' => $this->input->post('induk_indikator', TRUE),
                'data_id' => $this->input->post('kode_indikator', TRUE),
                'data_name' => $this->input->post('nama_indikator', TRUE),
                'data_unit' => $this->input->post('unit', TRUE),
                'instansi_cd' => $this->input->post('instansi', TRUE),
                'rumus_type' => $this->input->post('rumus_type', TRUE),
                'rumus_detail' => $this->input->post('rumus_detail', TRUE),
                'class_sdgs' => $this->input->post('sdgs', TRUE),
                'class_rpjmd' => $this->input->post('rpjmd', TRUE),
                'class_ikklppd' => $this->input->post('ikklppd', TRUE),
                'class_spm' => $this->input->post('spm', TRUE),
                'class_dda' => $this->input->post('dda', TRUE),
                'class_datakudiy' => $this->input->post('datakudiy', TRUE),
                'class_pilahgender' => $this->input->post('gender', TRUE),
                'class_sektoral' => $this->input->post('sektoral', TRUE),
                'class_geospasial' => $this->input->post('spasial', TRUE),
                'active_st' => "yes",
                'mdb_name' => $this->com_user['user_alias'],
                'mdb' => $this->com_user['user_id'],
                'mdd' => date("Y-m-d H:i:s")
            );

            $where = array(
                'data_id' => $this->stripHTMLtags($this->input->post('data_id', TRUE))
            );

            // print_r($where);
            // die;

            if ($this->M_statistik_pg->update_indicator($params, $where)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
                // redirect
                redirect("admin/tampilan/statistik/edit_indikator/" . $this->input->post('data_id'));
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/tampilan/statistik/edit_indikator/" . $this->input->post('data_id'));
    }


    // delete process
    public function delete_urusan_process($urusan_id)
    {
        // set page rules
        $this->_set_page_rule("D");

        $where = array(
            'urusan_id' => $urusan_id
        );

        // delete
        if ($this->M_statistik_pg->delete_urusan($where)) {
            $this->tnotification->delete_last_field();
            $this->tnotification->sent_notification("success", "Data berhasil dihapus");
            // default redirect
            redirect("admin/tampilan/statistik/urusan");
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }

        // default redirect
        redirect("admin/tampilan/statistik/urusan");
    }

    public function search_process()
    {
        // SET PAGE RULES
        $this->_set_page_rule("R");
        // CEK INPUT PROCESS EMPTY OR NOT
        if ($this->input->post('process') == "process") {
            // SET PARAMS
            $params = array(
                "urusan_id" => trim(strip_tags($this->input->post('urusan_id', TRUE))),
                "indikator_id" => trim(strip_tags($this->input->post('induk_indikator', TRUE))),

            );
            // GET SESSION
            // print_r($params);
            // die;
            $this->session->set_userdata("search_indicator", $params);
        } else {
            // UNSET SESSION
            $this->session->unset_userdata("search_indicator");
        }

        // REDIRECT
        $url = "admin/tampilan/statistik/indikator";
        redirect($url);
    }

    public function select_induk_indicator()
    {
        $id_urusan =  $this->input->post('id_urusan', TRUE);
        $params = array($id_urusan);
        // $indicator_parent = $this->input->post('id', TRUE);

        $data = $this->M_statistik_pg->get_parent_indicator($params);

        echo json_encode($data);
    }
}
