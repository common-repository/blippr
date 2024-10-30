<?
class blipprFSCache {
	var $maxAge = 0;
	function blipprFSCache($root, $compress = true) {
		$this->compress = $compress;
		$this->root = $root;
		@mkdir($root, 0775, true);
	}
	
	function get($key) {
		$file = $this->root . "/" . $key;
		if(!file_exists($file)) return false;
		$stat = stat($file);
		if($this->maxAge > 0) {
			if($stat["mtime"] < time() - $this->maxAge) {
				unlink($file);
				return false;
			}
		}
		$contents = file_get_contents($file);
		if($this->compress) {
			return gzuncompress($contents);
		} else {
			return $contents;
		}
	}
	
	function set($key, $data) {
		$fp = fopen($this->root . "/" . $key, "w");
		if($this->compress) $data = gzcompress($data, 6);
		fwrite($fp, $data);
		fclose($fp);
		return true;
	}

	function set_max_age($age) {
		$this->maxAge = $age;
	}
	
	// Try to expire old cached fragments
	function expire_old($age) {
		$sweeper = $this->root . "/" . ".sweeper";
		$override = false;
		$stat = false;
		if(!file_exists($sweeper)) {
			$override = true;
		} else {
			$stat = stat($sweeper);
		}
		if($override || $stat["mtime"] < time() - 3600) {
			touch($this->root . "/" . ".sweeper");
			$fp = opendir($this->root);
			$min_fresh_time = time() - $age;
			while($x = readdir($fp)) {
				if(preg_match("/^\./", $x)) continue;
				$stat = stat($x);
				if($stat['mtime'] < $min_fresh_time) {
					unlink($x);
				}
			}
			closedir($fp);
		}
	}
}
?>