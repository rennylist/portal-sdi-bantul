<?php

if (!defined('BASEPATH'))
exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/OperatorBase.php' );

class Slideshow extends OperatorBase {

    // constructor
    public function __construct() {
        parent::__construct();
        // load model
        $this->load->model('admin/datas/M_slideshow');
        // load library
        $this->load->library('tnotification');
        $this->load->library('pagination');
        $this->load->library("tdtm");
        $this->tsmarty->assign("datetimemanipulation", $this->tdtm);
    }

    // list data
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/datas/slideshow/index.html");
        // get session search
        $search = $this->session->userdata('search_slide');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // search
        $judul = empty($search['judul']) ? '%' : '%' . $search['judul'] . '%';
        // get list data
        $params = array($judul);
        $rs_id = $this->M_slideshow->get_list_data_slideshow($params);
        //assign data
        $this->tsmarty->assign("rs_id", $rs_id);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // search process
    public function search_process() {
        // set page rules
        $this->_set_page_rule("R");
        // session
        if ($this->input->post('save') == "Cari") {
            // params
            $params = array(
                "judul" => $this->stripHTMLtags($this->input->post('judul', TRUE))
            );
            // set session
            $this->session->set_userdata("search_slide", $params);
        } else {
            // unset session
            $this->session->unset_userdata("search_slide");
        }
        // redirect
        redirect("admin/datas/slideshow");
    }

    // add form
    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "admin/datas/slideshow/add.html");
        // load js
        $this->tsmarty->load_javascript("resource/themes/default/plugins/ckeditor/ckeditor.js");
        $this->tsmarty->load_javascript('resource/themes/default/plugins/select2/dist/js/select2.js');
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // add data pokok process
    public function add_process() {
        // set page rules
        $this->_set_page_rule("C");
        // load library
        $this->load->library('tupload');
        // cek input
        $this->tnotification->set_rules('slideshow_title', 'Judul', 'trim|max_length[100]');
        $this->tnotification->set_rules('slideshow_desc', 'Deskripsi', 'trim|max_length[255]');
        $this->tnotification->set_rules('slideshow_order', 'Urutan', 'trim|required|max_length[11]');
        $this->tnotification->set_rules('publish_st', 'Status Ditampilkan', 'trim|required');
        // id_pokok_menu id
        $prefix = date('Y');
        $params = '%';
        $slideshow_id = $this->M_slideshow->get_data_slideshow_last_kode($prefix, $params);
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
                'slideshow_id' => $slideshow_id,
                'slideshow_title' => $this->stripHTMLtags($this->input->post('slideshow_title', TRUE)),
                'slideshow_desc' => ($this->input->post('slideshow_desc', FALSE)),
                'slideshow_order' => $this->stripHTMLtags($this->input->post('slideshow_order', TRUE)),
                'publish_st' => $this->stripHTMLtags($this->input->post('publish_st', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            // insert
            if ($this->M_slideshow->insert_data_slideshow($params)) {
                // cek gambar
                if (!empty($_FILES['gambar_slideshow']['tmp_name'])) {
                    // file name
                    // $file_name = $this->input->post('img_name', TRUE);
                    $file_name = $slideshow_id;
                    // upload config
                    $config['upload_path'] = 'resource/doc/images/slideshow/';
                    $config['allowed_types'] = 'jpg|png|gif|jpeg';
                    $config['file_name'] = $file_name;
                    $config['overwrite'] = TRUE;
                    // set config
                    $this->tupload->initialize($config);
                    // upload files ftp
                    if ($this->tupload->do_upload('gambar_slideshow')) {
                        // input gambar data pokok
                        $files_data = $this->tupload->data();
                        $params = array(
                            'img_name' => $files_data['file_name'],
                            'img_path' => $config['upload_path']
                        );
                        $where = array(
                            'slideshow_id' => $slideshow_id
                        );
                        $this->M_slideshow->update_data_slideshow($params, $where);
                    } else {
                        // notification
                        $this->tnotification->sent_notification("error", $this->tupload->display_errors());
                        // default redirect
                        redirect("admin/datas/slideshow/add");
                    }
                }
                // cek file
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
                // default redirect
                redirect("admin/datas/slideshow/add");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
            // default redirect
            redirect("admin/datas/slideshow/add");
        }
        // default redirect
        redirect("admin/datas/slideshow");
    }

    // edit data pokok kategori form
    public function edit($slideshow_id = "") {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "admin/datas/slideshow/edit.html");
        // load js
        $this->tsmarty->load_javascript("resource/themes/default/plugins/ckeditor/ckeditor.js");
        $this->tsmarty->load_javascript('resource/themes/default/plugins/select2/dist/js/select2.js');
        // get data
        $result = $this->M_slideshow->get_data_slideshow_by_id(array($slideshow_id));
        $foto = base_url() . $result['img_path'] . $result['img_name'];
        // echo $foto; exit();
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("admin/datas/slideshow");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        $this->tsmarty->assign("foto", $foto);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // edit data pokok kategori process
    public function edit_process() {
        // set page rules
        $this->_set_page_rule("U");
        // load library
        $this->load->library('tupload');
        // cek input
        $this->tnotification->set_rules('slideshow_id', 'ID Data Slideshow', 'trim|required|max_length[10]');
        $this->tnotification->set_rules('slideshow_title', 'Judul', 'trim|max_length[100]');
        $this->tnotification->set_rules('slideshow_desc', 'Deskripsi', 'trim|max_length[255]');
        $this->tnotification->set_rules('slideshow_order', 'Urutan', 'trim|required|max_length[11]');
        $this->tnotification->set_rules('publish_st', 'Status Ditampilkan', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
           
            // params
            $params = array(
                'slideshow_title' => $this->stripHTMLtags($this->input->post('slideshow_title', TRUE)),
                'slideshow_desc' => ($this->input->post('slideshow_desc', FALSE)),
                'slideshow_order' => $this->stripHTMLtags($this->input->post('slideshow_order', TRUE)),
                'publish_st' => $this->stripHTMLtags($this->input->post('publish_st', TRUE)),
                'mdb' => $this->com_user['user_id'],
                'mdb_name' => $this->com_user['user_alias'],
                'mdd' => date("Y-m-d H:i:s")
            );
            $where = array(
                'slideshow_id' => $this->stripHTMLtags($this->input->post('slideshow_id', TRUE))
            );
            // update
            if ($this->M_slideshow->update_data_slideshow($params, $where)) {
                // cek gambar
                if (!empty($_FILES['gambar_slideshow']['tmp_name'])) {
                    // file name
                    // $file_name = $this->input->post('img_name', TRUE);
                    $file_name = $this->input->post('slideshow_id', TRUE);
                    // upload config
                    $config['upload_path'] = 'resource/doc/images/slideshow/';
                    $config['allowed_types'] = 'jpg|png|gif';
                    $config['file_name'] = $file_name;
                    $config['overwrite'] = TRUE;
                    // set config
                    $this->tupload->initialize($config);
                    // upload files ftp
                    if ($this->tupload->do_upload('gambar_slideshow')) {
                        // input gambar data pokok
                        $files_data = $this->tupload->data();
                        $params = array(
                            'img_name' => $files_data['file_name'],
                            'img_path' => $config['upload_path']
                        );
                        $where = array(
                            'slideshow_id' => $this->stripHTMLtags($this->input->post('slideshow_id', TRUE))
                        );
                        $this->M_slideshow->update_data_slideshow($params, $where);
                    } else {
                        // notification
                        $this->tnotification->sent_notification("error", $this->tupload->display_errors());
                        // default redirect
                        redirect("admin/datas/slideshow");
                    }
                }
                // cek file
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/datas/slideshow/edit/" . $this->input->post('slideshow_id', TRUE));
    }

    // delete data form
    public function delete($slideshow_id = "") {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "admin/datas/slideshow/delete.html");
        // get data
        $result = $this->M_slideshow->get_data_slideshow_by_id(array($slideshow_id));
        $foto = base_url() . $result['img_path'] . $result['img_name'];
        // check
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("admin/datas/slideshow");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        $this->tsmarty->assign("foto", $foto);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // delete process
    public function delete_process() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('slideshow_id', 'ID Data Slideshow', 'trim|required|max_length[10]');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'slideshow_id' => $this->stripHTMLtags($this->input->post('slideshow_id', TRUE))
            );
            // delete
            if ($this->M_slideshow->delete_data_slideshow($params)) {
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("admin/datas/slideshow");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("admin/datas/slideshow/delete/" . $this->input->post('slideshow_id'));
    }
}
