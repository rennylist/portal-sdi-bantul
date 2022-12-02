<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

// --

class Portal extends AdminBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('settings/sistem/M_settings');
        // load library
        $this->load->library('tnotification');
    }

    // list data
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/portal/index.html");
        // get data
        $this->tsmarty->assign("rs_id", $this->M_settings->get_all_portal());
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // add form
    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/portal/add.html");
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // add process
    public function add_process() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('portal_nm', 'Nama Portal', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('site_title', 'Judul Web', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('site_desc', 'Deskripsi Web', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('meta_desc', 'Meta Deskripsi', 'trim|required|max_length[255]');
        $this->tnotification->set_rules('meta_keyword', 'Meta Keyword', 'trim|required|max_length[255]');
        // portal id
        $portal_id = $this->M_settings->get_portal_last_id();
        if (!$portal_id) {
            $this->tnotification->set_error_message('ID is not available');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'portal_id' => $portal_id,
                'portal_nm' => $this->input->post('portal_nm', TRUE),
                'site_title' => $this->input->post('site_title', TRUE),
                'site_desc' => $this->input->post('site_desc', TRUE),
                'meta_desc' => $this->input->post('meta_desc', TRUE),
                'meta_keyword' => $this->input->post('meta_keyword', TRUE),
                'create_by' => $this->com_user['user_id'],
                'create_date' => date("Y-m-d H:i:s")
            );
            // insert
            if ($this->M_settings->insert_portal($params)) {
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
        redirect("settings/sistem/portal/add");
    }

    // edit form
    public function edit($portal_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/portal/edit.html");
        // get data
        $result = $this->M_settings->get_portal_by_id($portal_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/portal");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit process
    public function edit_process() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('portal_id', 'Portal ID', 'trim|required|max_length[2]');
        $this->tnotification->set_rules('portal_nm', 'Nama Portal', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('site_title', 'Judul Web', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('site_desc', 'Deskripsi Web', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('meta_desc', 'Meta Deskripsi', 'trim|required|max_length[255]');
        $this->tnotification->set_rules('meta_keyword', 'Meta Keyword', 'trim|required|max_length[255]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'portal_nm' => $this->input->post('portal_nm', TRUE),
                'site_title' => $this->input->post('site_title', TRUE),
                'site_desc' => $this->input->post('site_desc', TRUE),
                'meta_desc' => $this->input->post('meta_desc', TRUE),
                'meta_keyword' => $this->input->post('meta_keyword', TRUE)
            );
            $where = array(
                'portal_id' => $this->input->post('portal_id', TRUE)
            );
            // update
            if ($this->M_settings->update_portal($params, $where)) {
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
        redirect("settings/sistem/portal/edit/" . $this->input->post('portal_id', TRUE));
    }

    // hapus form
    public function delete($portal_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/portal/delete.html");
        // get data
        $result = $this->M_settings->get_portal_by_id($portal_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/portal");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // hapus process
    public function delete_process() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('portal_id', 'Portal ID', 'trim|required|max_length[2]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'portal_id' => $this->input->post('portal_id', TRUE)
            );
            // delete
            if ($this->M_settings->delete_portal($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("settings/sistem/portal");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("settings/sistem/portal/delete/" . $this->input->post('portal_id'));
    }

}
