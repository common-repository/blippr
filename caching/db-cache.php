<?
class blipprDBCache {
	function blipprDBCache($table) {
		$this->table = $table;
		$this->memcache = array();
	}

	function get($key) {
		global $wpdb;
		if($this->memcache[$key]) return $this->memcache[$key];
		$this->memcache[$key] = $wpdb->get_var("select content from " . $this->table . " where hash = '$key' and expires_at >= NOW()");
		return $this->memcache[$key];
	}

	function set($hash, $content, $expire_in) {
		global $wpdb;
		$existing = $wpdb->get_var("select id from " . $this->table . " where hash = '$hash'");
		$this->memcache[$hash] = $content;

		$expire_in += time();
		$expires_at = date( 'Y-m-d H:i:s', $expire_in );

		if($existing) {
			$wpdb->update($this->table, compact("content", "expires_at"), compact("hash"));
		} else {
			$wpdb->insert($this->table, compact("content", "hash", "expires_at"));
		}
		return true;
	}

	function expire_single($key) {
		global $wpdb;
		$this->memcache[$key] = false;
		$query = "delete from " . $this->table . " where hash = '$key'";
		$wpdb->query($query);
	}

	function expire_old() {
		global $wpdb;
		$expires_at = date( 'Y-m-d H:i:s', time() - $age );
		$wpdb->query("delete from " . $this->table . " where expires_at < NOW()");
	}

	function expire_all() {
		global $wpdb;
		$wpdb->query("truncate table " . $this->table);
	}
}
?>
