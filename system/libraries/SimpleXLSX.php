<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once( BASEPATH.'plugins/simplexlsx/SimpleXLSX.php' );

class CI_SimpleXLSX extends SimpleXLSX {

    function CI__construct() {
        // tcpdf constructor
        parent::__construct();
    }
}