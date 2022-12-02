<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/OperatorBase.php' );

class Setting extends OperatorBase
 {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('admin/master/M_setting');
        // load library
        $this->load->library('tnotification');
        $this->load->library('pagination');
        // $this->load->library('encrypt');
        $this->load->library('encryption');
    }

    
    /**
     * Menganbil data setting
     */
    public function index()
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "admin/master/setting/index.html");
        //load javascript
        // $this->tsmarty->load_javascript("resource/themes/blora-backend/plugins/uniform/uniform.min.js");
        // load data kecamtan
        $setting =  $this->M_setting->get_settting();
        // assign
        $this->tsmarty->assign("result" , $setting );
        // notificationr
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    /**
     * proses update data setting
     */
    public function set_process()
    {
        // set page rules
        $this->_set_page_rule("U");
        
        // load library
        $this->load->library('tupload');
        // cek input
        $this->tnotification->set_rules('title', 'Title', 'trim|max_length[100]');
        $this->tnotification->set_rules('alamat', 'Alamat', 'trim');
        $this->tnotification->set_rules('email', 'E-Mail', 'trim|max_length[100]');
        $this->tnotification->set_rules('no_telp', 'No Telpone', 'trim|max_length[20]');
        $this->tnotification->set_rules('facebook', 'Facebook', 'trim|max_length[100]');
        $this->tnotification->set_rules('twitter', 'Twitter', 'trim|max_length[100]');
        $this->tnotification->set_rules('gplus', 'G-Plus', 'trim|max_length[100]');
        $this->tnotification->set_rules('youtube', 'Youtube', 'trim|max_length[100]');
        $this->tnotification->set_rules('instagram', 'Instagram', 'trim|max_length[100]');
        // set notification
        if($this->tnotification->run() !== FALSE){
            //set parameter
            $params = array(
                'title' => $this->input->post('title', TRUE),
                'alamat' => $this->input->post('alamat', TRUE),
                'email' => $this->input->post('email', TRUE),
                'no_telp' => $this->input->post('no_telp', TRUE),
                'facebook' => $this->input->post('facebook', TRUE),
                'twitter' => $this->input->post('twitter', TRUE),
                'gplus' => $this->input->post('gplus', TRUE),
                'youtube' => $this->input->post('youtube', TRUE),
                'instagram' => $this->input->post('instagram', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdd' => date('Y-m-d H:i:s')
            );
            //cek apakah gambar logo header lama akan di hapus
            if($this->input->post('logo_header_delete', TRUE) != false){
                // proses hapus image link lama dari localserver
                unlink($this->input->post('logo_header_delete'));
                // set paramater null untuk disimpan ke database
                $params['logo_header'] = '';
            }
            //cek apakah gambar logo footer lama akan di hapus
            if($this->input->post('logo_footer_delete', TRUE) != false){
                // proses hapus image link lama dari localserver
                unlink($this->input->post('logo_footer_delete'));
                // set paramater null untuk disimpan ke database
                $params['logo_footer'] = '';
            }
            $where = array(
                'id' => 1,
            );
            // poroses update data link kecamatan di database
            if ($this->M_setting->update_setting($params, $where)) {
                //set notifikasi success
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil disimpan");
            }else{
                // default error
                $this->tnotification->sent_notification("error", "Data gagal disimpan");
            }
        }else{
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("admin/master/setting");
    }
}
