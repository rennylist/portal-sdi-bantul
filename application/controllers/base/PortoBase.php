<?php

class ApplicationBase extends CI_Controller
{

    // init base variable
    protected $portal_id;
    protected $com_portal;
    protected $com_user;
    protected $com_user_uuid = null;
    protected $com_user_nik;
    protected $nav_login_st;
    protected $nav_nik_st;
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
        $this->com_display = $this->session->userdata('session_bantulpedia_display');
        //cek 
        if(!isset($this->com_display)){
            //set display light
            $this->com_display = "light";
            $this->session->set_userdata('session_bantulpedia_display',  $this->com_display);
        }

        //set display
        $this->tsmarty->load_themes("porto.1.0");

        //set smarty
        $this->tsmarty->assign("com_display", $this->com_display);

        // load base models

        // load javascript
        // <!-- Vendor -->
        // $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/jquery/jquery.min.js");
        // $this->tsmarty->load_javascript("resource/js/jquery/jquery.js");
        $this->tsmarty->load_javascript("resource/js/jquery/jquery-3.6.0.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/jquery.appear/jquery.appear.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/jquery.easing/jquery.easing.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/jquery.cookie/jquery.cookie.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/bootstrap/js/bootstrap.bundle.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/jquery.validation/jquery.validate.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/jquery.easy-pie-chart/jquery.easypiechart.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/jquery.gmap/jquery.gmap.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/lazysizes/lazysizes.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/isotope/jquery.isotope.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/owl.carousel/owl.carousel.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/magnific-popup/jquery.magnific-popup.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/vide/jquery.vide.min.js");
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/vendor/vivus/vivus.min.js");

        // <!-- Theme Base, Components and Settings -->
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/js/theme.js");

        // <!-- Theme Custom -->
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/js/custom.js");

        // <!-- Theme Initialization Files -->
        $this->tsmarty->load_javascript("resource/themes/porto.1.0/js/theme.init.js");

        // component
        // $this->tsmarty->load_javascript("resource/js/jquery/jquery.js");
        $this->tsmarty->load_javascript("resource/js/datatables/js/bs-datatable.js");
        $this->tsmarty->load_javascript("resource/js/datetimepicker/2.5.20/jquery.datetimepicker.full.min.js");
        $this->tsmarty->load_javascript("resource/js/jqueryui/jquery-ui.js");


        // load style
        $this->tsmarty->load_style('porto.1.0/fonts/css.css');

        //load library
        $this->load->library("tdtm");
        $this->load->library("Unirest");
        // load model
        // $this->load->model('public/M_aplikasi');
        $this->load->model('public/M_preferences');     
        $this->load->model('public/M_apis'); 
        $this->load->model('apps/M_email');
        $this->load->model('admin/master/M_setting');

        // $email['to'] = "djatoyz@gmail.com";
        // $email['subject'] = "Masukan terhadap Raperda - Jarimas DPRD Kabupaten Bantul";
        // $email['message']['title'] ="Masukan terhadap Raperda";
        // $email['message']['greetings'] ="Terima Kasih atas pastisipasi anda,";
        // $email['message']['intro'] ="";
        // $email['message']['details'] ="Masukan anda terhadap Raperda <b>xxx</b> telah kami terima. Kami akan segera memproses masukan anda terhadap Raperda tersebut.";
        // $this->M_email->set_mail($email);
        // $this->M_email->send_mail();

        //get data
        $setting =  $this->M_setting->get_settting();
        // print_r( $setting);
        //assign
        $this->tsmarty->assign("setting" , $setting );
        $this->tsmarty->assign("tdtm", $this->tdtm);

        //
        $url_change_pass = $this->M_apis->get_apis_val(array("sso", "change_pass"));
        $this->tsmarty->assign("url_change_pass", $url_change_pass);
    }

   

    protected function base_load_display()
    {
        $this->com_display = $this->session->userdata('session_bantulpedia_display');
        //cek 
        if(!isset($this->com_display)){
            $this->com_display = "light";
            $this->session->set_userdata('session_bantulpedia_display',  $this->com_display);
        }
        // echo  $this->session->userdata('session_bantulkab_lang');;
        $this->tsmarty->assign("com_display", $this->com_display);
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
        // print_r($session);
        // exit();
        if (!empty($session)) {
            // //nama depan
            $this->tsmarty->assign("com_user", $session);
            $this->com_user = $session;
            $this->com_user_nik = $session->nik;
            $this->com_user_uuid= $session->uuid;
            // print_r($this->com_user->mobile);
        }else{
            $this->com_user_uuid = null;
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
        $this->base_load_visitor();
    }

    protected function base_load_visitor()
    {
        // load Model
        $this->load->model('public/M_statistic');
        //cek jika bukan admin

        //get session
        $this->com_visitor = $this->session->userdata('session_bantulpedia_visitor');
        $url_menu = $this->router->fetch_directory().$this->router->fetch_class();
        
        //cek apabila masih belum ada data
        if(!isset($this->com_visitor)){
            //get id
            $com_visitor = $this->_get_id();
            //insert data
            $user = $this->_get_user_agent();
            $params = array(
                "statistic_id" =>  $com_visitor,
                "user_ip_address" => $user['ip_address'],
                "user_browser" => $user['name'],
                "user_browser_ver" => $user['version'],
                "user_device" => $user['device'],
                "user_platform" =>  $user['platform'],
                "user_platform_detail" => $user['platform_detail'],
                "user_uuid" =>  $this->com_user_uuid,
                "browse_start" =>  date("Y-m-d H:i:s"),
                "browse_end" =>  date("Y-m-d H:i:s"),
                "mdb" => "1010",
                "mdb_name" =>  "visitor",
                "mdd" => date("Y-m-d H:i:s")
            );
            //insert data
            $this->M_statistic->insert($params);

            //insert data detail
            $params = array(
                "statistic_id" =>  $com_visitor,
                "user_uuid" =>  $this->com_user_uuid,
                "browse_start" =>  date("Y-m-d H:i:s"),
                "browse_end" =>  date("Y-m-d H:i:s"),
                "url" =>   $url_menu,
                "mdb" => "1010",
                "mdb_name" =>  "visitor",
                "mdd" => date("Y-m-d H:i:s")
            );
            $this->M_statistic->insert_detail($params);
            
            //set session visitor
            $this->session->set_userdata('session_bantulpedia_visitor',  $com_visitor);
        }else{
            //update data
            if($this->com_user_uuid == null){
                $params= array(date("Y-m-d H:i:s"), $this->com_visitor);
                $this->M_statistic->update_nonuuid($params);
            }else{
                $params= array(date("Y-m-d H:i:s"), $this->com_user_uuid, $this->com_visitor);
                $this->M_statistic->update($params);
            }

            // statistik detail
            if ($this->M_statistic->check_exist_stat_detail(array($this->com_visitor, $url_menu))){
                $params = array(
                    "statistic_id" =>  $this->com_visitor,
                    "user_uuid" =>  $this->com_user_uuid,
                    "browse_start" =>  date("Y-m-d H:i:s"),
                    "browse_end" =>  date("Y-m-d H:i:s"),
                    "url" =>   $url_menu,
                    "mdb" => "1010",
                    "mdb_name" =>  "visitor",
                    "mdd" => date("Y-m-d H:i:s")
                );
                $this->M_statistic->insert_detail($params);
            }else{
                // update hits
                if($this->com_user_uuid == null){
                    $params= array(date("Y-m-d H:i:s"), $this->com_visitor, $url_menu);
                    $this->M_statistic->update_detail_nonuuid($params);
                }else{
                    $params= array(date("Y-m-d H:i:s"), $this->com_user_uuid, $this->com_visitor, $url_menu);
                    $this->M_statistic->update_detail($params);
                }
            }
        }
        //stat
        $this->tsmarty->assign("stats", $this->M_statistic->get_stat());
    }

    protected function _get_id() {
        $micro = explode(' ', microtime());
        $micro[0] = preg_replace('/(\ |,|\.)/', '', $micro[0]);
        return $micro[1].$micro[0];
    }

    
    protected function _get_user_agent()
    {
        $u_agent 	= $_SERVER['HTTP_USER_AGENT']; 
        $bname   	= 'Unknown';
        $platform 	= 'Unknown';
        $platform_detail 	= 'Unknown';
        $version 	= "";

        $os_array   =   array(
            '/windows nt 10.0/i'     =>  array('platform'=>'windows', 'platform_detail' => 'Windows 10'),
            '/windows nt 6.2/i'     =>   array('platform'=>'windows', 'platform_detail' => 'Windows 8'),
            '/windows nt 6.1/i'     =>   array('platform'=>'windows', 'platform_detail' => 'Windows 7'),
            '/windows nt 6.0/i'     =>   array('platform'=>'windows', 'platform_detail' => 'Windows Vista'),
            '/windows nt 5.2/i'     =>   array('platform'=>'windows', 'platform_detail' => 'Windows Server 2003/XP x64'),
            '/windows nt 5.1/i'     =>   array('platform'=>'windows', 'platform_detail' => 'Windows XP'),
            '/windows xp/i'         =>   array('platform'=>'windows', 'platform_detail' => 'Windows XP'),
            '/windows nt 5.0/i'     =>   array('platform'=>'windows', 'platform_detail' => 'Windows 2000'),
            '/windows me/i'         =>   array('platform'=>'windows', 'platform_detail' => 'Windows ME'),
            '/win98/i'              =>   array('platform'=>'windows', 'platform_detail' => 'Windows 98'),
            '/win95/i'              =>   array('platform'=>'windows', 'platform_detail' => 'Windows 95'),
            '/win16/i'              =>   array('platform'=>'windows', 'platform_detail' => 'Windows 3.11'),
            '/macintosh|mac os x/i' =>   array('platform'=>'macos', 'platform_detail' => 'Mac OS X'),
            '/mac_powerpc/i'        =>   array('platform'=>'macos', 'platform_detail' => 'Mac OS 9'),
            '/linux/i'              =>   array('platform'=>'linux', 'platform_detail' => 'Linux'),
            '/ubuntu/i'             =>   array('platform'=>'linux', 'platform_detail' => 'Ubuntu'),
            '/iphone/i'             =>   array('platform'=>'ios', 'platform_detail' => 'iPhone'),
            '/ipod/i'               =>   array('platform'=>'ios', 'platform_detail' => 'iPod'),
            '/ipad/i'               =>   array('platform'=>'ios', 'platform_detail' => 'iPad'),
            '/android/i'            =>   array('platform'=>'android', 'platform_detail' => 'Android'),
            '/blackberry/i'         =>   array('platform'=>'blackberry', 'platform_detail' => 'BlackBerry'),
            '/webos/i'              =>   array('platform'=>'webos', 'platform_detail' => 'Web OS')
        );
        
        foreach ($os_array as $regex => $value) { 
            if (preg_match($regex, $u_agent)) {
                $platform    =   $value['platform'];
                $platform_detail =   $value['platform_detail'];
                break;
            }

        }

        //get browser
        $arr_browsers = ["Opera", "Edge", "Chrome", "Safari", "Firefox", "MSIE", "Trident"];
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $user_browser = '';
        foreach ($arr_browsers as $browser) {
            if (strpos($agent, $browser) !== false) {
                $user_browser = $browser;
                break;
            }   
        }
        switch ($user_browser) {
            case 'MSIE':
                $user_browser = 'Internet Explorer';
                break;
          
            case 'Trident':
                $user_browser = 'Internet Explorer';
                break;
          
            case 'Edge':
                $user_browser = 'Microsoft Edge';
                break;
        }
          
        // echo "You are using ".$user_browser." browser";

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) { 
            $bname = 'Internet Explorer'; 
            $ub = "MSIE"; 
        
        } elseif(preg_match('/Firefox/i',$u_agent)) { 
            $bname = 'Mozilla Firefox'; 
            $ub = "Firefox"; 
        
        } elseif(preg_match('/Chrome/i',$u_agent)) { 
            $bname = 'Google Chrome'; 
            $ub = "Chrome"; 

        } elseif (preg_match('/Safari/i',$u_agent)) { 
            $bname = 'Apple Safari'; 
            $ub = "Safari"; 

        } elseif (preg_match('/Opera/i',$u_agent)) { 
            $bname = 'Opera'; 
            $ub = "Opera"; 
        
        } elseif (preg_match('/Netscape/i',$u_agent)) { 
            $bname = 'Netscape'; 
            $ub = "Netscape"; 
        }

        //  finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    
        if (! preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }
        
        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            
            } else {
                $version= $matches['version'][1];
            }
        } else {
            $version= $matches['version'][0];
        }
        
        // check if we have a number
        $version = ( $version == null || $version == "" ) ? "?" : $version;
        
        // get mobile /dekstop
        $deviceType = 0;
        if (is_numeric(strpos(strtolower($u_agent), "mobile"))) {
            $deviceType =  is_numeric(strpos(strtolower($u_agent), "tablet")) ? 2 : 1 ;
        } else {
            $deviceType = 0;
        }

        if ($deviceType==0) {
            $device = "dekstop";
        } else if ($deviceType==1) {
            $device =  "mobile";
        } else {
            $device =  "tablet";
        }
      
        //get ip addrs
        if (! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        
        } elseif (! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        
        }
    

        return array(
            'ip_address' => $ip,
            'userAgent' => $u_agent,
            'name'      => $user_browser,
            'version'   => $version,
            'platform'  => $platform,
            'platform_detail' => $platform_detail,
            'device'  => $device,
            'pattern'   => $pattern
        );
        
    }

    /*
     * Method layouting base document
     * diperbolehkan untuk dioverride pada class anaknya
     */

    protected function display($tmpl_name = 'base/porto/document.html')
    {
        // --
        $this->tsmarty->assign("template_topbar", "base/porto/topbar.html");
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
        // get url
        $url_menu = $this->router->fetch_directory().$this->router->fetch_class();
        // print_r($url_menu);
        $result = $this->M_site->get_current_page_double(array($url_menu, "30", "40"));
        // print_r($result);
        // exit();
        if (!empty($result)) {
            // print_r($result);
            $this->tsmarty->assign("page", $result);
            $this->tsmarty->assign("pagestyle", $result);
            $this->nav_id = $result['nav_id'];
            $this->nav_nik_st = $result['nav_nik_st'];
            $this->nav_login_st = $result['nav_login_st'];
            $this->parent_id = $result['parent_id'];
        }
    }

    // authority
    protected function _check_authority()
    {
        // default rule tp
        $this->role_tp = array("C" => "0", "R" => "0", "U" => "0", "D" => "0");

        // user authority
        $params = array($this->nav_id, "30", "40");
        $role_tp = $this->M_site->get_bss_authority_by_nav($params);
        
        //cek jenis navigasi
        if($this->nav_login_st == "yes"){
            $access_token = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : '';
            if ($access_token == ''){
                // tidak memiliki authority
                redirect('signin');
            }else{
                //cek harus pakai NIK atau tidak
                if($this->nav_nik_st == "yes"){
                    if(empty($this->com_user_nik)){
                        redirect('error/authorized');
                    }
                }

                // get rule tp
                $i = 0;
                foreach ($this->role_tp as $rule => $val) {
                    $N = substr($role_tp, $i, 1);
                    $this->role_tp[$rule] = $N;
                    $i++;
                }
            }
        }
        else{
            // echo "tidak perlu login";
            // exit();
        }
    }

    // set rule per pages
    protected function _set_page_rule($rule)
    {
        // print_r($this->nav_login_st);
        // exit();
        if($this->nav_login_st == "yes"){   
            if (!isset($this->role_tp[$rule]) or $this->role_tp[$rule] != "1") {
                // redirect to forbiden access
                redirect('error/notfound');
            }
        }
    }

    // sidebar navigation
    private function _display_top_navigation()
    {
        $html = "";

        // get data
        // $params = array($this->portal_id, 0);
        $params = array($this->portal_id, 0);
        $rs_id = $this->M_site->get_navigation_public_by_parent($params);
        // print_r($this->nav_id);
        if ($rs_id) 
        {
            foreach ($rs_id as $rec) {
                // get parent selected
                $parent_active = '';
                $parent_selected = self::_get_parent_group($this->parent_id, $this->parent_selected);
                if ($parent_selected == 0) {
                    $parent_selected = $this->nav_id;
                }
                // parent active
                if ($parent_selected == $rec['nav_id']) {
                    $parent_active = 'active';
                    // print_r("ssss");
                }

                // print_r($this->nav_id);
                // exit();
                // echo $this->parent_selected;
                // echo "<br />";
                // echo $rec['nav_id'];
                // echo "<br />";
                // echo "<br />";
                // echo $parent_active;
                // echo $rec['nav_id'];
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
                        $html .= ' <li class="dropdown">';
                        $html .= '  <a class="dropdown-item dropdown-toggle" href="#">'.($rec['nav_title']).'</a>';
                        $html .= '</li>';
                    }else{
                        $url_parent = site_url($rec['nav_url']);
                        $html .= ' <li class="dropdown">';
                        $html .= '  <a class="dropdown-item dropdown-toggle '.$parent_active.'" href="'.$url_parent.'" >'.($rec['nav_title']).'</a>';
                        $html .= '</li>';
                    }
                } else {
                    // $selected = ($rec['nav_id'] == $parent_selected) ? 'kt-menu__item--here kt-menu__item--open' : "";
                    $url_parent = site_url($rec['nav_url']);
                    $html .= '<li class="dropdown">';
                    $html .= '  <a class="dropdown-item dropdown-toggle '.$parent_active.'" href="#">'.($rec['nav_title']).'</a>';
                    $html .= '  <ul class="dropdown-menu">';
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
                //get grandchild
                $grandchild = $this->_get_grandchild_navigation($rec['nav_id']);
                //check jika kosong
                $selected = ($rec['nav_id'] == $parent_selected) ? "active" : "";
                if (empty($grandchild)) {
                    // parse
                    $html .= '<li>';
                    $html .= '  <a href="' . site_url($rec['nav_url']) . '" class="dropdown-item '.$selected.'">';
                    $html .=        $rec['nav_title'];
                    $html .= '  </a>';
                    $html .= '</li>';
                }
                else{
                    $html .= '<li  class="dropdown-submenu">';
                    $html .= '  <a href="#" class="dropdown-item '.$selected.'">';
                    $html .=        $rec['nav_title'];
                    $html .= '  </a>';
                    $html .=    $grandchild;
                    $html .= '</li>';
                }
             }
         }
         return $html;
     }

     private function _get_grandchild_navigation($parent_id)
     {
        $html = "";
        // if parent selected then show child
        $params = array($this->portal_id,  $parent_id);
        // print_r($params);
        $rs_id = $this->M_site->get_navigation_public_by_parent($params);
        // print_r($rs_id);

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
                $html .= '<ul class="dropdown-menu">';
                $html .= '  <li>';
                $html .= '      <a href="' . site_url($rec['nav_url']) . '" class="dropdown-item'.$selected.'">';
                $html .=            $rec['nav_title'];
                $html .= '      </a>';
                $html .= '  </li>';
                $html .= '</ul>';
            }
        }
        //  print_r($html);

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
