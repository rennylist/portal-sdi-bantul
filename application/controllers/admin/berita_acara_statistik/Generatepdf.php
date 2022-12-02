<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// LOAD BASE CLASS IF NEEDED
require_once(APPPATH . 'controllers/base/UboldBase.php');

class Generatepdf extends OperatorBase
{
    // CONSTRUCTOR
    public function __construct()
    {
        parent::__construct();
        // LOAD MODEL
        $this->load->model('admin/master/M_instansi');
        $this->load->model('admin/berita_acara/M_create_ba');
        $this->load->model('admin/berita_acara/M_create_ba_pg');
        $this->load->model('admin/statistik/M_urusan');
        $this->load->model('admin/statistik/M_urusan_pg');
        // LOAD LIBRARY
        $this->load->library('tnotification');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
        $this->load->library('pdf');
        $this->load->library('SimpleXLSXGen');
    }

    public function index($ba_id)
    {

        // SET PAGE RULES
        $this->_set_page_rule("R");

        // GET BA ID
        //$ba_id = $_GET['ba_id'];

        // GET INSTANSI CD
        $instansi_cd = $this->com_user['instansi_cd'];

        $param = array($instansi_cd,  $ba_id);
        $query = $this->M_create_ba_pg->get_berita_acara_by_ba_id($param);

        foreach ($query as $data_ba) {
            $data_ba;
        }
        $instansi_1 = $this->M_instansi->get_detail_instansi($instansi_cd);
        $instansi_2 = array(
            "nama" => "Siapa",
            "nip" => "1234",
            "pangkat" => "IV B",
            "jabatan" => "Kepala",
            "alamat2" =>  "Bantul"
        );
        // $data = array_merge($data_ba, $instansi_1, $instansi_2);
        // print_r("<pre>");
        // print_r($query);
        // print_r("</pre>");

        // print_r("<pre>");
        // print_r($data);
        // print_r("</pre>");

        // GET URUSAN
        $get_urusan = $this->M_urusan->get_urusan_by_instansi($instansi_cd);
        $urusan_id = array();
        foreach ($get_urusan as $result) {
            array_push(
                $urusan_id,
                $result['urusan_id']
            );
        }
        $arr = $urusan_id;
        $data_id = implode(', ', $arr);

        // GET INDICATOR
        $tahun = $data_ba['ba_year'];
        $param = array($instansi_cd,  $tahun);
        //$rsid = $this->M_create_ba->get_indicator_by_instansi($param, $data_id);
        // foreach ($rsid as $rincian) {
        //     $data_id = $rincian['data_id'];

        // }
        $data = array_merge($data_ba, $instansi_1, $instansi_2);
        $bulan = $data['ba_month'];
        $tahun = $data['ba_year'];

        $html = $this->load->view('admin/berita_acara_statistik/unduh', $data, true);
        $this->pdf->createPDF($html, 'BA_Statistik_' . $bulan . '_' . $tahun, false);
        //$this->load->view('GeneratePdfView', $data);

    }

    public function laporan()
    {
        $this->load->library('mypdf');
        // $data['data'] = array(
        //     ['nim' => '123456789', 'name' => 'example name 1', 'jurusan' => 'Teknik Informatika'],
        //     ['nim' => '123456789', 'name' => 'example name 2', 'jurusan' => 'Jaringan']
        // );
        $instansi_cd = $this->com_user['instansi_cd'];
        $ba_id = array('1646617843069616300');
        $param = array($instansi_cd,  $ba_id);
        $query = $this->M_create_ba_pg->get_berita_acara_by_ba_id($param);

        foreach ($query as $data_ba) {
            $data_ba;
        }
        $instansi_1['data'] = $this->M_instansi->get_detail_instansi($instansi_cd);


        $instansi_cd = $this->com_user['instansi_cd'];
        $get_urusan = $this->M_urusan_pg->get_urusan_by_instansi($instansi_cd);
        $urusan_id = array();
        foreach ($get_urusan as $result) {
            array_push(
                $urusan_id,
                $result['urusan_id']
            );
        }
        $arr = $urusan_id;
        $data_id = implode(', ', $arr);

        $tahun = '2019';
        $param = array($instansi_cd,  $tahun);
        $data['data'] = $this->M_create_ba->get_indicator_by_instansi_ba($param, $data_id);


        // print_r("<pre>");
        // print_r($instansi_1);
        // print_r("</pre>");
        // print_r("<pre>");
        // print_r($data_ba['instansi_cd']);
        // print_r("</pre>");


        // print_r("<pre>");
        // print_r($data2);
        // print_r("</pre>");
        // print_r("<pre>");
        // print_r($data);
        // print_r("</pre>");
        // die;
        $this->mypdf->generate('admin/berita_acara_statistik/laporan', $data, 'laporan-mahasiswa', 'A4', 'landscape');
    }

    public function unduh_rincian($ba_id)
    {

        //GET INSTANSI 
        $instansi_cd = $this->com_user['instansi_cd'];
        $instansi =  $this->M_instansi->get_detail_instansi($instansi_cd);

        //GET BA 
        $param = array($instansi_cd,  $ba_id);
        $query = $this->M_create_ba_pg->get_berita_acara_by_ba_id($param);

        foreach ($query as $data_ba) {
            $data_ba;
        }
        $tahun = $data_ba['ba_year'];

        // GET URUSAN
        $get_urusan = $this->M_urusan_pg->get_urusan_by_instansi($instansi_cd);
        $urusan_id = array();
        foreach ($get_urusan as $result) {
            array_push(
                $urusan_id,
                $result['urusan_id']
            );
        }
        $arr = $urusan_id;
        $data_id = implode(', ', $arr);

        // GET INDICATOR
        $tahun = $data_ba['ba_year'];
        $param = array($ba_id, $instansi_cd,  $tahun);
        $rsid = $this->M_create_ba_pg->get_indicator_by_instansi_unduh_rincian($param, $data_id);



        // SET TITLE
        $result = array();

        // TITLE
        $result[0]['data_id'] = "<b>Kode ID</b>";
        $result[0]['data_name'] = "<b>Nama Data</b>";
        $result[0]['data_type'] = "<b>Indikator/Variabel/Subvariabel/Subsubvariabel</b>";
        $result[0]['year'] = "<b>Tahun</b>";
        $result[0]['data_unit'] = "<b>Satuan</b>";
        $result[0]['value'] = "<b>Nilai</b>";
        $result[0]['data_st'] = "<b>Sifat Data Tahun Terakhir</b>";


        // MERGE DATA;
        $dinas = $instansi['instansi_name'];
        $data = array_merge($result, $rsid);
        // print_r("<pre>");
        // print_r($data);
        // print_r("</pre>");
        // die;
        // DONWLOAD EXCEL WITH PARAMS
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($data);
        $filename =  "Rincian BA " . $data_ba['ba_month'] . " " . $data_ba['ba_year'] . " " . $dinas . ".xlsx";
        $xlsx->downloadAs($filename);
    }
}
