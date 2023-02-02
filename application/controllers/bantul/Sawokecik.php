<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once(APPPATH . 'controllers/base/UboldLoginBase.php');

class Sawokecik extends ApplicationBase
{
    protected $attempt_session = 'login_operator';
    protected $attempt_max = 3;
    protected $attempt_time = 1;

    // constructor
    public function __construct()
    {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('apps/M_email');
        $this->load->model('apps/M_account');
        // load notification
        $this->load->library('tnotification');
        // load helper
        $this->load->helper("captcha");
        $this->load->helper(array('captcha', 'url', 'form'));
    }

    public function tes()
    {
        // load encryption
        $this->load->library('encryption');

        // //encrypt
        // $password_plain = "caturgagah";
        // $password_md5 = md5( $password_plain);
        // $key = $result['user_key'];
        // // $key = bin2hex($this->encryption->create_key(16));
        // $this->encryption->initialize(array('cipher' => 'aes-256','mode' => 'ctr','key' => $key));
        // //decrypt
        // $ciphertext = $this->encryption->encrypt($password_md5);
        // $password_decode =  $this->encryption->decrypt($ciphertext);


        //encrypt

        $password_plain = "pancagagah";
        $password_md5 = md5($password_plain);
        // $password_md5 = "caturgagah";
        $key = bin2hex($this->encryption->create_key(16));
        $this->encryption->initialize(
            array(
                'cipher' => 'aes-256',
                'mode' => 'ctr',
                'key' => $key
            )
        );

        $ciphertext = $this->encryption->encrypt($password_md5);

        echo "pass_asli = " . $password_plain;
        echo "<br />";
        echo "pass_md5 = " . md5($password_plain);
        echo "<br />";
        echo "key = " . $key;
        echo "<br />";
        echo "enkrip = " . ($ciphertext);


        // $this->encryption->initialize(
        //     array(
        //         'cipher' => 'aes-256',
        //         'mode' => 'ctr',
        //         'key' => $key
        //     )
        // );

        // Outputs: This is a plain-text message!
        echo "<br />";
        echo "<br />";
        echo "dekrip = " . ($this->encryption->decrypt($ciphertext));
    }
    // view
    public function index($status = "")
    {
        // set template content
        // $this->tsmarty->assign("template_content", "bantul/sawokecik/maintenance.html");

        $this->tsmarty->assign("template_content", "bantul/sawokecik/form.html");
        // session
        $session = $this->session->userdata('session_operator');

        //print_r($this->com_user);
        // exit();
        // bisnis proses
        if (!empty($this->com_user)) {
            // echo "sss";
            // exit();
            // redirect
            redirect($session['default_page']);
        } else {
            $this->tsmarty->assign("login_st", $status);
        }

        //START LOGIN ATTEMPT SESSION
        //get data
        $login_attempt = $this->session->userdata($this->attempt_session);
        $attempt = !empty($login_attempt['attempt']) ? $login_attempt['attempt'] : 0;
        $attempt_again = !empty($login_attempt['attempt_again']) ? $login_attempt['attempt_again'] : '';
        $msg_error = !empty($login_attempt['msg_error']) ? $login_attempt['msg_error'] : '';
        $msg_success = !empty($login_attempt['attempt']) ? $login_attempt['msg_success'] : '';
        //cek attempt
        if ($attempt_again != '') {
            //compare last attempt
            $now = time();
            // echo $now."<br />". $attempt_again."<br /><br />";
            if ($now >= $attempt_again) {
                //reset session
                $this->session->set_userdata($this->attempt_session, array(
                    'attempt' => 0,
                    'attempt_again' => '',
                    'msg_error' => '',
                    'msg_success' => ''
                ));
            }
        }

        // $this->session->set_userdata($this->attempt_session, array(
        //     'attempt' => 0,
        //     'attempt_again' => '',
        //     'msg_error' => '' ,
        //     'msg_success' => ''
        // ));
        //asign
        // print_r($login_attempt);
        $this->tsmarty->assign("attempt_max", $this->attempt_max);
        $this->tsmarty->assign("attempt_time", $this->attempt_time);
        $this->tsmarty->assign("login_attempt", $login_attempt);
        //END LOGIN ATTEMPT SESSION

        // clear captcha
        $captcha_data = $this->session->userdata('captcha_data');
        if (isset($captcha_data['captcha_time'])) {
            $capctha_path = 'resource/doc/captcha/' . $captcha_data['captcha_time'] . '.jpg';
            if (is_file($capctha_path)) {
                unlink($capctha_path);
            }
        }
        // set captcha

        $vals = array(
            'img_path' => FCPATH . '/resource/doc/captcha/',
            'img_url' => base_url() . '/resource/doc' . '/captcha/',
            'font_path' => FCPATH . '/resource/doc/font/COURIER.TTF',
            'pool' => '0123456789',
            'word_length' => 4,
            'font_size' => 96,
            'img_height' => 32,
            'img_width' => 96,
            'expiration' => 7200,
        );
        $captcha = create_captcha($vals);
        $captcha_data = array(
            'captcha_time' => $captcha['time'],
            'ip_address' => $_SERVER["REMOTE_ADDR"],
            'word' => $captcha['word']
        );

        $this->session->set_userdata("captcha_data", $captcha_data);
        // assign captcha
        $this->tsmarty->assign("captcha", $captcha);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }


    public function login_attempt()
    {
        // load Model
        $this->load->model('public/M_login');
        //cek jika bukan admin

        $user = $this->_get_user_agent();
        // statistik detail
        if ($this->M_login->check_exist(array($user['ip_address']))) {
            $params = array(
                "login_id" =>  $this->_get_id(),
                "user_ip_address" =>  $user['ip_address'],
                "browse_start" =>  date("Y-m-d H:i:s"),
                "browse_end" =>  date("Y-m-d H:i:s"),
                "browse_url" =>   "login/sawokecik",
                "mdb" => "1010",
                "mdb_name" =>  "visitor",
                "mdd" => date("Y-m-d H:i:s")
            );
            $this->M_login->insert($params);
        } else {
            $params = array($user['ip_address'], "login/sawokecik");
            $this->M_login->update($params);
        }
    }

    // login process
    public function login_process()
    {
        // cek input
        $this->tnotification->set_rules('username', 'Username', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('password', 'Password', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('captcha', 'Kode Captcha', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // captcha
            $captcha = trim($this->input->post('captcha', true));
            $captcha_data = $this->session->userdata('captcha_data');
            $expiration = time() - 7200;

            if ($captcha_data['word'] == $captcha and $captcha_data['ip_address'] == $_SERVER["REMOTE_ADDR"] and $captcha_data['captcha_time'] > $expiration) {
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Kode Captcha tidak sesuai!");
                // output
                redirect('bantul/sawokecik');
            }

            // params
            $username = trim($this->input->post('username', true));
            $password = trim($this->input->post('password', true));

            // get user detail
            $result = $this->M_account->get_user_login_all_roles($username, $password, $this->portal_id);
            // print_r($result);
            // exit();
            // check
            if (!empty($result)) {
                // cek lock status
                if ($result['user_st'] == '0') {
                    // default error
                    $this->tnotification->sent_notification("error", "Status pengguna anda telah di non aktifkan, hubungi administrator sistem.");
                    // output
                    redirect('bantul/sawokecik');
                }
                // set session login
                $this->session->set_userdata('session_operator', array(
                    'user_id' => $result['user_id'],
                    'role_id' => $result['role_id'],
                    'role_nm' => $result['role_nm'],
                    'instansi_cd' => $result['instansi_cd'],
                    'default_page' => $result['default_page'],
                ));
                // insert login time
                $this->M_account->save_user_login($result['user_id'], $_SERVER['REMOTE_ADDR']);

                //START LOGIN ATTEMPT SESSION
                //get session attempt
                $login_attempt = $this->session->userdata($this->attempt_session);
                $attempt = !empty($login_attempt['attempt']) ? $login_attempt['attempt'] : 0;
                $attempt_again = !empty($login_attempt['attempt_again']) ? $login_attempt['attempt_again'] : '';
                $msg_error = !empty($login_attempt['msg_error']) ? $login_attempt['msg_error'] : '';
                $msg_success = !empty($login_attempt['attempt']) ? $login_attempt['msg_success'] : '';
                //set
                $attempt = 0;
                $msg_error = '';
                $msg_success = 'login';
                //set session
                $this->session->set_userdata($this->attempt_session, array(
                    'attempt' => $attempt,
                    'attempt_again' => $attempt_again,
                    'msg_error' => $msg_error,
                    'msg_success' => $msg_success
                ));
                //END LOGIN ATTEMPT SESSION

                // redirect
                redirect($result['default_page']);
            } else {
                //START LOGIN ATTEMPT SESSION
                //get session attempt
                $login_attempt = $this->session->userdata($this->attempt_session);
                $attempt = !empty($login_attempt['attempt']) ? $login_attempt['attempt'] : 0;
                $attempt_again = !empty($login_attempt['attempt_again']) ? $login_attempt['attempt_again'] : '';
                $msg_error = !empty($login_attempt['msg_error']) ? $login_attempt['msg_error'] : '';
                $msg_success = !empty($login_attempt['attempt']) ? $login_attempt['msg_success'] : '';
                //set
                $attempt  = $attempt + 1;
                if ($attempt == $this->attempt_max) {
                    $attempt_again = time() + ($this->attempt_time * 60);
                    $msg_error = 'attempt';
                } else if ($attempt > $this->attempt_max) {
                    $msg_error = 'attempt';
                } else {
                    $msg_error = 'password';
                }
                $msg_success = '';
                //set session
                $this->session->set_userdata($this->attempt_session, array(
                    'attempt' => $attempt,
                    'attempt_again' => $attempt_again,
                    'msg_error' => $msg_error,
                    'msg_success' => $msg_success
                ));
                //END LOGIN ATTEMPT SESSION

                // default error
                $this->tnotification->sent_notification("error", "Username atau password salah.");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Isikan username dan password anda.");
        }
        // output
        redirect('bantul/sawokecik');
    }

    // logout process
    public function logout_process()
    {
        // user id
        $user_id = $this->session->userdata('session_operator');
        // insert logout time
        $this->M_account->update_user_logout($user_id['user_id']);
        // unset session
        $this->session->unset_userdata('session_operator');
        // output
        redirect('bantul/sawokecik');
    }


    // reload captcha
    public function ajax_reload_captcha()
    {
        // header
        header('Content-Type: application/json');
        // clear captcha
        $captcha_data = $this->session->userdata('captcha_data');
        if (isset($captcha_data['captcha_time'])) {
            $capctha_path = 'resource/doc/captcha/' . $captcha_data['captcha_time'] . '.jpg';
            if (is_file($capctha_path)) {
                unlink($capctha_path);
            }
        }
        // set captcha
        $vals = array(
            'img_path' => FCPATH . '/resource/doc/captcha/',
            'img_url' => base_url() . '/resource/doc' . '/captcha/',
            'font_path' => FCPATH . '/resource/doc/font/COURIER.TTF',
            'pool' => '0123456789',
            'word_length' => 4,
            'font_size' => 96,
            'img_height' => 32,
            'img_width' => 96,
            'expiration' => 7200,
        );
        $captcha = create_captcha($vals);
        $captcha_data = array(
            'captcha_time' => $captcha['time'],
            'ip_address' => $_SERVER["REMOTE_ADDR"],
            'word' => $captcha['word'],
        );
        $this->session->set_userdata("captcha_data", $captcha_data);
        // encode
        echo json_encode($captcha);
        exit();
    }


    // lupa password
    public function lupa_password()
    {
        // set template content
        $this->tsmarty->assign("template_content", "bantul/sawokecik/lupa_password.html");

        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // lupa password process
    public function lupa_password_process()
    {
        // cek input
        $this->tnotification->set_rules('user_mail', 'Email', 'trim|required|max_length[50]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $user_mail = trim($this->input->post('user_mail', TRUE));
            // get user by mail
            $result = $this->M_account->get_pegawai_by_email($user_mail);
            if ($result) {
                // reset password
                $time = microtime(true);
                $data_id = str_replace('.', '', $time);
                $request_key = $this->M_account->rand_password(50);
                $params = array(
                    'data_id' => $data_id,
                    'email' => $user_mail,
                    'nama_lengkap' => $result['nama_lengkap'],
                    'nomor_telepon' => $result['nomor_telepon'],
                    'request_st' => 'waiting',
                    'request_date' => date('Y-m-d H:i:s'),
                    'request_key' => $request_key,
                );
                // insert
                if ($this->M_account->insert_reset($params)) {
                    // config
                    $email_params['to'] = $user_mail;
                    $email_params['cc'] = array();
                    $email_params['subject'] = 'Reset Password, Pemkab Bantul Manajemen Tools';
                    $email_params['message']['title'] = 'Permintaan untuk reset password';
                    $email_params['message']['greetings'] = 'Hai ' . $result['nama_lengkap'] . ',';
                    $email_params['message']['intro'] = 'Anda ingin melakukan reset password pada aplikasi ini. Gunakan link berikut ini dan ikuti langkah selanjutnya : ';
                    $link = site_url('bantul/sawokecik/reset_password/' . md5($request_key));
                    $email_params['message']['details'] = '<p><a href="' . $link . '" target="blank">Ya, Saya akan melakukan reset password.</a></p>';
                    $email_params['attachments'] = array();
                    // print_r($email_params); exit();
                    // set email parameters
                    $this->M_email->set_mail($email_params);
                    // send
                    if ($this->M_email->send_mail('01')) {
                        // default success
                        $this->tnotification->delete_last_field();
                        $this->tnotification->sent_notification("success", "Link reset password telah dikirimkan.");
                    } else {
                        // default error
                        $this->tnotification->sent_notification("error", "Mohon maaf, email gagal dikirimkan.");
                    }
                } else {
                    // default error
                    $this->tnotification->sent_notification("error", "Mohon maaf, permintaan anda gagal diproses.");
                }
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Mohon maaf, email anda tidak terdaftar.");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Isikan Email anda.");
        }
        // output
        redirect('bantul/sawokecik/lupa_password');
    }

    // proses reset
    public function reset_password($id_encoded = '')
    {
        // get request
        $result = $this->M_account->get_reset_data_by_id(array($id_encoded));
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Permintaan reset password anda sudah kadaluarsa!");
            // default redirect
            redirect('bantul/sawokecik');
        } else {
            // get detail
            $detail = $this->M_account->get_pegawai_by_email(array($result['email']));
            if (empty($detail)) {
                // default error
                $this->tnotification->sent_notification("error", "Maaf, permintaan anda tidak dapat diproses. User Account yang anda minta tidak terdaftar!");
                // default redirect
                redirect('bantul/sawokecik');
            } else {
                // generate new password
                $new_password = $this->M_account->rand_password();
                $password_key = abs(crc32($new_password));
                $password = $this->encrypt->encode(md5($new_password), $password_key);
                // params
                $params = array(
                    'user_key' => $password_key,
                    'user_pass' => $password,
                );
                $where = array(
                    'user_id' => $detail['user_id'],
                );
                if ($this->M_account->update_user($params, $where)) {
                    // update request
                    $params = array(
                        'request_st' => 'done',
                        'response_by' => 'BY EMAIL',
                        'response_date' => date('Y-m-d H:i:s'),
                    );
                    $where = array(
                        'data_id' => $result['data_id'],
                    );
                    $this->M_account->update_reset($params, $where);
                    // send email
                    // config
                    $email_params['to'] = $detail['user_mail'];
                    $email_params['cc'] = array();
                    $email_params['subject'] = 'Reset Password, Pemkab Bantul Management Tools';
                    $email_params['message']['title'] = 'Password telah diperbaharui';
                    $email_params['message']['greetings'] = 'Hi ' . $detail['nama_lengkap'] . ',';
                    $email_params['message']['intro'] = 'Anda berhasil melakukan reset password pada user account anda. Berikut ini adalah password baru anda : ';
                    // detail
                    $message = '<table cellspacing="0" cellpadding="0" border="0">';
                    $message .= '<tbody>';
                    $message .= '<tr>';
                    $message .= '<td style="width: 100px;">Password</td>';
                    $message .= '<td><b>' . $new_password . '</b></td>';
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
                    // notification
                    $this->tnotification->delete_last_field();
                    $this->tnotification->sent_notification("success", "Password berhasil direset!");
                } else {
                    // default error
                    $this->tnotification->sent_notification("error", "Maaf, data tidak dapat diproses. Ulangi reset dan akitifasi pada email anda!");
                }
            }
        }
        // default redirect
        redirect('bantul/sawokecik');
    }


    protected function _get_user_agent()
    {
        $u_agent     = $_SERVER['HTTP_USER_AGENT'];
        $bname       = 'Unknown';
        $platform     = 'Unknown';
        $platform_detail     = 'Unknown';
        $version     = "";

        $os_array   =   array(
            '/windows nt 10.0/i'     =>  array('platform' => 'windows', 'platform_detail' => 'Windows 10'),
            '/windows nt 6.2/i'     =>   array('platform' => 'windows', 'platform_detail' => 'Windows 8'),
            '/windows nt 6.1/i'     =>   array('platform' => 'windows', 'platform_detail' => 'Windows 7'),
            '/windows nt 6.0/i'     =>   array('platform' => 'windows', 'platform_detail' => 'Windows Vista'),
            '/windows nt 5.2/i'     =>   array('platform' => 'windows', 'platform_detail' => 'Windows Server 2003/XP x64'),
            '/windows nt 5.1/i'     =>   array('platform' => 'windows', 'platform_detail' => 'Windows XP'),
            '/windows xp/i'         =>   array('platform' => 'windows', 'platform_detail' => 'Windows XP'),
            '/windows nt 5.0/i'     =>   array('platform' => 'windows', 'platform_detail' => 'Windows 2000'),
            '/windows me/i'         =>   array('platform' => 'windows', 'platform_detail' => 'Windows ME'),
            '/win98/i'              =>   array('platform' => 'windows', 'platform_detail' => 'Windows 98'),
            '/win95/i'              =>   array('platform' => 'windows', 'platform_detail' => 'Windows 95'),
            '/win16/i'              =>   array('platform' => 'windows', 'platform_detail' => 'Windows 3.11'),
            '/macintosh|mac os x/i' =>   array('platform' => 'macos', 'platform_detail' => 'Mac OS X'),
            '/mac_powerpc/i'        =>   array('platform' => 'macos', 'platform_detail' => 'Mac OS 9'),
            '/linux/i'              =>   array('platform' => 'linux', 'platform_detail' => 'Linux'),
            '/ubuntu/i'             =>   array('platform' => 'linux', 'platform_detail' => 'Ubuntu'),
            '/iphone/i'             =>   array('platform' => 'ios', 'platform_detail' => 'iPhone'),
            '/ipod/i'               =>   array('platform' => 'ios', 'platform_detail' => 'iPod'),
            '/ipad/i'               =>   array('platform' => 'ios', 'platform_detail' => 'iPad'),
            '/android/i'            =>   array('platform' => 'android', 'platform_detail' => 'Android'),
            '/blackberry/i'         =>   array('platform' => 'blackberry', 'platform_detail' => 'BlackBerry'),
            '/webos/i'              =>   array('platform' => 'webos', 'platform_detail' => 'Web OS')
        );

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $u_agent)) {
                $platform    =   $value['platform'];
                $platform_detail =   $value['platform_detail'];
                break;
            }
        }

        //get browser
        $arr_browsers = ["Opera", "Edge", "Chrome", "Safari", "Firefox", "MSIE", "Trident"];
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $user_browser = '';
        foreach ($arr_browsers as $browser) {
            if (strpos($agent, $browser) !== false) {
                $user_browser = $browser;
                break;
            }
        }
        switch ($user_browser) {
            case 'MSIE':
                $user_browser = 'Internet Explorer';
                break;

            case 'Trident':
                $user_browser = 'Internet Explorer';
                break;

            case 'Edge':
                $user_browser = 'Microsoft Edge';
                break;
        }

        // echo "You are using ".$user_browser." browser";

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        //  finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        $version = ($version == null || $version == "") ? "?" : $version;

        // get mobile /dekstop
        $deviceType = 0;
        if (is_numeric(strpos(strtolower($u_agent), "mobile"))) {
            $deviceType =  is_numeric(strpos(strtolower($u_agent), "tablet")) ? 2 : 1;
        } else {
            $deviceType = 0;
        }

        if ($deviceType == 0) {
            $device = "dekstop";
        } else if ($deviceType == 1) {
            $device =  "mobile";
        } else {
            $device =  "tablet";
        }

        //get ip addrs
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }


        return array(
            'ip_address' => $ip,
            'userAgent' => $u_agent,
            'name'      => $user_browser,
            'version'   => $version,
            'platform'  => $platform,
            'platform_detail' => $platform_detail,
            'device'  => $device,
            'pattern'   => $pattern
        );
    }

    protected function _get_id()
    {
        $micro = explode(' ', microtime());
        $micro[0] = preg_replace('/(\ |,|\.)/', '', $micro[0]);
        return $micro[1] . $micro[0];
    }
}
