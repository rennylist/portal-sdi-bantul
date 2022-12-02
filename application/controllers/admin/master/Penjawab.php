<?php

if (!defined('BASEPATH'))
exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/OperatorBase.php' );

class Penjawab extends OperatorBase {

    // constructor
    public function __construct() {
        parent::__construct();
        // load model
        $this->load->model('admin/master/M_penjawab');
        // load library
        $this->load->library('tnotification');
        $this->load->library('pagination');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
        // load js
        // $this->tsmarty->load_javascript('resource/js/bootstrap-tagsinput/bootstrap-tagsinput.js');
        $this->tsmarty->load_javascript("resource/themes/default/plugins/ckeditor/ckeditor.js");
        $this->tsmarty->load_javascript('resource/themes/default/plugins/select2/dist/js/select2.js');
        // load style
        // $this->tsmarty->load_style('metronic.1.0/css/bootstrap-tagsinput/bootstrap-tagsinput.css');
        // $this->tsmarty->load_style('metronic.1.0/css/bootstrap-tagsinput/bootstrap-min.css');
    }

    // list data
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/penjawab/index.html");
        // get session search
        $search = $this->session->userdata('search_slide');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // search
        $judul = empty($search['judul']) ? '%' : '%' . $search['judul'] . '%';
        // get list data
        $params = array($judul, "%");
        $rs_id = $this->M_penjawab->get_datas_params($params);
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
        redirect("admin/master/penjawab");
    }

    // add form
    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/penjawab/add.html");
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
        $this->tnotification->set_rules('penjawab_cat', 'Kategori', 'required|trim|strip_tags|max_length[250]');
        $this->tnotification->set_rules('penjawab_nm', 'Judul', 'required|trim|strip_tags|max_length[250]');
        $this->tnotification->set_rules('penjawab_desc', 'Deskripsi', 'trim|strip_tags|max_length[100000]');
        $this->tnotification->set_rules('publish_st', 'Status Ditampilkan', 'required|trim|strip_tags');
        // id_pokok_menu id
        $prefix = date('Y');
        $params = '%';
        $penjawab_id = $this->M_penjawab->get_penjawab_last_kode($prefix, $params);
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
                'penjawab_id' => $penjawab_id,
                'penjawab_cat' => $this->stripHTMLtags($this->input->post('penjawab_cat', TRUE)),
                'penjawab_nm' => $this->stripHTMLtags($this->input->post('penjawab_nm', TRUE)),
                'penjawab_desc' => ($this->input->post('penjawab_desc', FALSE)),
                'publish_st'=> $this->stripHTMLtags($this->input->post('publish_st', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            // insert
            if ($this->M_penjawab->insert($params)) {
                // cek file
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
                // default redirect
                redirect("admin/master/penjawab/add");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
            // default redirect
            redirect("admin/master/penjawab/add");
        }
        // default redirect
        redirect("admin/master/penjawab/add");
    }

    // edit data pokok kategori form
    public function edit($penjawab_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/penjawab/edit.html");
        // get data
        $result = $this->M_penjawab->get_data_by_id(array($penjawab_id));
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("admin/master/penjawab");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        $this->tsmarty->assign("penjawab_id", $penjawab_id);
     
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
        $this->tnotification->set_rules('penjawab_cat', 'Kategori', 'required|trim|strip_tags|max_length[250]');
        $this->tnotification->set_rules('penjawab_nm', 'Judul', 'required|trim|strip_tags|max_length[250]');
        $this->tnotification->set_rules('penjawab_desc', 'Deskripsi', 'trim|strip_tags|max_length[100000]');
        $this->tnotification->set_rules('publish_st', 'Status Ditampilkan', 'required|trim|strip_tags');
        // process
        if ($this->tnotification->run() !== FALSE) {
           
            // params
            $params = array(
                'penjawab_cat' => $this->stripHTMLtags($this->input->post('penjawab_cat', TRUE)),
                'penjawab_nm' => $this->stripHTMLtags($this->input->post('penjawab_nm', TRUE)),
                'penjawab_desc' => ($this->input->post('penjawab_desc', FALSE)),
                'publish_st'=> $this->stripHTMLtags($this->input->post('publish_st', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'penjawab_id' => $this->stripHTMLtags($this->input->post('penjawab_id', TRUE))
            );
            // update
            if ($this->M_penjawab->update($params, $where)) {
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
        redirect("admin/master/penjawab/edit/" . $this->input->post('penjawab_id', TRUE));
    }

    // delete data form
    public function delete($penjawab_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/penjawab/delete.html");
        // get data
        $result = $this->M_penjawab->get_data_by_id(array($penjawab_id));
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("admin/master/penjawab");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        $this->tsmarty->assign("penjawab_id", $penjawab_id);
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
        $this->tnotification->set_rules('penjawab_id', 'ID Data Pages', 'trim|required|max_length[10]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'penjawab_id' => $this->stripHTMLtags($this->input->post('penjawab_id', TRUE))
            );
            // delete
            if ($this->M_penjawab->delete($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("admin/master/penjawab");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("admin/master/penjawab/delete/" . $this->input->post('penjawab_id'));
    }


    private function _get_id() {
        $micro = explode(' ', microtime());
        $micro[0] = preg_replace('/(\ |,|\.)/', '', $micro[0]);
        return $micro[1].$micro[0];
    }
}
