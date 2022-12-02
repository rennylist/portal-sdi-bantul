<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once(APPPATH . 'controllers/base/UboldBase.php');

class User extends OperatorBase
{

    // constructor
    public function __construct()
    {
        parent::__construct();
        // load model
        $this->load->model('admin/master/M_user');
        $this->load->model('apps/M_email');
        $this->load->model('admin/master/M_penjawab');
        $this->load->model('admin/master/M_instansi');
        // load library
        $this->load->library('tnotification');
        $this->load->library('pagination');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    // list role
    public function index()
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/user/index.html");
        // get search parameter
        $search = $this->session->userdata('search_users');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // search parameters
        $user_st = !isset($search['user_st']) ? '%' : $search['user_st'];
        $user_st = ($user_st == '') ? '%' : $user_st;
        $user_alias = empty($search['user_alias']) ? '%' : '%' . $search['user_alias'] . '%';
        /* start of pagination --------------------- */
        // pagination
        $config['base_url'] = site_url("admin/master/user/index/");
        $config['total_rows'] = $this->M_user->get_total_users(array($user_st, $user_alias, $user_alias));
        $config['uri_segment'] = 5;
        $config['per_page'] = 10;
        $config['attributes'] = array('class' => 'page-link');
        $this->pagination->initialize($config);
        $pagination['data'] = $this->pagination->create_links();
        // pagination attribute
        $start = $this->uri->segment(5, 0) + 1;
        $end = $this->uri->segment(5, 0) + $config['per_page'];
        $end = (($end > $config['total_rows']) ? $config['total_rows'] : $end);
        $pagination['start'] = ($config['total_rows'] == 0) ? 0 : $start;
        $pagination['end'] = $end;
        $pagination['total'] = $config['total_rows'];
        // pagination assign value
        $this->tsmarty->assign("pagination", $pagination);
        $this->tsmarty->assign("no", $start);
        /* end of pagination ---------------------- */
        // get data
        $params = array($user_st, $user_alias, $user_alias, ($start - 1), $config['per_page']);
        $this->tsmarty->assign("rs_id", $this->M_user->get_list_users_by_group($params));
        // $this->tsmarty->assign("skpd", $this->m_skpd->get_all_skpd());
        
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // search process
    public function search_process()
    {
        // set page rules
        $this->_set_page_rule("R");
        // session
        if ($this->input->post('save') == "Cari") {
            // params
            $params = array(
                "user_alias" => $this->stripHTMLtags($this->input->post('user_alias', TRUE)),
                "user_st" => $this->stripHTMLtags($this->input->post('user_st', TRUE)),
            );
            // set session
            $this->session->set_userdata("search_users", $params);
        } else {
            // unset session
            $this->session->unset_userdata("search_users");
        }
        // redirect
        redirect("admin/master/user");
    }


    // private function _get_id() {
    //     $time = microtime(true);
    //     $id = str_replace('.', '', $time);
    //     $id = str_replace(',', '', $id);
    //     return $id;
    // }


    public function add()
    {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/user/add.html");
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }


    // add process
    public function add_process()
    {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('user_alias', 'Nama Pengguna', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('user_mail', 'Email Pengguna', 'trim|required|valid_email|max_length[100]');
        // check email
        $email = trim($this->stripHTMLtags($this->input->post('user_mail', TRUE)));
        if ($this->M_user->is_exist_email($email)) {
            $this->tnotification->set_error_message('Email sudah digunakan oleh orang lain.');
        }
        // user_id
        $prefix = date('ymd');
        $params = $prefix . '%';
        $user_id = $this->M_user->get_user_last_id($prefix, $params);
        if (!$user_id) {
            $this->tnotification->set_error_message('ID is not available');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'user_id' => $user_id,
                'user_alias' => $this->stripHTMLtags($this->input->post('user_alias', TRUE)),
                'user_mail' => $this->stripHTMLtags($this->input->post('user_mail', TRUE)),
                'user_st' => '0',
                'user_completed' => '0',
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            if ($this->M_user->insert_user($params)) {
                //insert pegawai
                $params = array(
                    'user_id' => $user_id,
                    'nama_lengkap' => $this->stripHTMLtags($this->input->post('user_alias', TRUE)),
                    'mdb' => $this->com_user['user_id'],
                    'mdb_name' => $this->com_user['user_alias'],
                    'mdd' => date("Y-m-d H:i:s")
                );
                $this->M_user->insert_pegawai($params);

                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan. Silakan lengkapi data user account berikut ini.");
                // redirect
                redirect("admin/master/user/edit_info/" . $user_id);
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // redirect
        redirect("admin/master/user/add");
    }

    /*
     * STEP 1 : INFO PENGGUNA
     */

    // edit info form
    public function edit_info($user_id = "")
    {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/user/edit_info.html");
        // get detail
        $result = $this->M_user->get_detail_user_by_id($user_id);
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            // redirect
            redirect("admin/master/user/administrator");
        }
        $result['user_mail_old'] = $result['user_mail'];
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit info process
    public function edit_info_process()
    {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('user_id', 'ID User', 'trim|required|max_length[10]');
        $this->tnotification->set_rules('user_alias', 'Nama Pengguna', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('user_mail', 'Alamat Email', 'trim|required|valid_email|max_length[50]');
        $this->tnotification->set_rules('user_mail_old', 'Email Lama', 'trim|required');
        // check email
        $email = trim($this->stripHTMLtags($this->input->post('user_mail', TRUE)));
        $email_lama = trim($this->stripHTMLtags($this->input->post('user_mail_old', TRUE)));
        if ($email != $email_lama) {
            if ($this->M_user->is_exist_email($email)) {
                $this->tnotification->set_error_message('Email sudah digunakan oleh orang lain.');
            }
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'user_alias' => $this->stripHTMLtags($this->input->post('user_alias', TRUE)),
                'user_mail' => $this->stripHTMLtags($this->input->post('user_mail', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'user_id' => $this->stripHTMLtags($this->input->post('user_id', TRUE))
            );
            if ($this->M_user->update_user($params, $where)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
                // redirect
                redirect("admin/master/user/edit_roles/" . $this->input->post('user_id'));
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/master/user/edit_info/" . $this->input->post('user_id'));
    }



    /*
     * STEP 2 : HAK AKSES PENGGUNA
     */

    // edit roles form
    public function edit_roles($user_id = "")
    {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/user/edit_roles.html");
        // get detail
        $result = $this->M_user->get_detail_user_by_id($user_id);
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            // redirect
            redirect("admin/master/user");
        }
        $this->tsmarty->assign("detail", $result);
        // get checked role by user
        $roles_checked = array();
        $rs_roles = $this->M_user->get_roles_by_user($user_id);
        foreach ($rs_roles as $role) {
            $roles_checked[] = $role["role_id"];
        }
        // get M_user
        // echo $this->com_portal['portal_id'];
        $rs_roles =  $this->M_user->get_roles_by_portal($this->com_portal['portal_id']);
        // print_r("<pre>");
        // print_r( $rs_roles);
        // print_r("</pre>");
        // exit();
        $this->tsmarty->assign("rs_instansi", $this->M_instansi->get_datas_all());
        $this->tsmarty->assign("rs_roles", $rs_roles);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // last field
        $data = $this->tnotification->get_field_data();
        if (isset($data['roles[]']['postdata'])) {
            if (!empty($data['roles[]']['postdata'])) {
                // hak akses
                $this->tsmarty->assign('roles_checked', $data['roles[]']['postdata']);
            }
        } else {
            $this->tsmarty->assign('roles_checked', $roles_checked);
        }
        // output
        parent::display();
    }

    // edit info process
    public function edit_roles_process()
    {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('user_id', 'ID User', 'trim|required');
        $this->tnotification->set_rules('roles', 'Hak Akses', 'trim|required');
        $instansi_cd = $this->stripHTMLtags($this->input->post('instansi_cd', TRUE));
        $roles = $this->stripHTMLtags($this->input->post('roles'));
        $user_group = 'penjawab';
        if ($roles != '02003') {
            $instansi_cd = null;
            $user_group = 'admin';
        } else {
            $this->tnotification->set_rules('instansi_cd', 'Instansi', 'trim|required');
        }

        // process
        if ($this->tnotification->run() !== FALSE) {
            // roles

            // delete by user
            $where = array(
                'user_id' => $this->stripHTMLtags($this->input->post('user_id', TRUE)),
            );
            $this->M_user->delete_role_user($where);
            // insert roles
            $params = array(
                'user_id' => $this->stripHTMLtags($this->input->post('user_id', TRUE)),
                'role_id' => $roles
            );
            $this->M_user->insert_role_user($params);

            //update penjawab
            $params = array(
                'instansi_cd' => $instansi_cd,
                'user_group' => $user_group,
            );
            $where = array('user_id' => $this->input->post('user_id', TRUE));
            $this->M_user->update_user($params, $where);

            // notification
            $this->tnotification->delete_last_field();
            $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            // default redirect
            redirect("admin/master/user/edit_account/" . $this->input->post('user_id'));
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/master/user/edit_roles/" . $this->input->post('user_id'));
    }

    /*
     * STEP 3 : AKUN PENGGUNA DNA AKTIVASI
     */

    // edit account form
    public function edit_account($user_id = "")
    {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/user/edit_account.html");
        // get detail
        $result = $this->M_user->get_detail_user_by_id($user_id);
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            // redirect
            redirect("settings/users/administrator");
        }
        $result['user_name_old'] = $result['user_name'];
        // --
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit account process
    public function edit_account_process()
    {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('user_id', 'ID User', 'trim|required|max_length[10]');
        $this->tnotification->set_rules('user_name', 'Username', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('user_name_old', 'Username', 'trim');
        $this->tnotification->set_rules('user_pass', 'Password', 'trim|required|min_length[8]|max_length[50]');
        $this->tnotification->set_rules('user_st', 'Status', 'trim|required');
        $this->tnotification->set_rules('user_mail', 'Kirim Email', 'trim|required');
        // check email
        $email = trim($this->stripHTMLtags($this->input->post('user_mail', TRUE)));

        //print_r($email);
        $email_lama = trim($this->stripHTMLtags($this->input->post('user_mail_old', TRUE)));
        if ($email != $email_lama) {
            if ($this->M_user->is_exist_email($email)) {
                $this->tnotification->set_error_message('Email sudah digunakan oleh orang lain.');
            }
        }
        // get detail
        $user_id = $this->stripHTMLtags($this->input->post('user_id', TRUE));
        $result = $this->M_user->get_detail_user_by_id($user_id);
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            // redirect
            redirect("admin/master/user");
        }
        // check username
        $username = trim($this->stripHTMLtags($this->input->post('user_name', TRUE)));
        if ($this->input->post('user_name') != $this->stripHTMLtags($this->input->post('user_name_old', TRUE))) {
            if ($this->M_user->is_exist_username($username)) {
                $this->tnotification->set_error_message('Username is not available');
            }
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
                'user_name' => $this->stripHTMLtags($this->input->post('user_name', TRUE)),
                'user_st' => $this->stripHTMLtags($this->input->post('user_st', TRUE)),
                'user_mail' => $this->stripHTMLtags($this->input->post('user_mail', TRUE)),
                'user_completed' => '1',
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );

            //print_r($params);

            // password
            $password = $this->stripHTMLtags($this->input->post('user_pass', TRUE));

            $password_key = abs(crc32($password));
            $user_pass = md5($password);

            // cek password
            if (strlen($password) < 8) {
                $this->tnotification->sent_notification("error", "Password kurang 8 karakter");
                redirect("admin/master/user/edit_account/" . $this->input->post('user_id'));
            }
            if (!preg_match("#[0-9]+#", $password)) {
                $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 angka (0-9)");
                redirect("admin/master/user/edit_account/" . $this->input->post('user_id'));
            }
            if (!preg_match("#[a-z]+#", $password)) {
                $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 huruf kecil (a-z)");
                redirect("admin/master/user/edit_account/" . $this->input->post('user_id'));
            }
            if (!preg_match("#[A-Z]+#", $password)) {
                $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 huruf besar (A-Z)");
                redirect("admin/master/user/edit_account/" . $this->input->post('user_id'));
            }
            if (!preg_match("#[!@\#$%^&*]+#", $password)) {
                $this->tnotification->sent_notification("error", "Password tidak memuat paling sedikit 1 karakter spesial (!@#$%^&*)");
                redirect("admin/master/user/edit_account/" . $this->input->post('user_id'));
            }

            $params['user_pass'] = $user_pass;
            $params['user_key'] = $password_key;
            // where
            $where = array(
                'user_id' => $this->stripHTMLtags($this->input->post('user_id', TRUE))
            );
            if ($this->M_user->update_user($params, $where)) {
                // send email
                $send_mail = $this->stripHTMLtags($this->input->post('user_mail', TRUE));
                if ($send_mail === '1') {
                    // config
                    $email_params['to'] = $result['user_mail'];
                    $email_params['cc'] = array();
                    $email_params['subject'] = 'Informasi Akun, Website Bantulkab';
                    $email_params['message']['title'] = 'Akun anda telah diaktifkan';
                    $email_params['message']['greetings'] = 'Hai ' . $result['user_alias'] . ',';
                    $email_params['message']['intro'] = 'Akun anda telah berhasil diaktivasi. Berikut ini adalah akun baru anda : ';
                    // detail
                    $message = '<table cellspacing="0" cellpadding="0" border="0">';
                    $message .= '<tbody>';
                    $message .= '<tr>';
                    $message .= '<td style="width: 100px;">Username</td>';
                    $message .= '<td><b>' . $username . '</b></td>';
                    $message .= '</tr>';
                    $message .= '<tr>';
                    $message .= '<td style="width: 100px;">Password</td>';
                    $message .= '<td><b>' . $password . '</b></td>';
                    $message .= '</tr>';
                    $message .= '</tbody>';
                    $message .= '</table>';
                    // --
                    $email_params['message']['details'] = $message;
                    $email_params['attachments'] = array();
                    // set email parameters
                    $this->M_email->set_mail($email_params);
                    // send
                    if ($this->M_email->send_mail()) {
                        $this->tnotification->set_error_message('Email telah dikirim!');
                    } else {
                        $this->tnotification->set_error_message('Email gagal dikirim, periksa lagi email anda!');
                    }
                }
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
                // redirect to account
                redirect("admin/master/user");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/master/user/edit_account/" . $this->input->post('user_id'));
    }

    /*
     * AJAX generate password
     */

    // generate password
    public function generate_password($len = 6)
    {
        $chars = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
        $password = substr(str_shuffle($chars), 0, $len);
        echo $password;
    }
}
