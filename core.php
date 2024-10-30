<?php
/*
Plugin Name: blippr
Plugin URI: http://www.blippr.com/plugin
Description: Pull in rich reviews and ratings about media.
Version: 1.2
Author: blippr.com
Author URI: http://www.blippr.com
*/

include_once("_client.php");
include_once("_hooks.php");
register_activation_hook(__FILE__, array("BlipprClient", 'install'));

// Make sure we're installed to the right directory!
$bits = split("/", __FILE__);
$dirname = $bits[sizeof($bits)-2];
if($dirname != "blippr") {
	echo "<div class='blippr-error-box'>Warning: blippr plugin must be installed to a directory named 'blippr' under your plugins directory to work properly. Current directory name is \"$dirname\"</div>";
}
?>
