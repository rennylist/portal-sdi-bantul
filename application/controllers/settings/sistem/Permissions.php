<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
// load base class if needed
require_once(APPPATH . 'controllers/base/AdminBase.php');

// --

class Permissions extends AdminBase
{

    // constructor
    public function __construct()
    {
        // parent constructor
        parent::__construct();
        // load model
        $this->load->model('settings/sistem/M_settings');
        // load library
        $this->load->library('tnotification');
    }

    // list role
    public function index()
    {
        // set page rules
        $this->_set_page_rule("R");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/permissions/index.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
        // get search parameter
        $search = $this->session->userdata('search_roles');
        if (!empty($search)) {
            $this->tsmarty->assign("search", $search);
        }
        // search parameters
        $role_nm = empty($search['role_nm']) ? '%' : '%' . $search['role_nm'] . '%';
        $group_id = empty($search['group_id']) ? '%' : $search['group_id'];
        // get data
        $this->tsmarty->assign("rs_id", $this->M_settings->get_all_roles(array($role_nm, $group_id)));
        //groups
        $this->tsmarty->assign("rs_group", $this->M_settings->get_all_group());
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // search process
    public function search_process()
    {
        // set page rules
        $this->_set_page_rule("R");
        // session
        if ($this->input->post('save') == "Cari") {
            // params
            $params = array(
                "role_nm" => $this->input->post('role_nm', TRUE),
                "group_id" => $this->input->post('group_id', TRUE),
            );
            // set session
            $this->session->set_userdata("search_roles", $params);
        } else {
            // unset session
            $this->session->unset_userdata("search_roles");
        }
        // redirect
        redirect("settings/sistem/permissions");
    }

    // list menu by role
    public function access_update($role_id = "")
    {
        // set page rules
        $this->_set_page_rule("U");
        // set template content
        $this->tsmarty->assign("template_content", "settings/sistem/permissions/access_update.html");
        // load javascript
        $this->tsmarty->load_javascript("resource/themes/default/plugins/select2/dist/js/select2.min.js");
        // get detail role
        $result = $this->M_settings->get_detail_role_by_id($role_id);
        if (empty($result)) {
            // default error
            $this->tnotification->sent_notification("error", "Data yang anda pilih tidak terdaftar!");
            redirect("settings/sistem/permissions");
        }
        $this->tsmarty->assign("result", $result);
        $this->tsmarty->assign("detail", $result);
        // get_list_portal
        $rs_portal = $this->M_settings->get_all_portal();
        $this->tsmarty->assign("rs_portal", $rs_portal);
        // set default_portal for filtering
        $default_portal_id = (!empty($rs_portal)) ? $rs_portal[0]["portal_id"] : "";
        $search = $this->session->userdata('filter_portal');
        if (!empty($search)) {
            $default_portal_id = $search["portal_id"];
        }
        $this->tsmarty->assign("default_portal_id", $default_portal_id);
        // get data menu
        $list_menu = self::_display_menu($default_portal_id, $role_id, 0, "");
        $this->tsmarty->assign("list_menu", $list_menu);
        // notification
        $this->tnotification->display_notification();
        $this->tnotification->display_last_field();
        // output
        parent::display();
    }

    // search
    public function filter_portal_process($role_id = "")
    {
        // set page rules
        $this->_set_page_rule("R");
        // session
        if ($this->input->post('save') == "Cari") {
            // params
            $params = array(
                "portal_id" => $this->input->post('portal_id', TRUE),
            );
            // set session
            $this->session->set_userdata("filter_portal", $params);
        } else {
            // unset session
            $this->session->unset_userdata("filter_portal");
        }
        // redirect
        redirect("settings/sistem/permissions/access_update/" . $role_id);
    }

    private function _display_menu($portal_id, $role_id, $parent_id, $indent)
    {
        $html = "";
        // get data
        $params = array($role_id, $portal_id, $parent_id);
        $rs_id = $this->M_settings->get_all_menu_selected_by_parent($params);
        if (!empty($rs_id)) {
            $no = 0;
            $indent .= "--- ";
            foreach ($rs_id as $rec) {
                $role_tp = array("C" => "0", "R" => "0", "U" => "0", "D" => "0");
                $i = 0;
                foreach ($role_tp as $rule => $val) {
                    $N = substr($rec['role_tp'], $i, 1);
                    $role_tp[$rule] = $N;
                    $i++;
                }
                $checked = "";
                if (array_sum($role_tp) > 0) {
                    $checked = "checked='true'";
                }
                // parse
                $html .= "<tr>";
                $html .= "<td class='text-center'>";
                $html .= '<div class="checkbox"><input type="checkbox" id="' . $rec['nav_id'] . '" class="checked-all r-menu" value="' . $rec['nav_id'] . '" ' . $checked . '><label for="' . $rec['nav_id'] . '"></label> </div>';
                $html .= "</td>";
                $html .= "<td><label for='" . $rec['nav_id'] . "'>" . $indent . $rec['nav_title'] . "</label></td>";
                $html .= "";
                $html .= '<td class="text-center"><div class="checkbox"><input type="checkbox" id="c-' . $rec['nav_id'] . '" class="r' . $rec['nav_id'] . ' r-menu" name="rules[' . $rec['nav_id'] . '][C]" value="1" ' . ($role_tp['C'] == "1" ? 'checked ="true"' : "") . '><label for="c-' . $rec['nav_id'] . '"></label></div></td>';
                $html .= '<td class="text-center"><div class="checkbox"><input type="checkbox" id="r-' . $rec['nav_id'] . '" class="r' . $rec['nav_id'] . ' r-menu" name="rules[' . $rec['nav_id'] . '][R]" value="1" ' . ($role_tp['R'] == "1" ? 'checked ="true"' : "") . '><label for="r-' . $rec['nav_id'] . '"></label></div></td>';
                $html .= '<td class="text-center"><div class="checkbox"><input type="checkbox" id="u-' . $rec['nav_id'] . '" class="r' . $rec['nav_id'] . ' r-menu" name="rules[' . $rec['nav_id'] . '][U]" value="1" ' . ($role_tp['U'] == "1" ? 'checked ="true"' : "") . '><label for="u-' . $rec['nav_id'] . '"></label></div></td>';
                $html .= '<td class="text-center"><div class="checkbox"><input type="checkbox" id="d-' . $rec['nav_id'] . '" class="r' . $rec['nav_id'] . ' r-menu" name="rules[' . $rec['nav_id'] . '][D]" value="1" ' . ($role_tp['D'] == "1" ? 'checked ="true"' : "") . '><label for="d-' . $rec['nav_id'] . '"></label></div></td>';
                $html .= "</tr>";
                $html .= $this->_display_menu($portal_id, $role_id, $rec['nav_id'], $indent);
                $no++;
            }
        }
        return $html;
    }

    // process update
    public function process()
    {
        // set page rules
        $this->_set_page_rule("U");
        // cek input
        $this->tnotification->set_rules('role_id', 'Role ID', 'trim|required');
        $this->tnotification->set_rules('portal_id', 'Portal ID', 'trim|required');
        // process
        if ($this->tnotification->run() !== FALSE) {
            // delete
            $params = array(
                $this->input->post('role_id', TRUE),
                $this->input->post('portal_id', TRUE),
            );
            $this->M_settings->delete_role_menu($params);
            // insert
            $rules = $this->input->post('rules');
            if (is_array($rules)) {
                foreach ($rules as $nav => $rule) {
                    // get rule tipe
                    $role_tp = array("C" => "0", "R" => "0", "U" => "0", "D" => "0");
                    $i = 0;
                    foreach ($role_tp as $tp => $val) {
                        if (isset($rule[$tp])) {
                            $role_tp[$tp] = $rule[$tp];
                        }
                        $i++;
                    }
                    $result = implode("", $role_tp);
                    // insert
                    $params = array($this->input->post('role_id'), $nav, $result);
                    $this->M_settings->insert_role_menu($params);
                }
            }
            $this->tnotification->sent_notification("success", "Data berhasil disimpan");
        } else {
            // default error
            $this->tnotification->sent_notification("error", "Data gagal disimpan");
        }
        // default redirect
        redirect("settings/sistem/permissions/access_update/" . $this->input->post('role_id'));
    }

}
