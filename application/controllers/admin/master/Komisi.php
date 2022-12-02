<?php

if (!defined('BASEPATH'))
exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/OperatorBase.php' );

class Komisi extends OperatorBase {

    // constructor
    public function __construct() {
        parent::__construct();
        // load model
        $this->load->model('admin/master/M_komisi');
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
        $this->tsmarty->assign("template_content", "admin/master/komisi/index.html");
        // get session search
        $search = $this->session->userdata('search_slide');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // search
        $judul = empty($search['judul']) ? '%' : '%' . $search['judul'] . '%';
        // get list data
        $params = array($judul);
        $rs_id = $this->M_komisi->get_list_data_komisi($params);
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
        redirect("admin/master/komisi");
    }

    // add form
    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/komisi/add.html");
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
        $this->tnotification->set_rules('komisi_nm', 'Judul banner Bahasa Indonesia', 'trim|max_length[100]');
        $this->tnotification->set_rules('komisi_desc', 'Deskripsi banner Bahasa Indonesia', 'trim|max_length[255]');
        $this->tnotification->set_rules('publish_st', 'Status Ditampilkan', 'trim|required');
        // id_pokok_menu id
        $prefix = date('Y');
        $params = '%';
        $komisi_id = $this->M_komisi->get_data_komisi_last_kode($prefix, $params);
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
                'komisi_id' => $komisi_id,
                'komisi_nm' => $this->stripHTMLtags($this->input->post('komisi_nm', TRUE)),
                'komisi_desc' => ($this->input->post('komisi_desc', FALSE)),
                'publish_st' => $this->stripHTMLtags($this->input->post('publish_st', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            // insert
            if ($this->M_komisi->insert_data_komisi($params)) {
                // cek file
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
                // default redirect
                redirect("admin/master/komisi/add");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
            // default redirect
            redirect("admin/master/komisi/add");
        }
        // default redirect
        redirect("admin/master/komisi");
    }

    // edit data pokok kategori form
    public function edit($komisi_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/komisi/edit.html");
        // load js
        $this->tsmarty->load_javascript("resource/themes/default/plugins/ckeditor/ckeditor.js");
        $this->tsmarty->load_javascript('resource/themes/default/plugins/select2/dist/js/select2.js');
        // get data
        $result = $this->M_komisi->get_data_komisi_by_id(array($komisi_id));
        // echo $foto; exit();
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("admin/master/komisi");
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
        $this->tnotification->set_rules('komisi_nm', 'Judul banner Bahasa Indonesia', 'trim|max_length[100]');
        $this->tnotification->set_rules('komisi_desc', 'Deskripsi banner Bahasa Indonesia', 'trim|max_length[255]');
        $this->tnotification->set_rules('publish_st', 'Status Ditampilkan', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {

            // params
            $params = array(
                'komisi_nm' => $this->stripHTMLtags($this->input->post('komisi_nm', TRUE)),
                'komisi_desc' => ($this->input->post('komisi_desc', FALSE)),
                'publish_st' => $this->stripHTMLtags($this->input->post('publish_st', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'komisi_id' => $this->stripHTMLtags($this->input->post('komisi_id', TRUE))
            );
            // update
            if ($this->M_komisi->update_data_komisi($params, $where)) {
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
        redirect("admin/master/komisi/edit/" . $this->input->post('komisi_id', TRUE));
    }

    // delete data form
    public function delete($komisi_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/komisi/delete.html");
        // get data
        $result = $this->M_komisi->get_data_komisi_by_id(array($komisi_id));
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("admin/master/komisi");
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
        $this->tnotification->set_rules('komisi_id', 'ID Data komisi', 'trim|required|max_length[10]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'komisi_id' => $this->stripHTMLtags($this->input->post('komisi_id', TRUE))
            );
            // delete
            if ($this->M_komisi->delete_data_komisi($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("admin/master/komisi");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("admin/master/komisi/delete/" . $this->input->post('komisi_id'));
    }
}
