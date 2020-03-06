<?php

class Uploader
{
	private $_path;
	private $_file_folder;
	private $_err;
	private $_file_temp;
	private $_file_size;
	private $_file_name;
	private $_file_ext;
	private $_upload_path;
	private $_allow_file_ext;
	private $_upload_basepath;
	private $_init_path = TRUE;
	private $_rename = TRUE;

	function __construct( $config ) {
		$this->_init_path = isset( $config['init_path'] ) ? $config['init_path'] : TRUE;
		$this->_rename = isset( $config['rename'] ) ? $config['rename'] : TRUE;
		$this->_init_upload_path($config['path']);
		$this->_allow_file_ext = explode('|', $config['allowed_types']);
	}

	private function _init_upload_path( $path ) {
		if ( !is_dir($path) ) mkdir( $path );
		$this->_upload_basepath = $path;
		$path = trim($path, '/');
		$this->_file_folder = $path;
		if ( $this->_init_path ) {
			$date_arr = explode('-', date('Y-m-d'));
			foreach ($date_arr as $k => $v) {
				$this->_file_folder .= "/{$v}";
				$path = $path . DIRECTORY_SEPARATOR . $v;
				if ( !is_dir($path) ) mkdir( $path );
			}
		}
		$this->_file_folder = trim($this->_file_folder, '/') . '/';
		$this->_path = $path . DIRECTORY_SEPARATOR;
		$this->_upload_path = str_replace("\\", "/", realpath($this->_path)) . DIRECTORY_SEPARATOR;
	}

	public function get_upload_path() {
		return $this->_upload_path;
	}

	public function get_file_name() {
		return $this->_file_name;
	}

	public function get_file_uri() {
		return $this->_file_folder . $this->_file_name;
	}


	private function set_error($error) {
		$this->_err = $error;
	}

	public function error() {
		return $this->_err;
	}

	public function do_upload($field) {

		if ( ! isset($_FILES[$field])) {
			$this->set_error('没有上传任何文件');
			return FALSE;
		}
		if ( !is_uploaded_file($_FILES[$field]['tmp_name']))
		{
			$error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

			switch($error)
			{
				case 1:	// UPLOAD_ERR_INI_SIZE
					$this->set_error('upload_file_exceeds_limit');
					break;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->set_error('upload_file_exceeds_form_limit');
					break;
				case 3: // UPLOAD_ERR_PARTIAL
					$this->set_error('upload_file_partial');
					break;
				case 4: // UPLOAD_ERR_NO_FILE
					$this->set_error('upload_no_file_selected');
					break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->set_error('upload_no_temp_directory');
					break;
				case 7: // UPLOAD_ERR_CANT_WRITE
					$this->set_error('upload_unable_to_write_file');
					break;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->set_error('upload_stopped_by_extension');
					break;
				default :   $this->set_error('upload_no_file_selected');
					break;
			}
			return FALSE;
		}

		// Set the uploaded data as class variables
		$this->_file_temp = $_FILES[$field]['tmp_name'];
		$this->_file_size = $_FILES[$field]['size'];
		$this->_file_ext = $this->get_extension($_FILES[$field]['name']);
		$this->_file_name = ($this->_rename) ? md5(microtime(TRUE)).".{$this->_file_ext}" : $_FILES[$field]['name'];

		if ( !$this->_is_allowed_filetype()) {
			$this->set_error('上传文件类型不合法');
			return FALSE;
		}

		if ( ! @move_uploaded_file($this->_file_temp, $this->_upload_path.$this->_file_name)) {
			$this->set_error('上传失败');
			return FALSE;
		}
		return TRUE;
	}

	public function get_extension($filename) {
		$tmp = explode('.', $filename);
		return array_pop($tmp);
	}

	private function _is_allowed_filetype() {
		return in_array($this->_file_ext, $this->_allow_file_ext);
	}

	public function remove($file) {
		$file = dirname(realpath($this->_upload_basepath)).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR,$file);
		@unlink($file);
	}
}