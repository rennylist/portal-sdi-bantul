<?php

if (!defined('BASEPATH'))
exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/OperatorBase.php' );

class Bidang extends OperatorBase {

    // constructor
    public function __construct() {
        parent::__construct();
        // load model
        $this->load->model('admin/master/M_bidang');
        // load library
        $this->load->library('tnotification');
        $this->load->library('pagination');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    // list data
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/bidang/index.html");
        // get session search
        $search = $this->session->userdata('search_slide');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // search
        $judul = empty($search['judul']) ? '%' : '%' . $search['judul'] . '%';
        // get list data
        $params = array($judul);
        $rs_id = $this->M_bidang->get_list_data_bidang($params);
        //assign data
        $this->tsmarty->assign("rs_id", $rs_id);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // search process
    public function search_process() {
        // set page rules
        $this->_set_page_rule("R");
        // session
        if ($this->input->post('save') == "Cari") {
            // params
            $params = array(
                "judul" => $this->stripHTMLtags($this->input->post('judul', TRUE))
            );
            // set session
            $this->session->set_userdata("search_slide", $params);
        } else {
            // unset session
            $this->session->unset_userdata("search_slide");
        }
        // redirect
        redirect("admin/master/bidang");
    }

    // add form
    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/bidang/add.html");
        // load js
        $this->tsmarty->load_javascript("resource/themes/default/plugins/ckeditor/ckeditor.js");
        $this->tsmarty->load_javascript('resource/themes/default/plugins/select2/dist/js/select2.js');
        // $this->tsmarty->load_javascript('resource/themes/metronic.1.0/js/crud/metronic-datatable/base/html-table.js');
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // add data pokok process
    public function add_process() {
        // set page rules
        $this->_set_page_rule("C");
        // load library
        $this->load->library('tupload');
        // cek input
        $this->tnotification->set_rules('bidang_nm', 'Judul banner Bahasa Indonesia', 'trim|max_length[100]');
        $this->tnotification->set_rules('bidang_desc', 'Deskripsi banner Bahasa Indonesia', 'trim|max_length[255]');
        $this->tnotification->set_rules('publish_st', 'Status Ditampilkan', 'trim|required');
        // id_pokok_menu id
        $prefix = date('Y');
        $params = '%';
        $bidang_id = $this->M_bidang->get_data_bidang_last_kode($prefix, $params);
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
                'bidang_id' => $bidang_id,
                'bidang_nm' => $this->stripHTMLtags($this->input->post('bidang_nm', TRUE)),
                'bidang_desc' => ($this->input->post('bidang_desc', FALSE)),
                'publish_st' => $this->stripHTMLtags($this->input->post('publish_st', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            // insert
            if ($this->M_bidang->insert_data_bidang($params)) {
                // cek file
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
                // default redirect
                redirect("admin/master/bidang/add");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
            // default redirect
            redirect("admin/master/bidang/add");
        }
        // default redirect
        redirect("admin/master/bidang");
    }

    // edit data pokok kategori form
    public function edit($bidang_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/bidang/edit.html");
        // load js
        $this->tsmarty->load_javascript("resource/themes/default/plugins/ckeditor/ckeditor.js");
        $this->tsmarty->load_javascript('resource/themes/default/plugins/select2/dist/js/select2.js');
        // get data
        $result = $this->M_bidang->get_data_bidang_by_id(array($bidang_id));
        // echo $foto; exit();
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("admin/master/bidang");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit data pokok kategori process
    public function edit_process() {
        // set page rules
        $this->_set_page_rule("U");
        // load library
        $this->load->library('tupload');
        // cek input
        $this->tnotification->set_rules('bidang_nm', 'Judul banner Bahasa Indonesia', 'trim|max_length[100]');
        $this->tnotification->set_rules('bidang_desc', 'Deskripsi banner Bahasa Indonesia', 'trim|max_length[255]');
        $this->tnotification->set_rules('publish_st', 'Status Ditampilkan', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {

            // params
            $params = array(
                'bidang_nm' => $this->stripHTMLtags($this->input->post('bidang_nm', TRUE)),
                'bidang_desc' => ($this->input->post('bidang_desc', FALSE)),
                'publish_st' => $this->stripHTMLtags($this->input->post('publish_st', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'bidang_id' => $this->stripHTMLtags($this->input->post('bidang_id', TRUE))
            );
            // update
            if ($this->M_bidang->update_data_bidang($params, $where)) {
                // cek file
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
        redirect("admin/master/bidang/edit/" . $this->input->post('bidang_id', TRUE));
    }

    // delete data form
    public function delete($bidang_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/bidang/delete.html");
        // get data
        $result = $this->M_bidang->get_data_bidang_by_id(array($bidang_id));
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("admin/master/bidang");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // delete process
    public function delete_process() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('bidang_id', 'ID Data bidang', 'trim|required|max_length[10]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'bidang_id' => $this->stripHTMLtags($this->input->post('bidang_id', TRUE))
            );
            // delete
            if ($this->M_bidang->delete_data_bidang($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("admin/master/bidang");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("admin/master/bidang/delete/" . $this->input->post('bidang_id'));
    }
}
