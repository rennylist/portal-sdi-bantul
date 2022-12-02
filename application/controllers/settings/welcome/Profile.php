<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

class Profile extends AdminBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('settings/welcome/M_profile');
        $this->load->model('apps/M_email');
        // load library
        $this->load->library('tupload');
        $this->load->library('tnotification');
        $this->load->library('encryption');
    }

    // view
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/welcome/profile/index.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/uniform/uniform.min.js");
        // get data pegawai
        $pegawai = $this->M_profile->get_detail_pegawai_by_id($this->com_user['user_id']);
        $this->tsmarty->assign('pegawai', $pegawai);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output        
        parent::display();
    }

    // view
    public function account_settings() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/welcome/profile/account_settings.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/uniform/uniform.min.js");
        // result
        $this->tsmarty->assign("result", $result = $this->M_account->get_user_account_by_id($this->com_user['user_id']));
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit akun process
    public function edit_account_process() {
        // set page rules
        $this->_set_page_rule("U");
        // check input
        $this->tnotification->set_rules('old_pass', 'Password Saat Ini', 'trim|required');
        $this->tnotification->set_rules('user_pass', 'Password Baru', 'trim|required|min_length[6]|max_length[20]');
        $this->tnotification->set_rules('confirm_user_pass', 'Konfirmasi Password Baru', 'trim|required|min_length[6]|max_length[50]');
        // validate password
        $password_old = trim($this->input->post('old_pass', TRUE));
        // $password_decode = $this->encryption->decode($this->com_user['user_pass'], $this->com_user['user_key']);
        $password_key = $this->com_user['user_key'];
        $this->encryption->initialize(array('cipher' => 'aes-256','mode' => 'ctr','key' => $password_key));
        $user_pass = $this->encryption->encrypt(md5(($this->input->post('user_pass', TRUE))));
        $password_decode = $this->encryption->decrypt($this->com_user['user_pass']);
        if ($password_decode <> md5($password_old)) {
            $this->tnotification->set_error_message('Password yang lama tidak cocok.');
        }
        // validate password confirmation
        $password_new = trim($this->input->post('user_pass', TRUE));
        $password_confirm = trim($this->input->post('confirm_user_pass', TRUE));
        // cek password
        if (strlen($password_new) < 9) {
            $this->tnotification->sent_notification("error", "Password kurang 8 karakter");
            redirect("settings/welcome/profile/account_settings");
        }
        if (!preg_match("#[0-9]+#", $password_new)) {
            $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 angka (0-9)");
            redirect("settings/welcome/profile/account_settings");
        }
        if (!preg_match("#[a-z]+#", $password_new)) {
            $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 huruf kecil (a-z)");
            redirect("settings/welcome/profile/account_settings");
        }     
        if (!preg_match("#[A-Z]+#", $password_new)) {
            $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 huruf besar (A-Z)");
            redirect("settings/welcome/profile/account_settings");
        }   
        if (!preg_match("#[!@\#$%^&*]+#", $password_new)) {
            $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 karakter spesial (!@#$%^&*)");
            redirect("settings/welcome/profile/account_settings");
        }   
        if ($password_new <> $password_confirm) {
            $this->tnotification->set_error_message('Konfirmasi Password Baru Tidak Sesuai!');
        }
        // process        
        if ($this->tnotification->run() !== FALSE) {
            // password
            $password_key = bin2hex($this->encryption->create_key(16));
            $this->encryption->initialize(array('cipher' => 'aes-256','mode' => 'ctr','key' => $password_key));
            $user_pass = $this->encryption->encrypt(md5(($this->input->post('user_pass', TRUE))));
            // params
            $params = array(
                'user_pass' => $user_pass,
                'user_key' => $password_key,
            );
            // where
            $where = array(
                'user_id' => $this->com_user['user_id']
            );
            // update data
            if ($this->M_account->update_user($params, $where)) {
                // send email
                $send_mail = trim($this->input->post('send_mail', TRUE));
                // check mail
                if ($send_mail == 'yes') {
                    // send email
                    // config
                    $email_params['to'] = $this->com_user['user_mail'];
                    $email_params['cc'] = array();
                    $email_params['subject'] = 'Ganti Password, Pemkab Bantul Management Tools';
                    $email_params['message']['title'] = 'Password telah diperbaharui';
                    $email_params['message']['greetings'] = 'Hi ' . $this->com_user['nama_lengkap'] . ',';
                    $email_params['message']['intro'] = 'Anda berhasil melakukan perubahan password pada user account anda. Berikut ini adalah password baru anda : ';
                    // detail
                    $message = '<table cellspacing="0" cellpadding="0" border="0">';
                    $message .= '<tbody>';
                    $message .= '<tr>';
                    $message .= '<td style="width: 100px;">Password</td>';
                    $message .= '<td><b>' . $password_confirm . '</b></td>';
                    $message .= '</tr>';
                    $message .= '</tbody>';
                    $message .= '</table>';
                    // --
                    $email_params['message']['details'] = $message;
                    $email_params['attachments'] = array();
                    // set email parameters
                    $this->M_email->set_mail($email_params);
                    // send
                    if ($this->M_email->send_mail('01')) {
                        $this->tnotification->set_error_message('Email telah dikirim!');
                    } else {
                        $this->tnotification->set_error_message('Email gagal dikirim, periksa lagi email anda!');
                    }
                }
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal diubah");
        }
        // default redirect
        redirect("settings/welcome/profile/account_settings");
    }

    // edit foto process
    public function edit_foto_process() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('page', 'Halaman', 'trim|required');
        // page
        $page = $this->input->post('page', TRUE);
        // process
        if ($this->tnotification->run() !== FALSE) {
            // upload
            if (!empty($_FILES['user_img_upload']['tmp_name'])) {
                // upload config
                $config['upload_path'] = 'resource/doc/images/users/';
                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['file_name'] = $this->com_user['user_id'] . '_' . date('Ymdhis');
                // --
                $this->tupload->initialize($config);
                // process upload images
                if ($this->tupload->do_upload_image('user_img_upload', 128, FALSE)) {
                    // --
                    $data = $this->tupload->data();
                    // --
                    $params = array(
                        'user_img_name' => $data['file_name'],
                        'user_img_path' => 'resource/doc/images/users/'
                    );
                    $where = array(
                        'user_id' => $this->com_user['user_id']
                    );
                    $this->M_account->update_user($params, $where);
                    // hapus foto lama
                    $this->tnotification->sent_notification("success", "Foto profil berhasil diupdate.");
                } else {
                    // jika gagal
                    $this->tnotification->set_error_message($this->tupload->display_errors());
                    $this->tnotification->sent_notification("error", "Foto profil gagal disimpan.");
                }
            } else {
                $this->tnotification->sent_notification("error", "Foto profil gagal disimpan.");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal diubah");
        }
        // redirect
        if ($page == 'profil') {
            redirect("settings/welcome/profile");
        } else {
            redirect("settings/welcome/profile/account_settings");
        }
    }

}
