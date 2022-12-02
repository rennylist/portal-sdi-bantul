<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once( BASEPATH . 'plugins/smarty-3.1.39/libs/Smarty.class.php' );

class tsmarty extends Smarty {

    private $javascript = "";
    private $style = "";

    // constructor
    function __construct() {
        // smarty constructor
        parent::__construct();
        // default cache
        $this->compile_dir = "cache";
        // default view
        $this->template_dir = APPPATH . "views";
        // define variable
        $this->assign('BASEURL', base_url());
        $this->assign('LOAD_STYLE', "");
        $this->assign('LOAD_JAVASCRIPT', "");
    }

    // load main themes
    public function load_themes($name = "default", $css = "load-style.css") {
        // load CI
        $CI = & get_instance();
        // get css file
        $css_file = $CI->config->item('themes_path') . '/themes/' . $name . '/' . $css;
        // assign
        if (is_file($css_file)) {
            $this->assign('THEMESPATH', base_url($css_file));
        } else {
            $msg = "File berikut ini tidak ditemukan : " . base_url($css_file);
            show_error($msg, 404);
        }
    }

    // load javascript
    public function load_javascript($path) {
        if (is_file($path)) {
            $this->javascript .= '<script type="text/javascript" src="' . base_url($path) . '"></script>';
            $this->javascript .= "\n";
            // assign
            $this->assign('LOAD_JAVASCRIPT', $this->javascript);
        } else {
            $msg = "File berikut ini tidak ditemukan : " . base_url($path);
            show_error($msg, 404);
        }
    }

    // load style
    public function load_style($path, $media = "all") {
        // load CI
        $CI = & get_instance();
        // get css file
        $css_file = $CI->config->item('themes_path') . '/themes/' . $path;
        // assign
        if (is_file($css_file)) {
            $this->style .= '<link rel="stylesheet" type="text/css" href="' . base_url($css_file) . '" media="' . $media . '" />';
            $this->style .= "\n";
            $this->assign('LOAD_STYLE', $this->style);
        } else {
            $msg = "File berikut ini tidak ditemukan : " . base_url($css_file);
            show_error($msg, 404);
        }
    }

    public function load_style_manual($path, $media = "all") {
        // load CI
        $CI = & get_instance();
        // get css file
        $css_file = $CI->config->item('themes_path') . '/themes/' . $path;
        // assign
        if (is_file($path)) {
            $this->style .= '<link rel="stylesheet" type="text/css" href="' . base_url($path) . '" media="' . $media . '" />';
            $this->style .= "\n";
            $this->assign('LOAD_STYLE', $this->style);
        } else {
            $msg = "File berikut ini tidak ditemukan : " . base_url($css_file);
            show_error($msg, 404);
        }
    }
}

// END Smarty Class
