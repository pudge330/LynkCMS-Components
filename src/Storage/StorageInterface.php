<?php
namespace BGStudios\Component\Storage;

interface StorageInterface {
	public function get($key);
	public function set($key, $value = null);
	public function has($key);
	public function remove($key);
}