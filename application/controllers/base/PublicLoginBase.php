<?php

class ApplicationBase extends CI_Controller
{

    // init base variable
    protected $portal_id;
    protected $com_portal;
    protected $com_user;
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
      

     
        // load style
        // $this->tsmarty->load_style('blora-backend/plugins/font-awesome/css/font-awesome.css');

        $this->load->library("tdtm");
        $this->tsmarty->assign("dtm", $this->tdtm);
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
        // display global link
        self::_display_base_link();
        // display site title
        self::_display_site_title();
        // display current page
        self::_display_current_page();
        // check security
        self::_check_authority();
        // display top navigation
        self::_display_top_navigation();
        // display sidebar navigation
        // self::_display_sidebar_navigation();
    }

    /*
     * Method layouting base document
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function display($tmpl_name = 'base/publiclogin/document.html')
    {
        // --
        // $this->tsmarty->assign("template_topbar", "base/publiclogin/topbar.html");
        // set template
        $this->tsmarty->display($tmpl_name);
    }

    //
    // base private method here
    // prefix ( _ )
    // base link
    private function _display_base_link()
    { 
        
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

    // get current page
    private function _display_current_page()
    {
        // get current page (segment 1 : folder, segment 2 : sub folder, segment 3 : controller)
        $url_menu = $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3);
        $url_menu = trim($url_menu, '/');
        $url_menu = (empty($url_menu)) ? 'admin/dashboard/dashboard' : $url_menu;
        $result = $this->M_site->get_current_page(array($url_menu, $this->portal_id));

        // print_r($url_menu);
        // print_r($result);
        // exit();

        if (!empty($result)) {
            $this->tsmarty->assign("page", $result);
            $this->nav_id = $result['nav_id'];
            $this->parent_id = $result['parent_id'];
        }
    }

    // authority
    protected function _check_authority()
    {
        // default rule tp
        $this->role_tp = array("C" => "0", "R" => "0", "U" => "0", "D" => "0");

        // check
        // if (!empty($this->com_user)) {
        //     // user authority
        //     $params = array($this->com_user['user_id'], $this->nav_id, $this->portal_id);
        //     $role_tp = $this->M_site->get_user_authority_by_nav($params);
            
        //     // get rule tp
        //     $i = 0;
        //     foreach ($this->role_tp as $rule => $val) {
        //         $N = substr($role_tp, $i, 1);
        //         $this->role_tp[$rule] = $N;
        //         $i++;
        //     }
        // } else {
        //     // tidak memiliki authority
        //     //redirect('login/operator/logout_process');
        //     redirect('login/operator/logout_process');
        // }
    }

    // set rule per pages
    protected function _set_page_rule($rule)
    {
        //exit();
        // if (!isset($this->role_tp[$rule]) or $this->role_tp[$rule] != "1") {

        //     // redirect to forbiden access
        //     redirect('admin/setting/forbidden/page/' . $this->nav_id);
        // }
    }

    // top navigation
    // private function _display_top_navigation()
    // {
    //     // get parent selected
    //     $this->parent_selected = self::_get_parent_group($this->parent_id, 0);
    //     if ($this->parent_selected == 0) {
    //         $this->parent_selected = $this->nav_id;
    //     }
    //     // get data
    //     // $params = array($this->portal_id, 2, $this->com_user['user_id'], 0);
    //     $params = array($this->portal_id, $this->com_user['user_id'], 0);
    //     $rs_id = $this->M_site->get_navigation_user_by_parent($params);

        

    //     $this->tsmarty->assign("list_top_nav", $rs_id);
    //     $this->tsmarty->assign("top_menu_selected", $this->parent_selected);
    // }

    // sidebar navigation
    private function _display_top_navigation()
    {
        $html = "";

        // get data
        if(1==1){
            // $params = array($this->portal_id, $this->com_user['user_id'], 0);
            // $rs_id = $this->M_site->get_navigation_user_by_parent($params);
            $params = array($this->portal_id, 0);
            $rs_id = $this->M_site->get_navigation_public_by_parent($params);
            // print_r($rs_id);
        }

        if ($rs_id) {
            foreach ($rs_id as $rec) {
                // get parent selected
                $parent_active = '';
                $parent_selected = self::_get_parent_group($this->parent_id, $this->parent_selected);
                if ($parent_selected == 0) {
                    $parent_selected = $this->nav_id;
                }
                // parent active
                if ($this->parent_selected == $rec['nav_id']) {
                    $parent_active = 'class="active"';
                }

                $selected = ($rec['nav_id'] == $parent_selected) ? '--active' : "";
                // echo $rec['nav_id'].$parent_selected;
                // exit();
                $url_parent = "#";
                $icon = "fa fa-table";
                if (!empty($rec['nav_icon'])) {
                    $icon = "fa " . $rec['nav_icon'];
                }
                // $icon = "fa fa-table";
                // check child
                $child = $this->_get_child_navigation($rec['nav_id']);
                if (empty($child)) {
                    if($rec['nav_desc'] == "#"){
                        $html .= '<li>';
                        $html .= '  <a href="#" class="sub-menu-item">'.($rec['nav_title']).'</a>';
                        $html .= '</li>';
                    }else{
                        $url_parent = site_url($rec['nav_url']);
                        $html .= '<li '.$parent_active.'>';
                        $html .= '  <a href="'.$url_parent.'" class="sub-menu-item">'.($rec['nav_title']).'</a>';
                        $html .= '</li>';
                    }
                } else {
                    // $selected = ($rec['nav_id'] == $parent_selected) ? 'kt-menu__item--here kt-menu__item--open' : "";
                    $url_parent = site_url($rec['nav_url']);
                    $html .= '<li class="has-submenu parent-menu-item '.$parent_active.'">';
                    $html .= '  <a href="javascript:void(0)">'.($rec['nav_title']).'</a><span class="menu-arrow"></span>';
                    $html .= '  <ul class="submenu">';
                    // get child navigation
                    $html .=        $child;
                    $html .= "  </ul>";
                    $html .= "</li>";
                }
            }
        }
        $this->tsmarty->assign("list_topbar_nav", $html);
    }


     // get child
     private function _get_child_navigation($parent_id)
     {
         $html = "";
         // if parent selected then show child
         $params = array($this->portal_id,  $parent_id);
         $rs_id = $this->M_site->get_navigation_public_by_parent($params);
 
        // get parent selected
        $parent_selected = self::_get_parent_group($this->parent_id, $parent_id);
        if ($parent_selected == 0) {
            $parent_selected = $this->nav_id;
        }
         
         if ($rs_id) {
             foreach ($rs_id as $rec) {
                 //check jika kosong
                $selected = ($rec['nav_id'] == $parent_selected) ? "active" : "";
                // parse
                $html .= '<li>';
                $html .= '  <a href="' . site_url($rec['nav_url']) . '" class="sub-menu-item '.$selected.'">';
                $html .=        $rec['nav_title'];
                $html .= '  </a>';
                $html .= '</li>';
             }
         }
         return $html;
     }



    // utility to get parent selected
    private function _get_parent_group($int_nav, $int_limit)
    {
        $selected_parent = 0;
        $result = $this->M_site->get_menu_by_id($int_nav);
        if (!empty($result)) {
            if ($result['parent_id'] == $int_limit) {
                $selected_parent = $result['nav_id'];
            } else {
                return self::_get_parent_group($result['parent_id'], $int_limit);
            }
        } else {
            $selected_parent = null;
        }
        return $selected_parent;
    }
}
