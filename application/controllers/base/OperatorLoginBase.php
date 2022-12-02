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
        $this->tsmarty->load_themes("metronic.1.0");
        // load base models
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/metronic.1.0/plugins/global/plugins.bundle.js");
        $this->tsmarty->load_javascript("resource/themes/metronic.1.0/js/scripts.bundle.js");
        $this->tsmarty->load_javascript("resource/themes/metronic.1.0/plugins/custom/fullcalendar/fullcalendar.bundle.js");
        $this->tsmarty->load_javascript("resource/themes/metronic.1.0/js/scripts.bundle.js");
        $this->tsmarty->load_javascript("resource/themes/metronic.1.0/plugins/custom/gmaps/gmaps.js");
        $this->tsmarty->load_javascript("resource/themes/metronic.1.0/js/pages/dashboard.js");
    }

    /*
     * Method pengolah base view
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function base_view_app() {
        $this->tsmarty->assign("config", $this->config);
        // display site title
        self::_display_site_title();
        // get session admin
        self::_get_admin_session();
    }

    /*
     * Method layouting base document
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function display($tmpl_name = 'base/operatorlogin/document-login.html') {
        // set template
        $this->tsmarty->display($tmpl_name);
    }

    //
    // base private method here
    // prefix ( _ )
    // site title
    private function _display_site_title() {
        $this->portal_id = $this->config->item('portal_operator');
        // site data
        $this->com_portal = $this->M_site->get_site_data_by_id($this->portal_id);
        if (!empty($this->com_portal)) {
            $this->tsmarty->assign("site", $this->com_portal);
        }
    }

    // get session admin
    private function _get_admin_session() {
        // session admin
        $this->com_user = $this->session->userdata('session_operator');
    }

}
