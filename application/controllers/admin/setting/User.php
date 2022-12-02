<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/UboldBase.php' );

class User extends OperatorBase
 {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('settings/M_user');
        // $this->load->model('home/M_profile');
        $this->load->model('apps/M_email');
        $this->load->model('apps/M_account');
        // load library
        $this->load->library('tnotification');
        $this->load->library('pagination');
        $this->load->library('encryption');
    }

    // list role
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/setting/user/index.html");
        // get data
        $user_id = $this->com_user['user_id'];
        $result = $this->M_user->get_data_user_by_id($user_id);
        $default_foto = base_url() . 'resource/doc/images/users/default.png';
        $foto = "";
        if($result['foto_name'] != ""){
            $foto = base_url() . $result['foto_path'] . $result['foto_name'];
        }
        // assign
        $this->tsmarty->assign("user_id", $user_id);
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("default_foto", $default_foto);
        $this->tsmarty->assign("foto", $foto);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit foto process
    public function edit_process() {
        // set page rules
        $this->_set_page_rule("U");
        // load library
        $this->load->library('tupload');
        // cek input
        $this->tnotification->set_rules('user_id', 'User ID', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'nama_lengkap' => $this->stripHTMLtags($this->input->post('nama_lengkap', TRUE)),
                'tempat_lahir' => $this->stripHTMLtags($this->input->post('tempat_lahir', TRUE)),
                'tanggal_lahir' => $this->stripHTMLtags($this->input->post('tanggal_lahir', TRUE)),
                'jenis_kelamin' => $this->stripHTMLtags($this->input->post('jenis_kelamin', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'user_id' => $this->stripHTMLtags($this->input->post('user_id', TRUE))
            );
            //update
            if ($this->M_user->update($params, $where)) {
                // upload
                if (!empty($_FILES['foto_name']['tmp_name'])) {
                    // upload config
                    $config['upload_path'] = 'resource/doc/images/users/';
                    $config['allowed_types'] = 'gif|jpg|jpeg|png';
                    // $config['file_name'] = $this->com_user['user_id'] . '_' . date('Ymdhis');
                    $config['file_name'] = $this->stripHTMLtags($this->input->post('user_id', TRUE));
                    // --
                    $this->tupload->initialize($config);
                    // process upload images
                    if ($this->tupload->do_upload_image('foto_name', 128, FALSE)) {
                        // --
                        $data = $this->tupload->data();
                        // --
                        $params = array(
                            'foto_name' => $data['file_name'],
                            'foto_path' => 'resource/doc/images/users/'
                        );
                        $where = array(
                            'user_id' => $this->stripHTMLtags($this->input->post('user_id', TRUE))
                        );
                        $this->M_user->update($params, $where);
                        // hapus foto lama
                        $this->tnotification->sent_notification("success", "Foto profil berhasil diupdate.");
                    } else {
                        // jika gagal
                        $this->tnotification->set_error_message($this->tupload->display_errors());
                        $this->tnotification->sent_notification("error", "Foto profil gagal disimpan.");
                    }
                }
            } else {
                $this->tnotification->sent_notification("error", "Foto profil gagal disimpan.");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal diubah");
        }
        // redirect
        redirect("admin/setting/user/index");
    }

    // list role
    public function kontak() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/setting/user/kontak.html");
        // get data
        $user_id = $this->com_user['user_id'];
        $result = $this->M_user->get_data_user_by_id($user_id);
        $default_foto = base_url() . 'resource/doc/images/users/default.png';
        $foto = "";
        if($result['foto_name'] != ""){
            $foto = base_url() . $result['foto_path'] . $result['foto_name'];
        }
        // assign
        $this->tsmarty->assign("user_id", $user_id);
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("default_foto", $default_foto);
        $this->tsmarty->assign("foto", $foto);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // view
    public function password() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/setting/user/password.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/uniform/uniform.min.js");
        // get data
        $user_id = $this->com_user['user_id'];
        $detail = $this->M_user->get_data_user_by_id($user_id);
        $default_foto = base_url() . 'resource/doc/images/users/default.png';
        $foto = base_url() . $detail['foto_path'] . $detail['foto_name'];
        // result
        $this->tsmarty->assign("result", $result = $this->M_account->get_user_account_by_id($this->com_user['user_id']));
        $this->tsmarty->assign("user_id", $user_id);
        $this->tsmarty->assign("detail", $detail);
        $this->tsmarty->assign("default_foto", $default_foto);
        $this->tsmarty->assign("foto", $foto);            
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit akun process
    public function edit_password_process() {
        // mematikan error
        error_reporting(0);
        // set page rules
        $this->_set_page_rule("U");
        // check input
        $this->tnotification->set_rules('old_pass', 'Password Saat Ini', 'trim|required');
        $this->tnotification->set_rules('user_pass', 'Password Baru', 'trim|required|min_length[8]|max_length[20]');
        $this->tnotification->set_rules('confirm_user_pass', 'Konfirmasi Password Baru', 'trim|required|min_length[6]|max_length[50]');
        // process        
        if ($this->tnotification->run() !== FALSE) {
            // get session username
            $username = $this->com_user['user_alias'];
            $password_old = trim($this->stripHTMLtags($this->input->post('old_pass', TRUE)));
            // get user detail
            $result = $this->M_account->get_user_login_all_roles($username, $password_old, $this->portal_id);
            // validate password
            if (empty($result)) {
                // notification & default redirect
                $this->tnotification->set_error_message('Password yang lama tidak cocok.');
                redirect("admin/setting/user/password");
            }
            // validate password confirmation
            $password_new = trim($this->stripHTMLtags($this->input->post('user_pass', TRUE)));
            $password_confirm = trim($this->stripHTMLtags($this->input->post('confirm_user_pass', TRUE)));
            // cek password
            if (strlen($password_new) < 8) {
                $this->tnotification->sent_notification("error", "Password kurang 8 karakter");
                redirect("admin/setting/user/password");
            }
            if (!preg_match("#[0-9]+#", $password_new)) {
                $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 angka (0-9)");
                redirect("admin/setting/user/password");
            }
            if (!preg_match("#[a-z]+#", $password_new)) {
                $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 huruf kecil (a-z)");
                redirect("admin/setting/user/password");
            }     
            if (!preg_match("#[A-Z]+#", $password_new)) {
                $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 huruf besar (A-Z)");
                redirect("admin/setting/user/password");
            }   
            if (!preg_match("#[!@\#$%^&*]+#", $password_new)) {
                $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 karakter spesial (!@#$%^&*)");
                redirect("admin/setting/user/password");
            }   

            //cek password            
            if ($password_new <> $password_confirm) {
                // notification & default redirect
                $this->tnotification->set_error_message('Konfirmasi Password Baru Tidak Sesuai!');
                redirect("admin/setting/user/password");
            }
            // password
            // $password_key = abs(crc32($this->stripHTMLtags($this->input->post('user_pass', TRUE))));
            // $user_pass = $this->encryption->encode(md5($this->stripHTMLtags($this->input->post('user_pass', TRUE))), $password_key);
            $password_key = bin2hex($this->encryption->create_key(16));
            $this->encryption->initialize(array('cipher' => 'aes-256','mode' => 'ctr','key' => $password_key));
            $user_pass = $this->encryption->encrypt(md5($this->stripHTMLtags($this->input->post('user_pass', TRUE))));
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
                $send_mail = trim($this->stripHTMLtags($this->input->post('send_mail', TRUE)));
                // check mail
                if ($send_mail == 'yes') {
                    // send email
                    // config
                    $email_params['to'] = $this->stripHTMLtags($this->com_user['user_mail']);
                    $email_params['cc'] = array();
                    $email_params['subject'] = 'Ganti Password, Pemkab Bantul Management Tools';
                    $email_params['message']['title'] = 'Password telah diperbaharui';
                    $email_params['message']['greetings'] = 'Hai ' . $this->com_user['nama_lengkap'] . ',';
                    $email_params['message']['intro'] = 'Anda berhasil melakukan perubahan password pada akun user anda. Berikut ini adalah password baru anda : ';
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
        redirect("admin/setting/user/password");
    }

    // edit foto process
    public function kontak_process() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('user_id', 'User ID', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'nomor_telepon' => $this->stripHTMLtags($this->input->post('nomor_telepon', TRUE)),
                // 'user_mail' => $this->stripHTMLtags($this->input->post('user_mail', TRUE)),
                'alamat_tinggal' => $this->stripHTMLtags($this->input->post('alamat_tinggal', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'user_id' => $this->stripHTMLtags($this->input->post('user_id', TRUE))
            );
            //update
            if ($this->M_user->update($params, $where)) {
                $this->tnotification->sent_notification("success", "Data berhasil diubah.");
            } else {
                $this->tnotification->sent_notification("error", "Data gagal disimpan.");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal diubah");
        }
        // redirect
        redirect("admin/setting/user/kontak");
    }
}
