<?
	global $wp_version;
	if($wp_version < 2.7) {
		$extraClasses = "blippr-fieldset-25";
	}
?>
<fieldset class="blippr-fieldset <?=$extraClasses?>">
	<legend>Add <?=$this->a_an($this->title_name(true))?> to this post</legend>
	<div style="font-weight: bold; margin: 5px;"></div>
	<div id="blippr_preview" style="display: none;"></div>	
	<div style="width: 75%;">
		<label for="blippr_search_field" class="clearinginput">Search for <?=$this->a_an($this->title_name(true))?>...</label>
		<div id="blippr-search-wrapper">
			<input type="text" class="blippr_autocomplete clearinginput blippr-fullwidth" id="blippr_search_field" rel="<?=$this->apiRoot?>/titles/autocomplete.json?media_type=<?=$this->media_types()?>" />
		</div>
		<div class="submit" style="padding: 0;"><input type="button" value="Add to post" id="blippr-add-title-to-post" /></div>	
		<a href="#" rel="blippr_syntax_help" onclick="return false;" class="blippr-toggle">Help</a>	
		<div class="blippr-clear">&nbsp;</div>
	</div>
	
	<div id="blippr_options" style="display: none;">
		<input type="hidden" name="blippr_insert_note" id="blippr_insert_note" />
		<div>
			Use search to find a title or manually enter an ID
		</div>
		<div id="blippr_id_entry">
			<label for="blippr_insert_id">blippr ID</label> <input type="text" id="blippr_insert_id" size="8">
		</div>		
	</div>
	<div class="blippr-clear">&nbsp;</div>
	<div id="blippr_syntax_help" style="display: none;">
		<p>Insert blippr content into your posts with easy-to-use blippr shortcodes. A shortcode takes the form of:</p>
		<p class="fixed">[blippr id="12345"/]</p>
		<p>or</p>
		<p class="fixed">[blippr title="Facebook"/]</p>
		<p>or</p>
		<p class="fixed">[blippr]Facebook[/blippr]</p>
		<p style="margin-top: 2em;">The ID can be found by using the blippr WordPress plugin (you're looking at it!), or from any blippr title's URL. If you use the name of a title, blippr will try to determine which title to show in the post. If it shows the wrong one, then lookup and use an ID instead.</p>
		<p style="margin-top: 2em;">When using names, you may specify which vertical you want blippr to search in. Valid verticals are:</p>
		<p class="fixed">apps, books, games, movies, music</p>
		<p style="margin-top: 2em;">Verticals are specified like so:</p>
		<p class="fixed">[blippr title="Chrome" type="apps" /]</p>
		<p class="fixed">[blippr type="movies"]Watchmen[/blippr]</p>
		<p style="margin-top: 2em;">Usually you won't need to specify the type, but it can help if blippr is finding the wrong titles.</p>
	</div>	
	<div class="blippr-clear">&nbsp;</div>
</fieldset>

<fieldset class="blippr-fieldset  <?=$extraClasses?> blippr-fieldset-disabled">
	<legend class="blippr-toggler"><img src="../wp-content/plugins/blippr/plus.png" style="vertical-align: text-top;" /> New <?=$this->title_name(true)?></legend>
	<div id="blippr-new-title-form" class="blippr-toggleable" style="display: none;">
		<p style="font-weight: bold; margin: 10px 0;">Can't find <?=$this->a_an($this->title_name(true))?> you want to add? No problem, just add it here. It'll be automatically added to your post.</p>
		<div class="blippr-error-box" style="display: none;"></div>

		<div class='blippr-row'>
			<span class="blippr-required">*</span> Name:
			<label for="blippr_new_title_title" class="clearinginput">Name, with correct spelling and punctuation</label>
			<input type="text" class="clearinginput blippr-fullwidth" tabindex="7" id="blippr_new_title_title" name="blippr-ntf-node[title]" />
		</div>
		
		<? if(sizeof($this->categories) > 1) { ?>
		<div class='blippr-row'>
			<span class="blippr-required">*</span> Name: This title is: 
			<div class='blippr-row'>
				<? foreach($this->categories as $type => $val) { ?>
				<span style="margin-right: 20px;">
					<input type="radio" name="blippr-ntf-media_type" class="blippr-form-data blippr-meta-select" tabindex="7" id="blippr-ntf-media_type_<?=$type?>" value="<?=$type?>" />
					<label for="blippr-ntf-media_type_<?=$type?>"><?=$this->a_an(preg_replace("/s$/", "", $type)) ?></label>
				</span>
				<? } ?>
			</div>
		</div>
		<? } else { ?>
			<input type="hidden" name="blippr-ntf-media_type" id="blippr-ntf-media_type" value="<?=$this->media_types()?>" />
		<? } ?>
		
		<? if($this->categories["apps"]) { ?>
			<div id="blippr-apps-meta" class="blippr-meta" style="display: none;">
				<div class='blippr-row'>
					URL:
					<label for="blippr_new_title_url" class="clearinginput">http://example.com</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-url-icon" tabindex="7" id="blippr_new_title_url" name="blippr-ntf-node[url_list]" />
				</div>
				<div class='blippr-row'>
					Developed by:
					<label for="blippr_new_title_developed_by" class="clearinginput">Software Company, Inc.</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_developed_by" name="blippr-ntf-node[developer_list]" />
				</div>
			</div>
		<? } ?>
		
		<? if($this->categories["books"]) { ?>
			<div id="blippr-books-meta" class="blippr-meta" style="display: none;">
				<div class='blippr-row'>
					Author(s):
					<label for="blippr_new_title_authors" class="clearinginput">John Doe, Jane Doe</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_authors" name="blippr-ntf-node[author_list]" />
				</div>
				<div class='blippr-row'>
					Editor(s):
					<label for="blippr_new_title_editors" class="clearinginput">Edward Editor</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_editors" name="blippr-ntf-node[editor_list]" />
				</div>
			</div>
		<? } ?>

		<? if($this->categories["games"]) { ?>
			<div id="blippr-games-meta" class="blippr-meta" style="display: none;">
				<div class='blippr-row'>
					Developed by:
					<label for="blippr_new_title_game_developers" class="clearinginput">Game Studio</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_game_developers" name="blippr-ntf-node[developer_list]" />
				</div> 
				<div class='blippr-row'>
					System(s):
					<label for="blippr_new_title_game_systems" class="clearinginput">Edward Editor</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_game_systems" name="blippr-ntf-node[system_list]" />
				</div>
			</div>
		<? } ?>
		
		<? if($this->categories["movies"]) { ?>
			<div id="blippr-movies-meta" class="blippr-meta" style="display: none;">
				<div class='blippr-row'>
					Starring:
					<label for="blippr_new_title_movie_starring" class="clearinginput">Joe Actor, Jane Actress</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_movie_starring" name="blippr-ntf-node[actor_list]" />
				</div>
				<div class='blippr-row'>
					Directed by:
					<label for="blippr_new_title_movie_directors" class="clearinginput">Jerry Directorheimer</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_movie_directors" name="blippr-ntf-node[director_list]" />
				</div>
				<div class='blippr-row'>
					Produced by:
					<label for="blippr_new_title_movie_producers" class="clearinginput">Jill Producer</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_movie_producers" name="blippr-ntf-node[producer_list]" />
				</div>
			</div>
		<? } ?>
		
		<? if($this->categories["music"]) { ?>
			<div id="blippr-music-meta" class="blippr-meta" style="display: none;">
				<div class='blippr-row'>
					Performed by:
					<label for="blippr_new_title_music_performers" class="clearinginput">Band Name, Artist Name</label>
					<input type="text" class="clearinginput blippr-fullwidth blippr-people-icon" tabindex="7" id="blippr_new_title_music_performers" name="blippr-ntf-node[group_list]" />
				</div>
			</div>		
		<? } ?>
		
		<div class='blippr-row'>
			Description:
			<textarea name="blippr-ntf-node[summary]" tabindex="7" id="blippr_new_title_description" rows="6" cols="60"></textarea>
		</div>
		<div class='blippr-row'>
			Tags:
			<label for="blippr_new_title_tags" class="clearinginput">Tags, separate multiple tags with commas</label>
			<input type="text" class="clearinginput blippr-fullwidth" tabindex="7" id="blippr_new_title_tags" name="blippr-ntf-node[tag_list]" />
		</div>
		<div class='blippr-row'>
			<label for="blippr_new_title_image">http://example.com/image.jpg</label>
			Image URL: <input type="text" class="clearinginput blippr-fullwidth" tabindex="7" id="blippr_new_title_image" name="blippr-ntf-node[image]" />
			<div id="blippr-new-image-preview"></div>
		</div>
		<div class="submit">
			<img src="../wp-content/plugins/blippr/22.gif" id="blippr-new-title-loader" style="display: none;" />
			<input type="button" value="Add title to blippr" tabindex="7" id="blippr-add-new-title" />
		</div>
	</div>
</fieldset>

