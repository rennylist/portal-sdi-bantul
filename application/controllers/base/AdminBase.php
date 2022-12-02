<?php

class AdminBase extends CI_Controller {

    // init base variable
    protected $portal_id;
    protected $com_portal;
    protected $com_user;
    protected $nav_id = 0;
    protected $parent_id = 0;
    protected $parent_selected = 0;
    protected $role_tp = array();

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
        // load library
        $this->load->library("tdtm");
        $this->tsmarty->assign("dtm", $this->tdtm);
    }

    /*
     * Method pengolah base view
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function base_view_app() {
        // default config
        $this->tsmarty->assign("config", $this->config);
        // get user login
        $session = $this->session->userdata('session_id');
        // print_r("sss");
        // print_r( $session);
        // exit();

        if (!empty($session)) {
            // get com user
            $this->com_user = $this->M_account->get_pegawai_login_by_id(array($session['user_id']));
            // get user img
            // default
            $this->com_user['user_img'] = base_url() . 'resource/doc/images/users/default.png';
            $image_path = trim($this->com_user['user_img_path'], '/') . '/' . trim($this->com_user['user_img_name'], '/');
            // check images path
            if (is_file($image_path)) {
                $this->com_user['user_img'] = base_url() . $image_path;
            }
            // assign user
            $this->tsmarty->assign("com_user", $this->com_user);
        }
        // get date now
        $hari_ini = $this->tdtm->get_date_now();
        $this->tsmarty->assign("hari_ini", $hari_ini);
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
        self::_display_sidebar_navigation();
    }

    /*
     * Method layouting base document
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function display($tmpl_name = 'base/default/document.html') {
        // --
        $this->tsmarty->assign("template_sidebar", "base/default/sidebar.html");
        // set template
        $this->tsmarty->display($tmpl_name);
    }

    // base private method here
    // prefix ( _ )
    // base link
    private function _display_base_link() {
        
    }

    // site title
    private function _display_site_title() {
        $this->portal_id = $this->config->item('portal_id');
        // site data
        $this->com_portal = $this->M_site->get_site_data_by_id($this->portal_id);
        if (!empty($this->com_portal)) {
            $this->tsmarty->assign("site", $this->com_portal);
        }
    }

    // get current page
    private function _display_current_page() {
        // get current page (segment 1 : folder, segment 2 : sub folder, segment 3 : controller)
        $url_menu = $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3);
        $url_menu = trim($url_menu, '/');
        $url_menu = (empty($url_menu)) ? 'home/welcome/dashboard' : $url_menu;
        $result = $this->M_site->get_current_page(array($url_menu, $this->portal_id));
        if (!empty($result)) {
            $this->tsmarty->assign("page", $result);
            $this->nav_id = $result['nav_id'];
            $this->parent_id = $result['parent_id'];
        }
    }

    // authority
    protected function _check_authority() {

        // echo $this->com_user;
        // print_r("ssss");
        // exit();

        // default rule tp
        $this->role_tp = array("C" => "0", "R" => "0", "U" => "0", "D" => "0");
        // check
        if (!empty($this->com_user)) {
            // user authority
            $params = array($this->com_user['user_id'], $this->nav_id, $this->portal_id);
            $role_tp = $this->M_site->get_user_authority_by_nav($params);
            // get rule tp
            $i = 0;
            foreach ($this->role_tp as $rule => $val) {
                $N = substr($role_tp, $i, 1);
                $this->role_tp[$rule] = $N;
                $i++;
            }
        } else {
            // tidak memiliki authority
            redirect('bantul/backend/logout_process');
        }
    }

    // set rule per pages
    protected function _set_page_rule($rule) {
        // print_r("ssss");
        // exit();

        if (!isset($this->role_tp[$rule]) or $this->role_tp[$rule] != "1") {
            // redirect to forbiden access
            redirect('bantul/backend/logout_process');
        }
    }

    // top navigation
    protected function _display_top_navigation() {
        $html = "";
        // get data
        $params = array($this->portal_id, $this->com_user['user_id'], 0);
        $rs_id = $this->M_site->get_navigation_user_by_parent($params);
        if ($rs_id) {
            $html = '';
            foreach ($rs_id as $rec) {
                // parent active
                $parent_class = '';
                $parent_class_a = '';
                $parent_active = '';
                $this->parent_selected = self::_get_parent_group($this->parent_id, 0);
                if ($this->parent_selected == 0) {
                    $this->parent_selected = $this->nav_id;
                }
                // parent active
                if ($this->parent_selected == $rec['nav_id']) {
                    $parent_active = 'btn-warning active';
                } else {
                    $parent_active = 'btn-warning';
                }
                // data
                $html .= '<a class="btn btn-sm text-dark mr-1 mb-2 ' . $parent_active . '" style="width: 30px;" title="' . $rec['nav_title'] . '" data-toggle="tab" data-target="' . $rec['nav_url'] . '"><i class="' . $rec['nav_icon'] . '"></i></a>';
            }
        }
        // output
        $this->tsmarty->assign("list_top_nav", $html);
    }

    // sidebar navigation
    protected function _display_sidebar_navigation() {
        $html = "";
        // get data
        $params = array($this->portal_id, $this->com_user['user_id'], 0);
        $rs_tab = $this->M_site->get_navigation_user_by_parent($params);
        if ($rs_tab) {
            foreach ($rs_tab as $tab) {
                // parent active
                $parent_active = '';
                $this->parent_selected = self::_get_parent_group($this->parent_id, 0);
                if ($this->parent_selected == 0) {
                    $this->parent_selected = $this->nav_id;
                }
                // parent active
                if ($this->parent_selected == $tab['nav_id']) {
                    $parent_active = 'active';
                }
                // html
                $html .= '<div class="tab-pane ' . $parent_active . '" id="' . str_replace('#', '', $tab['nav_url']) . '" role="tabpanel">';
                $html .= '<div class="flex hide-scroll">
                            <div class="scroll">
                                <div class="nav-border b-primary" data-nav>
                                    <ul class="nav bg">
                                    <li class="nav-header">
                                        <span class="text-xs hidden-folded">' . $tab['nav_desc'] . '</span>
                                    </li>';
                // sidebar menu
                $params = array($this->portal_id, $this->com_user['user_id'], $tab['nav_id']);
                $rs_id = $this->M_site->get_navigation_user_by_parent($params);
                if (!empty($rs_id)) {
                    foreach ($rs_id as $rec) {
                        // check selected
                        $parent_selected = self::_get_parent_group($this->parent_id, $tab['nav_id']);
                        if ($parent_selected == 0) {
                            $parent_selected = $this->nav_id;
                        }
                        // parent active
                        $parent_class_caret = '';
                        // get child navigation
                        $child = $this->_get_child_navigation($rec['nav_id']);
                        if (!empty($child)) {
                            $url_parent = '#';
                            $parent_class_caret = '<span class="nav-caret"><i class="fa fa-caret-down"></i></span>';
                        } else {
                            $url_parent = site_url($rec['nav_url']);
                            $parent_class_caret = '';
                        }
                        // selected
                        $selected = ($rec['nav_id'] == $parent_selected) ? 'class="active"' : "";
                        // parse
                        $html .= '<li ' . $selected . '>';
                        $html .= '<a href="' . $url_parent . '" title="' . $rec['nav_desc'] . '">';
                        $html .= $parent_class_caret;
                        $html .= '<span class="nav-icon"><i class="' . $rec['nav_icon'] . '"></i></span>';
                        $html .= '<span class="nav-text">' . $rec['nav_title'] . '</span>';
                        $html .= '</a>';
                        $html .= $child;
                        $html .= '</li>';
                    }
                }
                // --
                $html .= '</ul>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
        }
        // output
        $this->tsmarty->assign("list_sidebar_nav", $html);
    }

    // get child
    protected function _get_child_navigation($parent_id) {
        $html = '';
        // get parent selected
        $parent_selected = self::_get_parent_group($this->parent_id, $parent_id);
        if ($parent_selected == 0) {
            $parent_selected = $this->nav_id;
        }
        // --
        $params = array($this->portal_id, $this->com_user['user_id'], $parent_id);
        $rs_id = $this->M_site->get_navigation_user_by_parent($params);
        if (!empty($rs_id)) {
            $html = "<ul class='nav-sub'>";
            foreach ($rs_id as $rec) {
                // parent active
                $parent_class_caret = '';
                // get child navigation
                $child = $this->_get_child_navigation($rec['nav_id']);
                if (!empty($child)) {
                    $url_parent = '#';
                    $parent_class_caret = '<span class="nav-caret"><i class="fa fa-caret-down"></i></span>';
                } else {
                    $url_parent = site_url($rec['nav_url']);
                    $parent_class_caret = '';
                }
                // selected
                $selected = ($rec['nav_id'] == $parent_selected) ? 'class="active"' : "";
                // parse
                $html .= '<li ' . $selected . '>';
                $html .= '<a href="' . $url_parent . '" title="' . $rec['nav_desc'] . '">';
                $html .= $parent_class_caret;
                $html .= '<span class="nav-text">' . $rec['nav_title'] . '</span>';
                $html .= '</a>';
                $html .= $child;
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        // return
        return $html;
    }

    // utility to get parent selected
    protected function _get_parent_group($int_nav, $int_limit) {
        $selected_parent = 0;
        $result = $this->M_site->get_menu_by_id($int_nav);
        if (!empty($result)) {
            if ($result['parent_id'] == $int_limit) {
                $selected_parent = $result['nav_id'];
            } else {
                return self::_get_parent_group($result['parent_id'], $int_limit);
            }
        } else {
            // $selected_parent = $result['nav_id'];
            $selected_parent = empty($result['nav_id']) ? '' : $result['nav_id'];
        }
        return $selected_parent;
    }

}
