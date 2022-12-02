<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

// --

class Roles extends AdminBase {

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
        $this->tsmarty->assign("template_content", "settings/sistem/roles/index.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
        // get search parameter
        $search = $this->session->userdata('search_roles');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // search parameters
        $role_nm = empty($search['role_nm']) ? '%' : '%' . $search['role_nm'] . '%';
        $group_id = empty($search['group_id']) ? '%' : $search['group_id'];
        // get data
        $this->tsmarty->assign("rs_id", $this->M_settings->get_all_roles(array($role_nm, $group_id)));
        //groups
        $this->tsmarty->assign("rs_group", $this->M_settings->get_all_group());
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
                "role_nm" => $this->input->post('role_nm', TRUE),
                "group_id" => $this->input->post('group_id', TRUE),
            );
            // set session
            $this->session->set_userdata("search_roles", $params);
        } else {
            // unset session
            $this->session->unset_userdata("search_roles");
        }
        // redirect
        redirect("settings/sistem/roles");
    }

    // add form
    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/roles/add.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
        // get data
        $this->tsmarty->assign("rs_group", $this->M_settings->get_all_group());
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
        $this->tnotification->set_rules('group_id', 'Group', 'trim|required|max_length[2]');
        $this->tnotification->set_rules('role_nm', 'Role Name', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('role_desc', 'Role Description', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('default_page', 'Default Pages', 'trim|required|max_length[50]');
        // role id
        $role_id = $this->M_settings->get_role_last_id($this->input->post('group_id', TRUE));
        if (!$role_id) {
            $this->tnotification->set_error_message('ID is not available');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'role_id' => $role_id,
                'group_id' => $this->input->post('group_id', TRUE),
                'role_nm' => $this->input->post('role_nm', TRUE),
                'role_desc' => $this->input->post('role_desc', TRUE),
                'default_page' => $this->input->post('default_page', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdd' => date("Y-m-d H:i:s")
            );
            // insert
            if ($this->M_settings->insert_role($params)) {
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
        redirect("settings/sistem/roles/add");
    }

    // edit form
    public function edit($role_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/roles/edit.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
        // get data
        $this->tsmarty->assign("rs_group", $this->M_settings->get_all_group());
        // get data
        $result = $this->M_settings->get_detail_role_by_id($role_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/roles");
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
        $this->tnotification->set_rules('role_id', 'ID Role', 'trim|required');
        $this->tnotification->set_rules('group_id', 'Group', 'trim|required|max_length[2]');
        $this->tnotification->set_rules('role_nm', 'Role Name', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('role_desc', 'Role Description', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('default_page', 'Default Pages', 'trim|required|max_length[50]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'group_id' => $this->input->post('group_id', TRUE),
                'role_nm' => $this->input->post('role_nm', TRUE),
                'role_desc' => $this->input->post('role_desc', TRUE),
                'default_page' => $this->input->post('default_page', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'role_id' => $this->input->post('role_id', TRUE)
            );
            // update
            if ($this->M_settings->update_role($params, $where)) {
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
        redirect("settings/sistem/roles/edit/" . $this->input->post('role_id', TRUE));
    }

    // hapus form
    public function delete($role_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/roles/delete.html");
        // get data
        $result = $this->M_settings->get_detail_role_by_id($role_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/roles");
        }
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
        $this->tnotification->set_rules('role_id', 'ID Role', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'role_id' => $this->input->post('role_id', TRUE)
            );
            // delete
            if ($this->M_settings->delete_role($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("settings/sistem/roles");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("settings/sistem/roles/hapus/" . $this->input->post('role_id'));
    }

}
