<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

class Preferences extends AdminBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('settings/sistem/M_settings');
        // load library
        $this->load->library('tnotification');
        //page header
        $this->tsmarty->assign("page_header", "System Preferences");
    }

    // Level Surat
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/preferences/index.html");
        // get search parameter
        $search = $this->session->userdata('search_pref');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // search parameters
        $pref_nm = empty($search['pref_nm']) ? '%' : '%' . $search['pref_nm'] . '%';
        $pref_group = empty($search['pref_group']) ? '%' : $search['pref_group'];
        // get data
        $this->tsmarty->assign("no", 1);
        $rs_id = $this->M_settings->get_all_preferences_by_params(array($pref_nm, $pref_group));
        $this->tsmarty->assign("rs_id", $rs_id);
        $this->tsmarty->assign("rs_group", $this->M_settings->get_all_preferences_group());
        // echo "<pre>"; print_r($rs_id); echo"</pre>"; exit();
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
        if ($this->input->post('save') == "Reset") {
            // unset session
            $this->session->unset_userdata("search_pref");
        } else {
            // params
            $params = array(
                "pref_nm" => $this->input->post('pref_nm', TRUE),
                "pref_group" => $this->input->post('pref_group', TRUE),
            );
            // set session
            $this->session->set_userdata("search_pref", $params);
        }
        // redirect
        redirect("settings/sistem/preferences");
    }

    // form add
    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/preferences/add.html");
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
        $this->tnotification->set_rules('pref_nm', 'Name', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('pref_value', 'Value', 'trim|required|max_length[50]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'pref_group' => $this->input->post('pref_group', TRUE),
                'pref_nm' => $this->input->post('pref_nm', TRUE),
                'pref_value' => $this->input->post('pref_value', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdd' => date('Y-m-d H:i:s'),
            );
            // insert
            if ($this->M_settings->insert_preferences($params)) {
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
        redirect("settings/sistem/preferences/add");
    }

    // form edit
    public function edit($pref_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/preferences/edit.html");
        // get data
        $result = $this->M_settings->get_preference_by_id($pref_id);
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/preferences");
        }
        // assign
        $this->tsmarty->assign("result", $result);
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
        $this->tnotification->set_rules('pref_id', 'ID', 'required');
        $this->tnotification->set_rules('pref_nm', 'Name', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('pref_value', 'Value', 'trim|required|max_length[50]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'pref_group' => $this->input->post('pref_group', TRUE),
                'pref_nm' => $this->input->post('pref_nm', TRUE),
                'pref_value' => $this->input->post('pref_value', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdd' => date('Y-m-d H:i:s'),
            );
            $where = array(
                'pref_id' => $this->input->post('pref_id', TRUE)
            );
            // insert
            if ($this->M_settings->update_preferences($params, $where)) {
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
        redirect("settings/sistem/preferences/edit/" . $this->input->post('pref_id', TRUE));
    }

    // form delete
    public function delete($pref_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/preferences/delete.html");
        // get data
        $result = $this->M_settings->get_preference_by_id($pref_id);
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/preferences");
        }
        // assign
        $this->tsmarty->assign("result", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit process
    public function delete_process() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('pref_id', 'ID', 'required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $where = array(
                'pref_id' => $this->input->post('pref_id', TRUE)
            );
            // insert
            if ($this->M_settings->delete_preferences($where)) {
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
        redirect("settings/sistem/preferences/");
    }

}
