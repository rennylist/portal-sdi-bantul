<?php

class OperatorBase extends CI_Controller
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
        $this->tsmarty->load_themes("ubold.5.0");
        // load base models
        $this->load->model('apps/M_email');
        $this->load->model('apps/M_account');
        $this->load->model('apps/M_email');
        // load javascript
      

        // <!-- Vendor js -->
        // <script src="../assets/js/vendor.min.js"></script>
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/js/vendor.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net/js/jquery.dataTables.min.js");
        // $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net/js/jquery.dataTables.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-responsive/js/dataTables.responsive.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-buttons/js/dataTables.buttons.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-buttons/js/buttons.html5.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-buttons/js/buttons.flash.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-buttons/js/buttons.print.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-keytable/js/dataTables.keyTable.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net-select/js/dataTables.select.min.js");
        // $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/datatables.net/js/jquery.dataTables.min.js");

        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/selectize/js/standalone/selectize.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/mohithg-switchery/switchery.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/multiselect/js/jquery.multi-select.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/select2/js/select2.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/jquery-mockjax/jquery.mockjax.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/devbridge-autocomplete/jquery.autocomplete.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/bootstrap-maxlength/bootstrap-maxlength.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/admin-resources/rwd-table/rwd-table.min.js");
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/js/pages/responsive-table.init.js");
      


        // <!-- Init js-->
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/js/pages/form-advanced.init.js");

        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/js/pages/datatables.init.js");
        // <script src="../assets/"></script>

        // $this->tsmarty->load_javascript("resource/themes/ubold.5.0/libs/tippy.js/tippy.all.min.js");
        // <!-- App js -->
        $this->tsmarty->load_javascript("resource/themes/ubold.5.0/js/app.min.js");

        // load style
        // $this->tsmarty->load_style_manual('resource/js/jquery-confirm/jquery-confirm.min.css');

        // $email['to'] = "djatoyz@gmail.com";
        // $email['subject'] = "Jaring Aspirasi DPRD Kab. Bantul";
        // $email['message']['title'] ="";
        // $email['message']['greetings'] ="";
        // $email['message']['intro'] ="";
        // $email['message']['details'] ="";
        // $this->M_email->set_mail($email);
        // $this->M_email->send_mail('01');
        // exit();
        

        $this->load->library("tdtm");
        $this->tsmarty->assign("dtm", $this->tdtm);
        //load template
        $this->tsmarty->assign("view_eng", "yes");
        $this->tsmarty->assign("view_jav","no");
        $this->tsmarty->assign("view_icon","yes");
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
        $session = $this->session->userdata('session_operator');
        if (!empty($session)) {
            // get com user
            $this->com_user = $this->M_account->get_pegawai_login_by_id_new(array($session['user_id']));
            // print_r( $session );
            // exit();
            // get user img
            // default
            $this->com_user['user_img'] = base_url() . 'resource/doc/images/users/default.png';
            $image_path = "";
            if (!empty($this->com_user['user_img_path'])) {
                $image_path = trim($this->com_user['user_img_path'], '/') . '/' . trim($this->com_user['user_img_name'], '/');
            }
            // check images path
            if (is_file($image_path)) {
                $this->com_user['user_img'] = base_url() . $image_path;
            }
            // assign user
            $this->tsmarty->assign("com_user", $this->com_user);

           
        }
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

    protected function display($tmpl_name = 'base/ubold/document.html')
    {
        // --
        $this->tsmarty->assign("template_sidebar", "base/ubold/sidebar.html");
        // set template
        $this->tsmarty->display($tmpl_name);
    }

    //
    // base private method here
    // prefix ( _ )
    // base link
    private function _display_base_link()
    { }


    // site title
    private function _display_site_title()
    {
        $this->portal_id = $this->config->item('portal_id');
        $this->portal_id = 20;
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
        if (!empty($this->com_user)) {
            // user authority
            $user_id = empty($this->com_user['user_id']) ? '' : $this->com_user['user_id'];

            $params = array($user_id, $this->nav_id, $this->portal_id);
            $role_tp = $this->M_site->get_user_authority_by_nav($params);
            // print_r($params); 
            // print_r($role_tp);
            // echo "sss";
            // exit();

            // get rule tp
            $i = 0;
            foreach ($this->role_tp as $rule => $val) {
                $N = substr($role_tp, $i, 1);
                $this->role_tp[$rule] = $N;
                $i++;
            }
        } else {
            // tidak memiliki authority
            redirect('bantul/sawokecik/logout_process');
        }
    }

    // set rule per pages
    protected function _set_page_rule($rule)
    {

        if (!isset($this->role_tp[$rule]) or $this->role_tp[$rule] != "1") {
            // redirect to forbiden access
            redirect('admin/setting/forbidden/page/' . $this->nav_id);
        }
    }

    // top navigation
    private function _display_top_navigation()
    {
        // get parent selected
        $this->parent_selected = self::_get_parent_group($this->parent_id, 0);
        if ($this->parent_selected == 0) {
            $this->parent_selected = $this->nav_id;
        }
        // get data
        // $params = array($this->portal_id, 2, $this->com_user['user_id'], 0);
        $params = array($this->portal_id, $this->com_user['user_id'], 0);
        $rs_id = $this->M_site->get_navigation_user_by_parent($params);

        $this->tsmarty->assign("list_top_nav", $rs_id);
        $this->tsmarty->assign("top_menu_selected", $this->parent_selected);
    }

    // sidebar navigation
    private function _display_sidebar_navigation()
    {
        $html = "";       
      
        // get data
        // load model
        // $params = array($this->portal_id, $this->com_user['role_id'], $this->com_user['user_id'], 0);
        $params = array($this->portal_id, $this->com_user['user_id'], 0);
        $rs_id = $this->M_site->get_navigation_user_by_parent($params);

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
                    $parent_active = 'class="menuitem-active"';
                }

                $selected = ($rec['nav_id'] == $parent_selected) ? '-active' : "";
                // echo $rec['nav_id'].$parent_selected;
                // exit();
                $url_parent = "#";
                $icon = "fa fa-table";
                if (!empty($rec['nav_icon'])) {
                    $icon = "fa " . $rec['nav_icon'];
                }
                // $icon = "fa fa-table";
                // check child
                $tag = str_replace(" ", "",$rec['nav_title']);
                $child = $this->_get_child_navigation($rec['nav_id'], $tag);
                if (empty($child)) {
                    if($rec['nav_desc'] == "#"){
                        $html .= '<li>';
                        $html .= '  '.($rec['nav_title']).'';
                        $html .= '</li>';
                    }else if($rec['nav_desc'] == "##"){
                        $html .= '<li class="menu-title">';
                        $html .= '  '.($rec['nav_title']).'';
                        $html .= '</li>';
                    }else{
                        $url_parent = site_url($rec['nav_url']);
                        $html .= '<li class="menuitem'. $selected.'">';
                        $html .= '  <a href="'.$url_parent.'">';
                        $html .= '      <i class="' . $icon . '"></i>';
                        $html .= '      <span>'.($rec['nav_title']).'</span>';
                        $html .= '  </a>';
                        $html .= "</li>";
                    }
                } else {
                    $url_parent = site_url($rec['nav_url']);
                    $html .= '<li '.$parent_active.'>';
                    $html .= '  <a href="#'.$tag.'" data-bs-toggle="collapse">';
                    $html .= '      <i class="' . $icon . '"></i>';
                    $html .= '      <span>' .($rec['nav_title']).'</span>';
                    $html .= '      <span class="menu-arrow"></span>';
                    $html .= '  </a>';
                   // get child navigation
                    $html .= $child;
                    $html .= "</li>";
                }
            }
        }
        $this->tsmarty->assign("list_sidebar_nav", $html);
    }

    // get child
    private function _get_child_navigation($parent_id, $tag)
    {
        $html = "";
        // get parent selected
        $parent_selected = self::_get_parent_group($this->parent_id, $parent_id);
        if ($parent_selected == 0) {
            $parent_selected = $this->nav_id;
        }
        // if parent selected then show child
        $top = self::_get_parent_group($this->parent_id, 0);

     
        $params = array($this->portal_id, $this->com_user['user_id'], $parent_id);
        $rs_id = $this->M_site->get_navigation_user_by_parent($params);
        // print_r($params);
        // exit();

        if ($rs_id) {
            $html .= '<div class="collapse" id="'.$tag.'">';
            $html .= '  <ul class="nav-second-level">';
            foreach ($rs_id as $rec) {
                $grandchild = $this->_get_grandchild_navigation($rec['nav_id']);
                // selected
                $selected = ($rec['nav_id'] == $parent_selected) ? 'class="menuitem-active"' : "";

                // parse
                $html .= '<li '.$selected.' >';
                $tag = str_replace(" ", "",$rec['nav_title']);
                
                if (empty($grandchild)) {
                    $html .= '<a href="' . site_url($rec['nav_url']) . '">';
                    $html .= '<span>'.$rec['nav_title'].'</span>';
                    $html .= '</a>';
                }else{
                    $html .= '<a href="javascript:;">';
                    $html .= '<span>'.$rec['nav_title'].'</span>';
                    $html .= '<i class="kt-menu__ver-arrow la la-angle-right"></i>';
                    $html .= '</a>';
                }
                $html .= $grandchild;
                $html .= '</li>';
            }
            $html .= '  </ul>';
            $html .= '</div>';
        }
        return $html;
    }

      // get child
      private function _get_grandchild_navigation($parent_id)
      {
          $html = "";
          // get parent selected
          $parent_selected = self::_get_parent_group($this->parent_id, $parent_id);
          if ($parent_selected == 0) {
              $parent_selected = $this->nav_id;
          }

          $params = array($this->portal_id, $this->com_user['user_id'], $parent_id);
          $rs_id = $this->M_site->get_navigation_user_by_parent($params);

          if ($rs_id) {

              $html .= '  <ul>';
              foreach ($rs_id as $rec) {
                  // selected
                  $selected = ($rec['nav_id'] == $parent_selected) ? "-active" : "";
                  // parse
                  $html .= '<li class="nav-second-level'.$selected.'">';
                  $html .= '<a href="' . site_url($rec['nav_url']) . '" ">';
                  $html .= '<span >'.$rec['nav_title'].'</span>';
                  $html .= '</a>';
                  $html .= '</li>';
              }
              $html .= '  </ul>';
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
            // $selected_parent = $result['nav_id'];
            $selected_parent = empty($result['nav_id']) ? '' : $result['nav_id'];
        }
        return $selected_parent;
    }

    // clean tag html
    protected function stripHTMLtags($str) {
        $t = preg_replace('/<[^<|>]+?>/', '', htmlspecialchars_decode($str));
        $t = htmlentities($t, ENT_QUOTES, "UTF-8");
        return $t;
    }

    public function _get_id() {
        $micro = explode(' ', microtime());
        $micro[0] = preg_replace('/(\ |,|\.)/', '', $micro[0]);
        return $micro[1].$micro[0];
    }
}
