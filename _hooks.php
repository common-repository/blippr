<?php

$client = BlipprClient::singleton();

add_shortcode('blippr', 		array (&$client, 'shortcode'), 1);

// Runs on post save so that viewers don't get bogged down.
add_action('publish_post', 					array(&$client, "cacheOnSaveByID"), 1);
add_filter('the_posts',						array(&$client, "cachePostSet"), 1001);
add_filter('the_content', 					array(&$client, "addBlipprSmilies"), 99);
add_action('admin_menu', 		array(&$client, "addAdminBox"), 1);
add_action('admin_head', 		array(&$client, 'adminHeaders'), 1);

add_action('wp_head', 			array(&$client, 'wpHead'), 999);
// add_action('wp_footer', 		array(&$client, 'wpHead'), 999);
add_action('admin_menu', 		array(&$client, 'pluginMenu'), 1);

add_option("blippr_source_key", 		"");
add_option("blippr_source_secret", 		"");
add_option("blippr_include_jquery", 	"1");
add_option("blippr_numblips", 			"3");
add_option("blippr_username", 			"");
add_option("blippr_password", 			"");
add_option("blippr_caching_strategy", 	"database");
add_option("blippr_defer_loading", 		"1");
add_option("blippr_link_non_links", 	"0");
add_option("blippr_include_footer", 	"1");
add_option("blippr_hash_version", 		"1");		// For cache invalidations
add_option("blippr_singles_only", 		"0");		// For cache invalidations
add_option("blippr_categories", 		array(
		"apps" => 1,
		"books" => 1,
		"games" => 1,
		"movies" => 1,
		"music" => 1
	));
// $client->cache_clear();
unset($client);

?>