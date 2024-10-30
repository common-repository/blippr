jQuery(document).ready(function($) {
	$.fn.clearField = function() {
		return this.each(function() {
			var type = this.type, tag = this.tagName.toLowerCase();
			if(tag == "input") {
				if(type == "text" || type == "password") {
					this.value = '';
				} else if(type == "checkbox" || type == "radio") {
					this.checked = false;
				}
			} else if(tag == "textarea") {
				this.value = '';
			} else if(tag == "select") {
				this.selectedIndex = -1;
			}
		});
	}

	// Add to stock editor
	var toolbar = document.getElementById("ed_toolbar");
	var blippr_add_shortcode = function() {
		edInsertContent(edCanvas, content);
	}

	if (toolbar) {
	  var button = document.createElement('input');
	  button.type = 'button';
	  button.value = 'blippr';
	  button.className = 'ed_button';
	  button.title = 'blippr';
	  button.id = 'ed_blippr';
	  toolbar.appendChild(button);
	  edButtons[edButtons.length] = new edButton("ed_blippr", "blippr", "[blippr]", "[/blippr]");
	  button.idx = edButtons.length - 1;
	  button.onclick = function() {
		edInsertTag(edCanvas, this.idx);
	  }
	}

	// Add to tinyMCE
	// tinyMCE.activeEditor.addButton('blippr', {
		// title : 'blippr',
		// image : '../wp-content/plugins/blippr/favicon.png',
		// onclick: function() {
			// alert(tinyMCE.selection.getContent({format : 'text'}));
		// }
	// });

	var formatTableItem = function(item) {
		s = "<tr class='blippr-line-item' id=blippr-line-item-" + item["id"] + ">";
		s += "<td class='blippr-line-item-image'><img src=\"" + item["image"] + "\" /></td>";
		s += "<td><a href=\"" + item["url"] + "\" target='_blank'>" + item["title"] + "</a></td>";
		s += "<td class='blippr-line-item-tools'>&nbsp;</td>";
		s += "</tr>";
		return s;
	}

	var addLineItemTools = function() {
		$("td.blippr-line-item-tools:not(.blippr-tools-added)").each(function() {
			var $this = $(this);
			$this.append("<a href='#' onclick='return false;' class='blippr-tool-remove'>remove</a>");
			$this.addClass("blippr-tools-added");
			$this.find(".blippr-tool-remove").click(function() {
				var e = $this.parents("tr:first");
				var parent = $this.parents("table:first");
				e.fadeOut("normal", function() {
					e.remove();
					serializeTable(parent.get(0));
					if(parent.find(".blippr-line-item").length == 0) {
						parent.find(".blippr-no-results").fadeIn('normal');
					}
				});
			});
		});
	}

	var serializeTable = function(table, row) {
		$(row).animate({backgroundColor: "#fff"}, 'fast');
		var rows = table.tBodies[0].rows;
		var rowOrder = Array();
		for(var i=0; i<rows.length; i++) {
			$row = $(rows[i]);
			if($row.hasClass("blippr-line-item")) {
				var id = $row.attr("id")
				var bits = id.split("-");
				rowOrder[rowOrder.length] = parseInt(bits[bits.length-1]);
			}
		}
		$("#" + $(table).attr("id") + "_values").val(rowOrder.join(","));
	}

	var startDrag = function(table, row) {
		$(row).animate({backgroundColor: "#ffc"}, 'fast');
	}

	$(".blippr_autocomplete").each(function() {
		$(this).autocomplete($(this).attr("rel"), {
			dataType: "jsonp",
			scroll: false,
			max: 5,
			parse: function(data) {
				$("#blippr-search-wrapper img").fadeOut("normal");
				if(data.length == 0) {
					return Array({
						data: {
							title: "<i>No results found</i>",
							info: "Please try a different search"
						}});
				} else {
					return data;
				}
			},
			formatItem: function(item) {
				var str = "";
				if(item.image) {
					str += "<img src=\"" + item.image + "\" />";
				}
				str += "<div class='t'>" + item.title + "</div>";
				if(item.info) {
					str += "<div class='i'>" + item.info + "</div>";
				}
				return "<div class='blippr_ac_result'>" + str + "</div>";
			}
		});
	}).result(function(event, item) {
		if(!item.id) return;
		$(this).val("");

		// $("#blippr_show_options:visible").hide();
		// $("#blippr_options:hidden").show();
		$("#blippr_insert_id").val(item.id).parents("tr:first").animate({backgroundColor: "#ffa"}, function() {
			$(this).animate({backgroundColor: "#fff"});
		});
		$("#blippr_insert_note").val(item.title.replace(/\"/, "'"));
		$("#blippr_preview").html("<img src='" + item.image + "' /> " + item.title).show();
	});

	$("#blippr_insert_id").change(function() {
		$("#blippr_insert_note").val("");
	});

	var insertIntoPost = function(content) {
		if($(edCanvas).is(":visible"))
			edInsertContent(edCanvas, content);
		else
			tinyMCE.execCommand('mceInsertContent', false, content);
	}

	$("#blippr-add-title-to-post").click(function() {
		var id = parseInt($("#blippr_insert_id").val());
		if(isNaN(id) || id <= 0) {
			alert("Invalid blippr ID entered.");
			return;
		}
		content = "[blippr id=\"" + id + "\"";
		if($("#blippr_hide_footer").get(0).checked) content += " reviews=\"false\"";

		var note = $("#blippr_insert_note").val();
		if(note && note != "") content += " note=\"" + note + "\"";

		var blipCount = $("#blippr_num_blips").val();
		if(parseInt(blipCount) != 3) content += " blips=\"" + blipCount + "\"";

		content += " /]";

		insertIntoPost(content);

		$("#blippr_preview").hide();
		$("#blippr_insert_id").val("");
		$("#blippr_insert_note").val("");
	});
	$(".blippr-toggle").click(function() {
		$("#" + $(this).attr("rel")).toggle();
	});

	$("#blippr_toggle_id_entry").click(function() {
		$("#blippr_id_entry").toggle('normal');
	});

	$(".blippr-toggler").click(function() {
		var fieldset = $(this).parents("fieldset:first");
		fieldset.toggleClass("blippr-fieldset-disabled");
		fieldset.find(".blippr-toggleable").toggle("normal");
	});

	$("input.clearinginput").clearingInput({blurClass: 'form-input-tip'});

	$("#blippr_new_title_image").change(function() {
		$("#blippr-new-image-preview").html("<img src=\"" + $(this).val() + "\" />");
	});

	$("input.blippr-meta-select").change(function() {
		$(".blippr-meta").hide().find("input").val("").blur();
		$("#blippr-" + $(this).val() + "-meta").fadeIn('normal');
	});
	if($(".blippr-meta").length == 1) $(".blippr-meta").show();

	// Mock a full form submit. Really ugly, but the only way we're going to make this happen inline in the WordPress post form.
	$("#blippr-add-new-title").click(function() {
		$("#blippr-new-title-loader").fadeIn();
		var data = { format: "json" };

		var buildData = function() {
			var $this = $(this);
			if($this.hasClass("form-input-tip")) return;

			var origID = $this.attr("name");
			var id = origID.replace("blippr-ntf-", "");
			if(id == origID) return;

			var val = $this.val();
			if(val == "undefined") return;

			data[id] = val;
		}

		var fields = [
			"#blippr-new-title-form input[type='checkbox']:checked",
			"#blippr-new-title-form input[type='radio']:checked",
			"#blippr-new-title-form input[type='text']",
			"#blippr-new-title-form input[type='hidden']",
			"#blippr-new-title-form textarea"
		];

		$(fields.join(",")).each(buildData);

		$.getJSON("../wp-content/plugins/blippr/proxy.php", data, function(data) {
			$("#blippr-new-title-loader").fadeOut();
			if(data.errors) {
				var errDiv = $("#blippr-new-title-form .blippr-error-box");
				errDiv.fadeOut("slow", function() {
					var messages;
					if(typeof(data.errors.error) == "object") {
						messages = data.errors.error.join("<br />");
					} else if (typeof(data.errors.error) == "string") {
						messages = data.errors.error;
					}
					errDiv.hide().text(messages);
					errDiv.fadeIn("slow");
				});
			} else {
				$(fields.join(",")).clearField();
				$("#blippr-new-title-form").animate({height: "hide", opacity: "hide"});
				var content = "[blippr id=\"" + data.node.id + "\" note=\"" + data.node.title.replace(/[\[\]\"]/, "") + "\" /]";
				insertIntoPost(content);
			}
		})
	});

	var flushCacheSuccess = function(data) {
		var e = $("#blippr_delete_cache_status");
		e.hide().html(data).fadeIn("normal");
	}

	$("#blippr_delete_cache_status").hide();
	$("#blippr_delete_cache_id_js_sub").click(function() {
		var id = $("#blippr_delete_cache_id_js").val();
		$.post("../wp-content/plugins/blippr/flush.php", {flush_post_id: id}, flushCacheSuccess, "text");
		return false;
	});
});