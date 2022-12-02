<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once( BASEPATH.'plugins/simplexlsx/SimpleXLSXGen.php' );

class CI_SimpleXLSXGen extends SimpleXLSXGen {

    function CI__construct() {
        // tcpdf constructor
        parent::__construct();
    }
}