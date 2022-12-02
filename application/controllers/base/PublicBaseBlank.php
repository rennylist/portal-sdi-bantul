<?php

class ApplicationBase extends CI_Controller
{

    // init base variable
    protected $portal_id;
    protected $com_portal;
    protected $com_user;
    protected $nav_login_st;
    protected $nav_id = 0;
    protected $parent_id = 0;
    protected $parent_selected = 0;
    protected $role_tp = array();

    public function __construct()
    {
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

    protected function base_load_app()
    {
        // load themes (themes default : default)
        $this->tsmarty->load_themes("landrick.1.0");
        // load base models
 

        // load javascript
        $this->tsmarty->load_javascript("resource/themes/landrick.1.0/js/bootstrap.bundle.min.js");
        $this->tsmarty->load_javascript("resource/themes/landrick.1.0/js/typed.js");
        $this->tsmarty->load_javascript("resource/themes/landrick.1.0/js/typed.init.js");
        $this->tsmarty->load_javascript("resource/themes/landrick.1.0/js/tiny-slider.js");
        $this->tsmarty->load_javascript("resource/themes/landrick.1.0/js/tiny-slider-init.js");
        $this->tsmarty->load_javascript("resource/themes/landrick.1.0/js/feather.min.js");
        $this->tsmarty->load_javascript("resource/themes/landrick.1.0/js/app.js");
        // $this->tsmarty->load_javascript("resource/js/jquery/jquery-3.6.0.js");

        // load style
        $this->tsmarty->load_style('landrick.1.0/css/materialdesignicons.min.css');
        $this->tsmarty->load_style("jquery/2.1.1/jquery-ui.css");
       

        //load library
        $this->load->library("tdtm");
        $this->load->library("Unirest");
        // load model
        $this->load->model('public/M_aplikasi');
        $this->load->model('public/M_preferences');       
        //assign
        $this->tsmarty->assign("tdtm", $this->tdtm);
    }

    /*
     * Method pengolah base view
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function base_view_app()
    {
        // default config
        $this->tsmarty->assign("config", $this->config);
        // get user login
        $session = $this->session->userdata('bantulpedia_user_profile');
        // print_r("<pre>");
        // print_r($session);
        // print_r("</pre>");
        // exit();
        if (!empty($session)) {
            // //nama depan
            $this->tsmarty->assign("com_user", $session);
            $this->com_user = $session;
            // print_r($this->com_user->mobile);
        }
        // display site title
        self::_display_site_title();

    }

    /*
     * Method layouting base document
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function display($tmpl_name = 'base/publicblank/document.html')
    {
        // --
        $this->tsmarty->assign("template_topbar", "base/publicblank/topbar.html");
        // set template
        $this->tsmarty->display($tmpl_name);
    }


    // site title
    private function _display_site_title()
    {
        $this->portal_id = $this->config->item('portal_id');
        $this->portal_id = 30;
        // site data
        $this->com_portal = $this->M_site->get_site_data_by_id($this->portal_id);
        if (!empty($this->com_portal)) {
            $this->tsmarty->assign("site", $this->com_portal);
        }
    }

    // set rule per pages
    protected function _set_page_rule($rule)
    {
        // exit();
        if($this->nav_login_st == "yes"){
            if (!isset($this->role_tp[$rule]) or $this->role_tp[$rule] != "1") {

                // redirect to forbiden access
                // redirect('admin/setting/forbidden/page/' . $this->nav_id);
                redirect('error/notfound');
            }
        }
    }

  
}
