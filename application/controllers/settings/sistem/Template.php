<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

class Template extends AdminBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
    }

    // index
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/template/index.html");
        // output
        parent::display();
    }

    // add
    public function add() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/template/add.html");
        // load js
        $this->tsmarty->load_javascript("resource/themes/default/plugins/uniform/uniform.min.js");
        // output
        parent::display();
    }

}
