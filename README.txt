=== Blippr ===
Contributors: cheald
Tags: blippr, ratings, reviews, mashable
Requires at least: 2.7
Tested up to: 2.9.2
Stable tag: trunk

Enhance your blog with rich ratings and reviews from blippr.com, and syndicate your content back to blippr.

== Description ==

Enhance your blog with ratings and reviews from blippr.com. Simply install the plugin and your content will automatically be scanned and enriched with the blippr plugin (don't worry, we don't change your actual posts - you can turn it off at any time by disabling the plugin).

Additionally, you can sign up for the blippr Blipbacks network, so you receive backlinks when people blip from your site, and you can even syndicate your content back into blippr, to receive additional traffic and visibility.

== Installation ==

1) Add this directory to your wp-content/plugins directory in your Wordpress install.
2) Go to your Wordpress administration panel, then go to "Plugins", and click "Activate" next to
   the blippr plugin.
3) Got to the settings for the "blippr" plugin and configure the plugin as you require for your
   blog.
   

**Once installed, blippr will automatically begin scanning your content and adding small rating icons next to relevant terms.** However, if you want to specifically link a given word or phrase, you can use special [blippr] markup to do so.

Insert blippr content into your posts with easy-to-use blippr shortcodes. A shortcode takes the form of:

[blippr id="12345"]

The ID can be found by using the blippr WordPress plugin panel on the New/Edit Post screen, or from any blippr title's URL. You can also specify multiple IDs together with commas to generate a block of multiple items, like so:

[blippr id="12345,54321,9999"]

Additionally, you can specify display options for each shortcode tag. They are:

* blips - may be a number between 0 and 10. Specifies the maximum number of blips to show on this item.
* note - A label inserted by the AJAX search tool. Does not affect display - just helps post authors keep track of which blippr tag is which.

For example:

[blippr id="12345" blips="9"]

== Screenshots ==
1. The review icons that show up to relevant links.
2. User reviews pop up when a user hovers the review icon.