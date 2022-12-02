<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// load base
require_once( APPPATH . 'controllers/base/AdminBase.php' );

// --
class Dashboard extends AdminBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('settings/welcome/M_dashboard');
    }

    // dashboard
    public function index() {
        // set page rules
        // $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/welcome/dashboard/index.html");
        // get last login
        $last_login = $this->M_dashboard->get_last_login($this->com_user['user_id']);
        $this->tsmarty->assign("last_login", $last_login);
        // output
        parent::display();
    }

}
