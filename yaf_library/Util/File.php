<?php

class Util_File {
	public static function getFile($dir, $ext = null) {
		$files = array();

		$handle = opendir ($dir);
		if (!$handle) {
			return false;
		}

		while ($file = readdir($handle)) {
			if ($file == "." || $file == "..") {
				continue;
			}

			if ($ext && $ext != pathinfo($file, PATHINFO_EXTENSION)) {
				continue;
			}

			if (!is_file($dir . "/" . $file)) {
				continue;
			}
	
			$files[] = $file;
		}

		return $files;
	}

	public static function saveFile($file, $content) {
		$dir = dirname($file);
		if (!is_dir($dir) ) {
			if (!mkdir($dir)) {
				throw new Exception("Create dir($dir) failed!");
			}
		}

		if (!is_writable ($dir)) {
			throw new Exception("Dir($dir) is not writable!");
		}

		$bytes = file_put_contents($file, $content);

		return $bytes;
	}

	public static function zip($zip_file, $resource, $filename) {
		$zip = new ZipArchive;
		if (!$zip->open($zip_file, ZipArchive::CREATE)) {
			throw new Exception("Open zipfile($filename) failed!");
		} 

		$status = $zip->addFile($resource, $filename);
		$zip->close();
		return $status;
	}
}