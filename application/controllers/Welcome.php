<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed

class Welcome extends CI_Controller
{
    public function index()
    {
        redirect('bantul/sawokecik');
    }
}
