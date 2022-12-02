<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once( APPPATH . 'controllers/base/AdminBase.php' );

// --

class Menu extends AdminBase {

    // constructor
    public function __construct() {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('settings/sistem/M_settings');
        // load library
        $this->load->library('tnotification');
    }

    // list portal menu
    public function index() {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/menu/index.html");
        // get data
        $this->tsmarty->assign("rs_id", $this->M_settings->get_all_portal_menu());
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // list navigasi by portal
    public function navigation($portal_id = '') {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/menu/navigation.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
        // get data portal
        $portal = $this->M_settings->get_portal_by_id($portal_id);
        if (empty($portal)) {
            redirect('settings/sistem/menu');
        }
        $this->tsmarty->assign("portal", $portal);
        // get search parameter
        $search = $this->session->userdata('search_menu');
        // search parameters
        $search['parent_id'] = empty($search['parent_id']) ? 0 : $search['parent_id'];
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // get data menu
        $html = $this->_get_menu_by_portal($portal_id, $search['parent_id'], "");
        // print_r($this->_get_menu_by_portal($portal_id, $search['parent_id'], "")); exit();
        // echo $search['parent_id']; exit();
        $this->tsmarty->assign("html", $html);
        // get list parent
        $rs_parent = $this->M_settings->get_all_menu_by_parent(array($portal_id, 0));
        $this->tsmarty->assign("rs_parent", $rs_parent);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // search
    public function search_process($portal_id = "") {
        // set page rules
        $this->_set_page_rule("R");
        // session
        if ($this->input->post('save') == "Cari") {
            // params
            $params = array(
                "parent_id" => $this->input->post('parent_id', TRUE),
            );
            //       print_r($params);exit;
            // set session
            $this->session->set_userdata("search_menu", $params);
        } else {
            // unset session
            $this->session->unset_userdata("search_menu");
        }
        // redirect
        redirect("settings/sistem/menu/navigation/" . $portal_id);
    }

    // get menu
    private function _get_menu_by_portal($portal_id, $parent_id, $indent) {
        $html = "";
        $params = array($portal_id, $parent_id);
        $rs_id = $this->M_settings->get_all_menu_by_parent($params);
        // print_r($rs_id); exit();
        if ($rs_id) {
            $no = 0;
            $indent .= "--- ";
            foreach ($rs_id as $rec) {
                // url
                $url_edit = site_url('settings/sistem/menu/edit/' . $portal_id . '/' . $rec['nav_id']);
                $url_hapus = site_url('settings/sistem/menu/delete/' . $portal_id . '/' . $rec['nav_id']);
                // icon
                $icon = '';
                if (!empty($rec['nav_icon'])) {
                    $icon = '<i class="' . $rec['nav_icon'] . '"></i>';
                }
                // cek active
                if ($rec['active_st'] == '1') {
                    $active = 'IYA';
                } elseif ($rec['active_st'] == '0') {
                    $active = 'TIDAK';
                }
                // cek display
                if ($rec['display_st'] == '1') {
                    $display = 'IYA';
                } elseif ($rec['display_st'] == '0') {
                    $display = 'TIDAK';
                }
                // parse
                $html .= "<tr>";
                $html .= "<td class='text-center'>" . $icon . "</td>";
                $html .= "<td>" . $indent . $rec['nav_title'] . "</td>";
                $html .= "<td>" . $rec['nav_url'] . "</td>";
                $html .= "<td class='text-center'>" . $active . "</td>";
                $html .= "<td class='text-center'>" . $display . "</td>";
                $html .= "<td class='text-center'>";
                $html .= "<a href='" . $url_edit . "' class='btn btn-xs white text-success'><i class='fa fa-pencil'></i></a> &nbsp;";
                $html .= "<a href='" . $url_hapus . "' class='btn btn-xs white text-danger'><i class='fa fa-trash'></i></a>";
                $html .= "</td>";
                $html .= "</tr>";
                $html .= $this->_get_menu_by_portal($rec['portal_id'], $rec['nav_id'], $indent);
                $no++;
            }
        }
        return $html;
    }

    // form tambah menu
    public function add($portal_id = "") {
        // set page rules
        $this->_set_page_rule("C");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/menu/add.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
        // get data portal
        $portal = $this->M_settings->get_portal_by_id($portal_id);
        if (empty($portal)) {
            redirect('settings/sistem/menu');
        }
        $this->tsmarty->assign("portal", $portal);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // get last field
        $result = $this->tnotification->get_field_data();
        $parent_selected = isset($result['parent_id']['postdata']) ? $result['parent_id']['postdata'] : 0;
        // get list parent
        $html = $this->_get_menu_selectbox_by_portal($portal_id, 0, "", $parent_selected);
        $this->tsmarty->assign("list_parent", $html);
        // output
        parent::display();
    }

    // get menu selectbox
    private function _get_menu_selectbox_by_portal($portal_id, $parent_id, $indent, $parent_selected) {
        $html = "";
        $params = array($portal_id, $parent_id);
        $rs_id = $this->M_settings->get_all_menu_by_parent($params);
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

    // proses tambah
    public function process_add() {
        // set page rules
        $this->_set_page_rule("C");
        // cek input
        $this->tnotification->set_rules('portal_id', 'Web Portal', 'trim|required');
        $this->tnotification->set_rules('parent_id', 'Induk Menu', 'trim');
        $this->tnotification->set_rules('nav_title', 'Judul', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('nav_desc', 'Deskripsi', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('nav_url', 'Alamat Menu', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('nav_no', 'Urutan', 'trim|required|max_length[5]');
        $this->tnotification->set_rules('active_st', 'Digunakan', 'trim|required');
        $this->tnotification->set_rules('display_st', 'Ditampilkan', 'trim|required');
        $this->tnotification->set_rules('nav_icon', 'Icon Class', 'trim|max_length[50]');
        // nav id
        $nav_id = $this->M_settings->get_nav_last_id($this->input->post('portal_id', TRUE));
        if (!$nav_id) {
            $this->tnotification->set_error_message('ID is not available');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
                'nav_id' => $nav_id,
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
            // insert
            if ($this->M_settings->insert_menu($params)) {
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
        redirect("settings/sistem/menu/add/" . $this->input->post('portal_id'));
    }

    // form edit
    public function edit($portal_id = '', $nav_id = '') {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/menu/edit.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
        // get data portal
        $portal = $this->M_settings->get_portal_by_id($portal_id);
        if (empty($portal)) {
            redirect('settings/sistem/menu');
        }
        $this->tsmarty->assign("portal", $portal);
        // get data
        $result = $this->M_settings->get_detail_menu_by_id($nav_id);
        $this->tsmarty->assign("result", $result);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // get last
        $result_field = $this->tnotification->get_field_data();
        if (isset($result_field['parent_id']['postdata'])) {
            $parent_selected = isset($result_field['parent_id']['postdata']) ? $result_field['parent_id']['postdata'] : $result['parent_id'];
        } else {
            $parent_selected = $result['parent_id'];
        }
        // get list parent
        $html = $this->_get_menu_selectbox_by_portal($portal_id, 0, "", $parent_selected);
        $this->tsmarty->assign("list_parent", $html);
        // output
        parent::display();
    }

    // edit process
    public function process_update() {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('nav_id', 'ID', 'trim|required');
        $this->tnotification->set_rules('portal_id', 'Web Portal', 'trim|required');
        $this->tnotification->set_rules('parent_id', 'Induk Menu', 'trim');
        $this->tnotification->set_rules('nav_title', 'Judul', 'trim|required|max_length[50]');
        $this->tnotification->set_rules('nav_desc', 'Deskripsi', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('nav_url', 'Alamat Menu', 'trim|required|max_length[100]');
        $this->tnotification->set_rules('nav_no', 'Urutan', 'trim|required|max_length[5]');
        $this->tnotification->set_rules('active_st', 'Digunakan', 'trim|required');
        $this->tnotification->set_rules('display_st', 'Ditampilkan', 'trim|required');
        $this->tnotification->set_rules('nav_icon', 'Icon Class', 'trim|max_length[50]');
        // jika parent dan nav sama
        if ($this->input->post('parent_id') == $this->input->post('nav_id')) {
            $this->tnotification->set_error_message("Induk Menu tidak boleh pada diri sendiri");
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            // params
            $params = array(
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
            // where
            $where = array(
                'nav_id' => $this->input->post('nav_id', TRUE),
                'portal_id' => $this->input->post('portal_id', TRUE),
            );
            // update
            if ($this->M_settings->update_menu($params, $where)) {
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
        redirect("settings/sistem/menu/edit/" . $this->input->post('portal_id') . '/' . $this->input->post('nav_id'));
    }

    // form hapus
    public function delete($portal_id = '', $nav_id = '') {
        // set page rules
        $this->_set_page_rule("D");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/menu/hapus.html");
        // get data portal
        $portal = $this->M_settings->get_portal_by_id($portal_id);
        if (empty($portal)) {
            redirect('settings/sistem/menu');
        }
        $this->tsmarty->assign("portal", $portal);
        // get data
        $result = $this->M_settings->get_detail_menu_by_id($nav_id);
        $this->tsmarty->assign("detail", $result);
        // get parent
        $parent = $this->M_settings->get_detail_menu_by_id($result['parent_id']);
        $this->tsmarty->assign("parent", $parent);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // hapus process
    public function process_delete() {
        // set page rules
        $this->_set_page_rule("D");
        // cek input
        $this->tnotification->set_rules('nav_id', 'Menu ID', 'trim|required');
        $this->tnotification->set_rules('portal_id', 'Web Portal', 'trim|required');
        // check child
        $params = array(
            $this->input->post('portal_id', TRUE),
            $this->input->post('nav_id', TRUE),
        );
        $child = $this->M_settings->get_all_menu_by_parent($params);
        if (!empty($child)) {
            $this->tnotification->set_error_message('Hapus / pindahkan terlebih dahulu menu anaknya!');
        }
        // process
        if ($this->tnotification->run() !== FALSE) {
            $params = array(
                'portal_id' => $this->input->post('portal_id'),
                'nav_id' => $this->input->post('nav_id'),
            );
            // delete
            if ($this->M_settings->delete_menu($params)) {
                // notification
                $this->tnotification->delete_last_field();
                $this->tnotification->sent_notification("success", "Data berhasil dihapus");
                // default redirect
                redirect("settings/sistem/menu/navigation/" . $this->input->post('portal_id'));
            } else {
                // default error
                $this->tnotification->sent_notification("error", "Data gagal dihapus");
            }
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal dihapus");
        }
        // default redirect
        redirect("settings/sistem/menu/delete/" . $this->input->post('portal_id') . '/' . $this->input->post('nav_id'));
    }

}
