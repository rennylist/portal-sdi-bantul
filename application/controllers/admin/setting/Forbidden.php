<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

// --

class forbidden extends AdminBase {

    // constructor
    public function __construct() {
        parent::__construct();
        $this->load->model('apps/m_site');
    }

    // forbidden page
    public function page($nav_id = "") {
        // set template content
        $this->tsmarty->assign("template_content", "admin/setting/forbidden/index.html");
        // get navigation info
        $result = $this->m_site->get_menu_by_id($nav_id);
        if (!empty($result)) {
            $this->tsmarty->assign("nav", $result);
        } else {
            $result['nav_url'] = 'login/loginoperator';
            $this->tsmarty->assign("nav", $result);
        }
        // output
        parent::display();
    }

}