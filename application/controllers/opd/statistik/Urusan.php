<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Urusan extends OperatorBase
{

    // constructor
    public function __construct()
    {
        parent::__construct();
        // load model
        $this->load->model('admin/statistik/M_urusan');
        $this->load->model('settings/M_user');
        // load library
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    // welcome operator
    public function index()
    {
        // get user = opd
        $user_id = $this->com_user['user_id'];
        $result_user = $this->M_user->get_data_user_by_id($user_id);
        $params = $result_user['instansi_cd'];
        // print_r($result_user['user_name']);
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/statistik/urusan/index.html");
        //get data
        $rs_id = $this->M_urusan->get_all($params);
        //print_r($rs_id);
        //assign data
        $this->tsmarty->assign("rs_id", $rs_id);
        // user
        $this->tsmarty->assign('user', $this->com_user['user_id']);
        $this->tsmarty->assign("result_user", $result_user);
        // output
        parent::display();
    }
}
