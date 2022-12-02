<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

class Groups extends AdminBase {

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
        $this->tsmarty->assign("template_content", "settings/sistem/groups/index.html");
        // get data
        $this->tsmarty->assign("rs_id", $this->M_settings->get_all_group());
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
        $this->tsmarty->assign("template_content", "settings/sistem/groups/add.html");

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
        $this->tnotification->set_rules('group_name', 'Nama Group', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('group_desc', 'Deskripsi Group', 'trim|required|max_length[255]');
        // group id
        $group_id = $this->M_settings->get_group_last_id();
        if (!$group_id) {
            $this->tnotification->set_error_message('ID is not available');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'group_id' => $group_id,
                'group_name' => $this->input->post('group_name', TRUE),
                'group_desc' => $this->input->post('group_desc', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdd' => date("Y-m-d H:i:s")
            );
            // insert
            if ($this->M_settings->insert_group($params)) {
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
        redirect("settings/sistem/groups/add");
    }

    // edit form
    public function edit($group_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/groups/edit.html");
        // get data
        $result = $this->M_settings->get_group_by_id($group_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/groups");
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
        $this->tnotification->set_rules('group_id', 'Group ID', 'trim|required|max_length[2]');
        $this->tnotification->set_rules('group_name', 'Nama Group', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('group_desc', 'Deskripsi Group', 'trim|required|max_length[255]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'group_name' => $this->input->post('group_name', TRUE),
                'group_desc' => $this->input->post('group_desc', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'group_id' => $this->input->post('group_id', TRUE)
            );
            // update
            if ($this->M_settings->update_group($params, $where)) {
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
        redirect("settings/sistem/groups/edit/" . $this->input->post('group_id', TRUE));
    }

    // hapus form
    public function delete($group_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/groups/delete.html");
        // get data
        $result = $this->M_settings->get_group_by_id($group_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/groups");
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
        $this->tnotification->set_rules('group_id', 'Group ID', 'trim|required|max_length[2]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'group_id' => $this->input->post('group_id', TRUE)
            );
            // delete
            if ($this->M_settings->delete_group($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("settings/sistem/groups");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("settings/sistem/groups/delete/" . $this->input->post('group_id'));
    }

}
