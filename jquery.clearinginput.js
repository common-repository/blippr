/*
 * clearingInput: a jQuery plugin
 *
 * clearingInput is a simple jQuery plugin that provides example/label text
 * inside text inputs that automatically clears when the input is focused.
 * Common uses are for a hint/example, or as a label when space is limited.
 *
 * For usage and examples, visit:
 * http://github.com/alexrabarts/jquery-clearinginput
 *
 * Licensed under the MIT:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright (c) 2008 Stateless Systems (http://statelesssystems.com)
 *
 * @author   Alex Rabarts (alexrabarts -at- gmail -dawt- com)
 * @requires jQuery v1.2 or later
 * @version  0.1.1
 */

(function ($) {
  $.extend($.fn, {
    clearingInput: function (options) {
      var defaults = {blurClass: 'blur'};

      options = $.extend(defaults, options);

      return this.each(function () {
        var input = $(this).addClass(options.blurClass);
		input.origType = input.attr("type").toLowerCase();
        var form  = input.parents('form:first');
        var label, text;

        text = options.text || textFromLabel() || input.val();
		
		if(input.origType && $.browser.msie) return;
        if (text) {
          if(input.val() == "") { input.val(text); }
		  else { input.removeClass(options.blurClass) }

		  var blurFunc = function () {
            if (input.val() === '' || input.val() === text) {
              input.val(text).addClass(options.blurClass);
			  if(!$.browser.msie && input.origType == "password") this.type = "text";
            }
          }
		  
          input.blur(blurFunc).focus(function () {
            if (input.val() === text) {
              input.val('');
			  if(!$.browser.msie && input.origType == "password") this.type = "password";
            }
            input.removeClass(options.blurClass);
          });

          form.submit(function() {
            if (input.hasClass(options.blurClass)) {
              input.val('');
            }
          });

		  blurFunc();
        }

        function textFromLabel() {
          label = form.find('label[for=' + input.attr('id') + ']');
          // Position label off screen and use it for the input text
          return label ? label.css({position: 'absolute', left: '-9999px'}).text() : '';
        }
      });
    }
  });
})(jQuery);
