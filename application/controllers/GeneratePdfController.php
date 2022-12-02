<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GeneratePdfController extends CI_Controller
{

    function index()
    {
        $this->load->library('pdf');
        $data['nama'] = "Taufik Hidayat";
        $data['kelas'] = "3MM1";

        //print_r($data);
        $html = $this->load->view('GeneratePdfView', $data, true);
        $this->pdf->createPDF($html, 'mypdf', false);

        //$this->load->view('GeneratePdfView', $data);
    }
}
