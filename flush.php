<?
include("../../../wp-load.php");

if($_REQUEST["flush_post_id"]) {
	BlipprClient::singleton()->cache_expire_by_id($_REQUEST["flush_post_id"]);
	echo "Flushed cached versions of this post.";
} else {
	echo "No post given to flush cache for!";
}
?>