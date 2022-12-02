<?php

class m_email extends CI_Model {

    //put your code here
    public function __construct() {
        parent::__construct();
        //load library
        $this->load->library('email');
    }

    //get list email settings format : key=>val
    function get_settings_mail_clean() {
        $sql = "SELECT pref_nm, pref_value
                FROM com_preferences
                WHERE pref_group = 'email'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $results = $query->result_array();
            $query->free_result();
            $result = array();
            foreach ($results as $data) {
                $result[$data['pref_nm']] = $data['pref_value'];
            }
            return $result;
        } else {
            return array();
        }
    }

    //get list email settings format : select all
    function get_settings_mail() {
        $sql = "SELECT *
                FROM com_preferences
                WHERE pref_group = 'email'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    function edit_email_preference($params, $where) {
        $this->db->update('com_preferences', $params, $where);
    }

    function sendmail($to, $subject, $message, $attachments = array(), $cc = array(), $bcc = array()) {
        //set default return status false and message empty
        $return = array('status'=>FALSE,'message'=>'');
        //clear email
        $this->email->clear(TRUE);
        //get preferences
        $result = $this->get_settings_mail_clean();
        //config
        $config['mailtype'] = 'html';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        if ($result['use_smtp'] == '1') {
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = str_replace(" ", "", "ssl://".$result['smtp_host']);
            $config['smtp_port'] = $result['smtp_port'];
            if ($result['use_authorization'] == '1') {
                $config['smtp_user'] = $result['smtp_username'];
                $config['smtp_pass'] = $result['smtp_password'];
            }
        }
        $this->email->initialize($config);
        $this->email->from($result['admin_email'], $result['admin_name']);
        $this->email->to($to);
        $this->email->cc($cc);
        $this->email->cc($bcc);
        $this->email->subject($subject);
        $this->email->message($message);
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $this->email->attach($attachment);
            }
        }
        if ($this->email->send()) {
            $return = array('status'=>TRUE,'message'=>'');
        }else{
            $return = array('status'=>FALSE,'message'=>'Email gagal dikirim.');
            $debug = FALSE;
            if($debug){
                echo $this->email->print_debugger();exit();
            }
        }
        return $return;
    }

    
    function sendmail_manual($to, $subject, $message, $attachments = array(), $cc = array(), $bcc = array()) {
        //set default return status false and message empty
        $return = array('status'=>FALSE,'message'=>'');
        //clear email
        $this->email->clear(TRUE);
        //get preferences
        $result = $this->get_settings_mail_clean();
        //config
        $config['mailtype'] = 'html';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        if ($result['use_smtp'] == '1') {
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = str_replace(" ", "", "ssl://".$result['smtp_host']);
            $config['smtp_port'] = $result['smtp_port'];
            if ($result['use_authorization'] == '1') {
                $config['smtp_user'] = $result['smtp_username'];
                $config['smtp_pass'] = $result['smtp_password'];
            }
        }
        $this->email->initialize($config);
        $this->email->from($result['admin_email'], $result['admin_name']);
        $this->email->to($to);
        $this->email->cc($cc);
        $this->email->cc($bcc);
        $this->email->subject($subject);
        $this->email->message($message);
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $this->email->attach($attachment);
            }
        }
        if ($this->email->send()) {
            $return = array('status'=>TRUE,'message'=>'');
        }else{
            $return = array('status'=>FALSE,'message'=>'Email gagal dikirim.');
            $debug = FALSE;
            if($debug){
                echo $this->email->print_debugger();exit();
            }
        }
        return $return;
    }

    function sendmail_multiple($to = array(), $subject, $message, $attachments = array(), $cc = array(), $bcc = array()) {
        //set default return status false and message empty
        $return = array('status'=>FALSE,'message'=>'');
        //clear email
        $this->email->clear(TRUE);
        //get preferences
        $result = $this->get_settings_mail_clean();
        //config
        $config['mailtype'] = 'html';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        if ($result['use_smtp'] == '1') {
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = str_replace(" ", "", "ssl://".$result['smtp_host']);
            $config['smtp_port'] = $result['smtp_port'];
            if ($result['use_authorization'] == '1') {
                $config['smtp_user'] = $result['smtp_username'];
                $config['smtp_pass'] = $result['smtp_password'];
            }
        }
        $this->email->initialize($config);
        $this->email->from($result['admin_email'], $result['admin_name']);
        $this->email->to($to);
        $this->email->cc($cc);
        $this->email->cc($bcc);
        $this->email->subject($subject);
        $this->email->message($message);
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $this->email->attach($attachment);
            }
        }
        if ($this->email->send()) {
            $return = array('status'=>TRUE,'message'=>'');
        }else{
            $return = array('status'=>FALSE,'message'=>'Email gagal dikirim.');
            $debug = FALSE;
            if($debug){
                echo $this->email->print_debugger();exit();
            }
        }
        return $return;
    }

}
