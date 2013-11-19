jQuery(document).ready(function(){
	//COLOR PICKERS----------------------------------------------------------------/

	//border focus for color circle
	jQuery('.module-inputField.hexVal').each(function(){
		jQuery(this).focus(function(){
							   
			 jQuery(this).parent().children('.colorSelect').addClass('colorSelect_focus');
			 
		})
	});
	
	//border unfocus for color circle
	jQuery('.module-inputField.hexVal').each(function(){
		jQuery(this).blur(function(){
							   
	 	 jQuery(this).parent().children('.colorSelect').removeClass('colorSelect_focus');
		 
		})
	});
	
	
	//mimick field clicks upon color circle clicks
	jQuery('div.colorSelect').click(function(){		
		//get the ID of currently clicked element
		var id = jQuery(this).attr('id');
		//trigger click 
		jQuery('.colorpickerField#' + id).trigger('click');
     });
	
	jQuery(':input.colorpickerField').blur( function () {
		checkup_colorpickerField_value(jQuery(this));
	});
	
	jQuery(':input.colorpickerField').bind('paste', function (e) {
		var element = jQuery(this);		
		setTimeout(function () {
			checkup_colorpickerField_value(element);
		}, 100);
	}).bind('keyup', function (e) {
		var element = jQuery(this);
		if(element.val().length < 7) {
			return;
		} else {
			checkup_colorpickerField_value(jQuery(this));
		}
	});
		// support function for above
		function checkup_colorpickerField_value (element) {
			// get val and remove #
			var colorcode_value = element.val();
			colorcode_value = colorcode_value.replace("#","");

			if(colorcode_value.length > 6) {
				colorcode_value = colorcode_value.substr(0,6);
			} else if (colorcode_value.length == 3) {
				colorcode_value = colorcode_value.replace(/([A-Za-z0-9])([A-Za-z0-9])([A-Za-z0-9])/,"$1$1$2$2$3$3");
			}
			
			// set the sanitized value.
			element.val(colorcode_value.toUpperCase());
			// refresh the color picker once here
			element.ColorPickerSetColor(element.val());
			
			var hexCheckPattern = /^#?([0-9a-f]{1,2}){3}$/i;
			if(!element.val().match(hexCheckPattern) || element.val().length !== 6)  {
				element.val('000000');
				element.parent().find('div.colorSelect').css('background-color', 'rgb(0, 0, 0)'); 
			} else {
				element.parent().find('div.colorSelect').css('background-color', '#' + element.val());
			}
			
			// to enable / disable save button
			colorpickerCheckForDefaultValue (element.val(), element.prop('defaultValue'), element.parent().find('input[id^="settingItem_save_"]'));
		}
	
	//pickers field init
	jQuery(':input.colorpickerField').ColorPicker({
		//initial color
		color: '#000',
		onChange: function (hsb, hex, rgb, el) {
			jQuery(el).val(hex);
			jQuery(el).parent().find('div.colorSelect').css('backgroundColor', '#' + hex);
		},
		onHide: function(one, two) {
			var el = jQuery(two['colorpicker']['el']);
			colorpickerCheckForDefaultValue (el.val(), el.prop('defaultValue'), el.parent().find('input[id^="settingItem_save_"]'));
		},
		//on submit actions
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).val(hex);
			colorpickerCheckForDefaultValue (jQuery(el).val(), jQuery(el).prop('defaultValue'), jQuery(el).parent().find('input[id^="settingItem_save_"]'));
			jQuery(el).ColorPickerHide();
		},
		//before actions
		onBeforeShow: function () {
			jQuery(this).ColorPickerSetColor(this.value);
		}
		})
		.bind('keyup', function(e){
			jQuery(this).ColorPickerSetColor(this.value);
			if(e.which == 13) {
				jQuery(this).ColorPickerHide();
			}
		})
		.bind('focus', function(e){
			jQuery(this).parent().find('input[id^="settingItem_save_"]').addClass('saveDisabled');
		});
	
	/* color picker aid functions */
	function colorpickerCheckForDefaultValue (initVal, thisDefaultValue, targetSaveButton){
		var targetRevertButton = jQuery(targetSaveButton).parent().find('input[id^="settingItem_revert_"]');
		if(initVal != thisDefaultValue) {
			jQuery(targetSaveButton).removeClass('saveDisabled');
			jQuery(targetRevertButton).removeClass('revertDisabled');
		} else {
			jQuery(targetSaveButton).addClass('saveDisabled');
			jQuery(targetRevertButton).addClass('revertDisabled');
		}
	}
});
