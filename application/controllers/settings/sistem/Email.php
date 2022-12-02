<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

class Email extends AdminBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('settings/sistem/M_email');
        // load library
        $this->load->library('tnotification');
    }

    // list data
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/email/index.html");
        // get data
        $this->tsmarty->assign("rs_email", $this->M_email->get_list_email());
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
        $this->tsmarty->assign("template_content", "settings/sistem/email/add.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
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
        $this->tnotification->set_rules('email_name', 'Nama Email', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('email_address', 'Alamat Email', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('smtp_host', 'Smtp Host', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('smtp_port', 'Smtp Port', 'trim|required|max_length[5]');
        $this->tnotification->set_rules('smtp_username', 'Smtp Username', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('smtp_password', 'Smtp Password', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('use_smtp', 'Gunakan Smtp', 'trim|required|max_length[1]');
        $this->tnotification->set_rules('use_authorization', 'Gunakan Authorisasi', 'trim|required|max_length[1]');
        // email id
        $email_id = $this->M_email->get_email_last_id();
        if (!$email_id) {
            $this->tnotification->set_error_message('ID tidak tersedia');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'email_id' => $email_id,
                'email_name' => $this->input->post('email_name', TRUE),
                'email_address' => $this->input->post('email_address', TRUE),
                'smtp_host' => $this->input->post('smtp_host', TRUE),
                'smtp_port' => $this->input->post('smtp_port', TRUE),
                'smtp_username' => $this->input->post('smtp_username', TRUE),
                'smtp_password' => $this->input->post('smtp_password', TRUE),
                'use_smtp' => $this->input->post('use_smtp', TRUE),
                'use_authorization' => $this->input->post('use_authorization', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['nama_lengkap'],
                'mdd' => date("Y-m-d H:i:s")
            );
            // insert
            if ($this->M_email->insert_email($params)) {
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
        redirect("settings/sistem/email/add");
    }

    // edit form
    public function edit($email_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/email/edit.html");
        // get data
        $result = $this->M_email->get_email_by_id($email_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak tersedia!");
            redirect("settings/sistem/email");
        }
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
        $this->tnotification->set_rules('email_id', 'ID Email', 'trim|required|max_length[2]');
        $this->tnotification->set_rules('email_name', 'Nama Email', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('email_address', 'Alamat Email', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('smtp_host', 'Smtp Host', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('smtp_port', 'Smtp Port', 'trim|required|max_length[5]');
        $this->tnotification->set_rules('smtp_username', 'Smtp Username', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('smtp_password', 'Smtp Password', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('use_smtp', 'Gunakan Smtp', 'trim|required|max_length[1]');
        $this->tnotification->set_rules('use_authorization', 'Gunakan Authorisasi', 'trim|required|max_length[1]');
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'email_name' => $this->input->post('email_name', TRUE),
                'email_address' => $this->input->post('email_address', TRUE),
                'smtp_host' => $this->input->post('smtp_host', TRUE),
                'smtp_port' => $this->input->post('smtp_port', TRUE),
                'smtp_username' => $this->input->post('smtp_username', TRUE),
                'smtp_password' => $this->input->post('smtp_password', TRUE),
                'use_smtp' => $this->input->post('use_smtp', TRUE),
                'use_authorization' => $this->input->post('use_authorization', TRUE),
            );
            $where = array(
                'email_id' => $this->input->post('email_id', TRUE)
            );
            // update
            if ($this->M_email->update_email($params, $where)) {
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
        redirect("settings/sistem/email/edit/" . $this->input->post('email_id', TRUE));
    }

    // hapus form
    public function delete($email_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/email/delete.html");
        // get data
        $result = $this->M_email->get_email_by_id($email_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak tersedia !");
            redirect("settings/sistem/email");
        }
        $this->tsmarty->assign("result", $result);
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
        $this->tnotification->set_rules('email_id', 'Portal ID', 'trim|required|max_length[10]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'email_id' => $this->input->post('email_id', TRUE)
            );
            // delete
            if ($this->M_email->delete_email($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("settings/sistem/email");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("settings/sistem/email/delete/" . $this->input->post('email_id'));
    }

    // test email
    public function test_email($email_id = '') {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/email/test.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/ckeditor/ckeditor.js");
        $this->tsmarty->load_javascript("resource/themes/default/plugins/ckeditor/adapters/jquery.js");
        // get data
        $result = $this->M_email->get_email_by_id($email_id);
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak tersedia !");
            redirect("settings/sistem/email");
        }
        // get data
        $this->tsmarty->assign("result", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // process test email
    public function test_email_process() {
        // set page rules
        $this->_set_page_rule("R");
        // cek input
        $this->tnotification->set_rules('email_id', 'ID Email', 'trim|required|max_length[2]');
        $this->tnotification->set_rules('email_tujuan', 'Email Tujuan', 'trim|required|valid_email');
        $this->tnotification->set_rules('email_subject', 'Subject', 'trim|required');
        $this->tnotification->set_rules('isi_email', 'Isi Email', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            //get data
            $result = $this->M_email->get_email_by_id($this->input->post('email_id', TRUE));
            // setting
            $from = $result;
            $to = $this->input->post('email_tujuan', TRUE);
            $subject = $this->input->post('email_subject', TRUE);
            $message = $this->input->post('isi_email', TRUE);
            $mail = $this->M_email->sendmail($from, $to, $subject, $message);
            //cek status, true/false
            if ($mail['status']) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Email berhasil dikirim");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Email gagal dikirim");
                $this->tnotification->set_error_message($mail['message']);
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Email gagal dikirim");
        }
        // default redirect
        redirect("settings/sistem/email/test_email/" . $this->input->post('email_id', TRUE));
    }

}
