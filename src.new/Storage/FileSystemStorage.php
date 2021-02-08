<?php
namespace LynkCMS\Component\Storage;

use Exception;

class FileSystemStorage implements StorageInterface {
	protected $directory;
	public function __construct($directory = null) {
			if (!$directory) {
				$directory = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : null;
			}
			if (!$directory || !file_exists($directory)) {
				throw new \Exception('LynkCMS\\Component\\Storage\\FileSystemStorage expects either a valid directory or for $_SERVER[\'DOCUMENT_ROOT\'] to be set');
			}
			$namespace = $namespace ? DIRECTORY_SEPARATOR . $namespace : '';
			$this->directory = rtrim($directory, DIRECTORY_SEPARATOR) . $namespace;
	}
	public function get($key) {
		if (is_array($key)) {
			$final = Array();
			foreach ($key as $k) {
				$final[$k] = $this->get($k);
			}
			return $final;
		}
		else if (file_exists("{$this->directory}/{$key}")) {
			return file_get_contents("{$this->directory}/{$key}");
		}
	}
	public function set($key, $value = null) {
		if (is_array($key)) {
			$result = true;
			foreach($key as $k => $v) {
				$result = ($result && $this->set($k, $v));
			}
			return $result;
		}
		else {
			return (file_put_contents("{$this->directory}/{$key}", $value));
		}
	}
	public function has($key) {
		return (file_exists("{$this->directory}/{$key}"));
	}
	public function remove($key) {
		if (file_exists("{$this->directory}/{$key}")) {
			return (unlink("{$this->directory}/{$key}"));
		}
		return false;
	}
}