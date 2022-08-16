(function(global, $){
	$(function(){
		var Freeform		= window.Freeform || {};
		var $publish		= $('div.publish');
		var $publishParent	= $publish.closest('fieldset');
		var $formType		= $('[name="form_type"]');
		var $fieldChoices	= $('[data-field="form_fields"]', $publish);
		var $orderChoices	= $('.relate-wrap', $publish).not($fieldChoices);
		var $fieldIds		= $('[name="field_ids"]');
		var $fieldOrder		= $('[name="field_order"]');
		var $formFields     = $('input[name="form_fields[]"]:first').parents('fieldset.col-group:first');

		//has to be specific like this because EE has 2+ forms on the page
		//with no IDs on them.
		var $submit			= $('form[action*="save_form"] .form-btns [type="submit"]');

		//data-submit-text is required to get the name correct
		//by EE's JS. -_-
		$submit.val('Save').
			attr('data-submit-text', 'Save');

		//auto generate name
		Freeform.autoGenerateShortname(
			$('[name="form_label"]:first'),
			$('[name="form_name"]:first')
		);

		if (Freeform.pro)
		{
			$submit.each(function() {
				var self = $(this);

        var $saveAndFinish	= self.clone().addClass('save_and_finish');
        var $continue		= self.clone().attr('id', 'continue');
        $saveAndFinish.val(Freeform.lang.saveAndFinish).
        attr('data-submit-text', Freeform.lang.saveAndFinish);
        $continue.val(Freeform.lang.continue).
        attr('data-submit-text', Freeform.lang.continue);

        $(this).parent().append($saveAndFinish).append($continue);

        $formType.on('change', function(e){
          setTimeout(function(){
            if ($formType.val() === 'template')
            {
              $saveAndFinish.hide();
              $continue.hide();
              self.show();
              $publishParent.show();
              $formFields.show();
            }
            else
            {
              $saveAndFinish.show();
              $continue.show();
              self.hide();
              $publishParent.hide();
              $formFields.hide();
            }
          }, 100);
        }).change(); //fire once to get it all going to start

        //change return for different submit click
        $continue.on('click', function(){
          $('[name="ret"]').val('composer');
        });

        $saveAndFinish.on('click', function(){
          $('[name="ret"]').val('composer_save_finish');
        });
			});
		}


		$publish.on('mouseup', function(e){
			//timeout because apparently jQuery UI has a delay on drop
			//or is running async
			setTimeout(function(){
				var fieldIds = [];
				$orderChoices.find('label[data-entry-id]').each(function(e){
					var $that = $(this);
					fieldIds.push($that.attr('data-entry-id'));
				});

				$fieldOrder.val(fieldIds.join('|'));
				$fieldIds.val(fieldIds.sort().join('|'));
			}, 100);

		}).mouseup();
	});

  $('.field-order-block').each(function() { initCustomRelationships($(this)); });
}(window, jQuery));


function initCustomRelationships(elem) {
	var $checkAll = $('> div input:checkbox', elem);
	var $elements = $('> ul > li', elem);

	$checkAll.on({
		click: function() {
			$elements.trigger($(this).is(':checked') ? 'activate' : 'deactivate');
		}
	});

	$elements.on({
		activate: function () {
			$(this)
				.addClass('active')
				.find('input:checkbox')
				.prop('checked', true);
		},
		deactivate: function () {
      $(this)
        .removeClass('active')
        .find('input:checkbox')
        .prop('checked', false);
		}
	});

	$('input:checkbox', $elements).on({
		click: function () {
			$(this).parents('li:first').trigger($(this).is(':checked') ? 'activate' : 'deactivate');

			var total = $(this).parents('ul:first').find('input:checkbox').length;
			var checked = $(this).parents('ul:first').find('input:checkbox:checked').length;

			$(this).parents('.field-order-block').find('> div input:checkbox').prop('checked', total === checked);
		}
	});

	$('> ul', elem).sortable({
		handle: '.handle',
	});
}