<div class="wrap">
	<? if($blippr_flash) { ?>
		<div class="blippr-alert"><?=$blippr_flash?></div>

		<? } ?>
	<h2>blippr settings</h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">Your blippr login</th>
				<td>
					<input type="text" name="blippr_username" class="regular-text" value="<?php echo get_option('blippr_username'); ?>" />
				</td>
				<td>
					<span class="setting-description">The email you signed up for blippr with. You need to set your account information to add titles to blippr via the plugin.</span>
				</td>
			</tr>			 
			<tr valign="top">
				<th scope="row" width="1%" nowrap>Your blippr password</th>
				<td>
					<input type="password" name="blippr_password" class="regular-text" value="<?php echo get_option('blippr_password'); ?>" />
				</td>
			</tr>			 
			<tr valign="top">
				<th scope="row" width="1%" nowrap>Source key</th>
				<td>
					<input type="text" name="blippr_source_key" class="regular-text" value="<?php echo get_option('blippr_source_key'); ?>" />
				</td>
				<td>
					<span class="setting-description">A source key causes any blips that happen from your blog to be credited to your blog. Build links and traffic back to your blog when people blip!</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" width="1%" nowrap>Source secret</th>
				<td>
					<input type="text" name="blippr_source_secret" class="regular-text" value="<?php echo get_option('blippr_source_secret'); ?>" />
				</td>
				<td>
					<span class="setting-description">A source secret validates backlinks to your blog from blippr.</span>
				</td>
			</tr>			 
			
			<tr valign="top">
				<th class="th-full" colspan="2" scope="row">
					<h2>Categories</h2>
					<p>Select the categories that you would like blippr to provide data for:</p>
					<? $cats = array("Apps", "Books", "Games", "Movies", "Music"); ?>
					<? $opt_cats = get_option('blippr_categories'); ?>
					<? if(!is_array($opt_cats) || sizeof(array_values($opt_cats)) == 0) { ?>
					<div class="blippr-error-box">Please select at least one category.</div>
					<? } ?>
					<? foreach($cats as $cat_title) { ?>
						<? $cat = strtolower($cat_title); ?>
						<span class="blippr-checkbox-list">
							<input type="checkbox" name="blippr_categories[<?=$cat?>]" id="blippr_categories_<?=$cat?>" value="1" <?php echo $opt_cats[$cat] == 1 ? "checked" : "" ?>" />
							<label for="blippr_categories_<?=$cat?>"><?=$cat_title ?></label>
						</span>
					<? } ?>
				</th>
			</tr>
		</table>
		
		<h2>Display options</h2>
		<table class="form-table">
			<!--
			<tr valign="top">
				<th scope="row">Number of blips to show in the hover popup:</th>
				<td>
					<input type="text" name="blippr_numblips" class="regular-text" value="<?php echo get_option('blippr_numblips'); ?>" />
				</td>
				<td>
					<span class="setting-description">Allows you to specify how many reviews you would like to show in the popup. Set to 0 to not show any.</span>
				</td>
			</tr>
			//-->
			<tr valign="top">
				<th scope="row">Only show on post pages:</th>
				<td>
					<input type="checkbox" name="blippr_singles_only" id="blippr_singles_only" value="1" <?php echo get_option("blippr_singles_only") == 1 ? "checked" : "" ?>" />
				</td>
				<td>
					<span class="setting-description">You may want to use this if you use excerpts on your index or search pages, to prevent incorrect display due to blippr markup being stripped.</span>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">Include reviews in feed:</th>
				<td>
					<input type="checkbox" name="blippr_include_footer" id="blippr_include_footer" value="1" <?php echo get_option("blippr_include_footer") == 1 ? "checked" : "" ?>" />
				</td>
				<td>
					<span class="setting-description">Include a list of reviews linked in the post at the bottom of the feed item.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Discover items for non-links:</th>
				<td>
					<input type="checkbox" name="blippr_link_non_links" id="blippr_link_non_links" value="1" <?php echo get_option("blippr_link_non_links") == 1 ? "checked" : "" ?>" />
				</td>
				<td>
					<span class="setting-description">If checked, the plugin will attempt to automatically discover titles in the post. This is not recommended if you are serving content from the "Music" category.</span>
				</td>
			</tr>			
			<tr valign="top">
				<th scope="row">Include jQuery:</th>
				<td>
					<input type="checkbox" name="blippr_include_jquery" id="blippr_include_jquery" value="1" <?php echo get_option("blippr_include_jquery") == 1 ? "checked" : "" ?>" />
				</td>
				<td>
					<span class="setting-description">jQuery is a Javascript library that blippr uses to deliver content to your blog. Leave this on unless you are already using jQuery on your blog.</span>
				</td>
			</tr>			
		</table>
		
		<h2>Caching</h2>
		<table class="form-table">
			<tr valign="top">
				<th class="th-full" colspan="2" scope="row">
					<p>blippr will cache posts after it marks them up to ensure that your pages stay nice and snappy. Select the caching strategy you would like to use.</p>
					<? $caching = get_option('blippr_caching_strategy'); ?>
					<!--
					<div><input type="radio" name="blippr_caching_strategy" value="filesystem" <?=($caching == "filesystem") ? "checked" : "" ?> /> Filesystem - slowest, but requires no setup</div> //-->
					<div><input type="radio" name="blippr_caching_strategy" value="database" <?=($caching == "database") ? "checked" : "" ?> /> Database - scales better than filesystem, but incurs additional database overhead</div>
					<div><input type="radio" name="blippr_caching_strategy" <?= $this->has_memcache() ? "" : "disabled" ?> value="memcached" <?=($caching == "memcached") ? "checked" : "" ?> /> Memcached - fastest solution, requires installed memcached server</div>

				</th>
			</tr>			
		</table>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="blippr_link_non_links,blippr_source_key,blippr_include_footer,blippr_username,blippr_password,blippr_categories,blippr_caching_strategy,blippr_include_jquery,blippr_source_secret,blippr_singles_only" />

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
    <hr />
	<h2>Cache control</h2>
	<p>If you need to clear your blippr cache and have your blog pull in fresh content from blippr, you can do so here.</p>
	<p>Note that you generally shouldn't need to use this. When you save a post, its cached blippr content is automatically refreshed.</p>
  <div id="blippr-clear-cache" style="display: none;">
    <p style="color: #ff0000; font-weight: bold; ">Click below to clear your blippr cache. This may cause a brief performance hit to your site.</p>
    <?
    echo '<form name="wp_cache_content_delete" action="'. $_SERVER["REQUEST_URI"] . '" method="post">';
      echo '<input type="hidden" name="blippr_delete_cache" value="1" />';
      echo '<div class="submit" style="float:left;margin-left:10px"><input id="deletepost" type="submit" value="Delete Cache &raquo;" /></div>';
      echo wp_nonce_field('update-options');
    echo "</form>\n";
    ?>
  </div>
  <a href="#" onclick="jQuery('#blippr-clear-cache').toggle('fast'); jQuery(this).hide(); return false;">Delete blippr cache</a>
</div>
