<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once( BASEPATH . 'libraries/Ftp.php' );
require_once( BASEPATH . 'libraries/Tupload.php' );

class tftp extends CI_FTP {

    private $tupload;
    public $file_name;

    // constructor
    public function __construct() {
        // constructor
        parent::__construct();
        // load library
        $this->tupload = new CI_Tupload();
    }

    // display error message
    public function display_errors() {
        return $this->tupload->display_errors();
    }

    // create directory
    public function create_directory_in_ftp_server($config, $params) {
        // upload file ftp
        $connect['hostname'] = $config['ftp']['ftp_server'];
        $connect['username'] = $config['ftp']['ftp_username'];
        $connect['password'] = $config['ftp']['ftp_password'];
        $connect['debug'] = TRUE;
        // connect FTP
        if ($this->connect($connect)) {
            $dir = explode('/', trim($params['dir_path'], '/'));
            $tmp = "/";
            foreach ($dir as $rec) {
                if (!empty($rec)) {
                    $dest = $rec . '/';
                    $tmp .= $dest;
                    $is_dir = is_dir("ftp://" . $config['ftp']['ftp_username'] . ":" . $config['ftp']['ftp_password'] . "@" . $config['ftp']['ftp_ip'] . $tmp);
                    if (!$is_dir) {
                        $this->mkdir($tmp, $config['ftp']['ftp_chmod']);
                    }
                }
            }
        }
        return TRUE;
    }

    // remove directory
    public function remove_directory_in_ftp_server($config, $params) {
        // upload file ftp
        $connect['hostname'] = $config['ftp']['ftp_server'];
        $connect['username'] = $config['ftp']['ftp_username'];
        $connect['password'] = $config['ftp']['ftp_password'];
        $connect['debug'] = TRUE;
        // connect FTP
        if ($this->connect($connect)) {
            // check directory
            $dir_path = trim($params['dir_path'], '/') . '/';
            if (!$this->list_files($dir_path)) {
                return FALSE;
            } else {
                $this->chmod($dir_path, $config['ftp']['ftp_chmod']);
                // remove directory and files
                return $this->delete_dir($dir_path);
            }
        }
        return FALSE;
        /*
          // example delete directory
          // load library
          $this->load->library('tftp');
          // ftp config
          $config['ftp'] = $this->config->item('ftp_config');
          // upload config
          $params['dir_path'] = '/files/help';
          // upload ftp
          $this->tftp->remove_directory_in_ftp_server($config, $params);
         */
    }

    // upload file ftp
    public function upload_file_to_ftp_server($config) {
        // initialize
        $this->tupload->initialize($config['upload']);
        // validate files
        if (!$this->validate_config_upload($config['upload'])) {
            return FALSE;
        }
        // upload file ftp
        $connect['hostname'] = $config['ftp']['ftp_server'];
        $connect['username'] = $config['ftp']['ftp_username'];
        $connect['password'] = $config['ftp']['ftp_password'];
        $connect['debug'] = TRUE;
        // connect FTP
        if ($this->connect($connect)) {
            // create dir
            $dir = explode('/', trim($this->tupload->upload_path, '/'));
            $tmp = "/";
            foreach ($dir as $rec) {
                if (!empty($rec)) {
                    $dest = $rec . '/';
                    $tmp .= $dest;
                    $is_dir = is_dir("ftp://" . $config['ftp']['ftp_username'] . ":" . $config['ftp']['ftp_password'] . "@" . $config['ftp']['ftp_ip'] . $tmp);
                    if (!$is_dir) {
                        $this->mkdir($tmp, $config['ftp']['ftp_chmod']);
                    }
                }
            }
            // --
            $this->file_name = $this->tupload->file_name;
            // remote files
            $remote_file = $this->tupload->upload_path . $this->tupload->file_name;
            if ($this->upload($_FILES[$config['upload']['files']]['tmp_name'], $remote_file, 'binary', $config['ftp']['ftp_chmod'])) {
                return TRUE;
            } else {
                $this->tupload->set_error('upload_failed');
                return FALSE;
            }
            // close
            $this->close();
        } else {
            $this->tupload->set_error('upload_ftp_failed_to_connect');
            return FALSE;
        }
    }

    // upload images ftp
    public function upload_images_to_ftp_server($config, $new_width = FALSE, $new_height = FALSE) {
        // upload & resize images to local
        $config_local['upload_path'] = 'resource/doc/temp/';
        $config_local['allowed_types'] = $config['upload']['allowed_types'];
        $config_local['max_size'] = isset($config['upload']['max_size']) ? $config['upload']['max_size'] : '';
        $config_local['max_width'] = isset($config['upload']['max_width']) ? $config['upload']['max_width'] : '';
        $config_local['max_height'] = isset($config['upload']['max_height']) ? $config['upload']['max_height'] : '';
        $config_local['file_name'] = $config['upload']['file_name'];
        $config_local['overwrite'] = isset($config['upload']['overwrite']) ? $config['upload']['overwrite'] : TRUE;
        // initialize
        $this->tupload->initialize($config_local);
        // process upload images
        if ($this->tupload->do_upload_image($config['upload']['files'], $new_width, $new_height)) {
            // get file name
            $this->file_name = $this->tupload->file_name;
            // initialize
            $this->tupload->initialize($config['upload']);
            // validate files
            if (!$this->validate_config_upload($config['upload'])) {
                return FALSE;
            }
            // upload file ftp
            $connect['hostname'] = $config['ftp']['ftp_server'];
            $connect['username'] = $config['ftp']['ftp_username'];
            $connect['password'] = $config['ftp']['ftp_password'];
            $connect['debug'] = TRUE;
            // connect FTP
            if ($this->connect($connect)) {
                // create dir
                $dir = explode('/', trim($this->tupload->upload_path, '/'));
                $tmp = "/";
                foreach ($dir as $rec) {
                    if (!empty($rec)) {
                        $dest = $rec . '/';
                        $tmp .= $dest;
                        $is_dir = is_dir("ftp://" . $config['ftp']['ftp_username'] . ":" . $config['ftp']['ftp_password'] . "@" . $config['ftp']['ftp_ip'] . $tmp);
                        if (!$is_dir) {
                            $this->mkdir($tmp, $config['ftp']['ftp_chmod']);
                        }
                    }
                }
                // remote files
                $remote_file = $this->tupload->upload_path . $this->tupload->file_name;
                if ($this->upload('resource/doc/temp/' . $this->file_name, $remote_file, 'binary', $config['ftp']['ftp_chmod'])) {
                    // delete temp
                    if (is_file('resource/doc/temp/' . $this->file_name)) {
                        unlink('resource/doc/temp/' . $this->file_name);
                    }
                } else {
                    $this->tupload->set_error('upload_failed');
                    return FALSE;
                }
                // close
                $this->close();
                // return
                return TRUE;
            }
        }
        // default return
        return FALSE;
    }

    // is file exist
    public function is_file_exist($config, $params) {
        // upload file ftp
        $connect['hostname'] = $config['ftp']['ftp_server'];
        $connect['username'] = $config['ftp']['ftp_username'];
        $connect['password'] = $config['ftp']['ftp_password'];
        $connect['debug'] = TRUE;
        // connect FTP
        if ($this->connect($connect)) {
            // list file ftp
            $dir_path = trim($params['dir_path'], '/');
            $list_file = $this->list_files($dir_path);
            $file = trim($params['dir_path'], '/') . "/" . $params['file_name'];
            if (in_array($file, $list_file)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    // delete file on ftp server
    public function delete_file_in_ftp_server($config, $params) {
        // upload file ftp
        $connect['hostname'] = $config['ftp']['ftp_server'];
        $connect['username'] = $config['ftp']['ftp_username'];
        $connect['password'] = $config['ftp']['ftp_password'];
        $connect['debug'] = TRUE;
        // connect FTP
        if ($this->connect($connect)) {
            // list file ftp
            $dir_path = trim($params['dir_path'], '/');
            $list_file = $this->list_files($dir_path);
            $file = trim($params['dir_path'], '/') . "/" . $params['file_name'];
            if (in_array($file, $list_file)) {
                // chmod
                $this->chmod($file, $config['ftp']['ftp_chmod']);
                // delete
                $this->delete_file($file);
                // return
                return TRUE;
            }
        }
        return FALSE;
    }

    // download file on ftp server
    public function download_file_in_ftp_server($config, $params) {
        // upload file ftp
        $connect['hostname'] = $config['ftp']['ftp_server'];
        $connect['username'] = $config['ftp']['ftp_username'];
        $connect['password'] = $config['ftp']['ftp_password'];
        $connect['debug'] = TRUE;
        // connect FTP
        if ($this->connect($connect)) {
            // file ftp
            $dir_path = trim($params['dir_path'], '/');
            $list_file = $this->list_files($dir_path);
            $file = trim($params['dir_path'], '/') . "/" . $params['file_name'];
            if (in_array($file, $list_file)) {
                // download
                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $params['file_download'] . '"');
                if ($this->download($file, 'php://output', 'binary')) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                // return
                return TRUE;
            }
        }
        return FALSE;
    }

    // copy file on ftp server
    public function move_file_ftp_server_to_local($config, $params) {
        // upload file ftp
        $connect['hostname'] = $config['ftp']['ftp_server'];
        $connect['username'] = $config['ftp']['ftp_username'];
        $connect['password'] = $config['ftp']['ftp_password'];
        $connect['debug'] = TRUE;
        // connect FTP
        if ($this->connect($connect)) {
            // file ftp
            $dir_path = trim($params['dir_path'], '/');
            $list_file = $this->list_files($dir_path);
            $file = trim($params['dir_path'], '/') . "/" . $params['file_name'];
            if (in_array($file, $list_file)) {
                // check local path
                $target_path = trim($params['target_path'], '/');
                if (!is_dir($target_path)) {
                    $this->tupload->make_dir($target_path);
                }
                // target file
                $target_file = $target_path . '/' . $params['target_file_name'];
                // download
                if ($this->download($file, $target_file, 'binary')) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                // return
                return TRUE;
            }
        }
        return FALSE;
        /*
          // example download to local
          // load library
          $this->load->library('tftp');
          // ftp config
          $config['ftp'] = $this->config->item('ftp_config');
          // upload config
          $params['dir_path'] = 'files/help';
          $params['file_name'] = '86_TANDA_TERIMA_BERKAS_PTO-140600168.pdf';
          $params['target_path'] = 'resource/doc/files/help';
          $params['target_file_name'] = '86_TANDA_TERIMA_BERKAS_PTO-140600168.pdf';
          // upload ftp
          $this->tftp->move_file_ftp_server_to_local($config, $params);
         */
    }

    // check config upload
    private function validate_config_upload($config) {
        // Is $_FILES[$config['files']] set? If not, no reason to continue.
        if (!isset($_FILES[$config['files']])) {
            $this->tupload->set_error('upload_no_file_selected');
            return FALSE;
        }
        // Set the uploaded data as class variables
        $this->tupload->file_temp = $_FILES[$config['files']]['tmp_name'];
        $this->tupload->file_size = $_FILES[$config['files']]['size'];
        $this->tupload->file_type = preg_replace("/^(.+?);.*$/", "\\1", $_FILES[$config['files']]['type']);
        $this->tupload->file_type = strtolower(trim(stripslashes($this->tupload->file_type), '"'));
        $this->tupload->file_name = $this->tupload->_prep_filename($_FILES[$config['files']]['name']);
        $this->tupload->file_ext = $this->tupload->get_extension($this->tupload->file_name);
        $this->tupload->client_name = $this->tupload->file_name;
        // Is the file type allowed to be uploaded?
        if (!$this->tupload->is_allowed_filetype()) {
            $this->tupload->set_error('upload_invalid_filetype');
            return FALSE;
        }
        // if we're overriding, let's now make sure the new name and type is allowed
        if ($this->tupload->_file_name_override != '') {
            $this->tupload->file_name = $this->tupload->_prep_filename($this->tupload->_file_name_override);

            // If no extension was provided in the file_name config item, use the uploaded one
            if (strpos($this->tupload->_file_name_override, '.') === FALSE) {
                $this->tupload->file_name .= $this->tupload->file_ext;
            }

            // An extension was provided, lets have it!
            else {
                $this->tupload->file_ext = $this->tupload->get_extension($this->tupload->_file_name_override);
            }

            if (!$this->tupload->is_allowed_filetype(TRUE)) {
                $this->set_error('upload_invalid_filetype');
                return FALSE;
            }
        }
        // Convert the file size to kilobytes
        if ($this->tupload->file_size > 0) {
            $this->tupload->file_size = round($this->tupload->file_size / 1024, 2);
        }
        // Is the file size within the allowed maximum?
        if (!$this->tupload->is_allowed_filesize()) {
            $this->tupload->set_error('upload_invalid_filesize');
            return FALSE;
        }
        // Are the image dimensions within the allowed size?
        // Note: This can fail if the server has an open_basdir restriction.
        if (!$this->tupload->is_allowed_dimensions()) {
            $this->tupload->set_error('upload_invalid_dimensions');
            return FALSE;
        }
        // Sanitize the file name for security
        $this->tupload->file_name = $this->tupload->clean_file_name($this->tupload->file_name);
        // Truncate the file name if it's too long
        if ($this->tupload->max_filename > 0) {
            $this->tupload->file_name = $this->limit_filename_length($this->tupload->file_name, $this->tupload->max_filename);
        }
        // Remove white spaces in the name
        if ($this->tupload->remove_spaces == TRUE) {
            $this->tupload->file_name = preg_replace("/\s+/", "_", $this->tupload->file_name);
        }
        /*
         * Validate the file name
         * This function appends an number onto the end of
         * the file if one with the same name already exists.
         * If it returns false there was a problem.
         */
        $this->tupload->orig_name = $this->tupload->file_name;
        // overwrite file name
        if ($this->tupload->overwrite == FALSE) {
            $this->tupload->file_name = $this->tupload->set_filename($this->tupload->upload_path, $this->tupload->file_name);

            if ($this->tupload->file_name === FALSE) {
                return FALSE;
            }
        }
        // return
        return TRUE;
    }

}
