<?php

if (!defined('BASEPATH'))
exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/OperatorBase.php' );

class Navigasi extends OperatorBase {

    // constructor
    public function __construct() {
        parent::__construct();
        // load Model
        $this->load->model('admin/navigasi/M_navigasi');
        $this->load->model('settings/sistem/M_settings');
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
        $this->tsmarty->assign("template_content", "admin/navigasi/navigasi/index.html");
        //params default portal id
        $portal_id = 30;
        // get data portal
        $this->tsmarty->assign("portal", $this->M_navigasi->get_portal_by_id($portal_id));
        // get data menu
        $html = $this->_get_menu_by_portal($portal_id, 0, "");
        $this->tsmarty->assign("rs_id", $html);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    private function _get_menu_by_portal($portal_id, $parent_id, $indent) {
        $html = "";
        $params = array($portal_id, $parent_id);
        $rs_id = $this->M_navigasi->get_all_menu_by_parent($params);
        if ($rs_id) {
            $no = 0;
            $indent .= "--- ";
            foreach ($rs_id as $rec) {
                // url
                $url_edit = site_url('admin/navigasi/navigasi/edit/'. $rec['nav_id']);
                $url_hapus = site_url('admin/navigasi/navigasi/delete/'. $rec['nav_id']);
                // icon
                $icon = "resource/doc/images/nav/default.png";
                if (is_file("resource/doc/images/nav/" . $rec['nav_icon'])) {
                    $icon = "resource/doc/images/nav/" . $rec['nav_icon'];
                }
                // parse
                $html .= "<tr>";
                // $html .= "<td align='center'><i class='fa ".$rec['nav_icon']."'></i></td>";
                $html .= "<td>" . $indent . $rec['nav_title'] . "</td>";
                $html .= "<td>" . $rec['nav_url'] . "</td>";
                // cek status active navigasi
                if($rec['active_st'] == 1){
                    $active = "<span class='text-success text-semibold'>Ya</span>";
                }else{
                    $active = "<span class='text-danger text-semibold'>Tidak</span>";
                }
                $html .= "<td align='center'>" . $active. "</td>";
                // cek apakah menu di tampilkan di menu apa tidak
                if( $rec['display_st']  == 1){
                    $display = "<span class='text-success text-semibold'>Ya</span>";
                }else{
                    $display = "<span class='text-danger text-semibold'>Tidak</span>";
                }
                $html .= "<td align='center'>" .$display. "</td>";
                $html .= "<td align='center'>" .$rec['nav_st']. "</td>";
                $html .= '<td style="text-align: center">';
                $html .= '<a href="'.$url_edit.'" title="Ubah Data" class="btn btn-sm btn-clean btn-icon btn-icon-md"><i class="fas fa-edit"></i></a>';
                $html .= '<a href="'.$url_hapus.'"  title="Hapus Data" class="btn btn-sm btn-clean btn-icon btn-icon-md"><i class="fas fa-trash"></i></a>';
                // $html .= '<a href="'.$url_edit.'" class="btn btn-xs btn-success add-tooltip" data-toggle="tooltip" data-container="body" data-placement="top" data-original-title="edit"><i class="fa fa-edit"></i></a>&nbsp';
                // $html .= '<a href="'.$url_hapus.'" class="btn btn-xs btn-danger add-tooltip" data-toggle="tooltip" data-container="body" data-placement="top" data-original-title="hapus"><i class="fa fa-times"></i></a>';
                $html .= "</td>";
                $html .= "</tr>";
                $html .= $this->_get_menu_by_portal($rec['portal_id'], $rec['nav_id'], $indent);
                $no++;
            }
        }
        return $html;
    }

    private function _get_menu_selectbox_by_portal($portal_id, $parent_id, $indent, $parent_selected) {
        $html = "";
        $params = array($portal_id, $parent_id);
        $rs_id = $this->M_navigasi->get_all_menu_by_parent($params);
        if ($rs_id) {
            $no = 0;
            $indent .= "--- ";
            foreach ($rs_id as $rec) {
                // selected
                $selected = ($parent_selected == $rec['nav_id']) ? 'selected="selected"' : '';
                // parse
                $html .= "<option value='" . $rec['nav_id'] . "' " . $selected . ">";
                $html .= $indent . $rec['nav_title'];
                $html .= "</option>";
                $html .= $this->_get_menu_selectbox_by_portal($rec['portal_id'], $rec['nav_id'], $indent, $parent_selected);
                $no++;
            }
        }
        return $html;
    }

    public function add() {
        // set page rules
        $this->_set_page_rule("C");
        //load javascript
        // $this->tsmarty->load_javascript("resource/js/iconpicker/fontawesome-iconpicker.js");
        //load style
        // $this->tsmarty->load_style("iconpicker/fontawesome-iconpicker.css");
        // set template content
        $this->tsmarty->assign("template_content", "admin/navigasi/navigasi/add.html");
        //params
        $portal_id = 30;
        // get last parent id
        $result = $this->tnotification->get_field_data();
        $parent_selected = isset($result['parent_id']['postdata']) ? $result['parent_id']['postdata'] : 0;
        // get data portal
        $this->tsmarty->assign("portal", $this->M_navigasi->get_portal_by_id($portal_id));
        // get list parent
        $html = $this->_get_menu_selectbox_by_portal($portal_id, 0, "", $parent_selected);
        $this->tsmarty->assign("list_parent", $html);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    public function add_process() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('portal_id', 'Web Portal', 'trim|required');
        $this->tnotification->set_rules('parent_id', 'Induk Menu', '');
        $this->tnotification->set_rules('nav_title', 'Judul', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('nav_desc', 'Deskripsi', 'trim|required|max_length[255]');
        $this->tnotification->set_rules('nav_url', 'Alamat Menu', 'trim|required|max_length[255]');
        $this->tnotification->set_rules('nav_no', 'Urutan', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('active_st', 'Digunakan', 'trim|required');
        $this->tnotification->set_rules('display_st', 'Ditampilkan', 'trim|required');
        $this->tnotification->set_rules('nav_st', 'Status', 'trim|required');

        $id_nav = $this->M_settings->get_nav_last_id($this->input->post('portal_id', TRUE));
        if (!$id_nav) {
            $this->tnotification->set_error_message('ID is not available');
        }

        // run notificasion process
        if ($this->tnotification->run() !== FALSE) {
            // insert
            $params = array(
                'nav_id' => $id_nav,
                'portal_id' => $this->input->post('portal_id', TRUE),
                'parent_id' => $this->input->post('parent_id', TRUE),
                'nav_title' => $this->input->post('nav_title', TRUE),
                'nav_desc' => $this->input->post('nav_desc', TRUE),
                'nav_url' => $this->input->post('nav_url', TRUE),
                'nav_no' => $this->input->post('nav_no', TRUE),
                'active_st' => $this->input->post('active_st', TRUE),
                'display_st' => $this->input->post('display_st', TRUE),
                'nav_icon' => $this->input->post('nav_icon', TRUE),
                'mdb' => $this->com_user['user_id'],
                'mdd' => date("Y-m-d H:i:s")
            );
            if ($this->M_settings->insert_menu($params)) 
            {
                // upload icon
                if (!empty($_FILES['nav_icon']['tmp_name'])) {
                    // load
                    $this->load->library('tupload');
                    // last id
                    // $id_nav = $this->M_navigasi->get_last_inserted_id();

                    // upload config
                    $config['upload_path'] = 'resource/doc/images/nav/';
                    $config['allowed_types'] = 'gif|jpg|png';
                    $config['file_name'] = $id_nav;
                    $this->tupload->initialize($config);
                    // process upload images
                    if ($this->tupload->do_upload_image('nav_icon', false, 36)) {
                        $data = $this->tupload->data();
                        $this->M_navigasi->update_icon(array($data['file_name'], $id_nav));
                    } else {
                        // jika gagal
                        $this->tnotification->set_error_message($this->tupload->display_errors());
                    }
                }
                // notification
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
        redirect("admin/navigasi/navigasi/add/");
    }

    public function edit($nav_id) {
        $portal_id = 30;
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "admin/navigasi/navigasi/edit.html");
        //loas javascript
        // $this->tsmarty->load_javascript('resource/js/iconpicker/fontawesome-iconpicker.js');
        //load style
        // $this->tsmarty->load_style('iconpicker/fontawesome-iconpicker.css');
        // get data portal
        $this->tsmarty->assign("portal", $this->M_navigasi->get_portal_by_id($portal_id));
        // get data
        $result = $this->M_navigasi->get_detail_menu_by_id($nav_id);
        $this->tsmarty->assign("result", $result);
        // // icon
        // $icon = "resource/doc/images/nav/default.png";
        // if (!empty($result)) {
        //     if (is_file("resource/doc/images/nav/" . $result['nav_icon'])) {
        //         $icon = "resource/doc/images/nav/" . $result['nav_icon'];
        //     }
        // }
        // $this->tsmarty->assign("nav_icon", $icon);
        // get last parent id
        if (!empty($result)) {
            $result_field = $this->tnotification->get_field_data();
            $parent_selected = isset($result_field['parent_id']['postdata']) ? $result_field['parent_id']['postdata'] : $result['parent_id'];
        }
        // get list parent
        $html = $this->_get_menu_selectbox_by_portal($portal_id, 0, "", $parent_selected);
        $this->tsmarty->assign("list_parent", $html);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    public function edit_process() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('nav_id', 'Menu ID', 'trim|required');
        $this->tnotification->set_rules('portal_id', 'Web Portal', 'trim|required');
        $this->tnotification->set_rules('parent_id', 'Induk Menu', '');
        $this->tnotification->set_rules('nav_title', 'Judul', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('nav_desc', 'Deskripsi', 'trim|required|max_length[255]');
        $this->tnotification->set_rules('nav_url', 'Alamat Menu', 'trim|required|max_length[255]');
        $this->tnotification->set_rules('nav_no', 'Urutan', 'trim|required|max_length[4]');
        $this->tnotification->set_rules('active_st', 'Digunakan', 'trim|required');
        $this->tnotification->set_rules('display_st', 'Ditampilkan', 'trim|required');
        $this->tnotification->set_rules('nav_st', 'Status', 'trim|required');
        // jika parent dan nav sama
        if ($this->input->post('parent_id') == $this->input->post('nav_id')) {
            $this->tnotification->set_error_message("Induk Menu tidak boleh pada diri sendiri");
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
                $this->input->post('portal_id'),
                $this->input->post('parent_id'),
                $this->input->post('nav_title'),
                $this->input->post('nav_desc'),
                $this->input->post('nav_url'),
                $this->input->post('nav_no'),
                $this->input->post('active_st'),
                $this->input->post('display_st'),
                $this->input->post('nav_icon'),
                $this->input->post('nav_st'),
                $this->com_user['user_id'],
                $this->input->post('nav_id'));
            // update
            if ($this->M_navigasi->update_menu($params)) {
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
        redirect("admin/navigasi/navigasi/edit/" . $this->input->post('nav_id'));
    }

    // form hapus
    public function delete($nav_id) {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "admin/navigasi/navigasi/delete.html");
        //params
        $portal_id = 30;
        // get data portal
        $this->tsmarty->assign("portal", $this->M_navigasi->get_portal_by_id($portal_id));
        // get data
        $result = $this->M_navigasi->get_detail_menu_by_id($nav_id);
        $this->tsmarty->assign("result", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // hapus process
    public function delete_process() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('nav_id', 'Menu ID', 'trim|required');
        $this->tnotification->set_rules('portal_id', 'Web Portal', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array($this->input->post('nav_id'));
            // insert
            if ($this->M_navigasi->delete_menu($params)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("admin/navigasi/navigasi");
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("admin/navigasi/navigasi/delete/". $this->input->post('nav_id'));
    }
}
