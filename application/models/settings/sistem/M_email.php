<?php

class M_email extends CI_Model {

    //put your code here
    public function __construct() {
        parent::__construct();
        // load library
        $this->load->library('email');
    }

    // get last id
    function get_email_last_id() {
        $sql = "SELECT email_id as 'last_number' FROM com_email ORDER BY email_id DESC LIMIT 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            // create next number
            $number = intval($result['last_number']) + 1;
            if ($number >= 99) {
                return false;
            }
            $zero = '';
            for ($i = strlen($number); $i < 2; $i++) {
                $zero .= '0';
            }
            return $zero . $number;
        } else {
            // create new number
            return '01';
        }
    }

    // get list email settings
    function get_list_email() {
        $sql = "SELECT * FROM com_email";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $results = $query->result_array();
            $query->free_result();
            return $results;
        } else {
            return array();
        }
    }

    // get detail data by id
    function get_email_by_id($link_id) {
        $sql = "SELECT * FROM com_email WHERE email_id = ?";
        $query = $this->db->query($sql, $link_id);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $query->free_result();
            return $result;
        } else {
            return array();
        }
    }

    // insert data email
    function insert_email($params) {
        return $this->db->insert('com_email', $params);
    }

    // update data email
    function update_email($params, $where) {
        return $this->db->update('com_email', $params, $where);
    }

    // delete data email
    function delete_email($params) {
        return $this->db->delete('com_email', $params);
    }

    // send mail
    function sendmail($from, $to, $subject, $message, $attachments = array(), $cc = array(), $bcc = array()) {
        //set default return status false and message empty
        $return = array('status' => FALSE, 'message' => '');
        //clear email
        $this->email->clear(TRUE);
        //get data
        $result = $from;
        //config
        $config['mailtype'] = 'html';
        $config['charset'] = 'utf-8';
        if ($result['use_smtp'] == '1') {
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = $result['smtp_host'];
            $config['smtp_port'] = $result['smtp_port'];
            if ($result['use_authorization'] == '1') {
                $config['smtp_user'] = $result['smtp_username'];
                $config['smtp_pass'] = $result['smtp_password'];
            }
        }
        // initialize
        $this->email->initialize($config);
        $this->email->from($result['email_address'], $result['email_name']);
        $this->email->to($to);
        $this->email->cc($cc);
        $this->email->cc($bcc);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_newline("\r\n");
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $this->email->attach($attachment);
            }
        }
        // send
        if ($this->email->send()) {
            $return = array('status' => TRUE, 'message' => '');
        } else {
            $return = array('status' => FALSE, 'message' => 'Email gagal dikirim.');
            $debug = TRUE;
            if ($debug) {
                echo $this->email->print_debugger();
                exit();
            }
        }
        return $return;
    }

}
