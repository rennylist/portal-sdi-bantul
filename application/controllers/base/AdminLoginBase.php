<?php

class ApplicationBase extends CI_Controller {

    // init base variable
    protected $portal_id;
    protected $com_portal;
    protected $com_user;

    public function __construct() {
        // load basic controller
        parent::__construct();
        // load app data
        $this->base_load_app();
        // view app data
        $this->base_view_app();
    }

    /*
     * Method pengolah base load
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function base_load_app() {
        // load themes (themes default : default)
        $this->tsmarty->load_themes("default");
        // load base models
        $this->load->model('apps/M_email');
        $this->load->model('apps/M_account');
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/jquery/dist/jquery.min.js");
        $this->tsmarty->load_javascript("resource/themes/default/plugins/popper.js/dist/umd/popper.min.js");
        $this->tsmarty->load_javascript("resource/themes/default/plugins/bootstrap/dist/js/bootstrap.min.js");
    }

    /*
     * Method pengolah base view
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function base_view_app() {
        // assign config
        $this->tsmarty->assign("config", $this->config);
        // display site title
        self::_display_site_title();
        // get session
        self::_get_user_session();
    }

    /*
     * Method layouting base document
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function display($tmpl_name = 'base/default/document-login.html') {
        // set template
        $this->tsmarty->display($tmpl_name);
    }

    //
    // base private method here
    // prefix ( _ )
    // site title
    private function _display_site_title() {
        $this->portal_id = $this->config->item('portal_id');
        // site data
        $this->com_portal = $this->M_site->get_site_data_by_id($this->portal_id);
        if (!empty($this->com_portal)) {
            $this->tsmarty->assign("site", $this->com_portal);
        }
    }

    // get session
    private function _get_user_session() {
        // session admin
        $this->com_user = $this->session->userdata('session_te_tools');
    }

}
