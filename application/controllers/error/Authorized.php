<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// load base
require_once( APPPATH . 'controllers/base/PublicBase.php' );

// --
class authorized extends ApplicationBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load notification
    }

    // dashboard
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "error/authorized/index.html");
        //set
        $page['nav_title'] = "Error";
        //asssign
        $this->tsmarty->assign("page", $page);
        // output
        parent::display();
    }


}
