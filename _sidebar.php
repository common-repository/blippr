<? if($post && $post->ID > 0) { ?>
<div id="blippr_delete_cache_status"></div>
<input type="hidden" value="<?=$post->ID ?>" name="blippr_delete_cache_id_js" id="blippr_delete_cache_id_js" />
<input type="button" value="Clear cache for this post" class="button-primary" id="blippr_delete_cache_id_js_sub" />
<? } else { ?>
<i>No cached content for this post</i>
<? } ?>