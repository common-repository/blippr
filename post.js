// perform ajax loading of deferred widget blocks
jQuery(document).ready(function($) {
	$(".blippr_title").each(function() {
		var $this = $(this);
		var rel = $this.attr("rel") + "&callback=?";
		
		var error = function() {
			$this.find(".blippr_loader").fadeOut('normal', function() {
				$(this).html("<img src='wp-content/plugins/blippr/failed.png'> Widget could not be loaded").fadeIn("normal");
			});
		}

		var timeout = setTimeout(error, 12000);		
		$this.html("<div class='blippr_loader'>loading <img src='wp-content/plugins/blippr/22.gif' /></div>");
		$.ajax({
			type: "GET",
			data: {},
			dataType: "jsonp",
			url: rel,
			global: false,
			success: function(data) {
				clearTimeout(timeout);
				$this.html(data.html);
			}
		});
	});
});