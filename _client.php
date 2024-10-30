<?
class BlipprClient {
	var $pluginLocation;
	var $rootUrl = "http://www.blippr.com";
	var $assetRoot = "http://static1.blippr.com";
	var $apiRoot = "http://api.blippr.com/v2";
	var $imgRoot = "http://static1.blippr.com/images";
	var $categories;
	var $uncountables;
	var $in_development = false;
	var $cache_duration = 86400;			/* One day */
	var $cache_duration_wiggle = 3600;		/* One hour - using cache wiggle ensures that large blocks of content aren't always refreshed at once. */
	var $notry_duration = 300;				/* Five minutes */
	var $hashVersion = 10;
	var $cacheTableName;
	var $requestID;
	var $_bench_start = array();
	var $_bench_totals = array();
	const SILENCE_LOG = true;
	var $override_log = false;
	var $enabled = true;
	
	function BlipprClient() {
		global $wpdb;
		if ($this->in_development) {
			$this->rootUrl = "http://dev2.blippr.com";
			$this->assetRoot = "http://devstatic1.blippr.com";
			$this->apiRoot = "http://devapi.blippr.com/v2";
			$this->imgRoot = "http://devstatic1.blippr.com/images";
		}
		list($head, $tail) = split("\/wp-content\/", dirname(__FILE__), 2);
		$this->pluginLocation = "/wp-content/" . $tail . "/";
		$this->pluginPath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
		$this->filePath = get_option('siteurl') . $this->pluginLocation;
		$this->libPath = $this->pluginPath . 'lib' . DIRECTORY_SEPARATOR;
		$this->categories = get_option("blippr_categories");
		$this->existingContent = array();
		$this->is404 = false;
		$this->combined = false;
		if(!is_array($this->categories)) {
			$this->categories = array();
		}
		$this->uncountables = array(
			"music" => true
		);
		$this->cacheTableName = $wpdb->prefix . "blippr_cache";
		if($_REQUEST["blipprDebug"])
			$this->override_log = true;
	}
	
	function __destruct() {
		$benchSize = sizeof($this->_bench_start);
		if($benchSize != 0) {
			$this->log(sprintf("[BENCH][WARNING] %d entries left on the benchmark stack", $benchSize));
		}
		asort($this->_bench_totals);
		foreach($this->_bench_totals as $key => $val) {
			$this->log(sprintf("[BENCH][%s] %2.2f ms", $key, $val * 1000));
		}
		if(isset($this->logfp)) {
			fclose($this->logfp);
		}
	}
	
	static function singleton() {
		static $obj;
		if(!$obj) $obj = new BlipprClient();
		return $obj;
	}
	
	static function install() {
		global $wpdb;
		$table_name = $wpdb->prefix . "blippr_cache";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name (
				id INTEGER NOT NULL auto_increment,
				hash VARCHAR(64) NOT NULL,
				content TEXT,
				expires_at DATETIME NOT NULL,
				PRIMARY KEY id (id),
				KEY hash (hash)
			)";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			$wpdb->query("ALTER TABLE $table_name MODIFY hash varchar(64) COLLATE utf8_bin NOT NULL");
			$wpdb->query("CREATE INDEX hash_idx ON $table_name (hash)");
		}
	}
	
	function log($msg) {
		BlipprClient::log_message($msg, $this->override_log);
	}
  
	function bench_start() {
		array_push($this->_bench_start, microtime());
	}

	function bench_end($message, $category) {
		$end = microtime();
		if(sizeof($this->_bench_start) == 0) {
			$this->log(sprintf("[BENCH][WARNING] No entries left on the benchmark stack"));
		}
		$start = array_pop($this->_bench_start);
		$total = $end - $start;
		if($total > 0) {
			$this->_bench_totals[$category] += $total;
			if(sizeof($this->_bench_start) == 0)
				$this->_bench_totals["Grand Total"] += $total;
		}
		$this->log(sprintf("[BENCH] %s: %2.2f ms (category: %2.2fms, total: %2.2fms)", $message, $total * 1000, $this->_bench_totals[$category] * 1000, $this->_bench_totals["Grand Total"] * 1000));
	}
	
	static function backtrace($include_backtrace = true) {
		$stack = debug_backtrace();
		$backtrace = "Backtrace: " . $_SERVER['REQUEST_URI'] . "\n";
		if($include_backtrace) {
			foreach($stack as $idx => $trace)
				$backtrace .= $trace["file"] . ":" . $trace["line"] . "\n\tfunction: " . $trace["function"] . "\n";
		}
		BlipprClient::log($backtrace);
	}
	
	static function log_message($msg, $override) {
		if(BlipprClient::SILENCE_LOG && $override != true) return;
		
		static $logfp;		
		if(!isset($logfp)) {
			@$logfp = fopen("blippr.log", "a+");
		}
		if($logfp)
			fwrite($logfp, "[" . date( 'Y-m-d H:i:s', time() ) . "] " . trim($msg) . "\n");
	}
  
	function title_name($singular = false) {
		$title = "titles";
		if(is_array($this->categories)) {
			$keys = array_keys($this->categories);
			if(sizeof($keys) == 1) {
				$title = $keys[0];
			} else {
				$last = array_pop($keys);
				$title = join(", ", $keys) . " or " . $last;
			}
		}
		if($singular) {
			$title = preg_replace("/s\b/", "", $title);
		}
		return $title;
	}
	
	function media_types() {
		return join(",", array_keys($this->categories));
	}
	
	function a_an($title) {
		if($this->uncountables[$title]) {
			return $title;
		} else {
			return preg_match("/^[aeiou]/i", $title) ? "an $title" : "a $title";
		}
	}
		
	function adminHeaders() {
		global $action, $blog_id;
		?>
			<link rel="stylesheet" href="<?=$this->filePath . "admin.css"?>" />
			<script type="text/javascript" src="<?=$this->filePath . "admin.js"?>"></script>
			<script type="text/javascript" src="<?=$this->filePath . "jquery.clearinginput.js"?>"></script>
			<script type="text/javascript" src="<?=$this->filePath . "jquery.autocomplete.min.js"?>"></script>
		<?
		if($_REQUEST["action"] == "edit") { wp_cache_delete($blog_id . "-posts-" . $_REQUEST["post"]); }
	}
	
	function wpHead() {
		if($this->combined) return;		// We're using combined assets.
		?>
		<? if(get_option("blippr_include_jquery")) { ?>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" /></script>
		<? } ?>
		<script type="text/javascript"><!-- blippr = {root: "<?=$this->assetRoot; ?>", apiroot: "<?=$this->apiRoot?>", sourceKey: "<?=get_option("blippr_source_key")?>" }; //--></script>
		<script type="text/javascript" src="<?=$this->assetRoot ?>/javascripts/title_widget.js"></script>
		<?
	}
	
	function wpHeadCombined() {
		$this->combined = true;
		?>
<script type="text/javascript">
<!--
blippr = {root: "<?=$this->assetRoot; ?>", apiroot: "<?=$this->apiRoot?>", sourceKey: "<?=get_option("blippr_source_key")?>" };
//-->		
</script>
		<?
		AssetCombiner::singleton()->addJavascript("title_widget.min.js");
		AssetCombiner::singleton()->addStylesheet("node_widget.min.css");
	}
	
	function adminBoxContent() {
		include("_admin.php");
	}

	function adminSidebarContent() {
		global $post;
		include("_sidebar.php");
	}
	
	function addAdminBox() {
		global $wp_version, $post;
		if( function_exists( 'add_meta_box' )) {
			add_meta_box("blippr_container", "<img src='" . $this->filePath . "favicon.png' style='vertical-align: text-top;' /> Ratings & Reviews From blippr", array(&$this, "adminBoxContent"), 'post', 'advanced');
			print_r($post);
			if($wp_version >= 2.7) {
				add_meta_box("blippr_sidebar", "<img src='" . $this->filePath . "favicon.png' style='vertical-align: text-top;' /> blippr cache", array(&$this, "adminSidebarContent"), 'post', 'side');
			}
		} else {
			throw new Exception('blippr requires Wordpress 2.5 or later.');
		}
	}
	
	function pluginMenu() {
		add_options_page('blippr Options', 'blippr', 8, __FILE__, array(&$this, 'renderOptions'));
	}
	
	function renderOptions() {
		if($_REQUEST["blippr_delete_cache"]) {
			$this->cache_clear();
			$blippr_flash = "Your cache has been cleared.";
		}
		
		if($_REQUEST["blippr_delete_cache_id"]) {
			$this->cache_expire_by_id($_REQUEST["blippr_delete_cache_id"]);
			$blippr_flash = "Post " . $_REQUEST["blippr_delete_cache_id"] . " has been removed from the blippr cache.";
		}
		
		if($_REQUEST["blippr_establish_link"]) {
			$this->cache_set("notry", 0, 'blippr', 0);
		}
		
		include("_options.php");
	}	
	
	function has_memcache() {
		return isset($memcached_servers);
	}
	
	function cache() {
		global $memcached_servers;
		if(!$this->cacheObject) {
			$strategy = get_option("blippr_caching_strategy");
			if($strategy == "memcached" && $this->has_memcache()) {
				require_once($this->pluginPath . "/caching/memcached-client.php");
				$this->cacheObject = new memcached(array(
					"servers"		=> $memcached_servers,
					"persistant"	=> true
				));
			} elseif ($strategy == "database") { 
				require_once($this->pluginPath . "/caching/db-cache.php");
				$this->cacheObject = new blipprDBCache($this->cacheTableName);
			} elseif ($strategy == "filesystem") {
				$cachePath = dirname(dirname(dirname($this->pluginPath))) . "/wp-content/cache/blippr";
				require_once($this->pluginPath . "/caching/fs-cache.php");
				$this->cacheObject = new blipprFSCache($cachePath);
			} else {
				throw new Exception("No caching solution!");
			}
		}
		return $this->cacheObject;
	}
	
	function cache_get($key, $namespace, $expiry = 0) {
		// if($this->in_development) return false;
		$this->bench_start();
		if($expiry > 0 && method_exists($this->cache(), "set_max_age")) {
			$this->cache()->set_max_age($expiry);
		}
		$start = microtime(true);
		$f = $this->cache()->get($namespace . ":" . $key);
		$end = microtime(true);
		$this->bench_end("cache_get: $namespace:$key", "cache_get");
		return $f;
	}

	function cache_set($key, $data, $namespace, $expiry = 900) {
		$this->bench_start();
		$data = $this->cache()->set($namespace . ":" . $key, $data, $expiry);
		$this->bench_end("cache_set: $namespace:$key", "cache_set");
		return $data;
	}
	
	function cache_clear() {
		if(method_exists($this->cache(), "expire_all")) {
			$this->cache()->expire_all();
		}
	}
	
	function get_cache_duration() {
		$delta = $this->cache_duration_wiggle / 2;
		return $this->cache_duration + rand($delta * -1, $delta);
	}
	
	function cache_expire() {
		if(method_exists($this->cache(), "expire_old")) {
			$this->cache()->expire_old($this->cache_duration);
		}
	}
	
	function cache_expire_by_id($id) {
		$key = $this->generateCacheKey($id, "postlinks");
		$this->cache()->expire_single("blippr:" . $key);

		$key = $this->generateCacheKey($id, "post");
		$this->cache()->expire_single("blippr:" . $key);
	}	
	
	function generateCacheKey($id, $namespace = "post") {
		$hash = md5($id . "-" . $this->media_types() . "-" . $this->hashVersion . "-" . get_option("blippr_hash_version"));
		// $this->log("Generating cache key for $namespace, $id - key is $hash");
		return "$namespace-$hash";
	}
	
	function noRelCallback($m) {
		if(!preg_match("/(=|\"|')nofollow/", $m[2])) {
			$m[2] = trim($m[2]) . " rel=\"nofollow\"";
		}
		return $m[1] . $m[2] . $m[3];
	}
	
	function footerLinks($nofollow = false) {
		global $post;
		$key = $this->generateCacheKey($this->get_first($this->postid, $post->ID), "postlinks");
		$content = $this->cache_get($key, "blippr", $this->cache_duration);
		if($nofollow) {
			$content = preg_replace_callback("/(<a )(.*?)(>)/i", array(&$this, "noRelCallback"), $content);
		}
		return $content;
	}
	
	// Some plugins like post_teaser clobber our smilies. How rude!
	// We'll just add them in if they've gone missing.
	
	function addSmiley($matches) {
		if(preg_match("/<img/", $matches[3])) return $matches[0];
		preg_match("/[0-9]+$/", $matches[2], $cls);
		$content = $matches[1] . $matches[3] . "<img src=\"" . $this->imgRoot . "/inline-face_" . $cls[0] . ".png\" />" . $matches[4];
		return $content;
	}
	
	function addBlipprSmilies($content) {
		return preg_replace_callback("/(<a [^>]*?class=\"blippr-inline-smiley ([^>]*?)\".*?>)(.*?)(<\/a>)/", array(&$this, "addSmiley"), $content);
	}
	
	function postTypeFor($post, $posts) {
		global $wpdb;
		$this->bench_start();
		$post_type = false;
		
		// If it's a toplevel...
		if($post->post_type != "revision")
			$post_type = $post->post_type;
			
		// It's a revision, look for a parent
		if(!$post_type) {
			foreach($posts as $key => $parent) {
				if($parent->ID == $post->post_parent) {
					$post_type = $parent->post_type;
					break;
				}
			}
		}
		
		// Couldn't find a parent in the postset, query directly
		if(!$post_type) {
			$this->bench_start();
			$post_type = $wpdb->get_var("SELECT post_type FROM $wpdb->posts WHERE ID = '$post->post_parent'");
			$this->bench_end("Get parent post type via SQL", "SQL");
		}
		$this->bench_end("Get parent post type", "postTypeFor");
		return $post_type;
	}
	
	function postStatusValid($post) {
		global $wpdb;
		$this->bench_start();
		$valid = $post->post_status == "publish";
		if($post->post_status == "inherit") {
			$parent_status = $wpdb->get_var("select post_status from $wpdb->posts where ID = '$post->post_parent'");
			$valid = $parent_status == "publish";
		}
		$this->bench_end("postStatusValid", "postStatusValid");
		return $valid;
	}
	
	function removeBlipprTags($content) {
		$content = preg_replace("/\[\/?noblippr[^\]]*\]/i", "", $content);
		$content = preg_replace("/<\/?noblippr[^>]*>/i", "", $content);
		return $content;
	}
	
	function disable() {
		$this->enabled = false;
	}
	
	function enable() {
		$this->enabled = true;
	}
	
	function cachePostSet($posts) {
		if(!$this->enabled) return $posts;
		// If the first page query is a 404, then we're going to just completely ignore the entire page.
		if(is_404()) {
			$this->log("Got 404: referer is " . $_SERVER["HTTP_REFERER"]);
			BlipprClient::backtrace(false);
			$this->is404 = true;
		}
		
		if (!is_single() && get_option("blippr_singles_only")) return $posts;
			
		if ( !is_admin() && !$this->is404 ) {
			$this->bench_start();
			foreach($posts as $key => $post) {
				if($this->postTypeFor($post, $posts) == "post" && $this->postStatusValid($post)) {
					$data = array(
						"id" 		=> $post->ID,
						"title"	 	=> $post->post_title,
						"content" 	=> $post->post_content,
						"url" 		=> "permalink",
						"date" 		=> $post->post_date,
						"guid"		=> $post->guid
					);
					$this->addToQueue($data, is_preview(), is_preview());
				}
			}
			$this->bench_end("Queueing posts", "postQueue");
			$results = $this->requestContent();
			$this->bench_start();
			foreach($posts as $key => $post) {	
				$this->postid = $post->ID;
				if(is_feed()) {
					if(get_option("blippr_include_footer") == 1) {
						$links = $this->footerLinks();
						$post->post_content = $this->removeBlipprTags($post->post_content);
						if($links) $post->post_content = $post->post_content  . "\n<hr />Reviews: " . $links;
					}
				} else {
					$new_content = $this->get_first($this->existingContent[$post->ID], $results[$post->ID]);
					if($new_content) $post->post_content = $new_content;
				}
			}
			$this->bench_end("cachePostSet (" . sizeof($results) . " entries pulled from blippr)", "cachePostSet");
		}
		return $posts;
	}
	
	function cacheOnSaveByID($id) {
		global $wpdb, $post;
		$this->log("Got post save for ID $id...");
		$row = $wpdb->get_row("SELECT p.post_content, p.post_type, p.guid, p2.post_type as parent_post_type, p.post_title, p.post_date, p.post_status, p2.post_status as parent_post_status FROM $wpdb->posts p left join wp_posts p2 on p2.id = p.post_parent WHERE p.ID = '$id'", ARRAY_A);
		if($row["post_type"] != "post" && $row["parent_post_type"] != "post") return;
		if($_REQUEST["autosave"] == 1) return;		// Don't cache for autosaves...
		$this->bench_start();
		if($row["post_status"] == "publish" || ($row["post_status"] == "inherit" && $row["parent_post_status"] == "publish")) {
			$this->log("Caching!");
			$this->cache_expire();
			$this->cache_expire_by_id($id);
			$this->postid = $id;
			
			if($row["post_content"]) {
				$data = array(
					"id" 		=> $id,
					"title"	 	=> $row["post_title"],
					"content" 	=> $row["post_content"],
					"url" 		=> "permalink",
					"date" 		=> $row["post_date"],
					"guid"		=> $row["guid"]
				);			
				$this->addToQueue($data, true, true);
				$this->requestContent();
			}
		}
		$this->bench_end("cacheOnSaveByID", "cacheOnSaveByID");
	}
	
	function replaceCallback($matches) {
		$key = $this->generateCacheKey($this->postid, "postlinks");
		$this->cache_set($key, $matches[1], "blippr", $this->get_cache_duration());
		return "";		
	}
	
	static function curl() {
		static $curl;
		if(!$curl) {
			$curl = curl_init();
		}
		return $curl;
	}
	
	function addToQueue($data, $force = false, $bustCache = false) {
		$this->bench_start();
		$key = $this->generateCacheKey($data["id"], "post");
		$cached_content = $this->cache_get($key, "blippr", $this->cache_duration);
		if($cached_content && !$force) {
			$this->existingContent[$data["id"]] = $cached_content;
			$this->bench_end("Add to queue: hit cache", "addToQueue");
			return;
		}
		if($force) {
			$this->log("Content refresh for $key forced. Queueing...");
		} else {
			$this->log("Existing content for $key does not exist yet, queueing for remote pull...");
		}
		
		if($data["permalink"] == "permalink") {
			$data["permalink"] = get_permalink($data["id"]);
		}
		if(!$this->cacheQueue) $this->cacheQueue = array();
		$data["force"] = ($bustCache ? 1 : 0);
		$data["key"] = get_option("blippr_source_key");
		$data["secret"] = get_option("blippr_source_secret");
		$this->cacheQueue[$data["id"]] = $data;
		$this->bench_end("Add to queue: full cache", "addToQueue");
	}
	
	function requestContent() {
		if(!$this->cacheQueue) return array();
		$content = json_encode(array_values($this->cacheQueue));
		$reqid = join(",", array_keys($this->cacheQueue));
		
		// If we're in a timeout cycle, don't hit blippr
		$notry = $this->cache_get("notry", "blippr", $this->notry_duration);
		if($notry >= 3) {
			$this->log("Not talking to blippr, notry is $notry");
			return array();
		}
		
		$this->bench_start();
		// No cached copy, pull a copy from blippr
		$url = $this->apiRoot . "/utility/linkup";
		$unlinked = get_option("blippr_link_non_links") ? "1" : "";
		$data = $data = "&class=" . $this->media_types() . "&reqid=" . $reqid . "&format=json&unlinked=$unlinked&body=" . urlencode($content);
		$process = BlipprClient::curl();
		curl_setopt($process, CURLOPT_CONNECTTIMEOUT, 12);
		curl_setopt($process, CURLOPT_TIMEOUT, 12);
		curl_setopt($process, CURLOPT_POSTFIELDS, $data);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($process, CURLOPT_USERAGENT, "blippr Wordpress plugin v 1.0");
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_URL, $url);
		$return = curl_exec($process);
		$info = curl_getinfo($process);
		$err = curl_error($process);
		// curl_close($process);		
		$this->bench_end("Remote request", "Remote request");
		
		unset($this->cacheQueue);
		$this->cacheQueue = array();

		if($info['http_code'] == 200) {			// OK
			$this->bench_start();
			$objs = json_decode($return);
			$results = array();
			if($objs) {
				foreach($objs as $obj) {
					$key = $this->generateCacheKey($obj->id, "postlinks");
					$this->cache_set($key, $obj->footerLinks, "blippr", $this->get_cache_duration());

					$key = $this->generateCacheKey($obj->id, "post");
					$this->cache_set($key, $obj->content, "blippr", $this->get_cache_duration());
					$results[(int)$obj->id] = $obj->content;
				}
			}
			$this->log("Fetched " . sizeof($results) . " remote results");
			$this->bench_end("Decoding and storing results", "Decode and Store");
			return $results;
		} else {							// Timeout (or other error)
			$notry = $this->cache_get("notry", 'blippr', $this->notry_duration);
			$this->log("Error fetching results: HTTP code: " . $info['http_code'] . ", cURL code: " . $err);
			if(!$notry) $notry = 0;
			$this->cache_set("notry", $notry + 1, 'blippr', $this->notry_duration);
			return array();
		}
	}
	
	// Shortcode parsing should be done on blippr's side. Any shortcodes that make it through need to be clobbered.
	// If the shortcode is wrapped around content, just return the content. Works nicely for feeds.
	function shortcode($attrs, $content) {
		return $content;
	}
	
	function get_first() {
		for($i=0; $i<func_num_args(); $i++) {
			$v = func_get_arg($i);
			if($v) return $v;
		}
		return false;
	}
}

function blippr() {} // for function_exists

function blipprFooterLinks() {
	echo BlipprClient::singleton()->footerLinks(true);
}

function blipprHasFooterLinks() {
	return BlipprClient::singleton()->footerLinks() != "";
}
?>
