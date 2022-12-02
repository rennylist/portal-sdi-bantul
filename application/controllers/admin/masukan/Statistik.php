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
        $this->load->model('admin/masukan/M_statistik_pg');
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
        $this->tsmarty->assign("template_content", "admin/masukan/statistik/add_urusan.html");

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // get last field
        //$result = $this->tnotification->get_field_data();
        //$parent_selected = isset($result['parent_id']['postdata']) ? $result['parent_id']['postdata'] : 0;
        // get list parent
        $rs_id = $this->M_statistik_pg->get_all_menu_by_parent();
        $this->tsmarty->assign("rs_id", $rs_id);

        //print_r($rs_id);

        // output
        parent::display();
    }


    // proses tambah
    public function add_process()
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

            // insert
            if ($this->M_statistik_pg->insert_urusan($params)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/masukan/statistik");
    }

    public function add_indicator()
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/masukan/statistik/add_indicator.html");

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // get last field
        //$result = $this->tnotification->get_field_data();
        //$parent_selected = isset($result['parent_id']['postdata']) ? $result['parent_id']['postdata'] : 0;
        // get list parent
        $urusan_id = $this->M_statistik_pg->get_list_urusan_id();
        //$parent_indicator = $this->M_statistik_pg->get_list_parent_indicator();
        $instansi = $this->M_statistik_pg->get_intsansi();

        $this->tsmarty->assign("urusan_id", $urusan_id);
        $this->tsmarty->assign("instansi", $instansi);

        //print_r($rs_id);

        // output
        parent::display();
    }

    public function select_induk_indicator()
    {
        $id_urusan =  $this->input->post('id_urusan', TRUE);
        $params = array($id_urusan);
        // $indicator_parent = $this->input->post('id', TRUE);

        $data = $this->M_statistik_pg->get_parent_indicator($params);

        echo json_encode($data);
    }

    // proses tambah
    public function add_indicator_process()
    {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('kode_urusan', 'Kode Urusan', 'trim|required');
        $this->tnotification->set_rules('induk_indikator', 'Induk Indikator', 'trim|required');
        $this->tnotification->set_rules('kode_indikator', 'Kode Indikator/Variabel/Sub Variabel/Sub-sub Variabel', 'trim|required');
        $this->tnotification->set_rules('nama_indikator', 'Nama Indikator/Variabel/Sub Variabel/Sub-sub Variabel', 'trim|required');
        $this->tnotification->set_rules('unit', 'Unit/Satuan', 'trim|required');
        $this->tnotification->set_rules('type_indikator', 'Type Indikator', 'trim|required');
        $this->tnotification->set_rules('instansi', 'Instansi', 'trim|required');

        $this->tnotification->set_rules('sdgs', 'Indikator SDGs', 'trim|required');
        $this->tnotification->set_rules('rpjmd', 'Indikator RPJMD', 'trim|required');
        $this->tnotification->set_rules('ikklppd', 'IKK LPPD', 'trim|required');
        $this->tnotification->set_rules('spm', 'Indikator SPM', 'trim|required');
        $this->tnotification->set_rules('dda', 'Data Daerah Dalam Angka', 'trim|required');
        $this->tnotification->set_rules('datakudiy', 'Aplikasi Dataku DIY', 'trim|required');
        $this->tnotification->set_rules('gender', 'Data Pilah Gender', 'trim|required');
        $this->tnotification->set_rules('sektoral', 'Statistik Sektoral', 'trim|required');
        $this->tnotification->set_rules('spasial', 'Geospasial', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {

            $parent_id = $this->input->post('induk_indikator', TRUE);
            if ($parent_id == "0") {
                // params
                $params = array(
                    'urusan_id' => $this->input->post('kode_urusan', TRUE),
                    'parent_id' => $this->input->post('kode_indikator', TRUE),
                    'data_id' => $this->input->post('kode_indikator', TRUE),
                    'data_name' => $this->input->post('nama_indikator', TRUE),
                    'data_unit' => $this->input->post('unit', TRUE),
                    'data_type' => $this->input->post('type_indikator', TRUE),
                    'instansi_cd' => $this->input->post('instansi', TRUE),
                    'class_sdgs' => $this->input->post('sdgs', TRUE),
                    'class_rpjmd' => $this->input->post('rpjmd', TRUE),
                    'class_ikklppd' => $this->input->post('ikklppd', TRUE),
                    'class_spm' => $this->input->post('spm', TRUE),
                    'class_dda' => $this->input->post('dda', TRUE),
                    'class_datakudiy' => $this->input->post('datakudiy', TRUE),
                    'class_pilahgender' => $this->input->post('gender', TRUE),
                    'class_sektoral' => $this->input->post('sektoral', TRUE),
                    'class_geospasial' => $this->input->post('spasial', TRUE),
                    'rumus_type' => $this->input->post('rumus_type', TRUE),
                    'rumus_detail' => $this->input->post('rumus_detail', TRUE),
                    'active_st' => "yes",
                    'mdb_name' => $this->com_user['user_alias'],
                    'mdb' => $this->com_user['user_id'],
                    'mdd' => date("Y-m-d H:i:s")
                );
                // print_r($params);
                // die;
            } else {

                // params
                $params = array(
                    'urusan_id' => $this->input->post('kode_urusan', TRUE),
                    'parent_id' => $this->input->post('induk_indikator', TRUE),
                    'data_id' => $this->input->post('kode_indikator', TRUE),
                    'data_name' => $this->input->post('nama_indikator', TRUE),
                    'data_unit' => $this->input->post('unit', TRUE),
                    'data_type' => $this->input->post('type_indikator', TRUE),
                    'instansi_cd' => $this->input->post('instansi', TRUE),
                    'class_sdgs' => $this->input->post('sdgs', TRUE),
                    'class_rpjmd' => $this->input->post('rpjmd', TRUE),
                    'class_ikklppd' => $this->input->post('ikklppd', TRUE),
                    'class_spm' => $this->input->post('spm', TRUE),
                    'class_dda' => $this->input->post('dda', TRUE),
                    'class_datakudiy' => $this->input->post('datakudiy', TRUE),
                    'class_pilahgender' => $this->input->post('gender', TRUE),
                    'class_sektoral' => $this->input->post('sektoral', TRUE),
                    'class_geospasial' => $this->input->post('spasial', TRUE),
                    'rumus_type' => $this->input->post('rumus_type', TRUE),
                    'rumus_detail' => $this->input->post('rumus_detail', TRUE),
                    'active_st' => "yes",
                    'mdb_name' => $this->com_user['user_alias'],
                    'mdb' => $this->com_user['user_id'],
                    'mdd' => date("Y-m-d H:i:s")
                );
            }

            // print_r($params);
            // die;

            // insert
            if ($this->M_statistik_pg->insert_indikator($params)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/masukan/statistik/add_indicator");
    }
}
